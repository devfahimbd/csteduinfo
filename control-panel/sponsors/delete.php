<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header('Location: ../' . basename(__DIR__) . '.php' . ($id ? '?action=delete&id=' . $id : ''));
exit;

// performance improvement
