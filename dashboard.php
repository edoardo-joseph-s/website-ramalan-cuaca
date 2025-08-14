<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/User.php';

if (!isLoggedIn()) {
    redirectTo('auth/login.php');
}

$user = new User();
$user_id = getUserId();
$user_settings = $user->getSettings($user_id);
$favorites = $user->getFavorites($user_id);
$search_history = $user->getSearchHistory($user_id, 5);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'update_settings':
            $theme = sanitizeInput($_POST['theme']);
            $temperature_unit = sanitizeInput($_POST['temperature_unit']);
            
            $success = $user->updateSettings($user_id, $theme, $temperature_unit);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'remove_favorite':
            $favorite_id = (int)$_POST['favorite_id'];
            $success = $user->removeFavorite($user_id, $favorite_id);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'add_favorite':
            $location_name = sanitizeInput($_POST['location_name']);
            $latitude = (float)$_POST['latitude'];
            $longitude = (float)$_POST['longitude'];
            $kecamatan = sanitizeInput($_POST['kecamatan'] ?? '');
            $kota = sanitizeInput($_POST['kota'] ?? '');
            $provinsi = sanitizeInput($_POST['provinsi'] ?? '');
            
            $success = $user->addFavorite($user_id, $location_name, $latitude, $longitude, $kecamatan, $kota, $provinsi);
            echo json_encode(['success' => $success]);
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prakiraan Cuaca</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .dashboard-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        
        .dashboard-header h1 {
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }
        
        .dashboard-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }
        
        .dashboard-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .nav-btn {
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .nav-btn.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .dashboard-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dashboard-card h3 {
            color: #fff;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
        }
        
        .favorite-item, .history-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .favorite-item:hover, .history-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        
        .favorite-item h4, .history-item h4 {
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .favorite-item p, .history-item p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .favorite-actions {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        
        .btn-small {
            padding: 6px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: rgba(74, 144, 226, 0.3);
            color: #fff;
            border: 1px solid rgba(74, 144, 226, 0.5);
        }
        
        .btn-danger {
            background: rgba(231, 76, 60, 0.3);
            color: #fff;
            border: 1px solid rgba(231, 76, 60, 0.5);
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
        }
        
        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group label {
            color: #fff;
            font-weight: 500;
        }
        
        .form-group select {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
        }
        
        .form-group select option {
            background: #2c3e50;
            color: #fff;
        }
        
        .empty-state {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            padding: 2rem;
            font-style: italic;
        }
        
        .logout-btn {
            position: absolute;
            top: 2rem;
            right: 2rem;
            padding: 10px 15px;
            background: rgba(231, 76, 60, 0.2);
            color: #fff;
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(231, 76, 60, 0.3);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .logout-btn {
                position: static;
                margin-bottom: 1rem;
                display: inline-block;
            }
        }
    </style>
</head>
<body class="morning">
    <a href="auth/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </h1>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        </div>
        
        <div class="dashboard-nav">
            <a href="prakiraan-cuaca.php" class="nav-btn">
                <i class="fas fa-cloud-sun"></i> Cek Cuaca
            </a>
            <a href="#favorites" class="nav-btn" onclick="scrollToSection('favorites')">
                <i class="fas fa-heart"></i> Lokasi Favorit
            </a>
            <a href="#history" class="nav-btn" onclick="scrollToSection('history')">
                <i class="fas fa-history"></i> Riwayat
            </a>
            <a href="#settings" class="nav-btn" onclick="scrollToSection('settings')">
                <i class="fas fa-cog"></i> Pengaturan
            </a>
        </div>
        
        <div class="dashboard-grid">
            <!-- Lokasi Favorit -->
            <div class="dashboard-card" id="favorites">
                <h3>
                    <i class="fas fa-heart"></i>
                    Lokasi Favorit
                </h3>
                
                <?php if (empty($favorites)): ?>
                    <div class="empty-state">
                        <i class="fas fa-heart-broken" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        Belum ada lokasi favorit.
                        <br>Tambahkan lokasi favorit dari halaman pencarian cuaca.
                    </div>
                <?php else: ?>
                    <?php foreach ($favorites as $favorite): ?>
                        <div class="favorite-item">
                            <h4><?php echo htmlspecialchars($favorite['location_name']); ?></h4>
                            <p>
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($favorite['kecamatan'] . ', ' . $favorite['kota'] . ', ' . $favorite['provinsi']); ?>
                            </p>
                            <p>
                                <i class="fas fa-globe"></i>
                                <?php echo $favorite['latitude']; ?>, <?php echo $favorite['longitude']; ?>
                            </p>
                            
                            <div class="favorite-actions">
                                <a href="prakiraan-cuaca.php?lat=<?php echo $favorite['latitude']; ?>&lon=<?php echo $favorite['longitude']; ?>" class="btn-small btn-primary">
                                    <i class="fas fa-eye"></i> Lihat Cuaca
                                </a>
                                <button onclick="removeFavorite(<?php echo $favorite['id']; ?>)" class="btn-small btn-danger">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Riwayat Pencarian -->
            <div class="dashboard-card" id="history">
                <h3>
                    <i class="fas fa-history"></i>
                    Riwayat Pencarian
                </h3>
                
                <?php if (empty($search_history)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        Belum ada riwayat pencarian.
                    </div>
                <?php else: ?>
                    <?php foreach ($search_history as $history): ?>
                        <div class="history-item">
                            <h4><?php echo htmlspecialchars($history['location_name']); ?></h4>
                            <p>
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($history['kecamatan'] . ', ' . $history['kota'] . ', ' . $history['provinsi']); ?>
                            </p>
                            <p>
                                <i class="fas fa-clock"></i>
                                <?php echo date('d/m/Y H:i', strtotime($history['searched_at'])); ?>
                            </p>
                            
                            <div class="favorite-actions">
                                <a href="prakiraan-cuaca.php?lat=<?php echo $history['latitude']; ?>&lon=<?php echo $history['longitude']; ?>" class="btn-small btn-primary">
                                    <i class="fas fa-eye"></i> Lihat Cuaca
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pengaturan -->
            <div class="dashboard-card" id="settings">
                <h3>
                    <i class="fas fa-cog"></i>
                    Pengaturan
                </h3>
                
                <form class="settings-form" onsubmit="updateSettings(event)">
                    <div class="form-group">
                        <label for="theme">
                            <i class="fas fa-palette"></i> Tema
                        </label>
                        <select id="theme" name="theme">
                            <option value="light" <?php echo $user_settings['theme'] == 'light' ? 'selected' : ''; ?>>Terang</option>
                            <option value="dark" <?php echo $user_settings['theme'] == 'dark' ? 'selected' : ''; ?>>Gelap</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="temperature_unit">
                            <i class="fas fa-thermometer-half"></i> Satuan Suhu
                        </label>
                        <select id="temperature_unit" name="temperature_unit">
                            <option value="C" <?php echo $user_settings['temperature_unit'] == 'C' ? 'selected' : ''; ?>>Celsius (°C)</option>
                            <option value="F" <?php echo $user_settings['temperature_unit'] == 'F' ? 'selected' : ''; ?>>Fahrenheit (°F)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-small btn-primary" style="align-self: flex-start;">
                        <i class="fas fa-save"></i> Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({
                behavior: 'smooth'
            });
        }
        
        function removeFavorite(favoriteId) {
            if (confirm('Apakah Anda yakin ingin menghapus lokasi favorit ini?')) {
                fetch('dashboard.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_favorite&favorite_id=${favoriteId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menghapus lokasi favorit');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
            }
        }
        
        function updateSettings(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'update_settings');
            
            fetch('dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pengaturan berhasil disimpan!');
                } else {
                    alert('Gagal menyimpan pengaturan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
        
        // Set background based on time
        function setTimeBasedBackground() {
            const hour = new Date().getHours();
            const body = document.body;
            
            body.className = '';
            
            if (hour >= 5 && hour < 10) {
                body.classList.add('morning');
            } else if (hour >= 10 && hour < 15) {
                body.classList.add('day');
            } else if (hour >= 15 && hour < 18) {
                body.classList.add('evening');
            } else {
                body.classList.add('night');
            }
        }
        
        setTimeBasedBackground();
    </script>
</body>
</html>