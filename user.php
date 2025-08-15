<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

require_once 'config.php';

// Get user details
try {
    $stmt = $pdo->prepare("
        SELECT status, is_active, barangay, first_name, last_name, city, profile_picture
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        session_destroy();
        header("Location: user_login.php");
        exit();
    }
    
    $user_status = $user_data['status'];
    $is_active = $user_data['is_active'];
    $user_barangay = $user_data['barangay'];
    $user_city = $user_data['city'] ?? 'Unknown';
    $first_name = $user_data['first_name'];
    $last_name = $user_data['last_name'];
    $profile_picture = $user_data['profile_picture'];

    // Check if user account is active
    if (!$is_active) {
        session_destroy();
        header("Location: user_login.php?error=account_deactivated");
        exit();
    }
    
} catch (Exception $e) {
    session_destroy();
    header("Location: user_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
  <title>Help Chat - Barangay Help Desk</title>
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
      overflow-x: hidden;
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
    }

    .nav-link:hover {
        background: rgba(255, 145, 77, 0.1);
        color: #ff914d;
    }

    .nav-link.active {
        background: linear-gradient(135deg, #ff914d, #ff5e00);
        color: white;
    }

    .logo {
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #ff914d, #ff5e00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* USER INFO */
    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-avatar {
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

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-avatar-initial {
        font-size: 16px;
        font-weight: 600;
    }

    .user-avatar:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(255, 145, 77, 0.4);
    }

    .user-avatar::after {
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

    .user-avatar:hover::after {
        opacity: 1;
    }

    .user-status {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-basic {
        background: #fff3cd;
        color: #856404;
    }

    .status-verified {
        background: #d4edda;
        color: #155724;
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

    .welcome-banner {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        margin: 20px auto;
        max-width: 1400px;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .welcome-title {
        font-size: 2rem;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #ff914d, #ff5e00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .main-chat-container {
        max-width: 1400px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .chat-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 70vh;
        min-height: 500px;
    }

    .help-type-selector {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .help-type-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #2c3e50;
        text-align: center;
    }

    .help-type-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .help-type-btn {
        background: linear-gradient(135deg, #ff914d, #ff5e00);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .help-type-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 145, 77, 0.3);
    }

    .help-type-btn.selected {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
    }

    .chat-header {
        background: linear-gradient(135deg, #ff914d, #ff5e00);
        color: white;
        padding: 20px;
        text-align: center;
        flex-shrink: 0;
    }

    .chat-header h2 {
        font-size: 24px;
        margin-bottom: 5px;
    }

    .chat-header p {
        opacity: 0.9;
        font-size: 16px;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        border-bottom: 1px solid #eee;
        -webkit-overflow-scrolling: touch;
    }

    .message {
        margin: 10px 0;
        padding: 12px;
        border-radius: 12px;
        max-width: 85%;
        position: relative;
        word-wrap: break-word;
        hyphens: auto;
        font-size: 16px;
        line-height: 1.4;
    }

    .user-message {
        background: #ff914d;
        color: white;
        margin-left: auto;
        text-align: right;
        border-bottom-right-radius: 4px;
    }

    .admin-message {
        background: #f1f3f4;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .image-message {
        padding: 8px;
    }

    .message-image {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s ease;
        display: block;
    }

    .message-image:hover {
        transform: scale(1.02);
    }

    .image-caption {
        margin-top: 8px;
        font-size: 14px;
        opacity: 0.9;
    }

    .location-info {
        background: #4CAF50;
        color: white;
        font-size: 12px;
        padding: 6px 8px;
        border-radius: 4px;
        margin-top: 5px;
    }

    .location-info a {
        color: white;
        text-decoration: underline;
    }

    .chat-input {
        padding: 15px;
        display: flex;
        gap: 8px;
        align-items: flex-end;
        position: relative;
        flex-shrink: 0;
        background: white;
    }

    .input-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .input-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .attachment-btn, .camera-btn {
        width: 35px;
        height: 35px;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .attachment-btn {
        background: #4CAF50;
        color: white;
    }

    .attachment-btn:hover {
        background: #45a049;
        transform: scale(1.05);
    }

    .camera-btn {
        background: #FF5722;
        color: white;
    }

    .camera-btn:hover {
        background: #e64a19;
        transform: scale(1.05);
    }

    .file-input {
        display: none;
    }

    .message-input {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 20px;
        font-size: 16px;
        resize: none;
        min-height: 38px;
        max-height: 100px;
        line-height: 1.4;
        font-family: inherit;
    }

    .send-btn {
        padding: 10px 16px;
        background: linear-gradient(135deg, #ff914d, #ff5e00);
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
        white-space: nowrap;
        transition: all 0.2s ease;
        min-width: 60px;
    }

    .send-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .send-btn:hover:not(:disabled) {
        transform: scale(1.05);
    }

    .status {
        text-align: center;
        padding: 8px;
        background: #f8f9fa;
        color: #666;
        font-size: 12px;
        flex-shrink: 0;
    }

    .image-preview {
        position: relative;
        margin-bottom: 8px;
        max-width: 100%;
    }

    .preview-image {
        max-width: 100%;
        max-height: 120px;
        border-radius: 8px;
        border: 2px solid #ddd;
        display: block;
    }

    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 0, 0, 0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loading {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Image modal styles */
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
        padding: 20px;
    }

    .modal-image {
        max-width: 95vw;
        max-height: 95vh;
        border-radius: 8px;
        object-fit: contain;
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

    /* Camera modal styles */
    .camera-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.95);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10001;
        flex-direction: column;
        padding: 20px;
    }

    .camera-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        max-width: 95vw;
        max-height: 95vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        overflow: hidden;
    }

    .camera-container h3 {
        font-size: 20px;
        margin: 0;
    }

    .camera-video, .captured-photo {
        width: 100%;
        max-width: 400px;
        max-height: 300px;
        border-radius: 10px;
        background: #000;
        object-fit: cover;
    }

    .camera-canvas {
        display: none;
    }

    .camera-controls {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }

    .capture-btn {
        width: 60px;
        height: 60px;
        background: #FF5722;
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .capture-btn:hover {
        background: #e64a19;
        transform: scale(1.05);
    }

    .camera-close-btn, .camera-use-btn, .camera-retake-btn {
        padding: 10px 15px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .camera-close-btn {
        background: #666;
        color: white;
    }

    .camera-close-btn:hover {
        background: #555;
    }

    .camera-use-btn {
        background: #4CAF50;
        color: white;
        display: none;
    }

    .camera-use-btn:hover {
        background: #45a049;
    }

    .camera-retake-btn {
        background: #FF9800;
        color: white;
        display: none;
    }

    .camera-retake-btn:hover {
        background: #f57c00;
    }

    .camera-error {
        color: #f44336;
        text-align: center;
        padding: 10px;
        display: none;
        font-size: 14px;
        max-width: 100%;
    }

    @media (max-width: 768px) {
        .main-chat-container {
            padding: 0 10px;
        }

        .chat-container {
            height: 60vh;
            min-height: 400px;
        }

        .nav-container {
            flex-direction: column;
            gap: 15px;
        }

        .nav-links {
            order: -1;
        }

        .help-type-buttons {
            flex-direction: column;
            align-items: center;
        }

        .help-type-btn {
            width: 100%;
            max-width: 250px;
        }

        .message {
            max-width: 90%;
            font-size: 14px;
        }

        .message-input {
            font-size: 16px;
        }

        .camera-video, .captured-photo {
            max-height: 250px;
        }

        .modal-close {
            top: 10px;
            right: 15px;
            font-size: 30px;
        }

        .image-modal {
            padding: 10px;
        }

        .camera-modal {
            padding: 10px;
        }

        .camera-container {
            padding: 15px;
            border-radius: 10px;
            gap: 10px;
        }

        .capture-btn {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .camera-controls {
            gap: 8px;
        }

        .camera-close-btn, .camera-use-btn, .camera-retake-btn {
            padding: 8px 12px;
            font-size: 13px;
        }
    }

    button:focus, input:focus, textarea:focus {
        outline: 2px solid #ff914d;
        outline-offset: 2px;
    }

    * {
        -webkit-touch-callout: none;
        -webkit-tap-highlight-color: transparent;
    }

    .chat-messages {
        scroll-behavior: smooth;
    }
  </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🛡️ Barangay <?php echo htmlspecialchars($user_barangay); ?></div>
            <div class="nav-links">
                <a href="user_dashboard.php" class="nav-link">Dashboard</a>
                <?php if ($user_status === 'verified'): ?>
                    <a href="user_forms.php" class="nav-link">Request Forms</a>
                <?php endif; ?>
                <a href="user_requests.php" class="nav-link">My Requests</a>
                <a href="user.php" class="nav-link active">💬 Chat Support</a>
            </div>
            <div class="user-info">
                <a href="user_edit.php" class="user-avatar" title="Edit Profile">
                    <?php if (!empty($profile_picture) && file_exists($profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <span class="user-avatar-initial"><?php echo strtoupper($first_name[0]); ?></span>
                    <?php endif; ?>
                </a>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">
                        <?php echo $first_name . " " . $last_name; ?>
                    </div>
                </div>
                <div class="user-status status-<?php echo $user_status; ?>">
                    <?php echo ucfirst($user_status); ?>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="welcome-banner">
        <h1 class="welcome-title">Chat Support Center</h1>
        <p>Get instant help and support for your barangay services and inquiries.</p>
    </div>

    <div class="main-chat-container">
        <div class="help-type-selector">
            <div class="help-type-title">Select Help Type</div>
            <div class="help-type-buttons">
                <button class="help-type-btn" data-type="emergency" type="button">
                    <span>🚨</span>
                    <span>Emergency Help</span>
                </button>
                <button class="help-type-btn" data-type="technical" type="button">
                    <span>🔧</span>
                    <span>Technical Support</span>
                </button>
                <button class="help-type-btn" data-type="general" type="button">
                    <span>💬</span>
                    <span>General Inquiry</span>
                </button>
            </div>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <h2>Help Center</h2>
                <p>How can we assist you today?</p>
            </div>

            <div class="status" id="statusMessage">
                💬 Please select a help type above to start chatting.
            </div>

            <div class="chat-messages" id="chatMessages">
                <!-- Messages will appear here -->
            </div>

            <div class="chat-input">
                <div class="input-area">
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img class="preview-image" id="previewImage" src="" alt="Preview">
                        <button class="remove-image" type="button" onclick="removeImage()">&times;</button>
                    </div>

                    <div class="input-row">
                        <button class="attachment-btn" type="button" title="Upload from gallery" onclick="document.getElementById('fileInput').click()">
                            📎
                        </button>
                        <button class="camera-btn" type="button" title="Take photo with camera" onclick="openCamera()">
                            📷
                        </button>
                        <input type="file" id="fileInput" class="file-input" accept="image/*" onchange="handleFileSelect(event)">
                        <textarea id="messageInput" class="message-input" placeholder="Type your message..." rows="1" disabled onkeypress="handleKeyPress(event)"></textarea>
                        <button class="send-btn" id="sendBtn" type="button" disabled onclick="sendMessage()">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image modal -->
    <div class="image-modal" id="imageModal" onclick="closeImageModal()">
        <button class="modal-close" onclick="closeImageModal()">&times;</button>
        <img class="modal-image" id="modalImage" src="" alt="Full size image">
    </div>

    <!-- Camera modal -->
    <div class="camera-modal" id="cameraModal">
        <div class="camera-container">
            <h3>📷 Take a Photo</h3>
            
            <video id="cameraVideo" class="camera-video" autoplay playsinline></video>
            <canvas id="cameraCanvas" class="camera-canvas"></canvas>
            <img id="capturedPhoto" class="captured-photo" alt="Captured photo">
            
            <div class="camera-error" id="cameraError">
                Camera access denied or not available. Please use the file upload option instead.
            </div>
            
            <div class="camera-controls" id="cameraControls">
                <button class="camera-close-btn" onclick="closeCameraModal()">Cancel</button>
                <button class="capture-btn" id="captureBtn" onclick="capturePhoto()">📷</button>
                <button class="camera-retake-btn" id="retakeBtn" onclick="retakePhoto()">Retake</button>
                <button class="camera-use-btn" id="usePhotoBtn" onclick="usePhoto()">Use Photo</button>
            </div>
        </div>
    </div>

    <script>
        console.log('=== ENHANCED CHAT WITH CAMERA & LOCATION ===');
        
        // User info from PHP with enhanced safety
        let userInfo;
        try {
            const rawUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
            const rawUserName = <?php echo json_encode($_SESSION['user_name']); ?>;
            const rawBarangay = <?php echo json_encode($user_barangay); ?>;
            const rawCity = <?php echo json_encode($user_city); ?>;
            const rawStatus = <?php echo json_encode($user_status); ?>;

            let fixedUserId = rawUserId || Date.now();
            let fixedUserName = rawUserName;

            // Fix user name if needed
            if (!fixedUserName || fixedUserName === 'null') {
                const firstName = '<?php echo addslashes($first_name); ?>';
                const lastName = '<?php echo addslashes($last_name); ?>';
                
                if (firstName && lastName) {
                    fixedUserName = firstName + ' ' + lastName;
                } else if (firstName) {
                    fixedUserName = firstName;
                } else {
                    fixedUserName = 'User_' + fixedUserId;
                }
            }

            userInfo = {
                id: fixedUserId,
                name: fixedUserName,
                barangay: rawBarangay || 'Unknown',
                city: rawCity || 'Unknown',
                status: rawStatus || 'basic'
            };
            
            console.log('✅ userInfo:', userInfo);
        } catch (error) {
            console.error('Error creating userInfo:', error);
            userInfo = {
                id: Date.now(),
                name: 'Fallback_User',
                barangay: 'Unknown',
                city: 'Unknown',
                status: 'basic'
            };
        }

        // Create conversation ID
        let conversationId;
        try {
            if (userInfo.name && typeof userInfo.name === 'string') {
                conversationId = userInfo.name.toLowerCase().replace(/\s+/g, '_') + '_' + userInfo.id;
            } else {
                conversationId = 'user_' + userInfo.id + '_' + Date.now();
            }
            console.log('conversationId:', conversationId);
        } catch (error) {
            console.error('Error creating conversationId:', error);
            conversationId = 'fallback_' + Date.now();
        }

        // Global variables
        let selectedHelpType = '';
        let chatEnabled = false;
        let locationShared = false;
        let selectedFile = null;
        let cameraStream = null;
        let capturedImageBlob = null;
        let messageLoadInterval = null;
        let firstMessage = true;

        // Select help type function with location sharing
        window.selectHelpType = function(type) {
            console.log('selectHelpType called with:', type);
            
            try {
                selectedHelpType = type;
                chatEnabled = true;

                // Update buttons
                const buttons = document.querySelectorAll('.help-type-btn');
                buttons.forEach(btn => btn.classList.remove('selected'));
                
                const clickedBtn = document.querySelector(`[data-type="${type}"]`);
                if (clickedBtn) {
                    clickedBtn.classList.add('selected');
                }

                // Update status and handle emergency location
                const statusEl = document.getElementById('statusMessage');
                if (statusEl) {
                    if (type === 'emergency') {
                        statusEl.textContent = '🚨 Emergency support requested. Getting your location...';
                        getLocation();
                    } else if (type === 'technical') {
                        statusEl.textContent = '🔧 Technical support requested. An admin will assist you shortly.';
                    } else {
                        statusEl.textContent = '💬 General inquiry started. How can we help you?';
                    }
                }

                // Enable input
                const messageInput = document.getElementById('messageInput');
                const sendBtn = document.getElementById('sendBtn');
                
                if (messageInput) {
                    messageInput.disabled = false;
                }
                if (sendBtn) {
                    sendBtn.disabled = false;
                }

                // Add confirmation message to chat
                addMessageToChat(`You selected: ${type.charAt(0).toUpperCase() + type.slice(1)} Help`, 'admin');

                // Start message loading interval
                if (messageLoadInterval) clearInterval(messageLoadInterval);
                messageLoadInterval = setInterval(loadMessages, 3000);
                
                console.log('selectHelpType completed successfully');
                
            } catch (error) {
                console.error('Error in selectHelpType:', error);
                alert('Error: ' + error.message);
            }
        };

        // Location sharing function
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;
                    sendLocationToServer(lat, lng);
                }, function() {
                    document.getElementById('statusMessage').textContent = '⚠️ Location access denied. Emergency support still active.';
                });
            }
        }

        function sendLocationToServer(lat, lng) {
            const formData = new FormData();
            formData.append('action', 'send_location');
            formData.append('conversation_id', conversationId);
            formData.append('user_id', userInfo.id);
            formData.append('user_name', userInfo.name);
            formData.append('lat', lat);
            formData.append('lng', lng);
            formData.append('help_type', selectedHelpType);
            formData.append('barangay', userInfo.barangay);
            formData.append('city', userInfo.city);

            fetch('user_handler.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    locationShared = true;
                    document.getElementById('statusMessage').textContent = '📍 Emergency support with location shared. Admin notified.';
                    addMessageToChat('📍 Location shared with emergency support', 'admin');
                } else {
                    console.error('Location sending failed:', data.error);
                }
            }).catch(error => {
                console.error('Error sending location:', error);
            });
        }

        // Camera functions
        function openCamera() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('cameraVideo');
            const errorDiv = document.getElementById('cameraError');
            
            modal.style.display = 'flex';
            errorDiv.style.display = 'none';
            resetCameraUI();
            
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            })
            .then(stream => {
                cameraStream = stream;
                video.srcObject = stream;
                video.style.display = 'block';
            })
            .catch(error => {
                console.error('Camera access error:', error);
                errorDiv.style.display = 'block';
                video.style.display = 'none';
            });
        }

        function closeCameraModal() {
            const modal = document.getElementById('cameraModal');
            modal.style.display = 'none';
            
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            
            resetCameraUI();
        }

        function resetCameraUI() {
            document.getElementById('cameraVideo').style.display = 'block';
            document.getElementById('capturedPhoto').style.display = 'none';
            document.getElementById('captureBtn').style.display = 'inline-block';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('usePhotoBtn').style.display = 'none';
            capturedImageBlob = null;
        }

        function capturePhoto() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.getElementById('cameraCanvas');
            const photo = document.getElementById('capturedPhoto');
            const context = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            
            canvas.toBlob(blob => {
                capturedImageBlob = blob;
                
                const photoURL = URL.createObjectURL(blob);
                photo.src = photoURL;
                photo.style.display = 'block';
                
                video.style.display = 'none';
                document.getElementById('captureBtn').style.display = 'none';
                document.getElementById('retakeBtn').style.display = 'inline-block';
                document.getElementById('usePhotoBtn').style.display = 'inline-block';
            }, 'image/jpeg', 0.8);
        }

        function retakePhoto() {
            resetCameraUI();
        }

        function usePhoto() {
            if (capturedImageBlob) {
                const file = new File([capturedImageBlob], `camera_photo_${Date.now()}.jpg`, {
                    type: 'image/jpeg'
                });
                
                selectedFile = file;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
                
                closeCameraModal();
            }
        }

        // File handling functions
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file.');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB.');
                    return;
                }

                selectedFile = file;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            selectedFile = null;
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('fileInput').value = '';
        }

        // Image modal functions
        function showImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Enhanced send message function
        window.sendMessage = function() {
            console.log('sendMessage called');
            
            if (!chatEnabled) {
                alert('Please select a help type first.');
                return;
            }

            const input = document.getElementById('messageInput');
            const message = input ? input.value.trim() : '';
            const sendBtn = document.getElementById('sendBtn');

            if (!message && !selectedFile) {
                alert('Please type a message or select an image');
                return;
            }

            sendBtn.disabled = true;
            sendBtn.innerHTML = '<div class="loading"></div>';

            if (selectedFile) {
                sendImageMessage(message || 'Sent an image');
            } else {
                sendTextMessage(message);
            }

            if (input) {
                input.value = '';
                input.style.height = 'auto';
            }
            removeImage();
        };

        function sendTextMessage(message) {
            addMessageToChat(message, 'user');

            if (firstMessage) {
                setTimeout(() => {
                    addMessageToChat("Thank you for your message. An admin will be with you shortly.", 'admin');
                    firstMessage = false;
                }, 1000);
            }

            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('conversation_id', conversationId);
            formData.append('user_id', userInfo.id.toString());
            formData.append('user_name', userInfo.name);
            formData.append('message', message);
            formData.append('help_type', selectedHelpType);
            formData.append('barangay', userInfo.barangay);
            formData.append('city', userInfo.city);

            fetch('user_handler.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Message sent successfully');
                    setTimeout(loadMessages, 500);
                } else {
                    console.error('Server error:', data.error);
                    alert('Failed to send message: ' + data.error);
                }
            }).catch(error => {
                console.error('Fetch error:', error);
                alert('Error sending message. Please try again.');
            }).finally(() => {
                resetSendButton();
            });
        }

        function sendImageMessage(caption) {
            const formData = new FormData();
            formData.append('action', 'send_image');
            formData.append('conversation_id', conversationId);
            formData.append('user_id', userInfo.id.toString());
            formData.append('user_name', userInfo.name);
            formData.append('image', selectedFile);
            formData.append('caption', caption);
            formData.append('help_type', selectedHelpType);
            formData.append('barangay', userInfo.barangay);
            formData.append('city', userInfo.city);

            addImageMessageToChat(selectedFile, caption, 'user');

            fetch('user_handler.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Image sent successfully');
                    setTimeout(loadMessages, 500);
                } else {
                    console.error('Failed to send image:', data.error);
                    alert('Failed to send image: ' + data.error);
                }
            }).catch(error => {
                console.error('Error sending image:', error);
                alert('Error sending image. Please try again.');
            }).finally(() => {
                resetSendButton();
            });
        }

        function resetSendButton() {
            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = false;
            sendBtn.innerHTML = 'Send';
        }

        // Chat message functions
        function addMessageToChat(message, senderType) {
            let chatMessages = document.getElementById('chatMessages');
            let messageDiv = document.createElement('div');
            messageDiv.className = `message ${senderType}-message`;
            messageDiv.textContent = message;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function addImageMessageToChat(file, caption, senderType) {
            let chatMessages = document.getElementById('chatMessages');
            let messageDiv = document.createElement('div');
            messageDiv.className = `message ${senderType}-message image-message`;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                messageDiv.innerHTML = `
                    <img class="message-image" src="${e.target.result}" alt="Uploaded image" onclick="showImageModal('${e.target.result}')">
                    ${caption ? `<div class="image-caption">${caption}</div>` : ''}
                `;
            };
            reader.readAsDataURL(file);
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Load messages function
        function loadMessages() {
            const formData = new FormData();
            formData.append('action', 'get_messages');
            formData.append('conversation_id', conversationId);
            formData.append('user_id', userInfo.id);

            fetch('user_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(messages => {
                if (Array.isArray(messages)) {
                    let chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';

                    messages.forEach(message => {
                        let messageDiv = document.createElement('div');
                        messageDiv.className = `message ${message.sender_type}-message`;
                        
                        if (message.message_type === 'image' && message.file_path) {
                            messageDiv.classList.add('image-message');
                            messageDiv.innerHTML = `
                                <img class="message-image" src="${message.file_path}" alt="Shared image" onclick="showImageModal('${message.file_path}')">
                                ${message.message ? `<div class="image-caption">${message.message}</div>` : ''}
                            `;
                        } else {
                            messageDiv.textContent = message.message;
                        }

                        if (message.location_lat && message.location_lng) {
                            let locationDiv = document.createElement('div');
                            locationDiv.className = 'location-info';
                            locationDiv.innerHTML = `📍 Location: <a href="https://maps.google.com/?q=${message.location_lat},${message.location_lng}" target="_blank">${message.location_lat}, ${message.location_lng}</a>`;
                            messageDiv.appendChild(locationDiv);
                        }

                        chatMessages.appendChild(messageDiv);
                    });

                    chatMessages.scrollTop = chatMessages.scrollHeight;
                } else if (messages.error) {
                    console.error('Error loading messages:', messages.error);
                }
            }).catch(error => {
                console.error('Error loading messages:', error);
            });
        }

        // Event handlers
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }

        // Initialize when DOM loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - starting enhanced initialization');

            try {
                // Find and setup help type buttons
                const helpButtons = document.querySelectorAll('.help-type-btn');
                console.log('Found help buttons:', helpButtons.length);
                
                helpButtons.forEach((btn, index) => {
                    const type = btn.getAttribute('data-type');
                    console.log(`Setting up button ${index}: ${type}`);
                    
                    btn.onclick = function() {
                        console.log('Button clicked:', type);
                        selectHelpType(type);
                    };
                });

                // Auto-resize textarea
                const messageInput = document.getElementById('messageInput');
                if (messageInput) {
                    messageInput.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                    });
                }

                console.log('✅ Enhanced initialization complete');
                
            } catch (error) {
                console.error('❌ Enhanced initialization error:', error);
                alert('Initialization error: ' + error.message);
            }
        });

        // Cleanup functions
        window.addEventListener('beforeunload', function() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
            if (messageLoadInterval) {
                clearInterval(messageLoadInterval);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const cameraModal = document.getElementById('cameraModal');
                const imageModal = document.getElementById('imageModal');
                
                if (cameraModal.style.display === 'flex') {
                    closeCameraModal();
                } else if (imageModal.style.display === 'flex') {
                    closeImageModal();
                }
            }
        });

        // Global test function
        window.testFunction = function() {
            console.log('ENHANCED TEST FUNCTION WORKS!');
            alert('Enhanced JavaScript with camera and location features is working!');
        };

        console.log('=== ENHANCED SCRIPT FULLY LOADED ===');
        console.log('Features: Camera, Image Upload, Location Sharing, Real-time Chat');
    </script>
</body>
</html>