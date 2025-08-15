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

// Check if user is logged in (assuming user ID is stored in session)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$redirect_script = '';

// First, let's add the profile_picture column if it doesn't exist
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) NULL");
} catch(PDOException $e) {
    // Column might already exist, ignore error
}

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("User not found");
    }
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validate current password
    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } else {
        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($mobile_number) || empty($email)) {
            $error = "First name, last name, mobile number, and email are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            try {
                // Check if email is already taken by another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                if ($stmt->fetch()) {
                    $error = "Email is already taken by another user.";
                } else {
                    $update_fields = [
                        'first_name' => $first_name,
                        'middle_name' => $middle_name ?: null,
                        'last_name' => $last_name,
                        'mobile_number' => $mobile_number,
                        'email' => $email,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Handle profile picture upload
                    $picture_uploaded = false;
                    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = 'uploads/profile_pictures/';
                        
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
                                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
                                $upload_path = $upload_dir . $new_filename;
                                
                                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                                    // Delete old profile picture if exists
                                    if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                                        unlink($user['profile_picture']);
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
                    
                    // Update user data if no errors
                    if (empty($error)) {
                        $set_clause = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($update_fields)));
                        $sql = "UPDATE users SET $set_clause WHERE id = :user_id";
                        
                        $stmt = $pdo->prepare($sql);
                        $update_fields['user_id'] = $user_id;
                        $stmt->execute($update_fields);
                        
                        $changes = ["profile information"];
                        if ($picture_uploaded) $changes[] = "profile picture";
                        
                        $message = "Successfully updated: " . implode(" and ", $changes) . "!";
                        
                        // Set redirect script to go back to dashboard after 2 seconds
                        $redirect_script = '<script>
                            setTimeout(function() {
                                window.location.href = "user_dashboard.php";
                            }, 2000);
                        </script>';
                        
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                }
            } catch(PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
            content: '👤';
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

        .form-group {
            margin-bottom: 25px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="email"],
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

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="file"]:focus {
            outline: none;
            border-color: #ff914d;
            box-shadow: 0 0 0 3px rgba(255, 145, 77, 0.1);
        }

        .password-section {
            background: #fff3e0;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #e74c3c;
        }

        .password-section h3 {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .success-message {
            text-align: center;
            color: #155724;
            font-weight: 600;
        }

        .required {
            color: #e74c3c;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
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
    <a href="user_dashboard.php" class="back-btn">
        ← Back to Dashboard
    </a>

    <div class="container">
        <div class="header">
            <h1>Edit Profile</h1>
            <p>Update your personal information</p>
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
            
            <form method="POST" enctype="multipart/form-data">
                <div class="profile-picture-section">
                    <h3 style="color: #ff5e00; margin-bottom: 20px;">📷 Profile Picture</h3>
                    
                    <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <div class="default-avatar">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
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
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="mobile_number">Mobile Number <span class="required">*</span></label>
                    <input type="text" id="mobile_number" name="mobile_number" value="<?php echo htmlspecialchars($user['mobile_number']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="password-section">
                    <h3>🔒 Confirm Changes</h3>
                    <div class="form-group">
                        <label for="current_password">Enter Current Password to Save Changes <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" required placeholder="Your current password">
                        <small style="color: #e74c3c; margin-top: 8px; display: block;">Required to make any changes</small>
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

        // Form validation with orange theme
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['first_name', 'last_name', 'mobile_number', 'email', 'current_password'];
            let isValid = true;
            
            requiredFields.forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                    field.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.1)';
                } else {
                    field.style.borderColor = '#ffd4a3';
                    field.style.boxShadow = 'none';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        // Enhanced input validation
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = '#e74c3c';
                this.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.1)';
            } else {
                this.style.borderColor = '#ffd4a3';
                this.style.boxShadow = 'none';
            }
        });

        // Mobile number formatting
        document.getElementById('mobile_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            if (value.length > 2 && !value.startsWith('09')) {
                // Auto-format Philippine mobile numbers
                if (value.length === 10 && value.startsWith('9')) {
                    value = '0' + value;
                }
            }
            e.target.value = value;
        });
    </script>
</body>
</html>