<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $pdo = $database->getConnection();
    $user = new User($pdo);
    
    // Get device_id from request if available
    $device_id = $_GET['device_id'] ?? null;
    
    // Get search limit status
    $status = $user->getSearchLimitStatus($device_id);
    
    echo json_encode([
        'success' => true,
        'data' => $status
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan saat mengambil status limit pencarian'
    ]);
}
?>