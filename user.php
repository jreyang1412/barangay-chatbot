<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Help Chat - User</title>
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
    }

    /* Chathead bubble */
    #chathead {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border-radius: 50%;
      font-size: 30px;
      text-align: center;
      line-height: 60px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      z-index: 9999;
      cursor: move;
      user-select: none;
    }

    /* Chat popup */
    .chat-popup.hidden {
      display: none;
    }

    .chat-popup {
      position: fixed;
      z-index: 9998;
      animation: popupSlideUp 0.3s ease-in-out;
    }

    @keyframes popupSlideUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .chat-container {
      background: white;
      border-radius: 15px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      width: 400px;
      max-width: 90vw;
      overflow: hidden;
    }

    .chat-header {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      padding: 20px;
      text-align: center;
    }

    .help-options {
      padding: 20px;
      text-align: center;
    }

    .help-option {
      display: block;
      width: 100%;
      padding: 15px;
      margin: 10px 0;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .help-option:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .chat-messages {
      height: 400px;
      overflow-y: auto;
      padding: 20px;
      border-bottom: 1px solid #eee;
    }

    .message {
      margin: 10px 0;
      padding: 12px;
      border-radius: 8px;
      max-width: 80%;
    }

    .user-message {
      background: #667eea;
      color: white;
      margin-left: auto;
      text-align: right;
    }

    .admin-message {
      background: #f1f3f4;
      color: #333;
    }

    .location-info {
      background: #4CAF50;
      color: white;
      font-size: 12px;
      padding: 8px;
      border-radius: 4px;
      margin-top: 5px;
    }

    .chat-input {
      padding: 20px;
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

    .status {
      text-align: center;
      padding: 10px;
      background: #f8f9fa;
      color: #666;
      font-size: 12px;
    }
  </style>
</head>
<body>

  <!-- Chathead button -->
  <div id="chathead" onclick="toggleChat()">💬</div>

  <!-- Chat popup box -->
  <div class="chat-popup hidden" id="chatPopup">
    <div class="chat-container">
      <div class="chat-header">
        <h2>Help Center</h2>
        <p>How can we assist you today?</p>
      </div>

      <div id="helpOptions" class="help-options">
        <button class="help-option" onclick="selectHelpType('emergency')">🚨 Emergency Help</button>
        <button class="help-option" onclick="selectHelpType('technical')">🔧 Technical Support</button>
        <button class="help-option" onclick="selectHelpType('general')">💬 General Inquiry</button>
      </div>

      <div id="chatSection" class="hidden">
        <div class="status" id="statusMessage">
          Connecting you with support...
        </div>

        <div class="chat-messages" id="chatMessages">
          <!-- Messages will appear here -->
        </div>

        <div class="chat-input">
          <input type="text" id="messageInput" class="message-input" placeholder="Type your message..." onkeypress="handleKeyPress(event)">
          <button onclick="sendMessage()" class="send-btn">Send</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let userId = localStorage.getItem('userId') || 'user_' + Date.now();
    localStorage.setItem('userId', userId);

    let selectedHelpType = '';
    let locationShared = false;

    function toggleChat() {
  const popup = document.getElementById('chatPopup');
  popup.classList.toggle('hidden');

  if (!popup.classList.contains('hidden')) {
    const chathead = document.getElementById('chathead');
    const rect = chathead.getBoundingClientRect();

    // Position popup slightly above and centered to chathead
    const popupWidth = 400; // width of .chat-container
    const offsetX = rect.left + rect.width / 2 - popupWidth / 2;
    const offsetY = rect.top - 10 - popup.offsetHeight;

    popup.style.left = `${offsetX}px`;
    popup.style.top = `${rect.top - 450}px`; // adjust height accordingly
  }
}


    function selectHelpType(type) {
      selectedHelpType = type;
      document.getElementById('helpOptions').classList.add('hidden');
      document.getElementById('chatSection').classList.remove('hidden');

      let statusMessage = '';
      switch(type) {
        case 'emergency':
          statusMessage = '🚨 Emergency support requested. Getting your location...';
          getLocation();
          break;
        case 'technical':
          statusMessage = '🔧 Technical support requested. An admin will assist you shortly.';
          break;
        case 'general':
          statusMessage = '💬 General inquiry started. How can we help you?';
          break;
      }

      document.getElementById('statusMessage').textContent = statusMessage;
      sendHelpRequest();
      setInterval(loadMessages, 2000);
      loadMessages();
    }

    function getLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          let lat = position.coords.latitude;
          let lng = position.coords.longitude;

          fetch('user_handler.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=send_location&user_id=${userId}&lat=${lat}&lng=${lng}&help_type=${selectedHelpType}`
          });

          locationShared = true;
          document.getElementById('statusMessage').textContent = '📍 Emergency support with location shared. Admin notified.';
        }, function() {
          document.getElementById('statusMessage').textContent = '⚠️ Location access denied. Emergency support still active.';
        });
      }
    }

    function sendHelpRequest() {
      fetch('user_handler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=start_chat&user_id=${userId}&help_type=${selectedHelpType}`
      });
    }

    function sendMessage() {
      let input = document.getElementById('messageInput');
      let message = input.value.trim();

      if (message) {
        fetch('user_handler.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `action=send_message&user_id=${userId}&message=${encodeURIComponent(message)}`
        }).then(() => {
          input.value = '';
          loadMessages();
        });
      }
    }

    function handleKeyPress(event) {
      if (event.key === 'Enter') {
        sendMessage();
      }
    }

    function loadMessages() {
      fetch(`user_handler.php?action=get_messages&user_id=${userId}`)
        .then(response => response.json())
        .then(messages => {
          let chatMessages = document.getElementById('chatMessages');
          chatMessages.innerHTML = '';

          messages.forEach(message => {
            let messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.sender_type}-message`;
            messageDiv.textContent = message.message;

            if (message.location_lat && message.location_lng) {
              let locationDiv = document.createElement('div');
              locationDiv.className = 'location-info';
              locationDiv.textContent = `📍 Location: ${message.location_lat}, ${message.location_lng}`;
              messageDiv.appendChild(locationDiv);
            }

            chatMessages.appendChild(messageDiv);
          });

          chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }

    // Chathead dragging logic
    const chathead = document.getElementById('chathead');
    let offsetX, offsetY, isDragging = false;

    chathead.addEventListener('mousedown', (e) => {
      isDragging = true;
      offsetX = e.clientX - chathead.offsetLeft;
      offsetY = e.clientY - chathead.offsetTop;
    });

    document.addEventListener('mousemove', (e) => {
      if (isDragging) {
        chathead.style.left = `${e.clientX - offsetX}px`;
        chathead.style.top = `${e.clientY - offsetY}px`;
        chathead.style.bottom = 'auto';
        chathead.style.right = 'auto';
      }
    });

    document.addEventListener('mouseup', () => {
      isDragging = false;
    });
  </script>
</body>
</html>
