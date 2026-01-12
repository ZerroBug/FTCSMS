<?php
session_start();

/* ===================== ALLOW POST ONLY ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/* ===================== DESTROY SESSION ===================== */

// Clear session array
$_SESSION = [];

// Destroy session
session_destroy();

// Remove session cookie (extra security)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/* ===================== REDIRECT ===================== */
session_start();
$_SESSION['success'] = "You have been logged out successfully.";

header("Location:../index.php");
exit;