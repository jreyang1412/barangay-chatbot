<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_barangay']) || !isset($_SESSION['admin_city'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get admin's barangay and city information
$admin_barangay_number = $_SESSION['admin_barangay'];
$admin_city = $_SESSION['admin_city'];

try {
    // Build WHERE clause with barangay AND city filter - same logic as barangay_request.php
    $where_conditions = [
        "LOWER(TRIM(city)) = LOWER(TRIM(?))",
        "(TRIM(LEADING '0' FROM barangay) = TRIM(LEADING '0' FROM ?) OR LOWER(TRIM(barangay)) = LOWER(TRIM(?)))"
    ];
    $params = [$admin_city, $admin_barangay_number, $admin_barangay_number];
    
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Query to fetch users from the same city and barangay as the admin
    $sql = "SELECT 
                id,
                first_name,
                middle_name,
                last_name,
                email,
                mobile_number,
                city,
                barangay,
                status,
                is_active,
                created_at,
                updated_at
            FROM users 
            $where_clause
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug information (remove in production)
    $debug_info = [
        'admin_city' => $admin_city,
        'admin_barangay' => $admin_barangay_number,
        'total_users_found' => count($users),
        'sql_query' => $sql,
        'parameters' => $params
    ];
    
    echo json_encode([
        'success' => true, 
        'users' => $users,
        'debug' => $debug_info // Remove this in production
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'admin_info' => [
            'city' => $admin_city,
            'barangay' => $admin_barangay_number
        ]
    ]);
}
?>