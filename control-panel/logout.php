<?php
require_once '../includes/config.php';
requireAdmin();

// Unset all session variables
$_SESSION = [];

// Destroy the session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

// Start new session for flash message
session_start();
$_SESSION['flash'] = ['type' => 'success', 'message' => 'You have been logged out.'];
header('Location: login.php');
exit;

// minor refactor
