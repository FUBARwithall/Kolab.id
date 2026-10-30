<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-tags text-warning me-2"></i>Kategori &amp; Tagging KOL
        </h3>
        <p class="text-muted small mb-0">Manajemen penugasan kategori niche kreator (Beauty, Tech, Gaming, Culinary, Lifestyle, dll).</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('kol-categories/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" title="Export CSV Data Kategori KOL">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddKolCategory()">
          <i class="fas fa-plus me-1"></i> Tambah Kategori KOL
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Stat Cards Overview -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-tags fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Tagging Kategori</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_assignments']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-user-check fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">KOL Terkategori</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['total_kols_tagged']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-layer-group fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Niche / Kategori</span>
                <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['total_categories']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Kategori</label>
            <select class="form-select form-select-sm" id="filterCategory" onchange="reloadTable()">
              <option value="">-- Semua Kategori --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Talent KOL</label>
            <select class="form-select form-select-sm" id="filterKol" onchange="reloadTable()">
              <option value="">-- Semua Talent KOL --</option>
              <?php foreach ($kols as $k): ?>
                <option value="<?= $k['id'] ?>"><?= esc($k['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableKolCategories" style="width: 100%;">
            <thead class="bg-light text-secondary border-bottom">
              <tr>
                <th style="width: 60px;">ID</th>
                <th>Talent KOL</th>
                <th style="width: 200px;">Niche / Kategori</th>
                <th style="width: 160px;">Ditambahkan Pada</th>
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

<!-- Modal Form Tambah / Edit Kategori KOL -->
<div class="modal fade" id="modalKolCategoryForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formKolCategory" onsubmit="submitKolCategoryForm(event)">
        <input type="hidden" name="id" id="kcId" value="">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="modalTitle">
            <i class="fas fa-tags me-2"></i>Tambah Kategori KOL
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Talent KOL <span class="text-danger">*</span></label>
            <select class="form-select" name="kol_id" id="kcKolId" required>
              <option value="">-- Pilih Talent KOL --</option>
              <?php foreach ($kols as $k): ?>
                <option value="<?= $k['id'] ?>"><?= esc($k['name']) ?> (<?= esc($k['email'] ?: 'No email') ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Niche / Kategori <span class="text-danger">*</span></label>
            <select class="form-select" name="category_id" id="kcCategoryId" required>
              <option value="">-- Pilih Kategori --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" id="btnSubmitKc">
            <i class="fas fa-save me-1"></i> Simpan Penugasan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtKolCategories;

$(document).ready(function() {
  dtKolCategories = $('#tableKolCategories').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= site_url('kol-categories/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.category_id = $('#filterCategory').val();
        d.kol_id = $('#filterKol').val();
      }
    },
    columns: [
      { data: 'id' },
      { data: 'kol' },
      { data: 'category' },
      { data: 'created_at' },
      { data: 'action', orderable: false, searchable: false }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari KOL atau kategori...",
      lengthMenu: "_MENU_ baris per halaman",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ kategori KOL",
      infoEmpty: "Tidak ada data",
      zeroRecords: "Tidak ada data kategori KOL yang cocok",
      paginate: {
        previous: '<i class="fas fa-chevron-left"></i>',
        next: '<i class="fas fa-chevron-right"></i>'
      }
    }
  });
});

function reloadTable() {
  if (dtKolCategories) {
    dtKolCategories.ajax.reload(null, false);
  }
}

function modalAddKolCategory() {
  $('#formKolCategory')[0].reset();
  $('#kcId').val('');
  $('#modalTitle').html('<i class="fas fa-plus-circle me-2"></i>Tambah Kategori KOL');
  $('#modalKolCategoryForm').modal('show');
}

function editKolCategory(id) {
  $.get('<?= site_url('kol-categories/get-one') ?>/' + id, function(res) {
    if (res.success && res.data) {
      const row = res.data;
      $('#kcId').val(row.id);
      $('#kcKolId').val(row.kol_id);
      $('#kcCategoryId').val(row.category_id);

      $('#modalTitle').html('<i class="fas fa-edit me-2"></i>Edit Penugasan Kategori #' + row.id);
      $('#modalKolCategoryForm').modal('show');
    } else {
      Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Tidak dapat memuat data.' });
    }
  }).fail(function() {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
  });
}

function submitKolCategoryForm(e) {
  e.preventDefault();
  const btn = $('#btnSubmitKc');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

  $.post('<?= site_url('kol-categories/save') ?>', $('#formKolCategory').serialize(), function(res) {
    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Penugasan');
    if (res.success) {
      $('#modalKolCategoryForm').modal('hide');
      reloadTable();
      Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
    }
  }).fail(function(xhr) {
    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Penugasan');
    const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: err });
  });
}

function deleteKolCategory(id) {
  Swal.fire({
    title: 'Hapus Kategori KOL?',
    text: 'Apakah Anda yakin ingin menghapus penugasan kategori #' + id + ' ini?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-categories/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          reloadTable();
          Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
        }
      }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus kategori KOL.';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
