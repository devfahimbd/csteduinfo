<?php
require_once '../includes/config.php';
requireAdmin();

// Fetch counts (default to 0 on error)
$totalNotices = 0;
$totalTeachers = 0;
$totalGallery = 0;
$totalResources = 0;
$totalContacts = 0;
$totalResultStudents = 0;
$recentNotices = [];
$recentContacts = [];

try {
    $totalNotices = (int) $pdo->query('SELECT COUNT(*) FROM notices')->fetchColumn();
} catch (PDOException $e) { $totalNotices = 0; }

try {
    $totalTeachers = (int) $pdo->query('SELECT COUNT(*) FROM teachers')->fetchColumn();
} catch (PDOException $e) { $totalTeachers = 0; }

try {
    $totalGallery = (int) $pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn();
} catch (PDOException $e) { $totalGallery = 0; }

try {
    $totalResources = (int) $pdo->query('SELECT COUNT(*) FROM resources')->fetchColumn();
} catch (PDOException $e) { $totalResources = 0; }

try {
    $totalContacts = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
} catch (PDOException $e) { $totalContacts = 0; }

try {
    $totalResultStudents = (int) $pdo->query('SELECT COUNT(*) FROM result_students')->fetchColumn();
} catch (PDOException $e) { $totalResultStudents = 0; }

// Fetch 5 latest notices
try {
    $stmt = $pdo->query('SELECT n.*, c.name AS category_name FROM notices n LEFT JOIN categories c ON n.category_id = c.id ORDER BY n.created_at DESC LIMIT 5');
    $recentNotices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $recentNotices = []; }

// Fetch 3 latest contacts
try {
    $stmt = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 3');
    $recentContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $recentContacts = []; }

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — CST Admin</title>
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
            <span>CST Admin</span>
        </div>

        <?php $activePage = 'dashboard'; ?>
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
                    <h2>Dashboard</h2>
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

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color:#2563EB;background:#EFF6FF;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalNotices; ?></h3>
                        <p>Total Notices</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="color:#16A34A;background:#F0FDF4;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalTeachers; ?></h3>
                        <p>Total Faculty</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="color:#EA580C;background:#FFF7ED;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalGallery; ?></h3>
                        <p>Gallery Items</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="color:#9333EA;background:#FAF5FF;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalResources; ?></h3>
                        <p>Resources</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="color:#0891B2;background:#ECFEFF;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <path d="M9 15l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalResultStudents); ?></h3>
                        <p>Result Records</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="result-json-upload.php" class="quick-action">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span>Upload Result JSON</span>
                </a>
                <a href="notices.php?action=add" class="quick-action">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span>Add Notice</span>
                </a>
                <a href="teachers.php?action=add" class="quick-action">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <span>Add Teacher</span>
                </a>
                <a href="gallery.php?action=add" class="quick-action">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    <span>Add Gallery</span>
                </a>
                <a href="resources.php?action=add" class="quick-action">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="11" x2="12" y2="17"></line>
                        <line x1="9" y1="14" x2="15" y2="14"></line>
                    </svg>
                    <span>Add Resource</span>
                </a>
            </div>

            <!-- Recent Notices -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Recent Notices</h3>
                    <a href="notices.php">View All</a>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($recentNotices)): ?>
                        <p style="color:#6B7280;padding:16px 0;">No notices found.</p>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="table-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentNotices as $notice): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($notice['title'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($notice['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($notice['created_at'])); ?></td>
                                <td>
                                    <?php if (!empty($notice['status']) && $notice['status'] === 'published'): ?>
                                        <span class="badge badge-success">Published</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <a href="notices.php?action=edit&id=<?php echo (int)$notice['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                    <a href="notices.php?action=delete&id=<?php echo (int)$notice['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this notice?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Recent Messages</h3>
                    <a href="contacts.php">View All</a>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($recentContacts)): ?>
                        <p style="color:#6B7280;padding:16px 0;">No messages found.</p>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentContacts as $contact): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contact['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($contact['email'] ?? ''); ?></td>
                                <td><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                                <td>
                                    <?php if (!empty($contact['is_read'])): ?>
                                        <span class="badge badge-success">Read</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Unread</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

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
