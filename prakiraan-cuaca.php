<?php
// Include database and user class
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/User.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize User class
$user = new User($pdo);
$is_logged_in = isLoggedIn();
$user_id = getUserId();

// Initialize search limit check variable
$search_limit_check = ['allowed' => true, 'remaining' => 3];

// Fungsi untuk membaca file CSV (hanya data desa/kelurahan)
function bacaCSV($file) {
    $data = [];
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($line = fgets($handle)) !== FALSE) {
            $line = trim($line);
            if (!empty($line)) {
                $parts = explode(',', $line, 2);
                if (count($parts) == 2) {
                    $kode = trim($parts[0]);
                    // Filter hanya data desa/kelurahan (format: xx.xx.xx.xxxx)
                    if (preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $kode)) {
                        $data[] = [
                            'kode' => $kode,
                            'nama' => trim($parts[1])
                        ];
                    }
                }
            }
        }
        fclose($handle);
    }
    return $data;
}

// Fungsi untuk mencari wilayah berdasarkan kata kunci
function cariWilayah($data, $keyword) {
    $hasil = [];
    $keyword = strtolower($keyword);
    foreach ($data as $item) {
        if (strpos(strtolower($item['nama']), $keyword) !== false) {
            $hasil[] = $item;
        }
    }
    return $hasil;
}

// Inisialisasi variabel
$data = null;
$wilayah_data = bacaCSV(__DIR__ . '/kode_wilayah_tingkat_iv.csv');
$hasil_pencarian = [];
$keyword = '';
$kode_wilayah = '';

// AJAX endpoint untuk autocomplete
if (isset($_GET['ajax']) && $_GET['ajax'] === 'autocomplete' && isset($_GET['keyword'])) {
    header('Content-Type: application/json');
    $keyword = $_GET['keyword'];
    $hasil = cariWilayah($wilayah_data, $keyword);
    // Batasi hasil maksimal 10 untuk performa
    $hasil = array_slice($hasil, 0, 10);
    echo json_encode($hasil);
    exit;
}

// Proses pencarian (untuk fallback)
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    // Check search limit for guest users ONLY when actually searching
    $search_limit_check = $user->checkSearchLimit();
    if (!$search_limit_check['allowed']) {
        $error_message = $search_limit_check['message'];
    } else {
        $keyword = $_GET['keyword'];
        $hasil_pencarian = cariWilayah($wilayah_data, $keyword);
        
        // Jika hanya ada satu hasil, langsung redirect ke halaman cuaca
        if (count($hasil_pencarian) === 1) {
            $kode_wilayah = $hasil_pencarian[0]['kode'];
            header("Location: ?kode_wilayah=" . urlencode($kode_wilayah));
            exit;
        }
    }
}

// Proses pengambilan data cuaca
if (isset($_GET['kode_wilayah']) && !empty($_GET['kode_wilayah'])) {
    // Check search limit for guest users ONLY when actually getting weather data
    $search_limit_check = $user->checkSearchLimit();
    if (!$search_limit_check['allowed']) {
        $error_message = $search_limit_check['message'];
    } else {
        $kode_wilayah = $_GET['kode_wilayah'];
        $api_url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$kode_wilayah}";
        $response_body = @file_get_contents($api_url);
        
        // Check if fail
        if ($response_body === false) {
            $error_message = "ERROR: Gagal mengambil data.";
        } else {
            // Decode String JSON
            $data = json_decode($response_body, true);
            
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                $error_message = "ERROR: Data bukan format JSON yang valid. " . htmlspecialchars(json_last_error_msg());
            } else {
                // Save search history if data is successfully retrieved
                if (isset($data['data']) && !empty($data['data'])) {
                    $location_data = $data['data'][0]['lokasi'];
                    $user->addSearchHistory(
                        $location_data['desa'],
                        $location_data['lat'],
                        $location_data['lon'],
                        $location_data['kecamatan'],
                        $location_data['kota'],
                        $location_data['provinsi'],
                        $user_id
                    );
                }
            }
        }
    }
}

// Handle direct access with lat/lon (from favorites)
if (isset($_GET['lat']) && isset($_GET['lon']) && !isset($_GET['kode_wilayah'])) {
    $lat = $_GET['lat'];
    $lon = $_GET['lon'];
    
    // Find the closest location in CSV data
    $closest_location = null;
    $min_distance = PHP_FLOAT_MAX;
    
    foreach ($wilayah_data as $location) {
        // For this demo, we'll use a simple approach
        // In production, you'd want to use a proper geocoding service
        $distance = abs($lat - 0) + abs($lon - 0); // Placeholder calculation
        if ($distance < $min_distance) {
            $min_distance = $distance;
            $closest_location = $location;
        }
    }
    
    if ($closest_location) {
        $kode_wilayah = $closest_location['kode'];
        $api_url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$kode_wilayah}";
        $response_body = @file_get_contents($api_url);
        
        if ($response_body !== false) {
            $data = json_decode($response_body, true);
            if ($data !== null && json_last_error() === JSON_ERROR_NONE) {
                // Save search history
                if (isset($data['data']) && !empty($data['data'])) {
                    $location_data = $data['data'][0]['lokasi'];
                    $user->addSearchHistory(
                        $location_data['desa'],
                        $location_data['lat'],
                        $location_data['lon'],
                        $location_data['kecamatan'],
                        $location_data['kota'],
                        $location_data['provinsi'],
                        $user_id
                    );
                }
            }
        }
    }
}

// Set header
header("Content-Type: text/html; charset=utf-8");

// Include template header
include 'templates/header.php';

// Include template search form
include 'templates/search_form.php';

// Include template search results
include 'templates/search_results.php';

// Show favorite locations for logged in users
if ($is_logged_in) {
    $favorites = $user->getFavorites($user_id);
    if (!empty($favorites)) {
        echo '<div class="container">';
        echo '<div class="favorites-section">';
        echo '<h3><i class="fas fa-heart"></i> Lokasi Favorit</h3>';
        echo '<div class="favorites-grid">';
        foreach ($favorites as $favorite) {
            echo '<div class="favorite-card">';
            echo '<h4>' . htmlspecialchars($favorite['location_name']) . '</h4>';
            echo '<div class="favorite-actions">';
            echo '<a href="?lat=' . urlencode($favorite['latitude']) . '&lon=' . urlencode($favorite['longitude']) . '" class="btn-favorite-view">';
            echo '<i class="fas fa-eye"></i> Lihat Cuaca';
            echo '</a>';
            echo '<button class="btn-favorite-remove" data-id="' . $favorite['id'] . '">';
            echo '<i class="fas fa-trash"></i>';
            echo '</button>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

// Include template weather info
include 'templates/weather_info.php';

?>

<script>
// Search limit timer variables
let limitTimer = null;
let limitCountdown = null;

// Handle add to favorite button
document.addEventListener('DOMContentLoaded', function() {
    // Check search limit status on page load
    checkSearchLimitStatus();
    
    // Update search limit status every 30 seconds
    limitTimer = setInterval(checkSearchLimitStatus, 30000);
    
    const addToFavoriteBtn = document.getElementById('addToFavoriteBtn');
    const removeFavoriteBtns = document.querySelectorAll('.btn-favorite-remove');
    
    // Add to favorite functionality
    if (addToFavoriteBtn) {
        addToFavoriteBtn.addEventListener('click', function() {
            const lat = this.getAttribute('data-lat');
            const lon = this.getAttribute('data-lon');
            const name = this.getAttribute('data-name');
            
            fetch('ajax_handler.php', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/x-www-form-urlencoded',
                 },
                 body: `action=add_favorite&latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lon)}&location_name=${encodeURIComponent(name)}`
             })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = '<i class="fas fa-check"></i> Ditambahkan';
                    this.classList.add('added');
                    this.disabled = true;
                    
                    // Show success message
                    showMessage('Lokasi berhasil ditambahkan ke favorit!', 'success');
                } else {
                    showMessage(data.message || 'Gagal menambahkan ke favorit', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Terjadi kesalahan saat menambahkan ke favorit', 'error');
            });
        });
    }
    
    // Remove from favorite functionality
    removeFavoriteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const favoriteId = this.getAttribute('data-id');
            
            if (confirm('Apakah Anda yakin ingin menghapus lokasi ini dari favorit?')) {
                fetch('ajax_handler.php', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/x-www-form-urlencoded',
                     },
                     body: `action=remove_favorite&favorite_id=${encodeURIComponent(favoriteId)}`
                 })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.favorite-card').remove();
                        showMessage('Lokasi berhasil dihapus dari favorit!', 'success');
                        
                        // Check if favorites grid is empty
                        const favoritesGrid = document.querySelector('.favorites-grid');
                        if (favoritesGrid && favoritesGrid.children.length === 0) {
                            document.querySelector('.favorites-section').remove();
                        }
                    } else {
                        showMessage(data.message || 'Gagal menghapus dari favorit', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Terjadi kesalahan saat menghapus dari favorit', 'error');
                });
            }
        });
    });
    
    // Show message function
    function showMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            ${type === 'success' ? 'background: linear-gradient(135deg, #4CAF50, #45a049);' : 'background: linear-gradient(135deg, #f44336, #d32f2f);'}
        `;
        messageDiv.textContent = message;
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(messageDiv);
            }, 300);
        }, 3000);
    }
});

// Check search limit status
function checkSearchLimitStatus() {
    fetch('api/search_limit_status.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSearchLimitDisplay(data.data);
        }
    })
    .catch(error => {
        console.error('Error checking search limit:', error);
    });
}

// Update search limit display
function updateSearchLimitDisplay(limitData) {
    const limitInfo = document.getElementById('searchLimitInfo');
    if (!limitInfo) return;
    
    if (limitData.remaining === 'unlimited') {
        limitInfo.style.display = 'none';
        return;
    }
    
    if (limitData.allowed) {
        limitInfo.innerHTML = `
            <div class="limit-info">
                <i class="fas fa-search"></i>
                <span>Pencarian tersisa: <strong>${limitData.remaining}</strong></span>
            </div>
        `;
        limitInfo.className = 'search-limit-info allowed';
    } else if (limitData.time_remaining) {
        limitInfo.innerHTML = `
            <div class="limit-info">
                <i class="fas fa-clock"></i>
                <span>Batas pencarian tercapai. Reset dalam:</span>
                <div class="countdown" id="countdown">
                    <span id="hours">${limitData.time_remaining.hours.toString().padStart(2, '0')}</span>:
                    <span id="minutes">${limitData.time_remaining.minutes.toString().padStart(2, '0')}</span>:
                    <span id="seconds">${limitData.time_remaining.seconds.toString().padStart(2, '0')}</span>
                </div>
            </div>
        `;
        limitInfo.className = 'search-limit-info blocked';
        
        // Start countdown
        startCountdown(limitData.time_remaining.total_seconds);
    } else {
        limitInfo.innerHTML = `
            <div class="limit-info">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Batas pencarian tercapai. Silakan login untuk melanjutkan.</span>
            </div>
        `;
        limitInfo.className = 'search-limit-info blocked';
    }
    
    limitInfo.style.display = 'block';
}

// Start countdown timer
function startCountdown(totalSeconds) {
    if (limitCountdown) {
        clearInterval(limitCountdown);
    }
    
    let remaining = totalSeconds;
    
    limitCountdown = setInterval(() => {
        remaining--;
        
        if (remaining <= 0) {
            clearInterval(limitCountdown);
            // Refresh the page to update limit status
            location.reload();
            return;
        }
        
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;
        
        const hoursEl = document.getElementById('hours');
        const minutesEl = document.getElementById('minutes');
        const secondsEl = document.getElementById('seconds');
        
        if (hoursEl) hoursEl.textContent = hours.toString().padStart(2, '0');
        if (minutesEl) minutesEl.textContent = minutes.toString().padStart(2, '0');
        if (secondsEl) secondsEl.textContent = seconds.toString().padStart(2, '0');
    }, 1000);
}

// Clean up timers when page unloads
window.addEventListener('beforeunload', function() {
    if (limitTimer) clearInterval(limitTimer);
    if (limitCountdown) clearInterval(limitCountdown);
});
</script>

<style>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>

<?php include 'templates/footer.php'; ?>

<?php

// Debugging $data (dikomentari)
/*
echo "<pre>";
print_r($data);
echo "</pre>";
*/
?>