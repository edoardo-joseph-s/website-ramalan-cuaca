<?php
require_once 'config/database.php';
require_once 'classes/User.php';

// Initialize database and user class
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

echo "<h2>Membuat Akun User Test</h2>\n";
echo "<pre>\n";

// Create test users
$results = $user->createTestUsers();

foreach ($results as $result) {
    echo $result . "\n";
}

echo "\n=== Daftar Akun yang Tersedia ===\n";
echo "Admin: username=admin, password=admin\n";
echo "User 1: username=user1, password=password123\n";
echo "User 2: username=user2, password=password123\n";
echo "User 3: username=user3, password=password123\n";
echo "Test User: username=testuser, password=test123\n";
echo "Demo User: username=demo, password=demo123\n";
echo "\nSemua akun sudah siap untuk digunakan!\n";
echo "</pre>\n";
?>