<?php
// user_handler.php - Complete working version
header('Content-Type: application/json');

// Enable error reporting but don't display errors (they interfere with JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0); // IMPORTANT: Don't display errors to browser
ini_set('log_errors', 1); // Log errors instead

// Database connection
try {
    $pdo = new PDO("mysql:host=sql308.infinityfree.com;dbname=if0_38484017_barangay_chatbot", 
                   "if0_38484017", "8QPEk7NCVncLbL");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$action = $_POST['action'] ?? '';

switch($action) {
    case 'send_message':
        sendTextMessage();
        break;
    case 'send_image':
        sendImageMessage();
        break;
    case 'send_location':
        sendLocation();
        break;
    case 'get_messages':
        getMessages();
        break;
    case 'get_user_conversations':
        getUserConversations();
        break;
    case 'debug_conversation':
        debugConversation();
        break;
    case 'close_conversation':
        closeConversation();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function sendTextMessage() {
    global $pdo;
    
    // Get and validate inputs
    $conversationId = trim($_POST['conversation_id'] ?? '');
    $userId = trim($_POST['user_id'] ?? '');
    $userName = trim($_POST['user_name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $helpType = trim($_POST['help_type'] ?? 'general');
    $barangay = trim($_POST['barangay'] ?? 'Unknown');
    $city = trim($_POST['city'] ?? 'Unknown');
    
    // Validate required fields
    if (empty($conversationId) || empty($userId) || empty($message)) {
        echo json_encode(['error' => 'Missing required fields: conversation_id, user_id, or message']);
        return;
    }
    
    // Set defaults
    if (empty($userName)) {
        $userName = 'User ' . $userId;
    }
    
    // Format user ID
    $formattedUserId = (strpos($userId, 'user_') === 0) ? $userId : 'user_' . $userId;
    
    // Create conversation if it doesn't exist
    createConversationIfNotExists($conversationId, $formattedUserId, $userName, $helpType, $city, $barangay);
    
    try {
        // Insert message
        $stmt = $pdo->prepare("
            INSERT INTO messages (
                conversation_id, user_id, sender_type, message_type, message, help_type, created_at
            ) 
            VALUES (?, ?, 'user', 'text', ?, ?, NOW())
        ");
        
        $result = $stmt->execute([$conversationId, $formattedUserId, $message, $helpType]);
        
        if ($result) {
            updateConversationActivity($conversationId);
            echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['error' => 'Failed to save message']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function sendImageMessage() {
    global $pdo;
    
    $conversationId = trim($_POST['conversation_id'] ?? '');
    $userId = trim($_POST['user_id'] ?? '');
    $userName = trim($_POST['user_name'] ?? '');
    $caption = trim($_POST['caption'] ?? '');
    $helpType = trim($_POST['help_type'] ?? 'general');
    $barangay = trim($_POST['barangay'] ?? 'Unknown');
    $city = trim($_POST['city'] ?? 'Unknown');
    
    // Validate required fields
    if (empty($conversationId) || empty($userId)) {
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'No image uploaded']);
        return;
    }
    
    $uploadedFile = $_FILES['image'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['error' => 'Invalid file type']);
        return;
    }
    
    // Validate file size (5MB limit)
    if ($uploadedFile['size'] > 5 * 1024 * 1024) {
        echo json_encode(['error' => 'File too large']);
        return;
    }
    
    // Create upload directory
    $uploadDir = 'uploads/images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate filename
    $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    $filename = time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move file
    if (move_uploaded_file($uploadedFile['tmp_name'], $filepath)) {
        $formattedUserId = (strpos($userId, 'user_') === 0) ? $userId : 'user_' . $userId;
        if (empty($userName)) $userName = 'User ' . $userId;
        
        createConversationIfNotExists($conversationId, $formattedUserId, $userName, $helpType, $city, $barangay);
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages (
                    conversation_id, user_id, sender_type, message_type, message, 
                    file_path, file_name, file_size, file_type, help_type, created_at
                ) 
                VALUES (?, ?, 'user', 'image', ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $conversationId, $formattedUserId, $caption, $filepath,
                $uploadedFile['name'], $uploadedFile['size'], $mimeType, $helpType
            ]);
            
            updateConversationActivity($conversationId);
            
            echo json_encode([
                'success' => true,
                'message_id' => $pdo->lastInsertId(),
                'file_path' => $filepath
            ]);
            
        } catch (Exception $e) {
            unlink($filepath);
            echo json_encode(['error' => 'Failed to save image: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Failed to upload file']);
    }
}

function sendLocation() {
    global $pdo;
    
    $conversationId = trim($_POST['conversation_id'] ?? '');
    $userId = trim($_POST['user_id'] ?? '');
    $userName = trim($_POST['user_name'] ?? '');
    $lat = trim($_POST['lat'] ?? '');
    $lng = trim($_POST['lng'] ?? '');
    $helpType = trim($_POST['help_type'] ?? 'emergency');
    $barangay = trim($_POST['barangay'] ?? 'Unknown');
    $city = trim($_POST['city'] ?? 'Unknown');
    
    if (empty($conversationId) || empty($userId) || empty($lat) || empty($lng)) {
        echo json_encode(['error' => 'Missing location data']);
        return;
    }
    
    $formattedUserId = (strpos($userId, 'user_') === 0) ? $userId : 'user_' . $userId;
    if (empty($userName)) $userName = 'User ' . $userId;
    
    createConversationIfNotExists($conversationId, $formattedUserId, $userName, $helpType, $city, $barangay);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (
                conversation_id, user_id, sender_type, message_type, message, 
                location_lat, location_lng, help_type, created_at
            ) 
            VALUES (?, ?, 'user', 'location', '📍 Emergency location shared', ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$conversationId, $formattedUserId, $lat, $lng, $helpType]);
        
        // Update conversation with location
        $updateLocationStmt = $pdo->prepare("
            UPDATE conversations 
            SET location_lat = ?, location_lng = ?, last_activity = NOW()
            WHERE id = ?
        ");
        $updateLocationStmt->execute([$lat, $lng, $conversationId]);
        
        updateConversationActivity($conversationId);
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to save location: ' . $e->getMessage()]);
    }
}

function getMessages() {
    global $pdo;
    
    $conversationId = trim($_POST['conversation_id'] ?? '');
    
    if (empty($conversationId)) {
        echo json_encode([]);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM messages 
            WHERE conversation_id = ? AND is_active = 1
            ORDER BY created_at ASC
        ");
        $stmt->execute([$conversationId]);
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($messages);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to load messages']);
    }
}

function createConversationIfNotExists($conversationId, $formattedUserId, $userName, $helpType, $city, $barangay) {
    global $pdo;
    
    try {
        // Check if conversation exists
        $stmt = $pdo->prepare("SELECT id FROM conversations WHERE id = ?");
        $stmt->execute([$conversationId]);
        
        if (!$stmt->fetch()) {
            // Create new conversation
            $insertStmt = $pdo->prepare("
                INSERT INTO conversations (
                    id, user_id, user_name, city, barangay, help_type, 
                    status, priority, created_at, last_activity
                ) 
                VALUES (?, ?, ?, ?, ?, ?, 'waiting', ?, NOW(), NOW())
            ");
            
            $priority = $helpType === 'emergency' ? 'high' : 'normal';
            
            $insertStmt->execute([
                $conversationId, $formattedUserId, $userName,
                $city, $barangay, $helpType, $priority
            ]);
        }
    } catch (Exception $e) {
        // Continue silently
    }
}

function updateConversationActivity($conversationId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE conversations SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$conversationId]);
    } catch (Exception $e) {
        // Continue silently
    }
}

function getUserConversations() {
    global $pdo;
    
    $userId = trim($_POST['user_id'] ?? '');
    
    if (empty($userId)) {
        echo json_encode(['error' => 'User ID required']);
        return;
    }
    
    $formattedUserId = (strpos($userId, 'user_') === 0) ? $userId : 'user_' . $userId;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, COUNT(m.id) as message_count
            FROM conversations c
            LEFT JOIN messages m ON c.id = m.conversation_id
            WHERE c.user_id = ?
            GROUP BY c.id
            ORDER BY c.last_activity DESC
        ");
        
        $stmt->execute([$formattedUserId]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get conversations']);
    }
}

function debugConversation() {
    global $pdo;
    
    $conversationId = trim($_POST['conversation_id'] ?? '');
    
    if (empty($conversationId)) {
        echo json_encode(['error' => 'Conversation ID required']);
        return;
    }
    
    try {
        $convStmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ?");
        $convStmt->execute([$conversationId]);
        $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);
        
        $msgStmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE conversation_id = ?");
        $msgStmt->execute([$conversationId]);
        $messageCount = $msgStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'conversation' => $conversation,
            'message_count' => $messageCount['count'],
            'conversation_id' => $conversationId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Debug failed']);
    }
}

function closeConversation() {
    global $pdo;
    
    $conversationId = trim($_POST['conversation_id'] ?? '');
    $userId = trim($_POST['user_id'] ?? '');
    
    if (empty($conversationId) || empty($userId)) {
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $formattedUserId = (strpos($userId, 'user_') === 0) ? $userId : 'user_' . $userId;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE conversations 
            SET status = 'closed', last_activity = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$conversationId, $formattedUserId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Conversation not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to close conversation']);
    }
}
?>