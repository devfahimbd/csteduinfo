<?php
require_once '../includes/config.php';
requireAdmin();

// Handle edit request
$editCredit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM credits WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $editCredit = $stmt->fetch();
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare('SELECT image FROM credits WHERE id = ?');
    $stmt->execute([$id]);
    $credit = $stmt->fetch();

    if ($credit) {
        if (!empty($credit['image'])) {
            deleteFile($credit['image']);
        }
        $pdo->prepare('DELETE FROM credits WHERE id = ?')->execute([$id]);
        setFlash('success', 'Team member deleted successfully.');
    }
    header('Location: credits.php');
    exit;
}

// Handle POST (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $status = isset($_POST['status']) ? 1 : 0;
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

    if (empty($name)) {
        setFlash('error', 'Team member name is required.');
    } else {
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($_FILES['image'], 'credits', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if (!$image) {
                setFlash('error', 'Invalid image file. Allowed: JPG, PNG, GIF, WebP.');
                header('Location: credits.php' . ($editId ? '?action=edit&id=' . $editId : ''));
                exit;
            }
        }

        if ($editId > 0) {
            // Get old image if no new one uploaded
            if (!$image) {
                $stmt = $pdo->prepare('SELECT image FROM credits WHERE id = ?');
                $stmt->execute([$editId]);
                $old = $stmt->fetch();
                $image = $old ? $old['image'] : null;
            } else {
                // Delete old image
                $stmt = $pdo->prepare('SELECT image FROM credits WHERE id = ?');
                $stmt->execute([$editId]);
                $old = $stmt->fetch();
                if ($old && !empty($old['image'])) {
                    deleteFile($old['image']);
                }
            }
            $pdo->prepare('UPDATE credits SET name = ?, role = ?, about = ?, image = ?, facebook = ?, linkedin = ?, github = ?, sort_order = ?, status = ? WHERE id = ?')
                ->execute([$name, $role, $about, $image, $facebook, $linkedin, $github, $sortOrder, $status, $editId]);
            setFlash('success', 'Team member updated successfully.');
        } else {
            $pdo->prepare('INSERT INTO credits (name, role, about, image, facebook, linkedin, github, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$name, $role, $about, $image, $facebook, $linkedin, $github, $sortOrder, $status]);
            setFlash('success', 'Team member added successfully.');
        }
        header('Location: credits.php');
        exit;
    }
}

// Fetch all credits ordered by sort_order
$stmt = $pdo->query('SELECT * FROM credits ORDER BY sort_order ASC, id DESC');
$credits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits — CST Admin</title>
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
                <a href="credits.php" class="active">
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
                    <h2>Credits</h2>
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

            <!-- Add/Edit Form -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><?php echo $editCredit ? 'Edit Team Member' : 'Add New Team Member'; ?></h3>
                    <?php if ($editCredit): ?>
                        <a href="credits.php" class="btn btn-sm btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="credits.php" enctype="multipart/form-data" style="max-width:640px;">
                        <?php if ($editCredit): ?>
                            <input type="hidden" name="edit_id" value="<?php echo (int)$editCredit['id']; ?>">
                        <?php endif; ?>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <label class="form-label">Name <span style="color:#EF4444;">*</span></label>
                                <input type="text" name="name" class="form-input" placeholder="Full name" value="<?php echo htmlspecialchars($editCredit['name'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Role</label>
                                <input type="text" name="role" class="form-input" placeholder="e.g. Lead Developer" value="<?php echo htmlspecialchars($editCredit['role'] ?? ''); ?>">
                            </div>
                            <div style="grid-column:span 2;">
                                <label class="form-label">About</label>
                                <textarea name="about" class="form-input" rows="3" placeholder="Brief description..."><?php echo htmlspecialchars($editCredit['about'] ?? ''); ?></textarea>
                            </div>
                            <div style="grid-column:span 2;">
                                <label class="form-label">Image</label>
                                <?php if ($editCredit && !empty($editCredit['image'])): ?>
                                    <div style="margin-bottom:10px;">
                                        <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($editCredit['image']); ?>" alt="Current image" style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid #E2E8F0;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-input" accept="image/*" id="imageInput">
                                <div id="imagePreview" style="margin-top:8px;"></div>
                            </div>
                            <div>
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="facebook" class="form-input" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($editCredit['facebook'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" name="linkedin" class="form-input" placeholder="https://linkedin.com/in/..." value="<?php echo htmlspecialchars($editCredit['linkedin'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github" class="form-input" placeholder="https://github.com/..." value="<?php echo htmlspecialchars($editCredit['github'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-input" value="<?php echo htmlspecialchars($editCredit['sort_order'] ?? 0); ?>" min="0">
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?php echo (($editCredit['status'] ?? 1) == 1) ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo (($editCredit['status'] ?? 1) == 0) ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:20px;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <?php echo $editCredit ? 'Update Member' : 'Add Member'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Credits Table -->
            <div class="admin-card" style="margin-top:20px;">
                <div class="admin-card-header">
                    <h3>All Team Members</h3>
                    <span style="color:#64748B;font-size:13px;"><?php echo count($credits); ?> total</span>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($credits)): ?>
                        <div style="padding:48px 24px;text-align:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <p style="color:#94A3B8;font-size:14px;">No team members found. Add your first team member.</p>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Sort</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($credits as $cr): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($cr['image'])): ?>
                                            <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($cr['image']); ?>" alt="<?php echo htmlspecialchars($cr['name']); ?>" class="table-img" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                        <?php else: ?>
                                            <div style="width:40px;height:40px;border-radius:50%;background:#F1F5F9;display:flex;align-items:center;justify-content:center;color:#94A3B8;font-weight:600;font-size:14px;">
                                                <?php echo strtoupper(mb_substr($cr['name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight:500;"><?php echo htmlspecialchars($cr['name']); ?></td>
                                    <td style="color:#64748B;"><?php echo htmlspecialchars($cr['role'] ?? '&mdash;'); ?></td>
                                    <td>
                                        <?php if (!empty($cr['status'])): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:#64748B;"><?php echo (int)$cr['sort_order']; ?></td>
                                    <td class="table-actions">
                                        <a href="credits.php?action=edit&id=<?php echo (int)$cr['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="credits.php?action=delete&id=<?php echo (int)$cr['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this team member?');">Delete</a>
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

            // Image preview
            var imageInput = document.getElementById('imageInput');
            var imagePreview = document.getElementById('imagePreview');
            if (imageInput) {
                imageInput.addEventListener('change', function() {
                    imagePreview.innerHTML = '';
                    if (this.files && this.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.cssText = 'width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #E2E8F0;';
                            imagePreview.appendChild(img);
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        })();
    </script>
</body>
</html>
