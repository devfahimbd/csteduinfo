<?php
require_once '../includes/config.php';
requireAdmin();

// Handle edit request
$editPoly = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM polytechnics WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $editPoly = $stmt->fetch();
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare('SELECT image FROM polytechnics WHERE id = ?');
    $stmt->execute([$id]);
    $poly = $stmt->fetch();

    if ($poly) {
        if (!empty($poly['image'])) {
            deleteFile($poly['image']);
        }
        $pdo->prepare('DELETE FROM polytechnics WHERE id = ?')->execute([$id]);
        setFlash('success', 'Polytechnic deleted successfully.');
    }
    header('Location: polytechnics.php');
    exit;
}

// Generate slug from name
function generateSlug($name) {
    // Convert Bengali to slug-friendly format, fallback to transliteration
    $slug = preg_replace('/[^a-zA-Z0-9\u0980-\u09FF\s-]/u', '', $name);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    if (empty($slug) || !preg_match('/[a-zA-Z0-9]/', $slug)) {
        $slug = 'polytechnic-' . time();
    }
    return strtolower($slug);
}

// Handle POST (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $status = isset($_POST['status']) ? 1 : 0;
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $slug = generateSlug($name);

    if (empty($name) || empty($location)) {
        setFlash('error', 'Name and Location are required.');
    } else {
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($_FILES['image'], 'polytechnics', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if (!$image) {
                setFlash('error', 'Invalid image file. Allowed: JPG, PNG, GIF, WebP.');
                header('Location: polytechnics.php' . ($editId ? '?action=edit&id=' . $editId : ''));
                exit;
            }
        }

        // Ensure upload directory exists
        $uploadDir = UPLOAD_PATH . '/polytechnics';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if ($editId > 0) {
            // Get old image if no new one uploaded
            if (!$image) {
                $stmt = $pdo->prepare('SELECT image FROM polytechnics WHERE id = ?');
                $stmt->execute([$editId]);
                $old = $stmt->fetch();
                $image = $old ? $old['image'] : null;
            } else {
                // Delete old image
                $stmt = $pdo->prepare('SELECT image FROM polytechnics WHERE id = ?');
                $stmt->execute([$editId]);
                $old = $stmt->fetch();
                if ($old && !empty($old['image'])) {
                    deleteFile($old['image']);
                }
            }
            $pdo->prepare('UPDATE polytechnics SET name = ?, slug = ?, location = ?, website = ?, image = ?, sort_order = ?, status = ? WHERE id = ?')
                ->execute([$name, $slug, $location, $website, $image, $sortOrder, $status, $editId]);
            setFlash('success', 'Polytechnic updated successfully.');
        } else {
            $pdo->prepare('INSERT INTO polytechnics (name, slug, location, website, image, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$name, $slug, $location, $website, $image, $sortOrder, $status]);
            setFlash('success', 'Polytechnic added successfully.');
        }
        header('Location: polytechnics.php');
        exit;
    }
}

// Fetch all polytechnics ordered by sort_order
$stmt = $pdo->query('SELECT * FROM polytechnics ORDER BY sort_order ASC, id DESC');
$polytechnics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polytechnics — Admin Panel</title>
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
            <span>Admin Panel</span>
        </div>

        <?php $activePage = 'polytechnics'; ?>
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
                    <h2>Polytechnics</h2>
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
                    <h3><?php echo $editPoly ? 'Edit Polytechnic' : 'Add New Polytechnic'; ?></h3>
                    <?php if ($editPoly): ?>
                        <a href="polytechnics.php" class="btn btn-sm btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="polytechnics.php" enctype="multipart/form-data" style="max-width:640px;">
                        <?php if ($editPoly): ?>
                            <input type="hidden" name="edit_id" value="<?php echo (int)$editPoly['id']; ?>">
                        <?php endif; ?>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <label class="form-label">Name <span style="color:#EF4444;">*</span></label>
                                <input type="text" name="name" class="form-input" placeholder="Polytechnic name" value="<?php echo htmlspecialchars($editPoly['name'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Location <span style="color:#EF4444;">*</span></label>
                                <input type="text" name="location" class="form-input" placeholder="e.g. Dhaka, Bangladesh" value="<?php echo htmlspecialchars($editPoly['location'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-input" placeholder="https://example.com" value="<?php echo htmlspecialchars($editPoly['website'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-input" value="<?php echo htmlspecialchars($editPoly['sort_order'] ?? 0); ?>" min="0">
                            </div>
                            <div style="grid-column:span 2;">
                                <label class="form-label">Image</label>
                                <?php if ($editPoly && !empty($editPoly['image'])): ?>
                                    <div style="margin-bottom:10px;">
                                        <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($editPoly['image']); ?>" alt="Current image" style="width:60px;height:40px;object-fit:cover;border-radius:6px;border:2px solid #E2E8F0;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-input" accept="image/*" id="imageInput">
                                <div id="imagePreview" style="margin-top:8px;"></div>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?php echo (($editPoly['status'] ?? 1) == 1) ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo (($editPoly['status'] ?? 1) == 0) ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:20px;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <?php echo $editPoly ? 'Update Polytechnic' : 'Add Polytechnic'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Polytechnics Table -->
            <div class="admin-card" style="margin-top:20px;">
                <div class="admin-card-header">
                    <h3>All Polytechnics</h3>
                    <span style="color:#64748B;font-size:13px;"><?php echo count($polytechnics); ?> total</span>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($polytechnics)): ?>
                        <div style="padding:48px 24px;text-align:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;">
                                <path d="M3 21h18"></path>
                                <path d="M5 21V7l8-4v18"></path>
                                <path d="M19 21V11l-6-4"></path>
                                <path d="M9 9h1"></path>
                                <path d="M9 13h1"></path>
                                <path d="M9 17h1"></path>
                            </svg>
                            <p style="color:#94A3B8;font-size:14px;">No polytechnics found. Add your first polytechnic.</p>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Website</th>
                                    <th>Status</th>
                                    <th>Sort</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($polytechnics as $pl): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($pl['image'])): ?>
                                            <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($pl['image']); ?>" alt="<?php echo htmlspecialchars($pl['name']); ?>" class="table-img" style="width:50px;height:34px;border-radius:6px;object-fit:cover;">
                                        <?php else: ?>
                                            <div style="width:50px;height:34px;border-radius:6px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;color:#94A3B8;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight:500;"><?php echo htmlspecialchars($pl['name']); ?></td>
                                    <td style="color:#64748B;"><?php echo htmlspecialchars($pl['location'] ?? '&mdash;'); ?></td>
                                    <td>
                                        <?php if (!empty($pl['website'])): ?>
                                            <a href="<?php echo htmlspecialchars($pl['website']); ?>" target="_blank" rel="noopener" style="color:#2563EB;text-decoration:none;font-size:12px;">
                                                Visit ↗
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#94A3B8;font-size:12px;">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pl['status'])): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:#64748B;"><?php echo (int)$pl['sort_order']; ?></td>
                                    <td class="table-actions">
                                        <a href="polytechnics.php?action=edit&id=<?php echo (int)$pl['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="polytechnics.php?action=delete&id=<?php echo (int)$pl['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this polytechnic?');">Delete</a>
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
                            img.style.cssText = 'width:60px;height:40px;border-radius:6px;object-fit:cover;border:2px solid #E2E8F0;';
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
