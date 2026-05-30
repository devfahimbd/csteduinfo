<?php
/**
 * পলিটেকনিক শিক্ষা বাংলাদেশ - কৃতজ্ঞতা
 * Credits / Thanks Page
 */

require_once 'includes/config.php';

$pageTitle = 'কৃতজ্ঞতা - ' . SITE_NAME;

// ─── Settings ───
$siteName      = siteSetting('site_name', 'পলিটেকনিক শিক্ষা বাংলাদেশ');
$siteTagline   = siteSetting('site_tagline', 'বাংলাদেশের পলিটেকনিক শিক্ষা তথ্য পোর্টাল');
$sitePhone     = siteSetting('site_phone', '');
$siteEmail     = siteSetting('site_email', '');
$siteAddress   = siteSetting('site_address', '');
$siteLogo      = siteSetting('site_logo', '');
$siteDesc      = siteSetting('site_description', 'বাংলাদেশের পলিটেকনিক শিক্ষার তথ্য পোর্টাল');
$facebookUrl   = siteSetting('facebook_url', '#');
$twitterUrl    = siteSetting('twitter_url', '#');
$linkedinUrl   = siteSetting('linkedin_url', '#');
$youtubeUrl    = siteSetting('youtube_url', '#');
$footerText    = siteSetting('footer_text', '&copy; ' . date('Y') . ' পলিটেকনিক শিক্ষা বাংলাদেশ। সর্বস্বত্ব সংরক্ষিত।');

// ─── Fetch Credits ───
$credits = [];
try {
    $stmt = safeQuery($pdo, "SELECT * FROM credits WHERE status = 1 ORDER BY section ASC, sort_order ASC");
    if ($stmt) $credits = $stmt->fetchAll();
} catch (Exception $e) {
    $credits = [];
}

// Group credits by section
$groupedCredits = [];
foreach ($credits as $credit) {
    $section = !empty($credit['section']) ? $credit['section'] : 'সাধারণ';
    if (!isset($groupedCredits[$section])) $groupedCredits[$section] = [];
    $groupedCredits[$section][] = $credit;
}

// Section icon/color mapping
$sectionColors = [
    'অর্থ সহযোগিতা'     => ['color' => '#2563EB', 'class' => 'blue',   'icon' => 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
    'তথ্য সহযোগিতা'     => ['color' => '#10B981', 'class' => 'green',  'icon' => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z'],
    'টেকনোলজি সহযোগিতা' => ['color' => '#7C3AED', 'class' => 'purple', 'icon' => 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'],
    'বিশেষ সহযোগিতা'    => ['color' => '#F59E0B', 'class' => 'orange', 'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
];
$defaultSectionStyle = ['color' => '#2563EB', 'class' => 'blue', 'icon' => 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z'];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo clean($siteDesc); ?>">
    <title><?php echo clean($pageTitle); ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">

    <!-- Credits Page Specific Styles -->
    <style>
        /* ─── Credit Card ─── */
        .credit-card {
            background: #fff;
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #F1F5F9;
            position: relative;
            overflow: hidden;
        }
        .credit-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08), 0 8px 16px rgba(0,0,0,0.04);
        }
        .credit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent, #2563EB);
            border-radius: 16px 16px 0 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .credit-card:hover::before {
            opacity: 1;
        }

        /* ─── Credit Avatar ─── */
        .credit-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 16px;
            border: 3px solid #E2E8F0;
            transition: border-color 0.3s ease, transform 0.3s ease;
            background: #F8FAFC;
        }
        .credit-card:hover .credit-avatar {
            border-color: var(--accent, #2563EB);
            transform: scale(1.05);
        }
        .credit-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* ─── Credit Card Content ─── */
        .credit-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1E293B;
            margin: 0 0 6px;
            line-height: 1.4;
        }
        .credit-card .credit-role {
            font-size: 13px;
            color: #64748B;
            margin: 0 0 4px;
            line-height: 1.5;
        }
        .credit-card .credit-desc {
            font-size: 13px;
            color: #94A3B8;
            margin: 0 0 14px;
            line-height: 1.6;
        }

        /* ─── Credit Social Links ─── */
        .credit-socials {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 14px;
        }
        .credit-socials a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #F1F5F9;
            color: #64748B;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .credit-socials a:hover {
            background: var(--accent, #2563EB);
            color: #fff;
            transform: translateY(-2px);
        }
        .credit-socials a svg {
            width: 14px;
            height: 14px;
        }

        /* ─── Credit Website Button ─── */
        .credit-url-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 600;
            color: var(--accent, #2563EB);
            background: color-mix(in srgb, var(--accent, #2563EB) 8%, transparent);
            border: 1px solid color-mix(in srgb, var(--accent, #2563EB) 20%, transparent);
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .credit-url-btn:hover {
            background: var(--accent, #2563EB);
            color: #fff;
            transform: translateY(-1px);
        }
        .credit-url-btn svg {
            width: 12px;
            height: 12px;
        }

        /* ─── Section Heading ─── */
        .credit-section-heading {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 48px;
            margin-bottom: 24px;
            padding-bottom: 14px;
            border-bottom: 2px solid #E5E7EB;
        }
        .credit-section-heading:first-child {
            margin-top: 0;
        }
        .credit-section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            flex-shrink: 0;
        }
        .credit-section-icon svg {
            width: 20px;
            height: 20px;
        }
        .credit-section-heading h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1E293B;
            margin: 0;
            flex: 1;
        }
        .credit-section-count {
            font-size: 12px;
            font-weight: 600;
            color: #94A3B8;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 4px 12px;
            border-radius: 20px;
        }

        /* ─── Empty State ─── */
        .credits-empty {
            text-align: center;
            padding: 60px 20px;
            max-width: 500px;
            margin: 0 auto;
        }
        .credits-empty h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1E293B;
            margin: 0 0 8px;
        }
        .credits-empty p {
            font-size: 15px;
            color: #64748B;
            line-height: 1.6;
        }

        /* ─── Credits Intro Section ─── */
        .credits-intro {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 20px;
        }
        .credits-intro p {
            font-size: 15px;
            color: #64748B;
            line-height: 1.8;
            margin: 0;
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .credit-card {
                padding: 24px 20px;
            }
            .credit-avatar {
                width: 70px;
                height: 70px;
            }
            .credit-section-heading {
                margin-top: 32px;
            }
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
            <a href="<?php echo SITE_URL; ?>/" class="nav-brand">
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
                <li><a href="<?php echo SITE_URL; ?>/"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>হোম</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><span>সম্পর্কে</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/faculty.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>পলিটেকনিক সূমহ</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/notice.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span>নোটিশ</span></a></li>
                <li class="nav-more">
                    <a href="javascript:void(0)"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg><span>আরও</span><svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></a>
                    <div class="nav-dropdown">
                    <a href="<?php echo SITE_URL; ?>/gallery.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>গ্যালারি</span></a>
                    <a href="<?php echo SITE_URL; ?>/resources.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg><span>রিসোর্স</span></a>
                    <a href="<?php echo SITE_URL; ?>/result.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>ফলাফল</span></a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo SITE_URL; ?>/contact.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>যোগাযোগ</span></a>
                    <a href="<?php echo SITE_URL; ?>/credits.php" class="active"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg><span>কৃতজ্ঞতা</span></a>
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
        <h1>কৃতজ্ঞতা</h1>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>/">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                হোম
            </a>
            <span class="separator">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
            <span>কৃতজ্ঞতা</span>
        </div>
    </div>
</section>

<!-- ============================================
     CREDITS CONTENT SECTION
     ============================================ -->
<section class="section">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                স্বীকৃতি ও সম্মান
            </div>
            <h2 class="section-title">কৃতজ্ঞতা ও স্বীকৃতি</h2>
            <p class="section-desc">আমাদের প্রকল্পে সহায়তা প্রদানকারী ব্যক্তি ও প্রতিষ্ঠানকে আন্তরিক ধন্যবাদ।</p>
        </div>

        <!-- Intro Text -->
        <div class="credits-intro">
            <p>পলিটেকনিক শিক্ষা বাংলাদেশ পোর্টালটি তৈরি ও রক্ষণাবেক্ষণে যারা বিভিন্নভাবে সহাযোগিতা করেছেন তাদের প্রতি আমাদের গভীর কৃতজ্ঞতা। নিচে তাদের তালিকা দেওয়া হলো।</p>
        </div>

        <?php if (!empty($groupedCredits)): ?>
            <?php $sectionIndex = 0; ?>
            <?php foreach ($groupedCredits as $sectionName => $sectionCredits): ?>
                <?php
                    $sectionStyle = $sectionColors[$sectionName] ?? $defaultSectionStyle;
                    $accentColor  = $sectionStyle['color'];
                    $accentClass  = $sectionStyle['class'];
                    $accentIcon   = $sectionStyle['icon'];
                ?>

                <!-- Section Group Heading -->
                <div class="credit-section-heading">
                    <div class="credit-section-icon" style="background: <?php echo $accentColor; ?>15; color: <?php echo $accentColor; ?>;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo $accentIcon; ?>"/></svg>
                    </div>
                    <h3><?php echo clean($sectionName); ?></h3>
                    <span class="credit-section-count"><?php echo count($sectionCredits); ?>টি</span>
                </div>

                <!-- Credit Cards Grid -->
                <div class="grid-4">
                    <?php foreach ($sectionCredits as $credit): ?>
                        <?php
                            $cardAccent = $accentColor;
                            $hasImage = !empty($credit['image']) && file_exists(UPLOAD_PATH . '/' . $credit['image']);
                            $initials = mb_substr($credit['name'] ?? 'N/A', 0, 1, 'UTF-8');
                        ?>
                        <div class="credit-card" style="--accent: <?php echo $cardAccent; ?>;">
                            <!-- Avatar / Logo -->
                            <?php if ($hasImage): ?>
                                <div class="credit-avatar">
                                    <img src="<?php echo UPLOAD_URL . '/' . clean($credit['image']); ?>" alt="<?php echo clean($credit['name']); ?>" loading="lazy">
                                </div>
                            <?php else: ?>
                                <div class="credit-avatar" style="background: <?php echo $cardAccent; ?>12; border-color: <?php echo $cardAccent; ?>30; display:flex; align-items:center; justify-content:center;">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($credit['name'] ?? 'N/A'); ?>&background=<?php echo substr($cardAccent, 1); ?>&color=fff&font-size=0.35&bold=true" alt="<?php echo clean($credit['name']); ?>" loading="lazy" onerror="this.style.display='none';this.parentElement.innerHTML='<span style=&quot;font-size:28px;font-weight:700;color:<?php echo $cardAccent; ?>&quot;><?php echo $initials; ?></span>';">
                                </div>
                            <?php endif; ?>

                            <!-- Name -->
                            <h3><?php echo clean($credit['name']); ?></h3>

                            <!-- Role / Company -->
                            <?php if (!empty($credit['role'])): ?>
                                <p class="credit-role"><?php echo clean($credit['role']); ?></p>
                            <?php endif; ?>

                            <!-- Description -->
                            <?php if (!empty($credit['description'])): ?>
                                <p class="credit-desc"><?php echo clean($credit['description']); ?></p>
                            <?php endif; ?>

                            <!-- Social Links -->
                            <?php
                                $hasSocial = !empty($credit['facebook']) || !empty($credit['twitter']) || !empty($credit['linkedin']) || !empty($credit['website']);
                            ?>
                            <?php if ($hasSocial): ?>
                                <div class="credit-socials">
                                    <?php if (!empty($credit['website'])): ?>
                                        <a href="<?php echo clean($credit['website']); ?>" target="_blank" rel="noopener" title="ওয়েবসাইট">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($credit['facebook'])): ?>
                                        <a href="<?php echo clean($credit['facebook']); ?>" target="_blank" rel="noopener" title="Facebook">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($credit['twitter'])): ?>
                                        <a href="<?php echo clean($credit['twitter']); ?>" target="_blank" rel="noopener" title="Twitter">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 22.36.36 9 9 0 0 1 18.94 2a4.49 4.49 0 0 0-7.66 4.09A12.76 12.76 0 0 1 3.2 2.27a4.49 4.49 0 0 0 1.39 6.01A4.47 4.47 0 0 1 2.58 7.7v.06a4.49 4.49 0 0 0 3.6 4.4 4.47 4.47 0 0 1-2.02.08 4.49 4.49 0 0 0 4.19 3.12A9 9 0 0 1 1 17.54a12.72 12.72 0 0 0 6.9 2.02c8.28 0 12.8-6.86 12.8-12.8 0-.2 0-.4-.01-.6A9.14 9.14 0 0 0 23 3z"/></svg>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($credit['linkedin'])): ?>
                                        <a href="<?php echo clean($credit['linkedin']); ?>" target="_blank" rel="noopener" title="LinkedIn">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Website / URL Button (legacy support) -->
                            <?php if (!empty($credit['url']) && empty($credit['website'])): ?>
                                <a href="<?php echo clean($credit['url']); ?>" target="_blank" rel="noopener" class="credit-url-btn">
                                    ওয়েবসাইট দেখুন
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="credits-empty">
                <lottie-player
                    src="https://lottie.host/d6a883b8-1c16-4eb3-89eb-6c7e2495370f/DGRs98M9VI.json"
                    background="transparent"
                    speed="1"
                    loop
                    autoplay
                    style="width:240px;height:240px;display:block;margin:0 auto 24px;">
                </lottie-player>
                <h3>কৃতজ্ঞতা তালিকা শীঘ্রই আসছে</h3>
                <p>কৃতজ্ঞতা তালিকা প্রস্তুত করা হচ্ছে। অনুগ্রহ করে শীঘ্রই আবার দেখুন।</p>
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
                <p><?php echo clean($siteDesc) ?: 'বাংলাদেশের পলিটেকনিক শিক্ষার তথ্য পোর্টাল। একাডেমিক উৎকর্ষ এবং উদ্ভাবনের প্রতি প্রতিশ্রুতিবদ্ধ।'; ?></p>
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
                <h4>দ্রুত লিংক</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        হোম
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        সম্পর্কে
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/polytechnics.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        পলিটেকনিক সূমহ
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        নোটিশ
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/credits.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        কৃতজ্ঞতা
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        যোগাযোগ
                    </a></li>
                </ul>
            </div>

            <!-- Column 3: Resources -->
            <div class="footer-col">
                <h4>রিসোর্স</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/resources.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        লেকচার নোট
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/resources.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        ই-বুক
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/resources.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        সফটওয়্যার
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        পরীক্ষার সূচি
                    </a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        একাডেমিক ক্যালেন্ডার
                    </a></li>
                </ul>
            </div>

            <!-- Column 4: Contact Info -->
            <div class="footer-col">
                <h4>যোগাযোগের তথ্য</h4>
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
