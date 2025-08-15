<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

require_once 'config.php';

// Get user status and barangay from database
try {
    $stmt = $pdo->prepare("
        SELECT status, is_active, barangay, first_name, last_name, profile_picture
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        session_destroy();
        header("Location: user_login.php");
        exit();
    }
    
    $user_status = $user_data['status'];
    $is_active = $user_data['is_active'];
    $user_barangay = $user_data['barangay'];
    $first_name = $user_data['first_name'];
    $last_name = $user_data['last_name'];
    $profile_picture = $user_data['profile_picture'];
    
    // Check if user account is active
    if (!$is_active) {
        session_destroy();
        header("Location: user_login.php?error=account_deactivated");
        exit();
    }
    
    // Redirect if user is not verified
    if ($user_status !== 'verified') {
        header("Location: user_dashboard.php");
        exit();
    }
    
} catch (Exception $e) {
    session_destroy();
    header("Location: user_login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    try {
        $service_type = $_POST['service_type'];
        $surname = sanitizeInput($_POST['surname']);
        $given_name = sanitizeInput($_POST['given_name']);
        $middle_name = sanitizeInput($_POST['middle_name']);
        $birthdate = $_POST['birthdate'];
        $address = sanitizeInput($_POST['address']);
        $contact_number = sanitizeInput($_POST['contact_number']);
        $purpose = sanitizeInput($_POST['purpose']);
        $additional_info = sanitizeInput($_POST['additional_info']);
        $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : null;
        
        // Validate required fields
        if (empty($service_type) || empty($surname) || empty($given_name) || empty($birthdate) || empty($address) || empty($contact_number)) {
            throw new Exception("Please fill in all required fields.");
        }
        
        // Validate contact number
        if (!validateMobileNumber($contact_number)) {
            throw new Exception("Please enter a valid mobile number (09xxxxxxxxx).");
        }
        
        // Handle file uploads
        $uploaded_files = [];
        $upload_dir = 'uploads/barangay_requests/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Process file uploads based on service type
        $file_fields = getRequiredFileFields($service_type);
        
        foreach ($file_fields as $field_name => $field_info) {
            if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$field_name];
                
                // Validate file
                if (!validateUploadedFile($file, $field_info['allowed_types'], $field_info['max_size'])) {
                    throw new Exception("Invalid file for " . $field_info['label'] . ". " . getFileValidationMessage($field_info));
                }
                
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $unique_filename = $service_type . '_' . $field_name . '_' . time() . '_' . uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $unique_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $uploaded_files[$field_name] = $unique_filename;
                } else {
                    throw new Exception("Failed to upload " . $field_info['label']);
                }
            } elseif ($field_info['required']) {
                throw new Exception($field_info['label'] . " is required for this service.");
            }
        }
        
        // Insert into barangay_requests table
        $stmt = $pdo->prepare("
            INSERT INTO barangay_requests (
                user_id, service_type, surname, given_name, middle_name, 
                birthdate, address, contact_number, purpose, additional_info, 
                event_date, uploaded_files, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $service_type,
            $surname,
            $given_name,
            $middle_name,
            $birthdate,
            $address,
            $contact_number,
            $purpose,
            $additional_info,
            $event_date,
            json_encode($uploaded_files)
        ]);
        
        $success_message = "Your request for " . ucfirst(str_replace('_', ' ', $service_type)) . " has been submitted successfully!";
        
        // Log activity
        logActivity($pdo, 'user', $_SESSION['user_id'], 'BARANGAY_REQUEST_SUBMITTED', $service_type);
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Function to get required file fields for each service - MOVED TO LOCAL FUNCTION TO AVOID CONFLICTS
function getRequiredFileFields($service_type) {
    $file_requirements = [
        'certificate_of_residency' => [
            'billing_statement' => [
                'label' => 'Billing Statement',
                'required' => true,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'description' => 'Upload a recent billing statement as proof of residency'
            ]
        ],
        'certificate_of_indigency' => [
            'pao_application' => [
                'label' => 'PAO Application Proof',
                'required' => true,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024,
                'description' => 'Upload proof of application to Public Attorney\'s Office (PAO)'
            ]
        ],
        'business_clearance' => [
            'barangay_id_or_residency' => [
                'label' => 'Barangay ID or Certificate of Residency',
                'required' => true,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024,
                'description' => 'Upload your Barangay ID or Certificate of Residency'
            ],
            'lease_contract' => [
                'label' => 'Lease/Rental Contract',
                'required' => false,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024,
                'description' => 'Upload lease contract if applying for rental area (optional)'
            ],
            'dti_registration' => [
                'label' => 'DTI Registration',
                'required' => false,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024,
                'description' => 'Upload DTI registration document (if available)'
            ]
        ],
        'solo_parent_certificate' => [
            'supporting_document' => [
                'label' => 'Supporting Document',
                'required' => true,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024,
                'description' => 'Upload proof of solo parent status (death certificate, annulment papers, etc.)'
            ]
        ],
        'barangay_id' => [
            'id_picture' => [
                'label' => 'ID Picture',
                'required' => true,
                'allowed_types' => ['jpg', 'jpeg', 'png'],
                'max_size' => 2 * 1024 * 1024, // 2MB for images
                'description' => 'Upload a recent 2x2 ID picture'
            ]
        ],
        'calamity_certificate' => [
            'incident_proof' => [
                'label' => 'Proof of Incident',
                'required' => true,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024,
                'description' => 'Upload proof of calamity incident (photos, reports, etc.)'
            ],
            'valid_id' => [
                'label' => 'Valid ID',
                'required' => true,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024,
                'description' => 'Upload a copy of your valid ID for verification'
            ]
        ]
    ];
    
    return isset($file_requirements[$service_type]) ? $file_requirements[$service_type] : [];
}

// Barangay services data
$barangay_services = [
    'barangay_clearance' => [
        'name' => 'Barangay Clearance',
        'description' => 'Certifies that a person had no bad record in the barangay and is a resident',
        'required_for' => 'Job application, business permit, police clearance',
        'icon' => '📋'
    ],
    'certificate_of_residency' => [
        'name' => 'Certificate of Residency',
        'description' => 'Confirms a person is residing in the barangay',
        'required_for' => 'Scholarship application, court documentation',
        'icon' => '🏠'
    ],
    'certificate_of_indigency' => [
        'name' => 'Certificate of Indigency',
        'description' => 'Certifies that a person belongs to a low-income or indigent family',
        'required_for' => 'Medical assistance, PAO, scholarship, PhilHealth, social welfare benefits',
        'icon' => '💰'
    ],
    'business_clearance' => [
        'name' => 'Barangay Business Clearance',
        'description' => 'Certifies that a business is authorized to operate in the barangay',
        'required_for' => 'Business permit application with city/municipal hall',
        'icon' => '🏢'
    ],
    'solo_parent_certificate' => [
        'name' => 'Solo Parent Certificate',
        'description' => 'Confirms the status of an individual as solo parent',
        'required_for' => 'Accessing benefits under the Solo Parents\' Welfare Act',
        'icon' => '👨‍👧‍👦'
    ],
    'barangay_id' => [
        'name' => 'Barangay ID',
        'description' => 'Confirmation of identity and residency of the individual',
        'required_for' => 'Secondary ID and identification of residency',
        'icon' => '🆔'
    ],
    'event_permit' => [
        'name' => 'Permit for Event',
        'description' => 'Certifies that the local gathering is safe for public',
        'required_for' => 'Local gathering, fiesta, and public activities',
        'icon' => '🎉'
    ],
    'calamity_certificate' => [
        'name' => 'Certificate of Calamity Victim',
        'description' => 'Certifies that holder is a victim of disaster (e.g., Fire, flood)',
        'required_for' => 'Relief assistance, insurance claim, or financial aid',
        'icon' => '🆘'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Forms - Barangay Help Desk</title>
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
            color: #2c3e50;
        }
        
        /* NAVBAR */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-link {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255, 145, 77, 0.1);
            color: #ff914d;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            color: white;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* USER INFO */
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .user-avatar-initial {
            font-size: 16px;
            font-weight: 600;
        }
        
        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 145, 77, 0.4);
        }
        
        .user-avatar::after {
            content: "✏️";
            position: absolute;
            bottom: -2px;
            right: -2px;
            font-size: 12px;
            background: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .user-avatar:hover::after {
            opacity: 1;
        }
        
        .user-status {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            background: #d4edda;
            color: #155724;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .header-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .page-title {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .service-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-color: #ff914d;
        }
        
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #ff5e00;
        }
        
        .service-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .service-description {
            color: #7f8c8d;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .service-required {
            color: #27ae60;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99999;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: white;
            margin: 2% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            z-index: 100000;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .close-btn:hover {
            color: #e74c3c;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .required {
            color: #e74c3c;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #ff914d;
            box-shadow: 0 0 0 3px rgba(255, 145, 77, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .file-input-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-label {
            display: block;
            padding: 12px;
            border: 2px dashed #bdc3c7;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .file-input-label:hover {
            border-color: #ff914d;
            background: rgba(255, 145, 77, 0.05);
        }
        
        .file-input-label.has-file {
            border-color: #27ae60;
            background: rgba(39, 174, 96, 0.05);
            color: #27ae60;
        }
        
        .file-description {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 5px;
            font-style: italic;
        }
        
        .btn {
            background: linear-gradient(135deg, #ff914d, #ff5e00);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
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
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .requirements-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .requirements-title {
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 10px;
        }
        
        .requirements-list {
            list-style: none;
            padding: 0;
        }
        
        .requirements-list li {
            padding: 5px 0;
            color: #1565c0;
            font-size: 0.9rem;
        }
        
        .requirements-list li:before {
            content: "📎 ";
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                order: -1;
            }
            
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🛡️ Barangay <?php echo htmlspecialchars($user_barangay); ?></div>
            <div class="nav-links">
                <a href="user_dashboard.php" class="nav-link">Dashboard</a>
                <a href="user_forms.php" class="nav-link active">Request Forms</a>
                <a href="user_requests.php" class="nav-link">My Requests</a>
                <a href="user.php" class="nav-link">💬 Chat Support</a>
            </div>
            <div class="user-info">
                <a href="user_edit.php" class="user-avatar" title="Edit Profile">
                    <?php if (!empty($profile_picture) && file_exists($profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <span class="user-avatar-initial"><?php echo strtoupper($first_name[0]); ?></span>
                    <?php endif; ?>
                </a>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">
                        <?php echo $first_name . " " . $last_name; ?>
                    </div>
                </div>
                <div class="user-status">
                    <?php echo ucfirst($user_status); ?>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="header-section">
            <h1 class="page-title">Request Barangay Forms</h1>
            <p>Select a service below to submit your request. All forms will be processed within 3-5 business days.</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="services-grid">
            <?php foreach ($barangay_services as $service_key => $service): ?>
                <div class="service-card" onclick="openServiceModal('<?php echo $service_key; ?>', '<?php echo $service['name']; ?>')">
                    <div class="service-icon"><?php echo $service['icon']; ?></div>
                    <div class="service-name"><?php echo $service['name']; ?></div>
                    <div class="service-description"><?php echo $service['description']; ?></div>
                    <div class="service-required">Required for: <?php echo $service['required_for']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Service Request Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Request Service</h3>
                <button class="close-btn" onclick="closeServiceModal()">&times;</button>
            </div>
            
            <div id="requirementsInfo" class="requirements-info" style="display: none;">
                <div class="requirements-title">📋 Required Documents for this Service:</div>
                <ul id="requirementsList" class="requirements-list"></ul>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" id="service_type" name="service_type">
                
                <div class="form-section">
                    <div class="form-section-title">
                        👤 Personal Information
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <div class="form-row">
                            <div>
                                <input type="text" name="surname" placeholder="Surname/Last Name" required>
                            </div>
                            <div>
                                <input type="text" name="given_name" placeholder="Given/First Name" required>
                            </div>
                            <div>
                                <input type="text" name="middle_name" placeholder="Middle Name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="birthdate">Birthdate <span class="required">*</span></label>
                        <input type="date" id="birthdate" name="birthdate" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Complete Address <span class="required">*</span></label>
                        <textarea id="address" name="address" placeholder="House No., Street, Barangay, City" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_number">Contact Number <span class="required">*</span></label>
                        <input type="tel" id="contact_number" name="contact_number" placeholder="09xxxxxxxxx" pattern="09[0-9]{9}" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-section-title">
                        📝 Request Details
                    </div>
                    
                    <div class="form-group">
                        <label for="purpose">Purpose <span class="required">*</span></label>
                        <input type="text" id="purpose" name="purpose" placeholder="e.g., Job application, Business permit" required>
                    </div>
                    
                    <div class="form-group" id="eventDateGroup" style="display: none;">
                        <label for="event_date">Event Date <span class="required">*</span></label>
                        <input type="date" id="event_date" name="event_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_info">Additional Information</label>
                        <textarea id="additional_info" name="additional_info" placeholder="Any additional details or special requests"></textarea>
                    </div>
                </div>
                
                <div class="form-section" id="fileUploadsSection" style="display: none;">
                    <div class="form-section-title">
                        📎 Required Documents
                    </div>
                    <div id="fileUploadFields"></div>
                </div>
                
                <button type="submit" name="submit_request" class="btn">Submit Request</button>
            </form>
        </div>
    </div>

    <script>
        // File requirements for each service type
        const fileRequirements = {
            'certificate_of_residency': {
                'billing_statement': {
                    label: 'Billing Statement',
                    required: true,
                    description: 'Upload a recent billing statement as proof of residency',
                    accept: '.pdf,.jpg,.jpeg,.png'
                }
            },
            'certificate_of_indigency': {
                'pao_application': {
                    label: 'PAO Application Proof',
                    required: true,
                    description: 'Upload proof of application to Public Attorney\'s Office (PAO)',
                    accept: '.pdf,.jpg,.jpeg,.png'
                }
            },
            'business_clearance': {
                'barangay_id_or_residency': {
                    label: 'Barangay ID or Certificate of Residency',
                    required: true,
                    description: 'Upload your Barangay ID or Certificate of Residency',
                    accept: '.pdf,.jpg,.jpeg,.png'
                },
                'lease_contract': {
                    label: 'Lease/Rental Contract',
                    required: false,
                    description: 'Upload lease contract if applying for rental area (optional)',
                    accept: '.pdf,.jpg,.jpeg,.png'
                },
                'dti_registration': {
                    label: 'DTI Registration',
                    required: false,
                    description: 'Upload DTI registration document (if available)',
                    accept: '.pdf,.jpg,.jpeg,.png'
                }
            },
            'solo_parent_certificate': {
                'supporting_document': {
                    label: 'Supporting Document',
                    required: true,
                    description: 'Upload proof of solo parent status (death certificate, annulment papers, etc.)',
                    accept: '.pdf,.jpg,.jpeg,.png'
                }
            },
            'barangay_id': {
                'id_picture': {
                    label: 'ID Picture',
                    required: true,
                    description: 'Upload a recent 2x2 ID picture',
                    accept: '.jpg,.jpeg,.png'
                }
            },
            'calamity_certificate': {
                'incident_proof': {
                    label: 'Proof of Incident',
                    required: true,
                    description: 'Upload proof of calamity incident (photos, reports, etc.)',
                    accept: '.pdf,.jpg,.jpeg,.png'
                },
                'valid_id': {
                    label: 'Valid ID',
                    required: true,
                    description: 'Upload a copy of your valid ID for verification',
                    accept: '.pdf,.jpg,.jpeg,.png'
                }
            }
        };
        
        function openServiceModal(serviceKey, serviceName) {
            document.getElementById('service_type').value = serviceKey;
            document.getElementById('modalTitle').textContent = 'Request ' + serviceName;
            
            // Show/hide event date field for event permit
            const eventDateGroup = document.getElementById('eventDateGroup');
            const eventDateInput = document.getElementById('event_date');
            
            if (serviceKey === 'event_permit') {
                eventDateGroup.style.display = 'block';
                eventDateInput.required = true;
                // Set minimum date to today
                const today = new Date().toISOString().split('T')[0];
                eventDateInput.min = today;
            } else {
                eventDateGroup.style.display = 'none';
                eventDateInput.required = false;
            }
            
            // Setup file upload fields
            setupFileUploads(serviceKey);
            
            // Show requirements info
            showRequirements(serviceKey);
            
            document.getElementById('serviceModal').style.display = 'block';
        }
        
        function setupFileUploads(serviceKey) {
            const fileUploadsSection = document.getElementById('fileUploadsSection');
            const fileUploadFields = document.getElementById('fileUploadFields');
            
            // Clear previous file upload fields
            fileUploadFields.innerHTML = '';
            
            if (fileRequirements[serviceKey]) {
                fileUploadsSection.style.display = 'block';
                
                Object.keys(fileRequirements[serviceKey]).forEach(fieldName => {
                    const field = fileRequirements[serviceKey][fieldName];
                    const fieldHtml = `
                        <div class="form-group">
                            <label for="${fieldName}">
                                ${field.label} 
                                ${field.required ? '<span class="required">*</span>' : ''}
                            </label>
                            <div class="file-input-container">
                                <input type="file" 
                                       id="${fieldName}" 
                                       name="${fieldName}" 
                                       class="file-input"
                                       accept="${field.accept}"
                                       ${field.required ? 'required' : ''}
                                       onchange="handleFileSelect(this)">
                                <label for="${fieldName}" class="file-input-label">
                                    📎 Choose file or drag and drop
                                </label>
                            </div>
                            <div class="file-description">${field.description}</div>
                        </div>
                    `;
                    fileUploadFields.innerHTML += fieldHtml;
                });
            } else {
                fileUploadsSection.style.display = 'none';
            }
        }
        
        function showRequirements(serviceKey) {
            const requirementsInfo = document.getElementById('requirementsInfo');
            const requirementsList = document.getElementById('requirementsList');
            
            if (fileRequirements[serviceKey]) {
                requirementsList.innerHTML = '';
                Object.keys(fileRequirements[serviceKey]).forEach(fieldName => {
                    const field = fileRequirements[serviceKey][fieldName];
                    const li = document.createElement('li');
                    li.textContent = field.label + (field.required ? ' (Required)' : ' (Optional)');
                    requirementsList.appendChild(li);
                });
                requirementsInfo.style.display = 'block';
            } else {
                requirementsInfo.style.display = 'none';
            }
        }
        
        function handleFileSelect(input) {
            const label = input.nextElementSibling;
            if (input.files.length > 0) {
                const fileName = input.files[0].name;
                label.textContent = `📎 ${fileName}`;
                label.classList.add('has-file');
            } else {
                label.textContent = '📎 Choose file or drag and drop';
                label.classList.remove('has-file');
            }
        }
        
        function closeServiceModal() {
            document.getElementById('serviceModal').style.display = 'none';
            // Reset form
            document.querySelector('#serviceModal form').reset();
            // Reset file input labels
            document.querySelectorAll('.file-input-label').forEach(label => {
                label.textContent = '📎 Choose file or drag and drop';
                label.classList.remove('has-file');
            });
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('serviceModal');
            if (event.target === modal) {
                closeServiceModal();
            }
        }
        
        // Auto-format contact number
        document.addEventListener('DOMContentLoaded', function() {
            const contactInput = document.getElementById('contact_number');
            if (contactInput) {
                contactInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) {
                        value = value.substring(0, 11);
                    }
                    if (value.length > 2 && !value.startsWith('09')) {
                        value = '09' + value.substring(2);
                    }
                    e.target.value = value;
                });
            }
        });
        
        // Set max date for birthdate (18 years ago)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        document.getElementById('birthdate').max = maxDate.toISOString().split('T')[0];
        
        // Drag and drop functionality for file inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Add drag and drop events to file input labels
            document.addEventListener('dragover', function(e) {
                e.preventDefault();
            });
            
            document.addEventListener('drop', function(e) {
                e.preventDefault();
                if (e.target.classList.contains('file-input-label')) {
                    const fileInput = e.target.previousElementSibling;
                    if (e.dataTransfer.files.length > 0) {
                        fileInput.files = e.dataTransfer.files;
                        handleFileSelect(fileInput);
                    }
                }
            });
        });
        
        // File size validation
        function validateFileSize(input) {
            const maxSize = 5 * 1024 * 1024; // 5MB for documents, 2MB for images
            const file = input.files[0];
            
            if (file && file.size > maxSize) {
                alert('File size is too large. Maximum allowed size is 5MB.');
                input.value = '';
                handleFileSelect(input);
                return false;
            }
            return true;
        }
        
        // Add file size validation to all file inputs
        document.addEventListener('change', function(e) {
            if (e.target.type === 'file') {
                validateFileSize(e.target);
            }
        });
    </script>
</body>
</html>