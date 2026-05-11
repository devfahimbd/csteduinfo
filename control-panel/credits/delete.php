<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM credits WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $credit = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $credit = false;
}

if (!$credit) {
    setFlash('error', 'Credit not found.');
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM credits WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Credit deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Failed to delete credit.');
}

header('Location: index.php');
exit;
