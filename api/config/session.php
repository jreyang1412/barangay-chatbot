<?php
// Start session with secure settings for production
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit();
    }
}

function isBarangayOfficial() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'barangay_official';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}
?>