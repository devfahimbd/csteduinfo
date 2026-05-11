<?php
require_once '../includes/config.php';
requireAdmin();

// Handle edit request
$editCategory = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $editCategory = $stmt->fetch();
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Check if category is used by any content
    $used = false;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notices WHERE category_id = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) $used = true;

    if (!$used) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM teachers WHERE category_id = ?');
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) $used = true;
    }
    if (!$used) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM gallery WHERE category_id = ?');
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) $used = true;
    }
    if (!$used) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM resources WHERE category_id = ?');
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) $used = true;
    }

    if ($used) {
        setFlash('error', 'Cannot delete this category because it is assigned to existing content.');
    } else {
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        setFlash('success', 'Category deleted successfully.');
    }
    header('Location: categories.php');
    exit;
}

// Handle POST (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'notice';
    $status = isset($_POST['status']) ? 1 : 0;
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

    if (empty($name)) {
        setFlash('error', 'Category name is required.');
    } else {
        $slug = createSlug($name . '-' . $type);

        if ($editId > 0) {
            // Check for duplicate slug excluding current
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $editId]);
            if ($stmt->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }
            $pdo->prepare('UPDATE categories SET name = ?, slug = ?, type = ?, status = ? WHERE id = ?')
                ->execute([$name, $slug, $type, $status, $editId]);
            setFlash('success', 'Category updated successfully.');
        } else {
            // Check for duplicate slug
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE slug = ?');
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }
            $pdo->prepare('INSERT INTO categories (name, slug, type, status) VALUES (?, ?, ?, ?)')
                ->execute([$name, $slug, $type, $status]);
            setFlash('success', 'Category added successfully.');
        }
        header('Location: categories.php');
        exit;
    }
}

// Fetch all categories ordered by type then name
$stmt = $pdo->query('SELECT * FROM categories ORDER BY FIELD(type, "notice","teacher","gallery","resource"), name ASC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));

// Group categories by type
$grouped = [];
$typeLabels = [
    'notice' => 'Notices',
    'teacher' => 'Faculty',
    'gallery' => 'Gallery',
    'resource' => 'Resources'
];
foreach ($categories as $cat) {
    $grouped[$cat['type']][] = $cat;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — CST Admin</title>
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

        <?php $activePage = 'categories'; ?>
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
                    <h2>Categories</h2>
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
                    <h3><?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?></h3>
                    <?php if ($editCategory): ?>
                        <a href="categories.php" class="btn btn-sm btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="categories.php" style="max-width:560px;">
                        <?php if ($editCategory): ?>
                            <input type="hidden" name="edit_id" value="<?php echo (int)$editCategory['id']; ?>">
                        <?php endif; ?>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div style="grid-column:span 2;">
                                <label class="form-label">Name <span style="color:#EF4444;">*</span></label>
                                <input type="text" name="name" class="form-input" placeholder="Category name" value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="notice" <?php echo (($editCategory['type'] ?? '') === 'notice') ? 'selected' : ''; ?>>Notice</option>
                                    <option value="teacher" <?php echo (($editCategory['type'] ?? '') === 'teacher') ? 'selected' : ''; ?>>Faculty</option>
                                    <option value="gallery" <?php echo (($editCategory['type'] ?? '') === 'gallery') ? 'selected' : ''; ?>>Gallery</option>
                                    <option value="resource" <?php echo (($editCategory['type'] ?? '') === 'resource') ? 'selected' : ''; ?>>Resource</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?php echo (($editCategory['status'] ?? 1) == 1) ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo (($editCategory['status'] ?? 1) == 0) ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:20px;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <?php echo $editCategory ? 'Update Category' : 'Add Category'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="admin-card" style="margin-top:20px;">
                <div class="admin-card-header">
                    <h3>All Categories</h3>
                    <span style="color:#64748B;font-size:13px;"><?php echo count($categories); ?> total</span>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($categories)): ?>
                        <div style="padding:48px 24px;text-align:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                            <p style="color:#94A3B8;font-size:14px;">No categories found.</p>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <?php foreach ($typeLabels as $typeKey => $typeLabel):
                            if (empty($grouped[$typeKey])) continue;
                        ?>
                        <div style="margin-bottom:24px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding-bottom:8px;border-bottom:2px solid #E2E8F0;">
                                <?php
                                $typeColors = ['notice' => '#2563EB', 'teacher' => '#16A34A', 'gallery' => '#EA580C', 'resource' => '#9333EA'];
                                $typeColor = $typeColors[$typeKey] ?? '#64748B';
                                ?>
                                <span style="display:inline-block;width:4px;height:18px;background:<?php echo $typeColor; ?>;border-radius:2px;"></span>
                                <h4 style="font-size:14px;font-weight:600;color:#1E293B;margin:0;"><?php echo htmlspecialchars($typeLabel); ?></h4>
                                <span style="font-size:12px;color:#94A3B8;background:#F1F5F9;padding:2px 8px;border-radius:10px;"><?php echo count($grouped[$typeKey]); ?></span>
                            </div>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th class="table-actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grouped[$typeKey] as $cat): ?>
                                    <tr>
                                        <td style="font-weight:500;"><?php echo htmlspecialchars($cat['name']); ?></td>
                                        <td style="color:#64748B;font-size:13px;font-family:monospace;"><?php echo htmlspecialchars($cat['slug']); ?></td>
                                        <td>
                                            <span class="badge" style="background:<?php echo $typeColor; ?>22;color:<?php echo $typeColor; ?>;"><?php echo htmlspecialchars(ucfirst($cat['type'])); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($cat['status'])): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-actions">
                                            <a href="categories.php?action=edit&id=<?php echo (int)$cat['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                            <a href="categories.php?action=delete&id=<?php echo (int)$cat['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endforeach; ?>
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
    </script>
</body>
</html>
