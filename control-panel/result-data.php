<?php
/**
 * Result Data Management - Admin Panel
 * View, Edit, Delete scraped result data
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';
requireAdmin();

$parser = new ResultPdfParser($pdo);
$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit_student' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $roll = clean($_POST['roll']);
        $collegeCode = clean($_POST['college_code']);
        $collegeName = clean($_POST['college_name']);
        $gpa = !empty($_POST['gpa']) ? floatval($_POST['gpa']) : null;
        $resultType = clean($_POST['result_type']);
        $failedSubjectsJson = isset($_POST['failed_subjects_json']) ? $_POST['failed_subjects_json'] : null;

        $stmt = $pdo->prepare("
            UPDATE result_students SET 
                roll = ?, college_code = ?, college_name = ?, 
                gpa = ?, result_type = ?, failed_subjects_json = ?, failed_subjects_count = ?
            WHERE id = ?
        ");
        
        $failedCount = 0;
        if ($failedSubjectsJson) {
            $decoded = json_decode($failedSubjectsJson, true);
            $failedCount = is_array($decoded) ? count($decoded) : 0;
        }

        $stmt->execute([$roll, $collegeCode, $collegeName, $gpa, $resultType, $failedSubjectsJson, $failedCount, $id]);
        setFlash('success', 'Student record updated successfully.');
        header('Location: result-data.php?batch_id=' . (int)($_POST['batch_id'] ?? 0) . '&page=' . (int)($_POST['current_page'] ?? 1));
        exit;
    }

    if ($_POST['action'] === 'delete_selected' && isset($_POST['selected_ids'])) {
        $ids = array_map('intval', $_POST['selected_ids']);
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM result_students WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            setFlash('success', count($ids) . ' record(s) deleted successfully.');
        }
        header('Location: result-data.php?' . http_build_query($_GET));
        exit;
    }
}

// Handle single delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM result_students WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    setFlash('success', 'Record deleted successfully.');
    $params = $_GET;
    unset($params['action'], $params['id']);
    header('Location: result-data.php?' . http_build_query($params));
    exit;
}

// Get batch info
$batchId = isset($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
$currentBatch = $batchId ? $parser->getBatch($batchId) : null;

// If no batch specified, redirect to scraper
if (!$currentBatch && !$batchId) {
    header('Location: result-json-upload.php');
    exit;
}

// Get students with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$perPage = 50;

$studentsData = $batchId ? $parser->getStudentsByBatch($batchId, $page, $perPage, $search) : ['students' => [], 'total' => 0, 'page' => 1, 'per_page' => 50, 'total_pages' => 0];

// Get editing student
$editStudent = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM result_students WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editStudent = $stmt->fetch();
}

// Get all subjects for reference
$allSubjects = $parser->getAllSubjects();
$subjectMap = [];
foreach ($allSubjects as $sub) {
    $subjectMap[$sub['subject_code']] = $sub;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Data — অ্যাডমিন প্যানেল</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            <span>অ্যাডমিন প্যানেল</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">MAIN</div>
                <a href="index.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a>
                <a href="../index.php" target="_blank"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>Website</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">RESULTS</div>
                <a href="result-json-upload.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>Upload JSON</a>
                <a href="result-data.php<?php echo $batchId ? '?batch_id='.$batchId : ''; ?>" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>Result Data</a>
                <a href="result-subjects.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>Subject Codes</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">CONTENT</div>
                <a href="notices.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>Notices</a>
                <a href="teachers.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>Faculty</a>
                <a href="gallery.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>Gallery</a>
                <a href="resources.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>Resources</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">MANAGEMENT</div>
                <a href="categories.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path></svg>Categories</a>
                <a href="sponsors.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>Sponsors</a>
                <a href="credits.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>Credits</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">SETTINGS</div>
                <a href="settings.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Settings</a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <div class="topbar-title"><h2>Result Data Management</h2></div>
            </div>
            <div class="topbar-actions">
                <?php if ($currentBatch): ?>
                <a href="result-json-upload.php" class="btn btn-sm" style="background:#EFF6FF;color:#2563EB;border:1px solid #BFDBFE;">Back to Upload</a>
                <?php endif; ?>
                <div class="admin-user">
                    <div class="avatar"><?php echo htmlspecialchars($adminInitial); ?></div>
                    <span><?php echo htmlspecialchars($adminName); ?></span>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($flash['type']) && !empty($flash['message'])): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($currentBatch): ?>
            <!-- Batch Info -->
            <div style="background:#F0F9FF;border:1px solid #BAE6FD;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
                <span style="font-weight:600;color:#0369A1;"><?php echo htmlspecialchars($currentBatch['semester']); ?></span>
                <span style="color:#0C4A6E;">|</span>
                <span style="color:#475569;">Exam: <strong><?php echo $currentBatch['exam_year']; ?></strong></span>
                <span style="color:#0C4A6E;">|</span>
                <span style="color:#475569;">Regulation: <strong><?php echo $currentBatch['regulation_year']; ?></strong></span>
                <span style="color:#0C4A6E;">|</span>
                <span style="color:#475569;">Program: <strong><?php echo $currentBatch['program']; ?></strong></span>
                <span style="color:#0C4A6E;">|</span>
                <span style="color:#16A34A;font-weight:600;"><?php echo number_format($currentBatch['total_passed']); ?> Passed</span>
                <span style="color:#DC2626;font-weight:600;"><?php echo number_format($currentBatch['total_failed']); ?> Failed</span>
            </div>
            <?php endif; ?>

            <!-- Edit Modal -->
            <?php if ($editStudent): ?>
            <div class="admin-card" style="margin-bottom:20px;border:2px solid #2563EB;">
                <div class="admin-card-header" style="background:#EFF6FF;">
                    <h3>Edit Student Record #<?php echo (int)$editStudent['id']; ?> — Roll: <?php echo htmlspecialchars($editStudent['roll']); ?></h3>
                    <a href="result-data.php?batch_id=<?php echo $batchId; ?>&page=<?php echo $page; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-sm" style="background:#FEE2E2;color:#DC2626;">Cancel</a>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="result-data.php">
                        <input type="hidden" name="action" value="edit_student">
                        <input type="hidden" name="id" value="<?php echo (int)$editStudent['id']; ?>">
                        <input type="hidden" name="batch_id" value="<?php echo $batchId; ?>">
                        <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                            <div class="form-group">
                                <label>Roll Number</label>
                                <input type="text" name="roll" value="<?php echo htmlspecialchars($editStudent['roll']); ?>" required style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                            </div>
                            <div class="form-group">
                                <label>College Code</label>
                                <input type="text" name="college_code" value="<?php echo htmlspecialchars($editStudent['college_code']); ?>" required style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                            </div>
                            <div class="form-group">
                                <label>College Name</label>
                                <input type="text" name="college_name" value="<?php echo htmlspecialchars($editStudent['college_name']); ?>" required style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                            </div>
                            <div class="form-group">
                                <label>GPA (for passed)</label>
                                <input type="number" name="gpa" step="0.01" min="0" max="4" value="<?php echo $editStudent['gpa'] ?? ''; ?>" style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;" placeholder="Leave blank for failed">
                            </div>
                            <div class="form-group">
                                <label>Result Type</label>
                                <select name="result_type" style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                                    <option value="passed" <?php echo $editStudent['result_type'] === 'passed' ? 'selected' : ''; ?>>Passed</option>
                                    <option value="referred" <?php echo $editStudent['result_type'] === 'referred' ? 'selected' : ''; ?>>Referred (1-3 subjects)</option>
                                    <option value="failed_4plus" <?php echo $editStudent['result_type'] === 'failed_4plus' ? 'selected' : ''; ?>>Failed (4+ subjects)</option>
                                </select>
                            </div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <label>Failed Subjects JSON</label>
                                <textarea name="failed_subjects_json" rows="3" style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:13px;font-family:monospace;"><?php echo htmlspecialchars($editStudent['failed_subjects_json'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div style="margin-top:16px;display:flex;gap:12px;">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="result-data.php?batch_id=<?php echo $batchId; ?>&page=<?php echo $page; ?>" class="btn" style="background:#F3F4F6;color:#374151;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Search & Filter -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Student Records (<?php echo number_format($studentsData['total']); ?>)</h3>
                </div>
                <div class="admin-card-body">
                    <form method="GET" style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
                        <input type="hidden" name="batch_id" value="<?php echo $batchId; ?>">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by roll or college..." style="flex:1;min-width:200px;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search): ?>
                        <a href="result-data.php?batch_id=<?php echo $batchId; ?>" class="btn" style="background:#F3F4F6;color:#374151;">Clear</a>
                        <?php endif; ?>
                    </form>

                    <?php if (!empty($studentsData['students'])): ?>
                    <form method="POST" action="result-data.php?batch_id=<?php echo $batchId; ?>&page=<?php echo $page; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" id="bulkForm">
                        <input type="hidden" name="action" value="delete_selected">
                        <div style="margin-bottom:12px;display:flex;gap:8px;align-items:center;">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:#6B7280;">
                                <input type="checkbox" id="selectAll" style="cursor:pointer;"> Select All
                            </label>
                            <button type="submit" class="btn btn-sm btn-delete" id="bulkDeleteBtn" style="display:none;" onclick="return confirm('Delete selected records?');">Delete Selected</button>
                        </div>

                        <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="selectAllHeader" style="cursor:pointer;"></th>
                                    <th>Roll</th>
                                    <th>College</th>
                                    <th>Result</th>
                                    <th>GPA</th>
                                    <th>Failed Subjects</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studentsData['students'] as $student):
                                    $failedSubjects = $student['failed_subjects_json'] ? json_decode($student['failed_subjects_json'], true) : [];
                                ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_ids[]" value="<?php echo (int)$student['id']; ?>" class="row-checkbox" style="cursor:pointer;"></td>
                                    <td><strong><?php echo htmlspecialchars($student['roll']); ?></strong></td>
                                    <td>
                                        <div style="font-size:13px;">
                                            <span style="color:#6B7280;">[<?php echo htmlspecialchars($student['college_code']); ?>]</span>
                                            <?php echo htmlspecialchars(mb_substr($student['college_name'], 0, 40)); ?><?php echo mb_strlen($student['college_name']) > 40 ? '...' : ''; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($student['result_type'] === 'passed'): ?>
                                            <span class="badge badge-success">Passed</span>
                                        <?php elseif ($student['result_type'] === 'failed_4plus'): ?>
                                            <span class="badge badge-danger">Failed (4+)</span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#FEF3C7;color:#92400E;">Referred</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($student['gpa'] !== null): ?>
                                            <strong style="color:#16A34A;"><?php echo number_format($student['gpa'], 2); ?></strong>
                                        <?php else: ?>
                                            <span style="color:#DC2626;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($failedSubjects)): ?>
                                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                                <?php foreach ($failedSubjects as $fs):
                                                    // Bulletproof: extract clean code even if stored as "25911(T,P)"
                                                    $rawCode = trim($fs['code'] ?? '');
                                                    $rawType = strtoupper(trim($fs['fail_type'] ?? 'T'));

                                                    // If code itself contains (T,P) or (T) suffix, extract the pure code
                                                    if (preg_match('/^(\d{5})\s*\(([^)]+)\)\s*$/', $rawCode, $m)) {
                                                        $rawCode = $m[1];
                                                        $rawType = strtoupper(preg_replace('/[,\s]+/', '', $m[2]));
                                                    }

                                                    // Normalize fail_type: remove commas/spaces (e.g., "T,P" -> "TP")
                                                    $failType = strtoupper(preg_replace('/[,\s]+/', '', $rawType));

                                                    $subInfo = isset($subjectMap[$rawCode]) ? $subjectMap[$rawCode] : null;
                                                    $subName = $subInfo ? $subInfo['subject_name'] : $rawCode;
                                                    // Display fail_type as T,P for combined failures
                                                    $failTypeDisplay = ($failType === 'TP' || $failType === 'PT') ? 'T,P' : $failType;
                                                    // Build full form display
                                                    if ($failType === 'T') {
                                                        $fullFormText = $subName . ' Theory Fail';
                                                    } elseif ($failType === 'P') {
                                                        $fullFormText = $subName . ' Practical Fail';
                                                    } else {
                                                        $fullFormText = $subName . ' Theory & Practical Fail';
                                                    }
                                                ?>
                                                    <span style="display:inline-block;background:#FEF2F2;color:#991B1B;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:500;" title="<?php echo htmlspecialchars($fullFormText); ?>">
                                                        <?php echo htmlspecialchars($rawCode); ?> (<?php echo htmlspecialchars($failTypeDisplay); ?>)
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#9CA3AF;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="result-data.php?batch_id=<?php echo $batchId; ?>&page=<?php echo $page; ?>&edit_id=<?php echo (int)$student['id']; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="result-data.php?batch_id=<?php echo $batchId; ?>&page=<?php echo $page; ?>&action=delete&id=<?php echo (int)$student['id']; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this record?');">Del</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    </form>

                    <!-- Pagination -->
                    <?php if ($studentsData['total_pages'] > 1): ?>
                    <div style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:20px;">
                        <?php if ($page > 1): ?>
                        <a href="?batch_id=<?php echo $batchId; ?>&page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-sm" style="background:#F3F4F6;color:#374151;">Prev</a>
                        <?php endif; ?>
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($studentsData['total_pages'], $page + 2);
                        for ($p = $startPage; $p <= $endPage; $p++):
                        ?>
                            <a href="?batch_id=<?php echo $batchId; ?>&page=<?php echo $p; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-sm <?php echo $p === $page ? 'btn-primary' : ''; ?>" style="<?php echo $p !== $page ? 'background:#F3F4F6;color:#374151;' : ''; ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $studentsData['total_pages']): ?>
                        <a href="?batch_id=<?php echo $batchId; ?>&page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-sm" style="background:#F3F4F6;color:#374151;">Next</a>
                        <?php endif; ?>
                        <span style="color:#6B7280;font-size:13px;">Page <?php echo $page; ?> of <?php echo $studentsData['total_pages']; ?></span>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                        <p style="color:#6B7280;padding:20px 0;text-align:center;">No records found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        (function() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            var toggle = document.getElementById('mobileToggle');
            function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('active'); }
            function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
            if (toggle) toggle.addEventListener('click', function(e) { e.stopPropagation(); sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
            if (overlay) overlay.addEventListener('click', closeSidebar);
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });

            // Select all checkboxes
            var selectAll = document.getElementById('selectAll');
            var selectAllHeader = document.getElementById('selectAllHeader');
            var checkboxes = document.querySelectorAll('.row-checkbox');
            var bulkBtn = document.getElementById('bulkDeleteBtn');

            function updateBulkBtn() {
                var checked = document.querySelectorAll('.row-checkbox:checked').length;
                bulkBtn.style.display = checked > 0 ? 'inline-flex' : 'none';
            }

            if (selectAll) selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
                updateBulkBtn();
            });
            if (selectAllHeader) selectAllHeader.addEventListener('change', function() {
                checkboxes.forEach(function(cb) { cb.checked = selectAllHeader.checked; });
                updateBulkBtn();
            });
            checkboxes.forEach(function(cb) { cb.addEventListener('change', updateBulkBtn); });
        })();
    </script>
</body>
</html>
