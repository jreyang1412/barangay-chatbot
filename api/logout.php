<?php
// logout.php
require_once 'config/database.php';
require_once 'config/session.php';

if (isset($_SESSION['user_id'])) {
    // Update user offline status
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->prepare("UPDATE users SET is_online = 0, last_seen = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Destroy session
session_destroy();

// Redirect to login
header("Location: login.php");
exit();
?>