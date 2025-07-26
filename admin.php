<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            height: 100vh;
        }
        
        .sidebar {
            width: 300px;
            background: white;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s;
            position: relative;
        }
        
        .conversation-item:hover {
            background: #f8f9fa;
        }
        
        .conversation-item.active {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
        }
        
        .conversation-item.emergency {
            border-left: 4px solid #f44336;
            background: #ffebee;
        }
        
        .conversation-item.emergency:before {
            content: '🚨';
            position: absolute;
            right: 15px;
            top: 15px;
        }
        
        .conversation-info h4 {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .conversation-info p {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .conversation-status {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
        }
        
        .status-waiting {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-closed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }
        
        .chat-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .no-chat {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 18px;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .message {
            margin: 10px 0;
            padding: 12px;
            border-radius: 8px;
            max-width: 80%;
        }
        
        .user-message {
            background: #e3f2fd;
            color: #1976d2;
            margin-right: auto;
        }
        
        .admin-message {
            background: #667eea;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        
        .emergency-message {
            background: #ffcdd2;
            border-left: 4px solid #f44336;
            font-weight: bold;
        }
        
        .location-info {
            background: #4CAF50;
            color: white;
            font-size: 12px;
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
        
        .location-info a {
            color: white;
            text-decoration: underline;
        }
        
        .message-timestamp {
            font-size: 10px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
        }
        
        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
        }
        
        .send-btn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .stats {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .refresh-btn {
            padding: 8px 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>Help Desk Management</p>
            </div>
            
            <div class="conversations-list" id="conversationsList">
                <!-- Conversations will be loaded here -->
            </div>
            
            <div class="stats">
                <button onclick="loadConversations()" class="refresh-btn">Refresh</button>
                <div id="statsInfo" style="margin-top: 10px;">
                    Loading...
                </div>
            </div>
        </div>
        
        <div class="chat-area">
            <div id="noChatSelected" class="no-chat">
                <div style="text-align: center;">
                    <h3>👋 Welcome Admin!</h3>
                    <p>Select a conversation from the left to start helping users</p>
                </div>
            </div>
            
            <div id="chatSection" style="display: none; flex: 1; display: none; flex-direction: column;">
                <div class="chat-header">
                    <div>
                        <h3 id="currentUserTitle">Chat with User</h3>
                        <small id="currentUserInfo"></small>
                    </div>
                    <button onclick="closeChat()" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Close</button>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will appear here -->
                </div>
                
                <div class="chat-input">
                    <input type="text" id="messageInput" class="message-input" placeholder="Type your response..." onkeypress="handleKeyPress(event)">
                    <button onclick="sendAdminMessage()" class="send-btn">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let messageInterval = null;
        
        // Load conversations on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadConversations();
            setInterval(loadConversations, 5000); // Refresh every 5 seconds
        });
        
        function loadConversations() {
            fetch('admin_handler.php?action=get_conversations')
                .then(response => response.json())
                .then(data => {
                    let conversationsList = document.getElementById('conversationsList');
                    let statsInfo = document.getElementById('statsInfo');
                    
                    conversationsList.innerHTML = '';
                    
                    if (data.conversations && data.conversations.length > 0) {
                        data.conversations.forEach(conv => {
                            let convDiv = document.createElement('div');
                            convDiv.className = `conversation-item ${conv.help_type === 'emergency' ? 'emergency' : ''} ${conv.user_id === currentUserId ? 'active' : ''}`;
                            convDiv.onclick = () => selectConversation(conv.user_id, conv.help_type);
                            
                            let statusClass = `status-${conv.status}`;
                            let timeAgo = getTimeAgo(conv.last_activity);
                            
                            convDiv.innerHTML = `
                                <div class="conversation-info">
                                    <h4>${conv.user_id}</h4>
                                    <p><strong>${conv.help_type.charAt(0).toUpperCase() + conv.help_type.slice(1)} Help</strong></p>
                                    <p>Last message: ${conv.last_message || 'No messages'}</p>
                                    <p>Active: ${timeAgo}</p>
                                    <span class="conversation-status ${statusClass}">${conv.status}</span>
                                </div>
                            `;
                            
                            conversationsList.appendChild(convDiv);
                        });
                    } else {
                        conversationsList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">No active conversations</div>';
                    }
                    
                    // Update stats
                    statsInfo.innerHTML = `
                        Active: ${data.stats.active}<br>
                        Waiting: ${data.stats.waiting}<br>
                        Total: ${data.stats.total}
                    `;
                });
        }
        
        function selectConversation(userId, helpType) {
            currentUserId = userId;
            
            // Update UI
            document.getElementById('noChatSelected').style.display = 'none';
            document.getElementById('chatSection').style.display = 'flex';
            document.getElementById('currentUserTitle').textContent = `Chat with ${userId}`;
            document.getElementById('currentUserInfo').textContent = `${helpType.charAt(0).toUpperCase() + helpType.slice(1)} Help Request`;
            
            // Update active conversation in sidebar
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.closest('.conversation-item').classList.add('active');
            
            // Start loading messages
            loadMessages();
            if (messageInterval) clearInterval(messageInterval);
            messageInterval = setInterval(loadMessages, 2000);
        }
        
        function loadMessages() {
            if (!currentUserId) return;
            
            fetch(`admin_handler.php?action=get_messages&user_id=${currentUserId}`)
                .then(response => response.json())
                .then(messages => {
                    let chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    
                    messages.forEach(message => {
                        let messageDiv = document.createElement('div');
                        let messageClass = `message ${message.sender_type}-message`;
                        
                        if (message.help_type === 'emergency' && message.sender_type === 'user') {
                            messageClass += ' emergency-message';
                        }
                        
                        messageDiv.className = messageClass;
                        messageDiv.innerHTML = message.message;
                        
                        if (message.location_lat && message.location_lng) {
                            let locationDiv = document.createElement('div');
                            locationDiv.className = 'location-info';
                            locationDiv.innerHTML = `📍 Location: <a href="https://maps.google.com/?q=${message.location_lat},${message.location_lng}" target="_blank">${message.location_lat}, ${message.location_lng}</a>`;
                            messageDiv.appendChild(locationDiv);
                        }
                        
                        let timestampDiv = document.createElement('div');
                        timestampDiv.className = 'message-timestamp';
                        timestampDiv.textContent = new Date(message.created_at).toLocaleString();
                        messageDiv.appendChild(timestampDiv);
                        
                        chatMessages.appendChild(messageDiv);
                    });
                    
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }
        
        function sendAdminMessage() {
            let input = document.getElementById('messageInput');
            let message = input.value.trim();
            
            if (message && currentUserId) {
                fetch('admin_handler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=send_message&user_id=${currentUserId}&message=${encodeURIComponent(message)}`
                }).then(() => {
                    input.value = '';
                    loadMessages();
                    loadConversations(); // Refresh sidebar
                });
            }
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendAdminMessage();
            }
        }
        
        function closeChat() {
            currentUserId = null;
            document.getElementById('noChatSelected').style.display = 'flex';
            document.getElementById('chatSection').style.display = 'none';
            
            if (messageInterval) {
                clearInterval(messageInterval);
                messageInterval = null;
            }
            
            // Remove active class from sidebar
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
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
    </script>
</body>
</html>