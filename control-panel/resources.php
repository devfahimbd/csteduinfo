<?php
require_once '../includes/config.php';
requireAdmin();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare('SELECT file_path FROM resources WHERE id = ?');
    $stmt->execute([$id]);
    $resource = $stmt->fetch();

    if ($resource) {
        if (!empty($resource['file_path'])) {
            deleteFile($resource['file_path']);
        }
        $pdo->prepare('DELETE FROM resources WHERE id = ?')->execute([$id]);
        setFlash('success', 'Resource deleted successfully.');
    }
    header('Location: resources.php');
    exit;
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$countStmt = $pdo->query('SELECT COUNT(*) FROM resources');
$totalItems = (int) $countStmt->fetchColumn();
$pagination = getPagination($page, $totalItems, $perPage);

// Fetch resources
$stmt = $pdo->prepare('
    SELECT r.*, c.name AS category_name
    FROM resources r
    LEFT JOIN categories c ON r.category_id = c.id
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
');
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources — অ্যাডমিন প্যানেল</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-layout">

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            <span>অ্যাডমিন প্যানেল</span>
        </div>

        <?php $activePage = 'resources'; ?>
        <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

        <div class="sidebar-footer">
            <a href="logout.php">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- ========== SIDEBAR OVERLAY (mobile) ========== -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ========== MAIN AREA ========== -->
    <main class="admin-main">

        <!-- Topbar -->
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="topbar-title">
                    <h2>Resources</h2>
                </div>
            </div>
            <div class="topbar-actions">
                <div class="admin-user">
                    <div class="avatar"><?php echo htmlspecialchars($adminInitial); ?></div>
                    <span><?php echo htmlspecialchars($adminName); ?></span>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="admin-content">

            <!-- Flash message -->
            <?php if (!empty($flash['type']) && !empty($flash['message'])): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <p style="color:#64748B;font-size:14px;">Manage resources &mdash; <?php echo $pagination['total_items']; ?> total</p>
                <a href="resource-edit.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New Resource
                </a>
            </div>

            <!-- Resources Table -->
            <div class="admin-card">
                <div class="admin-card-body">
                    <?php if (empty($resources)): ?>
                        <div style="padding:48px 24px;text-align:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <p style="color:#94A3B8;font-size:14px;">No resources found. Add your first resource.</p>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resources as $res): ?>
                                <tr>
                                    <td>
                                        <a href="resource-edit.php?id=<?php echo (int)$res['id']; ?>" style="font-weight:500;">
                                            <?php echo htmlspecialchars($res['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($res['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <?php if (!empty($res['file_path'])): ?>
                                            <span class="badge badge-info">File</span>
                                        <?php elseif (!empty($res['external_url'])): ?>
                                            <span class="badge badge-warning">Link</span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#F1F5F9;color:#64748B;">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($res['created_at'])); ?></td>
                                    <td>
                                        <?php if (!empty($res['status'])): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="resource-edit.php?id=<?php echo (int)$res['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="resources.php?action=delete&id=<?php echo (int)$res['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this resource?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total'] > 1): ?>
            <div style="display:flex;justify-content:center;gap:6px;margin-top:8px;">
                <?php if ($pagination['current'] > 1): ?>
                    <a href="resources.php?page=<?php echo $pagination['current'] - 1; ?>" class="btn btn-sm btn-secondary">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total']; $i++):
                    if ($i === 1 || $i === $pagination['total'] || ($i >= $pagination['current'] - 2 && $i <= $pagination['current'] + 2)):
                ?>
                    <?php if ($i > 1 && $i < $pagination['current'] - 2): ?>
                        <span style="padding:7px 10px;color:#94A3B8;">...</span>
                    <?php elseif ($i < $pagination['total'] && $i > $pagination['current'] + 2): ?>
                        <span style="padding:7px 10px;color:#94A3B8;">...</span>
                    <?php else: ?>
                        <a href="resources.php?page=<?php echo $i; ?>" class="btn btn-sm <?php echo $i === $pagination['current'] ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endif; endfor; ?>

                <?php if ($pagination['current'] < $pagination['total']): ?>
                    <a href="resources.php?page=<?php echo $pagination['current'] + 1; ?>" class="btn btn-sm btn-secondary">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Mobile Sidebar Toggle -->
    <script>
        (function() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            var toggle = document.getElementById('mobileToggle');

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('active');
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }

            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (sidebar.classList.contains('open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });
        })();
    </script>
</body>
</html>
