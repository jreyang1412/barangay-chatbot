<?php
// simple_diagnostic.php - Check your current database structure
// Upload this file and run it to see what you already have

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=sql308.infinityfree.com;dbname=if0_38484017_barangay_chatbot", 
                   "if0_38484017", "8QPEk7NCVncLbL");
    
    echo "<h2>Database Connection: ✅ Success</h2>";
    
    // Check messages table structure
    echo "<h3>Current Messages Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $hasImageColumns = [];
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
        
        // Track image-related columns
        if (in_array($column['Field'], ['file_path', 'message_type', 'file_name', 'file_size', 'file_type', 'thumbnail_path'])) {
            $hasImageColumns[$column['Field']] = true;
        }
    }
    echo "</table>";
    
    // Check what image columns exist
    echo "<h3>Image Upload Readiness:</h3>";
    $imageColumns = ['file_path', 'message_type', 'file_name', 'file_size', 'file_type', 'thumbnail_path'];
    foreach ($imageColumns as $col) {
        echo $col . ": " . (isset($hasImageColumns[$col]) ? "✅ Exists" : "❌ Missing") . "<br>";
    }
    
    // Check if file_attachments table exists
    echo "<h3>File Attachments Table:</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE file_attachments");
        echo "✅ file_attachments table exists<br>";
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Column</th><th>Type</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "❌ file_attachments table does not exist<br>";
        echo "Error: " . $e->getMessage() . "<br>";
    }
    
    // Show missing columns to add
    echo "<h3>SQL Commands to Run:</h3>";
    $missingColumns = [];
    foreach ($imageColumns as $col) {
        if (!isset($hasImageColumns[$col])) {
            $missingColumns[] = $col;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "<strong>Run these commands one by one:</strong><br>";
        echo "<code>";
        
        if (!isset($hasImageColumns['message_type'])) {
            echo "ALTER TABLE messages ADD COLUMN message_type ENUM('text', 'image', 'location') DEFAULT 'text' AFTER sender_type;<br>";
        }
        if (!isset($hasImageColumns['file_name'])) {
            echo "ALTER TABLE messages ADD COLUMN file_name VARCHAR(255) NULL AFTER " . (isset($hasImageColumns['file_path']) ? 'file_path' : 'message') . ";<br>";
        }
        if (!isset($hasImageColumns['file_size'])) {
            echo "ALTER TABLE messages ADD COLUMN file_size INT NULL AFTER " . (isset($hasImageColumns['file_name']) ? 'file_name' : 'message') . ";<br>";
        }
        if (!isset($hasImageColumns['file_type'])) {
            echo "ALTER TABLE messages ADD COLUMN file_type VARCHAR(100) NULL AFTER " . (isset($hasImageColumns['file_size']) ? 'file_size' : 'message') . ";<br>";
        }
        if (!isset($hasImageColumns['thumbnail_path'])) {
            echo "ALTER TABLE messages ADD COLUMN thumbnail_path VARCHAR(500) NULL AFTER " . (isset($hasImageColumns['file_type']) ? 'file_type' : 'file_path') . ";<br>";
        }
        
        echo "</code>";
    } else {
        echo "✅ All required columns exist!";
    }
    
    // Test file upload capabilities
    echo "<h3>PHP Upload Settings:</h3>";
    echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "<br>";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
    echo "post_max_size: " . ini_get('post_max_size') . "<br>";
    echo "memory_limit: " . ini_get('memory_limit') . "<br>";
    echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
    
    // Check GD extension
    echo "<h3>Image Processing:</h3>";
    if (extension_loaded('gd')) {
        echo "✅ GD Extension available<br>";
        $gd_info = gd_info();
        echo "JPEG Support: " . ($gd_info['JPEG Support'] ? 'Yes' : 'No') . "<br>";
        echo "PNG Support: " . ($gd_info['PNG Support'] ? 'Yes' : 'No') . "<br>";
    } else {
        echo "❌ GD Extension not available<br>";
    }
    
    // Check upload directory
    echo "<h3>Upload Directory:</h3>";
    $uploadDir = 'uploads/images/';
    if (is_dir($uploadDir)) {
        echo "✅ Upload directory exists<br>";
        echo "Writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";
    } else {
        echo "❌ Upload directory missing<br>";
        echo "Need to create: uploads/images/<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>Database Connection: ❌ Failed</h2>";
    echo "Error: " . $e->getMessage();
}
?>

<!-- Simple test form -->
<h3>Test Image Upload (if ready):</h3>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_image" accept="image/*">
    <input type="submit" name="test_upload" value="Test Upload">
</form>

<?php
if (isset($_POST['test_upload']) && isset($_FILES['test_image'])) {
    echo "<h4>Upload Test Result:</h4>";
    
    if ($_FILES['test_image']['error'] === UPLOAD_ERR_OK) {
        echo "✅ File uploaded successfully to temporary location<br>";
        echo "File name: " . $_FILES['test_image']['name'] . "<br>";
        echo "File size: " . $_FILES['test_image']['size'] . " bytes<br>";
        echo "File type: " . $_FILES['test_image']['type'] . "<br>";
        
        // Try to create upload directory
        $uploadDir = 'uploads/images/';
        if (!is_dir($uploadDir)) {
            if (mkdir($uploadDir, 0755, true)) {
                echo "✅ Created upload directory<br>";
            } else {
                echo "❌ Failed to create upload directory<br>";
            }
        }
        
        // Try to move file
        $filename = time() . '_test.jpg';
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['test_image']['tmp_name'], $destination)) {
            echo "✅ File moved to: " . $destination . "<br>";
            echo "<img src='" . $destination . "' style='max-width: 200px;'><br>";
        } else {
            echo "❌ Failed to move file to destination<br>";
        }
        
    } else {
        echo "❌ Upload error code: " . $_FILES['test_image']['error'] . "<br>";
    }
}
?>