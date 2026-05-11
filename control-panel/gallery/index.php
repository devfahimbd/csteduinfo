<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$categories = getCategories('gallery');

// Handle inline add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gallery'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

        if (!empty($_FILES['image']['name'])) {
            $result = uploadFile($_FILES['image'], 'gallery', ['jpg','jpeg','png','webp','gif']);
            if ($result['success']) {
                try {
                    $slug = createSlug($title) . '-' . time();
                    $stmt = $pdo->prepare("INSERT INTO gallery (title, slug, category_id, image) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $slug, $categoryId, $result['path']]);
                    setFlash('success', 'Image added successfully.');
                } catch (PDOException $e) {
                    setFlash('error', 'Failed to add image.');
                }
            } else {
                setFlash('error', 'Failed to upload image.');
            }
        } else {
            setFlash('error', 'Please select an image.');
        }
        header('Location: index.php');
        exit;
    }
}

// Fetch gallery images
try {
    $stmt = $pdo->query("SELECT g.*, c.name AS category_name FROM gallery g LEFT JOIN categories c ON g.category_id = c.id ORDER BY g.created_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $images = [];
}

$currentPage = 'gallery';
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Admin Panel</title>
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
        .admin-body { padding: 28px; }

        .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }

        .panel { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px; overflow: hidden; }
        .panel-header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; }
        .panel-header h2 { font-size: 15px; font-weight: 600; color: #111827; }
        .panel-body { padding: 24px; }

        .form-row { display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { margin-bottom: 0; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .form-group input[type="text"],
        .form-group select {
            padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px;
            font-size: 13px; color: #1f2937; background: #fff; transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-group input[type="file"] { font-size: 13px; color: #374151; }
        .form-group input[type="text"] { width: 220px; }
        .form-group select { width: 180px; }

        .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: background 0.2s; white-space: nowrap; }
        .btn-primary:hover { background: #1d4ed8; }

        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
        .gallery-item {
            background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden;
            position: relative; group: true;
        }
        .gallery-item img {
            width: 100%; height: 160px; object-fit: cover; display: block;
        }
        .gallery-item-info {
            padding: 10px 12px;
        }
        .gallery-item-info h4 { font-size: 13px; font-weight: 500; color: #111827; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gallery-item-info span { font-size: 11px; color: #9ca3af; }
        .gallery-item-actions {
            position: absolute; top: 6px; right: 6px;
        }
        .btn-delete-sm {
            width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.9); border: 1px solid #fecaca; border-radius: 4px;
            color: #dc2626; cursor: pointer; text-decoration: none; font-size: 16px; font-weight: 700;
            transition: background 0.15s;
        }
        .btn-delete-sm:hover { background: #fef2f2; }
        .empty-state { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: 13px; }

        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-content { margin-left: 0; }
            .form-row { flex-direction: column; align-items: stretch; }
            .form-group input[type="text"], .form-group select { width: 100%; }
            .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
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
            <a href="../resources/" class="nav-item"><?php echo icon('download', 18); ?> Resources</a>
            <a href="index.php" class="nav-item active"><?php echo icon('image', 18); ?> Gallery</a>
            <a href="../categories/" class="nav-item"><?php echo icon('category', 18); ?> Categories</a>
            <a href="../sponsors/" class="nav-item"><?php echo icon('award', 18); ?> Sponsors</a>
            <a href="../credits/" class="nav-item"><?php echo icon('star', 18); ?> Credits</a>
            <a href="../settings.php" class="nav-item"><?php echo icon('settings', 18); ?> Settings</a>
            <a href="../logout.php" class="nav-item nav-item-danger"><?php echo icon('logout', 18); ?> Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> &nbsp;/&nbsp; <strong>Gallery</strong></div>
            <div class="admin-user">Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
        </header>

        <div class="admin-body">
            <?php echo displayFlash(); ?>

            <div class="panel">
                <div class="panel-header"><h2>Add Image</h2></div>
                <div class="panel-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="add_gallery" value="1">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" placeholder="Image title">
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id">
                                    <option value="">-- None --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Image</label>
                                <input type="file" name="image" accept="image/*">
                            </div>
                            <button type="submit" class="btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header"><h2>Gallery Images</h2></div>
                <div class="panel-body">
                    <?php if (empty($images)): ?>
                        <div class="empty-state">No images in gallery.</div>
                    <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($images as $img): ?>
                        <div class="gallery-item">
                            <a href="delete.php?id=<?php echo $img['id']; ?>" class="btn-delete-sm" onclick="return confirm('Delete this image?')" title="Delete">&times;</a>
                            <img src="<?php echo UPLOAD_URL . htmlspecialchars($img['image']); ?>" alt="<?php echo htmlspecialchars($img['title']); ?>" onerror="this.style.background='#e5e7eb';this.style.minHeight='160px'">
                            <div class="gallery-item-info">
                                <h4 title="<?php echo htmlspecialchars($img['title']); ?>"><?php echo htmlspecialchars($img['title'] ?: 'Untitled'); ?></h4>
                                <span><?php echo htmlspecialchars($img['category_name'] ?? 'Uncategorized'); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
