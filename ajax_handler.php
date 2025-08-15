<?php
require_once 'config/database.php';
require_once 'classes/User.php';

// Session is already started in database.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$pdo = $database->getConnection();
$user = new User($pdo);
$user_id = $_SESSION['user_id'];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_favorite':
            $latitude = $_POST['latitude'] ?? '';
            $longitude = $_POST['longitude'] ?? '';
            $location_name = $_POST['location_name'] ?? '';
            $kecamatan = $_POST['kecamatan'] ?? '';
            $kota = $_POST['kota'] ?? '';
            $provinsi = $_POST['provinsi'] ?? '';
            
            if (empty($latitude) || empty($longitude) || empty($location_name)) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                exit;
            }
            
            try {
                $result = $user->addFavorite($user_id, $latitude, $longitude, $location_name, $kecamatan, $kota, $provinsi);
                if ($result === true) {
                    echo json_encode(['success' => true, 'message' => 'Lokasi berhasil ditambahkan ke favorit']);
                } elseif ($result === 'exists') {
                    echo json_encode(['success' => false, 'message' => 'Lokasi sudah ada dalam daftar favorit']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lokasi sudah ada di favorit']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
            break;
            
        case 'remove_favorite':
            $favorite_id = $_POST['favorite_id'] ?? '';
            
            if (empty($favorite_id)) {
                echo json_encode(['success' => false, 'message' => 'ID favorit tidak valid']);
                exit;
            }
            
            try {
                $result = $user->removeFavorite($user_id, $favorite_id);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Lokasi berhasil dihapus dari favorit']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal menghapus lokasi dari favorit']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>