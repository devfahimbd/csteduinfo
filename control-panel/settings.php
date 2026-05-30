<?php
require_once '../includes/config.php';
requireAdmin();

// Handle POST - update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settingsMap = [
        'site_name'        => trim($_POST['site_name'] ?? ''),
        'site_tagline'     => trim($_POST['site_tagline'] ?? ''),
        'site_description' => trim($_POST['site_description'] ?? ''),
        'site_phone'       => trim($_POST['site_phone'] ?? ''),
        'site_email'       => trim($_POST['site_email'] ?? ''),
        'site_address'     => trim($_POST['site_address'] ?? ''),
        'facebook_url'     => trim($_POST['facebook_url'] ?? ''),
        'twitter_url'      => trim($_POST['twitter_url'] ?? ''),
        'linkedin_url'     => trim($_POST['linkedin_url'] ?? ''),
        'youtube_url'      => trim($_POST['youtube_url'] ?? ''),
        'footer_text'      => trim($_POST['footer_text'] ?? ''),
    ];

    // Handle logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $logo = uploadFile($_FILES['site_logo'], 'logo', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        if ($logo) {
            // Delete old logo
            $oldLogo = siteSetting('site_logo');
            if ($oldLogo) {
                deleteFile($oldLogo);
            }
            $settingsMap['site_logo'] = $logo;
        }
    }

    // Handle favicon upload
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
        $favicon = uploadFile($_FILES['site_favicon'], 'favicon', ['ico', 'png', 'jpg', 'jpeg', 'gif', 'svg']);
        if ($favicon) {
            // Delete old favicon
            $oldFavicon = siteSetting('site_favicon');
            if ($oldFavicon) {
                deleteFile($oldFavicon);
            }
            $settingsMap['site_favicon'] = $favicon;
        }
    }

    // Upsert each setting
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value2, updated_at = NOW()');
    foreach ($settingsMap as $key => $value) {
        $stmt->execute([':key' => $key, ':value' => $value, ':value2' => $value]);
    }

    setFlash('success', 'Settings updated successfully.');
    header('Location: settings.php');
    exit;
}

// Fetch all settings
$stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
$allSettings = [];
while ($row = $stmt->fetch()) {
    $allSettings[$row['setting_key']] = $row['setting_value'];
}

$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));

// Helper to get setting
function getSetting($key) {
    global $allSettings;
    return isset($allSettings[$key]) ? $allSettings[$key] : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সেটিংস - অ্যাডমিন প্যানেল</title>
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

        <?php $activePage = 'settings'; ?>
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
                    <h2>Settings</h2>
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

            <form method="POST" action="settings.php" enctype="multipart/form-data">

                <!-- General Settings -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            General
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:640px;">
                            <div style="grid-column:span 2;">
                                <label class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-input" value="<?php echo htmlspecialchars(getSetting('site_name')); ?>">
                            </div>
                            <div style="grid-column:span 2;">
                                <label class="form-label">Site Tagline</label>
                                <input type="text" name="site_tagline" class="form-input" value="<?php echo htmlspecialchars(getSetting('site_tagline')); ?>">
                            </div>
                            <div style="grid-column:span 2;">
                                <label class="form-label">Site Description</label>
                                <textarea name="site_description" class="form-input" rows="3"><?php echo htmlspecialchars(getSetting('site_description')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Settings -->
                <div class="admin-card" style="margin-top:20px;">
                    <div class="admin-card-header">
                        <h3>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            Contact Information
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:640px;">
                            <div>
                                <label class="form-label">Phone</label>
                                <input type="text" name="site_phone" class="form-input" value="<?php echo htmlspecialchars(getSetting('site_phone')); ?>">
                            </div>
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="site_email" class="form-input" value="<?php echo htmlspecialchars(getSetting('site_email')); ?>">
                            </div>
                            <div style="grid-column:span 2;">
                                <label class="form-label">Address</label>
                                <input type="text" name="site_address" class="form-input" value="<?php echo htmlspecialchars(getSetting('site_address')); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo Settings -->
                <div class="admin-card" style="margin-top:20px;">
                    <div class="admin-card-header">
                        <h3>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            Logo &amp; Favicon
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:640px;">
                            <div>
                                <label class="form-label">Site Logo</label>
                                <?php $currentLogo = getSetting('site_logo'); ?>
                                <?php if (!empty($currentLogo)): ?>
                                    <div style="margin-bottom:10px;">
                                        <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($currentLogo); ?>" alt="Current logo" style="max-height:48px;width:auto;object-fit:contain;border:1px solid #E2E8F0;border-radius:4px;padding:4px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="site_logo" class="form-input" accept="image/*" id="logoInput">
                                <div id="logoPreview" style="margin-top:8px;"></div>
                            </div>
                            <div>
                                <label class="form-label">Favicon</label>
                                <?php $currentFavicon = getSetting('site_favicon'); ?>
                                <?php if (!empty($currentFavicon)): ?>
                                    <div style="margin-bottom:10px;">
                                        <img src="<?php echo UPLOAD_URL . '/' . htmlspecialchars($currentFavicon); ?>" alt="Current favicon" style="height:32px;width:32px;object-fit:contain;border:1px solid #E2E8F0;border-radius:4px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="site_favicon" class="form-input" accept=".ico,.png,.jpg,.jpeg,.gif,.svg" id="faviconInput">
                                <div id="faviconPreview" style="margin-top:8px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Settings -->
                <div class="admin-card" style="margin-top:20px;">
                    <div class="admin-card-header">
                        <h3>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                            Social Media
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:640px;">
                            <div>
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="facebook_url" class="form-input" value="<?php echo htmlspecialchars(getSetting('facebook_url')); ?>">
                            </div>
                            <div>
                                <label class="form-label">Twitter URL</label>
                                <input type="url" name="twitter_url" class="form-input" value="<?php echo htmlspecialchars(getSetting('twitter_url')); ?>">
                            </div>
                            <div>
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" name="linkedin_url" class="form-input" value="<?php echo htmlspecialchars(getSetting('linkedin_url')); ?>">
                            </div>
                            <div>
                                <label class="form-label">YouTube URL</label>
                                <input type="url" name="youtube_url" class="form-input" value="<?php echo htmlspecialchars(getSetting('youtube_url')); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Settings -->
                <div class="admin-card" style="margin-top:20px;">
                    <div class="admin-card-header">
                        <h3>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                            Footer
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <div style="max-width:640px;">
                            <label class="form-label">Footer Text</label>
                            <input type="text" name="footer_text" class="form-input" value="<?php echo htmlspecialchars(getSetting('footer_text')); ?>">
                            <p style="color:#94A3B8;font-size:12px;margin-top:6px;">You can use HTML in the footer text.</p>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div style="margin-top:24px;padding-bottom:24px;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Save Settings
                    </button>
                </div>

            </form>

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

            // Logo preview
            var logoInput = document.getElementById('logoInput');
            var logoPreview = document.getElementById('logoPreview');
            if (logoInput) {
                logoInput.addEventListener('change', function() {
                    logoPreview.innerHTML = '';
                    if (this.files && this.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.cssText = 'max-height:48px;width:auto;object-fit:contain;border:1px solid #E2E8F0;border-radius:4px;padding:4px;';
                            logoPreview.appendChild(img);
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Favicon preview
            var faviconInput = document.getElementById('faviconInput');
            var faviconPreview = document.getElementById('faviconPreview');
            if (faviconInput) {
                faviconInput.addEventListener('change', function() {
                    faviconPreview.innerHTML = '';
                    if (this.files && this.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.cssText = 'height:32px;width:32px;object-fit:contain;border:1px solid #E2E8F0;border-radius:4px;';
                            faviconPreview.appendChild(img);
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        })();
    </script>
</body>
</html>
