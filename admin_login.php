<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $message = "Please enter both username and password.";
        $alertType = "alert-error";
    } else {
        try {
            // Fetch admin details including city and barangay_number
            $stmt = $pdo->prepare("SELECT id, username, password, city, barangay_number FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                // Store all necessary session data
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_city'] = $admin['city'];
                $_SESSION['admin_barangay'] = $admin['barangay_number'];
                $_SESSION['user_type'] = 'admin';

                // Update last_login timestamp
                $update_stmt = $pdo->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->execute([$admin['id']]);

                header("Location: admin_dashboard.php");
                exit();
            } else {
                $message = "Invalid username or password.";
                $alertType = "alert-error";
            }
        } catch (PDOException $e) {
            $message = "Login failed. Please try again.";
            $alertType = "alert-error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Help Desk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-orange: #FF6B35;
            --dark-orange: #E85A2C;
            --light-orange: #FFE5DB;
            --white: #FFFFFF;
            --gray: #6B7280;
            --light-gray: #F3F4F6;
            --success: #10B981;
            --error: #EF4444;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, var(--light-orange) 0%, var(--white) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            background: var(--white);
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }
        
        .back-button:hover {
            background: var(--light-gray);
            color: var(--primary-orange);
            transform: translateX(-4px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.2);
        }
        
        .login-container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(255, 107, 53, 0.08);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-orange), var(--dark-orange));
        }
        
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: var(--white);
        }
        
        .admin-badge {
            background: var(--light-orange);
            color: var(--primary-orange);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .title {
            font-size: 28px;
            color: #1F2937;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .subtitle {
            color: var(--gray);
            font-size: 14px;
        }
        
        .security-note {
            background: var(--light-gray);
            color: var(--gray);
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 3px solid var(--primary-orange);
        }
        
        .location-info {
            background: rgba(255, 107, 53, 0.05);
            color: var(--dark-orange);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1F2937;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid transparent;
            background: var(--light-gray);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }
        
        input:focus {
            border-color: var(--primary-orange);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }
        
        /* Password field with toggle */
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        
        .password-wrapper input {
            width: 100%;
            padding-right: 70px;
        }
        
        .password-toggle {
            position: absolute;
            right: 2px;
            top: 2px;
            bottom: 2px;
            background: var(--primary-orange);
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 55px;
            user-select: none;
        }
        
        .password-toggle:hover {
            background: var(--dark-orange);
        }
        
        .password-toggle:active {
            transform: scale(0.95);
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.25);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .register-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--light-gray);
        }
        
        .register-link a {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            text-decoration: underline;
            color: var(--dark-orange);
        }
        
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert-success {
            background: #D1FAE5;
            color: var(--success);
        }
        
        .alert-error {
            background: #FEE2E2;
            color: var(--error);
        }
        
        @media (max-width: 640px) {
            .back-button {
                top: 10px;
                left: 10px;
                padding: 6px 12px;
                font-size: 13px;
            }
            
            .login-container {
                padding: 32px 24px;
                border-radius: 20px;
            }
            
            .title {
                font-size: 24px;
            }
            
            .logo {
                width: 56px;
                height: 56px;
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="adminside.php" class="back-button">
            <span>←</span>
            <span>Back to Admin</span>
        </a>
        <div class="header">
            <div class="logo">⚙️</div>
            <div class="admin-badge">Administrator Access</div>
            <h1 class="title">Admin Portal</h1>
            <p class="subtitle">Secure login for barangay administrators</p>
        </div>

        <div class="security-note">
            <strong>🔒 Secure Access:</strong> This portal is restricted to authorized barangay administrators only.
        </div>

        <div class="location-info">
            <strong>📍 Location-Based Access:</strong> You will only have access to requests from your assigned city and barangay.
        </div>

        <!-- Alert message placeholder for PHP -->
        <div id="alert-placeholder" style="display: none;"></div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Admin Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required 
                       placeholder="Enter your admin username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Enter your password">
                    <button type="button" 
                            id="password-toggle"
                            class="password-toggle">
                        SHOW
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="login-btn">Access Admin Panel</button>
        </form>

        <div class="register-link">
            <p>Don't have admin account? <a href="admin_register.php">Create one here.</a></p>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');
            
            // Ensure elements exist
            if (!passwordInput || !passwordToggle) {
                console.error('Password input or toggle button not found');
                return;
            }
            
            // Add click event listener to toggle button
            passwordToggle.addEventListener('click', function(e) {
                // Prevent form submission
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle password visibility
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    passwordToggle.textContent = 'HIDE';
                    passwordToggle.setAttribute('aria-label', 'Hide password');
                } else {
                    passwordInput.type = 'password';
                    passwordToggle.textContent = 'SHOW';
                    passwordToggle.setAttribute('aria-label', 'Show password');
                }
                
                // Keep focus on the password input
                passwordInput.focus();
            });
            
            // Add keyboard support (Enter/Space on toggle button)
            passwordToggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    passwordToggle.click();
                }
            });
            
            // Auto-focus on username field when page loads
            document.getElementById('username').focus();
            
            // Page load animation
            const container = document.querySelector('.login-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });

        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const loginBtn = document.querySelector('.login-btn');
            
            if (form && loginBtn) {
                form.addEventListener('submit', function(e) {
                    // Show loading state
                    loginBtn.textContent = 'Authenticating...';
                    loginBtn.disabled = true;
                    
                    // Re-enable button after 3 seconds (in case of error)
                    setTimeout(() => {
                        loginBtn.textContent = 'Access Admin Panel';
                        loginBtn.disabled = false;
                    }, 3000);
                });
                
                // Multiple login attempts warning
                let attempts = parseInt(localStorage.getItem('loginAttempts') || '0');
                
                form.addEventListener('submit', function(e) {
                    attempts++;
                    localStorage.setItem('loginAttempts', attempts.toString());
                    
                    if (attempts > 3) {
                        const confirmation = confirm('Multiple login attempts detected. Are you an authorized administrator?');
                        if (!confirmation) {
                            e.preventDefault();
                            loginBtn.textContent = 'Access Admin Panel';
                            loginBtn.disabled = false;
                            return false;
                        }
                    }
                });
            }
        });

        // Clear login attempts on successful login (you can call this from PHP)
        function clearLoginAttempts() {
            localStorage.removeItem('loginAttempts');
        }

        // Function to show alerts (for PHP integration)
        function showAlert(message, type) {
            const alertPlaceholder = document.getElementById('alert-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            
            alertPlaceholder.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;
            alertPlaceholder.style.display = 'block';
        }
    </script>
</body>
</html>