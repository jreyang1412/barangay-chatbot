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

// Metro Manila cities array
$metroManilaCities = [
    'Caloocan',
    'Las Piñas',
    'Makati',
    'Malabon',
    'Mandaluyong',
    'Manila',
    'Marikina',
    'Muntinlupa',
    'Navotas',
    'Parañaque',
    'Pasay',
    'Pasig',
    'Quezon City',
    'San Juan',
    'Taguig',
    'Valenzuela',
    'Pateros'
];
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
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
        }
        .input-container {
            position: relative;
        }
        input[list] {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }
        select option[disabled] {
            color: #999;
            font-style: italic;
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
    <script>
        // Add validation for barangay number
        document.addEventListener('DOMContentLoaded', function() {
            const barangayInput = document.getElementById('barangay_number');
            const cityInput = document.getElementById('city');
            
            // Validate barangay number input
            barangayInput.addEventListener('input', function(e) {
                let value = e.target.value;
                // Remove non-numeric characters
                value = value.replace(/[^0-9]/g, '');
                // Limit to reasonable range
                if (value && parseInt(value) > 897) {
                    value = '897';
                }
                e.target.value = value;
            });

            // Add dropdown arrow click functionality
            function addDropdownBehavior(input) {
                input.addEventListener('click', function(e) {
                    // If clicking near the right edge (where arrow would be)
                    const rect = input.getBoundingClientRect();
                    const clickX = e.clientX - rect.left;
                    if (clickX > rect.width - 40) {
                        input.focus();
                        // Trigger dropdown by dispatching input event
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
            }
            
            addDropdownBehavior(cityInput);
            addDropdownBehavior(barangayInput);
        });
    </script>
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
            <div class="input-container">
                <input type="text" name="city" id="city" required 
                       placeholder="Type or select a city" 
                       value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
                       list="city-list" autocomplete="off">
                <datalist id="city-list">
                    <?php foreach ($metroManilaCities as $cityName): ?>
                        <option value="<?= htmlspecialchars($cityName) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>

        <div class="form-group">
            <label for="barangay_number">Barangay Number</label>
            <div class="input-container">
                <input type="text" name="barangay_number" id="barangay_number" required 
                       placeholder="Type or select barangay number" 
                       value="<?= htmlspecialchars($_POST['barangay_number'] ?? '') ?>"
                       list="barangay-list" autocomplete="off">
                <datalist id="barangay-list">
                    <?php for ($i = 1; $i <= 897; $i++): ?>
                        <option value="<?= $i ?>">
                    <?php endfor; ?>
                </datalist>
            </div>
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