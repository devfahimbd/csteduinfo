<?php
/**
 * Result Search API - AJAX endpoint
 * Returns student result data as JSON
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';

header('Content-Type: application/json; charset=utf-8');

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

// Build query
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
            $rawCode = isset($fs['code']) ? trim($fs['code']) : '';
            $rawType = isset($fs['fail_type']) ? trim($fs['fail_type']) : 'T';

            // Bulletproof: if code contains (T,P) or (T) suffix, extract pure 5-digit code
            if (preg_match('/^(\d{5})\s*\(([^)]+)\)\s*$/', $rawCode, $m)) {
                $rawCode = $m[1];
                $rawType = strtoupper(preg_replace('/[,\s]+/', '', $m[2]));
            }

            // Normalize fail_type: remove commas, spaces (e.g. "T,P" -> "TP")
            $failType = strtoupper(preg_replace('/[,\s]+/', '', $rawType));

            // Look up subject info
            $subInfo = isset($subjectMap[$rawCode]) ? $subjectMap[$rawCode] : null;
            $subName = $subInfo ? $subInfo['subject_name'] : 'Unknown Subject';

            // Build display values
            if ($failType === 'T') {
                $fullForm = $subName . ' Theory Fail';
            } elseif ($failType === 'P') {
                $fullForm = $subName . ' Practical Fail';
            } else {
                $fullForm = $subName . ' Theory & Practical Fail';
            }

            // Display label for badge (TP -> "T,P")
            $failTypeLabel = ($failType === 'TP' || $failType === 'PT') ? 'T,P' : $failType;

            $enrichedFailed[] = [
                'code'            => $rawCode,
                'fail_type'       => $failType,
                'fail_type_label' => $failTypeLabel,
                'subject_name'    => $subName,
                'full_form'       => $fullForm,
            ];
        }
    }

    $enrichedResults[] = [
        'id'                    => $row['id'],
        'roll'                  => $row['roll'],
        'college_code'          => $row['college_code'],
        'college_name'          => $row['college_name'],
        'gpa'                   => $row['gpa'] !== null ? floatval($row['gpa']) : null,
        'result_type'           => $row['result_type'],
        'failed_subjects_count' => (int)$row['failed_subjects_count'],
        'failed_subjects'       => $enrichedFailed,
        'exam_year'             => $row['exam_year'],
        'regulation_year'       => $row['regulation_year'],
        'semester'              => $row['semester'],
        'program'               => $row['program'],
    ];
}

echo json_encode(['success' => true, 'data' => $enrichedResults]);
