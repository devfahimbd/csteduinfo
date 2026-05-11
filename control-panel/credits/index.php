<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

// Handle inline add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_credit'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        try {
            $name = sanitizeInput($_POST['name'] ?? '');
            $role = sanitizeInput($_POST['role'] ?? '');
            $section = sanitizeInput($_POST['section'] ?? '');
            $icon = sanitizeInput($_POST['icon'] ?? 'heart');
            $url = sanitizeInput($_POST['url'] ?? '');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

            $stmt = $pdo->prepare("INSERT INTO credits (name, role, section, icon, url, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $role, $section, $icon, $url, $sortOrder, $status]);
            setFlash('success', 'Credit added successfully.');
        } catch (PDOException $e) {
            setFlash('error', 'Failed to add credit.');
        }
        header('Location: index.php');
        exit;
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM credits ORDER BY sort_order ASC, id DESC");
    $credits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $credits = [];
}

$iconOptions = ['heart', 'star', 'code', 'award', 'zap', 'shield', 'globe', 'monitor', 'users', 'layers'];

$currentPage = 'credits';
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits - Admin Panel</title>
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

        .toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }

        .panel { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px; overflow: hidden; }
        .panel-header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; }
        .panel-header h2 { font-size: 15px; font-weight: 600; color: #111827; }
        .panel-body { padding: 24px; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 18px; text-align: left; font-size: 13px; }
        .data-table th { background: #f9fafb; color: #6b7280; font-weight: 500; border-bottom: 1px solid #e5e7eb; }
        .data-table td { border-bottom: 1px solid #f3f4f6; color: #374151; }
        .data-table tr:hover td { background: #f9fafb; }

        .credit-cell { display: flex; align-items: center; gap: 12px; }
        .credit-icon { width: 34px; height: 34px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .credit-icon svg { width: 16px; height: 16px; }
        .credit-name { font-weight: 500; color: #111827; }
        .credit-role { font-size: 12px; color: #6b7280; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 500; }
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
            <a href="../categories/" class="nav-item"><?php echo icon('category', 18); ?> Categories</a>
            <a href="../sponsors/" class="nav-item"><?php echo icon('award', 18); ?> Sponsors</a>
            <a href="index.php" class="nav-item active"><?php echo icon('star', 18); ?> Credits</a>
            <a href="../settings.php" class="nav-item"><?php echo icon('settings', 18); ?> Settings</a>
            <a href="../logout.php" class="nav-item nav-item-danger"><?php echo icon('logout', 18); ?> Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> &nbsp;/&nbsp; <strong>Credits</strong></div>
            <div class="admin-user">Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
        </header>

        <div class="admin-body">
            <?php echo displayFlash(); ?>

            <div class="toolbar">
                <div style="font-size:14px;font-weight:600;color:#111827;">All Credits</div>
                <a href="create.php" class="btn-primary"><?php echo icon('plus', 16); ?> Add Credit</a>
            </div>

            <div class="panel">
                <?php if (empty($credits)): ?>
                    <div class="empty-state">No credits found.</div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Section</th>
                            <th>Icon</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($credits as $cr): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cr['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cr['role'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($cr['section'] ?? '—'); ?></td>
                            <td>
                                <div class="credit-icon"><?php echo icon($cr['icon'] ?? 'heart', 16); ?></div>
                            </td>
                            <td><?php echo (int)$cr['sort_order']; ?></td>
                            <td><span class="badge badge-<?php echo $cr['status']; ?>"><?php echo ucfirst($cr['status']); ?></span></td>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?php echo $cr['id']; ?>" class="btn-sm btn-edit">Edit</a>
                                    <a href="delete.php?id=<?php echo $cr['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this credit?')">Delete</a>
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
