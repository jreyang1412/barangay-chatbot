<?php
// check_db.php - Check your database structure and generate needed SQL commands
require_once 'config.php';

echo "<h2>Database Structure Check</h2>";

try {
    // Check conversations table structure
    echo "<h3>Conversations Table Structure:</h3>";
    $stmt = $pdo->prepare("DESCRIBE conversations");
    $stmt->execute();
    $convColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($convColumns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check messages table structure
    echo "<h3>Messages Table Structure:</h3>";
    $stmt = $pdo->prepare("DESCRIBE messages");
    $stmt->execute();
    $msgColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($msgColumns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check what columns are missing and generate SQL
    echo "<h3>Missing Columns Analysis:</h3>";
    
    $convColumnNames = array_column($convColumns, 'Field');
    $msgColumnNames = array_column($msgColumns, 'Field');
    
    $sqlCommands = [];
    
    // Check conversations table
    echo "<h4>Conversations Table:</h4>";
    if (!in_array('assigned_admin_id', $convColumnNames)) {
        $sqlCommands[] = "ALTER TABLE conversations ADD COLUMN `assigned_admin_id` INT NULL;";
        echo "❌ Missing: assigned_admin_id<br>";
    } else {
        echo "✅ Has: assigned_admin_id<br>";
    }
    
    if (!in_array('last_activity', $convColumnNames)) {
        $sqlCommands[] = "ALTER TABLE conversations ADD COLUMN `last_activity` DATETIME NULL;";
        echo "❌ Missing: last_activity<br>";
    } else {
        echo "✅ Has: last_activity<br>";
    }
    
    if (!in_array('priority', $convColumnNames)) {
        $sqlCommands[] = "ALTER TABLE conversations ADD COLUMN `priority` ENUM('low','normal','high') DEFAULT 'normal';";
        echo "❌ Missing: priority<br>";
    } else {
        echo "✅ Has: priority<br>";
    }
    
    if (!in_array('user_name', $convColumnNames)) {
        $sqlCommands[] = "ALTER TABLE conversations ADD COLUMN `user_name` VARCHAR(255) NULL;";
        echo "❌ Missing: user_name<br>";
    } else {
        echo "✅ Has: user_name<br>";
    }
    
    if (!in_array('location_lat', $convColumnNames)) {
        $sqlCommands[] = "ALTER TABLE conversations ADD COLUMN `location_lat` DECIMAL(10, 8) NULL;";
        echo "❌ Missing: location_lat<br>";
    } else {
        echo "✅ Has: location_lat<br>";
    }
    
    if (!in_array('location_lng', $convColumnNames)) {
        $sqlCommands[] = "ALTER TABLE conversations ADD COLUMN `location_lng` DECIMAL(11, 8) NULL;";
        echo "❌ Missing: location_lng<br>";
    } else {
        echo "✅ Has: location_lng<br>";
    }
    
    // Check messages table
    echo "<h4>Messages Table:</h4>";
    if (!in_array('is_read_by_admin', $msgColumnNames)) {
        $sqlCommands[] = "ALTER TABLE messages ADD COLUMN `is_read_by_admin` BOOLEAN DEFAULT FALSE;";
        echo "❌ Missing: is_read_by_admin<br>";
    } else {
        echo "✅ Has: is_read_by_admin<br>";
    }
    
    if (!in_array('is_read_by_user', $msgColumnNames)) {
        $sqlCommands[] = "ALTER TABLE messages ADD COLUMN `is_read_by_user` BOOLEAN DEFAULT FALSE;";
        echo "❌ Missing: is_read_by_user<br>";
    } else {
        echo "✅ Has: is_read_by_user<br>";
    }
    
    if (!in_array('admin_id', $msgColumnNames)) {
        $sqlCommands[] = "ALTER TABLE messages ADD COLUMN `admin_id` INT NULL;";
        echo "❌ Missing: admin_id<br>";
    } else {
        echo "✅ Has: admin_id<br>";
    }
    
    if (!in_array('admin_name', $msgColumnNames)) {
        $sqlCommands[] = "ALTER TABLE messages ADD COLUMN `admin_name` VARCHAR(255) NULL;";
        echo "❌ Missing: admin_name<br>";
    } else {
        echo "✅ Has: admin_name<br>";
    }
    
    if (!in_array('is_active', $msgColumnNames)) {
        $sqlCommands[] = "ALTER TABLE messages ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE;";
        echo "❌ Missing: is_active<br>";
    } else {
        echo "✅ Has: is_active<br>";
    }
    
    // Show SQL commands needed
    if (!empty($sqlCommands)) {
        echo "<h3>SQL Commands to Run:</h3>";
        echo "<div style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; margin: 10px 0;'>";
        echo "<pre style='margin: 0;'>";
        foreach ($sqlCommands as $sql) {
            echo $sql . "\n";
        }
        echo "</pre>";
        echo "</div>";
        
        echo "<p><strong>Copy and paste the above SQL commands into your phpMyAdmin SQL tab to add the missing columns.</strong></p>";
    } else {
        echo "<h3>✅ All Required Columns Present!</h3>";
        echo "<p>Your database structure is complete for the chat system.</p>";
    }
    
    // Check for existing data
    echo "<h3>Data Check:</h3>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM conversations");
    $stmt->execute();
    $convCount = $stmt->fetch()['count'];
    echo "Conversations: " . $convCount . "<br>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages");
    $stmt->execute();
    $msgCount = $stmt->fetch()['count'];
    echo "Messages: " . $msgCount . "<br>";
    
    if ($convCount > 0) {
        $stmt = $pdo->prepare("SELECT * FROM conversations LIMIT 1");
        $stmt->execute();
        $sampleConv = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<h4>Sample Conversation Data:</h4>";
        echo "<pre>" . print_r($sampleConv, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; }
    .error { color: red; }
</style>