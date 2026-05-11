<?php
/**
 * Result JSON Upload - Admin Panel (v3)
 * Two upload modes, each with two input methods:
 *   Mode 1: Fill regulation/exam year/semester manually + upload JSON file OR paste JSON code
 *   Mode 2: Upload complete JSON file OR paste complete JSON code (old format with metadata inside)
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';
requireAdmin();

$parser = new ResultPdfParser($pdo);
$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $parser->deleteBatch((int)$_GET['id']);
    setFlash('success', 'Batch deleted successfully.');
    header('Location: result-json-upload.php');
    exit;
}

// Get current batch if specified
$currentBatch = null;
if (isset($_GET['batch_id'])) {
    $currentBatch = $parser->getBatch((int)$_GET['batch_id']);
}

// Get all batches
$allBatches = $parser->getAllBatches();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Result — CST Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .mode-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 24px;
            background: #F1F5F9;
            border-radius: 10px;
            padding: 4px;
        }
        .mode-tab {
            flex: 1;
            padding: 12px 16px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #64748B;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }
        .mode-tab:hover { color: #334155; }
        .mode-tab.active {
            background: #fff;
            color: #2563EB;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .mode-panel { display: none; }
        .mode-panel.active { display: block; }

        .input-method-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 16px;
            background: #F8FAFC;
            border-radius: 8px;
            padding: 3px;
            border: 1px solid #E2E8F0;
        }
        .input-method-tab {
            flex: 1;
            padding: 8px 14px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #94A3B8;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }
        .input-method-tab:hover { color: #64748B; }
        .input-method-tab.active {
            background: #fff;
            color: #2563EB;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }
        .input-method-panel { display: none; }
        .input-method-panel.active { display: block; }

        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 6px;
        }
        .form-field label .req {
            color: #EF4444;
        }
        .form-field input,
        .form-field select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1E293B;
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-field input:focus,
        .form-field select:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .form-field .hint {
            font-size: 12px;
            color: #94A3B8;
            margin-top: 4px;
        }

        .drop-zone {
            border: 2px dashed #CBD5E1;
            border-radius: 12px;
            padding: 36px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #F8FAFC;
            margin-top: 20px;
        }
        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: #2563EB;
            background: #EFF6FF;
        }
        .drop-zone svg {
            margin: 0 auto 10px;
            display: block;
        }
        .drop-zone .dz-title {
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 4px;
        }
        .drop-zone .dz-sub {
            font-size: 12px;
            color: #94A3B8;
        }
        .drop-zone .dz-file {
            display: none;
            margin-top: 10px;
            padding: 10px 16px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 8px;
            font-size: 13px;
            color: #1E40AF;
            font-weight: 500;
        }
        .drop-zone .dz-file .dz-size {
            color: #3B82F6;
            font-weight: 400;
        }
        .drop-zone .dz-file .dz-remove {
            color: #EF4444;
            cursor: pointer;
            margin-left: 12px;
            font-weight: 600;
        }

        .json-code-area {
            margin-top: 20px;
        }
        .json-code-area textarea {
            width: 100%;
            min-height: 260px;
            max-height: 500px;
            padding: 16px;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            font-size: 13px;
            font-family: 'Courier New', Courier, monospace;
            color: #1E293B;
            background: #F8FAFC;
            outline: none;
            resize: vertical;
            line-height: 1.6;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .json-code-area textarea:focus {
            border-color: #2563EB;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .json-code-area textarea::placeholder {
            color: #94A3B8;
        }
        .json-code-area .code-hint {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            font-size: 12px;
            color: #94A3B8;
        }
        .json-code-area .code-hint .char-count {
            font-weight: 600;
            color: #64748B;
        }
        .json-code-area .code-hint .format-btn {
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .json-code-area .code-hint .format-btn:hover {
            background: #DBEAFE;
        }
        .json-code-area .code-hint .clean-btn {
            background: #FFF7ED;
            color: #EA580C;
            border: 1px solid #FED7AA;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .json-code-area .code-hint .clean-btn:hover {
            background: #FFEDD5;
        }
        .json-code-area .code-hint .sample-btn {
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .json-code-area .code-hint .sample-btn:hover {
            background: #DCFCE7;
        }

        .upload-progress {
            display: none;
            margin-top: 20px;
            padding: 16px 20px;
            border-radius: 10px;
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
        }
        .upload-progress.active { display: flex; align-items: center; gap: 10px; }
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid #BAE6FD;
            border-top-color: #2563EB;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .upload-progress span {
            font-size: 13px;
            color: #0369A1;
            font-weight: 500;
        }

        .result-toast {
            display: none;
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            max-width: 460px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }
        .result-toast.active { display: flex; align-items: flex-start; gap: 10px; }
        .result-toast.success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .result-toast.error { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 640px) {
            .form-row-3 { grid-template-columns: 1fr; }
            .form-row-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="admin-layout">

    <!-- Toast -->
    <div class="result-toast" id="toast"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            <span>CST Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section"><div class="nav-section-title">MAIN</div>
                <a href="index.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a>
                <a href="../index.php" target="_blank"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>Website</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">RESULTS</div>
                <a href="result-json-upload.php" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>Upload Result</a>
                <a href="result-data.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>Result Data</a>
                <a href="result-subjects.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>Subject Codes</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">CONTENT</div>
                <a href="notices.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>Notices</a>
                <a href="teachers.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>Faculty</a>
                <a href="gallery.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>Gallery</a>
                <a href="resources.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>Resources</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">MANAGEMENT</div>
                <a href="categories.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path></svg>Categories</a>
                <a href="sponsors.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>Sponsors</a>
                <a href="credits.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>Credits</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">SETTINGS</div>
                <a href="settings.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Settings</a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="logout.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-toggle" id="mobileToggle"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></button>
                <div class="topbar-title"><h2>Upload Result</h2></div>
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

            <!-- Upload Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Import Student Results</h3>
                    <span style="color:#64748B;font-size:13px;">JSON File or Paste Code</span>
                </div>
                <div class="admin-card-body">

                    <!-- Mode Tabs -->
                    <div class="mode-tabs">
                        <button class="mode-tab active" onclick="switchMode('manual')" id="tabManual">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Manual Entry + JSON
                        </button>
                        <button class="mode-tab" onclick="switchMode('auto')" id="tabAuto">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            Complete JSON
                        </button>
                    </div>

                    <!-- ============================================ -->
                    <!-- MODE 1: Manual Fields + JSON (File or Code) -->
                    <!-- ============================================ -->
                    <div class="mode-panel active" id="panelManual">
                        <form id="manualForm" enctype="multipart/form-data">
                            <div class="form-row-3" style="margin-bottom:16px;">
                                <div class="form-field">
                                    <label>Regulation Year <span class="req">*</span></label>
                                    <select name="regulation_year" required>
                                        <option value="">-- Select --</option>
                                        <?php for ($y = 2016; $y <= 2030; $y++): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label>Exam Year <span class="req">*</span></label>
                                    <select name="exam_year" required>
                                        <option value="">-- Select --</option>
                                        <?php for ($y = 2015; $y <= 2030; $y++): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label>Semester <span class="req">*</span></label>
                                    <select name="semester" required>
                                        <option value="">-- Select --</option>
                                        <option value="1st Semester">1st Semester</option>
                                        <option value="2nd Semester">2nd Semester</option>
                                        <option value="3rd Semester">3rd Semester</option>
                                        <option value="4th Semester">4th Semester</option>
                                        <option value="5th Semester">5th Semester</option>
                                        <option value="6th Semester">6th Semester</option>
                                        <option value="7th Semester">7th Semester</option>
                                        <option value="8th Semester">8th Semester</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row-2" style="margin-bottom:0;">
                                <div class="form-field">
                                    <label>Program</label>
                                    <input type="text" name="program" value="Diploma In Engineering" placeholder="e.g. Diploma In Engineering">
                                </div>
                                <div class="form-field" style="display:flex;align-items:flex-end;">
                                    <div class="hint" style="line-height:1.5;">
                                        Your JSON should contain student data with roll, institute, institute_code, status, cgpa, and failed_subjects fields. Also supports college_name, college_code, result_type, gpa format.
                                    </div>
                                </div>
                            </div>

                            <!-- Input Method Tabs: File Upload vs Paste Code -->
                            <div class="input-method-tabs" style="margin-top:20px;">
                                <button type="button" class="input-method-tab active" onclick="switchInputMethod('manual', 'file')" id="manualTabFile">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    Upload JSON File
                                </button>
                                <button type="button" class="input-method-tab" onclick="switchInputMethod('manual', 'code')" id="manualTabCode">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                    Paste JSON Code
                                </button>
                            </div>

                            <!-- Manual Mode: File Upload Panel -->
                            <div class="input-method-panel active" id="manualPanelFile">
                                <div class="drop-zone" id="dropZone1" onclick="document.getElementById('file1').click();">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <p class="dz-title">Click to select or drag & drop JSON file</p>
                                    <p class="dz-sub">Only .json files &bull; Max 50MB</p>
                                    <input type="file" name="json_file" id="file1" accept=".json" style="display:none;">
                                    <div class="dz-file" id="fileInfo1"></div>
                                </div>
                            </div>

                            <!-- Manual Mode: Paste Code Panel -->
                            <div class="input-method-panel" id="manualPanelCode">
                                <div class="json-code-area">
                                    <textarea name="json_code" id="jsonCode1" placeholder='Paste your JSON student data here...

Example (object format with roll as key):
{
  "200013": {
    "roll": "200013",
    "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
    "institute_code": "12053",
    "status": "referred",
    "cgpa": null,
    "failed_subjects": [
      "25913(T)",
      "26711(T)"
    ],
    "total_failed": 2
  },
  "200010": {
    "roll": "200010",
    "institute": "Dhaka Polytechnic Institute, Dhaka",
    "institute_code": "49166",
    "status": "passed",
    "cgpa": 3.52,
    "failed_subjects": [],
    "total_failed": 0
  }
}

Or (with "students" array):
{
  "students": [
    {
      "roll": "200010",
      "institute": "Dhaka Polytechnic Institute",
      "institute_code": "49166",
      "status": "passed",
      "cgpa": 3.52,
      "failed_subjects": [],
      "total_failed": 0
    }
  ]
}'></textarea>
                                    <div class="code-hint">
                                        <span class="char-count" id="charCount1">0 characters</span>
                                        <div style="display:flex;gap:6px;">
                                            <button type="button" class="sample-btn" onclick="loadSampleManual()">Load Sample</button>
                                            <button type="button" class="clean-btn" onclick="cleanAndFixCode('jsonCode1')">Clean & Fix</button>
                                            <button type="button" class="format-btn" onclick="formatCode('jsonCode1')">Format JSON</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top:20px;display:flex;gap:12px;">
                                <button type="submit" class="btn btn-primary" id="submitBtn1" style="padding:12px 32px;font-size:15px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    Upload & Import
                                </button>
                            </div>
                        </form>

                        <div class="upload-progress" id="progress1">
                            <div class="spinner"></div>
                            <span>Importing data... please wait for large files.</span>
                        </div>
                    </div>

                    <!-- ================================================ -->
                    <!-- MODE 2: Complete JSON (File or Code) -->
                    <!-- ================================================ -->
                    <div class="mode-panel" id="panelAuto">
                        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:16px 20px;margin-bottom:20px;">
                            <p style="font-size:13px;color:#475569;line-height:1.6;">
                                Upload or paste a complete JSON file that already contains <strong>exam_year</strong>, <strong>regulation_year</strong>, <strong>semester</strong>, and <strong>students</strong> array. The system will read everything automatically.
                            </p>
                        </div>
                        <form id="autoForm" enctype="multipart/form-data">

                            <!-- Input Method Tabs: File Upload vs Paste Code -->
                            <div class="input-method-tabs">
                                <button type="button" class="input-method-tab active" onclick="switchInputMethod('auto', 'file')" id="autoTabFile">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    Upload JSON File
                                </button>
                                <button type="button" class="input-method-tab" onclick="switchInputMethod('auto', 'code')" id="autoTabCode">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                    Paste JSON Code
                                </button>
                            </div>

                            <!-- Auto Mode: File Upload Panel -->
                            <div class="input-method-panel active" id="autoPanelFile">
                                <div class="drop-zone" id="dropZone2" onclick="document.getElementById('file2').click();">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    <p class="dz-title">Click to select or drag & drop complete JSON file</p>
                                    <p class="dz-sub">Must contain exam_year, regulation_year, semester, students[]</p>
                                    <input type="file" name="json_file" id="file2" accept=".json" style="display:none;">
                                    <div class="dz-file" id="fileInfo2"></div>
                                </div>
                            </div>

                            <!-- Auto Mode: Paste Code Panel -->
                            <div class="input-method-panel" id="autoPanelCode">
                                <div class="json-code-area">
                                    <textarea name="json_code" id="jsonCode2" placeholder='Paste your complete JSON data here...

Must include exam_year, regulation_year, semester, and students array:

{
  "exam_year": "2022",
  "regulation_year": "2016",
  "semester": "1st Semester",
  "program": "Diploma In Engineering",
  "students": [
    {
      "roll": "200010",
      "institute_code": "49166",
      "institute": "Dhaka Polytechnic Institute, Dhaka",
      "cgpa": 3.52,
      "status": "passed",
      "failed_subjects": [],
      "total_failed": 0
    },
    {
      "roll": "200013",
      "institute_code": "12053",
      "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
      "cgpa": null,
      "status": "referred",
      "failed_subjects": [
        "25913(T)",
        "26711(T)"
      ],
      "total_failed": 2
    }
  ]
}'></textarea>
                                    <div class="code-hint">
                                        <span class="char-count" id="charCount2">0 characters</span>
                                        <div style="display:flex;gap:6px;">
                                            <button type="button" class="sample-btn" onclick="loadSampleAuto()">Load Sample</button>
                                            <button type="button" class="clean-btn" onclick="cleanAndFixCode('jsonCode2')">Clean & Fix</button>
                                            <button type="button" class="format-btn" onclick="formatCode('jsonCode2')">Format JSON</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top:20px;">
                                <button type="submit" class="btn btn-primary" id="submitBtn2" style="padding:12px 32px;font-size:15px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    Upload Complete JSON
                                </button>
                            </div>
                        </form>

                        <div class="upload-progress" id="progress2">
                            <div class="spinner"></div>
                            <span>Importing data... please wait for large files.</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Batch Stats (if viewing a batch) -->
            <?php if ($currentBatch): ?>
            <div class="stats-grid" style="margin-top:20px;">
                <div class="stat-card">
                    <div class="stat-icon" style="color:#2563EB;background:#EFF6FF;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg></div>
                    <div class="stat-info"><h3><?php echo number_format($currentBatch['total_students']); ?></h3><p>Total Students</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#16A34A;background:#F0FDF4;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>
                    <div class="stat-info"><h3><?php echo number_format($currentBatch['total_passed']); ?></h3><p>Passed</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#DC2626;background:#FEF2F2;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></div>
                    <div class="stat-info"><h3><?php echo number_format($currentBatch['total_failed']); ?></h3><p>Failed/Referred</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#9333EA;background:#FAF5FF;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg></div>
                    <div class="stat-info"><h3><?php echo htmlspecialchars($currentBatch['semester']); ?></h3><p><?php echo htmlspecialchars($currentBatch['exam_year']); ?> / Reg: <?php echo htmlspecialchars($currentBatch['regulation_year']); ?></p></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upload History -->
            <div class="admin-card" style="margin-top:20px;">
                <div class="admin-card-header">
                    <h3>Upload History</h3>
                    <span style="color:#6B7280;font-size:13px;"><?php echo count($allBatches); ?> batch(es)</span>
                </div>
                <div class="admin-card-body" style="padding:0;">
                    <?php if (empty($allBatches)): ?>
                        <div style="padding:48px 24px;text-align:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" style="margin:0 auto 12px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <p style="color:#94A3B8;font-size:14px;">No data uploaded yet. Upload your first JSON file above.</p>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Regulation</th>
                                    <th>Exam Year</th>
                                    <th>Semester</th>
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
                                    <td><strong><?php echo htmlspecialchars($batch['regulation_year']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($batch['exam_year']); ?></td>
                                    <td><?php echo htmlspecialchars($batch['semester']); ?></td>
                                    <td><?php echo number_format($batch['total_students']); ?></td>
                                    <td><span style="color:#16A34A;font-weight:600;"><?php echo number_format($batch['total_passed']); ?></span></td>
                                    <td><span style="color:#DC2626;font-weight:600;"><?php echo number_format($batch['total_failed']); ?></span></td>
                                    <td>
                                        <?php if ($batch['status'] === 'completed'): ?>
                                            <span class="badge badge-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($batch['created_at'])); ?></td>
                                    <td class="table-actions">
                                        <a href="result-data.php?batch_id=<?php echo (int)$batch['id']; ?>" class="btn btn-sm btn-edit">View</a>
                                        <a href="result-json-upload.php?action=delete&id=<?php echo (int)$batch['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this batch and all student data?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    (function() {
        // ─── Sidebar ───
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var toggle = document.getElementById('mobileToggle');
        function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('active'); }
        function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
        if (toggle) toggle.addEventListener('click', function(e) { e.stopPropagation(); sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
        if (overlay) overlay.addEventListener('click', closeSidebar);

        // ─── Track current input method for each mode ───
        var currentInputMethod = { manual: 'file', auto: 'file' };

        // ─── Mode Switch ───
        window.switchMode = function(mode) {
            document.getElementById('tabManual').classList.toggle('active', mode === 'manual');
            document.getElementById('tabAuto').classList.toggle('active', mode === 'auto');
            document.getElementById('panelManual').classList.toggle('active', mode === 'manual');
            document.getElementById('panelAuto').classList.toggle('active', mode === 'auto');
        };

        // ─── Input Method Switch (File vs Code) ───
        window.switchInputMethod = function(mode, method) {
            currentInputMethod[mode] = method;
            var prefix = mode; // 'manual' or 'auto'
            document.getElementById(prefix + 'TabFile').classList.toggle('active', method === 'file');
            document.getElementById(prefix + 'TabCode').classList.toggle('active', method === 'code');
            document.getElementById(prefix + 'PanelFile').classList.toggle('active', method === 'file');
            document.getElementById(prefix + 'PanelCode').classList.toggle('active', method === 'code');
        };

        // ─── File Drop Zones ───
        setupDropZone('dropZone1', 'file1', 'fileInfo1');
        setupDropZone('dropZone2', 'file2', 'fileInfo2');

        function setupDropZone(zoneId, inputId, infoId) {
            var zone = document.getElementById(zoneId);
            var input = document.getElementById(inputId);
            var info = document.getElementById(infoId);
            if (!zone || !input) return;

            zone.addEventListener('click', function(e) {
                if (e.target.closest('.dz-remove')) return;
                input.click();
            });
            zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
            zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                zone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    showFileInfo(e.dataTransfer.files[0], info, zone);
                }
            });
            input.addEventListener('change', function() {
                if (input.files.length) showFileInfo(input.files[0], info, zone);
            });
        }

        function showFileInfo(file, infoEl, zone) {
            var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
            infoEl.innerHTML = file.name + ' <span class="dz-size">(' + sizeMB + ' MB)</span> <span class="dz-remove" onclick="clearFile(event, \'' + zone.id + '\', \'' + infoEl.id + '\')">&times; Remove</span>';
            infoEl.style.display = 'block';
            zone.style.borderColor = '#2563EB';
            zone.style.background = '#EFF6FF';
        }

        window.clearFile = function(e, zoneId, infoId) {
            e.stopPropagation();
            var zone = document.getElementById(zoneId);
            var info = document.getElementById(infoId);
            var input = zone.querySelector('input[type="file"]');
            if (input) input.value = '';
            info.style.display = 'none';
            info.innerHTML = '';
            zone.style.borderColor = '#CBD5E1';
            zone.style.background = '#F8FAFC';
        };

        // ─── Character counter for textareas ───
        var code1 = document.getElementById('jsonCode1');
        var code2 = document.getElementById('jsonCode2');
        var count1 = document.getElementById('charCount1');
        var count2 = document.getElementById('charCount2');

        if (code1) code1.addEventListener('input', function() {
            count1.textContent = this.value.length.toLocaleString() + ' characters';
        });
        if (code2) code2.addEventListener('input', function() {
            count2.textContent = this.value.length.toLocaleString() + ' characters';
        });

        // ─── JSON Cleanup Function (auto-fix common issues) ───
        function cleanupJsonString(str) {
            // Remove BOM
            if (str.charCodeAt(0) === 0xFEFF) str = str.substring(1);
            str = str.replace(/\uFEFF/g, '');
            // Remove single-line comments
            str = str.replace(/^\s*\/\/.*$/gm, '');
            // Remove multi-line comments
            str = str.replace(/\/\*[\s\S]*?\*\//g, '');
            // Remove trailing commas before } or ]
            str = str.replace(/,\s*([\]}])/g, '$1');
            // Remove trailing commas before }/] with newlines
            str = str.replace(/,\s*\n\s*([\]}])/g, '$1');
            // Remove non-printable chars (keep normal whitespace)
            str = str.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
            // Normalize line endings
            str = str.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            return str.trim();
        }

        // ─── Clean & Fix JSON button ───
        window.cleanAndFixCode = function(textareaId) {
            var ta = document.getElementById(textareaId);
            if (!ta || !ta.value.trim()) { showToast('Paste JSON code first.', 'error'); return; }
            var cleaned = cleanupJsonString(ta.value);
            // Try to parse
            try {
                var parsed = JSON.parse(cleaned);
                ta.value = JSON.stringify(parsed, null, 2);
                var countEl = textareaId === 'jsonCode1' ? count1 : count2;
                if (countEl) countEl.textContent = ta.value.length.toLocaleString() + ' characters';
                showToast('JSON cleaned and formatted successfully!', 'success');
            } catch(err) {
                // Even if still invalid after cleanup, show the cleaned version
                ta.value = cleaned;
                var countEl = textareaId === 'jsonCode1' ? count1 : count2;
                if (countEl) countEl.textContent = ta.value.length.toLocaleString() + ' characters';
                showToast('Auto-cleaned but JSON still has issues. Check line ' + err.message.split('line ')[1] + '.', 'error');
            }
        };

        // ─── Format JSON button (with auto-cleanup) ───
        window.formatCode = function(textareaId) {
            var ta = document.getElementById(textareaId);
            if (!ta || !ta.value.trim()) { showToast('Paste JSON code first.', 'error'); return; }
            // Auto-clean first
            var cleaned = cleanupJsonString(ta.value);
            try {
                var parsed = JSON.parse(cleaned);
                ta.value = JSON.stringify(parsed, null, 2);
                var countEl = textareaId === 'jsonCode1' ? count1 : count2;
                if (countEl) countEl.textContent = ta.value.length.toLocaleString() + ' characters';
                showToast('JSON formatted successfully.', 'success');
            } catch(err) {
                showToast('Invalid JSON: ' + err.message, 'error');
            }
        };

        // ─── Load Sample buttons ───
        window.loadSampleManual = function() {
            var sample = {
                "200010": {
                    "roll": "200010",
                    "institute": "Dhaka Polytechnic Institute, Dhaka",
                    "institute_code": "49166",
                    "status": "passed",
                    "cgpa": 3.52,
                    "failed_subjects": [],
                    "total_failed": 0
                },
                "200013": {
                    "roll": "200013",
                    "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
                    "institute_code": "12053",
                    "status": "referred",
                    "cgpa": null,
                    "failed_subjects": [
                        "25913(T)",
                        "26711(T)"
                    ],
                    "total_failed": 2
                },
                "200015": {
                    "roll": "200015",
                    "institute": "Dhaka Polytechnic Institute, Dhaka",
                    "institute_code": "49166",
                    "status": "passed",
                    "cgpa": 2.81,
                    "failed_subjects": [],
                    "total_failed": 0
                }
            };
            document.getElementById('jsonCode1').value = JSON.stringify(sample, null, 2);
            count1.textContent = document.getElementById('jsonCode1').value.length.toLocaleString() + ' characters';
            showToast('Sample data loaded. Fill the fields above and click Upload & Import.', 'success');
        };

        window.loadSampleAuto = function() {
            var sample = {
                "exam_year": "2022",
                "regulation_year": "2016",
                "semester": "1st Semester",
                "program": "Diploma In Engineering",
                "students": [
                    {
                        "roll": "200010",
                        "institute_code": "49166",
                        "institute": "Dhaka Polytechnic Institute, Dhaka",
                        "cgpa": 3.52,
                        "status": "passed",
                        "failed_subjects": [],
                        "total_failed": 0
                    },
                    {
                        "roll": "200013",
                        "institute_code": "12053",
                        "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
                        "cgpa": null,
                        "status": "referred",
                        "failed_subjects": [
                            "25913(T)",
                            "26711(T)"
                        ],
                        "total_failed": 2
                    },
                    {
                        "roll": "200020",
                        "institute_code": "49265",
                        "institute": "Chittagong Polytechnic Institute, Chittagong",
                        "cgpa": 3.98,
                        "status": "passed",
                        "failed_subjects": [],
                        "total_failed": 0
                    }
                ]
            };
            document.getElementById('jsonCode2').value = JSON.stringify(sample, null, 2);
            count2.textContent = document.getElementById('jsonCode2').value.length.toLocaleString() + ' characters';
            showToast('Sample complete JSON loaded. Click Upload Complete JSON.', 'success');
        };

        // ─── AJAX Upload (Mode 1: Manual) ───
        document.getElementById('manualForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var method = currentInputMethod.manual;
            var regYear = form.querySelector('[name="regulation_year"]').value;
            var examYear = form.querySelector('[name="exam_year"]').value;
            var semester = form.querySelector('[name="semester"]').value;
            if (!regYear || !examYear || !semester) { showToast('Please fill in Regulation Year, Exam Year, and Semester.', 'error'); return; }

            if (method === 'file') {
                var input = document.getElementById('file1');
                if (!input.files.length) { showToast('Please select a JSON file or switch to Paste Code tab.', 'error'); return; }
                uploadForm(form, 'submitBtn1', 'progress1');
            } else {
                // Code paste mode
                var rawCode = document.getElementById('jsonCode1').value.trim();
                if (!rawCode) { showToast('Please paste JSON code or switch to Upload File tab.', 'error'); return; }
                // Auto-cleanup JSON before validation
                var cleanedCode = cleanupJsonString(rawCode);
                // Validate JSON after cleanup
                try { JSON.parse(cleanedCode); } catch(err) {
                    showToast('JSON has syntax errors. Click "Clean & Fix" button to auto-fix, or check your data. Error: ' + err.message, 'error');
                    return;
                }
                // Put cleaned code back in textarea
                document.getElementById('jsonCode1').value = cleanedCode;
                // Build FormData with cleaned code
                var fd = new FormData();
                fd.append('regulation_year', regYear);
                fd.append('exam_year', examYear);
                fd.append('semester', semester);
                fd.append('program', form.querySelector('[name="program"]').value || 'Diploma In Engineering');
                fd.append('json_code', cleanedCode);
                uploadFormData(fd, 'submitBtn1', 'progress1');
            }
        });

        // ─── AJAX Upload (Mode 2: Auto) ───
        document.getElementById('autoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var method = currentInputMethod.auto;

            if (method === 'file') {
                var input = document.getElementById('file2');
                if (!input.files.length) { showToast('Please select a JSON file or switch to Paste Code tab.', 'error'); return; }
                uploadForm(this, 'submitBtn2', 'progress2');
            } else {
                var rawCode = document.getElementById('jsonCode2').value.trim();
                if (!rawCode) { showToast('Please paste JSON code or switch to Upload File tab.', 'error'); return; }
                // Auto-cleanup JSON before validation
                var cleanedCode = cleanupJsonString(rawCode);
                // Validate JSON after cleanup
                try { JSON.parse(cleanedCode); } catch(err) {
                    showToast('JSON has syntax errors. Click "Clean & Fix" button to auto-fix, or check your data. Error: ' + err.message, 'error');
                    return;
                }
                // Put cleaned code back in textarea
                document.getElementById('jsonCode2').value = cleanedCode;
                var fd = new FormData();
                fd.append('json_code', cleanedCode);
                uploadFormData(fd, 'submitBtn2', 'progress2');
            }
        });

        // ─── Upload form (file mode - uses FormData from form element) ───
        function uploadForm(form, btnId, progressId) {
            var fd = new FormData(form);
            uploadFormData(fd, btnId, progressId);
        }

        // ─── Upload FormData (works for both file and code mode) ───
        function uploadFormData(fd, btnId, progressId) {
            var btn = document.getElementById(btnId);
            var progress = document.getElementById(progressId);

            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
            progress.classList.add('active');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../api/upload-json.php', true);
            xhr.timeout = 600000; // 10 min timeout for large files

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                    progress.classList.remove('active');

                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            showToast(res.message, 'success');
                            setTimeout(function() { window.location.href = 'result-json-upload.php?batch_id=' + res.batch_id; }, 1500);
                        } else {
                            showToast('Error: ' + (res.error || 'Unknown error'), 'error');
                        }
                    } catch(err) {
                        if (xhr.status === 0) {
                            showToast('Network error or data too large. Try with smaller data or contact hosting.', 'error');
                        } else {
                            showToast('Server error. Please try again.', 'error');
                        }
                    }
                }
            };
            xhr.onerror = function() {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
                progress.classList.remove('active');
                showToast('Network error. Please check your connection.', 'error');
            };
            xhr.ontimeout = function() {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
                progress.classList.remove('active');
                showToast('Upload timed out. Data may be too large. Try again.', 'error');
            };
            xhr.send(fd);
        }

        // ─── Toast ───
        var toastTimer = null;
        function showToast(msg, type) {
            clearTimeout(toastTimer);
            var toast = document.getElementById('toast');
            toast.className = 'result-toast ' + type + ' active';
            var icon = type === 'success'
                ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
                : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            toast.innerHTML = icon + '<span>' + msg + '</span>';
            toastTimer = setTimeout(function() { toast.classList.remove('active'); }, 6000);
        }
    })();
    </script>
</body>
</html>
