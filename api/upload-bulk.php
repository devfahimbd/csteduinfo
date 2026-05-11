<?php
/**
 * Chunked Upload API for Student Results
 * 
 * Handles large dataset uploads by splitting into chunks.
 * Actions: init, chunk, finish, abort
 * 
 * Requires admin session. All inputs are cleaned and validated.
 */

// Increase limits for large uploads
ini_set('memory_limit', '1G');
ini_set('max_execution_time', '3600');

// Include dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

// Verify admin authentication
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required. Please log in as admin.']);
    exit;
}

// Get requested action
$action = clean($_POST['action'] ?? '');

if (empty($action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing "action" parameter.']);
    exit;
}

// Initialize parser
try {
    $parser = new ResultPdfParser($pdo);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to initialize result parser: ' . $e->getMessage()]);
    exit;
}

// ─── Helper: send JSON response and exit ───
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── Route actions ───
switch ($action) {

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // ACTION: init - Create a new processing batch
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    case 'init':
        $examYear      = clean($_POST['exam_year'] ?? '');
        $regulationYear = clean($_POST['regulation_year'] ?? '');
        $semester      = clean($_POST['semester'] ?? '');
        $program       = clean($_POST['program'] ?? 'Diploma In Engineering');
        $totalStudents = clean($_POST['total_students'] ?? '');

        // Validate required fields
        $requiredFields = [
            'exam_year'       => $examYear,
            'regulation_year' => $regulationYear,
            'semester'        => $semester,
            'total_students'  => $totalStudents,
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $value) {
            if ($value === '' || $value === null) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            sendResponse([
                'success' => false,
                'error'   => 'Missing required fields: ' . implode(', ', $missingFields)
            ], 400);
        }

        // Validate numeric fields
        if (!is_numeric($totalStudents) || intval($totalStudents) < 0) {
            sendResponse([
                'success' => false,
                'error'   => 'total_students must be a non-negative number.'
            ], 400);
        }

        $totalStudents = intval($totalStudents);

        try {
            $batchId = $parser->createEmptyBatch($examYear, $regulationYear, $semester, $program);

            sendResponse([
                'success'        => true,
                'batch_id'       => $batchId,
                'total_students' => $totalStudents,
            ]);
        } catch (Exception $e) {
            sendResponse([
                'success' => false,
                'error'   => 'Failed to create batch: ' . $e->getMessage()
            ], 500);
        }
        break;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // ACTION: chunk - Upload a chunk of students
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    case 'chunk':
        $batchId    = clean($_POST['batch_id'] ?? '');
        $chunkIndex = clean($_POST['chunk_index'] ?? '');
        $studentsRaw = $_POST['students'] ?? '';

        // Validate required fields
        if (empty($batchId)) {
            sendResponse(['success' => false, 'error' => 'Missing batch_id.'], 400);
        }
        if ($chunkIndex === '' || !is_numeric($chunkIndex)) {
            sendResponse(['success' => false, 'error' => 'Missing or invalid chunk_index.'], 400);
        }

        $batchId    = intval($batchId);
        $chunkIndex = intval($chunkIndex);

        // Decode students JSON
        if (empty($studentsRaw)) {
            sendResponse(['success' => false, 'error' => 'Missing students data.'], 400);
        }

        $students = json_decode($studentsRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendResponse([
                'success' => false,
                'error'   => 'Invalid JSON in students field: ' . json_last_error_msg()
            ], 400);
        }
        if (!is_array($students)) {
            sendResponse(['success' => false, 'error' => 'students must be a JSON array.'], 400);
        }
        if (empty($students)) {
            sendResponse([
                'success' => false,
                'error'   => 'students array is empty.',
                'chunk_index' => $chunkIndex
            ], 400);
        }

        // Verify batch exists and is in processing state
        try {
            $batch = $parser->getBatch($batchId);
            if (!$batch) {
                sendResponse(['success' => false, 'error' => 'Batch not found.'], 404);
            }
            if ($batch['status'] !== 'processing') {
                sendResponse([
                    'success' => false,
                    'error'   => 'Batch is not in processing state (current: ' . $batch['status'] . '). Cannot accept more chunks.'
                ], 400);
            }
        } catch (Exception $e) {
            sendResponse([
                'success' => false,
                'error'   => 'Failed to verify batch: ' . $e->getMessage()
            ], 500);
        }

        // Import the chunk
        try {
            $result = $parser->importChunk($batchId, $students);

            sendResponse([
                'success'     => true,
                'chunk_index' => $chunkIndex,
                'inserted'    => $result['inserted'],
                'errors'      => $result['errors'],
            ]);
        } catch (Exception $e) {
            sendResponse([
                'success'     => false,
                'chunk_index' => $chunkIndex,
                'error'       => 'Failed to import chunk: ' . $e->getMessage()
            ], 500);
        }
        break;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // ACTION: finish - Finalize batch and compute stats
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    case 'finish':
        $batchId = clean($_POST['batch_id'] ?? '');

        if (empty($batchId) || !is_numeric($batchId)) {
            sendResponse(['success' => false, 'error' => 'Missing or invalid batch_id.'], 400);
        }

        $batchId = intval($batchId);

        // Verify batch exists
        try {
            $batch = $parser->getBatch($batchId);
            if (!$batch) {
                sendResponse(['success' => false, 'error' => 'Batch not found.'], 404);
            }
            if ($batch['status'] === 'completed') {
                // Already completed — return existing stats
                sendResponse([
                    'success'        => true,
                    'batch_id'       => $batchId,
                    'total_students' => (int)$batch['total_students'],
                    'total_passed'   => (int)$batch['total_passed'],
                    'total_failed'   => (int)$batch['total_failed'],
                    'message'        => 'Batch was already completed.'
                ]);
            }
            if ($batch['status'] !== 'processing') {
                sendResponse([
                    'success' => false,
                    'error'   => 'Batch is not in processing state (current: ' . $batch['status'] . '). Cannot finalize.'
                ], 400);
            }
        } catch (Exception $e) {
            sendResponse([
                'success' => false,
                'error'   => 'Failed to verify batch: ' . $e->getMessage()
            ], 500);
        }

        // Finalize
        try {
            $result = $parser->finalizeBatch($batchId);

            sendResponse([
                'success'        => true,
                'batch_id'       => $result['batch_id'],
                'total_students' => $result['total_students'],
                'total_passed'   => $result['total_passed'],
                'total_failed'   => $result['total_failed'],
            ]);
        } catch (Exception $e) {
            sendResponse([
                'success' => false,
                'error'   => 'Failed to finalize batch: ' . $e->getMessage()
            ], 500);
        }
        break;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // ACTION: abort - Delete batch and all its students
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    case 'abort':
        $batchId = clean($_POST['batch_id'] ?? '');

        if (empty($batchId) || !is_numeric($batchId)) {
            sendResponse(['success' => false, 'error' => 'Missing or invalid batch_id.'], 400);
        }

        $batchId = intval($batchId);

        // Verify batch exists
        try {
            $batch = $parser->getBatch($batchId);
            if (!$batch) {
                sendResponse(['success' => false, 'error' => 'Batch not found.'], 404);
            }
        } catch (Exception $e) {
            sendResponse([
                'success' => false,
                'error'   => 'Failed to verify batch: ' . $e->getMessage()
            ], 500);
        }

        // Delete batch and all associated students
        try {
            $parser->deleteBatch($batchId);

            sendResponse([
                'success'  => true,
                'batch_id' => $batchId,
            ]);
        } catch (Exception $e) {
            sendResponse([
                'success' => false,
                'error'   => 'Failed to abort batch: ' . $e->getMessage()
            ], 500);
        }
        break;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UNKNOWN ACTION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    default:
        sendResponse([
            'success' => false,
            'error'   => 'Unknown action: "' . $action . '". Valid actions: init, chunk, finish, abort.'
        ], 400);
        break;
}
