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
            // Update this table name to match your actual admin table name
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['user_type'] = 'admin';

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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 50px 40px;
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
            background: linear-gradient(90deg, #e74c3c, #c0392b, #e67e22, #f39c12);
            background-size: 400% 400%;
            animation: gradientShift 3s ease infinite;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .title {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 1rem;
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
        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        input:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
            transform: translateY(-2px);
        }
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
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
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        .login-btn:active {
            transform: translateY(-1px);
        }
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #ecf0f1;
        }
        .register-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .register-link a:hover {
            text-decoration: underline;
            color: #c0392b;
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            color: #c0392b;
        }
        .admin-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .security-note {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 13px;
            border: 1px solid #ffeaa7;
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 25px;
            }
            .title {
                font-size: 1.8rem;
            }
            .logo {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
<a href="index.php" class="back-link">← Back to Home</a>

<div class="login-container">
    <div class="header">
        <div class="logo">⚙️</div>
        <div class="admin-badge">Administrator Access</div>
        <h1 class="title">Admin Portal</h1>
        <p class="subtitle">Secure login for help desk administrators</p>
    </div>

    <div class="security-note">
        <strong>🔒 Secure Access:</strong> This portal is restricted to authorized barangay administrators only.
    </div>

    <?php if (isset($message)): ?>
        <div class="alert <?php echo $alertType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Admin Username</label>
            <input type="text" id="username" name="username" required 
                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                   placeholder="Enter your admin username">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Enter your password">
        </div>

        <button type="submit" name="login" class="login-btn">Access Admin Panel</button>
    </form>

    <div class="register-link">
        <p>Need an admin account? <a href="admin_register.php">Register here</a></p>
    </div>
</div>

<script>
    document.getElementById('username').focus();

    document.querySelector('form').addEventListener('submit', function () {
        const btn = document.querySelector('.login-btn');
        btn.textContent = 'Authenticating...';
        btn.disabled = true;
    });

    window.addEventListener('load', function () {
        const container = document.querySelector('.login-container');
        container.style.opacity = '0';
        container.style.transform = 'translateY(30px)';

        setTimeout(() => {
            container.style.transition = 'all 0.6s ease';
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        }, 100);
    });

    let attempts = 0;
    document.querySelector('form').addEventListener('submit', function (e) {
        attempts++;
        if (attempts > 3) {
            const confirmation = confirm('Multiple login attempts detected. Are you an authorized administrator?');
            if (!confirmation) {
                e.preventDefault();
                return false;
            }
        }
    });
</script>
</body>
</html>