<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_barangay']) || !isset($_SESSION['admin_city'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

// Get admin's barangay and city information
$admin_barangay_number = $_SESSION['admin_barangay'];
$admin_city = $_SESSION['admin_city'];

// AJAX handler to return verification_requests for a given user
if (isset($_GET['action']) && $_GET['action'] === 'get_verifications') {
    header('Content-Type: application/json; charset=utf-8');

    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user id']);
        exit;
    }

    try {
        // Verify that the user belongs to the admin's barangay AND city before returning verifications
        $verify_user_stmt = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE id = ? AND 
            LOWER(TRIM(city)) = LOWER(TRIM(?)) AND
            (
                TRIM(LEADING '0' FROM barangay) = TRIM(LEADING '0' FROM ?) OR
                LOWER(TRIM(barangay)) = LOWER(TRIM(?))
            )
        ");
        $verify_user_stmt->execute([$user_id, $admin_city, $admin_barangay_number, $admin_barangay_number]);
        
        if ($verify_user_stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'User not found in your jurisdiction']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM verification_requests WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $verifications = [];
        foreach ($rows as $r) {
            // Include all fields from the verification_requests table
            $verifications[] = [
                'id' => $r['id'],
                'user_id' => $r['user_id'],
                'full_name' => $r['full_name'],
                'birthdate' => $r['birthdate'],
                'attachment_1' => $r['attachment_1'],
                'attachment_2' => $r['attachment_2'],
                'attachment_3' => $r['attachment_3'],
                'id_number_1' => $r['id_number_1'],
                'id_number_2' => $r['id_number_2'],
                'id_number_3' => $r['id_number_3'],
                'status' => $r['status'],
                'rejection_reason' => $r['rejection_reason'],
                'created_at' => $r['created_at'],
                'updated_at' => $r['updated_at'],
                'processed_by' => $r['processed_by'],
                'processed_at' => $r['processed_at']
            ];
        }

        echo json_encode(['success' => true, 'verifications' => $verifications]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Get admin info for display
$admin_stmt = $pdo->prepare("SELECT city, barangay_number FROM admins WHERE id = ?");
$admin_stmt->execute([$_SESSION['admin_id']]);
$admin_info = $admin_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> Verifier</title>
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
            color: #2c3e50;
        }
        
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
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-info {
            background: #f1f5f9;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #64748b;
            text-align: right;
        }
        
        .back-btn, .logout-btn {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .back-btn {
            background: #28a745;
        }
        
        .back-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .logout-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stats-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
            min-width: 120px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .filters-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
        }
        
        .filter-input {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            min-width: 150px;
        }
        
        .filter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .users-table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th,
        .users-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #f8f9fa;
            font-size: 13px;
        }
        
        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }
        
        .users-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .users-table tbody tr:hover {
            background: #f8f9fa;
            transform: translateX(2px);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .user-email {
            color: #6c757d;
            font-size: 11px;
        }
        
        .location-info {
            font-size: 12px;
            color: #495057;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
            min-width: 80px;
            display: inline-block;
        }
        
        .status-basic {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-verified {
            background: #d4edda;
            color: #155724;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            min-width: 80px;
        }
        
        .verify-btn {
            background: #28a745;
            color: white;
        }
        
        .unverify-btn {
            background: #dc3545;
            color: white;
        }
        
        .view-btn {
            background: #667eea;
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .table-wrapper {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .pagination {
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            border-top: 1px solid #dee2e6;
        }
        
        .pagination button {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            background: white;
            color: #495057;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pagination button:hover:not(:disabled) {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination .active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transform: translateX(400px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .toast.success {
            background: #28a745;
        }
        
        .toast.error {
            background: #dc3545;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
            margin-right: 5px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .confirmation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10001;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            margin: 20px;
        }
        
        .modal-content h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .modal-content p {
            margin-bottom: 20px;
            color: #6c757d;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .confirm-btn {
            background: #28a745;
            color: white;
        }
        
        .cancel-btn {
            background: #6c757d;
            color: white;
        }
        
        .modal-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10002;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #667eea;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        
        .error-message {
            text-align: center;
            padding: 30px;
            color: #dc3545;
            font-size: 18px;
        }

        /* Enhanced User Modal Styles */
        .user-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10003;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .user-modal-content {
            background: white;
            max-width: 1000px;
            max-height: 90vh;
            padding: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            position: relative;
        }

        .user-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .user-modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-modal-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .close-modal-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .user-modal-body {
            padding: 25px;
            overflow-y: auto;
            max-height: calc(90vh - 100px);
        }

        .user-basic-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }

        .user-basic-info h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }

        .verifications-section {
            margin-top: 25px;
        }

        .verifications-section h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .verification-request {
            border: 1px solid #dee2e6;
            padding: 20px;
            margin: 15px 0;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .verification-request:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .verification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .verification-request h4 {
            margin: 0;
            color: #495057;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .verification-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .verification-field {
            display: flex;
            flex-direction: column;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #667eea;
        }

        .verification-field-label {
            font-size: 11px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .verification-field-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 13px;
        }

        .verification-status {
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            font-size: 12px;
            text-transform: uppercase;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .attachments-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .attachments-section h5 {
            margin-bottom: 10px;
            color: #495057;
            font-size: 1rem;
        }

        .attachment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .attachment-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            background: #f8f9fa;
            text-align: center;
        }

        .attachment-item h6 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 12px;
            font-weight: 600;
        }

        .id-number-display {
            background: #e3f2fd;
            color: #1565c0;
            padding: 6px 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin-bottom: 8px;
            display: inline-block;
        }

        .attachment-preview {
            display: inline-block;
            margin: 5px 0;
        }

        .attachment-preview img {
            max-width: 120px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .pdf-preview {
            background: #dc3545;
            color: white;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .pdf-preview:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
            transform: scale(1.05);
        }

        .no-attachment {
            color: #6c757d;
            font-style: italic;
            font-size: 12px;
        }

        .no-verifications {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 2px dashed #dee2e6;
        }

        .rejection-reason-section {
            margin-top: 10px;
            padding: 12px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
        }

        .rejection-reason-section h6 {
            margin: 0 0 8px 0;
            color: #856404;
            font-size: 12px;
        }

        .rejection-reason-text {
            color: #856404;
            font-size: 13px;
            font-style: italic;
        }

        .processing-info {
            margin-top: 15px;
            padding: 12px;
            background: #e9ecef;
            border-radius: 6px;
        }

        .processing-info h6 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 12px;
        }

        .processing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .processing-item {
            font-size: 12px;
            color: #6c757d;
        }

        .processing-item strong {
            color: #495057;
        }
        
        @media (max-width: 768px) {
            .stats-bar {
                flex-direction: column;
                gap: 15px;
            }
            
            .filters-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .users-table {
                font-size: 11px;
            }
            
            .users-table th,
            .users-table td {
                padding: 8px 6px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 3px;
            }

            .user-modal-content {
                max-width: 95%;
                margin: 10px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .verification-details {
                grid-template-columns: 1fr;
            }

            .attachment-grid {
                grid-template-columns: 1fr;
            }

            .processing-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">✓ User Verifier - <?= htmlspecialchars($admin_info['city'] ?? $admin_city) ?> Barangay <?= htmlspecialchars($admin_barangay_number) ?></div>
            <div class="nav-actions">
                <div class="admin-info">
                    <div><strong><?= htmlspecialchars($admin_info['city'] ?? $admin_city) ?></strong></div>
                    <div>Barangay <?= htmlspecialchars($admin_barangay_number) ?></div>
                </div>
                <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> User Verification</h1>
            <p>Manage user verification status for your barangay jurisdiction</p>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number" id="totalUsers">0</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="verifiedUsers">0</div>
                <div class="stat-label">Verified</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="basicUsers">0</div>
                <div class="stat-label">Basic (Unverified)</div>
            </div>
        </div>

        <div class="filters-section">
            <div class="filter-group">
                <label>Search Name/Email</label>
                <input type="text" class="filter-input" id="searchInput" placeholder="Type to search..." oninput="filterUsers()">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select class="filter-input" id="statusFilter" onchange="filterUsers()">
                    <option value="">All Status</option>
                    <option value="basic">Basic</option>
                    <option value="verified">Verified</option>
                </select>
            </div>
            <div class="filter-group">
                <button class="back-btn" onclick="loadUsers()" style="margin-top: 20px;">🔄 Refresh</button>
            </div>
        </div>

        <div class="users-table-container">
            <div class="table-header">
                <div class="table-title">Users in <?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?></div>
            </div>

            <div class="table-wrapper" id="tableWrapper">
                <div id="loadingMessage" class="error-message">Loading users...</div>
            </div>

            <div class="pagination" id="paginationSection" style="display: none;">
                <button onclick="changePage(-1)" id="prevBtn">← Previous</button>
                <span id="pageInfo">Page 1 of 1</span>
                <button onclick="changePage(1)" id="nextBtn">Next →</button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="confirmation-modal" id="confirmationModal">
        <div class="modal-content">
            <h3 id="modalTitle">Confirm Action</h3>
            <p id="modalMessage">Are you sure you want to proceed?</p>
            <div class="modal-buttons">
                <button class="modal-btn confirm-btn" id="confirmBtn" onclick="confirmAction()">Confirm</button>
                <button class="modal-btn cancel-btn" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <script>
        let usersData = [];
        let filteredUsers = [];
        let currentPage = 1;
        const usersPerPage = 10;
        let pendingAction = null;
        
        // Function to fetch users from database - NOW FILTERED BY BARANGAY AND CITY
        async function loadUsers() {
            showLoading(true);
            try {
                const response = await fetch('get_users_filtered.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    usersData = result.users || [];
                    filteredUsers = [...usersData];
                    renderUsers();
                    document.getElementById('loadingMessage').style.display = 'none';
                    document.getElementById('paginationSection').style.display = 'flex';
                } else {
                    throw new Error(result.message || 'Failed to fetch users');
                }
            } catch (error) {
                console.error('Error loading users:', error);
                document.getElementById('loadingMessage').innerHTML = `
                    <div style="color: #dc3545;">
                        <strong>Error loading users:</strong><br>
                        ${error.message}<br><br>
                        <button class="back-btn" onclick="loadUsers()">Try Again</button>
                    </div>
                `;
                showToast('Failed to load users. Please try again.', 'error');
            } finally {
                showLoading(false);
            }
        }
        
        // Function to make API calls to update user status
        async function updateUserStatus(userId, newStatus) {
            try {
                const response = await fetch('update_user_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        status: newStatus
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update the local data
                    const user = usersData.find(u => u.id === userId);
                    if (user) {
                        user.status = newStatus;
                        user.updated_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
                        
                        // Update filtered users as well
                        const filteredUser = filteredUsers.find(u => u.id === userId);
                        if (filteredUser) {
                            filteredUser.status = newStatus;
                            filteredUser.updated_at = user.updated_at;
                        }
                    }
                    return { success: true };
                } else {
                    return { success: false, message: result.message || 'Failed to update user status' };
                }
            } catch (error) {
                console.error('Error updating user status:', error);
                return { success: false, message: 'Network error occurred' };
            }
        }
        
        function renderUsers() {
            const tableWrapper = document.getElementById('tableWrapper');
            const startIndex = (currentPage - 1) * usersPerPage;
            const endIndex = startIndex + usersPerPage;
            const usersToShow = filteredUsers.slice(startIndex, endIndex);
            
            if (usersToShow.length === 0) {
                tableWrapper.innerHTML = '<div class="error-message">No users found in your barangay jurisdiction.</div>';
                return;
            }
            
            const tableHTML = `
                <table class="users-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Information</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        ${usersToShow.map(user => {
                            const fullName = `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}`;
                            
                            return `
                                <tr>
                                    <td><strong>#${user.id.toString().padStart(3, '0')}</strong></td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-name">${fullName}</div>
                                            <div class="user-email">${user.email}</div>
                                        </div>
                                    </td>
                                    <td>${user.mobile_number}</td>
                                    <td>
                                        <div class="location-info">
                                            ${user.city}<br>
                                            <small>Brgy. ${user.barangay}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-${user.status}">
                                            ${user.status === 'verified' ? '✓ Verified' : '⏳ Basic'}
                                        </span>
                                    </td>
                                    <td>
                                        <small>${new Date(user.created_at).toLocaleDateString()}</small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            ${user.status === 'basic' 
                                                ? `<button class="action-btn verify-btn" onclick="promptUserAction(${user.id}, 'verify')" id="btn-${user.id}">Verify</button>`
                                                : `<button class="action-btn unverify-btn" onclick="promptUserAction(${user.id}, 'unverify')" id="btn-${user.id}">Unverify</button>`
                                            }
                                            <button class="action-btn view-btn" onclick="viewUser(${user.id})">View</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            `;
            
            tableWrapper.innerHTML = tableHTML;
            updatePagination();
            updateStats();
        }
        
        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            
            filteredUsers = usersData.filter(user => {
                const fullName = `${user.first_name} ${user.middle_name || ''} ${user.last_name}`.toLowerCase();
                const matchesSearch = fullName.includes(searchTerm) || user.email.toLowerCase().includes(searchTerm);
                const matchesStatus = !statusFilter || user.status === statusFilter;
                
                return matchesSearch && matchesStatus;
            });
            
            currentPage = 1;
            renderUsers();
        }
        
        function promptUserAction(userId, action) {
            const user = usersData.find(u => u.id === userId);
            if (!user) return;
            
            const fullName = `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}`;
            const modal = document.getElementById('confirmationModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            
            if (action === 'verify') {
                modalTitle.textContent = 'Verify User';
                modalMessage.textContent = `Are you sure you want to verify ${fullName}? This will change their status from Basic to Verified.`;
            } else {
                modalTitle.textContent = 'Remove Verification';
                modalMessage.textContent = `Are you sure you want to remove verification for ${fullName}? This will change their status from Verified to Basic.`;
            }
            
            pendingAction = { userId, action };
            modal.style.display = 'flex';
        }
        
        async function confirmAction() {
            if (!pendingAction) return;
            
            const { userId, action } = pendingAction;
            const user = usersData.find(u => u.id === userId);
            const button = document.getElementById(`btn-${userId}`);
            
            // Close modal first
            closeModal();
            
            // Disable button and show loading
            button.disabled = true;
            const originalText = button.textContent;
            button.innerHTML = '<span class="spinner"></span>Updating...';
            
            const newStatus = action === 'verify' ? 'verified' : 'basic';
            const result = await updateUserStatus(userId, newStatus);
            
            if (result.success) {
                const actionText = action === 'verify' ? 'verified' : 'unverified';
                showToast(`${user.first_name} ${user.last_name} ${actionText} successfully!`, 'success');
                renderUsers();
            } else {
                showToast(`Error: ${result.message}`, 'error');
                button.disabled = false;
                button.textContent = originalText;
            }
            
            pendingAction = null;
        }
        
        function closeModal() {
            document.getElementById('confirmationModal').style.display = 'none';
            pendingAction = null;
        }
        
        async function viewUser(userId) {
            const user = usersData.find(u => u.id === userId);
            if (!user) return;
            
            showLoading(true);
            
            try {
                // Fetch verification requests for this user
                const response = await fetch(`<?php echo basename(__FILE__); ?>?action=get_verifications&user_id=${userId}`);
                const result = await response.json();
                
                if (result.success) {
                    showUserModal(user, result.verifications);
                } else {
                    throw new Error(result.message || 'Failed to fetch verification data');
                }
            } catch (error) {
                console.error('Error fetching verification data:', error);
                showToast('Failed to load verification data', 'error');
                // Fallback to basic user info
                showBasicUserInfo(user);
            } finally {
                showLoading(false);
            }
        }
        
        function showUserModal(user, verifications) {
            const fullName = `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}`;
            const isActive = user.is_active == 1 ? 'Active' : 'Inactive';
            
            let verificationsHTML = '';
            if (verifications.length > 0) {
                verificationsHTML = verifications.map(v => {
                    let statusClass = 'status-pending';
                    if (v.status === 'approved') statusClass = 'status-approved';
                    if (v.status === 'rejected') statusClass = 'status-rejected';
                    
                    // Create attachments section
                    let attachmentsHTML = '';
                    const attachmentTypes = [
                        { key: 'attachment_1', idKey: 'id_number_1', title: 'Primary ID Document' },
                        { key: 'attachment_2', idKey: 'id_number_2', title: 'Secondary ID Document' },
                        { key: 'attachment_3', idKey: 'id_number_3', title: 'Additional Document' }
                    ];
                    
                    attachmentTypes.forEach(type => {
                        const hasFile = v[type.key] && v[type.key] !== null;
                        const hasIdNumber = v[type.idKey] && v[type.idKey] !== null;
                        
                        if (hasFile || hasIdNumber) {
                            let filePreview = '';
                            if (hasFile) {
                                const fileExtension = v[type.key].split('.').pop().toLowerCase();
                                if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                                    filePreview = `
                                        <div class="attachment-preview">
                                            <img src="${v[type.key]}" alt="${type.title}" onclick="openImageModal('${v[type.key]}')">
                                        </div>
                                    `;
                                } else if (fileExtension === 'pdf') {
                                    filePreview = `
                                        <a href="${v[type.key]}" target="_blank" class="pdf-preview">
                                            📄 PDF Document<br>
                                            <small>Click to view</small>
                                        </a>
                                    `;
                                } else {
                                    filePreview = `
                                        <a href="${v[type.key]}" target="_blank" class="pdf-preview">
                                            📎 ${type.title}<br>
                                            <small>Click to download</small>
                                        </a>
                                    `;
                                }
                            }
                            
                            attachmentsHTML += `
                                <div class="attachment-item">
                                    <h6>${type.title}</h6>
                                    ${hasIdNumber ? `<div class="id-number-display">${v[type.idKey]}</div>` : ''}
                                    ${hasFile ? filePreview : '<div class="no-attachment">No file uploaded</div>'}
                                </div>
                            `;
                        }
                    });
                    
                    if (!attachmentsHTML) {
                        attachmentsHTML = '<div class="no-attachment">No documents uploaded</div>';
                    }
                    
                    return `
                        <div class="verification-request">
                            <div class="verification-header">
                                <h4>Verification Request #${v.id}</h4>
                                <span class="verification-status ${statusClass}">${v.status.toUpperCase()}</span>
                            </div>
                            
                            <div class="verification-details">
                                <div class="verification-field">
                                    <div class="verification-field-label">Full Name</div>
                                    <div class="verification-field-value">${v.full_name || 'Not provided'}</div>
                                </div>
                                <div class="verification-field">
                                    <div class="verification-field-label">Birth Date</div>
                                    <div class="verification-field-value">${v.birthdate ? new Date(v.birthdate).toLocaleDateString() : 'Not provided'}</div>
                                </div>
                                <div class="verification-field">
                                    <div class="verification-field-label">Submitted</div>
                                    <div class="verification-field-value">${v.created_at ? new Date(v.created_at).toLocaleString() : 'N/A'}</div>
                                </div>
                                <div class="verification-field">
                                    <div class="verification-field-label">Last Updated</div>
                                    <div class="verification-field-value">${v.updated_at && v.updated_at !== v.created_at ? new Date(v.updated_at).toLocaleString() : 'Not updated'}</div>
                                </div>
                            </div>
                            
                            ${v.rejection_reason ? `
                                <div class="rejection-reason-section">
                                    <h6>Rejection Reason</h6>
                                    <div class="rejection-reason-text">${v.rejection_reason}</div>
                                </div>
                            ` : ''}
                            
                            ${v.processed_by || v.processed_at ? `
                                <div class="processing-info">
                                    <h6>Processing Information</h6>
                                    <div class="processing-grid">
                                        ${v.processed_by ? `<div class="processing-item"><strong>Processed by:</strong> Admin #${v.processed_by}</div>` : ''}
                                        ${v.processed_at ? `<div class="processing-item"><strong>Processed at:</strong> ${new Date(v.processed_at).toLocaleString()}</div>` : ''}
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="attachments-section">
                                <h5>📎 Submitted Documents</h5>
                                <div class="attachment-grid">
                                    ${attachmentsHTML}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                verificationsHTML = '<div class="no-verifications">📭 No verification requests found for this user.</div>';
            }
            
            const modalHTML = `
                <div class="user-modal" onclick="closeUserModal(event)" id="userModal">
                    <div class="user-modal-content" onclick="event.stopPropagation()">
                        <div class="user-modal-header">
                            <h2>👤 ${fullName} - User Details</h2>
                            <button class="close-modal-btn" onclick="closeUserModal()">&times;</button>
                        </div>
                        <div class="user-modal-body">
                            <div class="user-basic-info">
                                <h3>📋 Basic Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">User ID</div>
                                        <div class="info-value">#${user.id.toString().padStart(3, '0')}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Full Name</div>
                                        <div class="info-value">${fullName}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Email Address</div>
                                        <div class="info-value">${user.email}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Mobile Number</div>
                                        <div class="info-value">${user.mobile_number}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">City</div>
                                        <div class="info-value">${user.city}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Barangay</div>
                                        <div class="info-value">${user.barangay}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Verification Status</div>
                                        <div class="info-value">
                                            <span class="status-badge status-${user.status}">
                                                ${user.status === 'verified' ? '✅ Verified' : '⏳ Basic'}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Account Status</div>
                                        <div class="info-value">${isActive}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Registration Date</div>
                                        <div class="info-value">${new Date(user.created_at).toLocaleString()}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Last Updated</div>
                                        <div class="info-value">${new Date(user.updated_at).toLocaleString()}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="verifications-section">
                                <h3>📋 Verification Requests History</h3>
                                ${verificationsHTML}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        function openImageModal(imageSrc) {
            const imageModal = document.createElement('div');
            imageModal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10004;
                cursor: pointer;
            `;
            
            const enlargedImg = document.createElement('img');
            enlargedImg.src = imageSrc;
            enlargedImg.style.cssText = `
                max-width: 90%;
                max-height: 90%;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            `;
            
            imageModal.appendChild(enlargedImg);
            document.body.appendChild(imageModal);
            
            // Close on click
            imageModal.addEventListener('click', () => {
                document.body.removeChild(imageModal);
            });
            
            // Close on escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape') {
                    if (document.body.contains(imageModal)) {
                        document.body.removeChild(imageModal);
                    }
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);
        }
        
        function showBasicUserInfo(user) {
            const fullName = `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}`;
            const isActive = user.is_active == 1 ? 'Active' : 'Inactive';
            alert(`User Details:\n\nName: ${fullName}\nEmail: ${user.email}\nMobile: ${user.mobile_number}\nLocation: ${user.city}, Brgy. ${user.barangay}\nStatus: ${user.status.toUpperCase()}\nAccount Status: ${isActive}\nRegistered: ${new Date(user.created_at).toLocaleString()}\nLast Updated: ${new Date(user.updated_at).toLocaleString()}`);
        }
        
        function closeUserModal(event) {
            if (event && event.target !== event.currentTarget) return;
            const modal = document.getElementById('userModal');
            if (modal) {
                modal.remove();
            }
        }
        
        function changePage(direction) {
            const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
            const newPage = currentPage + direction;
            
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                renderUsers();
            }
        }
        
        function updatePagination() {
            const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
            document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages;
        }
        
        function updateStats() {
            const totalUsers = usersData.length;
            const verifiedUsers = usersData.filter(u => u.status === 'verified').length;
            const basicUsers = usersData.filter(u => u.status === 'basic').length;
            
            document.getElementById('totalUsers').textContent = totalUsers;
            document.getElementById('verifiedUsers').textContent = verifiedUsers;
            document.getElementById('basicUsers').textContent = basicUsers;
        }
        
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 3000);
        }
        
        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = show ? 'flex' : 'none';
        }
        
        // Close modal when clicking outside of it
        document.getElementById('confirmationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeUserModal();
            }
        });
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });
    </script>
</body>
</html>