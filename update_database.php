<?php
// Update database for improved login system
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Updating database for improved login system...\n";
    
    // Add last_login column if it doesn't exist (SQLite compatible)
    try {
        $add_column = "ALTER TABLE users ADD COLUMN last_login DATETIME";
        $db->exec($add_column);
        echo "✓ Added last_login column to users table\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "✓ last_login column already exists\n";
        } else {
            echo "✓ last_login column already exists or added successfully\n";
        }
    }
    
    // Update all passwords to plain text (remove hashing)
    $users_to_update = [
        ['username' => 'admin', 'password' => 'admin'],
        ['username' => 'user1', 'password' => 'password123'],
        ['username' => 'user2', 'password' => 'password123'],
        ['username' => 'user3', 'password' => 'password123'],
        ['username' => 'testuser', 'password' => 'test123'],
        ['username' => 'demo', 'password' => 'demo123']
    ];
    
    echo "\nUpdating user passwords to plain text...\n";
    
    foreach ($users_to_update as $user) {
        $update_query = "UPDATE users SET password = ? WHERE username = ?";
        $stmt = $db->prepare($update_query);
        $result = $stmt->execute([$user['password'], $user['username']]);
        
        if ($result) {
            echo "✓ Updated password for user: {$user['username']}\n";
        } else {
            echo "✗ Failed to update password for user: {$user['username']}\n";
        }
    }
    
    echo "\n=== Database Update Complete ===\n";
    echo "All user passwords are now stored as plain text\n";
    echo "Test accounts available:\n";
    foreach ($users_to_update as $user) {
        echo "- Username: {$user['username']}, Password: {$user['password']}\n";
    }
    
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>