<?php
require_once 'includes/config.php';

$pageTitle = 'Page Not Found - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                <a href="<?php echo SITE_URL; ?>" class="nav-brand">
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
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php">About</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/faculty.php">Faculty</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php">Notices</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/gallery.php">Gallery</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/resources.php">Resources</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/result.php">Result</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
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
        <p>Sorry, the page you are looking for doesn't exist or has been moved.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Go Home
            </a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-secondary">Contact Us</a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <?php echo siteSetting('footer_text', '&copy; ' . date('Y') . ' CST Department. All Rights Reserved.'); ?>
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
