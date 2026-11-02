<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-file-invoice text-primary me-2"></i>Detail Transaksi Mutasi Stok #<?= $item['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Rincian lengkap jejak audit pergerakan inventaris dan referensi operasional.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('products') ?>" class="text-decoration-none">Master Produk</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('stock-history') ?>" class="text-decoration-none">Riwayat Stok</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detail #<?= $item['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
      <a href="<?= base_url('stock-history') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Riwayat Stok
      </a>
      <div class="d-flex gap-2">
        <a href="<?= base_url('stock-history/single-pdf/' . $item['id']) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2">
          <i class="fas fa-file-pdf me-1"></i> Cetak Bukti Mutasi (PDF)
        </a>
      </div>
    </div>

    <?php
      $type = strtoupper($item['type'] ?: ($item['change_type'] ?? ''));
      $typeBadge = match ($type) {
        'IN'         => '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6"><i class="fas fa-arrow-down me-1"></i>BARANG MASUK (IN)</span>',
        'OUT'        => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6"><i class="fas fa-arrow-up me-1"></i>BARANG KELUAR (OUT)</span>',
        'ADJUSTMENT' => '<span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 fs-6"><i class="fas fa-sliders-h me-1"></i>PENYESUAIAN STOK (ADJUSTMENT)</span>',
        default      => '<span class="badge bg-secondary-subtle text-secondary border px-3 py-2 fs-6">' . esc($type) . '</span>',
      };
      $qtySign = match ($type) {
        'IN'    => '+',
        'OUT'   => '-',
        default => '',
      };
      $qtyColor = match ($type) {
        'IN'    => 'text-success',
        'OUT'   => 'text-danger',
        default => 'text-primary',
      };
    ?>

    <div class="row g-4">
      
      <!-- Kolom Kiri: Rincian Mutasi & Stok -->
      <div class="col-lg-7">
        
        <!-- Kartu Perubahan Stok -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-exchange-alt text-primary me-2"></i>Rincian Mutasi Inventaris
            </h5>
            <div><?= $typeBadge ?></div>
          </div>
          <div class="card-body p-4">

            <div class="row g-3 text-center mb-4">
              <div class="col-4">
                <div class="p-3 bg-light rounded-3 border">
                  <div class="text-muted small text-uppercase fw-semibold mb-1">Stok Sebelum</div>
                  <h3 class="fw-bold mb-0 text-secondary font-monospace"><?= number_format((int)$item['stock_before'], 0, ',', '.') ?></h3>
                  <small class="text-muted">unit gudang</small>
                </div>
              </div>
              <div class="col-4">
                <div class="p-3 bg-light rounded-3 border border-2 border-primary-subtle">
                  <div class="text-muted small text-uppercase fw-semibold mb-1">Kuantitas Mutasi</div>
                  <h3 class="fw-bold mb-0 <?= $qtyColor ?> font-monospace"><?= $qtySign ?><?= number_format((int)$item['qty'], 0, ',', '.') ?></h3>
                  <small class="text-muted">unit diproses</small>
                </div>
              </div>
              <div class="col-4">
                <div class="p-3 bg-light rounded-3 border">
                  <div class="text-muted small text-uppercase fw-semibold mb-1">Stok Sesudah</div>
                  <h3 class="fw-bold mb-0 text-dark font-monospace"><?= number_format((int)$item['balance_after'], 0, ',', '.') ?></h3>
                  <small class="text-muted">saldo akhir</small>
                </div>
              </div>
            </div>

            <table class="table table-bordered align-middle mb-0" style="font-size: 13.5px;">
              <tbody>
                <tr>
                  <td style="width: 200px;" class="bg-light text-muted fw-semibold">ID Mutasi</td>
                  <td class="font-monospace fw-bold text-dark">#<?= $item['id'] ?></td>
                </tr>
                <tr>
                  <td class="bg-light text-muted fw-semibold">Waktu Pencatatan</td>
                  <td>
                    <span class="font-monospace fw-semibold text-dark"><?= date('d F Y, H:i:s', strtotime($item['created_at'])) ?> WIB</span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light text-muted fw-semibold">Klasifikasi Mutasi</td>
                  <td>
                    <strong><?= esc($type) ?></strong> &bull; <?= $type === 'IN' ? 'Penambahan saldo stok masuk' : ($type === 'OUT' ? 'Pengurangan saldo stok keluar' : 'Penetapan stok opname fisik') ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light text-muted fw-semibold">Keterangan / Alasan</td>
                  <td class="text-secondary"><?= nl2br(esc($item['notes'] ?: 'Tidak ada catatan tambahan.')) ?></td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>

        <!-- Kartu Referensi & Logistik -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-link text-success me-2"></i>Referensi Dokumen &amp; Logistik
            </h5>
          </div>
          <div class="card-body p-4">
            <table class="table table-bordered align-middle mb-0" style="font-size: 13.5px;">
              <tbody>
                <tr>
                  <td style="width: 200px;" class="bg-light text-muted fw-semibold">Tipe Referensi</td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-7 font-monospace">
                      <?= esc($item['reference_type'] ?: 'MANUAL') ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light text-muted fw-semibold">ID Referensi</td>
                  <td>
                    <span class="font-monospace fw-bold text-dark"><?= $item['reference_id'] ? '#' . $item['reference_id'] : '-' ?></span>
                    <?php if (in_array($item['reference_type'], ['SHIPMENT', 'SHIPMENT_ITEM']) && !empty($item['reference_id'])): ?>
                      <a href="<?= base_url('shipments/view/' . $item['reference_id']) ?>" class="btn btn-outline-primary btn-sm ms-2 py-0 px-2 rounded-2" style="font-size: 11px;">
                        <i class="fas fa-external-link-alt me-1"></i> Buka Paket Pengiriman
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php if (!empty($item['shipment_tracking'])): ?>
                  <tr>
                    <td class="bg-light text-muted fw-semibold">No. Resi Pengiriman</td>
                    <td class="font-monospace fw-bold text-primary"><?= esc($item['shipment_tracking']) ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($item['shipment_courier'])): ?>
                  <tr>
                    <td class="bg-light text-muted fw-semibold">Ekspedisi Kurir</td>
                    <td><?= esc($item['shipment_courier']) ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($item['shipment_recipient'])): ?>
                  <tr>
                    <td class="bg-light text-muted fw-semibold">Penerima Paket (KOL)</td>
                    <td><strong><?= esc($item['shipment_recipient']) ?></strong></td>
                  </tr>
                <?php endif; ?>
                <tr>
                  <td class="bg-light text-muted fw-semibold">Operator Pencatat</td>
                  <td>
                    <div class="fw-semibold text-dark"><i class="fas fa-user-check text-success me-1"></i> <?= esc($item['user_name'] ?? 'Administrator') ?></div>
                    <small class="text-muted"><?= esc($item['user_email'] ?? '-') ?></small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Kolom Kanan: Spesifikasi Produk Terkait -->
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-box text-secondary me-2"></i>Spesifikasi Produk Terkait
            </h5>
          </div>
          <div class="card-body p-4">
            
            <div class="text-center pb-3 border-bottom mb-3">
              <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px; font-size: 24px;">
                <i class="fas fa-cube"></i>
              </div>
              <h5 class="fw-bold text-dark mb-1"><?= esc($item['product_name'] ?? 'Produk #' . $item['product_id']) ?></h5>
              <div>
                <span class="badge bg-light text-dark border font-monospace"><?= esc($item['product_sku'] ?? '-') ?></span>
                <?php if (!empty($item['variant_name'])): ?>
                  <span class="badge bg-info-subtle text-info border border-info-subtle ms-1"><?= esc($item['variant_name']) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <table class="table table-borderless align-middle mb-0" style="font-size: 13px;">
              <tbody>
                <tr>
                  <td class="text-muted" style="width: 140px;">Kategori:</td>
                  <td class="fw-semibold text-dark"><?= esc($item['product_category'] ?? 'Umum') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Estimasi Harga:</td>
                  <td class="fw-bold font-monospace text-primary">Rp <?= number_format((float)($item['product_price'] ?? 0), 0, ',', '.') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">ID Produk:</td>
                  <td class="font-monospace text-dark">#<?= $item['product_id'] ?></td>
                </tr>
              </tbody>
            </table>

            <?php if (!empty($item['product_description'])): ?>
              <div class="mt-3 p-3 bg-light rounded-2 border small text-secondary">
                <strong>Deskripsi:</strong><br>
                <?= nl2br(esc($item['product_description'])) ?>
              </div>
            <?php endif; ?>

            <div class="mt-4 pt-3 border-top text-center">
              <a href="<?= base_url('products/view/' . $item['product_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2 w-100">
                <i class="fas fa-cube me-1"></i> Buka Katalog Produk Ini
              </a>
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
// Additional interactions if needed
</script>
<?= $this->endSection() ?>
