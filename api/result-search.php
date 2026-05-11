<?php
/**
 * Result Search API - AJAX endpoint
 * Returns student result data as JSON
 * 
 * Parameters:
 *   roll (required) - Student roll number
 *   regulation_year (optional) - Filter by regulation year
 *   semester (optional) - Filter by semester (e.g., "1st Semester")
 * 
 * Logic:
 *   - If only roll provided → show ALL results for that roll (all semesters)
 *   - If roll + regulation_year → show results for that regulation
 *   - If roll + semester → show results for that specific semester
 *   - If roll + regulation_year + semester → show results for that specific combination
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';

header('Content-Type: application/json; charset=utf-8');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$roll = isset($_POST['roll']) ? trim($_POST['roll']) : '';
$regulationYear = isset($_POST['regulation_year']) ? trim($_POST['regulation_year']) : '';
$semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';

if (empty($roll)) {
    echo json_encode(['success' => false, 'message' => 'Roll number is required']);
    exit;
}

// ─── Build query based on filters ───
$where = "WHERE s.roll = ? AND b.status = 'completed'";
$params = [$roll];

if (!empty($regulationYear)) {
    $where .= " AND b.regulation_year = ?";
    $params[] = $regulationYear;
}

if (!empty($semester)) {
    $where .= " AND b.semester = ?";
    $params[] = $semester;
}

$sql = "
    SELECT s.*, b.exam_year, b.regulation_year, b.semester, b.program
    FROM result_students s
    JOIN result_batches b ON s.batch_id = b.id
    {$where}
    ORDER BY b.exam_year DESC, b.semester ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

if (empty($results)) {
    echo json_encode(['success' => false, 'message' => 'No result found for this roll number']);
    exit;
}

// Get all subjects for name mapping
$parser = new ResultPdfParser($pdo);
$allSubjects = $parser->getAllSubjects();
$subjectMap = [];
foreach ($allSubjects as $sub) {
    $subjectMap[$sub['subject_code']] = $sub;
}

// Enrich results with subject names
$enrichedResults = [];
foreach ($results as $row) {
    $failedSubjects = $row['failed_subjects_json'] ? json_decode($row['failed_subjects_json'], true) : [];
    
    $enrichedFailed = [];
    if (is_array($failedSubjects)) {
        foreach ($failedSubjects as $fs) {
            $code = $fs['code'];
            $failType = $fs['fail_type'] ?? 'T';
            $subInfo = isset($subjectMap[$code]) ? $subjectMap[$code] : null;
            
            $enrichedFailed[] = [
                'code' => $code,
                'fail_type' => $failType,
                'subject_name' => $subInfo ? $subInfo['subject_name'] : 'Unknown Subject',
                't_full' => $subInfo ? $subInfo['t_full_name'] : 'Theory',
                'p_full' => $subInfo ? $subInfo['p_full_name'] : 'Practical',
            ];
        }
    }

    $enrichedResults[] = [
        'id' => $row['id'],
        'roll' => $row['roll'],
        'college_code' => $row['college_code'],
        'college_name' => $row['college_name'],
        'gpa' => $row['gpa'] !== null ? floatval($row['gpa']) : null,
        'result_type' => $row['result_type'],
        'failed_subjects_count' => (int)$row['failed_subjects_count'],
        'failed_subjects' => $enrichedFailed,
        'exam_year' => $row['exam_year'],
        'regulation_year' => $row['regulation_year'],
        'semester' => $row['semester'],
        'program' => $row['program'],
    ];
}

echo json_encode(['success' => true, 'data' => $enrichedResults]);
