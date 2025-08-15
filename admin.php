<?php
session_start();

// Simple error handling to prevent 500 errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Initialize variables with defaults to prevent undefined variable errors
$admin = array(
    'username' => $_SESSION['admin_username'] ?? 'Admin',
    'first_name' => $_SESSION['admin_first_name'] ?? '',
    'last_name' => $_SESSION['admin_last_name'] ?? '',
    'city' => $_SESSION['admin_city'] ?? 'Unknown',
    'barangay_number' => $_SESSION['admin_barangay'] ?? 'Unknown',
    'profile_picture' => null
);

// Only try to connect to database if config exists
if (file_exists('config.php')) {
    try {
        require_once 'config.php';
        
        // Get admin details from database if PDO connection exists
        if (isset($pdo)) {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $adminData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($adminData) {
                $admin = $adminData;
            }
        }
    } catch (Exception $e) {
        // Continue with default values if database connection fails
        error_log("Database error in admin chat: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat Panel - Barangay Help Desk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff914d 0%, #ff5e00 100%);
            min-height: 100vh;
            color: #2c3e50;
        }
        
        /* NAVBAR */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255, 145, 77, 0.1);
            color: #ff914d;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            color: white;
        }

        .chat-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* ADMIN INFO */
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .admin-avatar-initial {
            font-size: 16px;
            font-weight: 600;
        }

        .admin-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 145, 77, 0.4);
        }

        .admin-avatar::after {
            content: "✏️";
            position: absolute;
            bottom: -2px;
            right: -2px;
            font-size: 12px;
            background: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .admin-avatar:hover::after {
            opacity: 1;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* CHAT CONTAINER */
        .chat-container {
            max-width: 1400px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .conversations-section {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .conversation-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .conversation-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }
        
        .conversation-card:hover {
            background: #fff3e0;
            border-color: #ff914d;
        }
        
        .conversation-card.active {
            background: #fff3e0;
            border-color: #ff914d;
        }
        
        .conversation-card.emergency {
            border-left: 4px solid #f44336;
        }
        
        .conversation-info h4 {
            margin-bottom: 8px;
            color: #333;
        }
        
        .conversation-info p {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .chat-section {
            display: none;
            padding: 20px;
        }
        
        .chat-header-controls {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background: #fafafa;
        }
        
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
            max-width: 70%;
        }
        
        .user-message {
            background: #fff3e0;
            color: #ff5e00;
            margin-right: auto;
        }
        
        .admin-message {
            background: #ff914d;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        
        .system-message {
            background: #f0f0f0;
            color: #666;
            margin: 10px auto;
            text-align: center;
            font-style: italic;
        }
        
        .image-message {
            padding: 5px;
        }
        
        .message-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .image-caption {
            margin-top: 5px;
            font-size: 14px;
        }
        
        .location-info {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .location-info a {
            color: white;
            text-decoration: underline;
        }
        
        .message-time {
            font-size: 10px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .admin-input {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .input-area {
            flex: 1;
        }
        
        .admin-message-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
            min-height: 40px;
        }

        .admin-message-input:focus {
            outline: none;
            border-color: #ff914d;
            box-shadow: 0 0 0 3px rgba(255, 145, 77, 0.1);
        }
        
        .admin-send-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .admin-send-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 145, 77, 0.3);
        }
        
        .admin-send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .refresh-btn {
            padding: 8px 15px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .close-chat-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .close-chat-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* Image modal */
        .image-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .modal-image {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 8px;
        }
        
        .modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
            background: none;
            border: none;
        }
        
        .emergency-badge {
            background: #f44336;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 10px;
        }
        
        .no-conversations {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }

        .error-banner {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin: 20px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .admin-info {
                order: -1;
                width: 100%;
                justify-content: space-between;
            }

            .nav-links {
                order: 1;
                width: 100%;
                justify-content: center;
            }
            
            .conversation-list {
                grid-template-columns: 1fr;
            }

            .chat-container {
                margin: 10px;
                border-radius: 15px;
            }

            .chat-header-controls {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .admin-input {
                flex-direction: column;
                gap: 10px;
            }

            .admin-send-btn {
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation with orange theme -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🛡️ Barangay Help Desk - Admin</div>
            <div class="nav-links">
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                <a href="admin.php" class="nav-link active">💬 Chat</a>
                <a href="barangay_request.php" class="nav-link">📋 Requests</a>
                <a href="verifier.php" class="nav-link">✓ Verifier</a>
            </div>
            <div class="admin-info">
                <a href="admin_edit.php" class="admin-avatar" title="Edit Profile">
                    <?php if (!empty($admin['profile_picture']) && file_exists($admin['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <span class="admin-avatar-initial">
                            <?php 
                            $initial = 'A';
                            if (!empty($admin['first_name'])) {
                                $initial = strtoupper($admin['first_name'][0]);
                            } elseif (!empty($admin['username'])) {
                                $initial = strtoupper($admin['username'][0]);
                            }
                            echo $initial;
                            ?>
                        </span>
                    <?php endif; ?>
                </a>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin['username']); ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">
                        <?php echo htmlspecialchars($admin['city'] . ', Brgy ' . $admin['barangay_number']); ?>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?')">Logout</a>
            </div>
        </div>
    </nav>

    <?php if (!file_exists('config.php') || !file_exists('admin_handler.php')): ?>
    <div class="error-banner">
        ⚠️ <strong>Chat System Setup Required</strong><br>
        Missing required files: config.php or admin_handler.php. Please ensure all chat system files are properly uploaded.
    </div>
    <?php endif; ?>

    <!-- Image modal -->
    <div class="image-modal" id="imageModal" onclick="closeImageModal()">
        <button class="modal-close" onclick="closeImageModal()">&times;</button>
        <img class="modal-image" id="modalImage" src="" alt="Full size image">
    </div>

    <div class="chat-container">
        <div class="chat-header">
            <h1>🛠️ Admin Chat Panel</h1>
            <p>Manage user conversations and provide support for <?php echo htmlspecialchars($admin['city'] . ', Barangay ' . $admin['barangay_number']); ?></p>
        </div>
        
        <div class="conversations-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Active Conversations</h3>
                <button onclick="loadConversations()" class="refresh-btn">🔄 Refresh</button>
            </div>
            
            <div class="conversation-list" id="conversationsList">
                <div class="no-conversations">Loading conversations...</div>
            </div>
        </div>
        
        <div class="chat-section" id="chatSection">
            <div class="chat-header-controls">
                <div>
                    <h3 id="currentUserTitle">Chat with User</h3>
                    <small id="currentUserInfo">Select a conversation to start</small>
                </div>
                <button onclick="closeChat()" class="close-chat-btn">Close Chat</button>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <!-- Messages will appear here -->
            </div>
            
            <div class="admin-input">
                <div class="input-area">
                    <textarea id="adminMessageInput" class="admin-message-input" placeholder="Type your response..." onkeypress="handleKeyPress(event)"></textarea>
                </div>
                <button onclick="sendAdminMessage()" class="admin-send-btn" id="adminSendBtn">Send</button>
            </div>
        </div>
    </div>

    <script>
        let currentConversationId = null;
        let messageInterval = null;
        
        // Admin info from PHP
        const adminInfo = {
            id: <?php echo $_SESSION['admin_id']; ?>,
            city: '<?php echo addslashes($admin['city']); ?>',
            barangay: '<?php echo addslashes($admin['barangay_number']); ?>'
        };
        
        // Load conversations on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (file_exists('config.php') && file_exists('admin_handler.php')): ?>
            loadConversations();
            setInterval(loadConversations, 10000); // Refresh every 10 seconds
            <?php else: ?>
            document.getElementById('conversationsList').innerHTML = '<div class="no-conversations">⚠️ Chat system not properly configured. Please contact system administrator.</div>';
            <?php endif; ?>
        });
        
        function loadConversations() {
            <?php if (!file_exists('admin_handler.php')): ?>
            console.error('admin_handler.php not found');
            return;
            <?php endif; ?>

            fetch('admin_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_conversations&admin_id=' + adminInfo.id + '&city=' + encodeURIComponent(adminInfo.city) + '&barangay=' + encodeURIComponent(adminInfo.barangay)
            })
            .then(response => response.json())
            .then(data => {
                let conversationsList = document.getElementById('conversationsList');
                
                if (data.success && data.conversations && data.conversations.length > 0) {
                    conversationsList.innerHTML = '';
                    
                    data.conversations.forEach(conv => {
                        let convDiv = document.createElement('div');
                        convDiv.className = `conversation-card ${conv.help_type === 'emergency' ? 'emergency' : ''} ${conv.id === currentConversationId ? 'active' : ''}`;
                        convDiv.onclick = () => selectConversation(conv.id, conv.user_id, conv.help_type);
                        
                        let timeAgo = getTimeAgo(conv.last_activity || conv.created_at);
                        
                        convDiv.innerHTML = `
                            <div class="conversation-info">
                                <h4>${conv.user_id}${conv.help_type === 'emergency' ? '<span class="emergency-badge">EMERGENCY</span>' : ''}</h4>
                                <p><strong>${conv.help_type.charAt(0).toUpperCase() + conv.help_type.slice(1)} Help</strong></p>
                                <p>Last activity: ${timeAgo}</p>
                                <p>Status: ${conv.status}</p>
                                <p>Messages: ${conv.message_count || 0}</p>
                                ${conv.unread_count > 0 ? `<p style="color: #e74c3c; font-weight: bold;">🔴 ${conv.unread_count} unread</p>` : ''}
                            </div>
                        `;
                        
                        conversationsList.appendChild(convDiv);
                    });
                } else if (data.error) {
                    conversationsList.innerHTML = `<div class="no-conversations">Error: ${data.error}</div>`;
                } else {
                    conversationsList.innerHTML = '<div class="no-conversations">No active conversations found</div>';
                }
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
                document.getElementById('conversationsList').innerHTML = '<div class="no-conversations">Error loading conversations. Please check network connection.</div>';
            });
        }
        
        function selectConversation(conversationId, userId, helpType) {
            currentConversationId = conversationId;
            
            // Update UI
            document.getElementById('chatSection').style.display = 'block';
            document.getElementById('currentUserTitle').textContent = `Chat with ${userId}`;
            document.getElementById('currentUserInfo').textContent = `${helpType.charAt(0).toUpperCase() + helpType.slice(1)} Help Request`;
            
            // Update active conversation in list
            document.querySelectorAll('.conversation-card').forEach(card => {
                card.classList.remove('active');
            });
            event.target.closest('.conversation-card').classList.add('active');
            
            // Start loading messages
            loadMessages();
            if (messageInterval) clearInterval(messageInterval);
            messageInterval = setInterval(loadMessages, 5000);
            
            // Scroll to chat section
            document.getElementById('chatSection').scrollIntoView({ behavior: 'smooth' });
        }
        
        function loadMessages() {
            if (!currentConversationId) return;
            
            fetch('admin_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_messages&conversation_id=${currentConversationId}`
            })
            .then(response => response.json())
            .then(messages => {
                let chatMessages = document.getElementById('chatMessages');
                chatMessages.innerHTML = '';
                
                if (Array.isArray(messages)) {
                    messages.forEach(message => {
                        let messageDiv = document.createElement('div');
                        let messageClass = `message ${message.sender_type}-message`;
                        
                        messageDiv.className = messageClass;
                        
                        // Handle different message types
                        if (message.message_type === 'image' && message.file_path) {
                            messageDiv.classList.add('image-message');
                            messageDiv.innerHTML = `
                                <img class="message-image" 
                                     src="${message.file_path}" 
                                     alt="Shared image" 
                                     onclick="showImageModal('${message.file_path}')"
                                     onerror="this.style.display='none'; this.parentNode.innerHTML+='<p style=color:red;>Image not found</p>'">
                                ${message.message ? `<div class="image-caption">${message.message}</div>` : ''}
                            `;
                        } else {
                            messageDiv.innerHTML = message.message || 'No message content';
                        }
                        
                        // Add location info if available
                        if (message.location_lat && message.location_lng) {
                            let locationDiv = document.createElement('div');
                            locationDiv.className = 'location-info';
                            locationDiv.innerHTML = `📍 <a href="https://maps.google.com/?q=${message.location_lat},${message.location_lng}" target="_blank">View Location</a>`;
                            messageDiv.appendChild(locationDiv);
                        }
                        
                        // Add timestamp
                        let timestampDiv = document.createElement('div');
                        timestampDiv.className = 'message-time';
                        timestampDiv.textContent = new Date(message.created_at).toLocaleString();
                        messageDiv.appendChild(timestampDiv);
                        
                        chatMessages.appendChild(messageDiv);
                    });
                    
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                } else if (messages.error) {
                    chatMessages.innerHTML = `<div class="system-message">Error: ${messages.error}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                document.getElementById('chatMessages').innerHTML = '<div class="system-message">Error loading messages. Please try again.</div>';
            });
        }
        
        function sendAdminMessage() {
            let input = document.getElementById('adminMessageInput');
            let message = input.value.trim();
            let sendBtn = document.getElementById('adminSendBtn');
            
            if (!message || !currentConversationId) {
                return;
            }
            
            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';
            
            fetch('admin_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=send_admin_message&conversation_id=${currentConversationId}&message=${encodeURIComponent(message)}&admin_id=${adminInfo.id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    loadMessages();
                } else {
                    alert('Failed to send message: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Error sending message. Please check your connection.');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send';
            });
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendAdminMessage();
            }
        }
        
        function showImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        function closeChat() {
            currentConversationId = null;
            document.getElementById('chatSection').style.display = 'none';
            
            if (messageInterval) {
                clearInterval(messageInterval);
                messageInterval = null;
            }
            
            // Remove active class from conversations
            document.querySelectorAll('.conversation-card').forEach(card => {
                card.classList.remove('active');
            });
        }
        
        function getTimeAgo(dateString) {
            let now = new Date();
            let date = new Date(dateString);
            let diffMs = now - date;
            let diffMins = Math.floor(diffMs / 60000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
            return `${Math.floor(diffMins / 1440)}d ago`;
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('imageModal');
                if (modal.style.display === 'flex') {
                    closeImageModal();
                }
            }
        });

        // Auto-resize textarea
        document.addEventListener('DOMContentLoaded', function() {
            const messageInput = document.getElementById('adminMessageInput');
            if (messageInput) {
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });
            }
        });

        // Cleanup intervals when page unloads
        window.addEventListener('beforeunload', function() {
            if (messageInterval) {
                clearInterval(messageInterval);
            }
        });

        // Add notification sound for new messages (optional)
        function playNotificationSound() {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmQdBi2J2/LNeSsFJHfH8N2QQAoUXrTp66hVFA==');
                audio.volume = 0.3;
                audio.play().catch(e => console.log('Could not play notification sound'));
            } catch (e) {
                console.log('Notification sound not available');
            }
        }

        // Enhanced conversation loading with better error handling
        function loadConversationsWithRetry(retryCount = 0) {
            const maxRetries = 3;
            
            fetch('admin_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_conversations&admin_id=' + adminInfo.id + '&city=' + encodeURIComponent(adminInfo.city) + '&barangay=' + encodeURIComponent(adminInfo.barangay)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                let conversationsList = document.getElementById('conversationsList');
                
                if (data.success && data.conversations && data.conversations.length > 0) {
                    conversationsList.innerHTML = '';
                    
                    data.conversations.forEach(conv => {
                        let convDiv = document.createElement('div');
                        convDiv.className = `conversation-card ${conv.help_type === 'emergency' ? 'emergency' : ''} ${conv.id === currentConversationId ? 'active' : ''}`;
                        convDiv.onclick = () => selectConversation(conv.id, conv.user_id, conv.help_type);
                        
                        let timeAgo = getTimeAgo(conv.last_activity || conv.created_at);
                        
                        convDiv.innerHTML = `
                            <div class="conversation-info">
                                <h4>${conv.user_id}${conv.help_type === 'emergency' ? '<span class="emergency-badge">EMERGENCY</span>' : ''}</h4>
                                <p><strong>${conv.help_type.charAt(0).toUpperCase() + conv.help_type.slice(1)} Help</strong></p>
                                <p>Last activity: ${timeAgo}</p>
                                <p>Status: ${conv.status}</p>
                                <p>Messages: ${conv.message_count || 0}</p>
                                ${conv.unread_count > 0 ? `<p style="color: #e74c3c; font-weight: bold;">🔴 ${conv.unread_count} unread</p>` : ''}
                            </div>
                        `;
                        
                        conversationsList.appendChild(convDiv);
                    });
                } else if (data.error) {
                    conversationsList.innerHTML = `<div class="no-conversations">Error: ${data.error}</div>`;
                } else {
                    conversationsList.innerHTML = '<div class="no-conversations">No active conversations found</div>';
                }
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
                
                if (retryCount < maxRetries) {
                    console.log(`Retrying conversation load... (${retryCount + 1}/${maxRetries})`);
                    setTimeout(() => {
                        loadConversationsWithRetry(retryCount + 1);
                    }, 2000);
                } else {
                    document.getElementById('conversationsList').innerHTML = '<div class="no-conversations">Error loading conversations. Please refresh the page or check your connection.</div>';
                }
            });
        }

        // Enhanced message loading with better error handling
        function loadMessagesWithRetry(retryCount = 0) {
            if (!currentConversationId) return;
            
            const maxRetries = 2;
            
            fetch('admin_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_messages&conversation_id=${currentConversationId}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(messages => {
                let chatMessages = document.getElementById('chatMessages');
                const previousScrollTop = chatMessages.scrollTop;
                const wasScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 1;
                
                chatMessages.innerHTML = '';
                
                if (Array.isArray(messages)) {
                    messages.forEach(message => {
                        let messageDiv = document.createElement('div');
                        let messageClass = `message ${message.sender_type}-message`;
                        
                        messageDiv.className = messageClass;
                        
                        // Handle different message types
                        if (message.message_type === 'image' && message.file_path) {
                            messageDiv.classList.add('image-message');
                            messageDiv.innerHTML = `
                                <img class="message-image" 
                                     src="${message.file_path}" 
                                     alt="Shared image" 
                                     onclick="showImageModal('${message.file_path}')"
                                     onerror="this.style.display='none'; this.parentNode.innerHTML+='<p style=color:red;>Image not found</p>'">
                                ${message.message ? `<div class="image-caption">${message.message}</div>` : ''}
                            `;
                        } else {
                            messageDiv.innerHTML = message.message || 'No message content';
                        }
                        
                        // Add location info if available
                        if (message.location_lat && message.location_lng) {
                            let locationDiv = document.createElement('div');
                            locationDiv.className = 'location-info';
                            locationDiv.innerHTML = `📍 <a href="https://maps.google.com/?q=${message.location_lat},${message.location_lng}" target="_blank">View Location</a>`;
                            messageDiv.appendChild(locationDiv);
                        }
                        
                        // Add timestamp
                        let timestampDiv = document.createElement('div');
                        timestampDiv.className = 'message-time';
                        timestampDiv.textContent = new Date(message.created_at).toLocaleString();
                        messageDiv.appendChild(timestampDiv);
                        
                        chatMessages.appendChild(messageDiv);
                    });
                    
                    // Smart scrolling - only auto-scroll if user was at bottom
                    if (wasScrolledToBottom) {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                } else if (messages.error) {
                    chatMessages.innerHTML = `<div class="system-message">Error: ${messages.error}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                
                if (retryCount < maxRetries) {
                    setTimeout(() => {
                        loadMessagesWithRetry(retryCount + 1);
                    }, 1000);
                } else {
                    document.getElementById('chatMessages').innerHTML = '<div class="system-message">Error loading messages. Please try again.</div>';
                }
            });
        }

        // Global function aliases for backward compatibility
        window.loadConversations = loadConversationsWithRetry;
        window.loadMessages = loadMessagesWithRetry;

        console.log('✅ Admin Chat Panel Fully Loaded - Orange Theme with Profile Picture Support');
    </script>
</body>
</html>