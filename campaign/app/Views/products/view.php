<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-box-open text-primary me-2"></i>Detail Produk: <?= esc($product['name']) ?>
        </h3>
        <p class="text-muted small mb-0">Spesifikasi produk, ketersediaan stok sampel, daftar varian, dan mutasi inventaris.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('products') ?>" class="text-decoration-none">Katalog Produk</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $product['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php
      $stock = (int)($product['stock_qty'] ?? $product['stock'] ?? 0);
      $price = (float)($product['unit_price'] ?? $product['price'] ?? 0);
      $statusBadge = ($product['is_active'] == 1)
        ? '<span class="badge bg-success px-3 py-2 fs-6"><i class="fas fa-check-circle me-1"></i>Aktif</span>'
        : '<span class="badge bg-danger px-3 py-2 fs-6"><i class="fas fa-times-circle me-1"></i>Nonaktif</span>';

      $imgSrc = !empty($product['image_url']) 
        ? (str_starts_with($product['image_url'], 'http') ? $product['image_url'] : base_url('uploads/campaign/' . $product['image_url']))
        : base_url('asset/img/no-image.png');
    ?>

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= esc($imgSrc) ?>" alt="<?= esc($product['name']) ?>" class="rounded-3 border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\' fill=\'%23dee2e6\'><rect width=\'100%\' height=\'100%\'/></svg>';">
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark"><?= esc($product['name']) ?></h4>
                <?= $statusBadge ?>
              </div>
              <small class="text-muted">
                SKU: <code class="text-dark fw-bold"><?= esc($product['sku']) ?></code> &bull; Kategori: <strong><?= esc($product['category'] ?? 'Umum') ?></strong> &bull; Terdaftar: <?= date('d M Y', strtotime($product['created_at'])) ?>
              </small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-edit me-1"></i> Edit Produk
            </a>
            <a href="<?= base_url('products/single-pdf/' . $product['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak PDF
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2" onclick="deleteProduct(<?= $product['id'] ?>)">
              <i class="fas fa-trash me-1"></i> Hapus Produk
            </button>
            <a href="<?= base_url('products') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Katalog
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Product Specifications -->
      <div class="col-lg-5">

        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Spesifikasi &amp; Inventaris</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 160px;">ID Produk</td>
                    <td><span class="font-monospace fw-bold text-dark">#<?= $product['id'] ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kode SKU</td>
                    <td><code class="text-dark fw-bold"><?= esc($product['sku']) ?></code></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Nama Produk</td>
                    <td><strong class="text-dark"><?= esc($product['name']) ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kategori</td>
                    <td><?= esc($product['category'] ?? '-') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Harga Satuan</td>
                    <td><strong class="text-success fs-5">Rp <?= number_format($price, 0, ',', '.') ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Stok Sampel Saat Ini</td>
                    <td>
                      <?php if ($stock <= 0): ?>
                        <span class="badge bg-danger">Habis (0 unit)</span>
                      <?php elseif ($stock <= 10): ?>
                        <span class="badge bg-warning text-dark">Menipis (<?= $stock ?> unit)</span>
                      <?php else: ?>
                        <span class="badge bg-primary"><?= $stock ?> unit</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Status Katalog</td>
                    <td><?= $product['is_active'] ? '<span class="text-success fw-bold">Aktif Tersedia</span>' : '<span class="text-danger fw-bold">Nonaktif</span>' ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Dibuat</td>
                    <td><?= date('d F Y, H:i:s', strtotime($product['created_at'])) ?> WIB</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Deskripsi Produk -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-align-left text-info me-2"></i>Deskripsi Produk</h5>
          </div>
          <div class="card-body p-4">
            <p class="mb-0 text-dark" style="white-space: pre-wrap; line-height: 1.6; font-size: 13.5px;"><?= esc($product['description'] ?: 'Tidak ada deskripsi rinci untuk produk ini.') ?></p>
          </div>
        </div>

      </div>

      <!-- Right Column: Variants & Stock History -->
      <div class="col-lg-7">

        <!-- Daftar Varian -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-tags text-warning me-2"></i>Varian Produk (<?= count($product['variants'] ?? []) ?>)
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 11px;">
                  <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Nama Varian</th>
                    <th>SKU Varian</th>
                    <th style="width: 100px;">Stok</th>
                    <th style="width: 120px;" class="text-end">Harga Tambahan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($product['variants'])): ?>
                    <?php foreach ($product['variants'] as $idx => $v): ?>
                      <tr>
                        <td class="text-center font-monospace"><?= $idx + 1 ?></td>
                        <td><strong><?= esc($v['name']) ?></strong></td>
                        <td><code><?= esc($v['sku']) ?></code></td>
                        <td><span class="badge bg-light text-dark border"><?= (int)$v['stock_qty'] ?> unit</span></td>
                        <td class="text-end font-monospace"><?= ((float)$v['additional_price'] > 0) ? '+Rp ' . number_format((float)$v['additional_price'], 0, ',', '.') : 'Rp 0' ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="text-center text-muted py-4">Belum ada varian terdaftar untuk produk ini.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Riwayat Mutasi Stok Terakhir -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-secondary me-2"></i>Riwayat Mutasi Stok Terakhir</h5>
            <a href="<?= base_url('stock-history') ?>" class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size: 11.5px;">Buka Semua</a>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 11px;">
                  <tr>
                    <th style="width: 120px;">Waktu</th>
                    <th style="width: 80px;" class="text-center">Tipe</th>
                    <th style="width: 70px;" class="text-center">Qty</th>
                    <th style="width: 90px;" class="text-center">Saldo Akhir</th>
                    <th>Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($product['stock_history'])): ?>
                    <?php foreach ($product['stock_history'] as $sh): ?>
                      <?php
                        $shType = strtoupper($sh['type'] ?? '');
                        $typeBadge = ($shType === 'IN')
                          ? '<span class="badge bg-success-subtle text-success border border-success-subtle">IN</span>'
                          : (($shType === 'OUT') ? '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">OUT</span>' : '<span class="badge bg-info-subtle text-info border border-info-subtle">ADJ</span>');
                      ?>
                      <tr>
                        <td class="small"><?= date('d/m/Y H:i', strtotime($sh['created_at'])) ?></td>
                        <td class="text-center"><?= $typeBadge ?></td>
                        <td class="text-center font-monospace fw-bold"><?= (int)$sh['qty'] ?></td>
                        <td class="text-center font-monospace"><?= (int)$sh['balance_after'] ?></td>
                        <td class="small text-muted text-truncate" style="max-width: 180px;"><?= esc($sh['notes'] ?? '-') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat mutasi stok tercatat.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function deleteProduct(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return;

  $.ajax({
    url: '<?= base_url('products/delete') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        window.location.href = '<?= base_url('products') ?>';
      } else {
        alert(res.message || 'Gagal menghapus produk');
      }
    },
    error: function() {
      alert('Terjadi kesalahan sistem saat menghapus produk.');
    }
  });
}
</script>
<?= $this->endSection() ?>
