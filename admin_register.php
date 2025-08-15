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

// Barangay data for each city (sample data - expand as needed)
$barangayData = [
    'Quezon City' => ['Fairview', 'Pasong Tamo', 'Novaliches', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10'],
    'Makati' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Bangkal', 'Bel-Air', 'Carmona', 'Cembo', 'Comembo', 'Dasmariñas'],
    'Manila' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10'],
    'Pasig' => ['Bagong Ilog', 'Bagong Katipunan', 'Bambang', 'Buting', 'Caniogan', 'Kalawaan', 'Kapasigan', 'Kapitolyo'],
    'Taguig' => ['Bagumbayan', 'Bambang', 'Calzada', 'Central Bicutan', 'Central Signal Village', 'Fort Bonifacio', 'Hagonoy', 'Ibayo-Tipas'],
    'Parañaque' => ['Baclaran', 'BF Homes', 'Don Bosco', 'Don Galo', 'La Huerta', 'Marcelo Green', 'Merville', 'Moonwalk'],
    'Caloocan' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8'],
    'Las Piñas' => ['Almanza Uno', 'Almanza Dos', 'Daniel Fajardo', 'Elias Aldana', 'Ilaya', 'Manuyo Uno', 'Manuyo Dos', 'Pamplona Uno'],
    'Malabon' => ['Acacia', 'Baritan', 'Bayan-bayanan', 'Catmon', 'Concepcion', 'Dampalit', 'Flores', 'Hulong Duhat'],
    'Mandaluyong' => ['Addition Hills', 'Bagong Silang', 'Barangka Drive', 'Barangka Ibaba', 'Barangka Ilaya', 'Barangka Itaas', 'Buayang Bato'],
    'Marikina' => ['Barangka', 'Calumpang', 'Concepcion Uno', 'Concepcion Dos', 'Fortune', 'Industrial Valley', 'Jesus De La Peña'],
    'Muntinlupa' => ['Alabang', 'Ayala Alabang', 'Bayanan', 'Buli', 'Cupang', 'Poblacion', 'Putatan', 'Sucat', 'Tunasan'],
    'Navotas' => ['Bagumbayan North', 'Bagumbayan South', 'Bangculasi', 'Daanghari', 'Navotas East', 'Navotas West', 'North Bay Boulevard North'],
    'Pasay' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8'],
    'San Juan' => ['Addition Hills', 'Balong-Bato', 'Batis', 'Corazon de Jesus', 'Ermitaño', 'Greenhills', 'Isabelita', 'Kabayanan'],
    'Valenzuela' => ['Arkong Bato', 'Bagbaguin', 'Balangkas', 'Bignay', 'Bisig', 'Canumay East', 'Canumay West', 'Coloong'],
    'Pateros' => ['Aguho', 'Magtanggol', 'Martires del 96', 'Poblacion', 'San Pedro', 'San Roque', 'Santa Ana', 'Santo Rosario-Kanluran']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Register - Help Desk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fff5f0 0%, #ffe0cc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 45px;
            box-shadow: 0 20px 40px rgba(255, 117, 0, 0.15);
            width: 100%;
            max-width: 500px;
            border: 2px solid #fff3ed;
        }

        .register-container h2 {
            text-align: center;
            color: #ff5722;
            margin-bottom: 35px;
            font-size: 28px;
            font-weight: 700;
            position: relative;
        }

        .register-container h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #ff7043, #ff5722);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            color: #424242;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select {
            width: 100%;
            padding: 14px;
            border: 2px solid #ffe0cc;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fffbf8;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ff7043;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 112, 67, 0.1);
        }

        .input-container {
            position: relative;
        }

        /* Password toggle button */
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            color: #ff7043;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #ff5722;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        /* Custom dropdown styling */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ff7043' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 18px;
            padding-right: 45px;
            cursor: pointer;
        }

        input[list] {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ff7043' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 18px;
            padding-right: 45px;
        }

        select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .register-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff7043, #ff5722);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 17px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(255, 87, 34, 0.3);
            background: linear-gradient(135deg, #ff5722, #ff7043);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert::before {
            content: '';
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: contain;
        }

        .alert-success {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .alert-success::before {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232e7d32' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M22 11.08V12a10 10 0 1 1-5.93-9.14'%3e%3c/path%3e%3cpolyline points='22 4 12 14.01 9 11.01'%3e%3c/polyline%3e%3c/svg%3e");
        }

        .alert-error {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .alert-error::before {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23c62828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3ccircle cx='12' cy='12' r='10'%3e%3c/circle%3e%3cline x1='12' y1='8' x2='12' y2='12'%3e%3c/line%3e%3cline x1='12' y1='16' x2='12.01' y2='16'%3e%3c/line%3e%3c/svg%3e");
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            text-decoration: none;
            color: #ff5722;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-link a:hover {
            color: #ff7043;
            transform: translateX(-3px);
        }

        /* Loading spinner for barangay dropdown */
        .loading-spinner {
            display: none;
            position: absolute;
            right: 40px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #ffe0cc;
            border-top-color: #ff7043;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
            }
            
            .register-container h2 {
                font-size: 24px;
            }
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
            <label for="barangay_number">Barangay</label>
            <div class="input-container">
                <select name="barangay_number" id="barangay_number" required disabled>
                    <option value="">Select a city first</option>
                </select>
                <span class="loading-spinner"></span>
            </div> 
        </div>

        <div class="form-group">
            <label for="username">Admin Username</label>
            <input type="text" name="username" id="username" required 
                   placeholder="Choose a username" 
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-container">
                <input type="password" name="password" id="password" required 
                       placeholder="Minimum 8 characters">
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="eye-closed" style="display:none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <div class="input-container">
                <input type="password" name="confirm_password" id="confirm_password" required 
                       placeholder="Repeat password">
                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="eye-closed" style="display:none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" name="register" class="register-btn">Create Admin Account</button>
    </form>

    <div class="back-link">
        <a href="admin_login.php">← Back to Login</a>
    </div>
</div>

<script>
    // Barangay data from PHP
    const barangayData = <?= json_encode($barangayData) ?>;
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const cityInput = document.getElementById('city');
        const barangaySelect = document.getElementById('barangay_number');
        const loadingSpinner = document.querySelector('.loading-spinner');
        
        // Handle city selection
        cityInput.addEventListener('input', function() {
            const selectedCity = this.value;
            
            // Check if it's a valid city
            if (barangayData.hasOwnProperty(selectedCity)) {
                // Show loading spinner
                loadingSpinner.style.display = 'block';
                
                // Simulate loading delay for better UX
                setTimeout(() => {
                    // Clear current options
                    barangaySelect.innerHTML = '<option value="">Select a barangay</option>';
                    
                    // Add barangays for selected city
                    const barangays = barangayData[selectedCity];
                    barangays.forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay;
                        option.textContent = barangay;
                        barangaySelect.appendChild(option);
                    });
                    
                    // Enable the select
                    barangaySelect.disabled = false;
                    
                    // Hide loading spinner
                    loadingSpinner.style.display = 'none';
                }, 300);
            } else {
                // Disable and reset barangay select
                barangaySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select a city first</option>';
            }
        });
        
        // Check if city is already selected (e.g., after form submission with error)
        if (cityInput.value && barangayData.hasOwnProperty(cityInput.value)) {
            cityInput.dispatchEvent(new Event('input'));
            
            // Restore selected barangay if exists
            <?php if (!empty($_POST['barangay_number'])): ?>
            setTimeout(() => {
                barangaySelect.value = '<?= htmlspecialchars($_POST['barangay_number']) ?>';
            }, 400);
            <?php endif; ?>
        }
    });
    
    // Toggle password visibility
    function togglePassword(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const toggleButton = passwordField.nextElementSibling;
        const eyeOpen = toggleButton.querySelector('.eye-open');
        const eyeClosed = toggleButton.querySelector('.eye-closed');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
        } else {
            passwordField.type = 'password';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        }
    }
    
    // Password strength indicator (optional enhancement)
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthIndicator = document.createElement('div');
        
        if (password.length < 8) {
            this.style.borderColor = '#ffcdd2';
        } else if (password.length < 12) {
            this.style.borderColor = '#ffe0b2';
        } else {
            this.style.borderColor = '#c8e6c9';
        }
    });
    
    // Confirm password validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            this.style.borderColor = '#ffcdd2';
        } else if (confirmPassword && password === confirmPassword) {
            this.style.borderColor = '#c8e6c9';
        }
    });
</script>

</body>
</html>