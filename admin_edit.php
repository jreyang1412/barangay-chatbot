<?php
session_start();

// Database configuration
$host = 'sql308.infinityfree.com';
$dbname = 'if0_38484017_barangay_chatbot';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if admin is logged in (assuming admin ID is stored in session)
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = '';
$error = '';
$redirect_script = '';

// First, let's add the profile_picture column if it doesn't exist
try {
    // Check if column exists first
    $stmt = $pdo->prepare("SHOW COLUMNS FROM admins LIKE 'profile_picture'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Column doesn't exist, create it
        $pdo->exec("ALTER TABLE admins ADD COLUMN profile_picture VARCHAR(500) NULL AFTER username");
        $message = "Profile picture column added to database successfully!";
    }
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Get current admin data
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        die("Admin not found");
    }
} catch(PDOException $e) {
    die("Error fetching admin data: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate current password
    if (!password_verify($current_password, $admin['password'])) {
        $error = "Current password is incorrect.";
    } else {
        $update_fields = [];
        $password_changed = false;
        
        // Handle password change
        if (!empty($new_password) || !empty($confirm_password)) {
            if (empty($new_password) || empty($confirm_password)) {
                $error = "Both new password and confirm password are required.";
            } elseif ($new_password !== $confirm_password) {
                $error = "New passwords do not match.";
            } elseif (strlen($new_password) < 8) {
                $error = "New password must be at least 8 characters long.";
            } else {
                $update_fields['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                $password_changed = true;
            }
        }
        
        // Handle profile picture upload
        $picture_uploaded = false;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/admin_profile_pictures/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_info = pathinfo($_FILES['profile_picture']['name']);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower($file_info['extension']);
            
            if (in_array($file_extension, $allowed_extensions)) {
                // Check file size (max 5MB)
                if ($_FILES['profile_picture']['size'] <= 5 * 1024 * 1024) {
                    $new_filename = 'admin_' . $admin_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        // Delete old profile picture if exists
                        if (!empty($admin['profile_picture']) && file_exists($admin['profile_picture'])) {
                            unlink($admin['profile_picture']);
                        }
                        $update_fields['profile_picture'] = $upload_path;
                        $picture_uploaded = true;
                    } else {
                        $error = "Failed to upload profile picture.";
                    }
                } else {
                    $error = "Profile picture must be less than 5MB.";
                }
            } else {
                $error = "Only JPG, JPEG, PNG, and GIF files are allowed for profile picture.";
            }
        }
        
        // Update admin data if no errors and there are changes
        if (empty($error) && !empty($update_fields)) {
            try {
                $set_clause = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($update_fields)));
                $sql = "UPDATE admins SET $set_clause WHERE id = :admin_id";
                
                $stmt = $pdo->prepare($sql);
                $update_fields['admin_id'] = $admin_id;
                $stmt->execute($update_fields);
                
                $changes = [];
                if ($password_changed) $changes[] = "password";
                if ($picture_uploaded) $changes[] = "profile picture";
                
                $message = "Successfully updated: " . implode(" and ", $changes) . "!";
                
                // Set redirect script to go back to dashboard after 2 seconds
                $redirect_script = '<script>
                    setTimeout(function() {
                        window.location.href = "admin_dashboard.php";
                    }, 2000);
                </script>';
                
                // Refresh admin data
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
                $stmt->execute([$admin_id]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch(PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } elseif (empty($error) && empty($update_fields)) {
            $error = "No changes were made. Please update your password or profile picture.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile Settings</title>
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
            padding: 20px;
            color: #2c3e50;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.95);
            color: #ff5e00;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .back-btn:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #ff914d 0%, #ff5e00 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '🛡️';
            font-size: 3rem;
            display: block;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .form-container {
            padding: 40px;
        }

        .admin-info {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b3 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #ff914d;
        }

        .admin-info h3 {
            color: #ff5e00;
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-info p {
            margin-bottom: 8px;
            color: #664228;
            font-weight: 500;
        }

        .profile-picture-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b3 100%);
            border-radius: 15px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #ff914d;
            object-fit: cover;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 10px 30px rgba(255, 145, 77, 0.3);
        }

        .default-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #ff914d;
            background: linear-gradient(135deg, #ff914d 0%, #ff5e00 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(255, 145, 77, 0.3);
        }

        .section {
            background: #fff3e0;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            border-left: 5px solid #e74c3c;
        }

        .section h3 {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 1rem;
        }

        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #ffd4a3;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        input[type="password"]:focus,
        input[type="file"]:focus {
            outline: none;
            border-color: #ff914d;
            box-shadow: 0 0 0 3px rgba(255, 145, 77, 0.1);
        }

        .password-strength {
            margin-top: 8px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .file-input-button {
            background: white;
            border: 3px dashed #ffd4a3;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
        }

        .file-input-button:hover {
            border-color: #ff914d;
            background: #fff8f0;
            transform: translateY(-2px);
        }

        .file-input-button .icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .btn {
            background: linear-gradient(135deg, #ff914d 0%, #ff5e00 100%);
            color: white;
            padding: 18px 35px;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 145, 77, 0.4);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            font-size: 1rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 2px solid #b8dabc;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 2px solid #f1b0b7;
        }

        .password-requirements {
            background: #fff3cd;
            border: 2px solid #ffd4a3;
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #856404;
        }

        .password-requirements ul {
            margin: 10px 0 0 20px;
        }

        .password-requirements li {
            margin-bottom: 5px;
        }

        .success-message {
            text-align: center;
            color: #155724;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .header {
                padding: 30px 25px;
            }
            
            .profile-picture,
            .default-avatar {
                width: 120px;
                height: 120px;
            }
            
            .default-avatar {
                font-size: 2.5rem;
            }

            .back-btn {
                margin: 10px;
            }
        }
    </style>
    <?php echo $redirect_script; ?>
</head>
<body>
    <a href="admin_dashboard.php" class="back-btn">
        ← Back to Dashboard
    </a>

    <div class="container">
        <div class="header">
            <h1>Admin Profile Settings</h1>
            <p>Manage your password and profile picture</p>
        </div>
        
        <div class="form-container">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                    <?php if ($redirect_script): ?>
                        <div class="success-message" style="margin-top: 10px;">
                            Redirecting to dashboard in 2 seconds...
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="admin-info">
                <h3>👤 Admin Information</h3>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($admin['username']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($admin['city'] . ' - Barangay ' . $admin['barangay_number']); ?></p>
                <p><strong>Last Login:</strong> <?php echo $admin['last_login'] ? date('F d, Y h:i A', strtotime($admin['last_login'])) : 'Never'; ?></p>
                <p><strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($admin['created_at'])); ?></p>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="profile-picture-section">
                    <h3 style="color: #ff5e00; margin-bottom: 20px;">📷 Profile Picture</h3>
                    
                    <?php if (!empty($admin['profile_picture']) && file_exists($admin['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Admin Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <div class="default-avatar">
                            <?php echo strtoupper(substr($admin['username'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <div class="file-input-wrapper">
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                            <label for="profile_picture" class="file-input-button">
                                <span class="icon">📷</span>
                                <div><strong>Update Profile Picture</strong></div>
                                <small style="color: #6c757d; margin-top: 8px; display: block;">JPG, PNG, GIF (Max 5MB)</small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>🔐 Change Password</h3>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password (optional)">
                        <div class="password-requirements">
                            <strong>Password Requirements:</strong>
                            <ul>
                                <li>At least 8 characters long</li>
                                <li>Mix of uppercase and lowercase letters recommended</li>
                                <li>Include numbers and special characters for better security</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                    </div>
                </div>
                
                <div class="section">
                    <h3>🔒 Verify Identity</h3>
                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required placeholder="Enter your current password to confirm changes">
                        <small style="color: #dc3545; margin-top: 8px; display: block;">Required to make any changes</small>
                    </div>
                </div>
                
                <button type="submit" class="btn">Update Profile</button>
            </form>
        </div>
    </div>

    <script>
        // Preview profile picture before upload
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const existingImg = document.querySelector('.profile-picture');
                    const existingAvatar = document.querySelector('.default-avatar');
                    
                    if (existingImg) {
                        existingImg.src = e.target.result;
                    } else if (existingAvatar) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'profile-picture';
                        img.alt = 'Profile Picture Preview';
                        existingAvatar.parentNode.replaceChild(img, existingAvatar);
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function(e) {
            const password = e.target.value;
            let strengthDiv = document.querySelector('.password-strength');
            
            if (!strengthDiv) {
                strengthDiv = document.createElement('div');
                strengthDiv.className = 'password-strength';
                e.target.parentNode.appendChild(strengthDiv);
            }
            
            let strength = 0;
            let message = '';
            
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    message = '🔴 Weak password';
                    break;
                case 2:
                case 3:
                    message = '🟡 Medium password';
                    break;
                case 4:
                case 5:
                    message = '🟢 Strong password';
                    break;
            }
            
            strengthDiv.textContent = password ? message : '';
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const profilePicture = document.getElementById('profile_picture').files[0];
            
            if (!currentPassword) {
                e.preventDefault();
                alert('Current password is required to make any changes.');
                return;
            }
            
            if (!newPassword && !confirmPassword && !profilePicture) {
                e.preventDefault();
                alert('Please make at least one change (password or profile picture).');
                return;
            }
            
            if ((newPassword || confirmPassword) && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match.');
                return;
            }
            
            if (newPassword && newPassword.length < 8) {
                e.preventDefault();
                alert('New password must be at least 8 characters long.');
                return;
            }
        });
    </script>
</body>
</html>