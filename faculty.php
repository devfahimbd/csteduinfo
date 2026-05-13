<?php
/**
 * CST Department Website - Faculty Page
 * Core PHP + MySQL with PDO
 */

require_once 'includes/config.php';

$pageTitle = 'Faculty - ' . SITE_NAME;

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

// ─── Fetch Teacher Categories ───
$teacherCategories = [];
try {
    $stmt = safeQuery($pdo, "SELECT * FROM categories WHERE type = 'teacher' AND status = 1 ORDER BY name ASC");
    if ($stmt) {
        $teacherCategories = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $teacherCategories = [];
}

// ─── Fetch All Active Teachers ───
$teachers = [];
try {
    $stmt = safeQuery($pdo, "SELECT t.*, c.name AS category_name FROM teachers t LEFT JOIN categories c ON t.category_id = c.id WHERE t.status = 1 ORDER BY t.sort_order DESC");
    if ($stmt) {
        $teachers = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $teachers = [];
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
                <li><a href="<?php echo SITE_URL; ?>"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>Home</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><span>About</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/faculty.php" class="active"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Faculty</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/notice.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span>Notices</span></a></li>
                <li class="nav-more">
                    <a href="javascript:void(0)"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg><span>More</span><svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></a>
                    <div class="nav-dropdown">
                    <a href="<?php echo SITE_URL; ?>/gallery.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Gallery</span></a>
                    <a href="<?php echo SITE_URL; ?>/resources.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg><span>Resources</span></a>
                    <a href="<?php echo SITE_URL; ?>/result.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>Result</span></a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo SITE_URL; ?>/contact.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>Contact</span></a>
                    </div>
                </li>
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
        <h1>Our Faculty</h1>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Home
            </a>
            <span class="separator">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
            <span>Faculty</span>
        </div>
    </div>
</section>

<!-- ============================================
     FACULTY SECTION
     ============================================ -->
<section class="section">
    <div class="container">

        <!-- Section Header -->
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Our Team
            </div>
            <h2 class="section-title">Meet Our Faculty</h2>
            <p class="section-desc">Dedicated educators and researchers guiding the next generation of technology leaders.</p>
        </div>

        <?php if (!empty($teachers)): ?>

            <!-- Filter Tabs -->
            <?php if (!empty($teacherCategories)): ?>
            <div class="filter-tabs" id="faculty-filter-tabs">
                <button class="filter-tab active" data-category="all">All</button>
                <?php foreach ($teacherCategories as $cat): ?>
                    <button class="filter-tab" data-category="<?php echo clean($cat['slug']); ?>"><?php echo clean($cat['name']); ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Faculty Grid -->
            <div class="grid-3" id="faculty-grid">
                <?php foreach ($teachers as $teacher): ?>
                    <?php
                        $teacherSlug  = createSlug($teacher['name']) . '-' . $teacher['id'];
                        $catSlug      = !empty($teacher['category_name']) ? createSlug($teacher['category_name']) : '';
                    ?>
                    <div class="teacher-card" data-category="<?php echo clean($catSlug); ?>">
                        <div class="teacher-img-wrap">
                            <?php if ($teacher['image'] && file_exists(UPLOAD_PATH . '/' . $teacher['image'])): ?>
                                <img src="<?php echo UPLOAD_URL . '/' . clean($teacher['image']); ?>" alt="<?php echo clean($teacher['name']); ?>">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode(clean($teacher['name'])); ?>&amp;background=DBEAFE&amp;color=2563EB&amp;size=300&amp;bold=true" alt="<?php echo clean($teacher['name']); ?>">
                            <?php endif; ?>
                            <div class="teacher-social">
                                <?php if (!empty($teacher['facebook'])): ?>
                                    <a href="<?php echo clean($teacher['facebook']); ?>" target="_blank" rel="noopener" aria-label="Facebook">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($teacher['linkedin'])): ?>
                                    <a href="<?php echo clean($teacher['linkedin']); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="teacher-info">
                            <h3><?php echo clean($teacher['name']); ?></h3>
                            <?php if (!empty($teacher['designation'])): ?>
                                <p class="designation"><?php echo clean($teacher['designation']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($teacher['qualification'])): ?>
                                <p class="qualification"><?php echo clean($teacher['qualification']); ?></p>
                            <?php endif; ?>
                            <div style="margin-top:12px;">
                                <a href="<?php echo SITE_URL; ?>/teacher-details.php?slug=<?php echo clean($teacherSlug); ?>" class="btn btn-sm btn-outline">
                                    View Profile
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

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
                <h3>Faculty Coming Soon</h3>
                <p>Our faculty profiles are being prepared. Please check back shortly.</p>
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

<!-- Faculty Filter Script -->
<script>
(function() {
    'use strict';
    var filterTabs = document.querySelectorAll('#faculty-filter-tabs .filter-tab');
    var teacherCards = document.querySelectorAll('#faculty-grid .teacher-card');

    if (filterTabs.length && teacherCards.length) {
        filterTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                // Update active tab
                filterTabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                var category = this.getAttribute('data-category');

                teacherCards.forEach(function(card) {
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
