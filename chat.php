<?php
// Simple chat system compatible with InfinityFree
session_start();

// Database configuration for InfinityFree
$host = 'sql308.infinityfree.com';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';
$database = 'if0_38484017_barangay_chatbot';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create table if not exists
$createTable = "
CREATE TABLE IF NOT EXISTS simple_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_type VARCHAR(10) NOT NULL,
    sender_name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

try {
    $pdo->exec($createTable);
} catch(PDOException $e) {
    // Table might already exist
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send') {
        $senderType = $_POST['sender_type'] ?? 'user';
        $senderName = $_POST['sender_name'] ?? 'Anonymous';
        $message = $_POST['message'] ?? '';
        
        if (!empty($message)) {
            // Clean the message
            $message = htmlspecialchars(trim($message), ENT_QUOTES, 'UTF-8');
            $senderName = htmlspecialchars(trim($senderName), ENT_QUOTES, 'UTF-8');
            
            try {
                $stmt = $pdo->prepare("INSERT INTO simple_messages (sender_type, sender_name, message) VALUES (?, ?, ?)");
                $stmt->execute([$senderType, $senderName, $message]);
                
                // Redirect to prevent resubmission
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch(PDOException $e) {
                $error = "Error sending message: " . $e->getMessage();
            }
        }
    } elseif ($action === 'clear') {
        // Clear old messages (keep last 100)
        try {
            $pdo->exec("DELETE FROM simple_messages WHERE id NOT IN (SELECT id FROM (SELECT id FROM simple_messages ORDER BY id DESC LIMIT 100) AS temp)");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch(PDOException $e) {
            $error = "Error clearing messages: " . $e->getMessage();
        }
    }
}

// Get messages
try {
    $stmt = $pdo->query("SELECT * FROM simple_messages ORDER BY timestamp DESC LIMIT 50");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $messages = array_reverse($messages); // Show oldest first
} catch(PDOException $e) {
    $messages = [];
    $error = "Error loading messages: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Chat System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .chat-modes {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .mode-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 14px;
            color: #666;
            transition: all 0.2s;
        }
        
        .mode-tab:hover {
            background: #e9ecef;
        }
        
        .mode-tab.active {
            background: white;
            color: #667eea;
            font-weight: 600;
            border-bottom: 3px solid #667eea;
        }
        
        .chat-container {
            height: 500px;
            display: flex;
            flex-direction: column;
        }
        
        .messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message.admin {
            justify-content: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }
        
        .message.user .message-bubble {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.admin .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .message-info {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .sender-name {
            font-weight: 600;
            font-size: 12px;
            margin-bottom: 5px;
            opacity: 0.8;
        }
        
        .input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #dee2e6;
        }
        
        .input-form {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .name-input {
            width: 150px;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .name-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .message-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 20px;
            font-size: 14px;
            resize: none;
        }
        
        .message-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .send-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .send-btn:hover {
            transform: scale(1.05);
        }
        
        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #666;
        }
        
        .refresh-btn, .clear-btn {
            padding: 5px 10px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
        }
        
        .refresh-btn:hover, .clear-btn:hover {
            background: #5a6268;
        }
        
        .clear-btn {
            background: #dc3545;
        }
        
        .clear-btn:hover {
            background: #c82333;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            border: 1px solid #f5c6cb;
        }
        
        .no-messages {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }
        
        .timestamp {
            font-size: 10px;
            opacity: 0.6;
            margin-top: 3px;
        }
        
        .online-count {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .input-form {
                flex-direction: column;
            }
            
            .name-input {
                width: 100%;
            }
            
            .controls {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Barangay Chat System</h1>
            <p>Connect with your local barangay officials</p>
        </div>
        
        <div class="chat-modes">
            <button class="mode-tab active" onclick="switchMode('user')">👤 User Chat</button>
            <button class="mode-tab" onclick="switchMode('admin')">👨‍💼 Admin Panel</button>
        </div>
        
        <div class="chat-container">
            <div class="messages" id="messages">
                <?php if (isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if (empty($messages)): ?>
                    <div class="no-messages">
                        No messages yet. Start the conversation! 👋
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?= $msg['sender_type'] ?>">
                            <div class="message-bubble">
                                <?php if ($msg['sender_type'] === 'admin'): ?>
                                    <div class="sender-name">👨‍💼 <?= $msg['sender_name'] ?></div>
                                <?php else: ?>
                                    <div class="sender-name">👤 <?= $msg['sender_name'] ?></div>
                                <?php endif; ?>
                                <?= nl2br($msg['message']) ?>
                                <div class="timestamp">
                                    <?= date('M j, g:i A', strtotime($msg['timestamp'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="input-area">
                <form method="POST" class="input-form" onsubmit="return validateForm()">
                    <input type="hidden" name="action" value="send">
                    <input type="text" 
                           name="sender_name" 
                           class="name-input" 
                           placeholder="Your name..." 
                           maxlength="50"
                           value="<?= $_POST['sender_name'] ?? '' ?>"
                           required>
                    <textarea name="message" 
                              class="message-input" 
                              placeholder="Type your message here..." 
                              maxlength="500"
                              rows="1"
                              required
                              onkeypress="handleKeyPress(event)"></textarea>
                    <button type="submit" class="send-btn">Send</button>
                    <input type="hidden" name="sender_type" value="user" id="senderType">
                </form>
                
                <div class="controls">
                    <div>
                        Messages: <?= count($messages) ?> 
                        <span class="online-count">Live</span>
                    </div>
                    <div>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="refresh-btn">🔄 Refresh</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Clear all messages?')">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="clear-btn">🗑️ Clear</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentMode = 'user';
        
        // Auto-refresh page every 10 seconds
        let autoRefresh = setInterval(() => {
            window.location.reload();
        }, 10000);
        
        function switchMode(mode) {
            currentMode = mode;
            document.getElementById('senderType').value = mode;
            
            // Update UI
            document.querySelectorAll('.mode-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update placeholder based on mode
            const nameInput = document.querySelector('.name-input');
            const messageInput = document.querySelector('.message-input');
            
            if (mode === 'admin') {
                nameInput.placeholder = 'Admin name...';
                messageInput.placeholder = 'Type admin response...';
            } else {
                nameInput.placeholder = 'Your name...';
                messageInput.placeholder = 'Type your message here...';
            }
        }
        
        function validateForm() {
            const name = document.querySelector('.name-input').value.trim();
            const message = document.querySelector('.message-input').value.trim();
            
            if (!name) {
                alert('Please enter your name');
                return false;
            }
            
            if (!message) {
                alert('Please enter a message');
                return false;
            }
            
            // Clear auto-refresh during submission
            clearInterval(autoRefresh);
            
            return true;
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                event.target.closest('form').submit();
            }
        }
        
        // Auto-scroll to bottom
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.getElementById('messages');
            messages.scrollTop = messages.scrollHeight;
        });
        
        // Focus on message input
        document.querySelector('.message-input').focus();
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>