<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $resource = false;
}

if (!$resource) {
    setFlash('error', 'Resource not found.');
    header('Location: index.php');
    exit;
}

// Delete file
if (!empty($resource['file'])) {
    deleteFile($resource['file']);
}

try {
    $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Resource deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Failed to delete resource.');
}

header('Location: index.php');
exit;
