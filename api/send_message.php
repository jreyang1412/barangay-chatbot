<?php
// ajax/send_message.php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../classes/Chatbot.php';

checkLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $conversationId = $input['conversation_id'] ?? null;
    $message = $input['message'] ?? '';
    
    if ($conversationId && $message) {
        $chatbot = new Chatbot();
        $success = $chatbot->sendUserMessage($conversationId, $_SESSION['user_id'], $message);
        
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>