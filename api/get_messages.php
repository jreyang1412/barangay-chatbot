<?php
// ajax/get_messages.php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../classes/Chatbot.php';

checkLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conversationId = $_GET['conversation_id'] ?? null;
    
    if ($conversationId) {
        $chatbot = new Chatbot();
        $messages = $chatbot->getConversationMessages($conversationId);
        
        // Get current bot options if conversation is still in bot mode
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("SELECT conversation_type FROM conversations WHERE id = ?");
        $stmt->execute([$conversationId]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $botOptions = [];
        if ($conversation && $conversation['conversation_type'] === 'bot') {
            // Get the last bot message to determine current options
            $lastBotMessage = null;
            foreach (array_reverse($messages) as $message) {
                if ($message['message_type'] === 'bot_option') {
                    $lastBotMessage = $message;
                    break;
                }
            }
            
            if ($lastBotMessage && $lastBotMessage['bot_option_id']) {
                $stmt = $conn->prepare("SELECT has_children FROM bot_options WHERE id = ?");
                $stmt->execute([$lastBotMessage['bot_option_id']]);
                $option = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($option && $option['has_children']) {
                    $botOptions = $chatbot->getBotOptions($lastBotMessage['bot_option_id']);
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'messages' => $messages,
            'bot_options' => $botOptions
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>