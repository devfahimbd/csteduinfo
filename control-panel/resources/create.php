<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$categories = getCategories('resource');

$semesters = ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'];
$types = ['note' => 'Note', 'assignment' => 'Assignment', 'question' => 'Question', 'syllabus' => 'Syllabus', 'routine' => 'Routine', 'other' => 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        try {
            $title = sanitizeInput($_POST['title'] ?? '');
            $slug = generateSlug($title, 'resources', 'slug');
            $type = in_array($_POST['type'] ?? '', array_keys($types)) ? $_POST['type'] : 'note';
            $semester = in_array($_POST['semester'] ?? '', $semesters) ? $_POST['semester'] : '';
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $description = sanitize($_POST['description'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

            $file = null;
            if (!empty($_FILES['file']['name'])) {
                $result = uploadFile($_FILES['file'], 'resources', ['pdf','doc','docx','xls','xlsx','ppt','pptx','zip','rar','jpg','jpeg','png','txt']);
                if ($result['success']) {
                    $file = $result['path'];
                } else {
                    setFlash('error', 'Failed to upload file.');
                    header('Location: create.php');
                    exit;
                }
            }

            if (!$file) {
                setFlash('error', 'Please upload a file.');
                header('Location: create.php');
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO resources (title, slug, type, semester, category_id, description, file, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $type, $semester, $categoryId, $description, $file, $status]);

            setFlash('success', 'Resource created successfully.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            setFlash('error', 'Failed to create resource.');
        }
    }
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource - Admin Panel</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #1f2937; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: #1a1a2e; color: #e5e7eb; position: fixed; top: 0; left: 0; bottom: 0; display: flex; flex-direction: column; z-index: 100; overflow-y: auto; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; font-weight: 600; font-size: 15px; }
        .sidebar-logo img { height: 32px; width: 32px; border-radius: 6px; object-fit: cover; }
        .sidebar-nav { padding: 12px 8px; flex: 1; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-radius: 6px; color: #9ca3af; text-decoration: none; font-size: 13.5px; font-weight: 450; transition: background 0.15s, color 0.15s; margin-bottom: 2px; }
        .nav-item:hover { background: rgba(255,255,255,0.06); color: #e5e7eb; }
        .nav-item.active { background: #2563eb; color: #fff; }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .nav-item-danger { margin-top: 12px; color: #f87171; }
        .nav-item-danger:hover { background: rgba(248,113,113,0.1); color: #f87171; }
        .admin-content { margin-left: 250px; flex: 1; min-height: 100vh; }
        .admin-header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 16px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .breadcrumb { font-size: 13px; color: #6b7280; }
        .breadcrumb strong { color: #1f2937; font-weight: 600; }
        .breadcrumb a { color: #2563eb; text-decoration: none; }
        .admin-user { font-size: 13px; color: #6b7280; }
        .admin-user strong { color: #1f2937; }
        .admin-body { padding: 28px; max-width: 820px; }

        .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }

        .panel { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px; overflow: hidden; }
        .panel-header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; }
        .panel-header h2 { font-size: 15px; font-weight: 600; color: #111827; }
        .panel-body { padding: 24px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px;
            font-size: 13px; color: #1f2937; background: #fff; transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group input[type="file"] { font-size: 13px; color: #374151; }
        .form-group .hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }

        .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background 0.2s; }
        .btn-secondary:hover { background: #f9fafb; }

        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-content { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="../../" class="sidebar-logo">
                <img src="<?php echo defined('SITE_URL') ? SITE_URL : ''; ?>/assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
                CST Admin
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="../dashboard.php" class="nav-item"><?php echo icon('dashboard', 18); ?> Dashboard</a>
            <a href="../notices/" class="nav-item"><?php echo icon('notice', 18); ?> Notices</a>
            <a href="../teachers/" class="nav-item"><?php echo icon('users', 18); ?> Faculty</a>
            <a href="index.php" class="nav-item active"><?php echo icon('download', 18); ?> Resources</a>
            <a href="../gallery/" class="nav-item"><?php echo icon('image', 18); ?> Gallery</a>
            <a href="../categories/" class="nav-item"><?php echo icon('category', 18); ?> Categories</a>
            <a href="../sponsors/" class="nav-item"><?php echo icon('award', 18); ?> Sponsors</a>
            <a href="../credits/" class="nav-item"><?php echo icon('star', 18); ?> Credits</a>
            <a href="../settings.php" class="nav-item"><?php echo icon('settings', 18); ?> Settings</a>
            <a href="../logout.php" class="nav-item nav-item-danger"><?php echo icon('logout', 18); ?> Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> &nbsp;/&nbsp; <a href="index.php">Resources</a> &nbsp;/&nbsp; <strong>Add Resource</strong></div>
            <div class="admin-user">Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
        </header>

        <div class="admin-body">
            <?php echo displayFlash(); ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="panel">
                    <div class="panel-header"><h2>Resource Details</h2></div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" required placeholder="Enter resource title">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Type *</label>
                                <select name="type">
                                    <?php foreach ($types as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester">
                                    <option value="">-- Select Semester --</option>
                                    <?php foreach ($semesters as $sem): ?>
                                        <option value="<?php echo $sem; ?>"><?php echo $sem; ?> Semester</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" placeholder="Brief description of the resource"></textarea>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header"><h2>File Upload</h2></div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Resource File *</label>
                            <input type="file" name="file" required>
                            <p class="hint">PDF, DOC, DOCX, XLS, PPT, ZIP, images, TXT</p>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="submit" class="btn-primary">Create Resource</button>
                    <a href="index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
