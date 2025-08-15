<!-- Informasi Cuaca -->
<?php if (isset($data) && $data !== null): ?>
<div class="weather-card">
    <!-- Informasi Lokasi -->
    <?php if (isset($data["lokasi"]["desa"]) && isset($data["lokasi"]["kecamatan"])): ?>
        
        <!-- Current Weather Header -->
        <?php 
        // Get current weather (first forecast item)
        $current_weather = null;
        if (isset($data["data"][0]["cuaca"][0][0])) {
            $current_weather = $data["data"][0]["cuaca"][0][0];
        }
        ?>
        
        <?php if ($current_weather): ?>
        <div class="current-weather">
            <div class="current-header">
                <div class="current-location">
                    <h2><?php echo htmlspecialchars($data["lokasi"]["desa"]); ?></h2>
                    <?php if (isLoggedIn()): ?>
                        <button id="addToFavoriteBtn" class="favorite-btn" 
                                data-lat="<?php echo htmlspecialchars($data['lokasi']['lat'] ?? ''); ?>" 
                                data-lon="<?php echo htmlspecialchars($data['lokasi']['lon'] ?? ''); ?>" 
                                data-name="<?php echo htmlspecialchars($data['lokasi']['desa'] ?? ''); ?>"
                                data-kecamatan="<?php echo htmlspecialchars($data['lokasi']['kecamatan'] ?? ''); ?>"
                                data-kota="<?php echo htmlspecialchars($data['lokasi']['kotkab'] ?? ''); ?>"
                                data-provinsi="<?php echo htmlspecialchars($data['lokasi']['provinsi'] ?? ''); ?>">
                            <i class="fas fa-heart"></i> Tambah ke Favorit
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="current-temp"><?php echo htmlspecialchars($current_weather["t"] ?? "N/A"); ?>°C</div>
            <div class="current-desc"><?php echo htmlspecialchars($current_weather["weather_desc"] ?? "N/A"); ?></div>
            
            <div class="current-details">
                <div class="detail-item">
                    <div class="icon">💧</div>
                    <div class="label">Kelembapan</div>
                    <div class="value"><?php echo htmlspecialchars($current_weather["hu"] ?? "N/A"); ?>%</div>
                </div>
                <div class="detail-item">
                    <div class="icon">💨</div>
                    <div class="label">Kecepatan Angin</div>
                    <div class="value"><?php echo htmlspecialchars($current_weather["ws"] ?? "N/A"); ?> km/j</div>
                </div>
                <div class="detail-item">
                    <div class="icon">🧭</div>
                    <div class="label">Arah Angin</div>
                    <div class="value"><?php echo htmlspecialchars($current_weather["wd"] ?? "N/A"); ?></div>
                </div>
                <div class="detail-item">
                    <div class="icon">👁️</div>
                    <div class="label">Jarak Pandang</div>
                    <div class="value"><?php echo htmlspecialchars($current_weather["vs_text"] ?? "N/A"); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="location-info">
            <h2><i class="fas fa-map-marker-alt"></i> Informasi Lokasi</h2>
            <div class="location-info-grid">
                <div class="location-detail-item">
                    <strong><i class="fas fa-building"></i> Kecamatan</strong>
                    <p><?php echo htmlspecialchars($data["lokasi"]["kecamatan"] ?? "N/A"); ?></p>
                </div>
                <div class="location-detail-item">
                    <strong><i class="fas fa-city"></i> Kota/Kab</strong>
                    <p><?php echo htmlspecialchars($data["lokasi"]["kotkab"] ?? "N/A"); ?></p>
                </div>
                <div class="location-detail-item">
                    <strong><i class="fas fa-map"></i> Provinsi</strong>
                    <p><?php echo htmlspecialchars($data["lokasi"]["provinsi"] ?? "N/A"); ?></p>
                </div>
                <div class="location-detail-item">
                     <strong><i class="fas fa-crosshairs"></i> Koordinat</strong>
                     <p>Lat: <?php echo htmlspecialchars($data["lokasi"]["lat"] ?? "N/A"); ?></p>
                     <p>Lon: <?php echo htmlspecialchars($data["lokasi"]["lon"] ?? "N/A"); ?></p>
                 </div>
                 <div class="location-detail-item">
                     <strong><i class="fas fa-clock"></i> Timezone</strong>
                     <p><?php echo htmlspecialchars($data["lokasi"]["timezone"] ?? "N/A"); ?></p>
                 </div>
             </div>
        </div>

        <!-- Forecast Section -->
        <div class="forecast-section">
            <h3><i class="fas fa-calendar-alt"></i> Prakiraan Cuaca</h3>
            <?php if (isset($data["data"][0]["cuaca"]) && is_array($data["data"][0]["cuaca"])): ?>
                <!-- Forecast Timeline -->
                <div class="forecast-timeline">
                    <?php 
                    $forecast_count = 0;
                    foreach ($data["data"][0]["cuaca"] as $index_hari => $prakiraan_harian): 
                        if (is_array($prakiraan_harian)):
                            foreach ($prakiraan_harian as $prakiraan):
                                if ($forecast_count >= 8) break 2; // Limit to 8 cards
                                
                                $waktu_lokal = isset($prakiraan["local_datetime"]) ? htmlspecialchars($prakiraan["local_datetime"]) : "N/A";
                                $deskripsi = isset($prakiraan["weather_desc"]) ? htmlspecialchars($prakiraan["weather_desc"]) : "N/A";
                                $suhu = isset($prakiraan["t"]) ? htmlspecialchars($prakiraan["t"]) : "N/A";
                                $kelembapan = isset($prakiraan["hu"]) ? htmlspecialchars($prakiraan["hu"]) : "N/A";
                                $kec_angin = isset($prakiraan["ws"]) ? htmlspecialchars($prakiraan["ws"]) : "N/A";
                                $arah_angin = isset($prakiraan["wd"]) ? htmlspecialchars($prakiraan["wd"]) : "N/A";
                                $jarak_pandang = isset($prakiraan["vs_text"]) ? htmlspecialchars($prakiraan["vs_text"]) : "N/A";
                                
                                // Format time display
                                $time_display = $waktu_lokal;
                                if ($waktu_lokal !== "N/A") {
                                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $waktu_lokal);
                                    if ($datetime) {
                                        $time_display = $datetime->format('H:i') . ' WITA';
                                    }
                                }
                                
                                $raw_img_url = isset($prakiraan["image"]) ? $prakiraan["image"] : "";
                                $img_url_processed = "";
                                if (!empty($raw_img_url)) {
                                    $img_url_processed = str_replace(" ", "%20", $raw_img_url);
                                }
                    ?>
                    <div class="forecast-card">
                        <div class="forecast-time"><?php echo $time_display; ?></div>
                        
                        <?php if ($img_url_processed && filter_var($img_url_processed, FILTER_VALIDATE_URL)): ?>
                            <img src="<?php echo $img_url_processed; ?>" alt="<?php echo htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        
                        <div class="forecast-temp"><?php echo $suhu; ?>°C</div>
                        <div class="forecast-desc"><?php echo $deskripsi; ?></div>
                        
                        <div class="forecast-details">
                            <div class="forecast-detail">
                                <i class="fas fa-tint"></i>
                                <span><?php echo $kelembapan; ?>%</span>
                            </div>
                            <div class="forecast-detail">
                                <i class="fas fa-wind"></i>
                                <span><?php echo $kec_angin; ?> km/j</span>
                            </div>
                            <div class="forecast-detail">
                                <i class="fas fa-compass"></i>
                                <span><?php echo $arah_angin; ?></span>
                            </div>
                            <div class="forecast-detail">
                                <i class="fas fa-eye"></i>
                                <span><?php echo $jarak_pandang; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php 
                                $forecast_count++;
                            endforeach;
                        endif;
                    endforeach; 
                    ?>
                </div>
                
                <!-- Detailed Daily Forecast -->
                <h4 style="margin-top: 2rem; margin-bottom: 1rem;"><i class="fas fa-list"></i> Detail Prakiraan Harian</h4>
                <?php foreach ($data["data"][0]["cuaca"] as $index_hari => $prakiraan_harian): ?>
                    <?php
                    // Ambil tanggal dari data pertama dalam hari tersebut
                    $tanggal_hari = "";
                    if (is_array($prakiraan_harian) && !empty($prakiraan_harian)) {
                        $first_forecast = reset($prakiraan_harian);
                        if (isset($first_forecast["local_datetime"])) {
                            $datetime = new DateTime($first_forecast["local_datetime"]);
                            $tanggal_hari = $datetime->format('d F Y');
                            // Konversi nama bulan ke bahasa Indonesia
                            $bulan_en = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                            $bulan_id = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            $tanggal_hari = str_replace($bulan_en, $bulan_id, $tanggal_hari);
                        }
                    }
                    if (empty($tanggal_hari)) {
                        $tanggal_hari = "Hari ke-" . ($index_hari + 1);
                    }
                    ?>
                    <div class="weather-day">
                        <h4><i class="far fa-calendar-alt"></i> <?php echo $tanggal_hari; ?></h4>
                        
                        <!-- Grid Layout untuk Detail Harian -->
                        <div class="daily-forecast-grid">
                            <?php if (is_array($prakiraan_harian)): ?>
                                <?php foreach ($prakiraan_harian as $prakiraan): ?>
                                    <?php
                                    $waktu_lokal = isset($prakiraan["local_datetime"]) ? htmlspecialchars($prakiraan["local_datetime"]) : "N/A";
                                    $deskripsi = isset($prakiraan["weather_desc"]) ? htmlspecialchars($prakiraan["weather_desc"]) : "N/A";
                                    $alt_text = isset($prakiraan["weather_desc"]) ? htmlspecialchars($prakiraan["weather_desc"], ENT_QUOTES, "UTF-8") : "Ikon Cuaca";
                                    $suhu = isset($prakiraan["t"]) ? htmlspecialchars($prakiraan["t"]) : "N/A";
                                    $kelembapan = isset($prakiraan["hu"]) ? htmlspecialchars($prakiraan["hu"]) : "N/A";
                                    $kec_angin = isset($prakiraan["ws"]) ? htmlspecialchars($prakiraan["ws"]) : "N/A";
                                    $arah_angin = isset($prakiraan["wd"]) ? htmlspecialchars($prakiraan["wd"]) : "N/A";
                                    $jarak_pandang = isset($prakiraan["vs_text"]) ? htmlspecialchars($prakiraan["vs_text"]) : "N/A";

                                    // Format time display
                                    $time_display = $waktu_lokal;
                                    if ($waktu_lokal !== "N/A") {
                                        $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $waktu_lokal);
                                        if ($datetime) {
                                            $time_display = $datetime->format('H:i') . ' WITA';
                                        }
                                    }

                                    $raw_img_url = isset($prakiraan["image"]) ? $prakiraan["image"] : "";
                                    $img_url_processed = "";

                                    if (!empty($raw_img_url)) {
                                        $img_url_processed = str_replace(" ", "%20", $raw_img_url);
                                    }
                                    ?>
                                    <div class="daily-forecast-card">
                                        <div class="forecast-date"><?php echo $waktu_lokal !== "N/A" ? date('d M Y', strtotime($waktu_lokal)) : "N/A"; ?></div>
                                        <div class="forecast-time"><?php echo $time_display; ?></div>
                                        
                                        <?php if ($img_url_processed && filter_var($img_url_processed, FILTER_VALIDATE_URL)): ?>
                                            <div class="forecast-icon">
                                                <img src="<?php echo $img_url_processed; ?>" alt="<?php echo $alt_text; ?>" title="<?php echo $alt_text; ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="forecast-icon">
                                                <i class="fas fa-cloud"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="forecast-temp"><?php echo $suhu; ?>°C</div>
                                        <div class="forecast-desc"><?php echo $deskripsi; ?></div>
                                        
                                        <div class="forecast-details">
                                            <div class="detail-row">
                                                <i class="fas fa-tint"></i> <?php echo $kelembapan; ?>%
                                            </div>
                                            <div class="detail-row">
                                                <i class="fas fa-wind"></i> <?php echo $kec_angin; ?> km/j
                                            </div>
                                            <div class="detail-row">
                                                <i class="fas fa-compass"></i> <?php echo $arah_angin; ?>
                                            </div>
                                            <div class="detail-row">
                                                <i class="fas fa-eye"></i> <?php echo $jarak_pandang; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-data">Data tidak valid.</div>
                            <?php endif; ?>
                        </div>
                     </div>
                 <?php endforeach; ?>
             <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-exclamation-circle"></i> Struktur data prakiraan cuaca tidak ditemukan.
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-exclamation-circle"></i> Lokasi tidak ditemukan. Silakan cari wilayah lain.
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>