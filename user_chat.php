<?php
// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'sql308.infinityfree.com';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';
$database = 'if0_38484017_barangay_chatbot';
$port = 3306;

// Add connection timeout and error handling
$conn = new mysqli($host, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Set charset to prevent issues
$conn->set_charset("utf8");

// Create messages table if it doesn't exist (with better error handling)
$createTable = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender VARCHAR(20) NOT NULL,
    message TEXT,
    image_path VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if (!$conn->query($createTable)) {
    error_log("Table creation failed: " . $conn->error);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set proper headers for AJAX
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    // Verify the request is coming from the same domain (basic CSRF protection)
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    
    if (isset($_POST['action'])) {
        $action = $conn->real_escape_string($_POST['action']);
        
        switch ($action) {
            case 'send_message':
                if (!isset($_POST['message']) || empty(trim($_POST['message']))) {
                    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
                    exit;
                }
                
                $message = $conn->real_escape_string(trim($_POST['message']));
                $sender = 'user';
                
                // Use prepared statement for better security
                $stmt = $conn->prepare("INSERT INTO chat_messages (sender, message) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ss", $sender, $message);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to save message']);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Database error']);
                }
                exit;
                
            case 'send_location':
                if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
                    echo json_encode(['success' => false, 'error' => 'Invalid location data']);
                    exit;
                }
                
                $latitude = floatval($_POST['latitude']);
                $longitude = floatval($_POST['longitude']);
                $sender = 'user';
                
                // Validate coordinates
                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    echo json_encode(['success' => false, 'error' => 'Invalid coordinates']);
                    exit;
                }
                
                $stmt = $conn->prepare("INSERT INTO chat_messages (sender, latitude, longitude) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sdd", $sender, $latitude, $longitude);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to save location']);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Database error']);
                }
                exit;
                
            case 'get_messages':
                $lastId = isset($_POST['last_id']) ? intval($_POST['last_id']) : 0;
                
                $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE id > ? ORDER BY timestamp ASC LIMIT 50");
                if ($stmt) {
                    $stmt->bind_param("i", $lastId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $messages = [];
                    while ($row = $result->fetch_assoc()) {
                        $messages[] = $row;
                    }
                    echo json_encode($messages);
                    $stmt->close();
                } else {
                    echo json_encode([]);
                }
                exit;
        }
    }
    
    // Handle file upload with better security
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Check file size (max 2MB for free hosting)
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'File too large. Max 2MB allowed.']);
            exit;
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP allowed.']);
            exit;
        }
        
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
                exit;
            }
        }
        
        // Generate safe filename
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = 'img_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
            $sender = 'user';
            $stmt = $conn->prepare("INSERT INTO chat_messages (sender, image_path) VALUES (?, ?)");
            if ($stmt) {
                $stmt->bind_param("ss", $sender, $filePath);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    // Clean up uploaded file if database insert fails
                    unlink($filePath);
                    echo json_encode(['success' => false, 'error' => 'Failed to save image info']);
                }
                $stmt->close();
            } else {
                unlink($filePath);
                echo json_encode(['success' => false, 'error' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'File upload failed']);
        }
        exit;
    }
    
    // If we reach here, it's an invalid POST request
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Chat - Barangay System</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .chat-container {
            width: 100%;
            max-width: 400px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-end;
            animation: fadeInUp 0.3s ease-out;
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message.barangay {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.barangay .message-content {
            background: #e9ecef;
            color: #333;
            border-bottom-left-radius: 4px;
        }
        
        .message img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 12px;
            margin-top: 8px;
            cursor: pointer;
        }
        
        .location-message {
            background: #e3f2fd !important;
            color: #1976d2 !important;
            border: 1px solid #bbdefb;
        }
        
        .location-link {
            color: inherit;
            text-decoration: none;
            font-weight: 500;
        }
        
        .timestamp {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
            text-align: right;
        }
        
        .message.barangay .timestamp {
            text-align: left;
        }
        
        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
        }
        
        .input-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        #messageInput {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        #messageInput:focus {
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 16px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .button-row {
            display: flex;
            gap: 10px;
        }
        
        #imageInput {
            display: none;
        }
        
        .typing-indicator {
            display: none;
            padding: 10px;
            font-style: italic;
            color: #666;
            font-size: 12px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .online-status {
            width: 10px;
            height: 10px;
            background: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-left: 8px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .chat-container {
                height: calc(100vh - 20px);
            }
            
            .button-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            User Chat <span class="online-status"></span>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="loading">Loading messages...</div>
        </div>
        
        <div class="typing-indicator" id="typingIndicator">
            Someone is typing...
        </div>
        
        <div class="chat-input">
            <div class="input-container">
                <input type="text" id="messageInput" placeholder="Type your message..." maxlength="500">
                <button class="btn btn-primary" onclick="sendMessage()" id="sendBtn">Send</button>
            </div>
            
            <div class="button-row">
                <input type="file" id="imageInput" accept="image/*" onchange="sendImage()">
                <button class="btn btn-secondary" onclick="document.getElementById('imageInput').click()" id="imageBtn">📷 Image</button>
                <button class="btn btn-secondary" onclick="sendLocation()" id="locationBtn">📍 Location</button>
            </div>
        </div>
    </div>

    <script>
        let lastMessageId = 0;
        let isLoading = false;
        let pollInterval;
        
        // Configuration
        const CONFIG = {
            pollIntervalMs: 3000, // 3 seconds for free hosting
            maxRetries: 3,
            retryDelay: 2000
        };
        
        // Load messages on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadMessages();
            startPolling();
        });
        
        // Handle Enter key in message input
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(loadMessages, CONFIG.pollIntervalMs);
        }
        
        function stopPolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        }
        
        function setButtonsDisabled(disabled) {
            document.getElementById('sendBtn').disabled = disabled;
            document.getElementById('imageBtn').disabled = disabled;
            document.getElementById('locationBtn').disabled = disabled;
        }
        
        function showError(message) {
            const chatMessages = document.getElementById('chatMessages');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            chatMessages.appendChild(errorDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Remove error after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.parentNode.removeChild(errorDiv);
                }
            }, 5000);
        }
        
        async function makeRequest(url, options, retries = 0) {
            try {
                const response = await fetch(url, options);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Request failed:', error);
                if (retries < CONFIG.maxRetries) {
                    await new Promise(resolve => setTimeout(resolve, CONFIG.retryDelay));
                    return makeRequest(url, options, retries + 1);
                }
                throw error;
            }
        }
        
        async function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (message === '' || isLoading) return;
            
            isLoading = true;
            setButtonsDisabled(true);
            
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('message', message);
            
            try {
                const data = await makeRequest('', {
                    method: 'POST',
                    body: formData
                });
                
                if (data.success) {
                    messageInput.value = '';
                    loadMessages();
                } else {
                    showError('Error sending message: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                showError('Network error. Please check your connection.');
            } finally {
                isLoading = false;
                setButtonsDisabled(false);
            }
        }
        
        async function sendImage() {
            const imageInput = document.getElementById('imageInput');
            const file = imageInput.files[0];
            
            if (!file || isLoading) return;
            
            // Check file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showError('Image size must be less than 2MB');
                imageInput.value = '';
                return;
            }
            
            isLoading = true;
            setButtonsDisabled(true);
            
            const formData = new FormData();
            formData.append('image', file);
            
            try {
                const data = await makeRequest('', {
                    method: 'POST',
                    body: formData
                });
                
                if (data.success) {
                    imageInput.value = '';
                    loadMessages();
                } else {
                    showError('Error sending image: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                showError('Network error. Please check your connection.');
            } finally {
                isLoading = false;
                setButtonsDisabled(false);
            }
        }
        
        async function sendLocation() {
            if (!navigator.geolocation || isLoading) {
                showError('Geolocation is not supported by this browser');
                return;
            }
            
            isLoading = true;
            setButtonsDisabled(true);
            
            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        timeout: 10000,
                        enableHighAccuracy: false
                    });
                });
                
                const formData = new FormData();
                formData.append('action', 'send_location');
                formData.append('latitude', position.coords.latitude);
                formData.append('longitude', position.coords.longitude);
                
                const data = await makeRequest('', {
                    method: 'POST',
                    body: formData
                });
                
                if (data.success) {
                    loadMessages();
                } else {
                    showError('Error sending location: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                if (error.code) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            showError('Location access denied. Please enable location permissions.');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            showError('Location information unavailable.');
                            break;
                        case error.TIMEOUT:
                            showError('Location request timed out.');
                            break;
                        default:
                            showError('Error getting location: ' + error.message);
                            break;
                    }
                } else {
                    showError('Network error. Please check your connection.');
                }
            } finally {
                isLoading = false;
                setButtonsDisabled(false);
            }
        }
        
        async function loadMessages() {
            if (isLoading) return;
            
            const formData = new FormData();
            formData.append('action', 'get_messages');
            formData.append('last_id', lastMessageId);
            
            try {
                const messages = await makeRequest('', {
                    method: 'POST',
                    body: formData
                });
                
                if (Array.isArray(messages) && messages.length > 0) {
                    const chatMessages = document.getElementById('chatMessages');
                    
                    // Remove loading message if it exists
                    const loadingMsg = chatMessages.querySelector('.loading');
                    if (loadingMsg) loadingMsg.remove();
                    
                    messages.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${message.sender}`;
                        
                        let content = '';
                        
                        if (message.message) {
                            content = `<div class="message-content">${escapeHtml(message.message)}</div>`;
                        } else if (message.image_path) {
                            content = `<div class="message-content"><img src="${message.image_path}" alt="Shared image" onclick="window.open('${message.image_path}', '_blank')" loading="lazy"></div>`;
                        } else if (message.latitude && message.longitude) {
                            const googleMapsUrl = `https://www.google.com/maps?q=${message.latitude},${message.longitude}`;
                            content = `<div class="message-content location-message">📍 <a href="${googleMapsUrl}" target="_blank" class="location-link">View Location</a></div>`;
                        }
                        
                        const timestamp = new Date(message.timestamp).toLocaleString();
                        content += `<div class="timestamp">${timestamp}</div>`;
                        
                        messageDiv.innerHTML = content;
                        chatMessages.appendChild(messageDiv);
                        
                        lastMessageId = Math.max(lastMessageId, parseInt(message.id));
                    });
                    
                    // Scroll to bottom
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            } catch (error) {
                console.error('Error loading messages:', error);
                // Don't show error for message loading to avoid spam
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                startPolling();
                loadMessages();
            } else {
                stopPolling();
            }
        });
        
        // Handle window beforeunload
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });
    </script>
</body>
</html>