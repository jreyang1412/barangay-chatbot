<?php
// Database configuration - YOUR ACTUAL CREDENTIALS
$host = 'sql308.infinityfree.com';
$dbname = 'if0_38484017_barangay_chatbot'; 
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ================== FORM VALIDATION FUNCTIONS (for user_forms.php) ==================

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateMobileNumber($number) {
    return preg_match('/^09[0-9]{9}$/', $number);
}

// Form file validation function (different from image upload validation)
function validateUploadedFile($file, $allowed_types, $max_size) {
    // Check file size
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    // Check if file is actually an image (for image files)
    if (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            return false;
        }
    }
    
    return true;
}

function getFileValidationMessage($field_info) {
    $max_size_mb = $field_info['max_size'] / (1024 * 1024);
    return "Allowed types: " . implode(', ', $field_info['allowed_types']) . ". Max size: {$max_size_mb}MB.";
}

function logActivity($pdo, $type, $user_id, $action, $details = '') {
    try {
        // Check if activity_logs table exists first
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'activity_logs'");
        $stmt->execute();
        
        if ($stmt->fetch()) {
            // Table exists, insert log
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_type, user_id, action, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            return $stmt->execute([$type, $user_id, $action, $details]);
        } else {
            // Table doesn't exist, create it first
            $createTable = "CREATE TABLE activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_type VARCHAR(50) NOT NULL,
                user_id INT NOT NULL,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $pdo->exec($createTable);
            
            // Now insert the log
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_type, user_id, action, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            return $stmt->execute([$type, $user_id, $action, $details]);
        }
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
        return false;
    }
}

// ================== CHATBOT FUNCTIONS (your existing functions) ==================

function getAdminConversations($pdo, $admin_id, $city, $barangay) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, COUNT(m.id) as unread_count
            FROM conversations c
            LEFT JOIN messages m ON c.id = m.conversation_id 
            WHERE c.city = ? AND c.barangay = ?
            GROUP BY c.id
            ORDER BY c.last_activity DESC
        ");
        $stmt->execute([$city, $barangay]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Generate unique filename for uploads
function generateUniqueFilename($originalFilename) {
    $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
    $timestamp = time();
    $randomString = substr(md5(uniqid(rand(), true)), 0, 8);
    return $timestamp . '_' . $randomString . '.' . $extension;
}

// Validate uploaded image file (for chatbot, different from form validation)
function validateUploadedImage($file) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'No file uploaded or upload error occurred';
        return $errors;
    }
    
    // Validate file type
    $allowedTypes = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes) && !in_array($file['type'], $allowedTypes)) {
        $errors[] = 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.';
    }
    
    // Validate file size (5MB limit)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size too large. Maximum 5MB allowed.';
    }
    
    // Validate file extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        $errors[] = 'Invalid file extension.';
    }
    
    return $errors;
}

// Create thumbnail image
function createImageThumbnail($sourcePath, $thumbnailPath, $maxWidth = 300, $maxHeight = 300) {
    try {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Calculate thumbnail dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $thumbWidth = round($sourceWidth * $ratio);
        $thumbHeight = round($sourceHeight * $ratio);
        
        // Create image resource based on type
        $sourceImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = imagecreatefromwebp($sourcePath);
                }
                break;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // Create thumbnail canvas
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled(
            $thumbImage, $sourceImage, 
            0, 0, 0, 0, 
            $thumbWidth, $thumbHeight, 
            $sourceWidth, $sourceHeight
        );
        
        // Save thumbnail
        $success = false;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $success = imagejpeg($thumbImage, $thumbnailPath, 85);
                break;
            case 'image/png':
                $success = imagepng($thumbImage, $thumbnailPath, 8);
                break;
            case 'image/gif':
                $success = imagegif($thumbImage, $thumbnailPath);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    $success = imagewebp($thumbImage, $thumbnailPath, 85);
                }
                break;
        }
        
        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Thumbnail creation failed: " . $e->getMessage());
        return false;
    }
}

// Ensure upload directories exist
function ensureUploadDirectories() {
    $directories = [
        '../uploads',
        '../uploads/images',
        '../uploads/images/thumbnails'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log("Failed to create directory: " . $dir);
                return false;
            }
        }
        
        // Ensure directory is writable
        if (!is_writable($dir)) {
            error_log("Directory not writable: " . $dir);
            return false;
        }
    }
    
    return true;
}

// Get file hash for duplicate detection
function getFileHash($filePath) {
    return hash_file('sha256', $filePath);
}

// Clean up old files (call this periodically)
function cleanupOldFiles($pdo, $daysOld = 30) {
    try {
        // Get files older than specified days
        $stmt = $pdo->prepare("
            SELECT fa.file_path, fa.thumbnail_path 
            FROM file_attachments fa
            JOIN messages m ON fa.message_id = m.id
            WHERE fa.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND m.is_active = FALSE
        ");
        $stmt->execute([$daysOld]);
        $oldFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $deletedCount = 0;
        foreach ($oldFiles as $file) {
            // Delete actual files
            if ($file['file_path'] && file_exists('../' . $file['file_path'])) {
                if (unlink('../' . $file['file_path'])) {
                    $deletedCount++;
                }
            }
            if ($file['thumbnail_path'] && file_exists('../' . $file['thumbnail_path'])) {
                unlink('../' . $file['thumbnail_path']);
            }
        }
        
        // Clean up database records
        $cleanupStmt = $pdo->prepare("
            DELETE fa FROM file_attachments fa
            JOIN messages m ON fa.message_id = m.id
            WHERE fa.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND m.is_active = FALSE
        ");
        $cleanupStmt->execute([$daysOld]);
        
        return $deletedCount;
        
    } catch (Exception $e) {
        error_log("Cleanup failed: " . $e->getMessage());
        return false;
    }
}

// Get upload statistics
function getUploadStats($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_files,
                SUM(file_size) as total_bytes,
                AVG(file_size) as avg_size,
                MAX(file_size) as max_size,
                MIN(file_size) as min_size
            FROM file_attachments
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Format bytes to human readable
        $stats['total_size_formatted'] = formatBytes($stats['total_bytes']);
        $stats['avg_size_formatted'] = formatBytes($stats['avg_size']);
        $stats['max_size_formatted'] = formatBytes($stats['max_size']);
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Failed to get upload stats: " . $e->getMessage());
        return false;
    }
}

// Format bytes to human readable format
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Validate image dimensions (optional)
function validateImageDimensions($filePath, $maxWidth = 4000, $maxHeight = 4000) {
    $imageInfo = getimagesize($filePath);
    if (!$imageInfo) {
        return false;
    }
    
    return ($imageInfo[0] <= $maxWidth && $imageInfo[1] <= $maxHeight);
}

// Create secure .htaccess for uploads directory
function createSecureHtaccess() {
    $htaccessContent = "# Prevent direct access to uploaded files\n";
    $htaccessContent .= "Options -Indexes\n";
    $htaccessContent .= "Options -ExecCGI\n";
    $htaccessContent .= "<FilesMatch \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n";
    $htaccessContent .= "    Order deny,allow\n";
    $htaccessContent .= "    Deny from all\n";
    $htaccessContent .= "</FilesMatch>\n";
    $htaccessContent .= "<FilesMatch \"\\.(jpg|jpeg|png|gif|webp)$\">\n";
    $htaccessContent .= "    Order allow,deny\n";
    $htaccessContent .= "    Allow from all\n";
    $htaccessContent .= "</FilesMatch>\n";
    
    $htaccessPath = '../uploads/.htaccess';
    return file_put_contents($htaccessPath, $htaccessContent) !== false;
}

// Check if GD library is available
function checkGDSupport() {
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $gdInfo = gd_info();
    return [
        'available' => true,
        'version' => $gdInfo['GD Version'] ?? 'Unknown',
        'jpeg_support' => $gdInfo['JPEG Support'] ?? false,
        'png_support' => $gdInfo['PNG Support'] ?? false,
        'gif_support' => $gdInfo['GIF Read Support'] ?? false,
        'webp_support' => $gdInfo['WebP Support'] ?? false
    ];
}

// ================== AUTO-CREATE REQUIRED TABLES ==================

// Create barangay_requests table if it doesn't exist
try {
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'barangay_requests'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $createTable = "CREATE TABLE barangay_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            service_type VARCHAR(100) NOT NULL,
            surname VARCHAR(100) NOT NULL,
            given_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100),
            birthdate DATE NOT NULL,
            address TEXT NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            purpose VARCHAR(255) NOT NULL,
            additional_info TEXT,
            event_date DATE NULL,
            uploaded_files JSON,
            status ENUM('pending', 'processing', 'approved', 'rejected', 'completed') DEFAULT 'pending',
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_service_type (service_type),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        )";
        $pdo->exec($createTable);
    }
} catch (Exception $e) {
    error_log("Table creation error: " . $e->getMessage());
}

// Ensure users table exists (basic structure)
try {
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'users'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $createTable = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            status ENUM('pending', 'verified', 'suspended') DEFAULT 'pending',
            is_active BOOLEAN DEFAULT TRUE,
            verification_token VARCHAR(255),
            reset_token VARCHAR(255),
            reset_token_expires DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_status (status)
        )";
        $pdo->exec($createTable);
    }
} catch (Exception $e) {
    error_log("Users table creation error: " . $e->getMessage());
}
?>