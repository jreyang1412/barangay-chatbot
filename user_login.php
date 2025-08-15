<?php
session_start();
require_once 'config.php';

// Check if user is already logged in via remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE remember_token = ? AND remember_token_expiry > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: user_dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        // Silent fail, continue to login page
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    if (!$email || !$password) {
        $message = "Please enter both email and password.";
        $alertType = "alert-error";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];

                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, remember_token_expiry = ? WHERE id = ?");
                    $stmt->execute([$token, $expiry, $user['id']]);
                    
                    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
                }

                header("Location: user_dashboard.php");
                exit();
            } else {
                $message = "Invalid email or password.";
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
    <title>User Login - Help Desk</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated background */
        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #fff5f0 0%, #ffe8db 25%, #fff5f0 50%, #ffeee6 75%, #fff5f0 100%);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
            z-index: -2;
        }
        
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: float 20s infinite ease-in-out;
            z-index: -1;
        }
        
        .orb1 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #ff6b35 0%, transparent 70%);
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }
        
        .orb2 {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, #ff9558 0%, transparent 70%);
            bottom: -125px;
            left: -125px;
            animation-delay: 5s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: 2px solid rgba(255, 107, 53, 0.2);
            border-radius: 10px;
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .back-link:hover {
            background: rgba(255, 107, 53, 0.05);
            border-color: #ff6b35;
            transform: translateX(-3px);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 40px 35px;
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 107, 53, 0.1);
            margin-top: 60px;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35, #ff9558, #ffa574);
            background-size: 300% 300%;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #ff6b35, #ff9558);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.25);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 6px;
        }
        
        .title-gradient {
            background: linear-gradient(135deg, #ff6b35, #ff9558);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            color: #636e72;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3436;
            font-size: 0.9rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            padding-right: 45px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 0.95rem;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }
        
        input::placeholder {
            color: #b2bec3;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #636e72;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #ff6b35;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.85rem;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #ff6b35;
            cursor: pointer;
        }
        
        .remember-me label {
            margin: 0;
            font-weight: 500;
            color: #636e72;
            cursor: pointer;
        }
        
        .forgot-link {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ff6b35, #ff9558);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0 20px;
            color: #b2bec3;
            font-size: 0.85rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ecf0f1;
        }
        
        .divider span {
            padding: 0 15px;
        }
        
        .register-link {
            text-align: center;
            color: #636e72;
            font-size: 0.9rem;
        }
        
        .register-link a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-error {
            background: #fff5f3;
            color: #c0392b;
            border: 1px solid #ffddda;
        }
        
        .alert-success {
            background: #f0fff4;
            color: #27ae60;
            border: 1px solid #d4edda;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
                padding-top: 70px;
                min-height: 100vh;
                align-items: flex-start;
            }
            
            .back-link {
                top: 15px;
                left: 15px;
                padding: 8px 15px;
                font-size: 0.85rem;
                gap: 5px;
            }
            
            .back-link span:last-child {
                display: none;
            }
            
            .login-container {
                padding: 30px 25px;
                border-radius: 15px;
                margin-top: 20px;
                max-height: calc(100vh - 100px);
                overflow-y: auto;
            }
            
            .header {
                margin-bottom: 25px;
            }
            
            .logo {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }
            
            .title {
                font-size: 1.5rem;
            }
            
            .subtitle {
                font-size: 0.9rem;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            label {
                font-size: 0.85rem;
                margin-bottom: 6px;
            }
            
            input[type="email"],
            input[type="password"],
            input[type="text"] {
                padding: 12px 14px;
                padding-right: 40px;
                font-size: 0.9rem;
            }
            
            .password-toggle {
                font-size: 1.1rem;
                right: 12px;
            }
            
            .remember-forgot {
                font-size: 0.8rem;
                margin-bottom: 20px;
            }
            
            .remember-me input[type="checkbox"] {
                width: 16px;
                height: 16px;
            }
            
            .login-btn {
                padding: 12px;
                font-size: 0.9rem;
            }
            
            .divider {
                margin: 20px 0 15px;
                font-size: 0.8rem;
            }
            
            .register-link {
                font-size: 0.85rem;
            }
            
            .alert {
                padding: 12px 14px;
                font-size: 0.85rem;
                margin-bottom: 20px;
            }
            
            .orb1, .orb2 {
                width: 180px;
                height: 180px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
                padding-top: 60px;
            }
            
            .back-link {
                top: 10px;
                left: 10px;
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            .login-container {
                padding: 25px 20px;
                margin-top: 15px;
            }
            
            .logo {
                width: 55px;
                height: 55px;
                font-size: 1.6rem;
                margin-bottom: 12px;
            }
            
            .title {
                font-size: 1.4rem;
                margin-bottom: 5px;
            }
            
            .subtitle {
                font-size: 0.85rem;
            }
            
            .form-group {
                margin-bottom: 16px;
            }
            
            input[type="email"],
            input[type="password"],
            input[type="text"] {
                padding: 11px 12px;
                padding-right: 38px;
            }
            
            .remember-forgot {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }
            
            .forgot-link {
                font-size: 0.8rem;
            }
            
            .orb1, .orb2 {
                width: 150px;
                height: 150px;
            }
        }
        
        @media (max-height: 700px) and (max-width: 480px) {
            body {
                padding-top: 50px;
            }
            
            .login-container {
                margin-top: 10px;
                padding: 20px 18px;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            .logo {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin-bottom: 10px;
            }
            
            .title {
                font-size: 1.3rem;
            }
            
            .form-group {
                margin-bottom: 14px;
            }
            
            .divider {
                margin: 18px 0 14px;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    
    <a href="userside.php" class="back-link">
        <span>←</span>
        <span>Back to Home</span>
    </a>
    
    <div class="login-container">
        <div class="header">
            <div class="logo">👤</div>
            <h1 class="title">Welcome <span class="title-gradient">Back</span></h1>
            <p class="subtitle">Sign in to access help desk services</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo $alertType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="Enter your email">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                    <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                        <span id="toggleIcon">Show</span>
                    </button>
                </div>
            </div>
            
            <button type="submit" name="login" class="login-btn" id="loginBtn">
                Sign In
            </button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="register-link">
            <p>Don't have an account? <a href="user_register.php">Create one here</a></p>
        </div>
    </div>

    <script>
        // Auto-focus email field
        document.getElementById('email').focus();
        
        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = 'Show';
            }
        });
        
        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const originalText = btn.textContent;
            
            btn.innerHTML = '<span style="display: inline-block; animation: spin 1s linear infinite;">⏳</span> Signing In...';
            btn.disabled = true;
            
            // Re-enable button after 5 seconds (fallback for if page doesn't redirect)
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 5000);
        });
        
        // Smooth entrance animation
        window.addEventListener('load', function() {
            const container = document.querySelector('.login-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
        
        // Add loading spinner animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        
        // Remember me persistence (visual feedback)
        const rememberCheckbox = document.getElementById('remember');
        if (localStorage.getItem('rememberEmail') && localStorage.getItem('rememberEmail') === 'true') {
            rememberCheckbox.checked = true;
        }
        
        rememberCheckbox.addEventListener('change', function() {
            if (this.checked) {
                localStorage.setItem('rememberEmail', 'true');
            } else {
                localStorage.removeItem('rememberEmail');
            }
        });
    </script>
</body>
</html>