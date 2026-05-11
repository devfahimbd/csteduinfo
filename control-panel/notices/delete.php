<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $notice = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notice = false;
}

if (!$notice) {
    setFlash('error', 'Notice not found.');
    header('Location: index.php');
    exit;
}

// Delete associated files
if (!empty($notice['file'])) {
    deleteFile($notice['file']);
}
if (!empty($notice['thumbnail'])) {
    deleteFile($notice['thumbnail']);
}

try {
    $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Notice deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Failed to delete notice.');
}

header('Location: index.php');
exit;
