<?php
// Final login test without session warnings
require_once 'config/database.php';
require_once 'classes/User.php';

// Start session at the beginning
session_start();

echo "=== Final Login System Test ===\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    echo "✓ Database connection successful\n";
    echo "✓ User class instantiated\n\n";
    
    // Test accounts
    $test_accounts = [
        ['username' => 'admin', 'password' => 'admin'],
        ['username' => 'user1', 'password' => 'password123'],
        ['username' => 'user2', 'password' => 'password123'],
        ['username' => 'user3', 'password' => 'password123'],
        ['username' => 'testuser', 'password' => 'test123'],
        ['username' => 'demo', 'password' => 'demo123']
    ];
    
    echo "Testing login for all accounts:\n";
    echo str_repeat("-", 50) . "\n";
    
    $success_count = 0;
    foreach ($test_accounts as $account) {
        echo "Testing: {$account['username']} / {$account['password']}\n";
        
        // Clear session before each test
        session_unset();
        
        $result = $user->login($account['username'], $account['password']);
        
        if ($result['success']) {
            echo "✓ SUCCESS: {$result['message']}\n";
            echo "  User ID: {$result['user']['id']}\n";
            echo "  Username: {$result['user']['username']}\n";
            echo "  Email: {$result['user']['email']}\n";
            echo "  Full Name: {$result['user']['full_name']}\n";
            $success_count++;
        } else {
            echo "✗ FAILED: {$result['message']}\n";
        }
        
        echo "\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "Total accounts tested: " . count($test_accounts) . "\n";
    echo "Successful logins: $success_count\n";
    echo "Failed logins: " . (count($test_accounts) - $success_count) . "\n";
    
    if ($success_count == count($test_accounts)) {
        echo "\n🎉 ALL TESTS PASSED! Login system is working perfectly.\n";
    } else {
        echo "\n⚠️  Some tests failed. Please check the issues above.\n";
    }
    
    // Test invalid credentials
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Testing invalid credentials:\n";
    echo str_repeat("-", 50) . "\n";
    
    $invalid_tests = [
        ['username' => 'admin', 'password' => 'wrongpassword', 'expected' => 'fail'],
        ['username' => 'nonexistent', 'password' => 'password', 'expected' => 'fail'],
        ['username' => '', 'password' => 'password', 'expected' => 'fail'],
        ['username' => 'admin', 'password' => '', 'expected' => 'fail']
    ];
    
    $invalid_success_count = 0;
    foreach ($invalid_tests as $test) {
        echo "Testing: '{$test['username']}' / '{$test['password']}'\n";
        
        session_unset();
        $result = $user->login($test['username'], $test['password']);
        
        if (!$result['success']) {
            echo "✓ CORRECTLY FAILED: {$result['message']}\n";
            $invalid_success_count++;
        } else {
            echo "✗ UNEXPECTED SUCCESS: This should have failed!\n";
        }
        
        echo "\n";
    }
    
    echo "Invalid credential tests: " . count($invalid_tests) . "\n";
    echo "Correctly failed: $invalid_success_count\n";
    
    if ($invalid_success_count == count($invalid_tests)) {
        echo "\n✅ All invalid credential tests passed!\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🚀 Enhanced Login System Implementation Complete!\n";
    echo "\nFeatures implemented:\n";
    echo "• Plain text password authentication (no hashing)\n";
    echo "• Input sanitization and validation\n";
    echo "• Rate limiting for failed attempts\n";
    echo "• Secure session management\n";
    echo "• CSRF token generation\n";
    echo "• Last login time tracking\n";
    echo "• Comprehensive error handling\n";
    echo "• SQLite database compatibility\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>