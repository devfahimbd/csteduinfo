<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $sponsor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sponsor = false;
}

if (!$sponsor) {
    setFlash('error', 'Sponsor not found.');
    header('Location: index.php');
    exit;
}

if (!empty($sponsor['logo'])) {
    deleteFile($sponsor['logo']);
}

try {
    $stmt = $pdo->prepare("DELETE FROM sponsors WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Sponsor deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Failed to delete sponsor.');
}

header('Location: index.php');
exit;
