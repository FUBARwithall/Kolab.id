<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-shipping-fast text-info me-2"></i>Detail Pengiriman Sampel #<?= $shipment['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Rincian resi logistik, ekspedisi kurir, barang sampel yang dikirim, dan tracking penerimaan KOL.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipments') ?>" class="text-decoration-none">Pengiriman Sampel</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $shipment['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php
      $status = strtoupper($shipment['shipping_status'] ?: ($shipment['status'] ?: 'PENDING'));
      $statusBadge = match ($status) {
        'DELIVERED'      => '<span class="badge bg-success px-3 py-2 fs-6"><i class="fas fa-check-double me-1"></i>DELIVERED (Terkirim)</span>',
        'SHIPPED'        => '<span class="badge bg-info px-3 py-2 fs-6"><i class="fas fa-truck me-1"></i>SHIPPED (Dalam Perjalanan)</span>',
        'PROCESSING'     => '<span class="badge bg-primary px-3 py-2 fs-6"><i class="fas fa-box me-1"></i>PROCESSING (Sedang Dipacking)</span>',
        'RETURN_PENDING' => '<span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6"><i class="fas fa-undo me-1"></i>RETURN PENDING</span>',
        'RETURNED'       => '<span class="badge bg-danger px-3 py-2 fs-6"><i class="fas fa-undo me-1"></i>RETURNED (Retur Diterima)</span>',
        default          => '<span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="far fa-clock me-1"></i>PENDING (Persiapan)</span>',
      };

      $trackingNo = $shipment['tracking_number'] ?: ($shipment['tracking_no'] ?: '-');
      $courier    = $shipment['courier_name'] ?: ($shipment['courier'] ?: '-');
      $recipient  = $shipment['recipient_name'] ?: ($shipment['kol_name'] ?? '-');
      $phone      = $shipment['recipient_phone'] ?: ($shipment['kol_whatsapp'] ?? '-');
    ?>

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
              <i class="fas fa-box-open fa-2x"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark"><?= esc($courier) ?>: <span class="font-monospace text-primary"><?= esc($trackingNo) ?></span></h4>
                <?= $statusBadge ?>
              </div>
              <small class="text-muted">
                Penerima: <strong class="text-dark"><?= esc($recipient) ?></strong> &bull; Kampanye: <strong><?= esc($shipment['campaign_title'] ?? '-') ?></strong> &bull; Didaftarkan: <?= date('d M Y, H:i', strtotime($shipment['created_at'])) ?>
              </small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('shipments/edit/' . $shipment['id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-edit me-1"></i> Edit Data
            </a>
            <a href="<?= base_url('shipments/single-pdf/' . $shipment['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Surat Jalan (PDF)
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2" onclick="deleteShipment(<?= $shipment['id'] ?>)">
              <i class="fas fa-trash me-1"></i> Hapus
            </button>
            <a href="<?= base_url('shipments') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Recipient & Courier Specs -->
      <div class="col-lg-5">

        <!-- Recipient Destination Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-tag text-primary me-2"></i>Informasi Penerima (Talent KOL)</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 160px;">Nama Penerima</td>
                    <td><strong class="text-dark"><?= esc($recipient) ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">No. HP / WhatsApp</td>
                    <td>
                      <?php if (!empty($phone) && $phone !== '-'): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $phone) ?>" target="_blank" class="text-decoration-none fw-semibold text-success">
                          <i class="fab fa-whatsapp me-1"></i><?= esc($phone) ?>
                        </a>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kampanye KOL</td>
                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= esc($shipment['campaign_title'] ?? '-') ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kota / Kabupaten</td>
                    <td><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($shipment['kol_city'] ?: '-') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Alamat Pengiriman</td>
                    <td class="small"><?= nl2br(esc($shipment['shipping_address'] ?: ($shipment['kol_address'] ?: '-'))) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Logistics Specs Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-truck text-info me-2"></i>Spesifikasi Ekspedisi Logistik</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 160px;">ID Pengiriman</td>
                    <td><span class="font-monospace fw-bold text-dark">#<?= $shipment['id'] ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Jasa Kurir / Ekspedisi</td>
                    <td><span class="badge bg-secondary px-2 py-1"><?= esc($courier) ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Nomor Resi / AWB</td>
                    <td><code class="text-dark fw-bold fs-6"><?= esc($trackingNo) ?></code></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Biaya Ongkir (Rp)</td>
                    <td class="font-monospace">Rp <?= number_format((float)($shipment['shipping_fee'] ?? 0), 0, ',', '.') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Status Pengiriman</td>
                    <td><?= $statusBadge ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Diserahkan (Shipped)</td>
                    <td class="small font-monospace"><?= !empty($shipment['shipped_at']) ? date('d F Y, H:i', strtotime($shipment['shipped_at'])) : '<span class="text-muted">Belum diserahkan</span>' ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Terkirim (Delivered)</td>
                    <td class="small font-monospace"><?= !empty($shipment['delivered_at']) ? date('d F Y, H:i', strtotime($shipment['delivered_at'])) : '<span class="text-muted">Belum terkirim</span>' ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Catatan Khusus</td>
                    <td class="small text-muted"><?= nl2br(esc($shipment['notes'] ?: '-')) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Shipped Items Breakdown -->
      <div class="col-lg-7">

        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-boxes text-success me-2"></i>Daftar Barang Sampel Dikirim</h5>
            <span class="badge bg-light text-dark border">
              Total: <?= !empty($shipment['items']) ? count($shipment['items']) : 0 ?> Jenis Barang
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                  <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Nama Produk &amp; Varian</th>
                    <th style="width: 110px;">Kode SKU</th>
                    <th style="width: 90px;" class="text-center">Jumlah (Qty)</th>
                    <th style="width: 120px;" class="text-end">Harga Satuan</th>
                    <th style="width: 130px;" class="text-end">Subtotal Valuasi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($shipment['items'])): ?>
                    <?php 
                      $totalQty = 0;
                      $totalValuation = 0;
                      foreach ($shipment['items'] as $idx => $it): 
                        $qty = (int)$it['qty'];
                        $price = (float)($it['product_price'] ?? 0);
                        $sub = $qty * $price;
                        $totalQty += $qty;
                        $totalValuation += $sub;
                    ?>
                      <tr>
                        <td class="text-center small text-muted"><?= $idx + 1 ?></td>
                        <td>
                          <strong class="text-dark"><?= esc($it['product_name']) ?></strong>
                          <?php if (!empty($it['variant_name'])): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle ms-1"><?= esc($it['variant_name']) ?></span>
                          <?php endif; ?>
                        </td>
                        <td><code class="text-dark font-monospace"><?= esc($it['product_sku'] ?? '-') ?></code></td>
                        <td class="text-center font-monospace fw-bold"><?= $qty ?> Pcs</td>
                        <td class="text-end font-monospace">Rp <?= number_format($price, 0, ',', '.') ?></td>
                        <td class="text-end font-monospace fw-semibold">Rp <?= number_format($sub, 0, ',', '.') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted py-5">
                        <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i>
                        Tidak ada rincian produk sampel spesifik yang dicatat pada paket pengiriman ini.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
                <?php if (!empty($shipment['items'])): ?>
                  <tfoot class="table-light">
                    <tr class="fw-bold">
                      <td colspan="3" class="text-end">TOTAL KESELURUHAN:</td>
                      <td class="text-center font-monospace"><?= $totalQty ?> Pcs</td>
                      <td></td>
                      <td class="text-end font-monospace text-primary">Rp <?= number_format($totalValuation, 0, ',', '.') ?></td>
                    </tr>
                  </tfoot>
                <?php endif; ?>
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
function deleteShipment(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus data pengiriman ini? Barang yang telah dikirim akan dikembalikan otomatis ke stok.')) return;

  $.ajax({
    url: '<?= base_url('shipments/delete') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        window.location.href = '<?= base_url('shipments') ?>';
      } else {
        alert(res.message || 'Gagal menghapus data pengiriman.');
      }
    },
    error: function() {
      alert('Terjadi kesalahan sistem saat menghapus pengiriman.');
    }
  });
}
</script>
<?= $this->endSection() ?>
