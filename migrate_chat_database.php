<?php
// migrate_chat_database.php - Run this ONCE to fix existing data
// This script will update your existing chat data to use the new format

// Database connection
try {
    $pdo = new PDO("mysql:host=sql308.infinityfree.com;dbname=if0_38484017_barangay_chatbot", 
                   "if0_38484017", "8QPEk7NCVncLbL");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

echo "Starting chat database migration...\n";

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // 1. Get all existing conversations with old format
    $stmt = $pdo->prepare("
        SELECT id, user_id 
        FROM conversations 
        WHERE user_id LIKE 'user_%'
    ");
    $stmt->execute();
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($conversations) . " conversations to migrate.\n";
    
    foreach ($conversations as $conv) {
        $oldUserId = $conv['user_id'];
        $oldConvId = $conv['id'];
        
        // Extract the actual user ID (remove 'user_' prefix)
        $actualUserId = str_replace('user_', '', $oldUserId);
        
        // Create new conversation ID format
        $newConvId = 'user_' . $actualUserId;
        
        echo "Migrating: $oldConvId -> $newConvId (User ID: $oldUserId -> $actualUserId)\n";
        
        // Check if user exists in users table
        $userCheckStmt = $pdo->prepare("SELECT id, first_name, last_name, email, barangay, city FROM users WHERE id = ?");
        $userCheckStmt->execute([$actualUserId]);
        $userInfo = $userCheckStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userInfo) {
            // User exists, get proper user name and location
            $userName = '';
            if ($userInfo['first_name'] && $userInfo['last_name']) {
                $userName = $userInfo['first_name'] . ' ' . $userInfo['last_name'];
            } elseif ($userInfo['email']) {
                $userName = $userInfo['email'];
            } else {
                $userName = 'User ' . $actualUserId;
            }
            
            $userBarangay = $userInfo['barangay'] ?: 'Unknown';
            $userCity = $userInfo['city'] ?: 'Unknown';
            
            echo "Found user: $userName from $userCity, Barangay $userBarangay\n";
        } else {
            // User doesn't exist, use defaults
            $userName = 'User ' . $actualUserId;
            $userBarangay = 'Unknown';
            $userCity = 'Unknown';
            echo "User not found in users table, using defaults\n";
        }
        
        // Check if new conversation ID already exists
        $existsStmt = $pdo->prepare("SELECT id FROM conversations WHERE id = ?");
        $existsStmt->execute([$newConvId]);
        
        if ($existsStmt->fetch()) {
            // New conversation already exists, need to merge or skip
            echo "WARNING: Conversation $newConvId already exists, skipping migration for $oldConvId\n";
            continue;
        }
        
        // Update conversation with new format
        $updateConvStmt = $pdo->prepare("
            UPDATE conversations 
            SET id = ?, user_id = ?, user_name = ?, city = ?, barangay = ?
            WHERE id = ?
        ");
        $updateConvStmt->execute([$newConvId, $actualUserId, $userName, $userCity, $userBarangay, $oldConvId]);
        
        // Update all messages for this conversation
        $updateMsgStmt = $pdo->prepare("
            UPDATE messages 
            SET conversation_id = ?, user_id = ?
            WHERE conversation_id = ?
        ");
        $updateMsgStmt->execute([$newConvId, $actualUserId, $oldConvId]);
        
        $msgCount = $updateMsgStmt->rowCount();
        echo "Updated $msgCount messages for conversation $newConvId\n";
    }
    
    // 2. Clean up any orphaned data
    echo "\nCleaning up orphaned data...\n";
    
    // Remove messages that don't have corresponding conversations
    $cleanupStmt = $pdo->prepare("
        DELETE m FROM messages m 
        LEFT JOIN conversations c ON m.conversation_id = c.id 
        WHERE c.id IS NULL
    ");
    $cleanupStmt->execute();
    $orphanedMessages = $cleanupStmt->rowCount();
    echo "Removed $orphanedMessages orphaned messages\n";
    
    // 3. Update any empty user_name fields
    $updateEmptyNamesStmt = $pdo->prepare("
        UPDATE conversations c
        LEFT JOIN users u ON c.user_id = u.id
        SET c.user_name = CASE
            WHEN u.first_name IS NOT NULL AND u.last_name IS NOT NULL 
                THEN CONCAT(u.first_name, ' ', u.last_name)
            WHEN u.email IS NOT NULL 
                THEN u.email
            ELSE CONCAT('User ', c.user_id)
        END
        WHERE c.user_name IS NULL OR c.user_name = ''
    ");
    $updateEmptyNamesStmt->execute();
    $updatedNames = $updateEmptyNamesStmt->rowCount();
    echo "Updated $updatedNames empty user names\n";
    
    // 4. Update any empty city/barangay fields
    $updateLocationStmt = $pdo->prepare("
        UPDATE conversations c
        LEFT JOIN users u ON c.user_id = u.id
        SET 
            c.city = COALESCE(NULLIF(c.city, ''), u.city, 'Unknown'),
            c.barangay = COALESCE(NULLIF(c.barangay, ''), u.barangay, 'Unknown')
        WHERE (c.city IS NULL OR c.city = '' OR c.city = 'Unknown')
           OR (c.barangay IS NULL OR c.barangay = '' OR c.barangay = 'Unknown')
    ");
    $updateLocationStmt->execute();
    $updatedLocations = $updateLocationStmt->rowCount();
    echo "Updated $updatedLocations location fields\n";
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Summary:\n";
    echo "- Migrated " . count($conversations) . " conversations\n";
    echo "- Removed $orphanedMessages orphaned messages\n";
    echo "- Updated $updatedNames user names\n";
    echo "- Updated $updatedLocations location fields\n";
    
    // Show final statistics
    $finalStatsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_conversations,
            COUNT(CASE WHEN user_id NOT LIKE 'user_%' THEN 1 END) as new_format_conversations,
            COUNT(CASE WHEN user_id LIKE 'user_%' THEN 1 END) as old_format_conversations
        FROM conversations
    ");
    $finalStatsStmt->execute();
    $stats = $finalStatsStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nFinal Database Status:\n";
    echo "- Total conversations: " . $stats['total_conversations'] . "\n";
    echo "- New format (user_id as number): " . $stats['new_format_conversations'] . "\n";
    echo "- Old format (user_id with 'user_' prefix): " . $stats['old_format_conversations'] . "\n";
    
    if ($stats['old_format_conversations'] > 0) {
        echo "\n⚠️ Warning: " . $stats['old_format_conversations'] . " conversations still use old format.\n";
        echo "You may need to run this migration again or check for conflicts.\n";
    }
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
}

echo "\nMigration script completed.\n";
echo "You can now use the updated chat handlers.\n";
?>

<!-- 
To run this migration:

1. Create a file called migrate_chat_database.php on your server
2. Copy this code into it
3. Run it once via browser: https://yoursite.com/migrate_chat_database.php
4. After successful migration, delete the migration file for security
5. The migration will:
   - Convert user_1234567890 -> 1234567890 (actual user ID)
   - Convert conv_1234567890_timestamp -> user_1234567890 (consistent conversation ID)
   - Link conversations to actual user records
   - Clean up orphaned data
   - Update user names and locations from users table

WARNING: Make a database backup before running this migration!
-->