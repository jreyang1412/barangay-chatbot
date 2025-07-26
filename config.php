<?php
// config.php - Database configuration
$host = 'sql308.infinityfree.com';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';
$database = 'if0_38484017_barangay_chatbot';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to generate unique user ID
function generateUserId() {
    return 'user_' . uniqid();
}

// Function to clean up inactive conversations (admin side only)
function cleanupInactiveConversations($pdo) {
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_active = FALSE 
        WHERE user_id IN (
            SELECT user_id FROM conversations 
            WHERE last_activity < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND status = 'active'
        )
        AND sender_type = 'admin'
    ");
    $stmt->execute();
    
    $stmt = $pdo->prepare("
        UPDATE conversations 
        SET status = 'closed' 
        WHERE last_activity < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        AND status = 'active'
    ");
    $stmt->execute();
}

// Function to update conversation activity
function updateActivity($pdo, $userId) {
    $stmt = $pdo->prepare("
        INSERT INTO conversations (user_id, last_activity, status) 
        VALUES (?, NOW(), 'active')
        ON DUPLICATE KEY UPDATE 
        last_activity = NOW(), 
        status = CASE WHEN status = 'closed' THEN 'active' ELSE status END
    ");
    $stmt->execute([$userId]);
}
?>