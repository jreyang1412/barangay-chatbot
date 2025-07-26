<?php
session_start();
require_once 'config.php';

$message = '';
$alertType = '';

if (isset($_POST['register'])) {
    $city = trim($_POST['city']);
    $barangayNumber = trim($_POST['barangay_number']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($city) || empty($barangayNumber) || empty($username) || empty($password) || empty($confirmPassword)) {
        $message = "Please fill in all fields.";
        $alertType = "alert-error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $alertType = "alert-error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $alertType = "alert-error";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $message = "Username already exists.";
                $alertType = "alert-error";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (city, barangay_number, username, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$city, $barangayNumber, $username, $hashedPassword]);

                $message = "Registration successful. You can now log in.";
                $alertType = "alert-success";
            }
        } catch (PDOException $e) {
            $message = "Registration failed. Please try again.";
            $alertType = "alert-error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Register - Help Desk</title>
    <style>
        /* Same styles as login page */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        .register-container h2 {
            text-align: center;
            color: #2980b9;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 16px;
        }
        input:focus {
            outline: none;
            border-color: #3498db;
        }
        .register-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
        }
        .register-btn:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .alert {
            padding: 12px;
            border-radius: 10px;
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
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            text-decoration: none;
            color: #2980b9;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Register Admin Account</h2>

    <?php if (!empty($message)): ?>
        <div class="alert <?= $alertType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" name="city" id="city" required placeholder="e.g. Manila" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="barangay_number">Barangay Number</label>
            <input type="text" name="barangay_number" id="barangay_number" required placeholder="e.g. 178" value="<?= htmlspecialchars($_POST['barangay_number'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="username">Admin Username</label>
            <input type="text" name="username" id="username" required placeholder="Choose a username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required placeholder="Minimum 8 characters">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repeat password">
        </div>

        <button type="submit" name="register" class="register-btn">Create Admin Account</button>
    </form>

    <div class="back-link">
        <a href="admin_login.php">← Back to Login</a>
    </div>
</div>

</body>
</html>
