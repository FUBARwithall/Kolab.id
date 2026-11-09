<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-boxes text-primary me-2"></i>Riwayat Audit Mutasi Stok Produk
        </h3>
        <p class="text-muted small mb-0">Lacak arus masuk, keluar, dan koreksi inventaris sampel produk KOL secara transparan.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('products') ?>" class="text-decoration-none">Master Produk</a></li>
          <li class="breadcrumb-item active" aria-current="page">Riwayat Stok</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-check-circle fa-lg me-2"></i>
          <div><?= session()->getFlashdata('success') ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-circle fa-lg me-2"></i>
          <div><?= session()->getFlashdata('error') ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small text-uppercase fw-semibold">Total Log Mutasi</span>
              <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="fas fa-list"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($summary['total_mutations'] ?? 0, 0, ',', '.') ?></h4>
            <small class="text-muted" style="font-size: 11px;">Aktivitas inventaris tercatat</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small text-uppercase fw-semibold">Barang Masuk (IN)</span>
              <span class="badge bg-success-subtle text-success rounded-pill"><i class="fas fa-arrow-down"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-success">+<?= number_format($summary['total_in'] ?? 0, 0, ',', '.') ?></h4>
            <small class="text-muted" style="font-size: 11px;">Restock &amp; retur paket</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small text-uppercase fw-semibold">Barang Keluar (OUT)</span>
              <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="fas fa-arrow-up"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-danger">-<?= number_format($summary['total_out'] ?? 0, 0, ',', '.') ?></h4>
            <small class="text-muted" style="font-size: 11px;">Alokasi sampel pengiriman</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small text-uppercase fw-semibold">Penyesuaian (ADJ)</span>
              <span class="badge bg-info-subtle text-info rounded-pill"><i class="fas fa-sliders-h"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-info"><?= number_format($summary['total_adjustment'] ?? 0, 0, ',', '.') ?></h4>
            <small class="text-muted" style="font-size: 11px;">Opname fisik &amp; koreksi stok</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="row g-2 align-items-center">
          
          <div class="col-12 col-md-3">
            <label class="form-label small fw-semibold text-muted mb-1">Filter Produk</label>
            <select class="form-select form-select-sm" id="filterProduct" onchange="reloadTable()">
              <option value="">-- Semua Produk --</option>
              <?php foreach ($allProducts as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (!empty($filters['product_id']) && $filters['product_id'] == $p['id']) ? 'selected' : '' ?>>
                  <?= esc($p['name']) ?> (<?= esc($p['sku'] ?? '-') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Tipe Mutasi</label>
            <select class="form-select form-select-sm" id="filterType" onchange="reloadTable()">
              <option value="">Semua Tipe</option>
              <option value="IN" <?= (!empty($filters['type']) && $filters['type'] === 'IN') ? 'selected' : '' ?>>Masuk (IN)</option>
              <option value="OUT" <?= (!empty($filters['type']) && $filters['type'] === 'OUT') ? 'selected' : '' ?>>Keluar (OUT)</option>
              <option value="ADJUSTMENT" <?= (!empty($filters['type']) && $filters['type'] === 'ADJUSTMENT') ? 'selected' : '' ?>>Koreksi (ADJUST)</option>
            </select>
          </div>

          <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
            <input type="date" class="form-control form-control-sm" id="filterDateFrom" value="<?= esc($filters['date_from'] ?? '') ?>" onchange="reloadTable()">
          </div>

          <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
            <input type="date" class="form-control form-control-sm" id="filterDateTo" value="<?= esc($filters['date_to'] ?? '') ?>" onchange="reloadTable()">
          </div>

          <div class="col-12 col-md-3 d-flex align-items-end justify-content-md-end gap-2 mt-md-4">
            <div class="btn-group">
              <a href="#" onclick="exportCsv(); return false;" class="btn btn-outline-success btn-sm rounded-2" title="Export CSV / Excel">
                <i class="fas fa-file-excel me-1"></i> Excel
              </a>
              <a href="#" onclick="exportPdf(); return false;" class="btn btn-outline-danger btn-sm rounded-2" title="Cetak Laporan PDF">
                <i class="fas fa-file-pdf me-1"></i> PDF
              </a>
            </div>
            <a href="<?= base_url('products') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-boxes me-1"></i> Master Produk
            </a>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableStockHistory" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="width: 130px;">Waktu Mutasi</th>
                <th>Produk &amp; Varian</th>
                <th style="width: 130px;" class="text-center">Tipe Mutasi</th>
                <th style="width: 110px;" class="text-end">Jumlah</th>
                <th style="width: 100px;" class="text-end">Stok Awal</th>
                <th style="width: 100px;" class="text-end">Stok Akhir</th>
                <th>Referensi / Keterangan</th>
                <th style="width: 110px;">Operator</th>
                <th style="width: 90px;" class="text-center">Aksi</th>
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
let dtStockHistory = null;

$(document).ready(function() {
  dtStockHistory = $('#tableStockHistory').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('stock-history/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.product_id = $('#filterProduct').val();
        d.type = $('#filterType').val();
        d.date_from = $('#filterDateFrom').val();
        d.date_to = $('#filterDateTo').val();
      }
    },
    columns: [
      { data: 'id', className: 'text-center text-muted small' },
      { data: 'created_at' },
      { data: 'product' },
      { data: 'type', className: 'text-center' },
      { data: 'qty', className: 'text-end' },
      { data: 'stock_before', className: 'text-end' },
      { data: 'stock_after', className: 'text-end' },
      { data: 'reference' },
      { data: 'user' },
      { data: 'action', orderable: false, className: 'text-center' }
    ],
    order: [[0, 'desc']],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari produk, SKU, catatan, resi...",
      lengthMenu: "Tampil _MENU_",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ mutasi",
      infoEmpty: "Tidak ada riwayat mutasi",
      zeroRecords: "Tidak ada data yang cocok",
      paginate: { first: "«", previous: "‹", next: "›", last: "»" }
    }
  });
});

function reloadTable() {
  if (dtStockHistory) {
    dtStockHistory.ajax.reload();
  }
}

function exportCsv() {
  const p = $('#filterProduct').val();
  const t = $('#filterType').val();
  const df = $('#filterDateFrom').val();
  const dt = $('#filterDateTo').val();
  let url = '<?= base_url('stock-history/export') ?>?product_id=' + encodeURIComponent(p) + '&type=' + encodeURIComponent(t) + '&date_from=' + encodeURIComponent(df) + '&date_to=' + encodeURIComponent(dt);
  window.location.href = url;
}

function exportPdf() {
  const p = $('#filterProduct').val();
  const t = $('#filterType').val();
  const df = $('#filterDateFrom').val();
  const dt = $('#filterDateTo').val();
  let url = '<?= base_url('stock-history/export-pdf') ?>?product_id=' + encodeURIComponent(p) + '&type=' + encodeURIComponent(t) + '&date_from=' + encodeURIComponent(df) + '&date_to=' + encodeURIComponent(dt);
  window.open(url, '_blank');
}
</script>
<?= $this->endSection() ?>
