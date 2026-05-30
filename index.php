<?php
/**
 * CST Department - Khulna Polytechnic Institute
 * Homepage - Professional Landing Page
 */
require_once 'includes/config.php';

$pageTitle = 'Home - CST Department | Khulna Polytechnic Institute';

// Fetch Data
$notices = [];
$teachers = [];
$gallery = [];
$sponsors = [];
$credits = [];

try {
    $stmt = safeQuery($pdo, "SELECT * FROM notices WHERE status = 1 ORDER BY created_at DESC LIMIT 5");
    if ($stmt) $notices = $stmt->fetchAll();
} catch (Exception $e) { $notices = []; }

try {
    $stmt = safeQuery($pdo, "SELECT * FROM teachers WHERE status = 1 ORDER BY sort_order ASC LIMIT 4");
    if ($stmt) $teachers = $stmt->fetchAll();
} catch (Exception $e) { $teachers = []; }

try {
    $stmt = safeQuery($pdo, "SELECT * FROM gallery WHERE status = 1 ORDER BY created_at DESC LIMIT 6");
    if ($stmt) $gallery = $stmt->fetchAll();
} catch (Exception $e) { $gallery = []; }

try {
    $stmt = safeQuery($pdo, "SELECT * FROM sponsors WHERE status = 1 ORDER BY sort_order ASC");
    if ($stmt) $sponsors = $stmt->fetchAll();
} catch (Exception $e) { $sponsors = []; }

try {
    $stmt = safeQuery($pdo, "SELECT * FROM credits WHERE status = 1 ORDER BY sort_order ASC");
    if ($stmt) $credits = $stmt->fetchAll();
} catch (Exception $e) { $credits = []; }

function noticeTagClass($catName) {
    $n = strtolower($catName);
    if (strpos($n, 'important') !== false || strpos($n, 'urgent') !== false) return 'important';
    if (strpos($n, 'academic') !== false) return 'academic';
    if (strpos($n, 'event') !== false) return 'event';
    if (strpos($n, 'exam') !== false) return 'exam';
    return 'general';
}

function monthAbbr($dateStr) {
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $dt = new DateTime($dateStr);
    return $months[(int)$dt->format('m') - 1];
}

$siteName = siteSetting('site_name', 'CST Department');
$siteTagline = siteSetting('site_tagline', 'Khulna Polytechnic Institute');
$sitePhone = siteSetting('site_phone', '+880-XXXX-XXXXXX');
$siteEmail = siteSetting('site_email', 'info@cst-kpi.edu.bd');
$siteAddress = siteSetting('site_address', 'Khulna Polytechnic Institute, Sonadanga, Khulna, Bangladesh');
$siteLogo = siteSetting('site_logo', '');
$siteDesc = siteSetting('site_description', '');
$facebookUrl = siteSetting('facebook_url', '#');
$youtubeUrl = siteSetting('youtube_url', '#');
$linkedinUrl = siteSetting('linkedin_url', '#');
$footerText = siteSetting('footer_text', '&copy; ' . date('Y') . ' CST Department, Khulna Polytechnic Institute. All Rights Reserved.');

// Semester Data (for KPI CST - 8 semesters, 4-year diploma)
$semesters = [
    ['num' => 1, 'name' => '1st Semester',
        'subjects' => ['Engineering Drawing', 'Bangla-I', 'English-I', 'Mathematics-I', 'Physics-I', 'Computer Office Application', 'Basic Electricity'],
        'outcomes' => [
            'Engineering Drawing-এ 2D/3D Drafting ও AutoCAD ব্যবহার শিখবেন',
            'Bangla ও English Communication Skill উন্নত করবেন',
            'Mathematics ও Physics-এর Fundamental Concept মাস্টার করবেন',
            'Computer Office Application: MS Word, Excel, PowerPoint-এ দক্ষ হবেন',
            'Basic Electricity-এ Circuit Theory ও Electrical Safety শিখবেন',
            'প্রাথমিক Technical Foundation সম্পূর্ণভাবে গড়ে তুলবেন',
        ]
    ],
    ['num' => 2, 'name' => '2nd Semester',
        'subjects' => ['Bangla-II', 'English-II', 'Physical Education & Life Skills', 'Chemistry', 'Mathematics-II', 'Python Programming', 'Computer Graphics Design-I', 'Basic Electronics'],
        'outcomes' => [
            'Python Programming দিয়ে প্রোগ্রামিং এর Basic শিখবেন',
            'Computer Graphics Design-I: Adobe Photoshop/Illustrator ব্যবহার করতে পারবেন',
            'Chemistry ও Mathematics-II এর Advanced Concept শিখবেন',
            'Basic Electronics-তে Transistor, Diode, Circuit বিশ্লেষণ করবেন',
            'English Proficiency ও Life Skill Development করবেন',
            'Programming ও Design-এর Combined Skill অর্জন করবেন',
        ]
    ],
    ['num' => 3, 'name' => '3rd Semester',
        'subjects' => ['Social Science', 'Physics-II', 'Mathematics-III', 'Application Development Using Python', 'Computer Graphics Design-II', 'IT Support Services', 'Digital Electronics-I'],
        'outcomes' => [
            'Python দিয়ে Real Application Development করতে পারবেন',
            'Computer Graphics Design-II: Advanced UI/UX Design শিখবেন',
            'Digital Electronics: Logic Gate, Boolean Algebra, Flip-Flop মাস্টার করবেন',
            'IT Support: Hardware Troubleshooting ও Network Setup করবেন',
            'Mathematics-III ও Physics-II এর Applied Concept শিখবেন',
            'Application Building ও IT Support-এ Hands-on Expertise অর্জন করবেন',
        ]
    ],
    ['num' => 4, 'name' => '4th Semester',
        'subjects' => ['Business Communication', 'Java Programming', 'Data Structure & Algorithm', 'Computer Peripherals & Interfacing', 'Web Design & Development-I', 'Digital Electronics-II', 'Environmental Studies'],
        'outcomes' => [
            'Java Programming: OOP Concept ও Software Development শিখবেন',
            'Data Structure & Algorithm: Array, Linked List, Tree, Sorting মাস্টার করবেন',
            'Web Design & Development-I: HTML, CSS, JavaScript দিয়ে Website বানাবেন',
            'Computer Peripherals & Interfacing: Hardware Integration শিখবেন',
            'Digital Electronics-II: Microprocessor Fundamental ও ADC/DAC শিখবেন',
            'Professional Communication ও Problem Solving Skill গড়ে তুলবেন',
        ]
    ],
    ['num' => 5, 'name' => '5th Semester',
        'subjects' => ['Accounting', 'Application Development Using Java', 'Web Design & Development-II', 'Computer Architecture & Microprocessor', 'Data Communication', 'Operating System', 'Project Work-I'],
        'outcomes' => [
            'Java দিয়ে Desktop/Web Application Development করবেন',
            'Web Design & Development-II: Responsive Website ও Frontend Framework শিখবেন',
            'Operating System: Process, Memory, File Management বুঝবেন',
            'Data Communication: Networking Protocol ও Transmission Media শিখবেন',
            'Computer Architecture & Microprocessor-এর Core Concept মাস্টার করবেন',
            'Project Work-I: প্রথম Real-World Project করবেন',
        ]
    ],
    ['num' => 6, 'name' => '6th Semester',
        'subjects' => ['Principles of Marketing', 'Industrial Management', 'Database Management System', 'Computer Networking', 'Sensor & IoT System', 'Microcontroller Based System Design', 'Surveillance Security System', 'Web Development Project'],
        'outcomes' => [
            'Database Management System: SQL, MySQL, Database Design শিখবেন',
            'Computer Networking: TCP/IP, LAN/WAN, Routing ও Switching মাস্টার করবেন',
            'IoT System: Sensor Interfacing ও Smart System Design করবেন',
            'Microcontroller Based System: Embedded System Development শিখবেন',
            'Surveillance Security System: CCTV, Access Control Setup করবেন',
            'Web Development Project: Full-Stack Project করবেন',
        ]
    ],
    ['num' => 7, 'name' => '7th Semester',
        'subjects' => ['Innovation & Entrepreneurship', 'Digital Marketing Technique', 'Network Administration & Services', 'Cyber Security & Ethics', 'Apps Development Project', 'Multimedia & Animation', 'Project Work-II'],
        'outcomes' => [
            'Cyber Security: Ethical Hacking, Encryption, Security Audit শিখবেন',
            'Network Administration: Server Setup, Firewall, DNS, DHCP Configure করবেন',
            'Digital Marketing: SEO, SMM, Google Ads, Content Marketing মাস্টার করবেন',
            'Multimedia & Animation: Video Editing, Motion Graphics তৈরি করবেন',
            'Innovation & Entrepreneurship: Startup Idea Generation শিখবেন',
            'Apps Development Project: Complete Application Deploy করবেন',
        ]
    ],
    ['num' => 8, 'name' => '8th Semester',
        'subjects' => ['Industrial Attachment + Project Presentation'],
        'outcomes' => [
            'Industrial Attachment: রিয়েল কোম্পানিতে 3-6 মাসের কাজের অভিজ্ঞতা',
            'Final Project Presentation: Professional Portfolio তৈরি করবেন',
            'Industry তে কাজের জন্য Complete Readiness অর্জন করবেন',
            'Technical Documentation ও Report Writing Skill ডেভেলপ করবেন',
            'Job Interview ও Placement Preparation সম্পন্ন করবেন',
            ' Diploma in Engineering সম্পূর্ণ করে Industry-Ready Graduate হবেন',
        ]
    ],
];

$semesterColors = ['#2563EB', '#7C3AED', '#DB2777', '#EA580C', '#059669', '#0891B2', '#4F46E5', '#DC2626'];
$semesterIcons = ['book-open', 'code', 'database', 'globe', 'server', 'shield', 'cpu', 'rocket'];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo clean($siteDesc); ?>">
    <title><?php echo clean($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
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
<header class="header" id="mainHeader">
    <div class="header-top">
        <div class="corner-bubble bubble-tl"></div>
        <div class="corner-bubble bubble-tr"></div>
        <div class="corner-bubble bubble-bl"></div>
        <div class="container">
            <div class="top-left">
                <a href="tel:<?php echo clean($sitePhone); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <?php echo clean($sitePhone); ?>
                </a>
                <a href="mailto:<?php echo clean($siteEmail); ?>" style="margin-left:16px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?php echo clean($siteEmail); ?>
                </a>
            </div>
            <div class="top-right">
                <?php if ($facebookUrl && $facebookUrl !== '#'): ?>
                <a href="<?php echo clean($facebookUrl); ?>" target="_blank"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                <?php endif; ?>
                <?php if ($youtubeUrl && $youtubeUrl !== '#'): ?>
                <a href="<?php echo clean($youtubeUrl); ?>" target="_blank"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.13C5.12 19.56 12 19.56 12 19.56s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33zM9.75 15.02V8.48l5.75 3.27-5.75 3.27z"/></svg></a>
                <?php endif; ?>
                <?php if ($linkedinUrl && $linkedinUrl !== '#'): ?>
                <a href="<?php echo clean($linkedinUrl); ?>" target="_blank"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="nav-container">
        <div class="nav-wrapper">
            <a href="<?php echo SITE_URL; ?>/" class="nav-brand">
                <?php if ($siteLogo && file_exists(UPLOAD_PATH . '/' . $siteLogo)): ?>
                    <img src="<?php echo UPLOAD_URL . '/' . clean($siteLogo); ?>" alt="KPI CST">
                <?php else: ?>
                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none" style="background:#2563EB;border-radius:10px;padding:8px;">
                        <rect x="10" y="12" width="24" height="18" rx="2" stroke="#fff" stroke-width="1.5" fill="none"/>
                        <path d="M15 20h14M15 24h10M15 28h6" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="32" cy="14" r="4" fill="#10B981"/>
                        <path d="M30.5 14l1 1 2-2" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
                <div class="brand-text">
                    <span class="brand-name"><?php echo clean($siteName); ?></span>
                    <span class="brand-tagline"><?php echo clean($siteTagline); ?></span>
                </div>
            </a>
            <ul class="nav-links" id="navLinks">
                <li><a href="<?php echo SITE_URL; ?>/" class="active"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>হোম</span></a></li>
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
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle navigation">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </div>
</header>

<!-- ============================================
     HERO SECTION - Big Lottie Animation
     ============================================ -->
<section class="hero hero-large shine-effect">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                খুলনা পলিটেকনিক ইনস্টিটিউট
            </div>
            <h1 class="hero-title">স্বাগতম <span class="highlight">CST</span><br>বিভাগে</h1>
            <p class="hero-desc">
                কম্পিউটার সায়েন্স অ্যান্ড টেকনোলজি বিভাগ, খুলনা পলিটেকনিক ইনস্টিটিউট, দক্ষ প্রযুক্তিবিদদের গড়ে তুলতে প্রতিশ্রুতিবদ্ধ। আমাদের ৪ বছরের ডিপ্লোমা প্রোগ্রাম ব্যবহারিক প্রশিক্ষণ, আধুনিক পাঠ্যক্রম এবং শিল্প অভিজ্ঞতাকে একত্রিত করে শিক্ষার্থীদের ডিজিটাল ভবিষ্যতের জন্য প্রস্তুত করে।
            </p>
            <div class="hero-actions">
                <a href="<?php echo SITE_URL; ?>/resources.php" class="btn btn-primary btn-lg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    প্রোগ্রামসমূহ দেখুন
                </a>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline btn-lg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    যোগাযোগ করুন
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <h3>500+</h3>
                    <p>শিক্ষার্থী</p>
                </div>
                <div class="hero-stat">
                    <h3>30+</h3>
                    <p>শিক্ষক</p>
                </div>
                <div class="hero-stat">
                    <h3>8</h3>
                    <p>সেমিস্টার</p>
                </div>
                <div class="hero-stat">
                    <h3>5+</h3>
                    <p>আধুনিক ল্যাব</p>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <lottie-player
                src="<?php echo SITE_URL; ?>/assets/lottie/coding.json"
                background="transparent"
                speed="1"
                loop
                autoplay
                style="width:100%;max-width:480px;">
            </lottie-player>
        </div>
    </div>
</section>

<!-- ============================================
     ABOUT US SECTION (Small)
     ============================================ -->
<section class="section section-alt shine-effect" id="about-short">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                আমাদের সম্পর্কে
            </div>
            <h2 class="section-title">খুলনা পলিটেকনিক ইনস্টিটিউটে <span class="title-blue">সিএসটি</span></h2>
        </div>
        <div class="about-grid-2col">
            <div class="about-text-col">
                <p style="color:#64748B;line-height:1.8;margin-bottom:16px;">
                    কম্পিউটার সায়েন্স অ্যান্ড টেকনোলজি (সিএসটি) বিভাগ <strong style="color:#1E293B;">খুলনা পলিটেকনিক ইনস্টিটিউট</strong>-এর অন্যতম প্রধান প্রযুক্তি বিভাগ, যা বাংলাদেশের অন্যতম বৃহৎ ও স্বনামধন্য পলিটেকনিক ইনস্টিটিউট, ১৯৬৩ সালে বাংলাদেশ কারিগরি শিক্ষা বোর্ড (বিটিইবি)-এর অধীনে প্রতিষ্ঠিত।
                </p>
                <p style="color:#64748B;line-height:1.8;margin-bottom:24px;">
                    আমাদের ৪ বছরের ডিপ্লোমা ইঞ্জিনিয়ারিং প্রোগ্রাম শিক্ষার্থীদের প্রোগ্রামিং, নেটওয়ার্কিং, ডাটাবেজ ম্যানেজমেন্ট, ওয়েব ডেভেলপমেন্ট, মোবাইল অ্যাপ ডেভেলপমেন্ট, সাইবার সিকিউরিটি এবং আইওটি ও এআই-এর মতো উদীয়মান প্রযুক্তিতে দক্ষ করে তোলে। আধুনিক কম্পিউটার ল্যাব ও অভিজ্ঞ শিক্ষকমণ্ডলীর সাহায্যে সিএসটি কেপিআই প্রতি বছর শিল্প-প্রস্তুত স্নাতক তৈরি করে।
                </p>
                <div class="about-highlights">
                    <div class="about-highlight-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>বিটিইবি অধীনে প্রতিষ্ঠিত</span>
                    </div>
                    <div class="about-highlight-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>৪ বছরের ডিপ্লোমা প্রোগ্রাম</span>
                    </div>
                    <div class="about-highlight-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>আধুনিক ল্যাব সুবিধা</span>
                    </div>
                    <div class="about-highlight-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>শিল্পভিত্তিক পাঠ্যক্রম</span>
                    </div>
                </div>
            </div>
            <div class="about-visual-col">
                <div class="about-stats-cards">
                    <div class="about-stat-card stat-card-primary">
                        <div class="about-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div class="about-stat-number">500+</div>
                        <div class="about-stat-label">সক্রিয় শিক্ষার্থী</div>
                    </div>
                    <div class="about-stat-card stat-card-green">
                        <div class="about-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="about-stat-number">30+</div>
                        <div class="about-stat-label">অভিজ্ঞ শিক্ষক</div>
                    </div>
                    <div class="about-stat-card stat-card-purple">
                        <div class="about-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                        <div class="about-stat-number">5+</div>
                        <div class="about-stat-label">আধুনিক ল্যাব</div>
                    </div>
                    <div class="about-stat-card stat-card-orange">
                        <div class="about-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg>
                        </div>
                        <div class="about-stat-number">95%</div>
                        <div class="about-stat-label">পাসের হার</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     MISSION & VISION SECTION
     ============================================ -->
<section class="section shine-effect">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16l4-4-4-4"/><path d="M8 12h8"/></svg>
                আমাদের উদ্দেশ্য
            </div>
            <h2 class="section-title">লক্ষ্য ও <span class="title-blue">উদ্দেশ্য</span></h2>
            <p class="section-desc">প্রযুক্তি শিক্ষায় শ্রেষ্ঠত্ব অর্জনের প্রতিশ্রুতিতে আমাদের পথপ্রদর্শক নীতিসমূহ।</p>
        </div>
        <div class="mv-grid">
            <div class="mv-card mv-mission">
                <div class="mv-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
                <h3>আমাদের লক্ষ্য</h3>
                <p>কম্পিউটার সায়েন্স অ্যান্ড টেকনোলজিতে মানসম্মত ডিপ্লোমা স্তরের শিক্ষা প্রদান করা যা শিক্ষার্থীদের ব্যবহারিক দক্ষতা, বিশ্লেষণাত্মক চিন্তা এবং পেশাদার নৈতিকতায় সক্ষম করে তুলবে। আমরা জাতীয় ও আন্তর্জাতিক আইটি শিল্পে অবদান রাখতে সক্ষম দক্ষ প্রযুক্তিবিদ তৈরি করার চেষ্টা করি।</p>
                <ul class="mv-points">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        ব্যবহারিক প্রশিক্ষণ পদ্ধতি
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        শিল্পভিত্তিক আধুনিক পাঠ্যক্রম
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        শক্তিশালী নৈতিক মূল্যবোধ গড়ে তোলা
                    </li>
                </ul>
            </div>
            <div class="mv-card mv-vision">
                <div class="mv-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <h3>আমাদের উদ্দেশ্য</h3>
                <p>বাংলাদেশের পলিটেকনিক ইনস্টিটিউটগুলির মধ্যে শ্রেষ্ঠ কম্পিউটার সায়েন্স অ্যান্ড টেকনোলজি বিভাগ হিসেবে স্বীকৃতি লাভ করা, যা উদ্ভাবনী, দক্ষ এবং সামাজিকভাবে দায়িত্বশীল প্রযুক্তি পেশাদারদের উৎপাদনের জন্য পরিচিত যারা ডিজিটাল রূপান্তরকে এগিয়ে নেবে।</p>
                <ul class="mv-points">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        প্রযুক্তি শিক্ষায় শ্রেষ্ঠত্বের কেন্দ্র
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        স্নাতকদের জন্য ১০০% কর্মসংস্থান প্রস্তুতি
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        উদ্ভাবন ও গবেষণার কেন্দ্র
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     FEATURES SECTION
     ============================================ -->
<section class="section section-alt shine-effect">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                কেন সিএসটি কেপিআই
            </div>
            <h2 class="section-title">আমাদের <span class="title-blue">বৈশিষ্ট্য</span></h2>
            <p class="section-desc">জেনে নিন কেন খুলনা পলিটেকনিক ইনস্টিটিউটের সিএসটি বিভাগ আগ্রহী প্রযুক্তিবিদদের জন্য শ্রেষ্ঠ পছন্দ।</p>
        </div>
        <div class="grid-4">
            <div class="feature-card">
                <div class="feature-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <h3>আধুনিক কম্পিউটার ল্যাব</h3>
                <p>৫টির বেশি সুসজ্জিত ল্যাব যেখানে সর্বশেষ হার্ডওয়্যার, উচ্চগতির ইন্টারনেট এবং আধুনিক সফটওয়্যার টুলস রয়েছে।</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg>
                </div>
                <h3>মানসম্মত শিক্ষা</h3>
                <p>অভিজ্ঞ শিক্ষকদের দ্বারা বিটিইবি-অনুমোদিত পাঠ্যক্রম পড়ানো হয় যেখানে তত্ত্ব ও ব্যবহারিক উভয়ের উপর জোর দেওয়া হয়।</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3>শিল্প সংযোগ</h3>
                <p>ইন্টার্নশিপ সুযোগ, শিল্প পরিদর্শন এবং শীর্ষস্থানীয় প্রযুক্তি কোম্পানি ও পেশাদারদের থেকে গেস্ট লেকচার।</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                </div>
                <h3>সার্টিফাইড প্রোগ্রাম</h3>
                <p>বিটিইবি ডিপ্লোমা সার্টিফিকেট সারাদেশে স্বীকৃত, যা উচ্চশিক্ষা ও কর্মসংস্থানের দ্বার উন্মুক্ত করে।</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                </div>
                <h3>সহপাঠক্রমিক কার্যক্রম</h3>
                <p>প্রোগ্রামিং প্রতিযোগিতা, টেক ফেয়ার, হ্যাকাথন, সেমিনার এবং ওয়ার্কশপের মাধ্যমে ক্লাসরুমের বাইরে ব্যবহারিক দক্ষতা বৃদ্ধি।</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <h3>গবেষণা ও উদ্ভাবন</h3>
                <p>শিক্ষার্থীরা বাস্তব প্রকল্প, আইওটি প্রোটোটাইপ, ওয়েব অ্যাপ্লিকেশন নিয়ে কাজ করে এবং জাতীয় প্রতিযোগিতায় অংশগ্রহণ করে।</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3>প্রাক্তন শিক্ষার্থী নেটওয়ার্ক</h3>
                <p>দেশে ও বিদেশে শীর্ষ আইটি কোম্পানিতে কর্মরত শক্তিশালী প্রাক্তন শিক্ষার্থী সম্প্রদায়, যারা মেন্টরশিপ ও ক্যারিয়ার গাইডলাইন প্রদান করেন।</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <h3>ডিজিটাল লাইব্রেরি</h3>
                <p>অনলাইন রিসোর্স, ই-বুক, ভিডিও লেকচার এবং স্ব-গতি শিক্ষা ও রেফারেন্সের জন্য ডিজিটাল টুলসে অ্যাক্সেস।</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     SEMESTER JOURNEY SECTION (Redesigned - Purple Theme)
     ============================================ -->
<section class="section semester-journey-section shine-effect" id="semester-section">
    <div class="container">
        <div class="sem-journey-header">
            <div class="sem-journey-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                একাডেমিক রোডম্যাপ
            </div>
            <h2 class="sem-journey-title">সেমিস্টার <span class="title-blue">যাত্রা</span></h2>
            <p class="sem-journey-desc">আমাদের ৪ বছরের ডিপ্লোমা প্রোগ্রাম ভিত্তিগত বিষয় থেকে শুরু করে জাভাস্ক্রিপ্ট, রিঅ্যাক্ট, নোড.জেএস, মঙ্গোডিবি, নেক্সট.জেএস এবং এক্সপ্রেসের মতো অত্যাধুনিক প্রযুক্তি পর্যন্ত সবকিছু অন্তর্ভুক্ত করে। প্রতিটি সেমিস্টারে ক্লিক করে পাঠ্যক্রম দেখুন।</p>
        </div>

        <!-- Milestone Tabs Navigation -->
        <div class="sem-milestone-tabs" id="semMilestoneTabs">
            <?php foreach ($semesters as $i => $sem): ?>
                <button class="sem-milestone-tab <?php echo $i === 0 ? 'active' : ''; ?>" data-semester="<?php echo $sem['num']; ?>">
                    <span class="milestone-num"><?php echo str_pad($sem['num'], 2, '0', STR_PAD_LEFT); ?></span>
                    <span class="milestone-label">সেমিস্টার <?php echo $sem['num']; ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Semester Content Display -->
        <div class="sem-journey-content-area" id="semJourneyContent">
            <?php foreach ($semesters as $i => $sem): ?>
                <div class="sem-journey-panel <?php echo $i === 0 ? 'active' : ''; ?>" id="semPanel<?php echo $sem['num']; ?>" data-semester="<?php echo $sem['num']; ?>">
                    <!-- Panel Title -->
                    <div class="sem-panel-title-area">
                        <span class="sem-panel-title"><?php echo $sem['name']; ?> &mdash; বছর <?php echo ceil($sem['num'] / 2); ?></span>
                    </div>

                    <!-- Mind-Map Layout: Subjects → Animated Line → Big Folder → Branching Lines → Learn Items -->
                    <div class="sem-mindmap-area" id="semMindmap<?php echo $sem['num']; ?>">
                        <!-- SVG Overlay for all connecting lines -->
                        <svg class="sem-lines-svg" id="semLinesSvg<?php echo $sem['num']; ?>">
                            <defs>
                                <linearGradient id="mainLineGrad<?php echo $sem['num']; ?>" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#C4B5FD;stop-opacity:0.75"/>
                                    <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:1"/>
                                </linearGradient>
                                <linearGradient id="branchGrad<?php echo $sem['num']; ?>" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#8B5CF6;stop-opacity:0.8"/>
                                    <stop offset="50%" style="stop-color:#A78BFA;stop-opacity:0.6"/>
                                    <stop offset="100%" style="stop-color:#C4B5FD;stop-opacity:0.4"/>
                                </linearGradient>
                                <filter id="lineGlow<?php echo $sem['num']; ?>">
                                    <feGaussianBlur stdDeviation="3" result="blur"/>
                                    <feMerge>
                                        <feMergeNode in="blur"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            <!-- Main line: Subjects → Folder (drawn by JS) -->
                            <path class="sem-main-line" id="semMainLine<?php echo $sem['num']; ?>" fill="none" stroke="url(#mainLineGrad<?php echo $sem['num']; ?>)" stroke-width="3.5" stroke-linecap="round" filter="url(#lineGlow<?php echo $sem['num']; ?>)"/>
                            <!-- Branch lines: Folder → Each Outcome (drawn by JS) -->
                            <?php
                            $displayOutcomes = isset($sem['outcomes']) ? $sem['outcomes'] : [];
                            foreach ($displayOutcomes as $oi => $outcome):
                            ?>
                                <path class="sem-branch-line" id="semBranch<?php echo $sem['num']; ?>_<?php echo $oi; ?>" fill="none" stroke="url(#branchGrad<?php echo $sem['num']; ?>)" stroke-width="2.8" stroke-linecap="round"/>
                                <!-- Animated dot flowing along each branch -->
                                <circle class="sem-branch-dot" id="semDot<?php echo $sem['num']; ?>_<?php echo $oi; ?>" r="4.5" fill="#8B5CF6" opacity="0"/>
                            <?php endforeach; ?>
                        </svg>

                        <!-- Left: Subjects Card -->
                        <div class="sem-subjects-card" id="semSubjects<?php echo $sem['num']; ?>">
                            <div class="sem-card-header">
                                <div class="sem-card-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                </div>
                                <span>বিষয়সমূহ</span>
                            </div>
                            <ul class="sem-subject-list">
                                <?php foreach ($sem['subjects'] as $subj): ?>
                                    <li class="sem-subject-tag"><?php echo clean($subj); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="sem-subject-footer"><?php echo count($sem['subjects']); ?>টি বিষয় মোট</div>
                        </div>

                        <!-- Center: BIG Folder Icon with text INSIDE -->
                        <div class="sem-folder-center" id="semFolder<?php echo $sem['num']; ?>">
                            <div class="sem-folder-3d">
                                <!-- Folder back -->
                                <div class="sem-folder-back">
                                    <svg viewBox="0 0 140 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <!-- Tab -->
                                        <path d="M10 16C10 8.268 16.268 2 24 2H52C54.5 2 56.8 3.2 58.2 5.2L64 14H116C123.732 14 130 20.268 130 28V94C130 101.732 123.732 108 116 108H24C16.268 108 10 101.732 10 94V16Z" fill="url(#fGradBack<?php echo $sem['num']; ?>)"/>
                                        <!-- Folder front face -->
                                        <path d="M10 28H130L123 101C122.46 105.8 118.4 109.4 114 109.4H26C21.6 109.4 17.54 105.8 17 101L10 28Z" fill="url(#fGradFront<?php echo $sem['num']; ?>)"/>
                                        <!-- Inner shadow line -->
                                        <path d="M12 28H128" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                                        <!-- Text: Semester number INSIDE the folder -->
                                        <text x="70" y="58" text-anchor="middle" font-family="Hind Siliguri, sans-serif" font-weight="800" font-size="28" fill="rgba(255,255,255,0.95)"><?php echo str_pad($sem['num'], 2, '0', STR_PAD_LEFT); ?></text>
                                        <text x="70" y="82" text-anchor="middle" font-family="Hind Siliguri, sans-serif" font-weight="600" font-size="13" fill="rgba(255,255,255,0.7)" letter-spacing="3">সেমিস্টার</text>
                                        <defs>
                                            <linearGradient id="fGradBack<?php echo $sem['num']; ?>" x1="70" y1="2" x2="70" y2="108" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#A78BFA"/><stop offset="1" stop-color="#7C3AED"/>
                                            </linearGradient>
                                            <linearGradient id="fGradFront<?php echo $sem['num']; ?>" x1="70" y1="28" x2="70" y2="109.4" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#8B5CF6"/><stop offset="1" stop-color="#5B21B6"/>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                </div>
                                <!-- Glow ring behind folder -->
                                <div class="sem-folder-glow"></div>
                            </div>
                            <div class="sem-folder-shadow"></div>
                        </div>

                        <!-- Right: Individual What You'll Learn Cards -->
                        <div class="sem-outcomes-column" id="semOutcomes<?php echo $sem['num']; ?>">
                            <div class="sem-outcomes-heading">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span>আপনি যা শিখবেন</span>
                            </div>
                            <?php foreach ($displayOutcomes as $oi => $outcome): ?>
                                <div class="sem-outcome-card" id="semOutcome<?php echo $sem['num']; ?>_<?php echo $oi; ?>">
                                    <div class="sem-outcome-circle">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                    <span><?php echo clean($outcome); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
     LATEST NOTICES SECTION
     ============================================ -->
<section class="section section-alt shine-effect">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                সর্বশেষ আপডেট
            </div>
            <h2 class="section-title">সাম্প্রতিক <span class="title-blue">নোটিশ</span></h2>
            <p class="section-desc">বিভাগের সর্বশেষ ঘোষণা ও আপডেট দেখুন।</p>
        </div>
        <?php if (!empty($notices)): ?>
            <div class="notices-list">
                <?php foreach ($notices as $notice): ?>
                    <?php
                        $catName = getCategoryName($pdo, $notice['category_id']);
                        $tagClass = noticeTagClass($catName);
                        $dateObj = new DateTime($notice['created_at']);
                        $dayNum = $dateObj->format('d');
                        $monthStr = monthAbbr($notice['created_at']);
                    ?>
                    <div class="notice-card">
                        <div class="notice-date">
                            <div class="day"><?php echo clean($dayNum); ?></div>
                            <div class="month"><?php echo clean($monthStr); ?></div>
                        </div>
                        <div class="notice-content">
                            <?php if ($notice['is_important']): ?>
                                <span class="notice-tag important">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2z"/><rect x="11" y="10" width="2" height="4" fill="white"/><rect x="11" y="16" width="2" height="2" fill="white"/></svg>
                                    গুরুত্বপূর্ণ
                                </span>
                            <?php else: ?>
                                <span class="notice-tag <?php echo clean($tagClass); ?>"><?php echo clean($catName); ?></span>
                            <?php endif; ?>
                            <h3><a href="<?php echo SITE_URL; ?>/notice-details.php?slug=<?php echo clean($notice['slug']); ?>"><?php echo clean($notice['title']); ?></a></h3>
                            <?php if (!empty($notice['content'])): ?>
                                <p><?php echo clean(mb_substr(strip_tags($notice['content']), 0, 120)) . '...'; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:30px;">
                <a href="<?php echo SITE_URL; ?>/notice.php" class="btn btn-outline">সকল নোটিশ দেখুন <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
            </div>
        <?php else: ?>
            <div class="empty-state" style="margin-top: -10px;">
                <lottie-player
                    src="<?php echo SITE_URL; ?>/assets/lottie/not-found.json"
                    background="transparent"
                    speed="1"
                    style="width: 220px; height: 220px; display: block; margin: 0 auto; margin-bottom: 0;"
                    loop
                    autoplay>
                </lottie-player>
                <h3>এখনও নোটিশ নেই</h3>
                <p>গুরুত্বপূর্ণ ঘোষণা এখানে প্রদর্শিত হবে। অনুগ্রহ করে পরে আবার দেখুন।</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
     FACULTY SPOTLIGHT
     ============================================ -->
<section class="section shine-effect">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                আমাদের দল
            </div>
            <h2 class="section-title">অভিজ্ঞ <span class="title-blue">শিক্ষকমণ্ডলী</span></h2>
            <p class="section-desc">পরবর্তী প্রজন্মের প্রযুক্তি নেতাদের পথপ্রদর্শনকারী নিবেদিতপ্রাণ শিক্ষকগণ।</p>
        </div>
        <?php if (!empty($teachers)): ?>
            <div class="grid-4">
                <?php foreach ($teachers as $teacher): ?>
                    <div class="teacher-card">
                        <div class="teacher-img-wrap">
                            <?php if ($teacher['image'] && file_exists(UPLOAD_PATH . '/' . $teacher['image'])): ?>
                                <img src="<?php echo UPLOAD_URL . '/' . clean($teacher['image']); ?>" alt="<?php echo clean($teacher['name']); ?>">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode(clean($teacher['name'])); ?>&background=DBEAFE&color=2563EB&size=300&bold=true" alt="<?php echo clean($teacher['name']); ?>">
                            <?php endif; ?>
                            <div class="teacher-social">
                                <?php if (!empty($teacher['facebook'])): ?>
                                    <a href="<?php echo clean($teacher['facebook']); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                                <?php endif; ?>
                                <?php if (!empty($teacher['linkedin'])): ?>
                                    <a href="<?php echo clean($teacher['linkedin']); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="teacher-info">
                            <h3><?php echo clean($teacher['name']); ?></h3>
                            <?php if (!empty($teacher['designation'])): ?><p class="designation"><?php echo clean($teacher['designation']); ?></p><?php endif; ?>
                            <?php if (!empty($teacher['qualification'])): ?><p class="qualification"><?php echo clean($teacher['qualification']); ?></p><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:30px;">
                <a href="<?php echo SITE_URL; ?>/faculty.php" class="btn btn-outline">সকল শিক্ষক দেখুন <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
            </div>
        <?php else: ?>
            <div class="empty-state" style="margin-top: -10px;">
                <lottie-player
                    src="<?php echo SITE_URL; ?>/assets/lottie/not-found.json"
                    background="transparent"
                    speed="1"
                    style="width: 220px; height: 220px; display: block; margin: 0 auto; margin-bottom: 0;"
                    loop
                    autoplay>
                </lottie-player>
                <h3>শিক্ষকমণ্ডলী শীঘ্রই আসছে</h3>
                <p>আমাদের শিক্ষকদের প্রোফাইল প্রস্তুত করা হচ্ছে।</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
     GALLERY PREVIEW
     ============================================ -->
<section class="section section-alt shine-effect">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                ফটো গ্যালারি
            </div>
            <h2 class="section-title">আমাদের <span class="title-blue">মুহূর্ত</span></h2>
            <p class="section-desc">সিএসটি বিভাগের প্রাণবন্ত জীবন, অনুষ্ঠান ও কার্যক্রমের এক ঝলক।</p>
        </div>
        <?php if (!empty($gallery)): ?>
            <div class="gallery-grid">
                <?php foreach ($gallery as $item): ?>
                    <a href="<?php echo SITE_URL; ?>/gallery-details.php?slug=<?php echo clean($item['slug']); ?>" class="gallery-card">
                        <?php if ($item['image'] && file_exists(UPLOAD_PATH . '/' . $item['image'])): ?>
                            <img src="<?php echo UPLOAD_URL . '/' . clean($item['image']); ?>" alt="<?php echo clean($item['title']); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="gallery-placeholder">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo clean($item['title']); ?></h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:30px;">
                <a href="<?php echo SITE_URL; ?>/gallery.php" class="btn btn-outline">সকল ছবি দেখুন <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
            </div>
        <?php else: ?>
            <div class="empty-state" style="margin-top: -10px;">
                <lottie-player
                    src="<?php echo SITE_URL; ?>/assets/lottie/not-found.json"
                    background="transparent"
                    speed="1"
                    style="width: 220px; height: 220px; display: block; margin: 0 auto; margin-bottom: 0;"
                    loop
                    autoplay>
                </lottie-player>
                <h3>গ্যালারি শীঘ্রই আসছে</h3>
                <p>অনুষ্ঠান ও ক্যাম্পাস জীবনের ছবি শীঘ্রই যোগ করা হবে।</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
     SPONSORS SECTION
     ============================================ -->
<?php if (!empty($sponsors)): ?>
<section class="sponsor-section shine-effect">
    <div class="container">
        <div style="text-align:center;margin-bottom:30px;">
            <div class="section-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/></svg> আমাদের অংশীদার</div>
            <h2 class="section-title" style="margin-bottom:6px;"><span class="title-blue">স্পন্সর</span></h2>
        </div>
        <div class="sponsor-logos">
            <?php foreach ($sponsors as $sponsor): ?>
                <?php if ($sponsor['logo'] && file_exists(UPLOAD_PATH . '/' . $sponsor['logo'])): ?>
                    <a href="<?php echo !empty($sponsor['website']) ? clean($sponsor['website']) : '#'; ?>" target="_blank">
                        <img src="<?php echo UPLOAD_URL . '/' . clean($sponsor['logo']); ?>" alt="<?php echo clean($sponsor['name']); ?>">
                    </a>
                <?php else: ?>
                    <span style="font-size:14px;font-weight:600;color:#64748B;padding:12px 20px;border:1px solid #E2E8F0;border-radius:10px;"><?php echo clean($sponsor['name']); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     CREDITS SECTION
     ============================================ -->
<?php if (!empty($credits)): ?>
<section class="section shine-effect">
    <div class="container">
        <div class="section-header">
            <div class="section-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> দল</div>
            <h2 class="section-title"><span class="title-blue">কৃতজ্ঞতা</span></h2>
            <p class="section-desc">এই ওয়েবসাইট ও বিভাগের উদ্যোগের পেছনের প্রতিভাবান মানুষদের প্রশংসা।</p>
        </div>
        <div class="credits-grid">
            <?php foreach ($credits as $credit): ?>
                <div class="credit-card">
                    <?php if ($credit['image'] && file_exists(UPLOAD_PATH . '/' . $credit['image'])): ?>
                        <img src="<?php echo UPLOAD_URL . '/' . clean($credit['image']); ?>" alt="<?php echo clean($credit['name']); ?>">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode(clean($credit['name'])); ?>&background=EFF6FF&color=2563EB&size=200&bold=true" alt="<?php echo clean($credit['name']); ?>">
                    <?php endif; ?>
                    <h3><?php echo clean($credit['name']); ?></h3>
                    <p class="role"><?php echo clean($credit['role']); ?></p>
                    <?php if (!empty($credit['about'])): ?><p><?php echo clean(mb_substr($credit['about'], 0, 100)); ?></p><?php endif; ?>
                    <div class="social-links">
                        <?php if (!empty($credit['facebook'])): ?><a href="<?php echo clean($credit['facebook']); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a><?php endif; ?>
                        <?php if (!empty($credit['linkedin'])): ?><a href="<?php echo clean($credit['linkedin']); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a><?php endif; ?>
                        <?php if (!empty($credit['github'])): ?><a href="<?php echo clean($credit['github']); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg></a><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     CTA SECTION
     ============================================ -->
<section class="cta-section shine-effect">
    <div class="container">
        <div class="cta-content">
            <div class="cta-lottie">
                <lottie-player
                    src="https://lottie.host/9c240974-98ae-4f24-9f75-27a36c845237/CqE32wJ95E.json"
                    background="transparent"
                    speed="1"
                    loop
                    autoplay
                    style="width:200px;height:200px;">
                </lottie-player>
            </div>
            <h2>আপনার প্রযুক্তি যাত্রা শুরু করতে প্রস্তুত?</h2>
            <p>খুলনা পলিটেকনিক ইনস্টিটিউটে সিএসটিতে যোগ দিন এবং প্রযুক্তিতে সফল ক্যারিয়ার গড়ুন। এখনই আবেদন করুন বা আমাদের প্রোগ্রাম সম্পর্কে আরও জানতে যোগাযোগ করুন।</p>
            <div class="cta-actions">
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-primary btn-lg" style="background:#fff;color:#2563EB;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    যোগাযোগ করুন
                </a>
                <a href="<?php echo SITE_URL; ?>/about.php" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    আরও জানুন
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     FOOTER
     ============================================ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="nav-brand" style="margin-bottom:14px;">
                    <?php if ($siteLogo && file_exists(UPLOAD_PATH . '/' . $siteLogo)): ?>
                        <img src="<?php echo UPLOAD_URL . '/' . clean($siteLogo); ?>" alt="KPI CST">
                    <?php else: ?>
                        <svg width="40" height="40" viewBox="0 0 44 44" fill="none" style="background:#2563EB;border-radius:10px;padding:8px;"><rect x="10" y="12" width="24" height="18" rx="2" stroke="#fff" stroke-width="1.5" fill="none"/><path d="M15 20h14M15 24h10M15 28h6" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/><circle cx="32" cy="14" r="4" fill="#10B981"/></svg>
                    <?php endif; ?>
                    <div class="brand-text">
                        <span class="brand-name"><?php echo clean($siteName); ?></span>
                        <span class="brand-tagline"><?php echo clean($siteTagline); ?></span>
                    </div>
                </div>
                <p><?php echo clean($siteDesc) ?: 'খুলনা পলিটেকনিক ইনস্টিটিউটের সিএসটি বিভাগের অফিসিয়াল ওয়েবসাইট। ১৯৬৩ সাল থেকে ভবিষ্যৎ প্রযুক্তিবিদ গড়ে তুলছি।'; ?></p>
                <div class="footer-social">
                    <?php if ($facebookUrl && $facebookUrl !== '#'): ?><a href="<?php echo clean($facebookUrl); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a><?php endif; ?>
                    <?php if ($youtubeUrl && $youtubeUrl !== '#'): ?><a href="<?php echo clean($youtubeUrl); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.13C5.12 19.56 12 19.56 12 19.56s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33zM9.75 15.02V8.48l5.75 3.27-5.75 3.27z"/></svg></a><?php endif; ?>
                    <?php if ($linkedinUrl && $linkedinUrl !== '#'): ?><a href="<?php echo clean($linkedinUrl); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a><?php endif; ?>
                </div>
            </div>
            <div class="footer-col">
                <h4>দ্রুত লিংক</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> হোম</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> সম্পর্কে</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/faculty.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> শিক্ষকমণ্ডলী</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> নোটিশ</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/gallery.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> গ্যালারি</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/result.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> ফলাফল</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> যোগাযোগ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>রিসোর্স</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/resources.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> লেকচার নোট</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/resources.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> ই-বুক</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/resources.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> সফটওয়্যার</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> পরীক্ষার সূচি</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/notice.php"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg> একাডেমিক ক্যালেন্ডার</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>যোগাযোগের তথ্য</h4>
                <ul class="footer-links">
                    <?php if ($siteAddress): ?><li><a href="javascript:void(0)" style="cursor:default;"><?php echo clean($siteAddress); ?></a></li><?php endif; ?>
                    <?php if ($sitePhone): ?><li><a href="tel:<?php echo clean($sitePhone); ?>"><?php echo clean($sitePhone); ?></a></li><?php endif; ?>
                    <?php if ($siteEmail): ?><li><a href="mailto:<?php echo clean($siteEmail); ?>"><?php echo clean($siteEmail); ?></a></li><?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <?php echo $footerText; ?>
        </div>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
<script>
// Semester Navigator
document.addEventListener('DOMContentLoaded', function() {
    const navBtns = document.querySelectorAll('.semester-nav-btn');
    const cards = document.querySelectorAll('.semester-card');
    const rocket = document.getElementById('rocketIcon');
    const progress = document.getElementById('rocketProgress');

    function activateSemester(num) {
        // Update nav buttons
        navBtns.forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector('.semester-nav-btn[data-semester="' + num + '"]');
        if (activeBtn) activeBtn.classList.add('active');

        // Update cards
        cards.forEach(card => card.classList.remove('active'));
        const activeCard = document.getElementById('semCard' + num);
        if (activeCard) {
            activeCard.classList.add('active');
            // Animate card entry
            activeCard.style.animation = 'none';
            activeCard.offsetHeight;
            activeCard.style.animation = 'cardSlideIn 0.5s ease forwards';
        }

        // Move rocket
        const progressPct = ((num - 1) / 7) * 100;
        if (progress) progress.style.width = progressPct + '%';
        if (rocket) {
            rocket.style.left = progressPct + '%';
            rocket.style.transform = 'translate(-50%, -50%) rotate(-45deg)';
        }
    }

    navBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            activateSemester(parseInt(this.dataset.semester));
        });
    });

    // Next semester buttons
    document.querySelectorAll('.btn-next-sem').forEach(btn => {
        btn.addEventListener('click', function() {
            activateSemester(parseInt(this.dataset.next));
        });
    });

    // Scroll-based semester reveal
    const semSection = document.getElementById('semester-section');
    if (semSection) {
        let currentVisible = 1;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    activateSemester(currentVisible);
                }
            });
        }, { threshold: 0.2 });
        observer.observe(semSection);
    }

    // Scroll animations for sections
    const animElements = document.querySelectorAll('.feature-card, .mv-card, .teacher-card, .about-stat-card, .notice-card');
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    animElements.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease ' + (i % 4) * 0.1 + 's, transform 0.5s ease ' + (i % 4) * 0.1 + 's';
        scrollObserver.observe(el);
    });
});
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

// update at 2026-05-17 13:23:23

// minor refactor
