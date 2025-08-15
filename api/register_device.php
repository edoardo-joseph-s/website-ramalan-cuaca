<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['deviceId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    $database = new Database();
    $pdo = $database->getConnection();
    $user = new User($pdo);
    
    $deviceId = $input['deviceId'];
    $userAgent = $input['userAgent'] ?? '';
    $screenResolution = $input['screenResolution'] ?? '';
    $lastSeen = $input['lastSeen'] ?? date('Y-m-d H:i:s');
    $sessionId = session_id();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // For guest users, we'll track devices differently
    // Create guest_devices table if not exists
    $createTableQuery = "CREATE TABLE IF NOT EXISTS guest_devices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        device_id TEXT UNIQUE NOT NULL,
        session_id TEXT,
        user_agent TEXT,
        screen_resolution TEXT,
        ip_address TEXT,
        search_count INTEGER DEFAULT 0,
        last_reset DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_used DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTableQuery);
    
    // Check if device already exists
    $checkQuery = "SELECT * FROM guest_devices WHERE device_id = :device_id";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':device_id', $deviceId);
    $checkStmt->execute();
    $existingDevice = $checkStmt->fetch();
    
    if ($existingDevice) {
        // Update existing device
        $updateQuery = "UPDATE guest_devices SET session_id = :session_id, last_used = datetime('now') WHERE device_id = :device_id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':session_id', $sessionId);
        $updateStmt->bindParam(':device_id', $deviceId);
        $updateStmt->execute();
    } else {
        // Insert new device
        $insertQuery = "INSERT INTO guest_devices (device_id, session_id, user_agent, screen_resolution, ip_address) VALUES (:device_id, :session_id, :user_agent, :screen_resolution, :ip_address)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->bindParam(':device_id', $deviceId);
        $insertStmt->bindParam(':session_id', $sessionId);
        $insertStmt->bindParam(':user_agent', $userAgent);
        $insertStmt->bindParam(':screen_resolution', $screenResolution);
        $insertStmt->bindParam(':ip_address', $ipAddress);
        $insertStmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Device registered successfully',
        'deviceId' => $deviceId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>