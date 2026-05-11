<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
require_once '../auth-check.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $category = false;
}

if (!$category) {
    setFlash('error', 'Category not found.');
    header('Location: index.php');
    exit;
}

try {
    // Set NULL on foreign keys first
    $pdo->exec("UPDATE notices SET category_id = NULL WHERE category_id = $id");
    $pdo->exec("UPDATE teachers SET category_id = NULL WHERE category_id = $id");
    $pdo->exec("UPDATE resources SET category_id = NULL WHERE category_id = $id");
    $pdo->exec("UPDATE gallery SET category_id = NULL WHERE category_id = $id");

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Category deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Failed to delete category.');
}

header('Location: index.php');
exit;
