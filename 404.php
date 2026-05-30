<?php
require_once 'includes/config.php';

$pageTitle = 'পৃষ্ঠা খুঁজে পাওয়া যায়নি - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="icon" href="<?php echo siteSetting('site_favicon', 'assets/images/favicon.png'); ?>" type="image/png">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <style>
        .error-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 200px);
            text-align: center;
            padding: 60px 20px;
        }
        .error-page lottie-player {
            margin-bottom: 0px;
        }
        .error-page .error-code {
            font-size: 48px;
            font-weight: 800;
            color: var(--text, #1E293B);
            letter-spacing: -1px;
        }
        .error-page h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text, #1E293B);
            margin-bottom: 8px;
        }
        .error-page p {
            font-size: 15px;
            color: var(--text-secondary, #64748B);
            max-width: 400px;
            margin-bottom: 28px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
        <lottie-player
            src="<?php echo SITE_URL; ?>/assets/lottie/loading.json"
            background="transparent"
            speed="1"
            loop
            autoplay>
        </lottie-player>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="top-left">
                    <a href="tel:<?php echo siteSetting('site_phone'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?php echo clean(siteSetting('site_phone')); ?>
                    </a>
                    <a href="mailto:<?php echo siteSetting('site_email'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <?php echo clean(siteSetting('site_email')); ?>
                    </a>
                </div>
                <div class="top-right">
                    <?php if (siteSetting('facebook_url')): ?>
                    <a href="<?php echo clean(siteSetting('facebook_url')); ?>" target="_blank">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="nav-container">
            <div class="nav-wrapper">
                <a href="<?php echo SITE_URL; ?>/" class="nav-brand">
                    <?php $logo = siteSetting('site_logo'); if ($logo): ?>
                        <img src="<?php echo SITE_URL . '/' . $logo; ?>" alt="<?php echo SITE_NAME; ?>">
                    <?php else: ?>
                        <div style="width:44px;height:44px;background:#2563EB;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="brand-text">
                        <span class="brand-name"><?php echo SITE_NAME; ?></span>
                        <span class="brand-tagline"><?php echo SITE_TAGLINE; ?></span>
                    </div>
                </a>
                <ul class="nav-links" id="navLinks">
                    <li><a href="<?php echo SITE_URL; ?>/"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>হোম</span></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><span>সম্পর্কে</span></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/faculty.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>শিক্ষকমণ্ডলী</span></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span>নোটিশ</span></a></li>
                    <li class="nav-more">
                        <a href="javascript:void(0)"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg><span>আরও</span><svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></a>
                        <div class="nav-dropdown">
                        <a href="<?php echo SITE_URL; ?>/gallery.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>গ্যালারি</span></a>
                        <a href="<?php echo SITE_URL; ?>/resources.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg><span>রিসোর্স</span></a>
                        <a href="<?php echo SITE_URL; ?>/result.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>ফলাফল</span></a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/contact.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>যোগাযোগ</span></a>
                        </div>
                    </li>
                    </ul>
                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- 404 Content -->
    <main class="error-page">
        <lottie-player
            src="<?php echo SITE_URL; ?>/assets/lottie/error-404.json"
            background="transparent"
            speed="1"
            style="width: 340px; height: 340px; display: block; margin: 0 auto; margin-bottom: 0;"
            loop
            autoplay>
        </lottie-player>
        <p>উপস! আপনি যে পৃষ্ঠাটি খুঁজছেন সেটি বিদ্যমান নেই।</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="<?php echo SITE_URL; ?>/" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                হোমে যান
            </a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-secondary">যোগাযোগ করুন</a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <?php echo siteSetting('footer_text', '&copy; ' . date('Y') . ' সিএসটি বিভাগ। সর্বস্বত্ব সংরক্ষিত।'); ?>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        window.addEventListener("load", function() {
            var overlay = document.getElementById("loadingOverlay");
            if (overlay) {
                overlay.classList.add("loaded");
                setTimeout(function() { overlay.remove(); }, 600);
            }
        });
        setTimeout(function() {
            var overlay = document.getElementById("loadingOverlay");
            if (overlay && !overlay.classList.contains("loaded")) {
                overlay.classList.add("loaded");
                setTimeout(function() { overlay.remove(); }, 600);
            }
        }, 3000);
    </script>
</body>
</html>

// update at 2026-05-17 13:23:44

// update at 2026-05-17 13:23:50
