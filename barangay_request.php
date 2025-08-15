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

// Database configuration
$host = 'sql308.infinityfree.com';
$dbname = 'if0_38484017_barangay_chatbot';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get admin's barangay and city information
$admin_barangay_number = $_SESSION['admin_barangay'];
$admin_city = $_SESSION['admin_city'];

// Handle status update
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['request_id'], $_POST['new_status'])) {
    // Verify that the request belongs to the admin's barangay AND city before updating
    $verify_stmt = $pdo->prepare("
        SELECT br.id 
        FROM barangay_requests br 
        JOIN users u ON br.user_id = u.id 
        WHERE br.id = ? AND 
        LOWER(TRIM(u.city)) = LOWER(TRIM(?)) AND
        (
            TRIM(LEADING '0' FROM u.barangay) = TRIM(LEADING '0' FROM ?) OR
            LOWER(TRIM(u.barangay)) = LOWER(TRIM(?))
        )
    ");
    $verify_stmt->execute([$_POST['request_id'], $admin_city, $admin_barangay_number, $admin_barangay_number]);
    
    if ($verify_stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE barangay_requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$_POST['new_status'], $_POST['request_id']]);
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$service_filter = $_GET['service'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build WHERE clause with barangay AND city filter
$where_conditions = [
    "LOWER(TRIM(u.city)) = LOWER(TRIM(?))",
    "(TRIM(LEADING '0' FROM u.barangay) = TRIM(LEADING '0' FROM ?) OR LOWER(TRIM(u.barangay)) = LOWER(TRIM(?)))"
];
$params = [$admin_city, $admin_barangay_number, $admin_barangay_number];

if ($status_filter) {
    $where_conditions[] = "br.status = ?";
    $params[] = $status_filter;
}

if ($service_filter) {
    $where_conditions[] = "br.service_type = ?";
    $params[] = $service_filter;
}

if ($search) {
    $where_conditions[] = "(br.surname LIKE ? OR br.given_name LIKE ? OR br.middle_name LIKE ? OR br.contact_number LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM barangay_requests br JOIN users u ON br.user_id = u.id $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records - Use direct integer values for LIMIT and OFFSET (not bound parameters)
$query = "SELECT br.*, u.barangay as user_barangay, u.city as user_city,
          CASE 
            WHEN br.middle_name IS NOT NULL AND br.middle_name != '' 
            THEN CONCAT(br.surname, ', ', br.given_name, ' ', br.middle_name)
            ELSE CONCAT(br.surname, ', ', br.given_name)
          END as full_name
          FROM barangay_requests br 
          JOIN users u ON br.user_id = u.id
          $where_clause 
          ORDER BY br.created_at DESC 
          LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics for admin's barangay AND city only
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN br.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN br.status = 'processing' THEN 1 ELSE 0 END) as processing,
    SUM(CASE WHEN br.status = 'ready' THEN 1 ELSE 0 END) as ready,
    SUM(CASE WHEN br.status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN br.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM barangay_requests br
    JOIN users u ON br.user_id = u.id
    WHERE LOWER(TRIM(u.city)) = LOWER(TRIM(?)) AND
    (TRIM(LEADING '0' FROM u.barangay) = TRIM(LEADING '0' FROM ?) OR LOWER(TRIM(u.barangay)) = LOWER(TRIM(?)))";

$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute([$admin_city, $admin_barangay_number, $admin_barangay_number]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Ensure all stats values are numeric (handle null values)
$stats = [
    'total' => (int)($stats['total'] ?? 0),
    'pending' => (int)($stats['pending'] ?? 0),
    'processing' => (int)($stats['processing'] ?? 0),
    'ready' => (int)($stats['ready'] ?? 0),
    'completed' => (int)($stats['completed'] ?? 0),
    'cancelled' => (int)($stats['cancelled'] ?? 0)
];

// Service type mapping - Updated to match database values
$service_types = [
    'barangay_clearance' => 'Barangay Clearance',
    'certificate_of_residency' => 'Certificate of Residency', 
    'certificate_of_indigency' => 'Certificate of Indigency',
    'business_clearance' => 'Business Clearance',
    'solo_parent_certificate' => 'Solo Parent Certificate',
    'barangay_id' => 'Barangay ID',
    'event_permit' => 'Event Permit',
    'calamity_certificate' => 'Calamity Certificate',
    'other' => 'Other'
];

// Status colors
$status_colors = [
    'pending' => '#ff9800',
    'processing' => '#ff7f00',
    'ready' => '#4caf50',
    'completed' => '#6b7280',
    'cancelled' => '#f44336'
];

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
    <title><?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> Requests</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff7f00 0%, #ff9500 25%, #ffb347 50%, #ffd700 100%);
            min-height: 100vh;
            color: #2c3e50;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 127, 0, 0.1);
            margin-bottom: 30px;
            position: relative;
            border: 1px solid rgba(255, 127, 0, 0.1);
        }

        .header h1 {
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .admin-info {
            position: absolute;
            top: 20px;
            right: 30px;
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            padding: 15px 20px;
            border-radius: 15px;
            font-size: 0.875rem;
            color: #e65100;
            border: 1px solid #ffcc80;
            box-shadow: 0 3px 10px rgba(255, 127, 0, 0.15);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff5722, #d84315);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            margin-top: 8px;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 87, 34, 0.3);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #d84315, #bf360c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 127, 0, 0.1);
            text-align: center;
            border: 1px solid rgba(255, 127, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 127, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #7f8c8d;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .filters {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 127, 0, 0.1);
            margin-bottom: 30px;
            border: 1px solid rgba(255, 127, 0, 0.1);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 2px solid #ffe0b2;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff7f00;
            box-shadow: 0 0 0 3px rgba(255, 127, 0, 0.1);
            background: white;
        }

        .btn {
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            box-shadow: 0 3px 10px rgba(255, 127, 0, 0.3);
        }

        .btn:hover {
            background: linear-gradient(135deg, #e65100, #d84315);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 127, 0, 0.4);
        }

        .btn-clear {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            margin-left: 10px;
        }

        .btn-clear:hover {
            background: linear-gradient(135deg, #4b5563, #374151);
        }

        .table-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 127, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(255, 127, 0, 0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            padding: 18px 15px;
            text-align: left;
            font-weight: 700;
            color: white;
            border-bottom: 3px solid #d84315;
            white-space: nowrap;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        th:first-child {
            border-top-left-radius: 15px;
        }

        th:last-child {
            border-top-right-radius: 15px;
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid rgba(255, 204, 128, 0.2);
            vertical-align: top;
            background: rgba(255, 255, 255, 0.95);
            transition: all 0.3s ease;
        }

        tr:hover td {
            background: rgba(255, 248, 240, 0.8);
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(255, 127, 0, 0.1);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .service-type {
            background: linear-gradient(135deg, #ffe0b2, #ffcc80);
            color: #e65100;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid #ffb74d;
        }

        .contact-info {
            font-size: 0.875rem;
            color: #7f8c8d;
        }

        .date-info {
            font-size: 0.875rem;
            color: #7f8c8d;
            white-space: nowrap;
        }

        .status-update {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .status-select {
            padding: 6px 10px;
            border: 2px solid #ffe0b2;
            border-radius: 8px;
            font-size: 0.875rem;
            background: white;
            transition: all 0.3s ease;
        }

        .status-select:focus {
            outline: none;
            border-color: #ff7f00;
            box-shadow: 0 0 0 2px rgba(255, 127, 0, 0.1);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a,
        .pagination span {
            padding: 10px 15px;
            border: 2px solid #ffe0b2;
            border-radius: 10px;
            text-decoration: none;
            color: #e65100;
            background: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            border-color: #ff7f00;
            transform: translateY(-2px);
        }

        .pagination .current {
            background: linear-gradient(135deg, #ff7f00, #ff5722);
            color: white;
            border-color: #ff7f00;
            box-shadow: 0 3px 10px rgba(255, 127, 0, 0.3);
        }

        .text-truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Default mobile card view - hidden on all screens larger than 640px */
        .mobile-card-view {
            display: none;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header,
        .stat-card,
        .filters,
        .table-container {
            animation: fadeInUp 0.6s ease-out;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }

        /* Mobile responsive design */
        @media (max-width: 1200px) {
            .container {
                max-width: 95%;
                padding: 15px;
            }
        }

        @media (max-width: 1024px) {
            .container {
                padding: 12px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
            }
            
            .table-responsive {
                border-radius: 15px;
                overflow: hidden;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 0.8rem;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .filters {
                padding: 20px 15px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 8px;
                max-width: 100%;
            }
            
            .header {
                padding: 15px 12px;
                text-align: left;
                margin-bottom: 20px;
            }
            
            .header h1 {
                font-size: 1.4rem;
                line-height: 1.2;
                margin-bottom: 8px;
            }
            
            .header p {
                font-size: 0.9rem;
                margin-bottom: 15px;
            }
            
            .admin-info {
                position: static;
                margin-top: 15px;
                padding: 12px 15px;
                text-align: left;
                font-size: 0.8rem;
            }
            
            .logout-btn {
                margin-top: 8px;
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            .filters {
                padding: 15px 12px;
                margin-bottom: 20px;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin-bottom: 20px;
            }
            
            .stat-card {
                padding: 15px 12px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            /* Mobile Table Redesign */
            .table-container {
                border-radius: 12px;
                overflow: hidden;
                margin-bottom: 20px;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 700px;
                font-size: 0.75rem;
            }
            
            th {
                padding: 10px 6px;
                font-size: 0.7rem;
                line-height: 1.1;
            }
            
            td {
                padding: 10px 6px;
                font-size: 0.75rem;
                line-height: 1.2;
            }
            
            .status-update {
                flex-direction: column;
                gap: 6px;
                align-items: stretch;
            }
            
            .status-select {
                width: 100%;
                min-width: 100px;
                padding: 4px 6px;
                font-size: 0.7rem;
            }
            
            .btn-sm {
                width: 100%;
                padding: 6px 8px;
                font-size: 0.7rem;
            }
            
            .text-truncate {
                max-width: 80px;
            }
            
            .service-type {
                padding: 4px 8px;
                font-size: 0.7rem;
                border-radius: 6px;
            }
            
            .status-badge {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
            
            .contact-info {
                font-size: 0.7rem;
            }
            
            .date-info {
                font-size: 0.7rem;
            }
            
            /* Mobile Card Layout Alternative - Hidden by default */
            .mobile-card-view {
                display: none;
            }
        }

        @media (max-width: 640px) {
            /* Switch to card layout on very small screens */
            .table-responsive {
                display: none !important;
            }
            
            .mobile-card-view {
                display: block !important;
            }
            
            .container {
                padding: 5px;
            }
            
            .header {
                padding: 12px 10px;
                margin-bottom: 15px;
            }
            
            .header h1 {
                font-size: 1.2rem;
            }
            
            .header p {
                font-size: 0.85rem;
            }
            
            .admin-info {
                padding: 10px 12px;
                font-size: 0.75rem;
            }
            
            .filters {
                padding: 12px 10px;
                margin-bottom: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
                margin-bottom: 15px;
            }
            
            .stat-card {
                padding: 12px 8px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.7rem;
            }
            
            .request-card {
                background: rgba(255, 255, 255, 0.98);
                margin-bottom: 12px;
                padding: 15px 12px;
                border-radius: 12px;
                border-left: 3px solid #ff7f00;
                box-shadow: 0 2px 8px rgba(255, 127, 0, 0.1);
                transition: all 0.3s ease;
            }
            
            .request-card:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(255, 127, 0, 0.15);
            }
            
            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 12px;
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .card-id {
                font-weight: bold;
                color: #ff7f00;
                font-size: 1rem;
            }
            
            .card-status {
                margin-left: auto;
            }
            
            .card-row {
                display: flex;
                margin-bottom: 8px;
                align-items: flex-start;
                gap: 8px;
            }
            
            .card-label {
                font-weight: 600;
                color: #666;
                min-width: 70px;
                font-size: 0.8rem;
                flex-shrink: 0;
            }
            
            .card-value {
                flex: 1;
                font-size: 0.85rem;
                word-break: break-word;
            }
            
            .card-actions {
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid rgba(255, 127, 0, 0.2);
            }
            
            .card-actions .status-update {
                flex-direction: column;
                gap: 8px;
            }
            
            .card-actions .status-select {
                width: 100%;
                padding: 8px 10px;
                font-size: 0.8rem;
            }
            
            .card-actions .btn-sm {
                width: 100%;
                padding: 8px 12px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 3px;
            }
            
            .header {
                padding: 10px 8px;
                margin-bottom: 12px;
            }
            
            .header h1 {
                font-size: 1.1rem;
                text-align: center;
            }
            
            .header p {
                font-size: 0.8rem;
                text-align: center;
            }
            
            .admin-info {
                padding: 8px 10px;
                font-size: 0.7rem;
                text-align: center;
            }
            
            .filters {
                padding: 10px 8px;
                margin-bottom: 12px;
            }
            
            .form-group label {
                font-size: 0.8rem;
                margin-bottom: 6px;
            }
            
            .form-group input,
            .form-group select {
                padding: 8px 10px;
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
                margin-bottom: 12px;
            }
            
            .stat-card {
                padding: 10px 6px;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .stat-label {
                font-size: 0.65rem;
            }
            
            .request-card {
                padding: 12px 8px;
                margin-bottom: 10px;
            }
            
            .card-row {
                flex-direction: column;
                gap: 4px;
                margin-bottom: 6px;
            }
            
            .card-label {
                min-width: auto;
                margin-bottom: 2px;
                font-size: 0.75rem;
            }
            
            .card-value {
                font-size: 0.8rem;
            }
            
            .pagination a,
            .pagination span {
                padding: 6px 8px;
                font-size: 0.75rem;
            }
            
            .footer-info {
                padding: 12px;
                font-size: 0.75rem;
                margin-top: 20px;
            }
        }

        @media (max-width: 375px) {
            .container {
                padding: 2px;
            }
            
            .header h1 {
                font-size: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 4px;
            }
            
            .stat-card {
                padding: 8px 4px;
            }
            
            .stat-number {
                font-size: 1.1rem;
            }
            
            .stat-label {
                font-size: 0.6rem;
            }
            
            .request-card {
                padding: 8px 6px;
            }
        }

        /* Loading states and interactions */
        .btn:active {
            transform: translateY(0);
        }

        .form-group:focus-within label {
            color: #ff7f00;
            transition: color 0.3s ease;
        }

        /* Enhanced table styling */
        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 127, 0, 0.1);
        }

        /* Improved typography hierarchy */
        .footer-info {
            text-align: center;
            margin-top: 40px;
            color: #7f8c8d;
            font-size: 0.875rem;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🏛️ <?= htmlspecialchars($admin_info['city'] ?? $admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> Requests</h1>
            <p>Manage and track barangay service requests for your area</p>
            <div class="admin-info">
                <div><strong>📍 <?= htmlspecialchars($admin_info['city'] ?? $admin_city) ?></strong></div>
                <div>Barangay <?= htmlspecialchars($admin_barangay_number) ?></div>
                <a href="admin_dashboard.php" class="logout-btn">Back</a>
                <a href="logout.php" class="logout-btn">🚪 Logout</a>
                
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color: #ff7f00;"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">📊 Total Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ff9800;"><?= number_format($stats['pending']) ?></div>
                <div class="stat-label">⏳ Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ff7f00;"><?= number_format($stats['processing']) ?></div>
                <div class="stat-label">⚙️ Processing</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #4caf50;"><?= number_format($stats['ready']) ?></div>
                <div class="stat-label">✅ Ready</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #6b7280;"><?= number_format($stats['completed']) ?></div>
                <div class="stat-label">🎉 Completed</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" id="filterForm">
                <div class="filters-grid">
                    <div class="form-group">
                        <label>🔍 Search (Name/Phone)</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Enter name or phone number" id="searchInput">
                    </div>
                    <div class="form-group">
                        <label>📋 Status Filter</label>
                        <select name="status" id="statusFilter" onchange="autoFilter()">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                            <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>⚙️ Processing</option>
                            <option value="ready" <?= $status_filter === 'ready' ? 'selected' : '' ?>>✅ Ready</option>
                            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>🎉 Completed</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🛠️ Service Type</label>
                        <select name="service" id="serviceFilter" onchange="autoFilter()">
                            <option value="">All Services</option>
                            <?php foreach ($service_types as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $service_filter === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; justify-content: flex-end; align-items: flex-end;">
                        <a href="?" class="btn btn-clear">🧹 Clear All Filters</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Service Type</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: #7f8c8d;">
                                    📭 No requests found for <?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><strong>#<?= $request['id'] ?></strong></td>
                                    <td>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($request['full_name']) ?></div>
                                        <div class="contact-info">🎂 Born: <?= date('M j, Y', strtotime($request['birthdate'])) ?></div>
                                    </td>
                                    <td>
                                        <span class="service-type">
                                            <?= $service_types[$request['service_type']] ?? $request['service_type'] ?>
                                        </span>
                                    </td>
                                    <td class="contact-info">📱 <?= htmlspecialchars($request['contact_number']) ?></td>
                                    <td class="text-truncate" title="<?= htmlspecialchars($request['address']) ?>">
                                        📍 <?= htmlspecialchars($request['address']) ?>
                                    </td>
                                    <td class="text-truncate" title="<?= htmlspecialchars($request['purpose']) ?>">
                                        📝 <?= htmlspecialchars($request['purpose']) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?= $status_colors[$request['status']] ?? '#6b7280' ?>">
                                            <?= ucfirst($request['status']) ?>
                                        </span>
                                    </td>
                                    <td class="date-info">
                                        📅 <?= date('M j, Y', strtotime($request['created_at'])) ?><br>
                                        <small>🕐 <?= date('g:i A', strtotime($request['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <form method="POST" class="status-update">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                            <select name="new_status" class="status-select">
                                                <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                                <option value="processing" <?= $request['status'] === 'processing' ? 'selected' : '' ?>>⚙️ Processing</option>
                                                <option value="ready" <?= $request['status'] === 'ready' ? 'selected' : '' ?>>✅ Ready</option>
                                                <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>🎉 Completed</option>
                                                <option value="cancelled" <?= $request['status'] === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm">💾 Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Card View -->
            <div class="mobile-card-view">
                <?php if (empty($requests)): ?>
                    <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                        📭 No requests found for <?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> matching your criteria.
                    </div>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <div class="request-card">
                            <div class="card-header">
                                <div class="card-id">#<?= $request['id'] ?></div>
                                <div class="card-status">
                                    <span class="status-badge" style="background-color: <?= $status_colors[$request['status']] ?? '#6b7280' ?>">
                                        <?= ucfirst($request['status']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-row">
                                <div class="card-label">👤 Name:</div>
                                <div class="card-value">
                                    <strong><?= htmlspecialchars($request['full_name']) ?></strong><br>
                                    <small>🎂 Born: <?= date('M j, Y', strtotime($request['birthdate'])) ?></small>
                                </div>
                            </div>
                            
                            <div class="card-row">
                                <div class="card-label">🛠️ Service:</div>
                                <div class="card-value">
                                    <span class="service-type">
                                        <?= $service_types[$request['service_type']] ?? $request['service_type'] ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-row">
                                <div class="card-label">📞 Contact:</div>
                                <div class="card-value"><?= htmlspecialchars($request['contact_number']) ?></div>
                            </div>
                            
                            <div class="card-row">
                                <div class="card-label">📍 Address:</div>
                                <div class="card-value"><?= htmlspecialchars($request['address']) ?></div>
                            </div>
                            
                            <div class="card-row">
                                <div class="card-label">📝 Purpose:</div>
                                <div class="card-value"><?= htmlspecialchars($request['purpose']) ?></div>
                            </div>
                            
                            <div class="card-row">
                                <div class="card-label">📅 Created:</div>
                                <div class="card-value">
                                    <?= date('M j, Y', strtotime($request['created_at'])) ?><br>
                                    <small>🕐 <?= date('g:i A', strtotime($request['created_at'])) ?></small>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <form method="POST" class="status-update">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <select name="new_status" class="status-select">
                                        <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                        <option value="processing" <?= $request['status'] === 'processing' ? 'selected' : '' ?>>⚙️ Processing</option>
                                        <option value="ready" <?= $request['status'] === 'ready' ? 'selected' : '' ?>>✅ Ready</option>
                                        <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>🎉 Completed</option>
                                        <option value="cancelled" <?= $request['status'] === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm">💾 Update Status</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">← Previous</a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                ?>
                
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Footer Info -->
        <div class="footer-info">
            📊 Showing <?= count($requests) ?> of <?= number_format($total_records) ?> total requests for <?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?>
            <?php if ($total_pages > 1): ?>
                (Page <?= $page ?> of <?= $total_pages ?>)
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-filter function for select dropdowns
        function autoFilter() {
            document.getElementById('filterForm').submit();
        }

        // Auto-filter for search input with debounce
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                document.getElementById('filterForm').submit();
            }, 500); // 500ms delay after user stops typing
        });

        // Add loading states for form submissions
        document.querySelectorAll('.status-update').forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = this.querySelector('button[type="submit"]');
                button.disabled = true;
                button.textContent = '⏳ Updating...';
                
                // Re-enable after 3 seconds as fallback
                setTimeout(() => {
                    button.disabled = false;
                    button.textContent = '💾 Update';
                }, 3000);
            });
        });

        // Enhanced mobile interactions
        if (window.innerWidth <= 768) {
            // Make table rows more touch-friendly on mobile
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.minHeight = '60px';
            });
        }

        // Smooth scroll for pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        // Add subtle animations on load
        window.addEventListener('load', function() {
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Enhanced keyboard navigation
        document.addEventListener('keydown', function(e) {
            // Quick filter shortcuts
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'f':
                        e.preventDefault();
                        document.getElementById('searchInput').focus();
                        break;
                    case 'r':
                        e.preventDefault();
                        window.location.href = '?';
                        break;
                }
            }
        });

        // Initialize tooltips for truncated text
        document.querySelectorAll('.text-truncate').forEach(element => {
            element.addEventListener('mouseenter', function() {
                if (this.scrollWidth > this.clientWidth) {
                    this.style.cursor = 'help';
                }
            });
        });

        // Auto-refresh functionality (optional)
        let autoRefreshEnabled = false;
        let refreshInterval;

        function toggleAutoRefresh() {
            if (autoRefreshEnabled) {
                clearInterval(refreshInterval);
                autoRefreshEnabled = false;
            } else {
                refreshInterval = setInterval(() => {
                    if (!document.hidden) {
                        window.location.reload();
                    }
                }, 60000); // Refresh every minute
                autoRefreshEnabled = true;
            }
        }

        // Add auto-refresh toggle (you can add a button for this if needed)
        // toggleAutoRefresh();
    </script>
</body>
</html>