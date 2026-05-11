<?php
/**
 * BTEB Result PDF Parser
 * Extracts student result data from BTEB Diploma Engineering result PDFs
 */

require_once __DIR__ . '/config.php';

class ResultPdfParser {

    private $pdo;
    private $batchId;
    private $currentCollegeCode = '';
    private $currentCollegeName = '';
    private $currentSection = ''; // 'passed', 'referred', 'failed_4plus'
    private $parsedStudents = [];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Main entry: Parse PDF file and save to database
     */
    public function parseAndSave($filePath, $examYear, $regulationYear, $semester, $program) {
        // Create batch record
        $this->createBatch($examYear, $regulationYear, $semester, $program, $filePath);

        try {
            // Extract text from PDF
            $text = $this->extractPdfText($filePath);
            if (!$text) {
                throw new Exception('Could not extract text from PDF');
            }

            // Parse the text
            $this->parseText($text);

            // Save students to database
            $this->saveStudents();

            // Update batch stats
            $this->updateBatchStats();

            return [
                'success' => true,
                'batch_id' => $this->batchId,
                'total_students' => count($this->parsedStudents),
                'message' => 'PDF parsed and saved successfully'
            ];
        } catch (Exception $e) {
            $this->updateBatchStatus('failed');
            throw $e;
        }
    }

    /**
     * Create batch record in database
     */
    private function createBatch($examYear, $regulationYear, $semester, $program, $filePath) {
        $fileName = basename($filePath);
        $stmt = $this->pdo->prepare("
            INSERT INTO result_batches (exam_year, regulation_year, semester, program, pdf_file)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$examYear, $regulationYear, $semester, $program, $fileName]);
        $this->batchId = $this->pdo->lastInsertId();
    }

    /**
     * Find available Python executable
     */
    private function findPython() {
        $candidates = ['python3', 'python', 'C:\\Python310\\python.exe', 'C:\\Python39\\python.exe', 
                       'C:\\Python311\\python.exe', 'C:\\Python312\\python.exe',
                       'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python310\\python.exe'];
        
        foreach ($candidates as $cmd) {
            $testOutput = [];
            $retCode = 0;
            @exec($cmd . ' --version 2>&1', $testOutput, $retCode);
            if ($retCode === 0 && !empty($testOutput)) {
                return $cmd;
            }
        }
        
        return 'python';
    }

    /**
     * Extract text from PDF using python3 + PyMuPDF
     */
    private function extractPdfText($filePath) {
        $output = [];
        $retCode = 0;

        // Find Python executable (works on both Windows and Linux)
        $pythonCmd = $this->findPython();
        
        // Write a temporary Python script file instead of -c (more reliable on Windows)
        $tempScript = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bteb_parse_' . uniqid() . '.py';
        $scriptContent = implode("\n", [
            'import fitz',
            'import sys',
            'try:',
            '    doc = fitz.open(sys.argv[1])',
            '    for page in doc:',
            '        print(page.get_text())',
            'except Exception as e:',
            '    print("ERROR: " + str(e), file=sys.stderr)',
            '    sys.exit(1)',
        ]);
        file_put_contents($tempScript, $scriptContent);

        $cmd = $pythonCmd . ' "' . $tempScript . '" "' . str_replace('\\', '\\\\', $filePath) . '" 2>&1';
        exec($cmd, $output, $retCode);

        // Clean up temp file
        @unlink($tempScript);

        if ($retCode !== 0) {
            $errorOutput = implode("\n", $output);
            throw new Exception('PDF text extraction failed (code ' . $retCode . '). ' . ($errorOutput ?: 'Make sure PyMuPDF is installed: pip install pymupdf'));
        }

        if (empty($output)) {
            throw new Exception('PDF text extraction returned empty result. The PDF file may be corrupted or empty.');
        }

        return implode("\n", $output);
    }

    /**
     * Parse extracted text into structured data
     */
    private function parseText($text) {
        $lines = explode("\n", $text);
        $totalLines = count($lines);
        $i = 0;

        while ($i < $totalLines) {
            $line = trim($lines[$i]);

            // Skip empty lines
            if (empty($line)) {
                $i++;
                continue;
            }

            // Skip page numbers
            if (preg_match('/^Page \d+ of \d+/', $line)) {
                $i++;
                continue;
            }

            // Skip memo lines
            if (preg_match('/^Memo No/', $line)) {
                $i++;
                continue;
            }

            // Check for section keywords FIRST (before skipping notice text)
            if (preg_match('/passed in all subjects/i', $line)) {
                $this->currentSection = 'passed';
                $i++;
                continue;
            }
            if (preg_match('/failed in three or less/i', $line) || preg_match('/failed in 3 or less/i', $line)) {
                $this->currentSection = 'referred';
                $i++;
                continue;
            }
            if (preg_match('/failed in four or more/i', $line) || preg_match('/failed in 4 or more/i', $line)) {
                $this->currentSection = 'failed_4plus';
                $i++;
                continue;
            }

            // Detect college header: "11044 - Himaloy Polytechnic Institute..."
            if (preg_match('/^(\d{4,5})\s*[-–]\s*(.+)$/', $line, $m)) {
                $this->currentCollegeCode = trim($m[1]);
                $this->currentCollegeName = trim($m[2]);
                $this->currentSection = '';
                $i++;
                continue;
            }

            // Skip notice header text
            if (preg_match('/^(Note:|NOTICE|Bangladesh Technical Education Board|Office of the Controller|Agargaon|Date :|\( Enrg|Controller of Examinations|Deputy Controller)/', $line)) {
                $i++;
                continue;
            }

            // Skip numbered distribution list
            if (preg_match('/^\d+\.?\s*(Director|Principal|Secretary|Deputy|Assistant|Guard)/', $line)) {
                $i++;
                continue;
            }

            // Skip lines that are part of the notice text (but NOT section keyword lines - already handled above)
            if (preg_match('/(It is to be notified|listed below|in accordance with|regulation of the board|obtained GPA|referred subjects|current semester|DIPLOMA IN)/i', $line)) {
                $i++;
                continue;
            }

            // Skip stray numbers (section numbers like "1", "2", "25" that appear in PDF)
            if (preg_match('/^\d{1,2}$/', $line)) {
                $i++;
                continue;
            }

            // Try to parse student data
            // Pattern 1: Passed student - "300010 (  3.10 )"
            if (preg_match('/^(\d{5,6})\s*\(\s*([\d.]+)\s*\)$/', $line, $m)) {
                if ($this->currentCollegeCode && ($this->currentSection === 'passed' || empty($this->currentSection))) {
                    $this->addStudent($m[1], floatval($m[2]), 'passed', []);
                }
                $i++;
                continue;
            }

            // Pattern 2: Failed/referred student - "200013 {  25913(T), 26711(T) }" (single line)
            if (preg_match('/^(\d{5,6})\s*\{(.+)\}$/', $line, $m)) {
                if ($this->currentCollegeCode) {
                    $failedSubjects = $this->parseFailedSubjects($m[2]);
                    $resultType = ($this->currentSection === 'failed_4plus' && count($failedSubjects) >= 4) ? 'failed_4plus' : 'referred';
                    $this->addStudent($m[1], null, $resultType, $failedSubjects);
                }
                $i++;
                continue;
            }

            // Pattern 3: Multi-line failed subjects - roll starts, subjects continue on next lines
            if (preg_match('/^(\d{5,6})\s*\{(.+)/', $line, $m)) {
                // Collect continuation lines until we find closing brace
                $subjectText = $m[2];
                $j = $i + 1;
                while ($j < $totalLines && !preg_match('/\}/', $subjectText)) {
                    $nextLine = trim($lines[$j]);
                    if (preg_match('/^\d{5,6}\s/', $nextLine) || preg_match('/^\d{4,5}\s*[-–]/', $nextLine)) {
                        break; // Next student or college
                    }
                    $subjectText .= ' ' . $nextLine;
                    $j++;
                }
                // Remove closing brace
                $subjectText = preg_replace('/\}.*/', '', $subjectText);

                if ($this->currentCollegeCode) {
                    $failedSubjects = $this->parseFailedSubjects($subjectText);
                    $resultType = ($this->currentSection === 'failed_4plus' && count($failedSubjects) >= 4) ? 'failed_4plus' : 'referred';
                    $this->addStudent($m[1], null, $resultType, $failedSubjects);
                }
                $i = $j;
                continue;
            }

            // Pattern 4: Continuation of previous student (line with just subjects)
            // This handles lines that are just closing braces or subject codes continuing from previous line
            if (preg_match('/^[\d\s,\(\)TP]+$/', $line) && !empty($this->parsedStudents)) {
                // Check if last parsed student was a multi-line one that might need continuation
                $i++;
                continue;
            }

            $i++;
        }
    }

    /**
     * Parse failed subject string like "25913(T), 26711(T)" into structured array
     */
    private function parseFailedSubjects($text) {
        $subjects = [];
        // Match patterns like: 25911(T), 25912(T,P), 25913(T,P),
        preg_match_all('/(\d{5})\(([TP,]+)\)/', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $code = $match[1];
            $tpStr = $match[2];
            $tpArr = array_map('trim', explode(',', $tpStr));
            $types = [];
            foreach ($tpArr as $t) {
                if ($t === 'T') $types[] = 'T';
                if ($t === 'P') $types[] = 'P';
            }
            $subjects[] = [
                'code' => $code,
                'fail_type' => implode(',', $types) // e.g., "T", "P", or "T,P"
            ];
        }

        return $subjects;
    }

    /**
     * Add parsed student to array
     */
    private function addStudent($roll, $gpa, $resultType, $failedSubjects) {
        $this->parsedStudents[] = [
            'roll' => $roll,
            'college_code' => $this->currentCollegeCode,
            'college_name' => $this->currentCollegeName,
            'gpa' => $gpa,
            'result_type' => $resultType,
            'failed_subjects' => $failedSubjects,
            'failed_subjects_count' => count($failedSubjects)
        ];
    }

    /**
     * Save all parsed students to database
     */
    private function saveStudents() {
        if (empty($this->parsedStudents)) {
            throw new Exception('No student data found in the PDF. Please check the PDF format.');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO result_students 
            (batch_id, roll, college_code, college_name, gpa, result_type, failed_subjects_json, failed_subjects_count)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($this->parsedStudents as $student) {
            $failedJson = !empty($student['failed_subjects']) ? json_encode($student['failed_subjects']) : null;
            $stmt->execute([
                $this->batchId,
                $student['roll'],
                $student['college_code'],
                $student['college_name'],
                $student['gpa'],
                $student['result_type'],
                $failedJson,
                $student['failed_subjects_count']
            ]);
        }
    }

    /**
     * Update batch statistics
     */
    private function updateBatchStats() {
        $stmt = $this->pdo->prepare("
            UPDATE result_batches SET
                total_students = (SELECT COUNT(*) FROM result_students WHERE batch_id = ?),
                total_passed = (SELECT COUNT(*) FROM result_students WHERE batch_id = ? AND result_type = 'passed'),
                total_failed = (SELECT COUNT(*) FROM result_students WHERE batch_id = ? AND result_type IN ('referred', 'failed_4plus')),
                status = 'completed'
            WHERE id = ?
        ");
        $stmt->execute([$this->batchId, $this->batchId, $this->batchId, $this->batchId]);
    }

    /**
     * Update batch status on failure
     */
    private function updateBatchStatus($status) {
        $stmt = $this->pdo->prepare("UPDATE result_batches SET status = ? WHERE id = ?");
        $stmt->execute([$status, $this->batchId]);
    }

    /**
     * Get batch by ID
     */
    public function getBatch($batchId) {
        $stmt = $this->pdo->prepare("SELECT * FROM result_batches WHERE id = ?");
        $stmt->execute([$batchId]);
        return $stmt->fetch();
    }

    /**
     * Get all batches
     */
    public function getAllBatches() {
        $stmt = $this->pdo->query("SELECT * FROM result_batches ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Delete batch and all its students
     */
    public function deleteBatch($batchId) {
        $stmt = $this->pdo->prepare("DELETE FROM result_batches WHERE id = ?");
        $stmt->execute([$batchId]);
    }

    /**
     * Get students by batch with pagination
     */
    public function getStudentsByBatch($batchId, $page = 1, $perPage = 50, $search = '') {
        $offset = ($page - 1) * $perPage;

        $where = "WHERE batch_id = ?";
        $params = [$batchId];

        if (!empty($search)) {
            $where .= " AND (roll LIKE ? OR college_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM result_students $where");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT * FROM result_students $where ORDER BY roll ASC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $students = $stmt->fetchAll();

        return [
            'students' => $students,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, ceil($total / $perPage))
        ];
    }

    /**
     * Search student by roll number across all batches
     */
    public function searchByRoll($roll) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, b.exam_year, b.regulation_year, b.semester, b.program
            FROM result_students s
            JOIN result_batches b ON s.batch_id = b.id
            WHERE s.roll = ? AND b.status = 'completed'
            ORDER BY b.exam_year DESC, b.semester ASC
        ");
        $stmt->execute([$roll]);
        return $stmt->fetchAll();
    }

    /**
     * Get subject info by code
     */
    public function getSubjectInfo($code) {
        $stmt = $this->pdo->prepare("SELECT * FROM result_subjects WHERE subject_code = ? AND status = 1");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    /**
     * Get all subjects
     */
    public function getAllSubjects() {
        $stmt = $this->pdo->query("SELECT * FROM result_subjects ORDER BY subject_code ASC");
        return $stmt->fetchAll();
    }

    /**
     * Save subject (insert or update)
     */
    public function saveSubject($code, $name, $tFullName, $pFullName) {
        $stmt = $this->pdo->prepare("
            INSERT INTO result_subjects (subject_code, subject_name, t_full_name, p_full_name)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                subject_name = VALUES(subject_name),
                t_full_name = VALUES(t_full_name),
                p_full_name = VALUES(p_full_name)
        ");
        $stmt->execute([$code, $name, $tFullName, $pFullName]);
    }

    /**
     * Delete subject
     */
    public function deleteSubject($id) {
        $stmt = $this->pdo->prepare("DELETE FROM result_subjects WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Get statistics for a batch
     */
    public function getBatchStats($batchId) {
        $stats = [];

        // Basic counts
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN result_type = 'passed' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN result_type IN ('referred', 'failed_4plus') THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN result_type = 'failed_4plus' THEN 1 ELSE 0 END) as failed_4plus,
                AVG(CASE WHEN gpa IS NOT NULL THEN gpa END) as avg_gpa
            FROM result_students WHERE batch_id = ?
        ");
        $stmt->execute([$batchId]);
        $basicStats = $stmt->fetch();
        $stats['basic'] = $basicStats;

        // Failed subjects frequency
        $stmt = $this->pdo->prepare("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(failed_subjects_json, '$[*].code')) as subject_codes
            FROM result_students 
            WHERE batch_id = ? AND failed_subjects_json IS NOT NULL
        ");
        $stmt->execute([$batchId]);
        $subjectFailCount = [];
        while ($row = $stmt->fetch()) {
            $codes = $row['subject_codes'];
            if ($codes) {
                // Parse individual codes
                preg_match_all('/"(\d{5})"/', $codes, $codeMatches);
                foreach ($codeMatches[1] as $code) {
                    if (!isset($subjectFailCount[$code])) {
                        $subjectFailCount[$code] = 0;
                    }
                    $subjectFailCount[$code]++;
                }
            }
        }
        arsort($subjectFailCount);
        $stats['subject_fails'] = $subjectFailCount;

        // College-wise stats
        $stmt = $this->pdo->prepare("
            SELECT 
                college_code,
                college_name,
                COUNT(*) as total,
                SUM(CASE WHEN result_type = 'passed' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN result_type IN ('referred', 'failed_4plus') THEN 1 ELSE 0 END) as failed,
                ROUND(AVG(CASE WHEN gpa IS NOT NULL THEN gpa END), 2) as avg_gpa
            FROM result_students 
            WHERE batch_id = ?
            GROUP BY college_code, college_name
            ORDER BY total DESC
            LIMIT 30
        ");
        $stmt->execute([$batchId]);
        $stats['college_stats'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Get global statistics across all batches
     */
    public function getGlobalStats() {
        $stats = [];

        // Per batch summary
        $stmt = $this->pdo->query("
            SELECT b.*, 
                COUNT(s.id) as student_count,
                SUM(CASE WHEN s.result_type = 'passed' THEN 1 ELSE 0 END) as passed_count,
                SUM(CASE WHEN s.result_type IN ('referred', 'failed_4plus') THEN 1 ELSE 0 END) as failed_count
            FROM result_batches b
            LEFT JOIN result_students s ON b.id = s.batch_id
            WHERE b.status = 'completed'
            GROUP BY b.id
            ORDER BY b.created_at DESC
        ");
        $stats['batches'] = $stmt->fetchAll();

        return $stats;
    }
}
