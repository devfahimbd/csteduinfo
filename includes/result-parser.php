<?php
/**
 * Result Data Manager
 * Handles database operations for student results
 * No PDF parsing, no Python, no external libraries
 * Data is imported from JSON files
 */

require_once __DIR__ . '/config.php';

class ResultPdfParser {

    private $pdo;
    private $batchId;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Import student data from JSON array (batch INSERT with transaction)
     * JSON format: { exam_year, regulation_year, semester, program, students: [...] }
     * Uses chunks of 500 rows per INSERT for ~500x speedup on large datasets.
     */
    public function importFromJson($data) {
        $examYear = clean($data['exam_year']);
        $regulationYear = clean($data['regulation_year']);
        $semester = clean($data['semester']);
        $program = clean($data['program'] ?? 'Diploma In Engineering');
        $students = $data['students'];

        if (empty($students) || !is_array($students)) {
            throw new Exception('No student data found in the JSON file.');
        }

        // Step 1: Create batch record
        $stmt = $this->pdo->prepare("
            INSERT INTO result_batches (exam_year, regulation_year, semester, program, status)
            VALUES (?, ?, ?, ?, 'processing')
        ");
        $stmt->execute([$examYear, $regulationYear, $semester, $program]);
        $this->batchId = $this->pdo->lastInsertId();

        // Step 2: Pre-process ALL students into rows (fast, no DB)
        $passed = 0;
        $failed = 0;
        $rows = [];
        $validTypes = ['passed', 'referred', 'failed_4plus'];

        foreach ($students as $s) {
            $roll = clean($s['roll'] ?? '');
            if (empty($roll)) continue;

            $collegeCode = clean($s['college_code'] ?? '');
            $collegeName = clean($s['college_name'] ?? '');
            $gpa = isset($s['gpa']) && $s['gpa'] !== null ? floatval($s['gpa']) : null;
            $resultType = clean($s['result_type'] ?? 'passed');
            if (!in_array($resultType, $validTypes)) $resultType = 'passed';

            $failedSubjects = [];
            $failCount = 0;
            if (!empty($s['failed_subjects']) && is_array($s['failed_subjects'])) {
                foreach ($s['failed_subjects'] as $fs) {
                    $failedSubjects[] = [
                        'code' => clean($fs['code'] ?? ''),
                        'fail_type' => clean($fs['fail_type'] ?? 'T')
                    ];
                }
                $failCount = count($failedSubjects);
            }

            $rows[] = [
                $this->batchId, $roll, $collegeCode, $collegeName,
                $gpa, $resultType,
                !empty($failedSubjects) ? json_encode($failedSubjects) : null,
                $failCount
            ];

            if ($resultType === 'passed') $passed++;
            else $failed++;
        }

        if (empty($rows)) {
            $this->pdo->prepare("DELETE FROM result_batches WHERE id = ?")->execute([$this->batchId]);
            throw new Exception('No valid student records found (all missing roll numbers).');
        }

        // Step 3: Batch INSERT with transaction (500 rows per query)
        $this->pdo->beginTransaction();
        try {
            $chunkSize = 500;
            $totalRows = count($rows);
            $columns = "(batch_id, roll, college_code, college_name, gpa, result_type, failed_subjects_json, failed_subjects_count)";
            $placeholders = "(?,?,?,?,?,?,?,?)";

            for ($i = 0; $i < $totalRows; $i += $chunkSize) {
                $chunk = array_slice($rows, $i, $chunkSize);

                $sql = "INSERT INTO result_students $columns VALUES ";
                $params = [];

                foreach ($chunk as $row) {
                    $sql .= $placeholders . ",";
                    foreach ($row as $val) {
                        $params[] = $val;
                    }
                }

                $sql = rtrim($sql, ',');
                $this->pdo->prepare($sql)->execute($params);
            }

            // Step 4: Update batch stats
            $updateStmt = $this->pdo->prepare("
                UPDATE result_batches SET
                    total_students = ?,
                    total_passed = ?,
                    total_failed = ?,
                    status = 'completed'
                WHERE id = ?
            ");
            $updateStmt->execute([$totalRows, $passed, $failed, $this->batchId]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->pdo->prepare("DELETE FROM result_batches WHERE id = ?")->execute([$this->batchId]);
            throw new Exception('Database insert failed: ' . $e->getMessage());
        }

        return [
            'batch_id' => $this->batchId,
            'total_students' => $totalRows,
            'total_passed' => $passed,
            'total_failed' => $failed
        ];
    }

    // ─── Chunked Upload Methods ───

    /**
     * Create empty batch with 'processing' status
     */
    public function createEmptyBatch($examYear, $regulationYear, $semester, $program = 'Diploma In Engineering') {
        $stmt = $this->pdo->prepare("
            INSERT INTO result_batches (exam_year, regulation_year, semester, program, status)
            VALUES (?, ?, ?, ?, 'processing')
        ");
        $stmt->execute([$examYear, $regulationYear, $semester, $program]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Import a chunk of students into an existing batch
     * $students = array of normalized student records
     * Returns: ['inserted' => int, 'errors' => array]
     */
    public function importChunk($batchId, $students) {
        // Validate batch exists and is 'processing'
        $batch = $this->getBatch($batchId);
        if (!$batch) throw new Exception('Batch not found.');
        if ($batch['status'] !== 'processing') throw new Exception('Batch is not in processing state.');

        $validTypes = ['passed', 'referred', 'failed_4plus'];
        $rows = [];
        $errors = [];
        $passed = 0;
        $failed = 0;

        foreach ($students as $i => $s) {
            $roll = clean($s['roll'] ?? '');
            if (empty($roll)) {
                $errors[] = ['index' => $i, 'roll' => '(empty)', 'error' => 'Missing or empty roll number'];
                continue;
            }

            $collegeCode = clean($s['college_code'] ?? '');
            $collegeName = clean($s['college_name'] ?? '');
            $gpa = isset($s['gpa']) && $s['gpa'] !== null ? floatval($s['gpa']) : null;
            if ($gpa == 0 && ($s['gpa'] ?? null) !== 0 && ($s['gpa'] ?? null) !== '0') {
                $gpa = null;
            }

            $resultType = clean($s['result_type'] ?? 'passed');
            if (!in_array($resultType, $validTypes)) $resultType = 'passed';

            $failedSubjects = [];
            $failCount = 0;
            if (!empty($s['failed_subjects']) && is_array($s['failed_subjects'])) {
                foreach ($s['failed_subjects'] as $fs) {
                    $failedSubjects[] = [
                        'code' => clean($fs['code'] ?? ''),
                        'fail_type' => clean($fs['fail_type'] ?? 'T')
                    ];
                }
                $failCount = count($failedSubjects);
            }

            $rows[] = [
                $batchId, $roll, $collegeCode, $collegeName,
                $gpa, $resultType,
                !empty($failedSubjects) ? json_encode($failedSubjects) : null,
                $failCount
            ];

            if ($resultType === 'passed') $passed++;
            else $failed++;
        }

        $inserted = 0;
        if (!empty($rows)) {
            $this->pdo->beginTransaction();
            try {
                $chunkSize = 500;
                $totalRows = count($rows);
                $columns = "(batch_id, roll, college_code, college_name, gpa, result_type, failed_subjects_json, failed_subjects_count)";
                $placeholders = "(?,?,?,?,?,?,?,?)";

                for ($i = 0; $i < $totalRows; $i += $chunkSize) {
                    $chunk = array_slice($rows, $i, $chunkSize);
                    $sql = "INSERT INTO result_students $columns VALUES ";
                    $params = [];
                    foreach ($chunk as $row) {
                        $sql .= $placeholders . ",";
                        foreach ($row as $val) {
                            $params[] = $val;
                        }
                    }
                    $sql = rtrim($sql, ',');
                    $this->pdo->prepare($sql)->execute($params);
                }
                $this->pdo->commit();
                $inserted = $totalRows;
            } catch (Exception $e) {
                $this->pdo->rollBack();
                throw new Exception('Database insert failed for chunk: ' . $e->getMessage());
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors, 'passed' => $passed, 'failed' => $failed];
    }

    /**
     * Finalize batch - count stats and update
     */
    public function finalizeBatch($batchId) {
        $batch = $this->getBatch($batchId);
        if (!$batch) throw new Exception('Batch not found.');

        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN result_type='passed' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN result_type IN ('referred','failed_4plus') THEN 1 ELSE 0 END) as failed
            FROM result_students WHERE batch_id = ?
        ");
        $stmt->execute([$batchId]);
        $stats = $stmt->fetch();

        $update = $this->pdo->prepare("
            UPDATE result_batches SET
                total_students = ?,
                total_passed = ?,
                total_failed = ?,
                status = 'completed'
            WHERE id = ?
        ");
        $update->execute([
            $stats['total'],
            $stats['passed'],
            $stats['failed'],
            $batchId
        ]);

        return [
            'batch_id' => $batchId,
            'total_students' => (int)$stats['total'],
            'total_passed' => (int)$stats['passed'],
            'total_failed' => (int)$stats['failed']
        ];
    }

    // ─── Database query methods (used by other pages) ───

    public function getBatch($batchId) {
        $stmt = $this->pdo->prepare("SELECT * FROM result_batches WHERE id = ?");
        $stmt->execute([$batchId]);
        return $stmt->fetch();
    }

    public function getAllBatches() {
        $stmt = $this->pdo->query("SELECT * FROM result_batches ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function deleteBatch($batchId) {
        $this->pdo->prepare("DELETE FROM result_students WHERE batch_id = ?")->execute([$batchId]);
        $this->pdo->prepare("DELETE FROM result_batches WHERE id = ?")->execute([$batchId]);
    }

    public function getStudentsByBatch($batchId, $page = 1, $perPage = 50, $search = '') {
        $offset = ($page - 1) * $perPage;
        $where = "WHERE batch_id = ?";
        $params = [$batchId];
        if (!empty($search)) {
            $where .= " AND (roll LIKE ? OR college_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $total = $this->pdo->prepare("SELECT COUNT(*) FROM result_students $where");
        $total->execute($params);
        $count = $total->fetchColumn();
        $stmt = $this->pdo->prepare("SELECT * FROM result_students $where ORDER BY roll ASC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        return ['students' => $stmt->fetchAll(), 'total' => $count, 'page' => $page, 'per_page' => $perPage, 'total_pages' => max(1, ceil($count / $perPage))];
    }

    public function searchByRoll($roll) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, b.exam_year, b.regulation_year, b.semester, b.program
            FROM result_students s JOIN result_batches b ON s.batch_id = b.id
            WHERE s.roll = ? AND b.status = 'completed'
            ORDER BY b.exam_year DESC, b.semester ASC
        ");
        $stmt->execute([$roll]);
        return $stmt->fetchAll();
    }

    public function getSubjectInfo($code) {
        $stmt = $this->pdo->prepare("SELECT * FROM result_subjects WHERE subject_code = ? AND status = 1");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function getAllSubjects() {
        return $this->pdo->query("SELECT * FROM result_subjects ORDER BY subject_code ASC")->fetchAll();
    }

    public function saveSubject($code, $name, $tFullName, $pFullName) {
        $stmt = $this->pdo->prepare("
            INSERT INTO result_subjects (subject_code, subject_name, t_full_name, p_full_name) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE subject_name = VALUES(subject_name), t_full_name = VALUES(t_full_name), p_full_name = VALUES(p_full_name)
        ");
        $stmt->execute([$code, $name, $tFullName, $pFullName]);
    }

    public function deleteSubject($id) {
        $this->pdo->prepare("DELETE FROM result_subjects WHERE id = ?")->execute([$id]);
    }

    public function getBatchStats($batchId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN result_type='passed' THEN 1 ELSE 0 END) as passed, SUM(CASE WHEN result_type IN ('referred','failed_4plus') THEN 1 ELSE 0 END) as failed, AVG(CASE WHEN gpa IS NOT NULL THEN gpa END) as avg_gpa FROM result_students WHERE batch_id = ?");
        $stmt->execute([$batchId]);
        $stats = ['basic' => $stmt->fetch()];

        $stmt = $this->pdo->prepare("SELECT JSON_UNQUOTE(JSON_EXTRACT(failed_subjects_json, '$[*].code')) as sc FROM result_students WHERE batch_id = ? AND failed_subjects_json IS NOT NULL");
        $stmt->execute([$batchId]);
        $fc = [];
        while ($r = $stmt->fetch()) {
            if ($r['sc']) {
                preg_match_all('/"(\d{5})"/', $r['sc'], $m);
                foreach ($m[1] as $c) {
                    $fc[$c] = ($fc[$c] ?? 0) + 1;
                }
            }
        }
        arsort($fc);
        $stats['subject_fails'] = $fc;

        $stmt = $this->pdo->prepare("SELECT college_code, college_name, COUNT(*) as total, SUM(CASE WHEN result_type='passed' THEN 1 ELSE 0 END) as passed, SUM(CASE WHEN result_type IN ('referred','failed_4plus') THEN 1 ELSE 0 END) as failed FROM result_students WHERE batch_id = ? GROUP BY college_code, college_name ORDER BY total DESC LIMIT 30");
        $stmt->execute([$batchId]);
        $stats['college_stats'] = $stmt->fetchAll();
        return $stats;
    }

    public function getGlobalStats() {
        return ['batches' => $this->pdo->query("SELECT b.*, COUNT(s.id) as student_count, SUM(CASE WHEN s.result_type='passed' THEN 1 ELSE 0 END) as passed_count, SUM(CASE WHEN s.result_type IN ('referred','failed_4plus') THEN 1 ELSE 0 END) as failed_count FROM result_batches b LEFT JOIN result_students s ON b.id = s.batch_id WHERE b.status='completed' GROUP BY b.id ORDER BY b.created_at DESC")->fetchAll()];
    }
}
