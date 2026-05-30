<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();
require_once 'auth-check.php';

// Stats queries
try {
    $totalTeachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $activeNotices = $pdo->query("SELECT COUNT(*) FROM notices WHERE status = 1")->fetchColumn();
    $activeResources = $pdo->query("SELECT COUNT(*) FROM resources WHERE status = 1")->fetchColumn();
    $galleryImages = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
    try {
        $totalPolytechnics = $pdo->query("SELECT COUNT(*) FROM polytechnics")->fetchColumn();
    } catch (PDOException $e) {
        $totalPolytechnics = 0;
    }

    $stmt = $pdo->query("SELECT n.title, c.name AS category, n.created_at, n.status FROM notices n LEFT JOIN categories c ON n.category_id = c.id ORDER BY n.created_at DESC LIMIT 5");
    $recentNotices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $totalTeachers = $activeNotices = $activeResources = $galleryImages = $totalPolytechnics = 0;
    $recentNotices = [];
}

$currentPage = 'dashboard.php';
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #1f2937; }

        .admin-layout { display: flex; min-height: 100vh; }

        .admin-sidebar {
            width: 250px; background: #1a1a2e; color: #e5e7eb; position: fixed; top: 0; left: 0; bottom: 0;
            display: flex; flex-direction: column; z-index: 100; overflow-y: auto;
        }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; font-weight: 600; font-size: 15px; }
        .sidebar-logo img { height: 32px; width: 32px; border-radius: 6px; object-fit: cover; }

        .sidebar-nav { padding: 12px 8px; flex: 1; }
        .nav-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-radius: 6px;
            color: #9ca3af; text-decoration: none; font-size: 13.5px; font-weight: 450;
            transition: background 0.15s, color 0.15s; margin-bottom: 2px;
        }
        .nav-item:hover { background: rgba(255,255,255,0.06); color: #e5e7eb; }
        .nav-item.active { background: #2563eb; color: #fff; }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .nav-item-danger { margin-top: 12px; color: #f87171; }
        .nav-item-danger:hover { background: rgba(248,113,113,0.1); color: #f87171; }

        .admin-content { margin-left: 250px; flex: 1; min-height: 100vh; }

        .admin-header {
            background: #fff; border-bottom: 1px solid #e5e7eb; padding: 16px 28px;
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .breadcrumb { font-size: 13px; color: #6b7280; }
        .breadcrumb strong { color: #1f2937; font-weight: 600; }
        .admin-user { font-size: 13px; color: #6b7280; }
        .admin-user strong { color: #1f2937; }

        .admin-body { padding: 28px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card {
            background: #fff; border-radius: 8px; padding: 22px 24px; border: 1px solid #e5e7eb;
            display: flex; align-items: center; gap: 16px;
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center;
            justify-content: center; flex-shrink: 0;
        }
        .stat-icon.blue { background: #dbeafe; color: #2563eb; }
        .stat-icon.green { background: #dcfce7; color: #16a34a; }
        .stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
        .stat-icon.amber { background: #fef3c7; color: #d97706; }
        .stat-icon svg { width: 22px; height: 22px; }
        .stat-info h3 { font-size: 24px; font-weight: 700; color: #111827; }
        .stat-info p { font-size: 13px; color: #6b7280; margin-top: 2px; }

        .panel {
            background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden;
        }
        .panel-header {
            padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex;
            align-items: center; justify-content: space-between;
        }
        .panel-header h2 { font-size: 15px; font-weight: 600; color: #111827; }
        .panel-header a { font-size: 13px; color: #2563eb; text-decoration: none; }
        .panel-header a:hover { text-decoration: underline; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 20px; text-align: left; font-size: 13px; }
        .data-table th { background: #f9fafb; color: #6b7280; font-weight: 500; border-bottom: 1px solid #e5e7eb; }
        .data-table td { border-bottom: 1px solid #f3f4f6; color: #374151; }
        .data-table tr:hover td { background: #f9fafb; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        .badge-active { background: #dcfce7; color: #16a34a; }
        .badge-inactive { background: #f3f4f6; color: #6b7280; }

        .empty-state { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: 13px; }

        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="../" class="sidebar-logo">
                <img src="<?php echo defined('SITE_URL') ? SITE_URL : ''; ?>/assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
                Admin Panel
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <?php echo icon('dashboard', 18); ?> Dashboard
            </a>
            <a href="notices/" class="nav-item <?php echo strpos($currentPage, 'notices') !== false ? 'active' : ''; ?>">
                <?php echo icon('notice', 18); ?> Notices
            </a>
            <a href="teachers/" class="nav-item <?php echo strpos($currentPage, 'teachers') !== false ? 'active' : ''; ?>">
                <?php echo icon('users', 18); ?> Faculty
            </a>
            <a href="resources/" class="nav-item <?php echo strpos($currentPage, 'resources') !== false ? 'active' : ''; ?>">
                <?php echo icon('download', 18); ?> Resources
            </a>
            <a href="gallery/" class="nav-item <?php echo strpos($currentPage, 'gallery') !== false ? 'active' : ''; ?>">
                <?php echo icon('image', 18); ?> Gallery
            </a>
            <a href="categories/" class="nav-item <?php echo strpos($currentPage, 'categories') !== false ? 'active' : ''; ?>">
                <?php echo icon('category', 18); ?> Categories
            </a>
            <a href="sponsors/" class="nav-item <?php echo strpos($currentPage, 'sponsors') !== false ? 'active' : ''; ?>">
                <?php echo icon('award', 18); ?> Sponsors
            </a>
            <a href="credits/" class="nav-item <?php echo strpos($currentPage, 'credits') !== false ? 'active' : ''; ?>">
                <?php echo icon('star', 18); ?> Credits
            </a>
            <a href="settings.php" class="nav-item <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <?php echo icon('settings', 18); ?> Settings
            </a>
            <a href="logout.php" class="nav-item nav-item-danger">
                <?php echo icon('logout', 18); ?> Logout
            </a>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <div class="breadcrumb"><strong>Dashboard</strong></div>
            <div class="admin-user">Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
        </header>

        <div class="admin-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><?php echo icon('users', 22); ?></div>
                    <div class="stat-info">
                        <h3><?php echo (int)$totalTeachers; ?></h3>
                        <p>Total Teachers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><?php echo icon('notice', 22); ?></div>
                    <div class="stat-info">
                        <h3><?php echo (int)$activeNotices; ?></h3>
                        <p>Active Notices</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><?php echo icon('download', 22); ?></div>
                    <div class="stat-info">
                        <h3><?php echo (int)$activeResources; ?></h3>
                        <p>Active Resources</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><?php echo icon('image', 22); ?></div>
                    <div class="stat-info">
                        <h3><?php echo (int)$galleryImages; ?></h3>
                        <p>Gallery Images</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue" style="background:#ede9fe;color:#7c3aed;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l8-4v18"></path>
                            <path d="M19 21V11l-6-4"></path>
                            <path d="M9 9h1"></path>
                            <path d="M9 13h1"></path>
                            <path d="M9 17h1"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo (int)$totalPolytechnics; ?></h3>
                        <p>Total Polytechnics</p>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Notices</h2>
                    <a href="notices/">View All &rarr;</a>
                </div>
                <?php if (empty($recentNotices)): ?>
                    <div class="empty-state">No notices found.</div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentNotices as $notice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($notice['title']); ?></td>
                            <td><?php echo htmlspecialchars($notice['category'] ?? 'Uncategorized'); ?></td>
                            <td><span class="badge badge-<?php echo $notice['status'] == 1 ? 'active' : 'inactive'; ?>"><?php echo $notice['status'] == 1 ? 'Active' : 'Inactive'; ?></span></td>
                            <td><?php echo formatDate($notice['created_at']); ?></td>
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
