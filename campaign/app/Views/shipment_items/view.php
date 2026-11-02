<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-box text-success me-2"></i>Detail Item Sampel: <?= esc($item['product_name']) ?>
        </h3>
        <p class="text-muted small mb-0">Rincian alokasi barang sampel dalam paket pengiriman ekspedisi ke talent KOL.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipments') ?>" class="text-decoration-none">Pengiriman</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipment-items') ?>" class="text-decoration-none">Item Sampel</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $item['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php
      $price = (float)($item['product_price'] ?? 0);
      $qty = (int)$item['qty'];
      $subtotal = $qty * $price;
      $status = strtoupper($item['shipping_status'] ?: ($item['status'] ?: 'PENDING'));
      $statusBadge = match ($status) {
        'DELIVERED' => '<span class="badge bg-success px-3 py-2 fs-6"><i class="fas fa-check-double me-1"></i>DELIVERED</span>',
        'SHIPPED'   => '<span class="badge bg-info px-3 py-2 fs-6"><i class="fas fa-truck me-1"></i>SHIPPED</span>',
        'RETURNED'  => '<span class="badge bg-danger px-3 py-2 fs-6"><i class="fas fa-undo me-1"></i>RETURNED</span>',
        default     => '<span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="far fa-clock me-1"></i>' . $status . '</span>',
      };
    ?>

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
              <i class="fas fa-cube fa-2x"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark"><?= esc($item['product_name']) ?> &times; <?= $qty ?> pcs</h4>
                <?= $statusBadge ?>
              </div>
              <small class="text-muted">
                Resi: <code class="text-dark fw-bold"><?= esc($item['tracking_number'] ?: ($item['tracking_no'] ?: 'Draft #' . $item['shipment_id'])) ?></code> &bull; Penerima: <strong><?= esc($item['recipient_name'] ?: ($item['kol_name'] ?? '-')) ?></strong>
              </small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('shipment-items/edit/' . $item['id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-edit me-1"></i> Edit Item
            </a>
            <a href="<?= base_url('shipment-items/single-pdf/' . $item['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak PDF
            </a>
            <a href="<?= base_url('shipments/view/' . $item['shipment_id']) ?>" class="btn btn-outline-info btn-sm rounded-2">
              <i class="fas fa-shipping-fast me-1"></i> Lihat Paket Pengiriman
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2" onclick="deleteItem(<?= $item['id'] ?>)">
              <i class="fas fa-trash me-1"></i> Hapus
            </button>
            <a href="<?= base_url('shipment-items') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Product Information -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open text-primary me-2"></i>Spesifikasi Produk Sampel</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 170px;">ID Item Alokasi</td>
                    <td><span class="font-monospace fw-bold text-dark">#<?= $item['id'] ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Nama Produk</td>
                    <td><strong class="text-dark fs-6"><?= esc($item['product_name']) ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kode SKU</td>
                    <td><code class="text-dark fw-bold"><?= esc($item['product_sku'] ?? '-') ?></code></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kategori Produk</td>
                    <td><?= esc($item['product_category'] ?? '-') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Varian Khusus</td>
                    <td>
                      <?php if (!empty($item['variant_name'])): ?>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><?= esc($item['variant_name']) ?></span>
                      <?php else: ?>
                        <span class="text-muted">Standar / Non-Varian</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Jumlah Sampel (Qty)</td>
                    <td><span class="font-monospace fw-bold text-primary fs-5"><?= $qty ?></span> <span class="text-muted">Pcs / Unit</span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Harga Satuan Estimasi</td>
                    <td class="font-monospace">Rp <?= number_format($price, 0, ',', '.') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Total Valuasi Sampel</td>
                    <td class="font-monospace fw-bold text-success fs-6">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Deskripsi Produk</td>
                    <td class="small text-muted"><?= nl2br(esc($item['product_description'] ?? '-')) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Shipment & Destination Information -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-truck text-info me-2"></i>Informasi Paket &amp; Penerima</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 170px;">ID Pengiriman Induk</td>
                    <td>
                      <a href="<?= base_url('shipments/view/' . $item['shipment_id']) ?>" class="font-monospace fw-bold text-decoration-none">
                        #<?= $item['shipment_id'] ?> <i class="fas fa-external-link-alt small ms-1"></i>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Jasa Kurir / Ekspedisi</td>
                    <td><span class="badge bg-secondary px-2 py-1"><?= esc($item['courier_name'] ?: ($item['courier'] ?: '-')) ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Nomor Resi / AWB</td>
                    <td><code class="text-dark fw-bold fs-6"><?= esc($item['tracking_number'] ?: ($item['tracking_no'] ?: 'Draft Belum Resi')) ?></code></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Status Pengiriman</td>
                    <td><?= $statusBadge ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kampanye Terkait</td>
                    <td><strong class="text-primary"><?= esc($item['campaign_title'] ?? '-') ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Nama Penerima (KOL)</td>
                    <td><strong class="text-dark"><?= esc($item['recipient_name'] ?: ($item['kol_name'] ?? '-')) ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kontak WhatsApp</td>
                    <td>
                      <?php if (!empty($item['recipient_phone'] ?: ($item['kol_whatsapp'] ?? ''))): ?>
                        <span class="font-monospace text-success"><i class="fab fa-whatsapp me-1"></i><?= esc($item['recipient_phone'] ?: ($item['kol_whatsapp'] ?? '')) ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Alamat Tujuan</td>
                    <td class="small"><?= nl2br(esc($item['shipping_address'] ?? '-')) ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Alokasi Item</td>
                    <td class="small font-monospace"><?= !empty($item['created_at']) ? date('d F Y, H:i:s', strtotime($item['created_at'])) : '-' ?></td>
                  </tr>
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
function deleteItem(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus item sampel ini? Qty produk akan dikembalikan ke stok gudang.')) return;

  $.ajax({
    url: '<?= base_url('shipment-items/delete') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        window.location.href = '<?= base_url('shipment-items') ?>';
      } else {
        alert(res.message || 'Gagal menghapus item.');
      }
    },
    error: function() {
      alert('Terjadi kesalahan sistem saat menghapus item.');
    }
  });
}
</script>
<?= $this->endSection() ?>
