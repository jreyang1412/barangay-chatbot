<?php
// admin_handler.php - Complete version compatible with existing database structure
header('Content-Type: application/json');

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
    case 'get_conversations':
        getConversations();
        break;
    case 'get_messages':
        getMessages();
        break;
    case 'send_admin_message':
        sendAdminMessage();
        break;
    case 'close_conversation':
        closeConversation();
        break;
    case 'update_conversation_status':
        updateConversationStatus();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getConversations() {
    global $pdo;
    
    $adminId = $_POST['admin_id'] ?? '';
    $city = $_POST['city'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    
    if (!$adminId) {
        echo json_encode(['error' => 'Admin ID required']);
        return;
    }
    
    try {
        // Get conversations with user details based on your actual table structure
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                COUNT(m.id) as message_count,
                COALESCE(MAX(m.created_at), c.created_at) as last_activity_calc,
                COUNT(CASE WHEN m.sender_type = 'user' AND m.is_read_by_admin = 0 THEN 1 END) as unread_count,
                CASE 
                    WHEN c.user_name IS NOT NULL AND c.user_name != '' THEN c.user_name
                    WHEN u.first_name IS NOT NULL AND u.last_name IS NOT NULL 
                        THEN CONCAT(u.first_name, ' ', u.last_name)
                    WHEN u.email IS NOT NULL THEN u.email
                    WHEN c.user_id LIKE 'user_%' THEN CONCAT('User ', REPLACE(c.user_id, 'user_', ''))
                    ELSE CONCAT('User ', c.user_id)
                END as user_display_name,
                u.first_name,
                u.last_name,
                u.email,
                u.status as user_status,
                COALESCE(NULLIF(c.barangay, ''), u.barangay, 'Unknown') as display_barangay,
                COALESCE(NULLIF(c.city, ''), u.city, 'Unknown') as display_city
            FROM conversations c
            LEFT JOIN messages m ON c.id = m.conversation_id AND m.is_active = 1
            LEFT JOIN users u ON (
                CASE 
                    WHEN c.user_id LIKE 'user_%' THEN REPLACE(c.user_id, 'user_', '')
                    ELSE c.user_id
                END = u.id
            )
            WHERE c.status != 'closed'
            GROUP BY c.id
            ORDER BY 
                CASE WHEN c.help_type = 'emergency' THEN 1 ELSE 2 END,
                COALESCE(MAX(m.created_at), c.created_at) DESC
        ");
        
        $stmt->execute();
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the conversations for display
        foreach ($conversations as &$conv) {
            // Ensure proper display name
            if (empty($conv['user_display_name']) || $conv['user_display_name'] === ' ') {
                $conv['user_display_name'] = 'User ' . str_replace('user_', '', $conv['user_id']);
            }
            
            // Format last activity
            $conv['formatted_last_activity'] = formatTimeAgo($conv['last_activity_calc']);
            
            // Add user status badge
            $conv['user_status_badge'] = $conv['user_status'] ?? 'unknown';
            
            // Set conversation urgency
            $conv['is_urgent'] = ($conv['help_type'] === 'emergency' || $conv['unread_count'] > 0);
            
            // Clean up the user_id for display (remove user_ prefix if present)
            $conv['clean_user_id'] = (strpos($conv['user_id'], 'user_') === 0) ? 
                str_replace('user_', '', $conv['user_id']) : $conv['user_id'];
        }
        
        echo json_encode([
            'success' => true,
            'conversations' => $conversations
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get conversations: ' . $e->getMessage()]);
    }
}

function getMessages() {
    global $pdo;
    
    $conversationId = $_POST['conversation_id'] ?? '';
    
    if (!$conversationId) {
        echo json_encode(['error' => 'Conversation ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                CASE 
                    WHEN m.sender_type = 'user' THEN 
                        CASE 
                            WHEN c.user_name IS NOT NULL AND c.user_name != '' THEN c.user_name
                            WHEN u.first_name IS NOT NULL AND u.last_name IS NOT NULL 
                                THEN CONCAT(u.first_name, ' ', u.last_name)
                            WHEN u.email IS NOT NULL THEN u.email
                            WHEN m.user_id LIKE 'user_%' THEN CONCAT('User ', REPLACE(m.user_id, 'user_', ''))
                            ELSE CONCAT('User ', m.user_id)
                        END
                    WHEN m.sender_type = 'admin' THEN 
                        COALESCE(
                            NULLIF(m.admin_name, ''),
                            'Admin'
                        )
                    ELSE 'System'
                END as sender_name
            FROM messages m
            LEFT JOIN conversations c ON m.conversation_id = c.id
            LEFT JOIN users u ON (
                CASE 
                    WHEN m.user_id LIKE 'user_%' THEN REPLACE(m.user_id, 'user_', '')
                    ELSE m.user_id
                END = u.id
            )
            WHERE m.conversation_id = ? AND m.is_active = 1
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$conversationId]);
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark user messages as read by admin
        $markReadStmt = $pdo->prepare("
            UPDATE messages 
            SET is_read_by_admin = 1 
            WHERE conversation_id = ? AND sender_type = 'user' AND is_read_by_admin = 0
        ");
        $markReadStmt->execute([$conversationId]);
        
        echo json_encode($messages);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get messages: ' . $e->getMessage()]);
    }
}

function sendAdminMessage() {
    global $pdo;
    
    $conversationId = $_POST['conversation_id'] ?? '';
    $adminId = $_POST['admin_id'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (!$conversationId || !$adminId || !$message) {
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    try {
        // Get conversation details to get user_id
        $convStmt = $pdo->prepare("SELECT user_id FROM conversations WHERE id = ?");
        $convStmt->execute([$conversationId]);
        $conv = $convStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$conv) {
            echo json_encode(['error' => 'Conversation not found']);
            return;
        }
        
        // Get admin name using correct column structure
        $adminName = 'Admin';
        try {
            $adminStmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
            $adminStmt->execute([$adminId]);
            $adminInfo = $adminStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($adminInfo && $adminInfo['username']) {
                $adminName = $adminInfo['username'];
            }
        } catch (Exception $e) {
            // If admin table query fails, just use 'Admin'
            $adminName = 'Admin';
        }
        
        // Insert admin message
        $stmt = $pdo->prepare("
            INSERT INTO messages (
                conversation_id, user_id, sender_type, message_type, message, 
                admin_id, admin_name, created_at
            ) 
            VALUES (?, ?, 'admin', 'text', ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$conversationId, $conv['user_id'], $message, $adminId, $adminName]);
        
        // Update conversation status and activity
        $updateStmt = $pdo->prepare("
            UPDATE conversations 
            SET status = 'active', last_activity = NOW(), assigned_admin_id = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$adminId, $conversationId]);
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to send message: ' . $e->getMessage()]);
    }
}

function closeConversation() {
    global $pdo;
    
    $conversationId = $_POST['conversation_id'] ?? '';
    $adminId = $_POST['admin_id'] ?? '';
    
    if (!$conversationId || !$adminId) {
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE conversations 
            SET status = 'closed', assigned_admin_id = ?, last_activity = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$adminId, $conversationId]);
        
        if ($stmt->rowCount() > 0) {
            // Add a system message about closing
            $systemMsgStmt = $pdo->prepare("
                INSERT INTO messages (
                    conversation_id, user_id, sender_type, message_type, message, 
                    admin_id, created_at
                ) 
                SELECT id, user_id, 'system', 'text', 'Conversation closed by admin', ?, NOW()
                FROM conversations WHERE id = ?
            ");
            $systemMsgStmt->execute([$adminId, $conversationId]);
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Conversation not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to close conversation: ' . $e->getMessage()]);
    }
}

function updateConversationStatus() {
    global $pdo;
    
    $conversationId = $_POST['conversation_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $adminId = $_POST['admin_id'] ?? '';
    
    if (!$conversationId || !$status || !$adminId) {
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE conversations 
            SET status = ?, assigned_admin_id = ?, last_activity = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $adminId, $conversationId]);
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to update status: ' . $e->getMessage()]);
    }
}

// Helper function to format time ago
function formatTimeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}

// Additional function to get conversation statistics
function getConversationStats() {
    global $pdo;
    
    $adminId = $_POST['admin_id'] ?? '';
    $city = $_POST['city'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_conversations,
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting_count,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
                COUNT(CASE WHEN help_type = 'emergency' AND status != 'closed' THEN 1 END) as emergency_count,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as today_count
            FROM conversations 
            WHERE (city = ? OR barangay = ? OR ? = 'admin')
        ");
        
        $stmt->execute([$city, $barangay, $city]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get stats: ' . $e->getMessage()]);
    }
}

// Handle stats request
if ($action === 'get_stats') {
    getConversationStats();
}
?>