<?php
require_once '../includes/config.php';
requireAdmin();

$item = null;
$isEdit = false;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $isEdit = true;
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        setFlash('error', 'Photo not found.');
        header('Location: gallery.php');
        exit;
    }
}

// Fetch categories for gallery
$catStmt = $pdo->prepare("SELECT id, name FROM categories WHERE type = 'gallery' AND status = 1 ORDER BY name ASC");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $oldImage = $_POST['existing_image'] ?? '';

    // Validate
    if (empty($title)) {
        $error = 'Title is required.';
    } elseif (!$isEdit && (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK)) {
        $error = 'Image is required.';
    } else {
        // Generate slug if empty
        if (empty($slug)) {
            $slug = createSlug($title);
        }

        // Handle image upload
        $imagePath = $oldImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadFile($_FILES['image'], 'gallery', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($uploaded) {
                // Delete old image if replacing
                if (!empty($oldImage) && $oldImage !== $uploaded) {
                    deleteFile($oldImage);
                }
                $imagePath = $uploaded;
            }
        }

        try {
            if ($isEdit) {
                $stmt = $pdo->prepare('
                    UPDATE gallery SET
                        title = ?, slug = ?, category_id = ?, description = ?,
                        image = ?, status = ?
                    WHERE id = ?
                ');
                $stmt->execute([$title, $slug, $category_id, $description, $imagePath, $status, $id]);
                setFlash('success', 'Photo updated successfully.');
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO gallery (title, slug, category_id, description, image, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([$title, $slug, $category_id, $description, $imagePath, $status]);
                setFlash('success', 'Photo created successfully.');
            }
            header('Location: gallery.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'A photo with this slug already exists. Please change the title or slug.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));
$pageTitle = $isEdit ? 'Edit Photo' : 'Add New Photo';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> — CST Admin</title>
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

        <?php $activePage = 'gallery-edit'; ?>
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
                    <h2><?php echo $pageTitle; ?></h2>
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

            <!-- Form -->
            <div class="admin-card">
                <div class="admin-card-body with-padding">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
                        <!-- Title -->
                        <div class="form-group">
                            <label class="form-label" for="title">Title <span style="color:#EF4444;">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" required
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ($item['title'] ?? '')); ?>"
                                placeholder="Enter photo title">
                        </div>

                        <!-- Slug -->
                        <div class="form-group">
                            <label class="form-label" for="slug">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['slug'] ?? ($item['slug'] ?? '')); ?>"
                                placeholder="auto-generated-from-title">
                            <p class="form-hint">Auto-generated from title. Edit if needed.</p>
                        </div>

                        <!-- Category -->
                        <div class="form-group">
                            <label class="form-label" for="category">Category</label>
                            <select id="category" name="category_id" class="form-control">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo (int)$cat['id']; ?>"
                                        <?php echo (($_POST['category_id'] ?? ($item['category_id'] ?? '')) == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                placeholder="Enter photo description"><?php echo htmlspecialchars($_POST['description'] ?? ($item['description'] ?? '')); ?></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="form-group">
                            <label class="form-label">Image <span style="color:#EF4444;">*</span></label>
                            <div class="file-upload" id="fileUploadArea" onclick="document.getElementById('image').click();">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p>Click to upload or drag an image</p>
                                <p style="font-size:12px;color:#94A3B8;">JPG, PNG, GIF, WebP &mdash; <?php echo $isEdit ? 'Leave empty to keep current image' : 'Required for new photos'; ?></p>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" <?php echo !$isEdit ? 'required' : ''; ?>>
                            <img id="imgPreview" class="img-preview" alt="Preview">
                            <?php if ($isEdit && !empty($item['image'])): ?>
                                <div style="margin-top:12px;">
                                    <p class="form-hint">Current image:</p>
                                    <img src="<?php echo htmlspecialchars(UPLOAD_URL . '/' . $item['image']); ?>" alt="Current" style="max-width:200px;border-radius:8px;border:1px solid #E2E8F0;margin-top:4px;">
                                </div>
                                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($item['image']); ?>">
                            <?php endif; ?>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="1" <?php echo (($_POST['status'] ?? ($item['status'] ?? 1)) == 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo (($_POST['status'] ?? ($item['status'] ?? 1)) == 0) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div style="display:flex;gap:12px;padding-top:8px;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Save Photo
                            </button>
                            <a href="gallery.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
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

        // Auto-generate slug from title on blur
        (function() {
            var titleInput = document.getElementById('title');
            var slugInput = document.getElementById('slug');

            titleInput.addEventListener('blur', function() {
                if (slugInput && titleInput.value.trim()) {
                    slugInput.value = titleInput.value.trim()
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/[\s_]+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');
                }
            });
        })();

        // Image preview
        (function() {
            var imageInput = document.getElementById('image');
            var preview = document.getElementById('imgPreview');

            if (imageInput && preview) {
                imageInput.addEventListener('change', function(e) {
                    var file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        var reader = new FileReader();
                        reader.onload = function(ev) {
                            preview.src = ev.target.result;
                            preview.classList.add('show');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        })();
    </script>
</body>
</html>
