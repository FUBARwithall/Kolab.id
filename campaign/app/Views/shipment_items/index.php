<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-boxes text-success me-2"></i>Daftar Item Sampel Pengiriman KOL
        </h3>
        <p class="text-muted small mb-0">Rincian produk sampel spesifik yang dialokasikan ke dalam paket ekspedisi untuk talent kreator.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipments') ?>" class="text-decoration-none">Pengiriman</a></li>
          <li class="breadcrumb-item active" aria-current="page">Item Sampel</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Mini Dashboard Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
              <i class="fas fa-list-ol fa-lg"></i>
            </div>
            <div>
              <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Baris Item</div>
              <h4 class="mb-0 fw-bold text-dark"><?= esc($summary['total_rows'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
              <i class="fas fa-cubes fa-lg"></i>
            </div>
            <div>
              <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Unit Sampel</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($summary['total_qty'] ?? 0, 0, ',', '.') ?> <small class="text-muted fs-6">Pcs</small></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
              <i class="fas fa-box-open fa-lg"></i>
            </div>
            <div>
              <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Produk Berbeda</div>
              <h4 class="mb-0 fw-bold text-dark"><?= esc($summary['unique_products'] ?? 0) ?> <small class="text-muted fs-6">SKU</small></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
              <i class="fas fa-shipping-fast fa-lg"></i>
            </div>
            <div>
              <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Paket Terkait</div>
              <h4 class="mb-0 fw-bold text-dark"><?= esc($summary['unique_shipments'] ?? 0) ?> <small class="text-muted fs-6">Resi</small></h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Data Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <div class="d-flex flex-wrap align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="filterShipment" style="min-width: 220px;" onchange="reloadTable()">
              <option value="">Semua Paket Pengiriman</option>
              <?php foreach ($allShipments as $s): ?>
                <option value="<?= $s['id'] ?>">#<?= $s['id'] ?> - <?= esc($s['tracking_number'] ?: 'Draft') ?> (<?= esc($s['recipient_name'] ?: 'KOL') ?>)</option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterProduct" style="min-width: 200px;" onchange="reloadTable()">
              <option value="">Semua Master Produk</option>
              <?php foreach ($allProducts as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?> (<?= esc($p['sku']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group">
              <a href="<?= base_url('shipment-items/export') ?>" class="btn btn-outline-success btn-sm rounded-2" title="Export Excel / CSV">
                <i class="fas fa-file-excel me-1"></i> Excel
              </a>
              <a href="<?= base_url('shipment-items/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2" title="Print Laporan PDF">
                <i class="fas fa-file-pdf me-1"></i> PDF
              </a>
            </div>
            <a href="<?= base_url('shipments') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-truck me-1"></i> Master Pengiriman
            </a>
            <a href="<?= base_url('shipment-items/add') ?>" class="btn btn-primary rounded-2 btn-sm px-3 shadow-sm">
              <i class="fas fa-plus me-1"></i> Tambah Item Sampel
            </a>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableShipmentItems" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="width: 220px;">Paket Pengiriman &amp; Penerima</th>
                <th>Produk Sampel &amp; Varian</th>
                <th style="width: 110px;">Jumlah</th>
                <th style="width: 140px;">Valuasi Item</th>
                <th style="width: 130px;">Status Paket</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtItems = null;

$(document).ready(function() {
  dtItems = $('#tableShipmentItems').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('shipment-items/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.shipment_id = $('#filterShipment').val();
        d.product_id = $('#filterProduct').val();
      }
    },
    columns: [
      { data: 'id', className: 'text-center text-muted small' },
      { data: 'shipment' },
      { data: 'product' },
      { data: 'qty' },
      { data: 'valuation' },
      { data: 'status' },
      { data: 'action', orderable: false, className: 'text-center' }
    ],
    order: [[0, 'desc']],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari produk, SKU, resi, penerima...",
      lengthMenu: "Tampil _MENU_",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ item",
      paginate: { first: "«", previous: "‹", next: "›", last: "»" }
    }
  });
});

function reloadTable() {
  if (dtItems) {
    dtItems.ajax.reload();
  }
}

function deleteShipmentItem(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus item sampel ini? Qty produk akan otomatis dikembalikan ke stok gudang.')) return;

  $.ajax({
    url: '<?= base_url('shipment-items/delete') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        reloadTable();
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
