<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Help Desk</title>
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
            color: #2c3e50;
        }
        
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
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .sos-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }
        
        .sos-btn:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
        }
        
        .sos-btn.active {
            background: linear-gradient(135deg, #27ae60, #229954);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f39c12;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .logout-btn {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        /* SOS Chat Panel Styles */
        .sos-chat-panel {
            display: none;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #ddd;
            height: 400px;
        }
        
        .sos-chat-panel.active {
            display: flex;
        }
        
        .chat-sidebar {
            width: 300px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sidebar-title {
            font-size: 1rem;
            font-weight: 600;
        }
        
        .minimize-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .minimize-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 12px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s;
            position: relative;
        }
        
        .conversation-item:hover {
            background: #e9ecef;
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
            right: 12px;
            top: 12px;
        }
        
        .conversation-info h4 {
            margin-bottom: 4px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .conversation-info p {
            font-size: 11px;
            color: #666;
            margin-bottom: 2px;
        }
        
        .conversation-status {
            font-size: 10px;
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
            padding: 15px;
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
            font-size: 14px;
            text-align: center;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f8f9fa;
        }
        
        .message {
            margin: 8px 0;
            padding: 10px;
            border-radius: 8px;
            max-width: 80%;
            font-size: 13px;
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
            font-size: 11px;
            padding: 6px;
            border-radius: 4px;
            margin-top: 5px;
        }
        
        .location-info a {
            color: white;
            text-decoration: underline;
        }
        
        .message-timestamp {
            font-size: 9px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .chat-input {
            padding: 15px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 8px;
        }
        
        .message-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 13px;
        }
        
        .message-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .send-btn {
            padding: 10px 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
        }
        
        .stats {
            padding: 12px;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .refresh-btn {
            padding: 6px 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            margin-bottom: 8px;
        }
        
        /* Main Dashboard Styles */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .dashboard-title {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .overview-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .sos-chat-panel {
                height: 300px;
            }
            
            .chat-sidebar {
                width: 250px;
            }
            
            .nav-container {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .admin-info {
                order: -1;
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🏛️ Barangay Help Desk - Admin</div>
            <div class="admin-info">
                <div class="admin-avatar">A</div>
                <div>
                    <div style="font-weight: 600;">Admin User</div>
                    <div style="font-size: 12px; color: #6c757d;">
                        Barangay 28, Caloocan City
                    </div>
                </div>
                <button class="sos-btn" id="sosToggle" onclick="toggleSOSChat()">
                    🚨 SOS Chat
                    <span class="notification-badge" id="sosNotification" style="display: none;">5</span>
                </button>
                <a href="barangay_request.php" class="logout-btn" style="background: #667eea; margin-right: 10px;">
                    📋 View Requests
                </a>
                <a href="verifier.php" class="logout-btn" style="background: #28a745; margin-right: 10px;">
                    ✓ User Verifier
                </a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <!-- SOS Chat Panel -->
    <div class="sos-chat-panel" id="sosChatPanel">
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">🚨 Emergency Chat</div>
                <button class="minimize-btn" onclick="toggleSOSChat()">−</button>
            </div>
            
            <div class="conversations-list" id="conversationsList">
                <!-- Sample conversations for demo -->
                <div class="conversation-item emergency active" onclick="selectConversation('user001', 'emergency')">
                    <div class="conversation-info">
                        <h4>User001</h4>
                        <p><strong>Emergency Help</strong></p>
                        <p>Last message: Need immediate help!</p>
                        <p>Active: 2m ago</p>
                        <span class="conversation-status status-waiting">waiting</span>
                    </div>
                </div>
                
                <div class="conversation-item" onclick="selectConversation('user002', 'general')">
                    <div class="conversation-info">
                        <h4>User002</h4>
                        <p><strong>General Help</strong></p>
                        <p>Last message: Question about services</p>
                        <p>Active: 15m ago</p>
                        <span class="conversation-status status-active">active</span>
                    </div>
                </div>
                
                <div class="conversation-item emergency" onclick="selectConversation('user003', 'emergency')">
                    <div class="conversation-info">
                        <h4>User003</h4>
                        <p><strong>Emergency Help</strong></p>
                        <p>Last message: Medical emergency</p>
                        <p>Active: 1h ago</p>
                        <span class="conversation-status status-closed">closed</span>
                    </div>
                </div>
            </div>
            
            <div class="stats">
                <button onclick="loadConversations()" class="refresh-btn">Refresh</button>
                <div id="statsInfo">
                    Active: 2<br>
                    Waiting: 1<br>
                    Total: 3
                </div>
            </div>
        </div>
        
        <div class="chat-area">
            <div id="noChatSelected" class="no-chat">
                <div>
                    <h3>👋 Welcome Admin!</h3>
                    <p>Select a conversation from the left to start helping users</p>
                </div>
            </div>
            
            <div id="chatSection" style="display: none; flex: 1; flex-direction: column;">
                <div class="chat-header">
                    <div>
                        <h3 id="currentUserTitle">Chat with User</h3>
                        <small id="currentUserInfo"></small>
                    </div>
                    <button onclick="closeChat()" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Close</button>
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

    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <p>Overview of barangay service requests and support activities</p>
        </div>

        <div class="overview-container">
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                📊 Overview
            </h3>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">25</div>
                    <div class="stat-label">Total Barangay Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Pending Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">5</div>
                    <div class="stat-label">Processing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">15</div>
                    <div class="stat-label">Support Tickets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Today's Requests</div>
                </div>
            </div>

            <h3 style="margin-bottom: 15px;">📈 Service Distribution</h3>
            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f8f9fa;">
                    <span>📋 Barangay Clearance</span>
                    <span style="font-weight: 600;">12</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f8f9fa;">
                    <span>🏠 Certificate of Residency</span>
                    <span style="font-weight: 600;">8</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f8f9fa;">
                    <span>🏢 Business Clearance</span>
                    <span style="font-weight: 600;">5</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let messageInterval = null;
        let sosActivated = false;
        
        // SOS Chat Functions
        function toggleSOSChat() {
            const panel = document.getElementById('sosChatPanel');
            const btn = document.getElementById('sosToggle');
            const notification = document.getElementById('sosNotification');
            
            sosActivated = !sosActivated;
            
            if (sosActivated) {
                panel.classList.add('active');
                btn.classList.add('active');
                btn.innerHTML = '✅ SOS Active <span class="notification-badge" style="display: none;">5</span>';
                notification.style.display = 'none';
                // Start polling for messages
                loadConversations();
                setInterval(loadConversations, 5000);
            } else {
                panel.classList.remove('active');
                btn.classList.remove('active');
                btn.innerHTML = '🚨 SOS Chat <span class="notification-badge" id="sosNotification" style="display: none;">5</span>';
                // Stop polling
                if (messageInterval) {
                    clearInterval(messageInterval);
                    messageInterval = null;
                }
            }
        }
        
        function loadConversations() {
            // Simulated function - in real app, this would fetch from server
            console.log('Loading conversations...');
            // Update notification badge if there are new messages
            const notification = document.getElementById('sosNotification');
            if (!sosActivated && Math.random() > 0.7) {
                notification.style.display = 'flex';
                notification.textContent = Math.floor(Math.random() * 10) + 1;
            }
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
            
            // Load sample messages
            loadSampleMessages(helpType);
            
            // Start message polling
            if (messageInterval) clearInterval(messageInterval);
            messageInterval = setInterval(() => loadSampleMessages(helpType), 2000);
        }
        
        function loadSampleMessages(helpType) {
            const chatMessages = document.getElementById('chatMessages');
            
            if (helpType === 'emergency') {
                chatMessages.innerHTML = `
                    <div class="message user-message emergency-message">
                        🚨 EMERGENCY: I need immediate help! There's a medical emergency at my location.
                        <div class="location-info">
                            📍 Location: <a href="https://maps.google.com/?q=14.6760,120.9626" target="_blank">14.6760, 120.9626</a>
                        </div>
                        <div class="message-timestamp">2 minutes ago</div>
                    </div>
                    <div class="message admin-message">
                        Emergency services have been notified. Medical team is being dispatched to your location. Stay calm and provide more details if possible.
                        <div class="message-timestamp">1 minute ago</div>
                    </div>
                    <div class="message user-message">
                        Thank you! Patient is conscious but needs immediate attention.
                        <div class="message-timestamp">30 seconds ago</div>
                    </div>
                `;
            } else {
                chatMessages.innerHTML = `
                    <div class="message user-message">
                        Hi, I have a question about barangay clearance requirements.
                        <div class="message-timestamp">15 minutes ago</div>
                    </div>
                    <div class="message admin-message">
                        Hello! I'd be happy to help you with barangay clearance requirements. What specific information do you need?
                        <div class="message-timestamp">14 minutes ago</div>
                    </div>
                    <div class="message user-message">
                        What documents do I need to bring and what's the processing time?
                        <div class="message-timestamp">13 minutes ago</div>
                    </div>
                `;
            }
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function sendAdminMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message && currentUserId) {
                const chatMessages = document.getElementById('chatMessages');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message admin-message';
                messageDiv.innerHTML = `
                    ${message}
                    <div class="message-timestamp">Just now</div>
                `;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                input.value = '';
                
                // Simulate user response after 2 seconds
                setTimeout(() => {
                    const userResponse = document.createElement('div');
                    userResponse.className = 'message user-message';
                    userResponse.innerHTML = `
                        Thank you for the quick response!
                        <div class="message-timestamp">Just now</div>
                    `;
                    chatMessages.appendChild(userResponse);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 2000);
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
        
        // Initialize notifications
        document.addEventListener('DOMContentLoaded', function() {
            // Show notification badge initially
            setTimeout(() => {
                const notification = document.getElementById('sosNotification');
                notification.style.display = 'flex';
                notification.textContent = '5';
            }, 3000);
            
            // Simulate periodic new messages
            setInterval(() => {
                if (!sosActivated) {
                    const notification = document.getElementById('sosNotification');
                    const currentCount = parseInt(notification.textContent) || 0;
                    if (Math.random() > 0.8) {
                        notification.style.display = 'flex';
                        notification.textContent = currentCount + 1;
                    }
                }
            }, 30000);
        });
        
        // Close SOS panel when clicking outside
        document.addEventListener('click', function(event) {
            const sosPanel = document.getElementById('sosChatPanel');
            const sosBtn = document.getElementById('sosToggle');
            
            if (sosActivated && !sosPanel.contains(event.target) && !sosBtn.contains(event.target)) {
                // Don't auto-close for better UX, admin needs to manually close
            }
        });
    </script>
</body>
</html>