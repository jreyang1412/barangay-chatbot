<?php
require_once 'config.php';

if (isset($_POST['register'])) {
    $firstName = trim($_POST['first_name']);
    $middleName = trim($_POST['middle_name']);
    $lastName = trim($_POST['last_name']);
    $mobileNumber = trim($_POST['mobile_number']);
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
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
                
                // Insert user with 'basic' status
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, middle_name, last_name, mobile_number, city, barangay, email, password, is_active, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'basic', NOW(), NOW())
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
                
                $message = "Account created successfully! You can now login with basic access.";
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
            // For debugging (remove in production)
            // error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

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
    background: linear-gradient(135deg, #ff914d 0%, #ff5e00 100%);
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
    background: linear-gradient(90deg, #ff914d, #ff5e00, #ffb347, #ff7f50);
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
    color: #ff5e00;
}

.title {
    font-size: 2rem;
    color: #ff5e00;
    margin-bottom: 10px;
    font-weight: 700;
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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
    border-color: #ff914d;
    box-shadow: 0 0 0 3px rgba(255, 145, 77, 0.2);
}

.password-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #7f8c8d;
    font-size: 16px;
    padding: 4px;
    transition: color 0.3s ease;
    z-index: 10;
}

.password-toggle:hover {
    color: #ff914d;
}

.register-btn {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #ff914d, #ff5e00);
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
    color: #ff5e00;
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

.status-info {
    background: #ffe0b2;
    color: #e65100;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
    text-align: center;
}

select:disabled {
    background-color: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
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
        
        <div class="status-info">
            📝 New accounts start with <strong>Basic</strong> access level
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
                    <select id="barangay" name="barangay" required disabled>
                        <option value="">Select City First</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email" class="required">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password" class="required">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">Show
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="required">Confirm Password</label>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">Show
                    </button>
                </div>
            </div>
            
            <button type="submit" name="register" class="register-btn">Create Account</button>
        </form>
        
        <div class="login-link">
            <p>Already have an account? <a href="user_login.php">Login here</a></p>
        </div>
    </div>
    
    <script>
        // Barangay data for Metro Manila cities
        const barangayData = {
            "Caloocan": [
                "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8",
                "Barangay 9", "Barangay 10", "Bagbaguin", "Bagong Silang", "Bagumbong North", "Bagumbong South", 
                "Bankers Village", "Barrio Maypajo", "Bignay", "Bonifacio", "Camarin", "Deparo", "Grace Park East",
                "Grace Park West", "Kaybiga", "Kaunlaran", "Llano", "Maypajo", "Morning Breeze", "Novaliches North",
                "Novaliches South", "Sangandaan", "Tala", "Talipapa", "Tandang Sora", "Tex Ville"
            ],
            "Las Piñas": [
                "Almanza Dos", "Almanza Uno", "B.F. International Village", "Daniel Fajardo", "Elias Aldana",
                "Ilaya", "Manuyo Dos", "Manuyo Uno", "Pamplona Dos", "Pamplona Tres", "Pamplona Uno",
                "Pilar", "Pulang Lupa Dos", "Pulang Lupa Uno", "Talon Dos", "Talon Kuatro", "Talon Singko",
                "Talon Tres", "Talon Uno", "Zapote"
            ],
            "Makati": [
                "Bangkal", "Bel-Air", "Carmona", "Cembo", "Comembo", "Dasmariñas", "East Rembo", "Forbes Park", 
                "Guadalupe Nuevo", "Guadalupe Viejo", "Kasilawan", "La Paz", "Magallanes", "Olympia", "Palanan", 
                "Pembo", "Pinagkaisahan", "Pio del Pilar", "Pitogo", "Poblacion", "Post Proper Northside",
                "Post Proper Southside", "Rizal", "San Antonio", "San Isidro", "San Lorenzo", "Santa Cruz", 
                "Singkamas", "South Cembo", "Tejeros", "Urdaneta", "Valenzuela", "West Rembo"
            ],
            "Malabon": [
                "Acacia", "Baritan", "Bayan-bayanan", "Catmon", "Concepcion", "Dampalit", "Flores",
                "Hulong Duhat", "Ibaba", "Longos", "Maysilo", "Muzon", "Niugan", "Panghulo",
                "Potrero", "San Agustin", "Santolan", "Tañong", "Tinajeros", "Tonsuya", "Tugatog"
            ],
            "Mandaluyong": [
                "Addition Hills", "Bagong Silang", "Barangka Drive", "Barangka Ibaba", "Barangka Ilaya",
                "Barangka Itaas", "Buayang Bato", "Burol", "Daang Bakal", "Hagdang Bato Itaas",
                "Hagdang Bato Libis", "Harapin Ang Bukas", "Highway Hills", "Hulo", "Mabini-J. Rizal",
                "Malamig", "Mauway", "Namayan", "New Zañiga", "Old Zañiga", "Plainview", "Pleasant Hills",
                "Poblacion", "San Jose", "Vergara", "Wack-Wack Greenhills"
            ],
            "Manila": [
                "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6",
                "Barangay 7", "Barangay 8", "Barangay 9", "Barangay 10", "Binondo", "Ermita", "Intramuros",
                "Malate", "Paco", "Pandacan", "Port Area", "Quiapo", "Sampaloc", "San Andres", "San Miguel",
                "San Nicolas", "Santa Ana", "Santa Cruz", "Santa Mesa", "Tondo"
            ],
            "Marikina": [
                "Barangka", "Calumpang", "Concepcion Dos", "Concepcion Uno", "Fortune", "Industrial Valley Complex",
                "Jesus Dela Peña", "Malanday", "Marikina Heights", "Nangka", "Parang", "San Roque",
                "Santa Elena", "Santo Niño", "Tañong", "Tumana"
            ],
            "Muntinlupa": [
                "Alabang", "Ayala Alabang", "Buli", "Cupang", "Filinvest Corporate City", "New Alabang Village",
                "Poblacion", "Putatan", "Sucat", "Tunasan"
            ],
            "Navotas": [
                "Bagumbayan North", "Bagumbayan South", "Bangculasi", "Daanghari", "Navotas East",
                "Navotas West", "North Bay Boulevard North", "North Bay Boulevard South", "San Jose",
                "San Rafael Village", "San Roque", "Sipac-Almacen", "Tangos North", "Tangos South", "Tanza"
            ],
            "Parañaque": [
                "Baclaran", "B.F. Homes", "Don Bosco", "Don Galo", "La Huerta", "Marcelo Green Village",
                "Merville", "Moonwalk", "San Antonio", "San Dionisio", "San Isidro", "San Martin de Porres",
                "Santo Niño", "Sun Valley", "Tambo", "Vitalez"
            ],
            "Pasay": [
                "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6",
                "Barangay 7", "Barangay 8", "Barangay 9", "Barangay 10", "Barangay 76", "Barangay 183",
                "Barangay 201", "Malibay", "Maricaban", "San Isidro", "San Rafael", "San Roque", "Villamor"
            ],
            "Pasig": [
                "Bagong Ilog", "Bagong Katipunan", "Bambang", "Buting", "Caniogan", "Dela Paz",
                "Kalawaan", "Kapasigan", "Kapitolyo", "Malinao", "Manggahan", "Maybunga", "Oranbo",
                "Palatiw", "Pinagbuhatan", "Rosario", "Sagad", "San Antonio", "San Joaquin",
                "San Jose", "San Miguel", "San Nicolas", "Santa Cruz", "Santa Lucia", "Santa Rosa",
                "Santo Tomas", "Santolan", "Sumilang", "Ugong", "Wawa"
            ],
            "Quezon City": [
                "Alicia", "Amihan", "Apolonio Samson", "Aurora", "Baesa", "Bagbag", "Bagong Lipunan ng Crame",
                "Bagong Pag-asa", "Bagong Silangan", "Bagumbayan", "Bagumbuhay", "Balingasa", "Balintawak",
                "Batasan Hills", "Bayanihan", "Blue Ridge A", "Blue Ridge B", "Botocan", "Bungad",
                "Camp Aguinaldo", "Central", "Claro", "Commonwealth", "Culiat", "Damar", "Dawn",
                "Diliman", "Doña Aurora", "Doña Imelda", "Doña Josefa", "Don Manuel", "Duyan-Duyan",
                "East Kamias", "East Triangle", "Escopa", "Fairview", "Greater Lagro", "Gulod",
                "Holy Spirit", "Horseshoe", "Kaligayahan", "Kalusugan", "Kamuning", "Katipunan",
                "Kaunlaran", "Kristong Hari", "Krus na Ligas", "Laging Handa", "La Loma",
                "Libis", "Lourdes", "Loyola Heights", "Luzon Avenue", "Maharlika", "Malaya",
                "Mariana", "Mariblo", "Masambong", "Matandang Balara", "Milagrosa", "N.S. Amoranto",
                "Nayong Kanluran", "New Era", "North Fairview", "Novaliches Proper", "Old Balara",
                "Obrero", "Paang Bundok", "Pagkakaisa", "Paligsahan", "Paltok", "Pansol",
                "Paraiso", "Pasong Putik Proper", "Pasong Tamo", "Payatas", "Phil-Am", "Pinagkaisahan",
                "Pinyahan", "Project 6", "Project 7", "Project 8", "Quirino 2-A", "Quirino 2-B",
                "Quirino 2-C", "Quirino 3-A", "Ramón Magsaysay", "Roxas", "Sacred Heart",
                "Saint Ignatius", "Saint Peter", "Salvacion", "San Agustin", "San Antonio",
                "San Bartolome", "Sangandaan", "San Isidro Labrador", "San Jose", "San Martin de Porres",
                "San Roque", "Santa Cruz", "Santa Lucia", "Santa Monica", "Santa Teresita",
                "Santo Cristo", "Santo Domingo", "Santo Niño", "Santol", "Sienna",
                "Silangan", "South Triangle", "Tagumpay", "Talayan", "Tatalon", "Teachers Village East",
                "Teachers Village West", "Ugong Norte", "Unang Sigaw", "UP Campus", "UP Village",
                "Veterans Village", "Villa Maria Clara", "West Kamias", "West Triangle", "White Plains"
            ],
            "San Juan": [
                "Addition Hills", "Balong-Bato", "Batis", "Corazon de Jesus", "Ermitaño", "Greenhills",
                "Halo-Halo", "Isabelita", "Kabayanan", "Little Baguio", "Maytunas", "Onse",
                "Pedro Cruz", "Progreso", "Rivera", "Salapan", "San Perfecto", "Santa Lucia",
                "Tibagan", "West Crame"
            ],
            "Taguig": [
                "Bagumbayan", "Bambang", "Calzada", "Central Bicutan", "Central Signal Village",
                "Fort Bonifacio", "Hagonoy", "Ibayo-Tipas", "Ligid-Tipas", "Lower Bicutan",
                "Maharlika Village", "Napindan", "New Lower Bicutan", "North Daang Hari",
                "North Signal Village", "Palingon", "Pinagsama", "San Miguel", "Santa Ana",
                "South Daang Hari", "South Signal Village", "Tanyag", "Tuktukan", "Upper Bicutan",
                "Ususan", "Wawa", "Western Bicutan"
            ],
            "Valenzuela": [
                "Arkong Bato", "Bagbaguin", "Bignay", "Bisig", "Canumay East", "Canumay West",
                "Coloong", "Dalandanan", "Gen. T. de Leon", "Hen. T. de Leon", "Isla", "Karuhatan",
                "Lawang Bato", "Lingunan", "Mabolo", "Malanday", "Malinta", "Mapulang Lupa",
                "Marulas", "Maysan", "Palasan", "Parada", "Pariancillo Villa", "Paso de Blas",
                "Pasolo", "Poblacion", "Polo", "Punturin", "Rincon", "Tagalag", "Ugong", "Viente Reales"
            ]
        };

        // Password toggle functionality
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                toggle.textContent = 'Hide';
            } else {
                field.type = 'password';
                toggle.textContent = 'Show';
            }
        }

        // City change handler
        document.getElementById('city').addEventListener('change', function() {
            const selectedCity = this.value;
            const barangaySelect = document.getElementById('barangay');
            
            // Clear existing options
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (selectedCity && barangayData[selectedCity]) {
                // Enable barangay dropdown
                barangaySelect.disabled = false;
                
                // Add barangay options for selected city
                barangayData[selectedCity].forEach(function(barangay) {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;
                    barangaySelect.appendChild(option);
                });
            } else {
                // Disable barangay dropdown if no city selected
                barangaySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select City First</option>';
            }
        });

        // Trigger city change on page load to restore barangay selection (for form persistence)
        document.addEventListener('DOMContentLoaded', function() {
            const citySelect = document.getElementById('city');
            const selectedCity = citySelect.value;
            
            if (selectedCity) {
                // Trigger the change event to populate barangays
                citySelect.dispatchEvent(new Event('change'));
                
                // If there's a previously selected barangay, select it
                <?php if (isset($_POST['barangay']) && !empty($_POST['barangay'])): ?>
                setTimeout(function() {
                    const barangaySelect = document.getElementById('barangay');
                    barangaySelect.value = '<?php echo htmlspecialchars($_POST['barangay']); ?>';
                }, 100);
                <?php endif; ?>
            }
        });

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
            
            const city = document.getElementById('city').value;
            const barangay = document.getElementById('barangay').value;
            
            if (!city) {
                e.preventDefault();
                alert('Please select a city');
                return false;
            }
            
            if (!barangay) {
                e.preventDefault();
                alert('Please select a barangay');
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