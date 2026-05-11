<?php
/**
 * CST Department Website - Notice Listing Page
 * Core PHP + MySQL with PDO
 */

require_once 'includes/config.php';

$pageTitle = 'Notices - ' . SITE_NAME;

// ─── Settings ───
$siteName      = siteSetting('site_name', 'CST Department');
$siteTagline   = siteSetting('site_tagline', 'Department of Computer Science & Technology');
$sitePhone     = siteSetting('site_phone', '');
$siteEmail     = siteSetting('site_email', '');
$siteAddress   = siteSetting('site_address', '');
$siteLogo      = siteSetting('site_logo', '');
$siteDesc      = siteSetting('site_description', '');
$facebookUrl   = siteSetting('facebook_url', '#');
$twitterUrl    = siteSetting('twitter_url', '#');
$linkedinUrl   = siteSetting('linkedin_url', '#');
$youtubeUrl    = siteSetting('youtube_url', '#');
$footerText    = siteSetting('footer_text', '&copy; ' . date('Y') . ' CST Department. All Rights Reserved.');

// ─── Helper: Notice tag class by category name ───
function noticeTagClass($catName) {
    $name = strtolower($catName);
    if (strpos($name, 'important') !== false || strpos($name, 'urgent') !== false) return 'important';
    if (strpos($name, 'academic') !== false) return 'academic';
    if (strpos($name, 'event') !== false) return 'event';
    if (strpos($name, 'exam') !== false) return 'exam';
    return 'general';
}

// ─── Helper: Month abbreviation from date string ───
function monthAbbr($dateStr) {
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $dt = new DateTime($dateStr);
    return $months[(int) $dt->format('m') - 1];
}

// ─── Fetch Notice Categories ───
$noticeCategories = [];
try {
    $stmt = safeQuery($pdo, "SELECT * FROM categories WHERE type = 'notice' AND status = 1 ORDER BY name ASC");
    if ($stmt) {
        $noticeCategories = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $noticeCategories = [];
}

// ─── Pagination Setup ───
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 8;

// ─── Fetch Total Active Notices for Pagination ───
$totalNotices = 0;
try {
    $countStmt = safeQuery($pdo, "SELECT COUNT(*) AS total FROM notices WHERE status = 1");
    if ($countStmt) {
        $countRow = $countStmt->fetch();
        $totalNotices = (int) $countRow['total'];
    }
} catch (Exception $e) {
    $totalNotices = 0;
}

$pagination = getPagination($currentPage, $totalNotices, $perPage);

// ─── Fetch Active Notices with Category ───
$notices = [];
try {
    $stmt = safeQuery($pdo, "SELECT n.*, c.name AS category_name FROM notices n LEFT JOIN categories c ON n.category_id = c.id WHERE n.status = 1 ORDER BY n.created_at DESC LIMIT :offset, :per_page", [
        ':offset' => $pagination['offset'],
        ':per_page' => $pagination['per_page']
    ]);
    if ($stmt) {
        $notices = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $notices = [];
}

// ─── Build Pagination Query String ───
function buildPageUrl($page, $currentQuery = '') {
    $params = $_GET;
    $params['page'] = $page;
    return SITE_URL . '/notice.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo clean($siteDesc); ?>">
    <title><?php echo clean($pageTitle); ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">

    <!-- Notice Page Styles -->
    <style>
        /* ─── Notice Cards ─── */
        .notice-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 860px;
            margin: 0 auto;
        }

        .notice-card {
            display: flex;
            gap: 20px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 20px;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
            position: relative;
        }

        .notice-card:hover {
            border-color: #2563EB;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.08);
        }

        .notice-card .important-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #DC2626;
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .notice-card .important-badge svg {
            width: 12px;
            height: 12px;
        }

        .notice-date {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            background: #2563EB;
            color: #FFFFFF;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .notice-date .day {
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
        }

        .notice-date .month {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .notice-content {
            flex: 1;
            min-width: 0;
        }

        .notice-content .notice-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 6px;
            text-transform: capitalize;
        }

        .notice-tag.important {
            background: #FEE2E2;
            color: #DC2626;
        }

        .notice-tag.academic {
            background: #DBEAFE;
            color: #2563EB;
        }

        .notice-tag.exam {
            background: #FFF7ED;
            color: #EA580C;
        }

        .notice-tag.event {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .notice-tag.general {
            background: #F1F5F9;
            color: #64748B;
        }

        .notice-content h3 {
            font-size: 17px;
            font-weight: 600;
            margin: 0 0 6px 0;
            line-height: 1.4;
            color: #1E293B;
        }

        .notice-content h3 a {
            color: #1E293B;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .notice-content h3 a:hover {
            color: #2563EB;
        }

        .notice-content p {
            font-size: 14px;
            color: #64748B;
            line-height: 1.6;
            margin: 0 0 8px 0;
        }

        .notice-content .card-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: #94A3B8;
        }

        .notice-content .card-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .notice-content .card-meta svg {
            width: 12px;
            height: 12px;
        }

        /* ─── Pagination ─── */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 36px;
            flex-wrap: wrap;
        }

        .pagination-wrapper a,
        .pagination-wrapper span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 10px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            color: #475569;
            background: #FFFFFF;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination-wrapper a:hover {
            border-color: #2563EB;
            color: #2563EB;
        }

        .pagination-wrapper .active {
            background: #2563EB;
            border-color: #2563EB;
            color: #FFFFFF;
        }

        .pagination-wrapper .disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        .pagination-wrapper .dots {
            border: none;
            background: transparent;
            pointer-events: none;
        }

        .pagination-info {
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
            color: #94A3B8;
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

<!-- ============================================
     HEADER
     ============================================ -->
<header class="header">

    <!-- Header Top Bar -->
    <div class="header-top">
        <div class="container">
            <div class="top-left">
                <?php if ($sitePhone): ?>
                    <a href="tel:<?php echo clean($sitePhone); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?php echo clean($sitePhone); ?>
                    </a>
                <?php endif; ?>
                <?php if ($siteEmail): ?>
                    <a href="mailto:<?php echo clean($siteEmail); ?>" style="margin-left:16px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <?php echo clean($siteEmail); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="top-right">
                <?php if ($facebookUrl && $facebookUrl !== '#'): ?>
                    <a href="<?php echo clean($facebookUrl); ?>" target="_blank" rel="noopener" aria-label="Facebook">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ($twitterUrl && $twitterUrl !== '#'): ?>
                    <a href="<?php echo clean($twitterUrl); ?>" target="_blank" rel="noopener" aria-label="Twitter">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 22.36.36 9 9 0 0 1 18.94 2a4.49 4.49 0 0 0-7.66 4.09A12.76 12.76 0 0 1 3.2 2.27a4.49 4.49 0 0 0 1.39 6.01A4.47 4.47 0 0 1 2.58 7.7v.06a4.49 4.49 0 0 0 3.6 4.4 4.47 4.47 0 0 1-2.02.08 4.49 4.49 0 0 0 4.19 3.12A9 9 0 0 1 1 17.54a12.72 12.72 0 0 0 6.9 2.02c8.28 0 12.8-6.86 12.8-12.8 0-.2 0-.4-.01-.6A9.14 9.14 0 0 0 23 3z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ($linkedinUrl && $linkedinUrl !== '#'): ?>
                    <a href="<?php echo clean($linkedinUrl); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ($youtubeUrl && $youtubeUrl !== '#'): ?>
                    <a href="<?php echo clean($youtubeUrl); ?>" target="_blank" rel="noopener" aria-label="YouTube">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.13C5.12 19.56 12 19.56 12 19.56s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33zM9.75 15.02V8.48l5.75 3.27-5.75 3.27z"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navbar -->
    <div class="nav-container">
        <div class="nav-wrapper">
            <!-- Brand -->
            <a href="<?php echo SITE_URL; ?>" class="nav-brand">
                <?php if ($siteLogo && file_exists(UPLOAD_PATH . '/' . $siteLogo)): ?>
                    <img src="<?php echo UPLOAD_URL . '/' . clean($siteLogo); ?>" alt="<?php echo clean($siteName); ?> Logo">
                <?php else: ?>
                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none" style="background:#2563EB;border-radius:10px;padding:8px;">
                        <path d="M12 14h6v6h-6zM12 24h6v6h-6zM22 14h6v6h-6zM26 24h2v2h-2z" fill="#fff"/>
                        <rect x="10" y="12" width="20" height="20" rx="2" stroke="#fff" stroke-width="2" fill="none"/>
                    </svg>
                <?php endif; ?>
                <div class="brand-text">
                    <span class="brand-name"><?php echo clean($siteName); ?></span>
                    <span class="brand-tagline"><?php echo clean($siteTagline); ?></span>
                </div>
            </a>

            <!-- Navigation Links -->
            <ul class="nav-links">
                <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php">About</a></li>
                <li><a href="<?php echo SITE_URL; ?>/faculty.php">Faculty</a></li>
                <li><a href="<?php echo SITE_URL; ?>/notice.php" class="active">Notices</a></li>
                <li><a href="<?php echo SITE_URL; ?>/gallery.php">Gallery</a></li>
                <li><a href="<?php echo SITE_URL; ?>/resources.php">Resources</a></li>
                <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
            </ul>

            <!-- Mobile Toggle -->
            <button class="mobile-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <svg class="menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
        </div>
    </div>
</header>

<!-- ============================================
     PAGE BANNER
     ============================================ -->
<section class="page-banner">
    <div class="container">
        <h1>Notices &amp; Announcements</h1>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Home
            </a>
            <span class="separator">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
            <span>Notices</span>
        </div>
    </div>
</section>

<!-- ============================================
     NOTICES SECTION
     ============================================ -->
<section class="section">
    <div class="container">

        <!-- Section Header -->
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Stay Informed
            </div>
            <h2 class="section-title">Notices &amp; Announcements</h2>
            <p class="section-desc">Important announcements, academic updates, and upcoming events from the department.</p>
        </div>

        <?php if (!empty($notices)): ?>

            <!-- Filter Tabs -->
            <?php if (!empty($noticeCategories)): ?>
            <div class="filter-tabs" id="notice-filter-tabs" style="margin-bottom:30px;">
                <button class="filter-tab active" data-category="all">All</button>
                <?php foreach ($noticeCategories as $cat): ?>
                    <button class="filter-tab" data-category="<?php echo clean($cat['name']); ?>"><?php echo clean($cat['name']); ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Notice List -->
            <div class="notice-list" id="notice-list">
                <?php foreach ($notices as $notice):
                    $catName   = !empty($notice['category_name']) ? $notice['category_name'] : getCategoryName($pdo, $notice['category_id']);
                    $tagClass  = noticeTagClass($catName);
                    $dateObj   = new DateTime($notice['created_at']);
                    $dayNum    = $dateObj->format('d');
                    $monthStr  = monthAbbr($notice['created_at']);
                    $isImportant = (strtolower($catName) === 'important' || strpos(strtolower($catName), 'important') !== false);
                ?>
                    <div class="notice-card" data-category="<?php echo clean($catName); ?>">
                        <?php if ($isImportant): ?>
                            <span class="important-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                Important
                            </span>
                        <?php endif; ?>
                        <div class="notice-date">
                            <div class="day"><?php echo clean($dayNum); ?></div>
                            <div class="month"><?php echo clean($monthStr); ?></div>
                        </div>
                        <div class="notice-content">
                            <span class="notice-tag <?php echo clean($tagClass); ?>"><?php echo clean($catName); ?></span>
                            <h3>
                                <a href="<?php echo SITE_URL; ?>/notice-details.php?slug=<?php echo clean($notice['slug']); ?>">
                                    <?php echo clean($notice['title']); ?>
                                </a>
                            </h3>
                            <?php if (!empty($notice['content'])): ?>
                                <p><?php echo clean(mb_substr(strip_tags($notice['content']), 0, 120)) . (mb_strlen(strip_tags($notice['content'])) > 120 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            <div class="card-meta">
                                <span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <?php echo timeAgo($notice['created_at']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total'] > 1): ?>
            <div class="pagination-wrapper">
                <!-- Previous -->
                <?php if ($pagination['current'] > 1): ?>
                    <a href="<?php echo buildPageUrl($pagination['current'] - 1); ?>" aria-label="Previous page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </a>
                <?php else: ?>
                    <span class="disabled" aria-label="Previous page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                    $startPage = max(1, $pagination['current'] - 2);
                    $endPage   = min($pagination['total'], $pagination['current'] + 2);

                    if ($startPage > 1) {
                        echo '<a href="' . buildPageUrl(1) . '">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="dots">...</span>';
                        }
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i === $pagination['current']) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="' . buildPageUrl($i) . '">' . $i . '</a>';
                        }
                    }

                    if ($endPage < $pagination['total']) {
                        if ($endPage < $pagination['total'] - 1) {
                            echo '<span class="dots">...</span>';
                        }
                        echo '<a href="' . buildPageUrl($pagination['total']) . '">' . $pagination['total'] . '</a>';
                    }
                ?>

                <!-- Next -->
                <?php if ($pagination['current'] < $pagination['total']): ?>
                    <a href="<?php echo buildPageUrl($pagination['current'] + 1); ?>" aria-label="Next page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php else: ?>
                    <span class="disabled" aria-label="Next page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                <?php endif; ?>
            </div>
            <div class="pagination-info">
                Showing <?php echo $pagination['offset'] + 1; ?> &ndash; <?php echo min($pagination['offset'] + $pagination['per_page'], $pagination['total_items']); ?> of <?php echo $pagination['total_items']; ?> notices
            </div>
            <?php endif; ?>

        <?php else: ?>

            <!-- Empty State -->
            <div class="empty-state" style="margin-top: -10px;">
                <lottie-player
                    src="<?php echo SITE_URL; ?>/assets/lottie/not-found.json"
                    background="transparent"
                    speed="1"
                    style="width: 240px; height: 240px; display: block; margin: 0 auto; margin-bottom: 0;"
                    loop
                    autoplay>
                </lottie-player>
                <h3>No Notices Yet</h3>
                <p>There are no notices published at the moment. Please check back later.</p>
            </div>

        <?php endif; ?>

    </div>
</section>

<!-- ============================================
     FOOTER
     ============================================ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">

            <!-- Column 1: About -->
            <div class="footer-col">
                <div class="nav-brand" style="margin-bottom:14px;">
                    <?php if ($siteLogo && file_exists(UPLOAD_PATH . '/' . $siteLogo)): ?>
                        <img src="<?php echo UPLOAD_URL . '/' . clean($siteLogo); ?>" alt="<?php echo clean($siteName); ?> Logo">
                    <?php else: ?>
                        <svg width="40" height="40" viewBox="0 0 44 44" fill="none" style="background:#2563EB;border-radius:10px;padding:8px;">
                            <path d="M12 14h6v6h-6zM12 24h6v6h-6zM22 14h6v6h-6zM26 24h2v2h-2z" fill="#fff"/>
                            <rect x="10" y="12" width="20" height="20" rx="2" stroke="#fff" stroke-width="2" fill="none"/>
                        </svg>
                    <?php endif; ?>
                    <div class="brand-text">
                        <span class="brand-name"><?php echo clean($siteName); ?></span>
                        <span class="brand-tagline"><?php echo clean($siteTagline); ?></span>
                    </div>
                </div>
                <p><?php echo clean($siteDesc) ?: 'Official website of the Department of Computer Science & Technology. Committed to academic excellence and innovation.'; ?></p>
                <div class="footer-social">
                    <?php if ($facebookUrl && $facebookUrl !== '#'): ?>
                        <a href="<?php echo clean($facebookUrl); ?>" target="_blank" rel="noopener" aria-label="Facebook">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($twitterUrl && $twitterUrl !== '#'): ?>
                        <a href="<?php echo clean($twitterUrl); ?>" target="_blank" rel="noopener" aria-label="Twitter">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 22.36.36 9 9 0 0 1 18.94 2a4.49 4.49 0 0 0-7.66 4.09A12.76 12.76 0 0 1 3.2 2.27a4.49 4.49 0 0 0 1.39 6.01A4.47 4.47 0 0 1 2.58 7.7v.06a4.49 4.49 0 0 0 3.6 4.4 4.47 4.47 0 0 1-2.02.08 4.49 4.49 0 0 0 4.19 3.12A9 9 0 0 1 1 17.54a12.72 12.72 0 0 0 6.9 2.02c8.28 0 12.8-6.86 12.8-12.8 0-.2 0-.4-.01-.6A9.14 9.14 0 0 0 23 3z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($linkedinUrl && $linkedinUrl !== '#'): ?>
                        <a href="<?php echo clean($linkedinUrl); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($youtubeUrl && $youtubeUrl !== '#'): ?>
                        <a href="<?php echo clean($youtubeUrl); ?>" target="_blank" rel="noopener" aria-label="YouTube">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.13C5.12 19.56 12 19.56 12 19.56s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33zM9.75 15.02V8.48l5.75 3.27-5.75 3.27z"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Home
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        About Us
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/faculty.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Faculty
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Notices
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/gallery.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Gallery
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Contact
                    </a></li>
                </ul>
            </div>

            <!-- Column 3: Resources -->
            <div class="footer-col">
                <h4>Resources</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/resources.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Lecture Notes
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/resources.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        E-Books
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/resources.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Software
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Exam Schedule
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Academic Calendar
                    </a></li>
                </ul>
            </div>

            <!-- Column 4: Contact Info -->
            <div class="footer-col">
                <h4>Contact Info</h4>
                <ul class="footer-links">
                    <?php if ($siteAddress): ?>
                    <li>
                        <a href="javascript:void(0)" style="cursor:default;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?php echo clean($siteAddress); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($sitePhone): ?>
                    <li>
                        <a href="tel:<?php echo clean($sitePhone); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <?php echo clean($sitePhone); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($siteEmail): ?>
                    <li>
                        <a href="mailto:<?php echo clean($siteEmail); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <?php echo clean($siteEmail); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <?php echo $footerText; ?>
        </div>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

<!-- Notice Filter Script -->
<script>
(function() {
    'use strict';
    var filterTabs = document.querySelectorAll('#notice-filter-tabs .filter-tab');
    var noticeCards = document.querySelectorAll('#notice-list .notice-card');

    if (filterTabs.length && noticeCards.length) {
        filterTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                filterTabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                var category = this.getAttribute('data-category');

                noticeCards.forEach(function(card) {
                    var cardCategory = card.getAttribute('data-category');
                    if (category === 'all' || cardCategory === category) {
                        card.style.display = '';
                        card.classList.remove('hidden');
                    } else {
                        card.style.display = 'none';
                        card.classList.add('hidden');
                    }
                });
            });
        });
    }
})();
</script>

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
