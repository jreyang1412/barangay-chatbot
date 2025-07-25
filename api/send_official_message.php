<?php
// ajax/send_official_message.php
require_once '../config/database.php';
require_once '../config/session.php';

checkLogin();

header('Content-Type: application/json');

if (!isBarangayOfficial()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $conversationId = $input['conversation_id'] ?? null;
    $message = $input['message'] ?? '';
    
    if ($conversationId && $message) {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Insert message
        $stmt = $conn->prepare("
            INSERT INTO messages (conversation_id, sender_id, message_text, message_type) 
            VALUES (?, ?, ?, 'text')
        ");
        $success = $stmt->execute([$conversationId, $_SESSION['user_id'], $message]);
        
        // Update conversation timestamp
        if ($success) {
            $stmt = $conn->prepare("
                UPDATE conversations 
                SET updated_at = CURRENT_TIMESTAMP, barangay_official_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $conversationId]);
        }
        
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>