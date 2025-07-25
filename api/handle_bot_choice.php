<?php
// ajax/handle_bot_choice.php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../classes/Chatbot.php';

checkLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $conversationId = $input['conversation_id'] ?? null;
    $optionId = $input['option_id'] ?? '';
    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;
    
    if ($conversationId && $optionId) {
        $chatbot = new Chatbot();
        $result = $chatbot->handleUserChoice($conversationId, $_SESSION['user_id'], $optionId, $latitude, $longitude);
        
        echo json_encode(['success' => true, 'result' => $result]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
