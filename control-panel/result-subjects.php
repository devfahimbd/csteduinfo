<?php
/**
 * Result Subject Codes Management - Admin Panel
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';
requireAdmin();

$parser = new ResultPdfParser($pdo);
$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_subject') {
        $code = clean($_POST['subject_code']);
        $name = clean($_POST['subject_name']);
        $tFull = clean($_POST['t_full_name']);
        $pFull = clean($_POST['p_full_name']);

        if (empty($code) || empty($name)) {
            setFlash('error', 'Subject code and name are required.');
        } else {
            $parser->saveSubject($code, $name, $tFull, $pFull);
            setFlash('success', "Subject {$code} saved successfully.");
        }
        header('Location: result-subjects.php');
        exit;
    }

    if ($_POST['action'] === 'update_subject' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $code = clean($_POST['subject_code']);
        $name = clean($_POST['subject_name']);
        $tFull = clean($_POST['t_full_name']);
        $pFull = clean($_POST['p_full_name']);
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $pdo->prepare("
            UPDATE result_subjects SET 
                subject_code = ?, subject_name = ?, t_full_name = ?, p_full_name = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$code, $name, $tFull, $pFull, $status, $id]);
        setFlash('success', 'Subject updated successfully.');
        header('Location: result-subjects.php');
        exit;
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $parser->deleteSubject((int)$_GET['id']);
    setFlash('success', 'Subject deleted successfully.');
    header('Location: result-subjects.php');
    exit;
}

$allSubjects = $parser->getAllSubjects();
$editSubject = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM result_subjects WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editSubject = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Codes — CST Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            <span>CST Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section"><div class="nav-section-title">MAIN</div>
                <a href="index.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a>
                <a href="../index.php" target="_blank"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>Website</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">RESULTS</div>
                <a href="result-scraper.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>PDF Scraper</a>
                <a href="result-data.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>Result Data</a>
                <a href="result-subjects.php" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>Subject Codes</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">CONTENT</div>
                <a href="notices.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>Notices</a>
                <a href="teachers.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>Faculty</a>
                <a href="gallery.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect></svg>Gallery</a>
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
                <div class="topbar-title"><h2>Subject Codes Management</h2></div>
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

            <!-- Add New Subject -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><?php echo $editSubject ? 'Edit Subject' : 'Add New Subject Code'; ?></h3>
                    <?php if ($editSubject): ?>
                    <a href="result-subjects.php" class="btn btn-sm" style="background:#FEE2E2;color:#DC2626;">Cancel Edit</a>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="result-subjects.php">
                        <input type="hidden" name="action" value="<?php echo $editSubject ? 'update_subject' : 'add_subject'; ?>">
                        <?php if ($editSubject): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$editSubject['id']; ?>">
                        <?php endif; ?>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div class="form-group">
                                <label>Subject Code *</label>
                                <input type="text" name="subject_code" value="<?php echo htmlspecialchars($editSubject['subject_code'] ?? ''); ?>" required placeholder="e.g., 25911" style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                            </div>
                            <div class="form-group">
                                <label>Subject Name *</label>
                                <input type="text" name="subject_name" value="<?php echo htmlspecialchars($editSubject['subject_name'] ?? ''); ?>" required placeholder="e.g., Engineering Drawing-1" style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                            </div>
                            <div class="form-group">
                                <label>T Full Name</label>
                                <input type="text" name="t_full_name" value="<?php echo htmlspecialchars($editSubject['t_full_name'] ?? 'Theory'); ?>" placeholder="Theory" style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                            </div>
                            <div class="form-group">
                                <label>P Full Name</label>
                                <input type="text" name="p_full_name" value="<?php echo htmlspecialchars($editSubject['p_full_name'] ?? 'Practical'); ?>" placeholder="Practical" style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;">
                            </div>
                        </div>
                        <?php if ($editSubject): ?>
                        <div style="margin-top:12px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="status" value="1" <?php echo $editSubject['status'] ? 'checked' : ''; ?> style="cursor:pointer;">
                                <span style="font-size:14px;">Active</span>
                            </label>
                        </div>
                        <?php endif; ?>
                        <div style="margin-top:16px;">
                            <button type="submit" class="btn btn-primary"><?php echo $editSubject ? 'Update Subject' : 'Add Subject'; ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Subject List -->
            <div class="admin-card" style="margin-top:20px;">
                <div class="admin-card-header">
                    <h3>All Subject Codes (<?php echo count($allSubjects); ?>)</h3>
                </div>
                <div class="admin-card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject Name</th>
                                <th>T Full Name</th>
                                <th>P Full Name</th>
                                <th>Status</th>
                                <th class="table-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allSubjects as $sub): ?>
                            <tr>
                                <td><strong style="color:#2563EB;font-family:monospace;font-size:14px;"><?php echo htmlspecialchars($sub['subject_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                                <td><span style="background:#DBEAFE;color:#1E40AF;padding:2px 10px;border-radius:6px;font-size:12px;font-weight:500;">T = <?php echo htmlspecialchars($sub['t_full_name']); ?></span></td>
                                <td><span style="background:#FCE7F3;color:#9D174D;padding:2px 10px;border-radius:6px;font-size:12px;font-weight:500;">P = <?php echo htmlspecialchars($sub['p_full_name']); ?></span></td>
                                <td>
                                    <?php if ($sub['status']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <a href="result-subjects.php?edit_id=<?php echo (int)$sub['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                    <a href="result-subjects.php?action=delete&id=<?php echo (int)$sub['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this subject code?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
        })();
    </script>
</body>
</html>
