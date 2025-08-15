<?php
// debug_admin_handler.php - Simple test version
header('Content-Type: application/json');

// Database connection test
try {
    $pdo = new PDO("mysql:host=sql308.infinityfree.com;dbname=if0_38484017_barangay_chatbot", 
                   "if0_38484017", "8QPEk7NCVncLbL");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'get_conversations') {
        // Simple test query
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM conversations");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Database connection working',
            'total_conversations' => $result['total'],
            'action' => $action,
            'post_data' => $_POST
        ]);
    } else {
        echo json_encode([
            'error' => 'Invalid action',
            'received_action' => $action,
            'post_data' => $_POST
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'error' => 'General error: ' . $e->getMessage()
    ]);
}
?>