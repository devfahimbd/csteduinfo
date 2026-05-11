<?php
/**
 * AJAX JSON Upload Endpoint (v4 - Bulletproof)
 * Supports 2 input methods:
 *   Method A: JSON file upload (json_file)
 *   Method B: JSON code paste (json_code)
 *
 * Supports 2 modes:
 *   Mode 1: Manual fields (regulation_year, exam_year, semester) + JSON with student data
 *   Mode 2: Complete JSON containing exam_year, regulation_year, semester, students[]
 *
 * Auto-detects format: if JSON has exam_year at top level → Mode 2, otherwise → Mode 1
 *
 * Student data format accepted:
 *   Object format: {"200013":{"roll":"200013","institute":"...","institute_code":"...","status":"referred","cgpa":null,"failed_subjects":["25913(T)"],"total_failed":2}}
 *   Array format: [{"roll":"200013","institute":"...","status":"passed","cgpa":3.5,"failed_subjects":["25913(T)"]}]
 *   Wrapped array: {"students": [{"roll":"200013",...}]}
 *
 * v4: Added robust JSON cleanup - auto-fixes trailing commas, BOM, comments, etc.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';

// Increase limits for large files
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', 600);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
    exit;
}

// ─── Get JSON content: either from file upload or from pasted code ───
$jsonContent = '';
$inputMethod = ''; // 'file' or 'code'

// Method B: JSON code pasted directly
if (!empty($_POST['json_code'])) {
    $jsonContent = trim($_POST['json_code']);
    $inputMethod = 'code';
}

// Method A: JSON file upload (fallback if no code provided)
if (empty($jsonContent)) {
    if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
        $errMsg = 'Please select a valid JSON file or paste JSON code.';
        if (isset($_FILES['json_file'])) {
            $errCodes = [
                UPLOAD_ERR_INI_SIZE => 'File too large. Server limit is ' . ini_get('upload_max_filesize') . '. Contact hosting to increase upload_max_filesize.',
                UPLOAD_ERR_FORM_SIZE => 'File too large. Form limit exceeded.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded. Please select a file or paste JSON code.',
            ];
            $errCode = $_FILES['json_file']['error'];
            if (isset($errCodes[$errCode])) {
                $errMsg = $errCodes[$errCode];
            }
        }
        echo json_encode(['success' => false, 'error' => $errMsg]);
        exit;
    }

    $file = $_FILES['json_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'json') {
        echo json_encode(['success' => false, 'error' => 'Only .json files are allowed.']);
        exit;
    }

    $jsonContent = file_get_contents($file['tmp_name']);
    $inputMethod = 'file';
}

if (!$jsonContent) {
    echo json_encode(['success' => false, 'error' => 'Could not read the JSON data. It may be empty.']);
    exit;
}

// ─── Clean JSON before parsing (auto-fix common issues) ───
$jsonContent = cleanupJson($jsonContent);

// ─── Parse JSON ───
$data = json_decode($jsonContent, true);
if ($data === null) {
    $jsonErr = json_last_error_msg();
    // Try one more time with aggressive cleanup
    $jsonContent2 = aggressiveCleanupJson($jsonContent);
    $data = json_decode($jsonContent2, true);
    if ($data === null) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON format: ' . $jsonErr . '. Please check your JSON data and try again.']);
        exit;
    }
}

// ─── Detect format and extract metadata ───
$examYear = '';
$regulationYear = '';
$semester = '';
$program = 'Diploma In Engineering';
$studentsArray = [];

if (isset($data['exam_year']) && isset($data['regulation_year']) && isset($data['semester']) && isset($data['students'])) {
    // MODE 2: Complete JSON (old format) - has metadata + students array
    $examYear = clean($data['exam_year']);
    $regulationYear = clean($data['regulation_year']);
    $semester = clean($data['semester']);
    $program = clean($data['program'] ?? 'Diploma In Engineering');
    $studentsArray = $data['students'];

    if (!is_array($studentsArray) || empty($studentsArray)) {
        echo json_encode(['success' => false, 'error' => 'JSON "students" array is empty.']);
        exit;
    }
} else {
    // MODE 1: New format - metadata from POST fields, students from JSON
    $examYear = clean($_POST['exam_year'] ?? '');
    $regulationYear = clean($_POST['regulation_year'] ?? '');
    $semester = clean($_POST['semester'] ?? '');
    $program = clean($_POST['program'] ?? 'Diploma In Engineering');

    if (empty($examYear) || empty($regulationYear) || empty($semester)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in Regulation Year, Exam Year, and Semester.']);
        exit;
    }

    // Convert students data - support multiple formats
    if (isset($data['students']) && is_array($data['students'])) {
        // Wrapped: {"students": [{...}, {...}]}
        $studentsArray = $data['students'];
    } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
        // Direct array: [{...}, {...}]
        $studentsArray = $data;
    } elseif (is_array($data) && !empty($data)) {
        // Object with roll numbers as keys: {"200013": {...}, "200010": {...}}
        $studentsArray = array_values($data);
    } else {
        echo json_encode(['success' => false, 'error' => 'Could not find student data in JSON. Expected roll numbers as keys or "students" array.']);
        exit;
    }
}

// ─── Convert students to standard format and import ───
try {
    $parser = new ResultPdfParser($pdo);

    // Normalize student data to the format importFromJson expects
    $normalizedStudents = [];
    foreach ($studentsArray as $s) {
        if (!is_array($s)) continue;

        $roll = clean($s['roll'] ?? '');
        if (empty($roll)) continue;

        // Map field names (support multiple formats)
        $collegeName = clean($s['college_name'] ?? $s['institute'] ?? $s['college'] ?? $s['institute_name'] ?? '');
        $collegeCode = clean($s['college_code'] ?? $s['institute_code'] ?? $s['code'] ?? '');
        $gpa = isset($s['gpa']) || isset($s['cgpa']) ? floatval($s['gpa'] ?? $s['cgpa']) : null;
        if ($gpa == 0 && ($s['gpa'] ?? $s['cgpa']) !== 0 && ($s['gpa'] ?? $s['cgpa']) !== '0') {
            $gpa = null;
        }

        // Map result_type / status
        $rawStatus = strtolower(clean($s['result_type'] ?? $s['status'] ?? 'passed'));
        if ($rawStatus === 'passed' || $rawStatus === 'p' || $rawStatus === 'pass') {
            $resultType = 'passed';
        } elseif ($rawStatus === 'failed' || $rawStatus === 'f') {
            $resultType = 'failed_4plus';
        } elseif ($rawStatus === 'referred' || $rawStatus === 'r' || $rawStatus === 'reffered' || $rawStatus === 'referral') {
            $resultType = 'referred';
        } else {
            $resultType = 'passed';
        }

        // Normalize failed_subjects
        $failedSubjects = [];
        if (!empty($s['failed_subjects']) && is_array($s['failed_subjects'])) {
            foreach ($s['failed_subjects'] as $fs) {
                if (is_string($fs) && preg_match('/^(\d{5})\(([TPtp]+)\)$/', trim($fs), $m)) {
                    $failedSubjects[] = ['code' => $m[1], 'fail_type' => strtoupper($m[2])];
                } elseif (is_array($fs) && isset($fs['code'])) {
                    $failedSubjects[] = [
                        'code' => clean($fs['code']),
                        'fail_type' => strtoupper(clean($fs['fail_type'] ?? 'T'))
                    ];
                } elseif (is_string($fs) && preg_match('/^(\d{5})$/', trim($fs))) {
                    $failedSubjects[] = ['code' => trim($fs), 'fail_type' => 'T'];
                } elseif (is_string($fs) && preg_match('/^(\d{5})\(([^)]+)\)$/', trim($fs), $m)) {
                    // Fallback: any format like 25913(X)
                    $failedSubjects[] = ['code' => $m[1], 'fail_type' => strtoupper($m[2])];
                }
            }
        }

        $normalizedStudents[] = [
            'roll' => $roll,
            'college_code' => $collegeCode,
            'college_name' => $collegeName,
            'gpa' => $gpa,
            'result_type' => $resultType,
            'failed_subjects' => $failedSubjects,
        ];
    }

    if (empty($normalizedStudents)) {
        echo json_encode(['success' => false, 'error' => 'No valid student records found in the JSON data. Make sure each student has a "roll" field.']);
        exit;
    }

    // Build the standard format and import
    $importData = [
        'exam_year' => $examYear,
        'regulation_year' => $regulationYear,
        'semester' => $semester,
        'program' => $program,
        'students' => $normalizedStudents,
    ];

    $result = $parser->importFromJson($importData);

    $methodLabel = $inputMethod === 'code' ? 'pasted JSON code' : 'uploaded JSON file';

    echo json_encode([
        'success' => true,
        'batch_id' => $result['batch_id'],
        'total_students' => $result['total_students'],
        'total_passed' => $result['total_passed'],
        'total_failed' => $result['total_failed'],
        'message' => "Successfully imported " . number_format($result['total_students']) . " students from {$methodLabel} (Regulation: {$regulationYear}, Exam: {$examYear}, {$semester})."
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Import failed: ' . $e->getMessage()]);
}

// ─── JSON Cleanup Function (auto-fix common issues) ───
function cleanupJson($json) {
    // Remove BOM if present
    $json = str_replace("\xEF\xBB\xBF", '', $json);
    
    // Remove UTF-8 BOM variant
    if (substr($json, 0, 3) === chr(0xEF) . chr(0xBB) . chr(0xBF)) {
        $json = substr($json, 3);
    }
    
    // Remove single-line comments (// ...)
    $json = preg_replace('#^\s*//.*$#m', '', $json);
    
    // Remove multi-line comments (/* ... */)
    $json = preg_replace('#/\*.*?\*/#s', '', $json);
    
    // Remove trailing commas before } or ]
    $json = preg_replace('/,\s*([\]}])/m', '$1', $json);
    
    // Remove trailing commas before closing bracket with possible whitespace/newlines
    $json = preg_replace('/,\s*\n\s*([\]}])/m', '$1', $json);
    
    // Fix escaped quotes that shouldn't be escaped
    // Not needed - JSON standard escaping is fine
    
    // Trim
    $json = trim($json);
    
    return $json;
}

// ─── Aggressive JSON Cleanup (last resort) ───
function aggressiveCleanupJson($json) {
    // Try to fix any remaining trailing commas more aggressively
    $json = preg_replace('/,\s*([\]}])/u', '$1', $json);
    
    // Remove any non-printable characters except whitespace
    $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $json);
    
    // Normalize line endings
    $json = str_replace(["\r\n", "\r"], "\n", $json);
    
    return trim($json);
}
