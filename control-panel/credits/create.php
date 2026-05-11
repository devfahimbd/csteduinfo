<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$iconOptions = ['heart', 'star', 'code', 'award', 'zap', 'shield', 'globe', 'monitor', 'users', 'layers'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        try {
            $name = sanitizeInput($_POST['name'] ?? '');
            $role = sanitizeInput($_POST['role'] ?? '');
            $icon = in_array($_POST['icon'] ?? '', $iconOptions) ? $_POST['icon'] : 'heart';
            $url = sanitizeInput($_POST['url'] ?? '');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $status = ($_POST['status'] ?? 'active') === 'active' ? 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO credits (name, role, icon, url, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $role, $icon, $url, $sortOrder, $status]);

            setFlash('success', 'Credit created successfully.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            setFlash('error', 'Failed to create credit.');
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
    <title>Add Credit - Admin Panel</title>
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
        .admin-body { padding: 28px; max-width: 600px; }

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
        .form-group input[type="url"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px;
            font-size: 13px; color: #1f2937; background: #fff; transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-group .hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }

        .icon-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin-top: 6px; }
        .icon-option {
            display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 10px 4px;
            border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.15s;
            background: #fff;
        }
        .icon-option:hover { border-color: #93c5fd; }
        .icon-option.selected { border-color: #2563eb; background: #eff6ff; }
        .icon-option svg { width: 20px; height: 20px; color: #374151; }
        .icon-option.selected svg { color: #2563eb; }
        .icon-option span { font-size: 10px; color: #6b7280; text-transform: capitalize; }
        .icon-option input { display: none; }

        .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background 0.2s; }
        .btn-secondary:hover { background: #f9fafb; }

        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-content { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
            .icon-grid { grid-template-columns: repeat(4, 1fr); }
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
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> &nbsp;/&nbsp; <a href="index.php">Credits</a> &nbsp;/&nbsp; <strong>Add Credit</strong></div>
            <div class="admin-user">Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
        </header>

        <div class="admin-body">
            <?php echo displayFlash(); ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="panel">
                    <div class="panel-header"><h2>Credit Details</h2></div>
                    <div class="panel-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" required placeholder="Person or team name">
                            </div>
                            <div class="form-group">
                                <label>Role *</label>
                                <input type="text" name="role" required placeholder="e.g. Developer, Designer">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>URL</label>
                                <input type="url" name="url" placeholder="https://...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Icon</label>
                            <div class="icon-grid">
                                <?php foreach ($iconOptions as $ic): ?>
                                <label class="icon-option <?php echo $ic === 'heart' ? 'selected' : ''; ?>" id="icon-opt-<?php echo $ic; ?>">
                                    <?php echo icon($ic, 20); ?>
                                    <span><?php echo $ic; ?></span>
                                    <input type="radio" name="icon" value="<?php echo $ic; ?>" <?php echo $ic === 'heart' ? 'checked' : ''; ?>>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="0" min="0">
                                <p class="hint">Lower numbers appear first</p>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="submit" class="btn-primary">Create Credit</button>
                    <a href="index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.querySelectorAll('.icon-option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
        opt.classList.add('selected');
        opt.querySelector('input').checked = true;
    });
});
</script>
</body>
</html>
