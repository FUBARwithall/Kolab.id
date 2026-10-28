<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
  
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div class="d-flex align-items-center gap-2">
        <h5 class="card-title fw-bold text-dark mb-0">
          <i class="fas fa-history text-secondary me-2"></i>Riwayat Audit Mutasi Stok Sampel
          <?php if (!empty($selectedProduct)): ?>
            <span class="badge bg-primary ms-2"><?= esc($selectedProduct['name']) ?></span>
          <?php endif; ?>
        </h5>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if (!empty($selectedProduct)): ?>
          <a href="<?= base_url('stock-history') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
            <i class="fas fa-times me-1"></i> Reset Filter Produk
          </a>
        <?php endif; ?>
        <a href="<?= base_url('products') ?>" class="btn btn-outline-primary btn-sm rounded-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Master Produk
        </a>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table id="stockHistoryTable" class="table table-hover align-middle w-100" style="font-size: 13.5px;">
          <thead class="table-light text-secondary text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
            <tr>
              <th style="width: 50px;">ID</th>
              <th style="width: 140px;">Waktu Mutasi</th>
              <th>Produk &amp; SKU</th>
              <th style="width: 110px;" class="text-center">Tipe Mutasi</th>
              <th style="width: 100px;" class="text-end">Jumlah</th>
              <th style="width: 110px;" class="text-end">Sisa Stok</th>
              <th>Referensi / Alasan</th>
              <th>Operator</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($history)): ?>
              <?php foreach ($history as $h): ?>
                <tr>
                  <td><?= $h['id'] ?></td>
                  <td class="text-secondary small"><?= date('d M Y, H:i', strtotime($h['created_at'])) ?></td>
                  <td>
                    <div class="fw-semibold text-dark"><?= esc($h['product_name'] ?? 'Produk #' . $h['product_id']) ?></div>
                    <small class="text-muted"><?= esc($h['product_sku'] ?? '-') ?></small>
                  </td>
                  <td class="text-center">
                    <?php if ($h['type'] === 'IN'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-arrow-down me-1"></i>MASUK</span>
                    <?php elseif ($h['type'] === 'OUT'): ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-arrow-up me-1"></i>KELUAR</span>
                    <?php else: ?>
                      <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-sliders-h me-1"></i>KOREKSI</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end fw-bold <?= $h['type'] === 'IN' ? 'text-success' : ($h['type'] === 'OUT' ? 'text-danger' : 'text-primary') ?>">
                    <?= $h['type'] === 'IN' ? '+' : ($h['type'] === 'OUT' ? '-' : '') ?><?= number_format($h['qty'], 0, ',', '.') ?>
                  </td>
                  <td class="text-end fw-semibold text-dark">
                    <?= number_format($h['balance_after'], 0, ',', '.') ?>
                  </td>
                  <td>
                    <?php if (!empty($h['reference_type'])): ?>
                      <span class="badge bg-light text-secondary border me-1"><?= esc($h['reference_type']) ?></span>
                    <?php endif; ?>
                    <span class="text-secondary small"><?= esc($h['notes'] ?? '-') ?></span>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1"><i class="fas fa-user me-1"></i><?= esc($h['user_name'] ?? 'Admin') ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">Belum ada riwayat mutasi stok.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
  $('#stockHistoryTable').DataTable({
    order: [[0, 'desc']],
    pageLength: 25,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari riwayat stok...",
      lengthMenu: "Tampilkan _MENU_ baris",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
      infoEmpty: "Tidak ada riwayat mutasi stok",
      zeroRecords: "Data tidak ditemukan",
      paginate: {
        first: "«",
        last: "»",
        next: "›",
        previous: "‹"
      }
    }
  });
});
</script>
<?= $this->endSection() ?>
