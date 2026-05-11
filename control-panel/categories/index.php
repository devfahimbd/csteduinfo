<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$activeTab = sanitizeInput($_GET['tab'] ?? 'all');
$validTabs = ['all', 'notice', 'teacher', 'gallery', 'resource'];
if (!in_array($activeTab, $validTabs)) $activeTab = 'all';

$conditions = [];
$params = [];
if ($activeTab !== 'all') {
    $conditions[] = "type = ?";
    $params[] = $activeTab;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

try {
    $sql = "SELECT * FROM categories $where ORDER BY type ASC, name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

$currentPage = 'categories';
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin Panel</title>
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

        .toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; gap: 12px; flex-wrap: wrap; }
        .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }

        .tabs { display: flex; gap: 4px; margin-bottom: 20px; background: #fff; padding: 6px; border-radius: 8px; border: 1px solid #e5e7eb; width: fit-content; }
        .tab { padding: 7px 16px; border-radius: 6px; font-size: 13px; font-weight: 450; color: #6b7280; text-decoration: none; transition: background 0.15s, color 0.15s; }
        .tab:hover { color: #374151; }
        .tab.active { background: #2563eb; color: #fff; }

        .panel { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 18px; text-align: left; font-size: 13px; }
        .data-table th { background: #f9fafb; color: #6b7280; font-weight: 500; border-bottom: 1px solid #e5e7eb; }
        .data-table td { border-bottom: 1px solid #f3f4f6; color: #374151; }
        .data-table tr:hover td { background: #f9fafb; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 500; text-transform: capitalize; }
        .badge-notice { background: #dbeafe; color: #2563eb; }
        .badge-teacher { background: #dcfce7; color: #16a34a; }
        .badge-gallery { background: #fef3c7; color: #d97706; }
        .badge-resource { background: #f3e8ff; color: #7c3aed; }
        .badge-active { background: #dcfce7; color: #16a34a; }
        .badge-inactive { background: #f3f4f6; color: #6b7280; }

        .actions { display: flex; gap: 6px; }
        .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 4px; text-decoration: none; font-weight: 500; }
        .btn-edit { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .btn-edit:hover { background: #dbeafe; }
        .btn-delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-delete:hover { background: #fee2e2; }
        .empty-state { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: 13px; }

        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-content { margin-left: 0; }
            .tabs { flex-wrap: wrap; width: 100%; }
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
            <a href="../gallery/" class="nav-item"><?php echo icon('image', 18); ?> Gallery</a>
            <a href="index.php" class="nav-item active"><?php echo icon('category', 18); ?> Categories</a>
            <a href="../sponsors/" class="nav-item"><?php echo icon('award', 18); ?> Sponsors</a>
            <a href="../credits/" class="nav-item"><?php echo icon('star', 18); ?> Credits</a>
            <a href="../settings.php" class="nav-item"><?php echo icon('settings', 18); ?> Settings</a>
            <a href="../logout.php" class="nav-item nav-item-danger"><?php echo icon('logout', 18); ?> Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> &nbsp;/&nbsp; <strong>Categories</strong></div>
            <div class="admin-user">Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
        </header>

        <div class="admin-body">
            <?php echo displayFlash(); ?>

            <div class="toolbar">
                <div class="tabs">
                    <a href="index.php?tab=all" class="tab <?php echo $activeTab === 'all' ? 'active' : ''; ?>">All</a>
                    <a href="index.php?tab=notice" class="tab <?php echo $activeTab === 'notice' ? 'active' : ''; ?>">Notice</a>
                    <a href="index.php?tab=teacher" class="tab <?php echo $activeTab === 'teacher' ? 'active' : ''; ?>">Teacher</a>
                    <a href="index.php?tab=gallery" class="tab <?php echo $activeTab === 'gallery' ? 'active' : ''; ?>">Gallery</a>
                    <a href="index.php?tab=resource" class="tab <?php echo $activeTab === 'resource' ? 'active' : ''; ?>">Resource</a>
                </div>
                <a href="create.php" class="btn-primary"><?php echo icon('plus', 16); ?> Add Category</a>
            </div>

            <div class="panel">
                <?php if (empty($categories)): ?>
                    <div class="empty-state">No categories found.</div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                            <td><span class="badge badge-<?php echo $cat['type']; ?>"><?php echo ucfirst($cat['type']); ?></span></td>
                            <td><span class="badge badge-<?php echo $cat['status'] == 1 ? 'active' : 'inactive'; ?>"><?php echo $cat['status'] == 1 ? 'Active' : 'Inactive'; ?></span></td>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?php echo $cat['id']; ?>" class="btn-sm btn-edit">Edit</a>
                                    <a href="delete.php?id=<?php echo $cat['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this category?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
