<!-- Hasil Pencarian (hanya tampil jika tidak ada data cuaca) -->
<?php if (isset($_GET['keyword']) && !empty($_GET['keyword']) && (!isset($data) || $data === null)): ?>
<section class="search-results">
    <h2><i class="fas fa-list"></i> Hasil Pencarian</h2>
    <?php if (count($hasil_pencarian) > 0): ?>
        <?php foreach ($hasil_pencarian as $wilayah): ?>
            <div class="result-item">
                <a href="?kode_wilayah=<?php echo htmlspecialchars($wilayah['kode']); ?>">
                    <?php echo htmlspecialchars($wilayah['nama']); ?> (<?php echo htmlspecialchars($wilayah['kode']); ?>)
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-exclamation-circle"></i> Tidak ada hasil yang ditemukan untuk "<?php echo htmlspecialchars($keyword); ?>". Coba kata kunci lain.
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- Tampilkan Error Jika Ada -->
<?php if (isset($error_message)): ?>
<div class="error-message">
    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
</div>
<?php endif; ?>