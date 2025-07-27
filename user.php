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
      cursor: pointer;
      user-select: none;
    }

    /* Chat popup overlay */
    .chat-popup.hidden {
      display: none;
    }

    .chat-popup {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9998;
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .chat-container {
      background: white;
      border-radius: 15px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
      width: 450px;
      max-width: 90vw;
      max-height: 90vh;
      overflow: hidden;
      animation: popupSlideUp 0.3s ease-in-out;
    }

    @keyframes popupSlideUp {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    /* Responsive adjustments */
    @media (max-width: 500px) {
      .chat-container {
        width: 95vw;
        max-height: 85vh;
      }
      
      .chat-messages {
        height: 350px !important;
      }
    }

    .chat-header {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      padding: 25px;
      text-align: center;
      position: relative;
    }

    .chat-header h2 {
      font-size: 24px;
      margin-bottom: 5px;
    }

    .chat-header p {
      opacity: 0.9;
      font-size: 16px;
    }

    /* Close button */
    .close-btn {
      position: absolute;
      top: 15px;
      right: 20px;
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background-color 0.2s ease;
    }

    .close-btn:hover {
      background-color: rgba(255, 255, 255, 0.2);
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
      align-items: center;
      position: relative;
    }

    .settings-icon {
      width: 35px;
      height: 35px;
      background: #f0f0f0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 18px;
      transition: all 0.2s ease;
      position: relative;
    }

    .settings-icon:hover {
      background: #e0e0e0;
      transform: scale(1.05);
    }

    .settings-dropdown {
      position: absolute;
      bottom: 50px;
      left: 0;
      background: white;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      padding: 8px;
      z-index: 10000;
      opacity: 0;
      transform: translateY(10px);
      transition: all 0.3s ease;
      pointer-events: none;
      min-width: 200px;
    }

    .settings-dropdown.show {
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto;
    }

    .dropdown-option {
      display: flex;
      align-items: center;
      padding: 10px 12px;
      margin: 2px 0;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color 0.2s ease;
      font-size: 14px;
      color: #333;
    }

    .dropdown-option:hover {
      background-color: #f5f5f5;
    }

    .dropdown-option .emoji {
      margin-right: 8px;
      font-size: 16px;
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
  <div class="chat-popup hidden" id="chatPopup" onclick="closeOnOverlay(event)">
    <div class="chat-container">
      <div class="chat-header">
        <button class="close-btn" onclick="toggleChat()">&times;</button>
        <h2>Help Center</h2>
        <p>How can we assist you today?</p>
      </div>

      <div id="chatSection">
        <div class="status" id="statusMessage">
          💬 Chat started. How can we help you?
        </div>

        <div class="chat-messages" id="chatMessages">
          <!-- Messages will appear here -->
        </div>

        <div class="chat-input">
          <div class="settings-icon" onclick="toggleSettingsDropdown()" id="settingsIcon">
            ⚙️
            <div class="settings-dropdown" id="settingsDropdown">
              <div class="dropdown-option" onclick="selectHelpTypeFromDropdown('emergency')">
                <span class="emoji">🚨</span>
                <span>Emergency Help</span>
              </div>
              <div class="dropdown-option" onclick="selectHelpTypeFromDropdown('technical')">
                <span class="emoji">🔧</span>
                <span>Technical Support</span>
              </div>
              <div class="dropdown-option" onclick="selectHelpTypeFromDropdown('general')">
                <span class="emoji">💬</span>
                <span>General Inquiry</span>
              </div>
            </div>
          </div>
          <input type="text" id="messageInput" class="message-input" placeholder="Type your message..." onkeypress="handleKeyPress(event)">
          <button onclick="sendMessage()" class="send-btn">Send</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let userId = 'user_' + Date.now();
    let selectedHelpType = '';
    let locationShared = false;
    let messageLoadInterval = null;
    let firstMessage = true;
    let autoResponseShown = false;

    function toggleSettingsDropdown() {
      const dropdown = document.getElementById('settingsDropdown');
      dropdown.classList.toggle('show');
      
      // Close dropdown when clicking outside
      if (dropdown.classList.contains('show')) {
        setTimeout(() => {
          document.addEventListener('click', closeDropdownOutside);
        }, 100);
      }
    }

    function closeDropdownOutside(event) {
      const dropdown = document.getElementById('settingsDropdown');
      const settingsIcon = document.getElementById('settingsIcon');
      
      if (!settingsIcon.contains(event.target)) {
        dropdown.classList.remove('show');
        document.removeEventListener('click', closeDropdownOutside);
      }
    }

    function selectHelpTypeFromDropdown(type) {
      selectHelpType(type);
      const dropdown = document.getElementById('settingsDropdown');
      dropdown.classList.remove('show');
      document.removeEventListener('click', closeDropdownOutside);
    }

    function toggleChat() {
      const popup = document.getElementById('chatPopup');
      popup.classList.toggle('hidden');

      if (!popup.classList.contains('hidden')) {
        // Start message loading interval
        if (messageLoadInterval) clearInterval(messageLoadInterval);
        messageLoadInterval = setInterval(loadMessages, 2000);
        loadMessages();
      } else {
        // Clear interval when chat is closed
        if (messageLoadInterval) {
          clearInterval(messageLoadInterval);
          messageLoadInterval = null;
        }
      }
    }

    // Close popup when clicking on overlay (outside the chat container)
    function closeOnOverlay(event) {
      if (event.target === event.currentTarget) {
        toggleChat();
      }
    }

    function selectHelpType(type) {
      selectedHelpType = type;

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
      
      // Send help request to backend
      sendHelpRequest();
      
      // Add a message indicating the help type was selected
      addMessageToChat(`You selected: ${type.charAt(0).toUpperCase() + type.slice(1)} Help`, 'admin');
      
      // Open chat if not already open
      const popup = document.getElementById('chatPopup');
      if (popup.classList.contains('hidden')) {
        toggleChat();
      }
    }

    function getLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          let lat = position.coords.latitude;
          let lng = position.coords.longitude;

          console.log(`Location shared: ${lat}, ${lng} for help type: ${selectedHelpType}`);

          // Send location to backend
          fetch('user_handler.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=send_location&user_id=${userId}&lat=${lat}&lng=${lng}&help_type=${selectedHelpType}`
          }).then(response => response.json())
          .then(data => {
            if (data.success) {
              locationShared = true;
              document.getElementById('statusMessage').textContent = '📍 Emergency support with location shared. Admin notified.';
              addMessageToChat('📍 Location shared with emergency support', 'admin');
            }
          }).catch(error => {
            console.error('Error sending location:', error);
          });

        }, function() {
          document.getElementById('statusMessage').textContent = '⚠️ Location access denied. Emergency support still active.';
        });
      }
    }

    function sendHelpRequest() {
      if (!selectedHelpType) return;

      fetch('user_handler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=start_chat&user_id=${userId}&help_type=${selectedHelpType}`
      }).then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Help request sent successfully');
          // Start loading messages
          loadMessages();
        } else {
          console.error('Failed to send help request:', data.error);
        }
      }).catch(error => {
        console.error('Error sending help request:', error);
      });
    }

    function sendMessage() {
      let input = document.getElementById('messageInput');
      let message = input.value.trim();

      if (message) {
        // Add message to chat immediately
        addMessageToChat(message, 'user');
        input.value = '';

        // Add automatic response only for the first message
        if (firstMessage) {
          setTimeout(() => {
            addMessageToChat("Thank you for your message. An admin will be with you shortly.", 'admin');
            autoResponseShown = true;
            firstMessage = false;
          }, 1000);
        }

        // Send message to backend
        fetch('user_handler.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `action=send_message&user_id=${userId}&message=${encodeURIComponent(message)}`
        }).then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Message sent successfully');
            // Load messages to get any admin responses
            setTimeout(loadMessages, 500);
          } else {
            console.error('Failed to send message:', data.error);
          }
        }).catch(error => {
          console.error('Error sending message:', error);
        });
      }
    }

    function addMessageToChat(message, senderType) {
      let chatMessages = document.getElementById('chatMessages');
      let messageDiv = document.createElement('div');
      messageDiv.className = `message ${senderType}-message`;
      messageDiv.textContent = message;
      chatMessages.appendChild(messageDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function loadMessages() {
      if (!userId) return;

      fetch(`user_handler.php?action=get_messages&user_id=${userId}`)
        .then(response => response.json())
        .then(messages => {
          if (Array.isArray(messages)) {
            let chatMessages = document.getElementById('chatMessages');
            
            // Store the auto-response if it exists
            let autoResponseExists = autoResponseShown;
            
            chatMessages.innerHTML = '';

            // Add all messages from database
            messages.forEach(message => {
              let messageDiv = document.createElement('div');
              messageDiv.className = `message ${message.sender_type}-message`;
              messageDiv.textContent = message.message;

              // Add location info if available
              if (message.location_lat && message.location_lng) {
                let locationDiv = document.createElement('div');
                locationDiv.className = 'location-info';
                locationDiv.textContent = `📍 Location: ${message.location_lat}, ${message.location_lng}`;
                messageDiv.appendChild(locationDiv);
              }

              chatMessages.appendChild(messageDiv);
            });

            // Always preserve the auto-response after the first user message
            if (autoResponseExists) {
              // Find where to insert the auto-response (after first user message)
              let firstUserMessageIndex = -1;
              let messageElements = chatMessages.children;
              
              for (let i = 0; i < messageElements.length; i++) {
                if (messageElements[i].classList.contains('user-message')) {
                  firstUserMessageIndex = i;
                  break;
                }
              }
              
              // Create auto-response element
              let autoResponseDiv = document.createElement('div');
              autoResponseDiv.className = 'message admin-message';
              autoResponseDiv.textContent = "Thank you for your message. An admin will be with you shortly.";
              
              // Insert after first user message
              if (firstUserMessageIndex >= 0 && firstUserMessageIndex + 1 < messageElements.length) {
                chatMessages.insertBefore(autoResponseDiv, messageElements[firstUserMessageIndex + 1]);
              } else {
                chatMessages.appendChild(autoResponseDiv);
              }
            }

            chatMessages.scrollTop = chatMessages.scrollHeight;
          }
        }).catch(error => {
          console.error('Error loading messages:', error);
        });
    }

    function handleKeyPress(event) {
      if (event.key === 'Enter') {
        sendMessage();
      }
    }

    // Prevent dragging since popup is now centered
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        const popup = document.getElementById('chatPopup');
        if (!popup.classList.contains('hidden')) {
          toggleChat();
        }
      }
    });
  </script>
</body>
</html>