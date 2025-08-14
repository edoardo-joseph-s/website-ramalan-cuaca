<!-- Search Limit Info -->
<div id="searchLimitInfo" class="search-limit-info" style="display: none;"></div>

<!-- Form Pencarian Wilayah -->
<section class="search-section">
    <h2><i class="fas fa-search"></i> Cari Wilayah</h2>
    <form class="search-form" method="GET" action="">
        <div class="form-group">
            <label for="keyword">Nama Wilayah:</label>
            <div class="autocomplete-container">
                <input type="text" id="keyword" name="keyword" placeholder="Masukkan nama desa/kelurahan" value="<?php echo htmlspecialchars($keyword); ?>" autocomplete="off">
                <div id="autocomplete-results" class="autocomplete-results"></div>
            </div>
            <input type="hidden" id="kode_wilayah" name="kode_wilayah" value="">
        </div>
        <button type="submit"><i class="fas fa-search"></i> Cari</button>
    </form>
</section>