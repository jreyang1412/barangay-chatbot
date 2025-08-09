<?php
header('Content-Type: application/json');
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database configuration (same as your update_user_status.php)
$host = 'sql308.infinityfree.com';
$dbname = 'if0_38484017_barangay_chatbot';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT id, first_name, middle_name, last_name, mobile_number, city, barangay, email, is_active, status, created_at, updated_at FROM users WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>