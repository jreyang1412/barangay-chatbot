<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - Help Desk</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }
        
        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
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
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            color: #7f8c8d;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 20px;
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
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .required::after {
            content: ' *';
            color: #e74c3c;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .register-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .register-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
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
        
        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .register-container {
                padding: 30px 20px;
            }
            
            .title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="header">
            <div class="logo">👤</div>
            <h1 class="title">User Registration</h1>
            <p class="subtitle">Create your account to access help desk services</p>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $alertType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name" class="required">First Name</label>
                    <input type="text" id="first_name" name="first_name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="last_name" class="required">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="middle_name">Middle Name (Optional)</label>
                <input type="text" id="middle_name" name="middle_name" value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="mobile_number" class="required">Mobile Number</label>
                <input type="tel" id="mobile_number" name="mobile_number" required pattern="[0-9]{11}" placeholder="09123456789" value="<?php echo isset($_POST['mobile_number']) ? htmlspecialchars($_POST['mobile_number']) : ''; ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="city" class="required">City</label>
                    <select id="city" name="city" required>
                        <option value="">Select City</option>
                        <option value="Caloocan" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Caloocan') ? 'selected' : ''; ?>>Caloocan</option>
                        <option value="Las Piñas" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Las Piñas') ? 'selected' : ''; ?>>Las Piñas</option>
                        <option value="Makati" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Makati') ? 'selected' : ''; ?>>Makati</option>
                        <option value="Malabon" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Malabon') ? 'selected' : ''; ?>>Malabon</option>
                        <option value="Mandaluyong" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Mandaluyong') ? 'selected' : ''; ?>>Mandaluyong</option>
                        <option value="Manila" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Manila') ? 'selected' : ''; ?>>Manila</option>
                        <option value="Marikina" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Marikina') ? 'selected' : ''; ?>>Marikina</option>
                        <option value="Muntinlupa" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Muntinlupa') ? 'selected' : ''; ?>>Muntinlupa</option>
                        <option value="Navotas" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Navotas') ? 'selected' : ''; ?>>Navotas</option>
                        <option value="Parañaque" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Parañaque') ? 'selected' : ''; ?>>Parañaque</option>
                        <option value="Pasay" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Pasay') ? 'selected' : ''; ?>>Pasay</option>
                        <option value="Pasig" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Pasig') ? 'selected' : ''; ?>>Pasig</option>
                        <option value="Quezon City" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Quezon City') ? 'selected' : ''; ?>>Quezon City</option>
                        <option value="San Juan" <?php echo (isset($_POST['city']) && $_POST['city'] == 'San Juan') ? 'selected' : ''; ?>>San Juan</option>
                        <option value="Taguig" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Taguig') ? 'selected' : ''; ?>>Taguig</option>
                        <option value="Valenzuela" <?php echo (isset($_POST['city']) && $_POST['city'] == 'Valenzuela') ? 'selected' : ''; ?>>Valenzuela</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="barangay" class="required">Barangay</label>
                    <input type="text" id="barangay" name="barangay" required placeholder="e.g. Barangay 1" value="<?php echo isset($_POST['barangay']) ? htmlspecialchars($_POST['barangay']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email" class="required">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password" class="required">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="required">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <button type="submit" name="register" class="register-btn">Create Account</button>
        </form>
        
        <div class="login-link">
            <p>Already have an account? <a href="user_login.php">Login here</a></p>
        </div>
    </div>
    
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            const mobileNumber = document.getElementById('mobile_number').value;
            if (!/^09[0-9]{9}$/.test(mobileNumber)) {
                e.preventDefault();
                alert('Please enter a valid mobile number (11 digits starting with 09)');
                return false;
            }
        });
        
        // Auto-format mobile number
        document.getElementById('mobile_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>

<?php
require_once 'config.php';

if (isset($_POST['register'])) {
    $firstName = trim($_POST['first_name']);
    $middleName = trim($_POST['middle_name']);
    $lastName = trim($_POST['last_name']);
    $mobileNumber = trim($_POST['mobile_number']);
    $city = $_POST['city'];
    $barangay = trim($_POST['barangay']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($mobileNumber) || empty($city) || empty($barangay) || empty($email) || empty($password)) {
        $message = "Please fill in all required fields.";
        $alertType = "alert-error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $alertType = "alert-error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $alertType = "alert-error";
    } elseif (!preg_match('/^09[0-9]{9}$/', $mobileNumber)) {
        $message = "Please enter a valid mobile number (11 digits starting with 09).";
        $alertType = "alert-error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $alertType = "alert-error";
    } else {
        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $message = "An account with this email already exists.";
                $alertType = "alert-error";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, middle_name, last_name, mobile_number, city, barangay, email, password, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $firstName,
                    $middleName ?: null,
                    $lastName,
                    $mobileNumber,
                    $city,
                    $barangay,
                    $email,
                    $hashedPassword
                ]);
                
                $message = "Account created successfully! You can now login.";
                $alertType = "alert-success";
                
                // Clear form data
                $_POST = array();
                
                // Redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'user_login.php';
                    }, 2000);
                </script>";
            }
        } catch (PDOException $e) {
            $message = "Registration failed. Please try again.";
            $alertType = "alert-error";
        }
    }
}
?>