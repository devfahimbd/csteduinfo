<?php
require_once 'includes/config.php';

// Get item type and ID
$type = isset($_GET['type']) ? clean($_GET['type']) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$type || !$id) {
    header('Location: ' . SITE_URL);
    exit;
}

// Determine table and column based on type
switch ($type) {
    case 'resource':
        $table = 'resources';
        $col = 'file_path';
        break;
    case 'gallery':
        $table = 'gallery';
        $col = 'image';
        break;
    case 'teacher':
        $table = 'teachers';
        $col = 'image';
        break;
    default:
        header('Location: ' . SITE_URL);
        exit;
}

try {
    $stmt = $pdo->prepare("SELECT {$col} FROM {$table} WHERE id = ? AND status = 1");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item || empty($item[$col])) {
        header('Location: ' . SITE_URL);
        exit;
    }
    
    $filepath = BASE_PATH . '/' . $item[$col];
    
    if (!file_exists($filepath)) {
        header('Location: ' . SITE_URL);
        exit;
    }
    
    // Get file info
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip' => 'application/zip',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    
    $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    $mimeType = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
    
    // Force download for documents, inline for images
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    
    if ($isImage) {
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
    } else {
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    }
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: public, max-age=3600');
    
    readfile($filepath);
    exit;
    
} catch (Exception $e) {
    header('Location: ' . SITE_URL);
    exit;
}
