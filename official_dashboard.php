<?php
// official_dashboard.php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/Chatbot.php';

checkLogin();

if ($_SESSION['user_type'] !== 'barangay_official') {
    header("Location: resident_chat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Official Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            display: flex;
        }
        
        .sidebar {
            width: 350px;
            background: white;
            border-right: 1px solid #e4e6ea;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .sidebar-header h1 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-header .user-info {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }
        
        .conversation-item:hover {
            background: #f8f9fa;
        }
        
        .conversation-item.active {
            background: #e3f2fd;
            border-right: 3px solid #667eea;
        }
        
        .conversation-item.emergency {
            background: #ffebee;
            border-left: 4px solid #f44336;
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .conversation-name {
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        
        .conversation-time {
            font-size: 0.8rem;
            color: #65676b;
        }
        
        .conversation-preview {
            color: #65676b;
            font-size: 0.85rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .conversation-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .emergency-indicator {
            background: #ff4444;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
            margin-left: 0.5rem;
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }
        
        .chat-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e4e6ea;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header h2 {
            color: #333;
            font-size: 1.1rem;
        }
        
        .chat-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-resolve {
            background: #28a745;
            color: white;
        }
        
        .btn-resolve:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        
        .btn-close {
            background: #dc3545;
            color: white;
        }
        
        .btn-close:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: #fafafa;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }
        
        .message.resident {
            align-items: flex-end;
        }
        
        .message.official {
            align-items: flex-start;
        }
        
        .message.bot {
            align-items: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 1rem 1.2rem;
            border-radius: 20px;
            margin-bottom: 0.3rem;
            word-wrap: break-word;
        }
        
        .message.resident .message-bubble {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .message.official .message-bubble {
            background: #28a745;
            color: white;
            border-bottom-left-radius: 5px;
        }
        
        .message.bot .message-bubble {
            background: #e4e6ea;
            color: #333;
            border-bottom-left-radius: 5px;
        }
        
        .message-info {
            font-size: 0.8rem;
            color: #65676b;
            margin: 0 1rem;
        }
        
        .input-container {
            padding: 1rem;
            background: white;
            border-top: 1px solid #e4e6ea;
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }
        
        .message-input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 2px solid #e4e6ea;
            border-radius: 25px;
            resize: none;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            max-height: 100px;
        }
        
        .message-input:focus {
            border-color: #667eea;
        }
        
        .send-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.8rem 1.2rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .send-btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #65676b;
            text-align: center;
        }
        
        .empty-state h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .location-link {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1rem;
            border-radius: 10px;
            margin: 0.5rem 0;
            color: #856404;
        }
        
        .location-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 1rem;
            display: inline-block;
            text-align: center;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: 40vh;
            }
            
            .chat-area {
                height: 60vh;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏢 Barangay Dashboard</h1>
            <div class="user-info">
                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="conversations-list" id="conversationsList">
            <!-- Conversations will be loaded here -->
        </div>
    </div>
    
    <div class="chat-area">
        <div class="chat-header" id="chatHeader" style="display: none;">
            <h2 id="chatTitle">Select a conversation</h2>
            <div class="chat-actions">
                <button class="btn btn-resolve" onclick="resolveConversation()">✓ Resolve</button>
                <button class="btn btn-close" onclick="closeConversation()">✗ Close</button>
            </div>
        </div>
        
        <div class="messages-container" id="messagesContainer">
            <div class="empty-state">
                <h3>Welcome to Barangay Dashboard</h3>
                <p>Select a conversation from the left to start helping residents</p>
            </div>
        </div>
        
        <div class="input-container" id="inputContainer" style="display: none;">
            <textarea class="message-input" id="messageInput" placeholder="Type your response..." rows="1"></textarea>
            <button class="send-btn" id="sendBtn" onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        let currentConversationId = null;
        let currentUser = {
            id: <?php echo $_SESSION['user_id']; ?>,
            name: '<?php echo addslashes($_SESSION['full_name']); ?>',
            type: '<?php echo $_SESSION['user_type']; ?>'
        };
        
        // Auto-resize textarea
        document.getElementById('messageInput').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Send message on Enter
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        function loadConversations() {
            fetch('ajax/get_conversations.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayConversations(data.conversations);
                    }
                })
                .catch(error => {
                    console.error('Error loading conversations:', error);
                });
        }
        
        function displayConversations(conversations) {
            const conversationsList = document.getElementById('conversationsList');
            
            if (conversations.length === 0) {
                conversationsList.innerHTML = `
                    <div style="padding: 2rem; text-align: center; color: #65676b;">
                        <h3>No Active Conversations</h3>
                        <p>When residents start conversations, they will appear here.</p>
                    </div>
                `;
                return;
            }
            
            conversationsList.innerHTML = '';
            
            conversations.forEach(conversation => {
                const conversationDiv = document.createElement('div');
                conversationDiv.className = `conversation-item ${conversation.conversation_type === 'emergency' ? 'emergency' : ''}`;
                conversationDiv.onclick = () => selectConversation(conversation.id, conversation.user_name);
                
                const timeAgo = getTimeAgo(conversation.last_message_time);
                const isEmergency = conversation.conversation_type === 'emergency';
                
                conversationDiv.innerHTML = `
                    <div class="conversation-header">
                        <div class="conversation-name">
                            ${conversation.user_name}
                            ${isEmergency ? '<span class="emergency-indicator">🚨 EMERGENCY</span>' : ''}
                        </div>
                        <div class="conversation-time">${timeAgo}</div>
                    </div>
                    <div class="conversation-preview">
                        ${conversation.last_message || 'No messages yet'}
                    </div>
                    ${isEmergency && conversation.latitude && conversation.longitude ? `
                        <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #dc3545;">
                            📍 <a href="https://maps.google.com/?q=${conversation.latitude},${conversation.longitude}" target="_blank" style="color: #dc3545;">View Location</a>
                        </div>
                    ` : ''}
                `;
                
                conversationsList.appendChild(conversationDiv);
            });
        }
        
        function selectConversation(conversationId, userName) {
            currentConversationId = conversationId;
            
            // Update UI
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Show chat header and input
            document.getElementById('chatHeader').style.display = 'flex';
            document.getElementById('inputContainer').style.display = 'flex';
            document.getElementById('chatTitle').textContent = userName;
            
            // Load messages
            loadMessages(conversationId);
        }
        
        function loadMessages(conversationId) {
            fetch(`ajax/get_messages.php?conversation_id=${conversationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.messages);
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                });
        }
        
        function displayMessages(messages) {
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.innerHTML = '';
            
            if (messages.length === 0) {
                messagesContainer.innerHTML = `
                    <div class="empty-state">
                        <h3>No messages yet</h3>
                        <p>Start the conversation by sending a message</p>
                    </div>
                `;
                return;
            }
            
            messages.forEach(message => {
                let type = 'resident';
                if (message.user_type === 'barangay_official') {
                    type = message.sender_id == 1 ? 'bot' : 'official';
                }
                
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${type}`;
                
                const messageBubble = document.createElement('div');
                messageBubble.className = 'message-bubble';
                messageBubble.textContent = message.message_text;
                
                const messageInfo = document.createElement('div');
                messageInfo.className = 'message-info';
                const timestamp = new Date(message.created_at).toLocaleString();
                messageInfo.textContent = `${message.full_name} • ${timestamp}`;
                
                messageDiv.appendChild(messageBubble);
                messageDiv.appendChild(messageInfo);
                
                // Add location info for emergency messages
                if (message.message_type === 'emergency' && message.latitude && message.longitude) {
                    const locationDiv = document.createElement('div');
                    locationDiv.className = 'location-link';
                    locationDiv.innerHTML = `📍 Emergency Location: <a href="https://maps.google.com/?q=${message.latitude},${message.longitude}" target="_blank">View on Google Maps</a>`;
                    messageDiv.appendChild(locationDiv);
                }
                
                messagesContainer.appendChild(messageDiv);
            });
            
            scrollToBottom();
        }
        
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message || !currentConversationId) return;
            
            // Add message to UI immediately
            addMessageToUI('official', message, currentUser.name);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // Send to server
            fetch('ajax/send_official_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: currentConversationId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function addMessageToUI(type, message, senderName) {
            const messagesContainer = document.getElementById('messagesContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const messageBubble = document.createElement('div');
            messageBubble.className = 'message-bubble';
            messageBubble.textContent = message;
            
            const messageInfo = document.createElement('div');
            messageInfo.className = 'message-info';
            messageInfo.textContent = `${senderName} • Just now`;
            
            messageDiv.appendChild(messageBubble);
            messageDiv.appendChild(messageInfo);
            messagesContainer.appendChild(messageDiv);
            
            scrollToBottom();
        }
        
        function resolveConversation() {
            if (!currentConversationId) return;
            
            if (confirm('Mark this conversation as resolved?')) {
                updateConversationStatus('resolved');
            }
        }
        
        function closeConversation() {
            if (!currentConversationId) return;
            
            if (confirm('Close this conversation?')) {
                updateConversationStatus('closed');
            }
        }
        
        function updateConversationStatus(status) {
            fetch('ajax/update_conversation_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: currentConversationId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh conversations list
                    loadConversations();
                    
                    // Clear current chat
                    currentConversationId = null;
                    document.getElementById('chatHeader').style.display = 'none';
                    document.getElementById('inputContainer').style.display = 'none';
                    document.getElementById('messagesContainer').innerHTML = `
                        <div class="empty-state">
                            <h3>Conversation ${status}</h3>
                            <p>Select another conversation to continue helping residents</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function scrollToBottom() {
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function getTimeAgo(dateString) {
            if (!dateString) return '';
            
            const now = new Date();
            const messageTime = new Date(dateString);
            const diffInMinutes = Math.floor((now - messageTime) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'Just now';
            if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
            
            const diffInHours = Math.floor(diffInMinutes / 60);
            if (diffInHours < 24) return `${diffInHours}h ago`;
            
            const diffInDays = Math.floor(diffInHours / 24);
            return `${diffInDays}d ago`;
        }
        
        // Initial load and periodic refresh
        loadConversations();
        
        // Refresh conversations every 5 seconds
        setInterval(loadConversations, 5000);
        
        // Refresh current conversation messages every 3 seconds
        setInterval(() => {
            if (currentConversationId) {
                loadMessages(currentConversationId);
            }
        }, 3000);
    </script>
</body>
</html>