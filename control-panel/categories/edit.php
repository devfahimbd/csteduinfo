<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header('Location: ../' . basename(__DIR__) . '.php' . ($id ? '?action=edit&id=' . $id : ''));
exit;

// auto-update at 2026-05-17 14:41:51
