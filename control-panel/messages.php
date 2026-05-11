<?php
require_once '../includes/config.php';
requireAdmin();

// Handle mark as read
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    header('Location: messages.php');
    exit;
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
    setFlash('success', 'Message deleted successfully.');
    header('Location: messages.php');
    exit;
}

// Handle view (mark as read and set session)
$viewMessage = null;
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ?');
    $stmt->execute([$id]);
    $viewMessage = $stmt->fetch();
}

// Fetch all messages
$stmt = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread
$unreadCount = 0;
foreach ($messages as $msg) {
    if (empty($msg['is_read'])) $unreadCount++;
}

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages — CST Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .message-expand {
            display: none;
            border-top: 1px solid #E2E8F0;
            background: #F8FAFC;
        }
        .message-expand.open {
            display: table-row;
        }
        .message-expand td {
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
        }
        .message-expand .msg-meta {
            display: flex;
            gap: 24px;
            margin-bottom: 12px;
            font-size: 13px;
            color: #64748B;
        }
        .message-expand .msg-body {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .message-expand .msg-actions {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #E2E8F0;
        }
    </style>
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

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">MAIN</div>
                <a href="index.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
                <a href="../index.php" target="_blank">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    Website
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">CONTENT</div>
                <a href="notices.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    Notices
                </a>
                <a href="teachers.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Faculty
                </a>
                <a href="gallery.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    Gallery
                </a>
                <a href="resources.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Resources
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">MANAGEMENT</div>
                <a href="categories.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    Categories
                </a>
                <a href="sponsors.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"></circle>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                    </svg>
                    Sponsors
                </a>
                <a href="credits.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    Credits
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">SETTINGS</div>
                <a href="settings.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Settings
                </a>
            </div>
        </nav>

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
                    <h2>Messages</h2>
                </div>
            </div>
            <div class="topbar-actions">
                <?php if ($unreadCount > 0): ?>
                    <span style="background:#EF4444;color:#fff;font-size:12px;font-weight:600;padding:2px 10px;border-radius:10px;">
                        <?php echo $unreadCount; ?> unread
                    </span>
                <?php endif; ?>
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
                <p style="color:#64748B;font-size:14px;">Manage contact messages &mdash; <?php echo count($messages); ?> total, <?php echo $unreadCount; ?> unread</p>
            </div>

            <!-- Messages Table -->
            <div class="admin-card">
                <div class="admin-card-body" style="padding:0;">
                    <?php if (empty($messages)): ?>
                        <div style="padding:48px 24px;text-align:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <p style="color:#94A3B8;font-size:14px;">No messages found.</p>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td>
                                        <a href="javascript:void(0)" onclick="toggleMessage(<?php echo (int)$msg['id']; ?>)" style="font-weight:<?php echo empty($msg['is_read']) ? '600' : '400'; ?>;color:<?php echo empty($msg['is_read']) ? '#1E293B' : '#2563EB'; ?>;text-decoration:none;">
                                            <?php echo htmlspecialchars($msg['name']); ?>
                                            <?php if (empty($msg['is_read'])): ?>
                                                <span style="display:inline-block;width:6px;height:6px;background:#2563EB;border-radius:50%;margin-left:6px;vertical-align:middle;"></span>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td style="font-size:13px;color:#64748B;"><?php echo htmlspecialchars($msg['email']); ?></td>
                                    <td>
                                        <?php if (!empty($msg['subject'])): ?>
                                            <?php echo htmlspecialchars(mb_strimwidth($msg['subject'], 0, 35, '...')); ?>
                                        <?php else: ?>
                                            <span style="color:#94A3B8;">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:13px;color:#64748B;white-space:nowrap;"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                    <td>
                                        <?php if (!empty($msg['is_read'])): ?>
                                            <span class="badge badge-success">Read</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="javascript:void(0)" onclick="toggleMessage(<?php echo (int)$msg['id']; ?>)" class="btn btn-sm btn-edit">View</a>
                                        <?php if (empty($msg['is_read'])): ?>
                                            <a href="messages.php?action=read&id=<?php echo (int)$msg['id']; ?>" class="btn btn-sm btn-secondary">Mark Read</a>
                                        <?php endif; ?>
                                        <a href="messages.php?action=delete&id=<?php echo (int)$msg['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                                    </td>
                                </tr>
                                <!-- Expanded message row -->
                                <tr class="message-expand" id="msg-<?php echo (int)$msg['id']; ?>">
                                    <td colspan="6">
                                        <div class="msg-meta">
                                            <span><strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?></span>
                                            <span><strong>Email:</strong> <?php echo htmlspecialchars($msg['email']); ?></span>
                                            <span><strong>Date:</strong> <?php echo date('F d, Y \a\t g:i A', strtotime($msg['created_at'])); ?></span>
                                        </div>
                                        <?php if (!empty($msg['subject'])): ?>
                                            <div style="margin-bottom:12px;">
                                                <strong style="color:#1E293B;">Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="msg-body"><?php echo htmlspecialchars($msg['message']); ?></div>
                                        <div class="msg-actions" style="display:flex;gap:8px;">
                                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="btn btn-sm btn-primary">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;">
                                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                                </svg>
                                                Reply
                                            </a>
                                            <a href="messages.php?action=delete&id=<?php echo (int)$msg['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this message?');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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

        // Toggle message expand
        function toggleMessage(id) {
            var row = document.getElementById('msg-' + id);
            if (!row) return;

            // Close all other expanded rows
            var allExpanded = document.querySelectorAll('.message-expand.open');
            for (var i = 0; i < allExpanded.length; i++) {
                if (allExpanded[i].id !== 'msg-' + id) {
                    allExpanded[i].classList.remove('open');
                }
            }

            row.classList.toggle('open');

            // If opening, mark as read via AJAX
            if (row.classList.contains('open')) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'messages.php?action=read&id=' + id, true);
                xhr.send();
            }
        }
    </script>
</body>
</html>
