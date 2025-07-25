<?php
// ajax/update_conversation_status.php
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
    $status = $input['status'] ?? '';
    
    if ($conversationId && in_array($status, ['resolved', 'closed'])) {
        $database = new Database();
        $conn = $database->getConnection();
        
        try {
            // Update conversation status
            $stmt = $conn->prepare("UPDATE conversations SET status = ? WHERE id = ?");
            $success = $stmt->execute([$status, $conversationId]);
            
            // If emergency, update emergency report status
            if ($success && $status === 'resolved') {
                $stmt = $conn->prepare("
                    UPDATE emergency_reports 
                    SET status = 'resolved', resolved_at = CURRENT_TIMESTAMP 
                    WHERE conversation_id = ?
                ");
                $stmt->execute([$conversationId]);
            }
            
            echo json_encode(['success' => $success]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>