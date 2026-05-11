<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cst_department');
define('DB_USER', 'root');
define('DB_PASS', '');
// Auto-detect SITE_URL from current directory (works on any XAMPP/WAMP setup)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('SITE_URL', $protocol . '://' . $host . $basePath);

// Site Paths
define('BASE_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads');
define('UPLOAD_URL', SITE_URL . '/assets/uploads');
define('UPLOAD_DIR', BASE_PATH . '/assets/uploads/');

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar']);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Database Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Load Site Settings
function getSiteSettings($pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

$site_settings = getSiteSettings($pdo);

// Helper to get setting
function siteSetting($key, $default = '') {
    global $site_settings;
    return isset($site_settings[$key]) ? $site_settings[$key] : $default;
}

// Define SETTINGS constant for use in header/footer
define('SETTINGS', $site_settings);

// getSettings() for footer.php compatibility
function getSettings() {
    global $site_settings;
    if (!defined('SETTINGS')) {
        define('SETTINGS', $site_settings);
    }
}

// Site helper functions used by header.php and footer.php
function siteLogo() {
    return siteSetting('site_logo', '');
}

function siteName() {
    return defined('SITE_NAME') ? SITE_NAME : siteSetting('site_name', 'CST Department');
}

function siteTagline() {
    return defined('SITE_TAGLINE') ? SITE_TAGLINE : siteSetting('site_tagline', 'Department of Computer Science & Technology');
}

function siteFavicon() {
    return siteSetting('site_favicon', '');
}

// Site Name
define('SITE_NAME', siteSetting('site_name', 'CST Department'));
define('SITE_TAGLINE', siteSetting('site_tagline', 'Department of Computer Science & Technology'));

// Check Admin Login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/control-panel/login.php');
        exit;
    }
}

// Flash Message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Time Ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

// Sanitize
function clean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// Slug Generator
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

// Get Category Name by ID
function getCategoryName($pdo, $id) {
    if (!$id) return 'Uncategorized';
    try {
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row['name'] : 'Uncategorized';
    } catch (Exception $e) {
        return 'Uncategorized';
    }
}

// Safe query helper
function safeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (Exception $e) {
        error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

// File Upload Helper
function uploadFile($file, $dir, $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','ppt','pptx','xls','xlsx','zip']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return null;
    
    $newName = uniqid() . '_' . time() . '.' . $ext;
    $targetDir = UPLOAD_PATH . '/' . $dir;
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $targetPath = $targetDir . '/' . $newName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $dir . '/' . $newName;
    }
    return null;
}

// Delete File
function deleteFile($filepath) {
    if ($filepath && file_exists(BASE_PATH . '/' . $filepath)) {
        unlink(BASE_PATH . '/' . $filepath);
    }
}

// Pagination
function getPagination($currentPage, $totalItems, $perPage = 10) {
    $totalPages = max(1, ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'current' => $currentPage,
        'total' => $totalPages,
        'offset' => $offset,
        'per_page' => $perPage,
        'total_items' => $totalItems
    ];
}
