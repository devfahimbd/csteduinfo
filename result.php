<?php
/**
 * CST Department Website - Result Search Page
 * Self-contained page matching about.php design pattern
 */

require_once 'includes/config.php';

$pageTitle = 'Result - ' . SITE_NAME;

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

// ─── Regulation Years ───
try {
    $regYears = $pdo->query("SELECT DISTINCT regulation_year FROM result_batches WHERE status = 'completed' ORDER BY regulation_year DESC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $regYears = [];
}

// ─── Semesters ───
$semesterOrder = ["1st Semester","2nd Semester","3rd Semester","4th Semester","5th Semester","6th Semester","7th Semester","8th Semester"];
try {
    $allSemesters = $pdo->query("SELECT DISTINCT semester FROM result_batches WHERE status = 'completed' ORDER BY FIELD(semester, '1st Semester','2nd Semester','3rd Semester','4th Semester','5th Semester','6th Semester','7th Semester','8th Semester')")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $allSemesters = [];
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

    <style>
        /* ─── Result Hero (replaces the gradient hero) ─── */
        .result-hero-section {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0;
            overflow: visible;
        }

        .result-hero-inner {
            position: relative;
            padding: 30px 0 70px;
            text-align: center;
            background: linear-gradient(135deg, #1E40AF 0%, var(--primary) 50%, #3B82F6 100%);
            overflow: hidden;
        }

        .result-hero-inner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .result-hero-inner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            border-radius: 50%;
        }

        .result-hero-inner h1 {
            font-size: 36px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 12px;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
        }

        .result-hero-inner p {
            font-size: 16px;
            color: rgba(255,255,255,0.8);
            margin: 0;
            position: relative;
            z-index: 1;
            max-width: 540px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ─── Search Card ─── */
        .result-search-card {
            max-width: 720px;
            margin: -50px auto 0;
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05);
            position: relative;
            z-index: 10;
            border: 1px solid var(--border);
        }

        .result-search-card h2 {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 6px;
            color: var(--text);
        }

        .result-search-card .subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0 0 24px;
        }

        .result-search-card .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .result-search-card .form-group select,
        .result-search-card .form-group input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 15px;
            font-family: var(--font);
            transition: border-color 0.2s, box-shadow 0.2s;
            background: var(--bg);
            color: var(--text);
        }

        .result-search-card .form-group select:focus,
        .result-search-card .form-group input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
            background: var(--white);
        }

        .result-search-card .form-group select:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            background: var(--bg-alt);
        }

        .btn-search {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: var(--font);
        }

        .btn-search:hover {
            background: var(--primary-hover);
            color: var(--white);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.4);
        }

        .btn-search:active { transform: translateY(0); }
        .btn-search:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .btn-search svg { flex-shrink: 0; }

        /* ─── Result Loading ─── */
        .result-loading { display: none; text-align: center; padding: 60px 20px; }
        .result-loading.active { display: block; }
        .result-loading p { color: var(--text-secondary); font-size: 14px; margin-top: 16px; }

        /* ─── Not Found ─── */
        .result-not-found { display: none; text-align: center; padding: 60px 20px; }
        .result-not-found.active { display: block; }
        .result-not-found h3 { font-size: 20px; font-weight: 700; color: var(--text); margin: 16px 0 8px; }
        .result-not-found p { color: var(--text-secondary); font-size: 15px; max-width: 400px; margin: 0 auto; line-height: 1.6; }

        /* ─── Result Container ─── */
        .result-container { display: none; max-width: 900px; margin: 0 auto; padding: 40px 20px 60px; }
        .result-container.active { display: block; }

        /* ─── Semester Tabs ─── */
        .semester-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
        .semester-tab {
            padding: 10px 20px;
            background: var(--bg-alt);
            border: 2px solid transparent;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #475569;
            font-family: var(--font);
        }
        .semester-tab:hover { background: var(--border); }
        .semester-tab.active { background: var(--primary-lighter); border-color: var(--primary); color: var(--primary); }
        .semester-tab .tab-count {
            display: inline-block;
            background: var(--border);
            color: var(--text-secondary);
            padding: 1px 8px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 6px;
        }
        .semester-tab.active .tab-count { background: var(--primary); color: var(--white); }

        /* ─── Student Info Card ─── */
        .student-info-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 28px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .info-item { display: flex; flex-direction: column; gap: 4px; }
        .info-item .label { font-size: 12px; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; }
        .info-item .value { font-size: 16px; font-weight: 700; color: var(--text); }
        .info-item .value.passed { color: var(--green); }
        .info-item .value.failed { color: #DC2626; }
        .info-item .value.referred { color: #D97706; }
        .info-item .gpa-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #F0FDF4;
            color: var(--green);
            padding: 6px 14px;
            border-radius: var(--radius);
            font-size: 20px;
            font-weight: 800;
        }

        /* ─── Result Status Badge ─── */
        .result-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 700;
        }
        .result-badge.passed { background: #DCFCE7; color: #166534; }
        .result-badge.referred { background: #FEF3C7; color: #92400E; }
        .result-badge.failed { background: #FEE2E2; color: #991B1B; }

        /* ─── College Card ─── */
        .college-card {
            background: linear-gradient(135deg, #F0F9FF, #E0F2FE);
            border: 1px solid #BAE6FD;
            border-radius: var(--radius-xl);
            padding: 20px 28px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .college-card .icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .college-card .college-info { flex: 1; min-width: 200px; }
        .college-card .college-name { font-size: 16px; font-weight: 700; color: #0C4A6E; }
        .college-card .college-code { font-size: 13px; color: #0369A1; font-weight: 500; margin-top: 2px; }

        /* ─── Failed Subjects ─── */
        .failed-section {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 28px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .failed-section h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .failed-table { width: 100%; border-collapse: collapse; }
        .failed-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--bg);
            border-bottom: 2px solid var(--border);
        }
        .failed-table td { padding: 12px 14px; font-size: 14px; color: var(--text); border-bottom: 1px solid var(--border-light); }
        .failed-table tr:last-child td { border-bottom: none; }
        .failed-table .code-badge {
            display: inline-block;
            background: #FEF2F2;
            color: #991B1B;
            padding: 3px 10px;
            border-radius: 8px;
            font-family: var(--font);
            font-weight: 600;
            font-size: 13px;
        }
        .fail-type-tag { display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; }
        .fail-type-tag.T { background: #FEE2E2; color: #991B1B; }
        .fail-type-tag.P { background: #FCE7F3; color: #9D174D; }
        .fail-type-tag.TP { background: #F3E8FF; color: #7C3AED; }
        .no-fail-msg { text-align: center; padding: 24px; color: var(--green); font-weight: 600; font-size: 15px; }

        /* ─── Stats Section ─── */
        .stats-section { max-width: var(--max-width); margin: 0 auto; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); border-color: transparent; }
        .stat-card .stat-value { font-size: 32px; font-weight: 800; margin: 8px 0 4px; }
        .stat-card .stat-label { font-size: 13px; color: var(--text-secondary); font-weight: 500; }
        .stat-card .stat-icon-sm { width: 44px; height: 44px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; }

        .top-failed-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 28px;
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .top-failed-card h3 { font-size: 18px; font-weight: 700; color: var(--text); margin: 0 0 20px; }

        .fail-bar-item { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .fail-bar-item:last-child { margin-bottom: 0; }
        .fail-bar-rank {
            width: 28px;
            height: 28px;
            background: var(--bg-alt);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            flex-shrink: 0;
        }
        .fail-bar-rank.top { background: #FEE2E2; color: #DC2626; }
        .fail-bar-info { flex: 1; min-width: 0; }
        .fail-bar-name { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .fail-bar-name small { font-weight: 400; color: var(--text-light); }
        .fail-bar-track { width: 100%; height: 8px; background: var(--bg-alt); border-radius: 4px; overflow: hidden; }
        .fail-bar-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }
        .fail-bar-count { font-size: 14px; font-weight: 700; color: #DC2626; min-width: 60px; text-align: right; }

        .college-stats-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 28px;
            border: 1px solid var(--border);
            overflow-x: auto;
        }
        .college-stats-card h3 { font-size: 18px; font-weight: 700; color: var(--text); margin: 0 0 20px; }

        .cstats-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .cstats-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--bg);
            border-bottom: 2px solid var(--border);
        }
        .cstats-table td { padding: 12px 14px; font-size: 14px; border-bottom: 1px solid var(--border-light); }
        .cstats-table .pass-rate { font-weight: 700; }
        .cstats-table .rate-high { color: #16A34A; }
        .cstats-table .rate-mid { color: #D97706; }
        .cstats-table .rate-low { color: #DC2626; }

        /* ─── Spin animation ─── */
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .spin { animation: spin 1s linear infinite; }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .result-hero-inner { padding: 30px 16px 60px; }
            .result-hero-inner h1 { font-size: 26px; }
            .result-search-card { margin: -36px 16px 0; padding: 24px; border-radius: var(--radius-lg); }
            .result-search-card .form-row { grid-template-columns: 1fr !important; }
            .student-info-grid { grid-template-columns: 1fr 1fr; }
            .result-search-card .form-row-optional { grid-template-columns: 1fr 1fr !important; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 480px) {
            .student-info-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
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
                <li><a href="<?php echo SITE_URL; ?>"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>Home</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><span>About</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/faculty.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Faculty</span></a></li>
                <li><a href="<?php echo SITE_URL; ?>/notice.php"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span>Notices</span></a></li>
                <li class="nav-more">
                    <a href="javascript:void(0)"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg><span>More</span><svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></a>
                    <div class="nav-dropdown">
                    <a href="<?php echo SITE_URL; ?>/gallery.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Gallery</span></a>
                    <a href="<?php echo SITE_URL; ?>/resources.php"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg><span>Resources</span></a>
                    <a href="<?php echo SITE_URL; ?>/result.php" class="active"><svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>Result</span></a>
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
        <h1>Result</h1>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Home
            </a>
            <span class="separator">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
            <span>Result</span>
        </div>
    </div>
</section>

<!-- ============================================
     RESULT HERO + SEARCH
     ============================================ -->
<section class="result-hero-section">
    <div class="result-hero-inner">
        <div class="container">
            <h1>Diploma Engineering Result</h1>
            <p>Search your BTEB Diploma In Engineering exam results by roll number. Select regulation year and click search.</p>
        </div>
    </div>

    <!-- Search Card (overlapping hero) -->
    <div class="result-search-card">
        <h2>Search Your Result</h2>
        <p class="subtitle">Enter your roll number to find your result. Regulation and Semester are optional.</p>
        <form id="searchForm" onsubmit="return false;">
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                <div class="form-group">
                    <label>Regulation Year <span style="font-weight:400;color:var(--text-light);">(optional)</span></label>
                    <select id="regulationYear">
                        <?php if (empty($regYears)): ?>
                            <option value="">No data available</option>
                        <?php else: ?>
                            <option value="">All Regulations</option>
                            <?php foreach ($regYears as $yr): ?>
                                <option value="<?php echo htmlspecialchars($yr); ?>"><?php echo htmlspecialchars($yr); ?> Regulation</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Semester <span style="font-weight:400;color:var(--text-light);">(optional)</span></label>
                    <select id="semesterSelect">
                        <?php if (empty($allSemesters)): ?>
                            <option value="">No data available</option>
                        <?php else: ?>
                            <option value="">All Semesters</option>
                            <?php foreach ($allSemesters as $sem): ?>
                                <option value="<?php echo htmlspecialchars($sem); ?>"><?php echo htmlspecialchars($sem); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Program</label>
                    <select disabled>
                        <option selected>Diploma In Engineering</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label>Roll Number</label>
                <input type="text" id="rollInput" placeholder="Enter your roll number (e.g., 300010)" required autocomplete="off">
            </div>
            <button type="submit" class="btn-search" id="searchBtn" onclick="searchResult()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Search Result
            </button>
        </form>
    </div>
</section>

<!-- ============================================
     LOADING ANIMATION (hidden)
     ============================================ -->
<div class="result-loading" id="resultLoading">
    <lottie-player src="<?php echo SITE_URL; ?>/assets/lottie/loading.json" background="transparent" speed="1" loop autoplay style="width:180px;height:180px;margin:0 auto;"></lottie-player>
    <p>Searching your result...</p>
</div>

<!-- ============================================
     NOT FOUND (hidden)
     ============================================ -->
<div class="result-not-found" id="resultNotFound">
    <lottie-player src="<?php echo SITE_URL; ?>/assets/lottie/not-found.json" background="transparent" speed="1" style="width:280px;height:280px;margin:0 auto;" loop autoplay></lottie-player>
    <h3>No Result Found</h3>
    <p>We couldn't find any result for this roll number. Please check your roll number and regulation year and try again.</p>
</div>

<!-- ============================================
     RESULT DISPLAY (hidden)
     ============================================ -->
<div class="result-container" id="resultContainer">
    <div class="semester-tabs" id="semesterTabs"></div>
    <div id="resultContent"></div>
</div>

<!-- ============================================
     STATISTICS SECTION (hidden, loaded via AJAX)
     ============================================ -->
<section class="section section-alt" id="statsSection" style="display:none;">
    <div class="stats-section">
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
                Analytics
            </div>
            <h2 class="section-title">Result Statistics</h2>
            <p class="section-desc">Comprehensive analysis of examination results across all batches.</p>
        </div>
        <div id="statsContent"></div>
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
                    <li><a href="<?php echo SITE_URL; ?>/result.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Result
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

<script>
    // ─── Loading Overlay ───
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

    // ─── Auto load stats on page load ───
    window.addEventListener('load', function() { loadStats(); });

    // ─── Utility Functions ───
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function buildStatCard(color, bg, iconSvg, value, label) {
        return '<div class="stat-card">' +
            '<div class="stat-icon-sm" style="background:' + bg + ';color:' + color + ';">' +
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + iconSvg + '</svg>' +
            '</div>' +
            '<div class="stat-value" style="color:' + color + ';">' + value.toLocaleString() + '</div>' +
            '<div class="stat-label">' + label + '</div>' +
            '</div>';
    }

    // ─── Search Result ───
    function searchResult() {
        var roll = document.getElementById('rollInput').value.trim();
        var regYear = document.getElementById('regulationYear').value;
        var semester = document.getElementById('semesterSelect').value;

        if (!roll) {
            document.getElementById('rollInput').focus();
            return;
        }

        // Show loading
        document.getElementById('resultContainer').classList.remove('active');
        document.getElementById('resultNotFound').classList.remove('active');
        document.getElementById('resultLoading').classList.add('active');
        document.getElementById('searchBtn').disabled = true;
        document.getElementById('searchBtn').innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin"><path d="M21 12a9 9 0 11-6.219-8.56"></path></svg> Searching...';

        var formData = new FormData();
        formData.append('roll', roll);
        formData.append('regulation_year', regYear);
        formData.append('semester', semester);

        fetch('<?php echo SITE_URL; ?>/api/result-search.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            document.getElementById('resultLoading').classList.remove('active');
            document.getElementById('searchBtn').disabled = false;
            document.getElementById('searchBtn').innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> Search Result';

            if (data.success && data.data.length > 0) {
                window._searchResults = data.data;
                renderResults(data.data, roll);
                document.getElementById('resultContainer').classList.add('active');
                setTimeout(function() {
                    document.getElementById('resultContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            } else {
                document.getElementById('resultNotFound').classList.add('active');
            }
        })
        .catch(function() {
            document.getElementById('resultLoading').classList.remove('active');
            document.getElementById('searchBtn').disabled = false;
            document.getElementById('searchBtn').innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> Search Result';
            document.getElementById('resultNotFound').classList.add('active');
        });
    }

    // ─── Render Results with Semester Tabs ───
    function renderResults(results, roll) {
        var tabsContainer = document.getElementById('semesterTabs');
        var tabsHtml = '';
        var grouped = {};

        results.forEach(function(r) {
            var key = r.semester + '_' + r.exam_year;
            if (!grouped[key]) grouped[key] = r;
        });

        var keys = Object.keys(grouped);
        keys.forEach(function(key, i) {
            var r = grouped[key];
            var isActive = i === 0 ? ' active' : '';
            tabsHtml += '<button class="semester-tab' + isActive + '" onclick="showSemester(\'' + key.replace(/'/g, "\\'") + '\', this)">' +
                escapeHtml(r.semester) + '<span class="tab-count">' + escapeHtml(r.exam_year) + '</span></button>';
        });
        tabsContainer.innerHTML = tabsHtml;

        // Render first semester
        showSemester(keys[0]);
    }

    function showSemester(key, clickedTab) {
        // Update tabs
        document.querySelectorAll('.semester-tab').forEach(function(t) { t.classList.remove('active'); });
        if (clickedTab) {
            clickedTab.classList.add('active');
        } else {
            // Find first tab as active
            var tabs = document.querySelectorAll('.semester-tab');
            if (tabs.length > 0) tabs[0].classList.add('active');
        }

        if (!window._searchResults) return;

        var targetResult = null;
        window._searchResults.forEach(function(r) {
            var k = r.semester + '_' + r.exam_year;
            if (k === key) targetResult = r;
        });

        if (!targetResult) return;

        var html = renderStudentResult(targetResult);
        document.getElementById('resultContent').innerHTML = html;
    }

    function renderStudentResult(r) {
        var isPassed = r.result_type === 'passed';
        var isFailed4 = r.result_type === 'failed_4plus';
        var statusClass = isPassed ? 'passed' : (isFailed4 ? 'failed' : 'referred');
        var statusText = isPassed ? 'PASSED' : (isFailed4 ? 'FAILED (4+ Subjects)' : 'REFERRED');
        var statusIcon = isPassed ?
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' :
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';

        var html = '';

        // Student Info Card
        html += '<div class="student-info-card">';
        html += '<div class="student-info-grid">';
        html += '<div class="info-item"><span class="label">Roll Number</span><span class="value">' + escapeHtml(r.roll) + '</span></div>';
        html += '<div class="info-item"><span class="label">Result Status</span><span class="result-badge ' + statusClass + '">' + statusIcon + ' ' + statusText + '</span></div>';
        if (r.gpa !== null) {
            html += '<div class="info-item"><span class="label">GPA</span><span class="gpa-badge"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>' + r.gpa.toFixed(2) + '</span></div>';
        }
        if (!isPassed) {
            html += '<div class="info-item"><span class="label">Failed Subjects</span><span class="value failed">' + r.failed_subjects_count + ' Subject(s)</span></div>';
        }
        html += '<div class="info-item"><span class="label">Semester</span><span class="value">' + escapeHtml(r.semester) + '</span></div>';
        html += '<div class="info-item"><span class="label">Exam Year</span><span class="value">' + escapeHtml(r.exam_year) + '</span></div>';
        html += '</div></div>';

        // College Card
        html += '<div class="college-card">';
        html += '<div class="icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg></div>';
        html += '<div class="college-info">';
        html += '<div class="college-name">' + escapeHtml(r.college_name) + '</div>';
        html += '<div class="college-code">College Code: ' + escapeHtml(r.college_code) + '</div>';
        html += '</div>';
        html += '<div style="font-size:12px;color:#0369A1;font-weight:500;">' + escapeHtml(r.program) + '</div>';
        html += '<div style="font-size:12px;color:#0369A1;font-weight:500;">' + escapeHtml(r.regulation_year) + ' Regulation</div>';
        html += '</div>';

        // Failed Subjects Section
        html += '<div class="failed-section">';
        html += '<h3>';
        if (isPassed) {
            html += '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> All Subjects Cleared';
        } else {
            html += '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> Failed Subjects (' + r.failed_subjects_count + ')';
        }
        html += '</h3>';

        if (isPassed) {
            html += '<div class="no-fail-msg"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2" style="vertical-align:middle;margin-right:8px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Congratulations! Passed in all subjects.</div>';
        } else if (r.failed_subjects && r.failed_subjects.length > 0) {
            html += '<table class="failed-table"><thead><tr><th>#</th><th>Subject Code</th><th>Subject Name</th><th>Failed In</th><th>Full Form</th></tr></thead><tbody>';
            r.failed_subjects.forEach(function(fs, i) {
                // Backend now sends: code, fail_type, fail_type_label, subject_name, full_form
                // fail_type: "T", "P", or "TP" (normalized)
                // fail_type_label: "T", "P", or "T,P" (for display)
                // full_form: "Subject Name Theory Fail" or "Subject Name Theory & Practical Fail"

                var ft = fs.fail_type || 'T';
                var failTypeClass = ft === 'T' ? 'T' : (ft === 'P' ? 'P' : 'TP');
                var failTypeLabel = fs.fail_type_label || (ft === 'TP' || ft === 'PT' ? 'T,P' : ft);
                var subjectName = fs.subject_name || 'Unknown Subject';
                var fullForm = fs.full_form || subjectName + ' Theory Fail';

                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td><span class="code-badge">' + escapeHtml(fs.code) + '</span></td>';
                html += '<td>' + escapeHtml(subjectName) + '</td>';
                html += '<td><span class="fail-type-tag ' + failTypeClass + '">' + escapeHtml(failTypeLabel) + '</span></td>';
                html += '<td style="font-size:13px;color:#64748B;">' + escapeHtml(fullForm) + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
        }
        html += '</div>';

        return html;
    }

    // ─── Enter key support ───
    document.getElementById('rollInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') searchResult();
    });

    // ─── Load Stats ───
    function loadStats() {
        fetch('<?php echo SITE_URL; ?>/api/result-stats.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.data.overall && parseInt(data.data.overall.total_students) > 0) {
                renderStats(data.data);
                document.getElementById('statsSection').style.display = '';
            }
        })
        .catch(function() {});
    }

    function renderStats(stats) {
        var o = stats.overall;
        var html = '';

        // Overview cards
        html += '<div class="stats-grid">';
        html += buildStatCard('#2563EB', '#EFF6FF', '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', parseInt(o.total_students || 0), 'Total Students');
        html += buildStatCard('#16A34A', '#F0FDF4', '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>', parseInt(o.total_passed || 0), 'Passed');
        html += buildStatCard('#DC2626', '#FEF2F2', '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>', parseInt(o.total_failed || 0), 'Failed/Referred');
        html += buildStatCard('#9333EA', '#FAF5FF', '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', parseFloat(o.avg_gpa || 0).toFixed(2), 'Average GPA');
        html += '</div>';

        // Semester-wise stats
        if (stats.semester_stats && stats.semester_stats.length > 0) {
            html += '<div class="top-failed-card"><h3>Semester-wise Analysis</h3>';
            html += '<table class="cstats-table"><thead><tr><th>Semester</th><th>Exam Year</th><th>Regulation</th><th>Total</th><th>Passed</th><th>Failed</th><th>Avg GPA</th><th>Pass Rate</th></tr></thead><tbody>';
            stats.semester_stats.forEach(function(s) {
                var passRate = s.total > 0 ? ((s.passed / s.total) * 100).toFixed(1) : '0.0';
                var rateClass = passRate >= 80 ? 'rate-high' : (passRate >= 50 ? 'rate-mid' : 'rate-low');
                html += '<tr>';
                html += '<td><strong>' + escapeHtml(s.semester) + '</strong></td>';
                html += '<td>' + escapeHtml(s.exam_year) + '</td>';
                html += '<td>' + escapeHtml(s.regulation_year) + '</td>';
                html += '<td>' + parseInt(s.total) + '</td>';
                html += '<td style="color:#16A34A;font-weight:600;">' + parseInt(s.passed) + '</td>';
                html += '<td style="color:#DC2626;font-weight:600;">' + parseInt(s.failed) + '</td>';
                html += '<td>' + parseFloat(s.avg_gpa || 0).toFixed(2) + '</td>';
                html += '<td class="pass-rate ' + rateClass + '">' + passRate + '%</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
        }

        // Top Failed Subjects
        if (stats.top_failed_subjects && stats.top_failed_subjects.length > 0) {
            var maxCount = stats.top_failed_subjects[0].count;
            html += '<div class="top-failed-card"><h3>Most Failed Subjects</h3>';
            stats.top_failed_subjects.forEach(function(fs, i) {
                var pct = (fs.count / maxCount * 100).toFixed(0);
                var barColor = i < 3 ? '#DC2626' : (i < 6 ? '#F97316' : '#EAB308');
                var rankClass = i < 3 ? ' top' : '';

                html += '<div class="fail-bar-item">';
                html += '<div class="fail-bar-rank' + rankClass + '">' + (i + 1) + '</div>';
                html += '<div class="fail-bar-info">';
                html += '<div class="fail-bar-name">' + escapeHtml(fs.subject_name) + ' <small>[' + escapeHtml(fs.code) + ' (' + escapeHtml(fs.fail_type_label || fs.fail_type) + ')]</small></div>';
                html += '<div class="fail-bar-track"><div class="fail-bar-fill" style="width:' + pct + '%;background:' + barColor + ';"></div></div>';
                html += '</div>';
                html += '<div class="fail-bar-count">' + fs.count.toLocaleString() + ' <small style="color:#94A3B8;font-weight:400;">fails</small></div>';
                html += '</div>';
            });
            html += '</div>';
        }

        // College-wise stats
        if (stats.college_stats && stats.college_stats.length > 0) {
            html += '<div class="college-stats-card"><h3>College-wise Performance (Top 20)</h3>';
            html += '<table class="cstats-table"><thead><tr><th>College Code</th><th>College Name</th><th>Students</th><th>Passed</th><th>Failed</th><th>Avg GPA</th><th>Pass Rate</th></tr></thead><tbody>';
            stats.college_stats.forEach(function(c) {
                var rateClass = c.pass_rate >= 80 ? 'rate-high' : (c.pass_rate >= 50 ? 'rate-mid' : 'rate-low');
                html += '<tr>';
                html += '<td><strong>[' + escapeHtml(c.college_code) + ']</strong></td>';
                html += '<td>' + escapeHtml(c.college_name) + '</td>';
                html += '<td>' + parseInt(c.total_students) + '</td>';
                html += '<td style="color:#16A34A;font-weight:600;">' + parseInt(c.passed) + '</td>';
                html += '<td style="color:#DC2626;font-weight:600;">' + parseInt(c.failed) + '</td>';
                html += '<td>' + parseFloat(c.avg_gpa || 0).toFixed(2) + '</td>';
                html += '<td class="pass-rate ' + rateClass + '">' + c.pass_rate + '%</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
        }

        document.getElementById('statsContent').innerHTML = html;
    }
</script>
</body>
</html>
