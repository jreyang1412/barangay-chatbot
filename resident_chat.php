<?php
// resident_chat.php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/Chatbot.php';

checkLogin();

if ($_SESSION['user_type'] !== 'resident') {
    header("Location: official_dashboard.php");
    exit();
}

$chatbot = new Chatbot();
$conversationId = $chatbot->startConversation($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Services Chat</title>
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
            flex-direction: column;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }
        
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
        
        .message.bot {
            align-items: flex-start;
        }
        
        .message.user {
            align-items: flex-end;
        }
        
        .message.official {
            align-items: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 1rem 1.2rem;
            border-radius: 20px;
            margin-bottom: 0.3rem;
            word-wrap: break-word;
        }
        
        .message.bot .message-bubble {
            background: #e4e6ea;
            color: #333;
            border-bottom-left-radius: 5px;
        }
        
        .message.user .message-bubble {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .message.official .message-bubble {
            background: #28a745;
            color: white;
            border-bottom-left-radius: 5px;
        }
        
        .message-info {
            font-size: 0.8rem;
            color: #65676b;
            margin: 0 1rem;
        }
        
        .bot-options {
            margin-top: 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .bot-option {
            background: white;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 0.8rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-weight: 500;
        }
        
        .bot-option:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
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
        
        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .emergency-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.8rem 1.2rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 0.5rem;
            transition: all 0.3s;
        }
        
        .emergency-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .typing-indicator {
            padding: 1rem;
            font-style: italic;
            color: #65676b;
        }
        
        .location-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1rem;
            border-radius: 10px;
            margin: 0.5rem 0;
            color: #856404;
        }
        
        .emergency-alert {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin: 0.5rem 0;
            font-weight: bold;
            text-align: center;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }
            
            .header h1 {
                font-size: 1.1rem;
            }
            
            .message-bubble {
                max-width: 85%;
            }
            
            .input-container {
                padding: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏢 Barangay Services</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="chat-container">
        <div class="messages-container" id="messagesContainer">
            <!-- Messages will be loaded here -->
        </div>
        
        <div class="input-container">
            <textarea class="message-input" id="messageInput" placeholder="Type your message..." rows="1"></textarea>
            <button class="send-btn" id="sendBtn" onclick="sendMessage()">Send</button>
            <button class="emergency-btn" onclick="sendEmergency()">🚨 Emergency</button>
        </div>
    </div>

    <script>
        let conversationId = <?php echo $conversationId; ?>;
        let currentUser = {
            id: <?php echo $_SESSION['user_id']; ?>,
            name: '<?php echo addslashes($_SESSION['full_name']); ?>',
            type: '<?php echo $_SESSION['user_type']; ?>'
        };
        let userLocation = null;
        
        // Get user location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLocation = {
                        lat: position.coords.latitude,
                        long: position.coords.longitude
                    };
                },
                (error) => {
                    console.log('Location access denied or unavailable');
                }
            );
        }
        
        // Auto-resize textarea
        document.getElementById('messageInput').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Send message on Enter (but allow Shift+Enter for new line)
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            // Add message to UI immediately
            addMessageToUI('user', message, currentUser.name);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // Send to server
            fetch('ajax/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: conversationId,
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
        
        function sendEmergency() {
            if (!userLocation) {
                alert('Location access is required for emergency alerts. Please enable location services and refresh the page.');
                return;
            }
            
            if (confirm('This will send an emergency alert to barangay officials with your location. Continue?')) {
                // Show emergency alert in UI
                const alertDiv = document.createElement('div');
                alertDiv.className = 'emergency-alert';
                alertDiv.textContent = '🚨 EMERGENCY ALERT SENT! Help is on the way!';
                document.getElementById('messagesContainer').appendChild(alertDiv);
                scrollToBottom();
                
                // Send emergency to server
                fetch('ajax/handle_bot_choice.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        option_id: 'emergency',
                        latitude: userLocation.lat,
                        longitude: userLocation.long
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadMessages(); // Reload messages to show emergency response
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }
        
        function handleBotChoice(optionId) {
            // Send choice to server
            fetch('ajax/handle_bot_choice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    option_id: optionId,
                    latitude: userLocation?.lat,
                    longitude: userLocation?.long
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadMessages(); // Reload messages to show bot response and new options
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function addMessageToUI(type, message, senderName, timestamp = null) {
            const messagesContainer = document.getElementById('messagesContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const messageBubble = document.createElement('div');
            messageBubble.className = 'message-bubble';
            messageBubble.textContent = message;
            
            const messageInfo = document.createElement('div');
            messageInfo.className = 'message-info';
            messageInfo.textContent = `${senderName} • ${timestamp || 'Just now'}`;
            
            messageDiv.appendChild(messageBubble);
            messageDiv.appendChild(messageInfo);
            messagesContainer.appendChild(messageDiv);
            
            scrollToBottom();
        }
        
        function addBotOptionsToUI(options) {
            const messagesContainer = document.getElementById('messagesContainer');
            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'bot-options';
            
            options.forEach(option => {
                const optionBtn = document.createElement('button');
                optionBtn.className = 'bot-option';
                optionBtn.textContent = option.option_text;
                optionBtn.onclick = () => handleBotChoice(option.id);
                optionsDiv.appendChild(optionBtn);
            });
            
            messagesContainer.appendChild(optionsDiv);
            scrollToBottom();
        }
        
        function loadMessages() {
            fetch(`ajax/get_messages.php?conversation_id=${conversationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.messages, data.bot_options);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        function displayMessages(messages, botOptions) {
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.innerHTML = '';
            
            messages.forEach(message => {
                let type = 'user';
                if (message.user_type === 'barangay_official') {
                    type = message.sender_id == 1 ? 'bot' : 'official';
                }
                
                const timestamp = new Date(message.created_at).toLocaleTimeString();
                addMessageToUI(type, message.message_text, message.full_name, timestamp);
                
                // Show location for emergency messages
                if (message.message_type === 'emergency' && message.latitude && message.longitude) {
                    const locationDiv = document.createElement('div');
                    locationDiv.className = 'location-message';
                    locationDiv.innerHTML = `📍 Emergency Location: <a href="https://maps.google.com/?q=${message.latitude},${message.longitude}" target="_blank">View on Map</a>`;
                    messagesContainer.appendChild(locationDiv);
                }
            });
            
            // Show bot options if available
            if (botOptions && botOptions.length > 0) {
                addBotOptionsToUI(botOptions);
            }
            
            scrollToBottom();
        }
        
        function scrollToBottom() {
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Initial load and polling for new messages
        loadMessages();
        setInterval(loadMessages, 3000); // Check for new messages every 3 seconds
    </script>
</body>
</html>