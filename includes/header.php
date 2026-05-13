<?php
// ============================================
// CST Department - Header
// ============================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Page-level SEO variables (set before including this file)
$page_title       = $page_title ?? '';
$page_description = $page_description ?? '';
$page_image       = $page_image ?? '';

// Determine active page from the current script or request URI
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Nav items with label, slug, and icon
$navItems = [
    ['label' => 'হোম',      'slug' => 'index',     'icon' => 'home'],
    ['label' => 'সম্পর্কে',     'slug' => 'about',     'icon' => 'info'],
    ['label' => 'শিক্ষকমণ্ডলী',   'slug' => 'faculty',   'icon' => 'users'],
    ['label' => 'নোটিশ',   'slug' => 'notice',    'icon' => 'bell'],
    ['label' => 'রিসোর্স', 'slug' => 'resources', 'icon' => 'book'],
    ['label' => 'গ্যালারি',   'slug' => 'gallery',   'icon' => 'image'],
    ['label' => 'যোগাযোগ',   'slug' => 'contact',   'icon' => 'mail'],
];

// Helper: check if a nav item is active
function isNavItemActive($slug, $currentPage) {
    if ($slug === 'index' && ($currentPage === 'index' || $currentPage === '')) {
        return true;
    }
    if ($slug !== 'index' && strpos($currentPage, $slug) === 0) {
        return true;
    }
    // Also check REQUEST_URI for sub-paths (e.g. /notice/some-notice)
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if ($slug !== 'index' && strpos($requestUri, '/' . $slug . '/') !== false) {
        return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <?= generateMetaTags($page_title, $page_description, $page_image); ?>

    <?php if (($favicon = siteFavicon())): ?>
    <link rel="icon" href="<?= sanitize($favicon) ?>" type="image/png">
    <?php endif; ?>

    <!-- Google Fonts: Hind Siliguri -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">

    <?php if (!empty($extra_css)): ?>
    <style><?= $extra_css ?></style>
    <?php endif; ?>
</head>
<body>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
        <lottie-player
            src="<?= SITE_URL ?>/assets/lottie/loading.json"
            background="transparent"
            speed="1"
            loop
            autoplay>
        </lottie-player>
    </div>

    <!-- Sticky Navbar -->
    <header class="navbar">
        <div class="container navbar-inner">

            <!-- Logo -->
            <a href="<?= SITE_URL ?>/" class="navbar-brand">
                <?php if (($logo = siteLogo())): ?>
                    <img src="<?= sanitize($logo) ?>" alt="<?= sanitize(siteName()) ?>" class="navbar-logo-img">
                <?php else: ?>
                    <span class="navbar-logo-icon">
                        <?= icon('code', 28) ?>
                    </span>
                <?php endif; ?>
                <span class="navbar-brand-text"><?= sanitize(siteName()) ?></span>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu">
                <?= icon('menu', 24) ?>
            </button>

            <!-- Navigation Links -->
            <nav class="nav-menu" id="navMenu">
                <?php foreach ($navItems as $item): ?>
                    <?php
                        $isActive = isNavItemActive($item['slug'], $currentPage);
                        $href = ($item['slug'] === 'index')
                            ? SITE_URL . '/'
                            : SITE_URL . '/' . $item['slug'];
                        $activeClass = $isActive ? ' active' : '';
                    ?>
                    <a href="<?= $href ?>" class="nav-link<?= $activeClass ?>">
                        <span class="nav-link-icon"><?= icon($item['icon'], 18) ?></span>
                        <span class="nav-link-label"><?= $item['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

        </div>
    </header>

    <!-- Inline loading overlay & toggle script -->
    <script>
        (function () {
            // Hide loading overlay once page is ready
            window.addEventListener('load', function () {
                var overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    overlay.classList.add('loaded');
                    setTimeout(function () { overlay.remove(); }, 600);
                }
            });
            // Fallback: hide after 3 seconds
            setTimeout(function () {
                var overlay = document.getElementById('loadingOverlay');
                if (overlay && !overlay.classList.contains('loaded')) {
                    overlay.classList.add('loaded');
                    setTimeout(function () { overlay.remove(); }, 600);
                }
            }, 3000);

            // Mobile nav toggle
            var toggle = document.getElementById('navToggle');
            var menu   = document.getElementById('navMenu');
            if (toggle && menu) {
                toggle.addEventListener('click', function () {
                    menu.classList.toggle('open');
                    toggle.classList.toggle('open');
                });

                // Close menu when clicking outside
                document.addEventListener('click', function (e) {
                    if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                        menu.classList.remove('open');
                        toggle.classList.remove('open');
                    }
                });
            }
        })();
    </script>
