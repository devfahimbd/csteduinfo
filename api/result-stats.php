<?php
/**
 * Result Stats API - Returns statistics for result page
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';

header('Content-Type: application/json; charset=utf-8');

$parser = new ResultPdfParser($pdo);
$regulationYear = isset($_GET['regulation_year']) ? trim($_GET['regulation_year']) : '';

$where = "WHERE b.status = 'completed'";
$params = [];

if (!empty($regulationYear)) {
    $where .= " AND b.regulation_year = ?";
    $params[] = $regulationYear;
}

// 1. Overall stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT b.id) as total_batches,
        COUNT(s.id) as total_students,
        SUM(CASE WHEN s.result_type = 'passed' THEN 1 ELSE 0 END) as total_passed,
        SUM(CASE WHEN s.result_type IN ('referred', 'failed_4plus') THEN 1 ELSE 0 END) as total_failed,
        SUM(CASE WHEN s.result_type = 'failed_4plus' THEN 1 ELSE 0 END) as total_failed_4plus,
        ROUND(AVG(CASE WHEN s.gpa IS NOT NULL THEN s.gpa END), 2) as avg_gpa
    FROM result_batches b
    LEFT JOIN result_students s ON b.id = s.batch_id
    $where
");
$stmt->execute($params);
$overall = $stmt->fetch();

// 2. Per-semester stats
$stmt = $pdo->prepare("
    SELECT 
        b.semester,
        b.exam_year,
        b.regulation_year,
        COUNT(s.id) as total,
        SUM(CASE WHEN s.result_type = 'passed' THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN s.result_type IN ('referred', 'failed_4plus') THEN 1 ELSE 0 END) as failed,
        ROUND(AVG(CASE WHEN s.gpa IS NOT NULL THEN s.gpa END), 2) as avg_gpa
    FROM result_batches b
    LEFT JOIN result_students s ON b.id = s.batch_id
    $where
    GROUP BY b.semester, b.exam_year, b.regulation_year
    ORDER BY b.exam_year DESC, b.semester ASC
");
$stmt->execute($params);
$semesterStats = $stmt->fetchAll();

// 3. Most failed subjects (top 15)
$stmt = $pdo->prepare("
    SELECT s.failed_subjects_json
    FROM result_students s
    JOIN result_batches b ON s.batch_id = b.id
    $where AND s.failed_subjects_json IS NOT NULL
");
if (!empty($regulationYear)) {
    $stmt2 = $pdo->prepare($stmt->queryString);
    $stmt2->execute($params);
} else {
    $stmt2 = $pdo->query("
        SELECT s.failed_subjects_json
        FROM result_students s
        JOIN result_batches b ON s.batch_id = b.id
        WHERE b.status = 'completed' AND s.failed_subjects_json IS NOT NULL
    ");
}

$subjectFailCount = [];
$subjectDetails = $parser->getAllSubjects();
$subjectMap = [];
foreach ($subjectDetails as $sub) {
    $subjectMap[$sub['subject_code']] = $sub;
}

while ($row = $stmt2->fetch()) {
    $json = $row['failed_subjects_json'];
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        foreach ($decoded as $item) {
            $code = $item['code'];
            $ft = $item['fail_type'] ?? 'T';
            $key = $code . '_' . $ft;
            if (!isset($subjectFailCount[$key])) {
                $subjectFailCount[$key] = [
                    'code' => $code,
                    'fail_type' => $ft,
                    'count' => 0,
                    'subject_name' => isset($subjectMap[$code]) ? $subjectMap[$code]['subject_name'] : 'Unknown',
                    't_full' => isset($subjectMap[$code]) ? $subjectMap[$code]['t_full_name'] : 'Theory',
                    'p_full' => isset($subjectMap[$code]) ? $subjectMap[$code]['p_full_name'] : 'Practical',
                ];
            }
            $subjectFailCount[$key]['count']++;
        }
    }
}

usort($subjectFailCount, function($a, $b) { return $b['count'] - $a['count']; });
$topFailedSubjects = array_slice($subjectFailCount, 0, 15);

// 4. College-wise stats (top 20 by student count)
$collegeWhere = str_replace('b.status', 'b.status', $where);
$collegeParams = $params;

$stmt3 = $pdo->prepare("
    SELECT 
        s.college_code,
        s.college_name,
        COUNT(s.id) as total_students,
        SUM(CASE WHEN s.result_type = 'passed' THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN s.result_type IN ('referred', 'failed_4plus') THEN 1 ELSE 0 END) as failed,
        ROUND(AVG(CASE WHEN s.gpa IS NOT NULL THEN s.gpa END), 2) as avg_gpa,
        ROUND((SUM(CASE WHEN s.result_type = 'passed' THEN 1 ELSE 0 END) / COUNT(s.id)) * 100, 1) as pass_rate
    FROM result_students s
    JOIN result_batches b ON s.batch_id = b.id
    $collegeWhere
    GROUP BY s.college_code, s.college_name
    ORDER BY total_students DESC
    LIMIT 20
");
$stmt3->execute($collegeParams);
$collegeStats = $stmt3->fetchAll();

echo json_encode([
    'success' => true,
    'data' => [
        'overall' => $overall,
        'semester_stats' => $semesterStats,
        'top_failed_subjects' => $topFailedSubjects,
        'college_stats' => $collegeStats,
    ]
]);
