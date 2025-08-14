<!-- Informasi Cuaca -->
<?php if (isset($data) && $data !== null): ?>
<div class="weather-card">
    <!-- Informasi Lokasi -->
    <?php if (isset($data["lokasi"]["desa"]) && isset($data["lokasi"]["kecamatan"])): ?>
        <div class="location-info">
            <h2><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($data["lokasi"]["desa"]); ?></h2>
            <p><strong>Kecamatan:</strong> <?php echo htmlspecialchars($data["lokasi"]["kecamatan"] ?? "N/A"); ?></p>
            <p><strong>Kota/Kab:</strong> <?php echo htmlspecialchars($data["lokasi"]["kotkab"] ?? "N/A"); ?></p>
            <p><strong>Provinsi:</strong> <?php echo htmlspecialchars($data["lokasi"]["provinsi"] ?? "N/A"); ?></p>
            <p><strong>Koordinat:</strong> Lat: <?php echo htmlspecialchars($data["lokasi"]["lat"] ?? "N/A"); ?>, Lon: <?php echo htmlspecialchars($data["lokasi"]["lon"] ?? "N/A"); ?></p>
            <p><strong>Timezone:</strong> <?php echo htmlspecialchars($data["lokasi"]["timezone"] ?? "N/A"); ?></p>
        </div>

        <!-- Data Prakiraan Cuaca -->
        <h3><i class="fas fa-cloud"></i> Detail Prakiraan Cuaca</h3>
        <?php if (isset($data["data"][0]["cuaca"]) && is_array($data["data"][0]["cuaca"])): ?>
            <?php foreach ($data["data"][0]["cuaca"] as $index_hari => $prakiraan_harian): ?>
                <div class="weather-day">
                    <h4><i class="far fa-calendar-alt"></i> Hari ke-<?php echo ($index_hari + 1); ?></h4>
                    <ul>
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

                                $raw_img_url = isset($prakiraan["image"]) ? $prakiraan["image"] : "";
                                $img_url_processed = "";

                                if (!empty($raw_img_url)) {
                                    $img_url_processed = str_replace(" ", "%20", $raw_img_url);
                                }
                                ?>
                                <li>
                                    <div class="weather-detail"><i class="far fa-clock"></i> <strong>Jam:</strong> <?php echo $waktu_lokal; ?></div>
                                    <div class="weather-detail"><i class="fas fa-cloud"></i> <strong>Cuaca:</strong> <?php echo $deskripsi; ?>
                                    <?php if ($img_url_processed && filter_var($img_url_processed, FILTER_VALIDATE_URL)): ?>
                                        <img src="<?php echo $img_url_processed; ?>" alt="<?php echo $alt_text; ?>" title="<?php echo $alt_text; ?>">
                                    <?php endif; ?>
                                    </div>
                                    <div class="weather-detail"><i class="fas fa-temperature-high"></i> <strong>Suhu:</strong> <?php echo $suhu; ?>°C</div>
                                    <div class="weather-detail"><i class="fas fa-tint"></i> <strong>Kelembapan:</strong> <?php echo $kelembapan; ?>%</div>
                                    <div class="weather-detail"><i class="fas fa-wind"></i> <strong>Kec. Angin:</strong> <?php echo $kec_angin; ?> km/j</div>
                                    <div class="weather-detail"><i class="fas fa-compass"></i> <strong>Arah Angin:</strong> dari <?php echo $arah_angin; ?></div>
                                    <div class="weather-detail"><i class="fas fa-eye"></i> <strong>Jarak Pandang:</strong> <?php echo $jarak_pandang; ?></div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Data tidak valid.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-exclamation-circle"></i> Struktur data prakiraan cuaca tidak ditemukan.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-exclamation-circle"></i> Lokasi tidak ditemukan. Silakan cari wilayah lain.
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>