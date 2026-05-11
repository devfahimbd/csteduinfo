<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teacher = false;
}

if (!$teacher) {
    setFlash('error', 'Faculty member not found.');
    header('Location: index.php');
    exit;
}

// Delete image
if (!empty($teacher['image'])) {
    deleteFile($teacher['image']);
}

try {
    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Faculty member deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Failed to delete faculty member.');
}

header('Location: index.php');
exit;
