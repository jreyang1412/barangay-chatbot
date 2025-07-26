<?php
// user_handler.php - Handles user requests
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'start_chat':
        startChat();
        break;
    case 'send_message':
        sendMessage();
        break;
    case 'send_location':
        sendLocation();
        break;
    case 'get_messages':
        getMessages();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function startChat() {
    global $pdo;
    
    $userId = $_POST['user_id'] ?? '';
    $helpType = $_POST['help_type'] ?? '';
    
    if (!$userId || !$helpType) {
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    // Insert initial help request message
    $helpMessages = [
        'emergency' => '🚨 EMERGENCY HELP REQUESTED - Please respond immediately!',
        'technical' => '🔧 Technical support needed - User is waiting for assistance.',
        'general' => '💬 General inquiry started - User has a question.'
    ];
    
    $message = $helpMessages[$helpType] ?? 'Help requested';
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (user_id, sender_type, message, help_type) 
        VALUES (?, 'user', ?, ?)
    ");
    $stmt->execute([$userId, $message, $helpType]);
    
    // Update conversation status
    updateActivity($pdo, $userId);
    
    echo json_encode(['success' => true]);
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
        VALUES (?, 'user', ?)
    ");
    $stmt->execute([$userId, $message]);
    
    // Update conversation activity
    updateActivity($pdo, $userId);
    
    echo json_encode(['success' => true]);
}

function sendLocation() {
    global $pdo;
    
    $userId = $_POST['user_id'] ?? '';
    $lat = $_POST['lat'] ?? '';
    $lng = $_POST['lng'] ?? '';
    $helpType = $_POST['help_type'] ?? '';
    
    if (!$userId || !$lat || !$lng) {
        echo json_encode(['error' => 'Missing location data']);
        return;
    }
    
    $message = "📍 Emergency location shared";
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (user_id, sender_type, message, help_type, location_lat, location_lng) 
        VALUES (?, 'user', ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $message, $helpType, $lat, $lng]);
    
    // Update conversation activity
    updateActivity($pdo, $userId);
    
    echo json_encode(['success' => true]);
}

function getMessages() {
    global $pdo;
    
    $userId = $_GET['user_id'] ?? '';
    
    if (!$userId) {
        echo json_encode(['error' => 'Missing user ID']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT sender_type, message, location_lat, location_lng, created_at
        FROM messages 
        WHERE user_id = ? AND is_active = TRUE
        ORDER BY created_at ASC
    ");
    $stmt->execute([$userId]);
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages);
}
?>