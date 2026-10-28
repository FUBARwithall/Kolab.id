<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

  <!-- 1. Mini Dashboard Summary Cards -->
  <div class="row g-3 mb-4" id="summaryCards">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
            <i class="fas fa-boxes fa-lg"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Produk</div>
            <h4 class="mb-0 fw-bold text-dark" id="summaryTotalProducts"><?= esc($summary['total_products'] ?? 0) ?></h4>
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
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Stok Sampel</div>
            <h4 class="mb-0 fw-bold text-dark" id="summaryTotalStock"><?= number_format($summary['total_stock'] ?? 0, 0, ',', '.') ?> <small class="text-muted fs-6">unit</small></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
            <i class="fas fa-check-circle fa-lg"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Produk Aktif</div>
            <h4 class="mb-0 fw-bold text-dark" id="summaryActiveProducts"><?= esc($summary['active_products'] ?? 0) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
            <i class="fas fa-exclamation-triangle fa-lg"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Stok Menipis (≤10)</div>
            <h4 class="mb-0 fw-bold <?= ($summary['low_stock_products'] ?? 0) > 0 ? 'text-danger' : 'text-dark' ?>" id="summaryLowStock"><?= esc($summary['low_stock_products'] ?? 0) ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Main Data Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div class="d-flex align-items-center gap-2">
        <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-box-open text-primary me-2"></i>Daftar Master Produk Sampel</h5>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Filter Status -->
        <select class="form-select form-select-sm rounded-2 w-auto" id="filterStatus" onchange="reloadTable()">
          <option value="">Semua Status</option>
          <option value="1">Aktif Saja</option>
          <option value="0">Nonaktif</option>
        </select>
        <a href="<?= base_url('stock-history') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
          <i class="fas fa-history me-1"></i> Riwayat Stok
        </a>
        <button type="button" class="btn btn-primary btn-sm rounded-2 shadow-sm" onclick="openAddModal()">
          <i class="fas fa-plus me-1"></i> Tambah Produk
        </button>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table id="productTable" class="table table-hover align-middle w-100" style="font-size: 13.5px;">
          <thead class="table-light text-secondary text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
            <tr>
              <th style="width: 50px;">ID</th>
              <th style="width: 60px;">Foto</th>
              <th style="width: 140px;">SKU</th>
              <th>Nama Produk &amp; Deskripsi</th>
              <th style="width: 130px;">Harga Satuan</th>
              <th style="width: 110px;">Stok</th>
              <th style="width: 100px;">Status</th>
              <th style="width: 110px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Modal 1: Tambah / Edit Produk -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold" id="productModalLabel"><i class="fas fa-box me-2 text-primary"></i>Tambah Produk Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="productForm" enctype="multipart/form-data">
        <input type="hidden" name="id" id="productId">
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">SKU Produk <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" name="sku" id="productSku" placeholder="Contoh: PRD-PARFUM-01" required>
              <div class="invalid-feedback" id="err_sku"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" id="productName" placeholder="Contoh: Extrait de Parfum 50ml" required>
              <div class="invalid-feedback" id="err_name"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
              <input type="number" step="1000" class="form-control" name="unit_price" id="productPrice" placeholder="0" required>
              <div class="invalid-feedback" id="err_unit_price"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Jumlah Stok Awal <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="stock_qty" id="productStock" placeholder="0" required>
              <div class="invalid-feedback" id="err_stock_qty"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status Produk <span class="text-danger">*</span></label>
              <select class="form-select" name="is_active" id="productStatus" required>
                <option value="1">Aktif (Tersedia untuk Kampanye)</option>
                <option value="0">Nonaktif (Arsip / Tidak Ditampilkan)</option>
              </select>
              <div class="invalid-feedback" id="err_is_active"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Foto Produk</label>
              <input type="file" class="form-control" name="image_file" id="productImage" accept="image/*">
              <small class="text-muted">Format JPG/PNG/WEBP, Maksimal 2MB</small>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Deskripsi / Spesifikasi Produk</label>
              <textarea class="form-control" name="description" id="productDescription" rows="3" placeholder="Rincian catatan produk atau panduan sampel..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveProduct">
            <i class="fas fa-save me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Penyesuaian Stok Cepat -->
<div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold" id="stockModalLabel"><i class="fas fa-boxes me-2 text-info"></i>Penyesuaian Stok Sampel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="stockForm">
        <input type="hidden" name="product_id" id="stockProductId">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="text-muted small">Nama Produk</label>
            <div class="fw-bold fs-6" id="stockProductName">-</div>
            <div class="text-secondary small">Stok Saat Ini: <span class="badge bg-secondary" id="stockCurrentVal">0</span> unit</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipe Penyesuaian <span class="text-danger">*</span></label>
            <select class="form-select" name="type" id="stockType" required>
              <option value="IN">Stok Masuk (Penambahan dari Supplier / Produksi)</option>
              <option value="OUT">Stok Keluar (Pengiriman Ekspedisi / Rusak)</option>
              <option value="ADJUSTMENT">Koreksi Stok Opname (Atur Stok Baru)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah (Qty) <span class="text-danger">*</span></label>
            <input type="number" min="1" class="form-control" name="qty" id="stockQty" placeholder="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan / Keterangan</label>
            <textarea class="form-control" name="notes" id="stockNotes" rows="2" placeholder="Alasan penyesuaian (misal: Penerimaan 50 botol dari pabrik)..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-info text-white rounded-2 px-4" id="btnSaveStock">
            <i class="fas fa-check me-1"></i> Terapkan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let table;

$(document).ready(function() {
  // Inisialisasi DataTables Server-Side
  table = $('#productTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('products/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.filter_status = $('#filterStatus').val();
      }
    },
    columns: [
      { data: 'id' },
      { data: 'thumb', orderable: false, className: 'text-center' },
      { data: 'sku' },
      { data: 'name' },
      { data: 'unit_price' },
      { data: 'stock', className: 'text-center' },
      { data: 'status', className: 'text-center' },
      { data: 'action', orderable: false, className: 'text-center' }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari produk / SKU...",
      lengthMenu: "Tampilkan _MENU_ baris",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
      infoEmpty: "Tidak ada data produk",
      zeroRecords: "Data produk tidak ditemukan",
      processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data...',
      paginate: {
        first: "«",
        last: "»",
        next: "›",
        previous: "‹"
      }
    }
  });

  // Handle Submit Form Produk (Tambah / Edit)
  $('#productForm').on('submit', function(e) {
    e.preventDefault();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    let formData = new FormData(this);
    let btn = $('#btnSaveProduct');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

    $.ajax({
      url: '<?= base_url('products/save') ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan');
        if (res.success) {
          bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: res.message,
            timer: 1800,
            showConfirmButton: false
          });
          table.ajax.reload(null, false);
          if (res.summary) updateSummaryCards(res.summary);
        } else {
          if (res.messages) {
            $.each(res.messages, function(field, msg) {
              $('#err_' + field).text(msg);
              $('[name="' + field + '"]').addClass('is-invalid');
            });
          } else {
            Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
          }
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan');
        Swal.fire('Error', 'Gagal menghubungi server.', 'error');
      }
    });
  });

  // Handle Submit Form Penyesuaian Stok
  $('#stockForm').on('submit', function(e) {
    e.preventDefault();
    let btn = $('#btnSaveStock');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

    $.ajax({
      url: '<?= base_url('products/adjust-stock') ?>',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Terapkan Perubahan');
        if (res.success) {
          bootstrap.Modal.getInstance(document.getElementById('stockModal')).hide();
          Swal.fire({
            icon: 'success',
            title: 'Stok Diperbarui!',
            text: res.message,
            timer: 1800,
            showConfirmButton: false
          });
          table.ajax.reload(null, false);
          if (res.summary) updateSummaryCards(res.summary);
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Terapkan Perubahan');
        Swal.fire('Error', 'Gagal memproses penyesuaian stok.', 'error');
      }
    });
  });
});

function reloadTable() {
  table.ajax.reload();
}

function updateSummaryCards(s) {
  $('#summaryTotalProducts').text(s.total_products);
  $('#summaryTotalStock').html(Number(s.total_stock).toLocaleString('id-ID') + ' <small class="text-muted fs-6">unit</small>');
  $('#summaryActiveProducts').text(s.active_products);
  $('#summaryLowStock').text(s.low_stock_products);
  if (s.low_stock_products > 0) {
    $('#summaryLowStock').addClass('text-danger').removeClass('text-dark');
  } else {
    $('#summaryLowStock').addClass('text-dark').removeClass('text-danger');
  }
}

function openAddModal() {
  $('#productForm')[0].reset();
  $('#productId').val('');
  $('.is-invalid').removeClass('is-invalid');
  $('.invalid-feedback').text('');
  $('#productModalLabel').html('<i class="fas fa-box me-2 text-primary"></i>Tambah Produk Baru');
  new bootstrap.Modal(document.getElementById('productModal')).show();
}

function editProduct(id) {
  $.get('<?= base_url('products/get-one') ?>/' + id, function(res) {
    if (res.success && res.data) {
      let p = res.data;
      $('#productId').val(p.id);
      $('#productSku').val(p.sku);
      $('#productName').val(p.name);
      $('#productPrice').val(p.unit_price);
      $('#productStock').val(p.stock_qty);
      $('#productStatus').val(p.is_active);
      $('#productDescription').val(p.description);

      $('.is-invalid').removeClass('is-invalid');
      $('.invalid-feedback').text('');
      $('#productModalLabel').html('<i class="fas fa-edit me-2 text-primary"></i>Edit Data Produk');
      new bootstrap.Modal(document.getElementById('productModal')).show();
    } else {
      Swal.fire('Error', res.message || 'Data tidak ditemukan.', 'error');
    }
  });
}

function openStockModal(id, name, currentStock) {
  $('#stockForm')[0].reset();
  $('#stockProductId').val(id);
  $('#stockProductName').text(name);
  $('#stockCurrentVal').text(currentStock);
  new bootstrap.Modal(document.getElementById('stockModal')).show();
}

function deleteProduct(id) {
  Swal.fire({
    title: 'Hapus Produk ini?',
    text: "Jika produk terkait dengan kampanye, statusnya akan otomatis diubah menjadi Nonaktif (Arsip).",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Lanjutkan!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= base_url('products/delete') ?>/' + id, function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Selesai!',
            text: res.message,
            timer: 1800,
            showConfirmButton: false
          });
          table.ajax.reload(null, false);
          if (res.summary) updateSummaryCards(res.summary);
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }
      }, 'json');
    }
  });
}
</script>
<?= $this->endSection() ?>
