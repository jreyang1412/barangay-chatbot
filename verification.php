<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

require_once 'config.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['full_name'];
    $birthdate = $_POST['birthdate'];

    // File uploads and ID numbers
    $attachments = [];
    $id_numbers = [];
    for ($i = 1; $i <= 3; $i++) {
        // Handle file upload
        if (!empty($_FILES["attachment_$i"]["name"])) {
            $file_name = time() . "_" . basename($_FILES["attachment_$i"]["name"]);
            $target_path = "uploads/" . $file_name;
            if (move_uploaded_file($_FILES["attachment_$i"]["tmp_name"], $target_path)) {
                $attachments[$i] = $target_path;
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('errorModalMsg').textContent = 'Failed to upload file $i';
                        document.getElementById('errorModal').style.display = 'block';
                    });
                </script>";
                exit();
            }
        } else {
            $attachments[$i] = null;
        }

        // Handle ID number
        $id_numbers[$i] = !empty($_POST["id_number_$i"]) ? $_POST["id_number_$i"] : null;
    }

    // Insert query - you'll need to add id_number_1, id_number_2, id_number_3 columns to your database table
    $stmt = $pdo->prepare("
        INSERT INTO verification_requests 
        (user_id, full_name, birthdate, attachment_1, attachment_2, attachment_3, 
         id_number_1, id_number_2, id_number_3, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    if ($stmt->execute([
        $user_id, 
        $full_name, 
        $birthdate, 
        $attachments[1], 
        $attachments[2], 
        $attachments[3],
        $id_numbers[1],
        $id_numbers[2],
        $id_numbers[3]
    ])) {
        // Success — redirect
        header("Location: user_dashboard.php");
        exit();
    } else {
        // Error — show modal
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('errorModalMsg').textContent = 'Database error: could not submit your request';
                document.getElementById('errorModal').style.display = 'block';
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification - Barangay Help Desk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff7f00 0%, #ff9500 25%, #ffb347 50%, #ffd700 100%);
            min-height: 100vh;
            color: #2c3e50;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(255, 127, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #ff7f00;
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
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn {
            background: #ff7f00;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 127, 0, 0.3);
        }
        
        .back-btn:hover {
            background: #e65100;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 127, 0, 0.4);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(255, 127, 0, 0.3);
        }
        
        .user-status {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            color: #e65100;
            border: 1px solid #ffcc80;
        }
        
        .logout-btn {
            background: #ff5722;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 87, 34, 0.3);
        }
        
        .logout-btn:hover {
            background: #d84315;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.4);
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .verification-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(255, 127, 0, 0.1);
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 127, 0, 0.1);
        }
        
        .verification-title {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .verification-subtitle {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            border: 1px solid;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #e8f5e8, #f1f8e9);
            color: #2e7d32;
            border-color: #81c784;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #ffebee, #fce4ec);
            color: #c62828;
            border-color: #ef5350;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            color: #e65100;
            border-color: #ffcc80;
            text-align: left;
        }
        
        .verification-form {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(255, 127, 0, 0.1);
            margin-bottom: 30px;
            border: 1px solid rgba(255, 127, 0, 0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .required {
            color: #ff5722;
        }
        
        input[type="text"], input[type="date"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ffe0b2;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        input:focus {
            outline: none;
            border-color: #ff7f00;
            box-shadow: 0 0 0 3px rgba(255, 127, 0, 0.1);
            background: white;
        }
        
        .file-upload-section {
            margin-bottom: 30px;
        }
        
        .attachment-group {
            border: 1px solid #ffe0b2;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff8f0, #fffaf5);
        }
        
        .attachment-header {
            font-weight: 600;
            color: #e65100;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .attachment-required {
            color: #ff5722;
            font-size: 12px;
            font-weight: 500;
        }
        
        .id-input-group {
            margin-bottom: 15px;
        }
        
        .id-input-group input {
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .file-upload-area {
            border: 2px dashed #ffcc80;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            margin-bottom: 15px;
            cursor: pointer;
            position: relative;
            background: rgba(255, 255, 255, 0.5);
        }
        
        .file-upload-area:hover {
            border-color: #ff7f00;
            background: rgba(255, 248, 240, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 127, 0, 0.1);
        }
        
        .file-upload-area.dragover {
            border-color: #ff7f00;
            background: rgba(255, 242, 230, 0.9);
            transform: scale(1.02);
        }
        
        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .upload-content {
            pointer-events: none;
        }
        
        .upload-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #ff7f00;
            filter: drop-shadow(0 2px 4px rgba(255, 127, 0, 0.3));
        }
        
        .upload-text {
            font-size: 16px;
            font-weight: 600;
            color: #e65100;
            margin-bottom: 5px;
        }
        
        .upload-subtext {
            font-size: 12px;
            color: #ff8a50;
        }
        
        .file-info {
            background: linear-gradient(135deg, #e8f5e8, #f1f8e9);
            border: 1px solid #81c784;
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
            display: none;
        }
        
        .file-info.active {
            display: block;
        }
        
        .file-name {
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 5px;
        }
        
        .file-size {
            font-size: 12px;
            color: #6c757d;
        }
        
        .remove-file-btn {
            background: #ff5722;
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .remove-file-btn:hover {
            background: #d84315;
            transform: translateY(-1px);
        }
        
        .btn {
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(255, 127, 0, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 127, 0, 0.4);
            background: linear-gradient(135deg, #e65100, #d84315);
        }
        
        .btn:disabled {
            background: #bdbdbd;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .status-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(255, 127, 0, 0.1);
            text-align: center;
            border: 1px solid rgba(255, 127, 0, 0.1);
        }
        
        .status-pending {
            border-left: 5px solid #ff9800;
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            color: #e65100;
        }
        
        .status-rejected {
            border-left: 5px solid #ff5722;
            background: linear-gradient(135deg, #ffebee, #fce4ec);
            color: #d32f2f;
        }
        
        .status-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            filter: drop-shadow(0 2px 4px rgba(255, 127, 0, 0.3));
        }
        
        .status-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .status-message {
            font-size: 1rem;
            margin-bottom: 15px;
        }
        
        .status-date {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .new-request-btn {
            background: linear-gradient(135deg, #4caf50, #66bb6a);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
        }
        
        .new-request-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(76, 175, 80, 0.4);
            background: linear-gradient(135deg, #388e3c, #4caf50);
        }
        
        .rejection-reason {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            color: #e65100;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid #ff9800;
        }
        
        /* Enhanced animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .verification-card,
        .verification-form,
        .status-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .upload-icon:hover {
            animation: pulse 1s infinite;
        }
        
        /* Comprehensive Mobile Responsive Design */
        @media (max-width: 1024px) {
            .container {
                max-width: 95%;
                padding: 20px 15px;
            }
            
            .verification-card,
            .verification-form {
                padding: 30px 25px;
            }
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 0 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            
            .user-info {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            
            .logo {
                font-size: 1.3rem;
                margin-bottom: 10px;
            }
            
            .container {
                padding: 15px 10px;
            }
            
            .verification-card,
            .verification-form,
            .status-card {
                padding: 25px 20px;
                margin-bottom: 20px;
            }
            
            .verification-title {
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .verification-subtitle {
                font-size: 1rem;
            }
            
            .attachment-group {
                padding: 15px;
            }
            
            .file-upload-area {
                padding: 20px 15px;
            }
            
            .upload-icon {
                font-size: 2rem;
            }
            
            .upload-text {
                font-size: 14px;
            }
            
            .upload-subtext {
                font-size: 11px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
            
            .attachment-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
        
        @media (max-width: 480px) {
            .navbar {
                padding: 10px 0;
            }
            
            .nav-container {
                padding: 0 10px;
            }
            
            .logo {
                font-size: 1.1rem;
            }
            
            .back-btn,
            .logout-btn {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .user-avatar {
                width: 35px;
                height: 35px;
            }
            
            .user-status {
                padding: 3px 8px;
                font-size: 10px;
            }
            
            .container {
                padding: 10px 5px;
            }
            
            .verification-card,
            .verification-form,
            .status-card {
                padding: 20px 15px;
                border-radius: 15px;
            }
            
            .verification-title {
                font-size: 1.7rem;
            }
            
            .form-title {
                font-size: 1.3rem;
            }
            
            .verification-subtitle {
                font-size: 0.95rem;
            }
            
            .attachment-group {
                padding: 12px;
                margin-bottom: 15px;
            }
            
            .file-upload-area {
                padding: 15px 10px;
            }
            
            .upload-icon {
                font-size: 1.8rem;
                margin-bottom: 8px;
            }
            
            .upload-text {
                font-size: 13px;
            }
            
            .upload-subtext {
                font-size: 10px;
            }
            
            input[type="text"], 
            input[type="date"] {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 13px;
            }
            
            .alert {
                padding: 12px;
                font-size: 13px;
            }
            
            .status-icon {
                font-size: 2.5rem;
            }
            
            .status-title {
                font-size: 1.3rem;
            }
            
            .status-message {
                font-size: 0.9rem;
            }
        }
        
        /* Additional responsive utilities */
        .form-group:focus-within label {
            color: #ff7f00;
            transition: color 0.3s ease;
        }
        
        .attachment-group:hover {
            border-color: #ff7f00;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        /* Touch-friendly buttons for mobile */
        @media (max-width: 768px) {
            .back-btn,
            .logout-btn,
            .new-request-btn,
            .remove-file-btn {
                min-height: 44px;
                min-width: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            
            .file-upload-area {
                min-height: 120px;
            }
        }
        
        /* Improved text legibility on small screens */
        @media (max-width: 480px) {
            body {
                font-size: 14px;
                line-height: 1.5;
            }
            
            label {
                font-size: 13px;
            }
            
            .attachment-header {
                font-size: 14px;
            }
            
            .file-name {
                font-size: 12px;
                word-break: break-word;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🏛️ Barangay Help Desk</div>
            <div class="nav-links">
                <a href="user_dashboard.php" class="back-btn">← Back to Dashboard</a>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                        <div style="font-size: 12px; color: #7f8c8d;">
                            <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                        </div>
                    </div>
                    <div class="user-status">
                        <?php echo ucfirst($user_status); ?>
                    </div>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="verification-card">
            <div class="verification-title">📋 Account Verification</div>
            <p class="verification-subtitle">
                Verify your account to access barangay online services and request official documents.
            </p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                ✅ <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                ❌ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($verification_status && $verification_status['status'] === 'pending'): ?>
            <div class="status-card status-pending">
                <div class="status-icon">⏳</div>
                <div class="status-title">Verification Pending</div>
                <div class="status-message">
                    Your verification request is currently being reviewed by the barangay administration.
                </div>
                <div class="status-date">
                    Submitted on <?php echo date('F j, Y \a\t g:i A', strtotime($verification_status['created_at'])); ?>
                </div>
                <p style="margin-top: 15px; font-size: 14px; color: #6c757d;">
                    You will be notified once your account has been verified. This process typically takes 1-3 business days.
                </p>
            </div>
        <?php elseif ($verification_status && $verification_status['status'] === 'rejected'): ?>
            <div class="status-card status-rejected">
                <div class="status-icon">❌</div>
                <div class="status-title">Verification Rejected</div>
                <div class="status-message">
                    Your verification request was rejected. Please review the reason below and submit a new request.
                </div>
                <div class="status-date">
                    Rejected on <?php echo date('F j, Y \a\t g:i A', strtotime($verification_status['created_at'])); ?>
                </div>
                
                <?php if (!empty($verification_status['rejection_reason'])): ?>
                    <div class="rejection-reason">
                        <strong>Reason for rejection:</strong><br>
                        <?php echo htmlspecialchars($verification_status['rejection_reason']); ?>
                    </div>
                <?php endif; ?>
                
                <button onclick="showVerificationForm()" class="new-request-btn">
                    🔄 Submit New Request
                </button>
            </div>
        <?php endif; ?>

        <?php if (!$verification_status || $verification_status['status'] === 'rejected'): ?>
            <div class="verification-form" <?php echo ($verification_status && $verification_status['status'] === 'rejected') ? 'style="display: none;" id="verification-form"' : ''; ?>>
                <div class="form-header">
                    <h2 class="form-title">📝 Submit Verification Request</h2>
                    <p>Please provide the following information and documents to verify your account.</p>
                </div>

                <div class="alert alert-info">
                    <strong>📌 Requirements:</strong><br>
                    • Valid government-issued ID (Driver's License, SSS, UMID, Passport, etc.)<br>
                    • Clear, readable photo or scan of your ID<br>
                    • Exact ID numbers as shown on your documents<br>
                    • Maximum 3 attachments, 5MB each<br>
                    • Supported formats: JPG, PNG, PDF<br>
                    • Make sure all information is clearly visible
                </div>

                <form method="POST" action="" enctype="multipart/form-data" id="verificationForm">
                    <div class="form-group">
                        <label for="full_name">Full Name (as shown on your ID) <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" required 
                               placeholder="Enter your complete legal name">
                    </div>
                    
                    <div class="form-group">
                        <label for="birthdate">Date of Birth <span class="required">*</span></label>
                        <input type="date" id="birthdate" name="birthdate" required>
                    </div>
                    
                    <div class="file-upload-section">
                        <label>ID Documents <span class="required">*</span></label>
                        <p style="font-size: 12px; color: #666; margin-bottom: 20px;">
                            Please attach clear photos or scans of your valid government-issued ID and provide the exact ID numbers.
                        </p>
                        
                        <!-- Primary ID Upload -->
                        <div class="attachment-group">
                            <div class="attachment-header">
                                🆔 Primary ID Document <span class="attachment-required">(Required)</span>
                            </div>
                            
                            <div class="id-input-group">
                                <label for="id_number_1">ID Number <span class="required">*</span></label>
                                <input type="text" id="id_number_1" name="id_number_1" required
                                       placeholder="Enter exact ID number (e.g., 12-3456789-0, A123456789)"
                                       style="margin-bottom: 15px;">
                            </div>
                            
                            <div class="file-upload-area" data-attachment="1">
                                <input type="file" name="attachment_1" class="file-input" 
                                       accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect(1)" required>
                                <div class="upload-content">
                                    <div class="upload-icon">📎</div>
                                    <div class="upload-text">Upload Primary ID</div>
                                    <div class="upload-subtext">Click to upload or drag and drop your main ID document</div>
                                </div>
                                <div class="file-info" id="file-info-1">
                                    <div class="file-name" id="file-name-1"></div>
                                    <div class="file-size" id="file-size-1"></div>
                                    <button type="button" class="remove-file-btn" onclick="removeFile(1)">Remove</button>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary ID Upload -->
                        <div class="attachment-group">
                            <div class="attachment-header">
                                🆔 Secondary ID Document <span style="color: #6c757d; font-size: 12px;">(Optional)</span>
                            </div>
                            
                            <div class="id-input-group">
                                <label for="id_number_2">ID Number</label>
                                <input type="text" id="id_number_2" name="id_number_2"
                                       placeholder="Enter ID number if uploading secondary ID"
                                       style="margin-bottom: 15px;">
                            </div>
                            
                            <div class="file-upload-area" data-attachment="2">
                                <input type="file" name="attachment_2" class="file-input" 
                                       accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect(2)">
                                <div class="upload-content">
                                    <div class="upload-icon">📎</div>
                                    <div class="upload-text">Upload Secondary ID</div>
                                    <div class="upload-subtext">Back side of ID or additional document</div>
                                </div>
                                <div class="file-info" id="file-info-2">
                                    <div class="file-name" id="file-name-2"></div>
                                    <div class="file-size" id="file-size-2"></div>
                                    <button type="button" class="remove-file-btn" onclick="removeFile(2)">Remove</button>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Document Upload -->
                        <div class="attachment-group">
                            <div class="attachment-header">
                                📄 Additional Document <span style="color: #6c757d; font-size: 12px;">(Optional)</span>
                            </div>
                            
                            <div class="id-input-group">
                                <label for="id_number_3">Reference Number</label>
                                <input type="text" id="id_number_3" name="id_number_3"
                                       placeholder="Enter reference number if applicable"
                                       style="margin-bottom: 15px;">
                            </div>
                            
                            <div class="file-upload-area" data-attachment="3">
                                <input type="file" name="attachment_3" class="file-input" 
                                       accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect(3)">
                                <div class="upload-content">
                                    <div class="upload-icon">📎</div>
                                    <div class="upload-text">Upload Additional Document</div>
                                    <div class="upload-subtext">Supporting document or proof of address</div>
                                </div>
                                <div class="file-info" id="file-info-3">
                                    <div class="file-name" id="file-name-3"></div>
                                    <div class="file-size" id="file-size-3"></div>
                                    <button type="button" class="remove-file-btn" onclick="removeFile(3)">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_verification" class="btn" id="submit-btn">
                        📤 Submit Verification Request
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Information Section -->
        <div class="verification-card" style="text-align: left;">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">ℹ️ Verification Process</h3>
            <div style="line-height: 1.6;">
                <p><strong>1. Submit Your Request:</strong> Fill out the form above with your accurate information, ID numbers, and upload clear copies of your valid government-issued ID.</p>
                <br>
                <p><strong>2. Admin Review:</strong> Our barangay staff will review your documents and verify your identity against the provided ID numbers. This process typically takes 1-3 business days.</p>
                <br>
                <p><strong>3. Account Activation:</strong> Once approved, your account will be upgraded to "Verified" status, giving you full access to our online services.</p>
                <br>
                <p><strong>4. Access Services:</strong> You can then request barangay clearances, certificates, and other official documents online.</p>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #fff8f0, #fffaf5); border-radius: 10px; border: 1px solid #ffe0b2;">
                <h4 style="color: #e65100; margin-bottom: 15px;">📞 Need Help?</h4>
                <p style="margin-bottom: 10px;"><strong>Barangay Office:</strong> (02) 123-4567</p>
                <p style="margin-bottom: 10px;"><strong>Email:</strong> verification@barangay.gov.ph</p>
                <p style="margin-bottom: 10px;"><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
                <p style="color: #6c757d; font-size: 14px;">You may also visit our office for in-person verification if you encounter any issues with the online process.</p>
            </div>
        </div>
    </div>

    <script>
        function handleFileSelect(attachmentNumber) {
            const fileInput = event.target;
            const file = fileInput.files[0];
            const fileInfo = document.getElementById(`file-info-${attachmentNumber}`);
            const fileName = document.getElementById(`file-name-${attachmentNumber}`);
            const fileSize = document.getElementById(`file-size-${attachmentNumber}`);
            const uploadContent = fileInput.parentElement.querySelector('.upload-content');
            
            if (file) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    fileInput.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, PNG, and PDF files are allowed');
                    fileInput.value = '';
                    return;
                }
                
                // Show file info
                fileName.textContent = `📄 ${file.name}`;
                fileSize.textContent = `Size: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
                fileInfo.classList.add('active');
                uploadContent.style.display = 'none';
                
                // Update submit button state
                updateSubmitButton();
            }
        }
        
        function removeFile(attachmentNumber) {
            const fileInput = document.querySelector(`input[name="attachment_${attachmentNumber}"]`);
            const fileInfo = document.getElementById(`file-info-${attachmentNumber}`);
            const uploadContent = fileInput.parentElement.querySelector('.upload-content');
            
            fileInput.value = '';
            fileInfo.classList.remove('active');
            uploadContent.style.display = 'block';
            
            // Clear corresponding ID number if removing file
            if (attachmentNumber > 1) {
                document.getElementById(`id_number_${attachmentNumber}`).value = '';
            }
            
            // Update submit button state
            updateSubmitButton();
        }
        
        function updateSubmitButton() {
            const primaryFile = document.querySelector('input[name="attachment_1"]').files[0];
            const submitBtn = document.getElementById('submit-btn');
            const fullName = document.getElementById('full_name').value.trim();
            const birthdate = document.getElementById('birthdate').value;
            const primaryIdNumber = document.getElementById('id_number_1').value.trim();
            
            // Enable submit button only if primary file, ID number and required fields are filled
            if (primaryFile && fullName && birthdate && primaryIdNumber) {
                submitBtn.disabled = false;
                submitBtn.textContent = '📤 Submit Verification Request';
            } else {
                submitBtn.disabled = true;
                submitBtn.textContent = '📤 Please complete all required fields';
            }
        }
        
        function showVerificationForm() {
            document.getElementById('verification-form').style.display = 'block';
            document.querySelector('.status-card').style.display = 'none';
        }
        
        // Validate ID number and file consistency
        function validateIdAndFile(attachmentNumber) {
            const fileInput = document.querySelector(`input[name="attachment_${attachmentNumber}"]`);
            const idInput = document.getElementById(`id_number_${attachmentNumber}`);
            
            // If file is uploaded but no ID number, show warning
            if (fileInput.files[0] && !idInput.value.trim() && attachmentNumber <= 2) {
                idInput.style.borderColor = '#ff9800';
                idInput.placeholder = 'ID number is required when uploading this document';
            } else {
                idInput.style.borderColor = '#ffe0b2';
            }
        }
        
        // Handle drag and drop for all file upload areas
        document.querySelectorAll('.file-upload-area').forEach((area, index) => {
            const attachmentNumber = index + 1;
            
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                area.classList.add('dragover');
            });
            
            area.addEventListener('dragleave', function(e) {
                e.preventDefault();
                area.classList.remove('dragover');
            });
            
            area.addEventListener('drop', function(e) {
                e.preventDefault();
                area.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const fileInput = area.querySelector('.file-input');
                    fileInput.files = files;
                    
                    // Trigger the file select event
                    const event = new Event('change', { bubbles: true });
                    fileInput.dispatchEvent(event);
                }
            });
            
            // Click to upload functionality
            area.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-file-btn')) {
                    return; // Don't trigger file select when removing
                }
                area.querySelector('.file-input').click();
            });
        });
        
        // Monitor form inputs for submit button state
        document.getElementById('full_name').addEventListener('input', updateSubmitButton);
        document.getElementById('birthdate').addEventListener('change', updateSubmitButton);
        document.getElementById('id_number_1').addEventListener('input', updateSubmitButton);
        
        // Monitor ID number inputs for validation
        for (let i = 1; i <= 3; i++) {
            const idInput = document.getElementById(`id_number_${i}`);
            const fileInput = document.querySelector(`input[name="attachment_${i}"]`);
            
            idInput.addEventListener('input', function() {
                validateIdAndFile(i);
                updateSubmitButton();
            });
            
            fileInput.addEventListener('change', function() {
                validateIdAndFile(i);
            });
        }
        
        // Format ID numbers as user types (for common ID formats)
        document.getElementById('id_number_1').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9A-Za-z-]/g, '');
            
            // Auto-format for SSS (XX-XXXXXXX-X)
            if (value.match(/^\d{2}\d{7}\d{1}$/)) {
                value = value.replace(/(\d{2})(\d{7})(\d{1})/, '$1-$2-$3');
            }
            // Auto-format for UMID (XXXX-XXXXXXX-X)
            else if (value.match(/^\d{4}\d{7}\d{1}$/)) {
                value = value.replace(/(\d{4})(\d{7})(\d{1})/, '$1-$2-$3');
            }
            
            e.target.value = value.toUpperCase();
        });
        
        // Set max date for birthdate (must be at least 18 years old)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        document.getElementById('birthdate').max = maxDate.toISOString().split('T')[0];
        
        // Form submission validation
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            const primaryFile = document.querySelector('input[name="attachment_1"]').files[0];
            const fullName = document.getElementById('full_name').value.trim();
            const birthdate = document.getElementById('birthdate').value;
            const primaryIdNumber = document.getElementById('id_number_1').value.trim();
            
            if (!primaryFile) {
                e.preventDefault();
                alert('Please upload your primary ID document.');
                return;
            }
            
            if (!primaryIdNumber) {
                e.preventDefault();
                alert('Please enter the ID number for your primary document.');
                document.getElementById('id_number_1').focus();
                return;
            }
            
            if (!fullName || !birthdate) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            // Check if secondary file is uploaded but no ID number
            const secondaryFile = document.querySelector('input[name="attachment_2"]').files[0];
            const secondaryIdNumber = document.getElementById('id_number_2').value.trim();
            
            if (secondaryFile && !secondaryIdNumber) {
                e.preventDefault();
                alert('Please enter the ID number for your secondary document, or remove the file.');
                document.getElementById('id_number_2').focus();
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = '📤 Submitting Request...';
        });
        
        // Initialize submit button state on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSubmitButton();
        });
    </script>
</body>
</html>