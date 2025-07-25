<?php
// index.php (Landing page that redirects to appropriate dashboard)
require_once 'config/session.php';

if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to appropriate dashboard
    if ($_SESSION['user_type'] === 'barangay_official') {
        header("Location: official_dashboard.php");
    } else {
        header("Location: resident_chat.php");
    }
    exit();
} else {
    // User not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>

