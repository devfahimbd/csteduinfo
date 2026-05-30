<?php
require_once '../includes/config.php';
requireAdmin();

$resource = null;
$isEdit = false;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $isEdit = true;
    $stmt = $pdo->prepare('SELECT * FROM resources WHERE id = ?');
    $stmt->execute([$id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$resource) {
        setFlash('error', 'Resource not found.');
        header('Location: resources.php');
        exit;
    }
}

// Fetch categories for resources
$catStmt = $pdo->prepare("SELECT id, name FROM categories WHERE type = 'resource' AND status = 1 ORDER BY name ASC");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $external_url = trim($_POST['external_url'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $oldFile = $_POST['existing_file'] ?? '';

    // Validate
    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        // Generate slug if empty
        if (empty($slug)) {
            $slug = createSlug($title);
        }

        // Handle file upload
        $filePath = $oldFile;
        $originalFileName = $_POST['existing_file_name'] ?? '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadFile($_FILES['file'], 'resources', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt', 'csv']);
            if ($uploaded) {
                // Delete old file if replacing
                if (!empty($oldFile) && $oldFile !== $uploaded) {
                    deleteFile($oldFile);
                }
                $filePath = $uploaded;
                $originalFileName = $_FILES['file']['name'];
            }
        }

        try {
            if ($isEdit) {
                $stmt = $pdo->prepare('
                    UPDATE resources SET
                        title = ?, slug = ?, category_id = ?, description = ?,
                        file_path = ?, external_url = ?, status = ?, file_name = ?
                    WHERE id = ?
                ');
                $stmt->execute([$title, $slug, $category_id, $description, $filePath, $external_url, $status, $originalFileName, $id]);
                setFlash('success', 'Resource updated successfully.');
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO resources (title, slug, category_id, description, file_path, external_url, status, file_name)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([$title, $slug, $category_id, $description, $filePath, $external_url, $status, $originalFileName]);
                setFlash('success', 'Resource created successfully.');
            }
            header('Location: resources.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'A resource with this slug already exists. Please change the title or slug.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));
$pageTitle = $isEdit ? 'Edit Resource' : 'Add New Resource';
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

        <?php $activePage = 'resource-edit'; ?>
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
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ($resource['title'] ?? '')); ?>"
                                placeholder="Enter resource title">
                        </div>

                        <!-- Slug -->
                        <div class="form-group">
                            <label class="form-label" for="slug">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['slug'] ?? ($resource['slug'] ?? '')); ?>"
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
                                        <?php echo (($_POST['category_id'] ?? ($resource['category_id'] ?? '')) == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                placeholder="Enter resource description"><?php echo htmlspecialchars($_POST['description'] ?? ($resource['description'] ?? '')); ?></textarea>
                        </div>

                        <!-- File Upload -->
                        <div class="form-group">
                            <label class="form-label" for="file">File Upload</label>
                            <input type="file" id="file" name="file" class="form-control"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.csv">
                            <p class="form-hint">PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, RAR, TXT, CSV</p>
                            <?php if ($isEdit && !empty($resource['file_path'])): ?>
                                <div style="margin-top:8px;padding:10px 14px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;display:flex;align-items:center;gap:10px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    <span style="font-size:13px;color:#334155;word-break:break-all;"><?php echo htmlspecialchars(!empty($resource['file_name']) ? $resource['file_name'] : basename($resource['file_path'])); ?></span>
                                    <input type="hidden" name="existing_file" value="<?php echo htmlspecialchars($resource['file_path']); ?>">
                                    <input type="hidden" name="existing_file_name" value="<?php echo htmlspecialchars($resource['file_name'] ?? ''); ?>">
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- External URL -->
                        <div class="form-group">
                            <label class="form-label" for="external_url">External URL</label>
                            <input type="text" id="external_url" name="external_url" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['external_url'] ?? ($resource['external_url'] ?? '')); ?>"
                                placeholder="https://example.com/resource">
                            <p class="form-hint">Optional. Provide an external link instead of or in addition to a file.</p>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="1" <?php echo (($_POST['status'] ?? ($resource['status'] ?? 1)) == 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo (($_POST['status'] ?? ($resource['status'] ?? 1)) == 0) ? 'selected' : ''; ?>>Inactive</option>
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
                                Save Resource
                            </button>
                            <a href="resources.php" class="btn btn-secondary">Cancel</a>
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
    </script>
</body>
</html>
