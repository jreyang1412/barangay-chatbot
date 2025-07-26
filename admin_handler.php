<?php
// admin_handler.php - Handles admin requests
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'get_conversations':
        getConversations();
        break;
    case 'get_messages':
        getMessages();
        break;
    case 'send_message':
        sendMessage();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getConversations() {
    global $pdo;
    
    // Clean up inactive conversations first
    cleanupInactiveConversations($pdo);
    
    // Get all conversations with latest message info
    $stmt = $pdo->prepare("
        SELECT 
            c.user_id,
            c.last_activity,
            c.status,
            m.help_type,
            (SELECT message FROM messages m2 
             WHERE m2.user_id = c.user_id 
             AND m2.is_active = TRUE 
             ORDER BY m2.created_at DESC LIMIT 1) as last_message
        FROM conversations c
        LEFT JOIN messages m ON c.user_id = m.user_id
        WHERE c.status IN ('waiting', 'active')
        GROUP BY c.user_id
        ORDER BY 
            CASE WHEN m.help_type = 'emergency' THEN 0 ELSE 1 END,
            c.last_activity DESC
    ");
    $stmt->execute();
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $statsStmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count
        FROM conversations 
        WHERE status IN ('waiting', 'active', 'closed')
        GROUP BY status
    ");
    $statsStmt->execute();
    $statsResult = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = ['waiting' => 0, 'active' => 0, 'closed' => 0, 'total' => 0];
    foreach($statsResult as $stat) {
        $stats[$stat['status']] = $stat['count'];
        $stats['total'] += $stat['count'];
    }
    
    echo json_encode([
        'conversations' => $conversations,
        'stats' => $stats
    ]);
}

function getMessages() {
    global $pdo;
    
    $userId = $_GET['user_id'] ?? '';
    
    if (!$userId) {
        echo json_encode(['error' => 'Missing user ID']);
        return;
    }
    
    // For admin view, show all messages including inactive admin messages
    // But only active user messages
    $stmt = $pdo->prepare("
        SELECT 
            sender_type, 
            message, 
            help_type,
            location_lat, 
            location_lng, 
            created_at
        FROM messages 
        WHERE user_id = ? 
        AND (
            (sender_type = 'user' AND is_active = TRUE) 
            OR sender_type = 'admin'
        )
        ORDER BY created_at ASC
    ");
    $stmt->execute([$userId]);
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages);
}

function sendMessage() {
    global $pdo;
    
    $userId = $_POST['user_id'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (!$userId || !$message) {
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (user_id, sender_type, message) 
        VALUES (?, 'admin', ?)
    ");
    $stmt->execute([$userId, $message]);
    
    // Update conversation activity and status
    updateActivity($pdo, $userId);
    
    // Set conversation to active if it was waiting
    $updateStmt = $pdo->prepare("
        UPDATE conversations 
        SET status = 'active' 
        WHERE user_id = ? AND status = 'waiting'
    ");
    $updateStmt->execute([$userId]);
    
    echo json_encode(['success' => true]);
}
?>