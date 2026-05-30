<?php
/**
 * Result Scraper - Admin Panel
 * Upload PDF and parse student results
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';
requireAdmin();

$parser = new ResultPdfParser($pdo);
$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));

// Handle PDF upload and parsing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload_pdf') {
        $examYear = clean($_POST['exam_year'] ?? '');
        $regulationYear = clean($_POST['regulation_year'] ?? '');
        $semester = clean($_POST['semester'] ?? '');
        $program = clean($_POST['program'] ?? 'Diploma In Engineering');

        if (empty($examYear) || empty($regulationYear) || empty($semester)) {
            setFlash('error', 'Please fill in all required fields.');
            header('Location: result-scraper.php');
            exit;
        }

        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            setFlash('error', 'Please select a valid PDF file.');
            header('Location: result-scraper.php');
            exit;
        }

        $file = $_FILES['pdf_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            setFlash('error', 'Only PDF files are allowed.');
            header('Location: result-scraper.php');
            exit;
        }

        // Save uploaded file
        $uploadDir = BASE_PATH . '/assets/uploads/results';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $newName = uniqid('result_') . '_' . time() . '.pdf';
        $targetPath = $uploadDir . '/' . $newName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            setFlash('error', 'Failed to upload the PDF file.');
            header('Location: result-scraper.php');
            exit;
        }

        try {
            $result = $parser->parseAndSave($targetPath, $examYear, $regulationYear, $semester, $program);
            setFlash('success', "Successfully parsed {$result['total_students']} student records from PDF.");
            header('Location: result-scraper.php?batch_id=' . $result['batch_id']);
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Error parsing PDF: ' . $e->getMessage());
            header('Location: result-scraper.php');
            exit;
        }
    }
}

// Get current batch if specified
$currentBatch = null;
if (isset($_GET['batch_id'])) {
    $currentBatch = $parser->getBatch((int)$_GET['batch_id']);
}

// Get all batches for history
$allBatches = $parser->getAllBatches();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Scraper — CST Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            <span>CST Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">MAIN</div>
                <a href="index.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
                <a href="../index.php" target="_blank">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    Website
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">RESULTS</div>
                <a href="result-scraper.php" class="active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M9 15l2 2 4-4"></path></svg>
                    PDF Scraper
                </a>
                <a href="result-data.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    Result Data
                </a>
                <a href="result-subjects.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Subject Codes
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">CONTENT</div>
                <a href="notices.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>Notices</a>
                <a href="teachers.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>Faculty</a>
                <a href="gallery.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>Gallery</a>
                <a href="resources.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>Resources</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">MANAGEMENT</div>
                <a href="categories.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path></svg>Categories</a>
                <a href="sponsors.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>Sponsors</a>
                <a href="credits.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>Credits</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">SETTINGS</div>
                <a href="settings.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Settings</a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Logout
            </a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <div class="topbar-title"><h2>Result PDF Scraper</h2></div>
            </div>
            <div class="topbar-actions">
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

            <!-- Upload Form -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Upload Result PDF</h3>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="result-scraper.php" enctype="multipart/form-data" id="scraperForm">
                        <input type="hidden" name="action" value="upload_pdf">
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:20px;">
                            <div class="form-group">
                                <label for="exam_year">Exam Year *</label>
                                <select name="exam_year" id="exam_year" required>
                                    <option value="">Select Year</option>
                                    <?php for ($y = 2026; $y >= 2015; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo (isset($_POST['exam_year']) && $_POST['exam_year'] == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="regulation_year">Regulation Year *</label>
                                <select name="regulation_year" id="regulation_year" required>
                                    <option value="">Select Regulation</option>
                                    <option value="2016" <?php echo (isset($_POST['regulation_year']) && $_POST['regulation_year'] == '2016') ? 'selected' : ''; ?>>2016</option>
                                    <option value="2022" <?php echo (isset($_POST['regulation_year']) && $_POST['regulation_year'] == '2022') ? 'selected' : ''; ?>>2022</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semester">Semester *</label>
                                <select name="semester" id="semester" required>
                                    <option value="">Select Semester</option>
                                    <?php
                                    $semesters = ['1st Semester','2nd Semester','3rd Semester','4th Semester','5th Semester','6th Semester','7th Semester','8th Semester'];
                                    foreach ($semesters as $sem):
                                    ?>
                                        <option value="<?php echo $sem; ?>" <?php echo (isset($_POST['semester']) && $_POST['semester'] == $sem) ? 'selected' : ''; ?>><?php echo $sem; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="program">Program</label>
                                <select name="program" id="program">
                                    <option value="Diploma In Engineering">Diploma In Engineering</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:20px;">
                            <label for="pdf_file">Upload PDF File *</label>
                            <div class="file-upload-area" id="dropZone" style="border:2px dashed #D1D5DB;border-radius:12px;padding:40px 20px;text-align:center;cursor:pointer;transition:all 0.2s;background:#F9FAFB;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="12" y1="18" x2="12" y2="12"></line>
                                    <polyline points="9 15 12 12 15 15"></polyline>
                                </svg>
                                <p style="margin:0 0 4px;color:#374151;font-weight:500;font-size:14px;">Click to upload or drag & drop</p>
                                <p style="margin:0;color:#9CA3AF;font-size:12px;">PDF files only, max 50MB</p>
                                <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" required style="display:none;">
                                <p id="fileName" style="margin:8px 0 0;color:#2563EB;font-weight:500;display:none;"></p>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="padding:12px 32px;font-size:15px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Upload & Parse PDF
                        </button>
                    </form>
                </div>
            </div>

            <!-- Current Batch Stats -->
            <?php if ($currentBatch): ?>
            <div class="stats-grid" style="margin-top:20px;">
                <div class="stat-card">
                    <div class="stat-icon" style="color:#2563EB;background:#EFF6FF;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($currentBatch['total_students']); ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#16A34A;background:#F0FDF4;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($currentBatch['total_passed']); ?></h3>
                        <p>Passed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#DC2626;background:#FEF2F2;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($currentBatch['total_failed']); ?></h3>
                        <p>Failed/Referred</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#9333EA;background:#FAF5FF;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $currentBatch['semester']; ?></h3>
                        <p><?php echo $currentBatch['exam_year']; ?> / <?php echo $currentBatch['regulation_year']; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upload History -->
            <div class="admin-card" style="margin-top:20px;">
                <div class="admin-card-header">
                    <h3>Upload History</h3>
                    <span style="color:#6B7280;font-size:13px;"><?php echo count($allBatches); ?> batch(es)</span>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($allBatches)): ?>
                        <p style="color:#6B7280;padding:16px 0;text-align:center;">No PDF uploads yet. Upload your first result PDF above.</p>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Exam Year</th>
                                <th>Regulation</th>
                                <th>Semester</th>
                                <th>Program</th>
                                <th>Students</th>
                                <th>Passed</th>
                                <th>Failed</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="table-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allBatches as $batch): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($batch['exam_year']); ?></td>
                                <td><?php echo htmlspecialchars($batch['regulation_year']); ?></td>
                                <td><?php echo htmlspecialchars($batch['semester']); ?></td>
                                <td><?php echo htmlspecialchars($batch['program']); ?></td>
                                <td><?php echo number_format($batch['total_students']); ?></td>
                                <td><span style="color:#16A34A;font-weight:600;"><?php echo number_format($batch['total_passed']); ?></span></td>
                                <td><span style="color:#DC2626;font-weight:600;"><?php echo number_format($batch['total_failed']); ?></span></td>
                                <td>
                                    <?php if ($batch['status'] === 'completed'): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php elseif ($batch['status'] === 'processing'): ?>
                                        <span class="badge" style="background:#FEF3C7;color:#92400E;">Processing</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($batch['created_at'])); ?></td>
                                <td class="table-actions">
                                    <a href="result-data.php?batch_id=<?php echo (int)$batch['id']; ?>" class="btn btn-sm btn-edit">View Data</a>
                                    <a href="result-scraper.php?action=delete&id=<?php echo (int)$batch['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this entire batch? This cannot be undone.');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Handle delete -->
    <?php if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])): ?>
        <?php
        $parser->deleteBatch((int)$_GET['id']);
        setFlash('success', 'Batch deleted successfully.');
        header('Location: result-scraper.php');
        exit;
        ?>
    <?php endif; ?>

    <!-- Mobile Sidebar Toggle -->
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

            // File upload drag & drop
            var dropZone = document.getElementById('dropZone');
            var fileInput = document.getElementById('pdf_file');
            var fileNameEl = document.getElementById('fileName');

            dropZone.addEventListener('click', function() { fileInput.click(); });
            dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.style.borderColor = '#2563EB'; dropZone.style.background = '#EFF6FF'; });
            dropZone.addEventListener('dragleave', function() { dropZone.style.borderColor = '#D1D5DB'; dropZone.style.background = '#F9FAFB'; });
            dropZone.addEventListener('drop', function(e) { e.preventDefault(); dropZone.style.borderColor = '#D1D5DB'; dropZone.style.background = '#F9FAFB'; if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; showFileName(e.dataTransfer.files[0]); } });
            fileInput.addEventListener('change', function() { if (fileInput.files.length) showFileName(fileInput.files[0]); });

            function showFileName(file) {
                fileNameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                fileNameEl.style.display = 'block';
            }
        })();
    </script>
</body>
</html>

// clean up pass
