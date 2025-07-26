<?php
// classes/Chatbot.php
require_once 'config/database.php';

class Chatbot {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function startConversation($userId) {
        try {
            // Check if user has an active bot conversation
            $stmt = $this->conn->prepare("
                SELECT id FROM conversations 
                WHERE user_id = ? AND conversation_type = 'bot' AND status = 'active'
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($conversation) {
                return $conversation['id'];
            }
            
            // Create new conversation
            $stmt = $this->conn->prepare("
                INSERT INTO conversations (user_id, conversation_type, status) 
                VALUES (?, 'bot', 'active')
            ");
            $stmt->execute([$userId]);
            $conversationId = $this->conn->lastInsertId();
            
            // Send welcome message
            $this->sendBotMessage($conversationId, 'main_menu');
            
            return $conversationId;
        } catch (PDOException $e) {
            error_log("Error starting conversation: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendBotMessage($conversationId, $optionId) {
        try {
            // Get bot option details
            $stmt = $this->conn->prepare("SELECT * FROM bot_options WHERE id = ?");
            $stmt->execute([$optionId]);
            $option = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$option) return false;
            
            // Insert bot message
            $stmt = $this->conn->prepare("
                INSERT INTO messages (conversation_id, sender_id, message_text, message_type, bot_option_id) 
                VALUES (?, 1, ?, 'bot_option', ?)
            ");
            $stmt->execute([$conversationId, $option['response_text'], $optionId]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error sending bot message: " . $e->getMessage());
            return false;
        }
    }
    
    public function getBotOptions($parentId = 'main_menu') {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM bot_options 
                WHERE parent_id = ? AND is_active = 1 
                ORDER BY option_order
            ");
            $stmt->execute([$parentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting bot options: " . $e->getMessage());
            return [];
        }
    }
    
    public function handleUserChoice($conversationId, $userId, $optionId, $userLat = null, $userLong = null) {
        try {
            // Special handling for emergency
            if ($optionId === 'emergency') {
                return $this->handleEmergency($conversationId, $userId, $userLat, $userLong);
            }
            
            // Get the selected option
            $stmt = $this->conn->prepare("SELECT * FROM bot_options WHERE id = ?");
            $stmt->execute([$optionId]);
            $option = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$option) return false;
            
            // Record user's choice
            $stmt = $this->conn->prepare("
                INSERT INTO messages (conversation_id, sender_id, message_text, message_type, bot_option_id) 
                VALUES (?, ?, ?, 'text', ?)
            ");
            $stmt->execute([$conversationId, $userId, $option['option_text'], $optionId]);
            
            // Send bot response
            $this->sendBotMessage($conversationId, $optionId);
            
            // If option has children, don't transfer to human yet
            if ($option['has_children']) {
                return ['type' => 'bot_options', 'parent_id' => $optionId];
            } else {
                // Transfer to human agent for final resolution
                $this->transferToHuman($conversationId);
                return ['type' => 'transferred_to_human'];
            }
            
        } catch (PDOException $e) {
            error_log("Error handling user choice: " . $e->getMessage());
            return false;
        }
    }
    
    private function handleEmergency($conversationId, $userId, $lat, $long) {
        try {
            // Update conversation type to emergency
            $stmt = $this->conn->prepare("
                UPDATE conversations 
                SET conversation_type = 'emergency' 
                WHERE id = ?
            ");
            $stmt->execute([$conversationId]);
            
            // Create emergency report
            $stmt = $this->conn->prepare("
                INSERT INTO emergency_reports (user_id, conversation_id, latitude, longitude, status, priority) 
                VALUES (?, ?, ?, ?, 'pending', 'critical')
            ");
            $stmt->execute([$userId, $conversationId, $lat, $long]);
            
            // Send emergency message with location
            $stmt = $this->conn->prepare("
                INSERT INTO messages (conversation_id, sender_id, message_text, message_type, latitude, longitude) 
                VALUES (?, ?, 'EMERGENCY! Help needed at my location!', 'emergency', ?, ?)
            ");
            $stmt->execute([$conversationId, $userId, $lat, $long]);
            
            // Send bot emergency response
            $this->sendBotMessage($conversationId, 'emergency');
            
            // Immediately transfer to human
            $this->transferToHuman($conversationId);
            
            return ['type' => 'emergency', 'message' => 'Emergency alert sent! Help is on the way!'];
            
        } catch (PDOException $e) {
            error_log("Error handling emergency: " . $e->getMessage());
            return false;
        }
    }
    
    private function transferToHuman($conversationId) {
        try {
            // Update conversation type to human
            $stmt = $this->conn->prepare("
                UPDATE conversations 
                SET conversation_type = 'human' 
                WHERE id = ?
            ");
            $stmt->execute([$conversationId]);
            
            // Notify barangay officials
            $stmt = $this->conn->prepare("
                INSERT INTO messages (conversation_id, sender_id, message_text, message_type) 
                VALUES (?, 1, 'This conversation has been transferred to a barangay official. Please wait for assistance.', 'text')
            ");
            $stmt->execute([$conversationId]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error transferring to human: " . $e->getMessage());
            return false;
        }
    }
    
    public function getConversationMessages($conversationId, $limit = 50) {
        try {
            $stmt = $this->conn->prepare("
                SELECT m.*, u.full_name, u.user_type 
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
                LIMIT ?
            ");
            $stmt->execute([$conversationId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting messages: " . $e->getMessage());
            return [];
        }
    }
    
    public function sendUserMessage($conversationId, $userId, $message) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO messages (conversation_id, sender_id, message_text, message_type) 
                VALUES (?, ?, ?, 'text')
            ");
            $stmt->execute([$conversationId, $userId, $message]);
            return true;
        } catch (PDOException $e) {
            error_log("Error sending user message: " . $e->getMessage());
            return false;
        }
    }
    
    public function getActiveConversations($officialId = null) {
        try {
            $sql = "
                SELECT c.*, u.full_name as user_name, u.phone_number,
                       (SELECT message_text FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                       er.priority, er.latitude, er.longitude
                FROM conversations c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN emergency_reports er ON c.id = er.conversation_id
                WHERE c.status = 'active' AND c.conversation_type IN ('human', 'emergency')
                ORDER BY 
                    CASE 
                        WHEN c.conversation_type = 'emergency' THEN 1 
                        ELSE 2 
                    END,
                    c.updated_at DESC
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting active conversations: " . $e->getMessage());
            return [];
        }
    }
}
?>