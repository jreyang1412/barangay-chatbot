<?php
// ajax/get_conversations.php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../classes/Chatbot.php';

checkLogin();

header('Content-Type: application/json');

if (!isBarangayOfficial()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $chatbot = new Chatbot();
    $conversations = $chatbot->getActiveConversations($_SESSION['user_id']);
    
    echo json_encode(['success' => true, 'conversations' => $conversations]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>