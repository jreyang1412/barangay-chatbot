<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Debug session (remove this after testing)
error_log("Session debug: " . print_r($_SESSION, true));

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access',
        'debug' => [
            'has_admin_id' => isset($_SESSION['admin_id']),
            'user_type' => $_SESSION['user_type'] ?? 'not_set',
            'all_session_keys' => array_keys($_SESSION)
        ]
    ]);
    exit;
}

// Database configuration
$host = 'sql308.infinityfree.com';
$dbname = 'if0_38484017_barangay_chatbot';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Debug input (remove this after testing)
error_log("Raw input: " . $rawInput);
error_log("Parsed input: " . print_r($input, true));

if (!$input || !isset($input['user_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid input data',
        'debug' => [
            'raw_input' => $rawInput,
            'parsed_input' => $input
        ]
    ]);
    exit;
}

$userId = (int)$input['user_id'];
$status = $input['status'];

// Validate status - matches your ENUM values
if (!in_array($status, ['basic', 'verified'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value. Must be "basic" or "verified"']);
    exit;
}

try {
    // Check if user exists and is active
    $checkStmt = $pdo->prepare("SELECT id, status FROM users WHERE id = ? AND is_active = 1");
    $checkStmt->execute([$userId]);
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
        exit;
    }
    
    // Update user status - the updated_at will auto-update due to ON UPDATE CURRENT_TIMESTAMP
    $updateStmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $result = $updateStmt->execute([$status, $userId]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'User status updated successfully',
            'user_id' => $userId,
            'old_status' => $user['status'],
            'new_status' => $status,
            'rows_affected' => $updateStmt->rowCount()
        ]);
        
        // Log success
        error_log("Successfully updated user $userId from {$user['status']} to $status");
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to execute update query']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error in update_user_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}
?>