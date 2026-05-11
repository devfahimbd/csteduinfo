<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teacher = false;
}

if (!$teacher) {
    setFlash('error', 'Faculty member not found.');
    header('Location: index.php');
    exit;
}

$categories = getCategories('teacher');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        try {
            $name = sanitizeInput($_POST['name'] ?? '');
            $slug = generateSlug($name, 'teachers', 'slug', $id);
            $designation = sanitizeInput($_POST['designation'] ?? '');
            $qualification = sanitizeInput($_POST['qualification'] ?? '');
            $bio = sanitize($_POST['bio'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $serial = (int)($_POST['serial'] ?? 0);
            $facebook = sanitizeInput($_POST['facebook'] ?? '');
            $linkedin = sanitizeInput($_POST['linkedin'] ?? '');

            $photo = $teacher['photo'];
            if (!empty($_FILES['photo']['name'])) {
                $result = uploadFile($_FILES['photo'], 'teachers', ['jpg','jpeg','png','webp','gif']);
                if ($result['success']) {
                    if (!empty($teacher['photo'])) deleteFile($teacher['photo']);
                    $photo = $result['path'];
                }
            }

            $stmt = $pdo->prepare("UPDATE teachers SET name=?, slug=?, designation=?, qualification=?, bio=?, email=?, phone=?, photo=?, category_id=?, serial=?, facebook=?, linkedin=? WHERE id=?");
            $stmt->execute([$name, $slug, $designation, $qualification, $bio, $email, $phone, $photo, $categoryId, $serial, $facebook, $linkedin, $id]);

            setFlash('success', 'Faculty member updated successfully.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            setFlash('error', 'Failed to update faculty member.');
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
    <title>Edit Faculty - Admin Panel</title>
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
        .form-group input[type="email"],
        .form-group input[type="url"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px;
            font-size: 13px; color: #1f2937; background: #fff; transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group input[type="file"] { font-size: 13px; color: #374151; }
        .form-group .hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }
        .current-file { font-size: 12px; color: #16a34a; margin-top: 4px; }

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
            <a href="index.php" class="nav-item active"><?php echo icon('users', 18); ?> Faculty</a>
            <a href="../resources/" class="nav-item"><?php echo icon('download', 18); ?> Resources</a>
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
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> &nbsp;/&nbsp; <a href="index.php">Faculty</a> &nbsp;/&nbsp; <strong>Edit Faculty</strong></div>
            <div class="admin-user">Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
        </header>

        <div class="admin-body">
            <?php echo displayFlash(); ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="panel">
                    <div class="panel-header"><h2>Personal Information</h2></div>
                    <div class="panel-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="name" required value="<?php echo htmlspecialchars($teacher['name']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Designation *</label>
                                <input type="text" name="designation" required value="<?php echo htmlspecialchars($teacher['designation']); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Qualification</label>
                                <input type="text" name="qualification" value="<?php echo htmlspecialchars($teacher['qualification'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Photo</label>
                                <input type="file" name="photo" accept="image/*">
                                <p class="hint">JPG, PNG, WebP. Leave empty to keep current.</p>
                                <?php if (!empty($teacher['photo'])): ?>
                                    <div class="current-file">Current: <?php echo htmlspecialchars(basename($teacher['photo'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Biography</label>
                            <textarea name="bio" rows="4"><?php echo htmlspecialchars($teacher['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header"><h2>Contact Information</h2></div>
                    <div class="panel-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Facebook URL</label>
                                <input type="url" name="facebook" value="<?php echo htmlspecialchars($teacher['facebook'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>LinkedIn URL</label>
                                <input type="url" name="linkedin" value="<?php echo htmlspecialchars($teacher['linkedin'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header"><h2>Organization</h2></div>
                    <div class="panel-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($teacher['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Serial Order</label>
                                <input type="number" name="serial" value="<?php echo (int)$teacher['serial']; ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="submit" class="btn-primary">Update Faculty Member</button>
                    <a href="index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
