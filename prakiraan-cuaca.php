<?php
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
    $keyword = $_GET['keyword'];
    $hasil_pencarian = cariWilayah($wilayah_data, $keyword);
    
    // Jika hanya ada satu hasil, langsung redirect ke halaman cuaca
    if (count($hasil_pencarian) === 1) {
        $kode_wilayah = $hasil_pencarian[0]['kode'];
        header("Location: ?kode_wilayah=" . urlencode($kode_wilayah));
        exit;
    }
}

// Proses pengambilan data cuaca
if (isset($_GET['kode_wilayah']) && !empty($_GET['kode_wilayah'])) {
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

// Include template weather info
include 'templates/weather_info.php';

// Include template footer
include 'templates/footer.php';

// Debugging $data (dikomentari)
/*
echo "<pre>";
print_r($data);
echo "</pre>";
*/
?>