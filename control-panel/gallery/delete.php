<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $image = false;
}

if (!$image) {
    setFlash('error', 'Image not found.');
    header('Location: index.php');
    exit;
}

if (!empty($image['image'])) {
    deleteFile($image['image']);
}

try {
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Image deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Failed to delete image.');
}

header('Location: index.php');
exit;
