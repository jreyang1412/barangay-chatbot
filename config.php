<?php
// config.php - Database configuration with authentication functions

// Session settings must be configured before session_start
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Set security headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
}
setSecurityHeaders();

// Database connection
$host = 'sql308.infinityfree.com';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';
$database = 'if0_38484017_barangay_chatbot';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Utility functions

function generateUserId() {
    return 'user_' . uniqid();
}

function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'user';
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['user_type'] === 'admin';
}

function requireUserAuth() {
    if (!isUserLoggedIn()) {
        header("Location: user_login.php");
        exit();
    }
}

function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit();
    }
}

function logActivity($pdo, $userType, $userId, $action, $details = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_type, user_id, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userType,
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

function getUserInfo($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAdminInfo($pdo, $adminId) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createUserSession($pdo, $userId) {
    $token = generateSessionToken();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (user_id, user_type, session_token, expires_at) 
        VALUES (?, 'user', ?, ?)
    ");
    $stmt->execute([$userId, $token, $expiresAt]);
    
    return $token;
}

function createAdminSession($pdo, $adminId) {
    $token = generateSessionToken();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+8 hours'));
    
    $stmt = $pdo->prepare("
        INSERT INTO admin_sessions (admin_id, session_token, expires_at) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$adminId, $token, $expiresAt]);
    
    return $token;
}

function validateUserSession($pdo, $token) {
    $stmt = $pdo->prepare("
        SELECT us.*, u.* FROM user_sessions us
        JOIN users u ON us.user_id = u.id
        WHERE us.session_token = ? AND us.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function validateAdminSession($pdo, $token) {
    $stmt = $pdo->prepare("
        SELECT ads.*, a.* FROM admin_sessions ads
        JOIN admins a ON ads.admin_id = a.id
        WHERE ads.session_token = ? AND ads.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function logoutUser($pdo, $userId = null) {
    if ($userId) {
        logActivity($pdo, 'user', $userId, 'LOGOUT');
    }
    session_destroy();
    header("Location: user_login.php");
    exit();
}

function logoutAdmin($pdo, $adminId = null) {
    if ($adminId) {
        logActivity($pdo, 'admin', $adminId, 'LOGOUT');
    }
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

function cleanupInactiveConversations($pdo) {
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_active = FALSE 
        WHERE user_id IN (
            SELECT user_id FROM conversations 
            WHERE last_activity < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND status = 'active'
        )
        AND sender_type = 'admin'
    ");
    $stmt->execute();

    $stmt = $pdo->prepare("
        UPDATE conversations 
        SET status = 'closed' 
        WHERE last_activity < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        AND status = 'active'
    ");
    $stmt->execute();
}

function updateActivity($pdo, $userId) {
    $stmt = $pdo->prepare("
        INSERT INTO conversations (user_id, last_activity, status) 
        VALUES (?, NOW(), 'active')
        ON DUPLICATE KEY UPDATE 
        last_activity = NOW(), 
        status = CASE WHEN status = 'closed' THEN 'active' ELSE status END
    ");
    $stmt->execute([$userId]);
}

function getMetroManilaCities() {
    return [
        'Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong', 'Manila',
        'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque', 'Pasay', 'Pasig',
        'Quezon City', 'San Juan', 'Taguig', 'Valenzuela'
    ];
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateMobileNumber($mobile) {
    return preg_match('/^09[0-9]{9}$/', $mobile);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

function sendEmail($to, $subject, $message) {
    return mail($to, $subject, $message);
}

function checkRateLimit($pdo, $identifier, $maxAttempts = 5, $timeWindow = 300) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempts 
        FROM audit_logs 
        WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->execute([$identifier, $timeWindow]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['attempts'] < $maxAttempts;
}
?>
