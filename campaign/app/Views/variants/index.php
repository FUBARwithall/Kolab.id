<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-layer-group text-primary me-2"></i>Variant Konsep Konten
        </h3>
        <p class="text-muted small mb-0">Master data jenis format deliverable &amp; konsep kreatif konten KOL (Video Review, Unboxing, GRWM, dll).</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('variants/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" title="Export CSV Data Konsep">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddVariant()">
          <i class="fas fa-plus me-1"></i> Tambah Konsep Baru
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
                <i class="fas fa-layer-group fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Varian Konsep</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h3>
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
                <i class="fas fa-check-circle fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Konsep Aktif (Ready)</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['active']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-secondary-subtle text-secondary p-3 me-3">
                <i class="fas fa-pause-circle fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Nonaktif</span>
                <h3 class="fw-bold mb-0 text-secondary"><?= number_format($stats['inactive']) ?></h3>
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
            <label class="form-label small text-muted mb-1">Filter Status</label>
            <select class="form-select form-select-sm" id="filterStatus" onchange="reloadTable()">
              <option value="">-- Semua Status --</option>
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableVariants" style="width: 100%;">
            <thead class="bg-light text-secondary border-bottom">
              <tr>
                <th style="width: 60px;">ID</th>
                <th style="width: 220px;">Nama Konsep Konten</th>
                <th style="width: 160px;">Kode Konsep</th>
                <th>Deskripsi Konsep</th>
                <th style="width: 110px;">Status</th>
                <th style="width: 140px;">Dibuat Pada</th>
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

<!-- Modal Form Tambah / Edit Variant -->
<div class="modal fade" id="modalVariantForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formVariant" onsubmit="submitVariantForm(event)">
        <input type="hidden" name="id" id="varId" value="">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="modalTitle">
            <i class="fas fa-layer-group me-2"></i>Tambah Varian Konsep Baru
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Konsep Konten <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" id="varName" required placeholder="Contoh: Video Review, Unboxing, GRWM">
            <small class="text-muted">Nama format atau konsep deliverable konten yang dikenali publik.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Konsep (Unique Code) <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" name="code" id="varCode" required placeholder="Contoh: VIDEO_REVIEW, UNBOXING" style="text-transform: uppercase;">
            <small class="text-muted">Digunakan sebagai identifier sistem. Format UPPERCASE.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi Konsep</label>
            <textarea class="form-control" name="description" id="varDesc" rows="3" placeholder="Penjelasan singkat instruksi atau karakteristik konsep konten ini..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Status Keaktifan</label>
            <select class="form-select" name="is_active" id="varIsActive">
              <option value="1">Aktif (Dapat dipilih di kampanye)</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" id="btnSubmitVar">
            <i class="fas fa-save me-1"></i> Simpan Varian
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtVariants;

$(document).ready(function() {
  dtVariants = $('#tableVariants').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= site_url('variants/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.is_active = $('#filterStatus').val();
      }
    },
    columns: [
      { data: 'id' },
      { data: 'name' },
      { data: 'code' },
      { data: 'description' },
      { data: 'is_active' },
      { data: 'created_at' },
      { data: 'action', orderable: false, searchable: false }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari konsep, kode, deskripsi...",
      lengthMenu: "_MENU_ baris per halaman",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ varian konsep",
      infoEmpty: "Tidak ada data",
      zeroRecords: "Tidak ada varian konsep yang cocok",
      paginate: {
        previous: '<i class="fas fa-chevron-left"></i>',
        next: '<i class="fas fa-chevron-right"></i>'
      }
    }
  });
});

function reloadTable() {
  if (dtVariants) {
    dtVariants.ajax.reload(null, false);
  }
}

function modalAddVariant() {
  $('#formVariant')[0].reset();
  $('#varId').val('');
  $('#modalTitle').html('<i class="fas fa-plus-circle me-2"></i>Tambah Varian Konsep Baru');
  $('#modalVariantForm').modal('show');
}

function editVariant(id) {
  $.get('<?= site_url('variants/get-one') ?>/' + id, function(res) {
    if (res.success && res.data) {
      const row = res.data;
      $('#varId').val(row.id);
      $('#varName').val(row.name);
      $('#varCode').val(row.code);
      $('#varDesc').val(row.description || '');
      $('#varIsActive').val(row.is_active);

      $('#modalTitle').html('<i class="fas fa-edit me-2"></i>Edit Varian Konsep #' + row.id);
      $('#modalVariantForm').modal('show');
    } else {
      Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Tidak dapat memuat data.' });
    }
  }).fail(function() {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
  });
}

function submitVariantForm(e) {
  e.preventDefault();
  const btn = $('#btnSubmitVar');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

  $.post('<?= site_url('variants/save') ?>', $('#formVariant').serialize(), function(res) {
    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Varian');
    if (res.success) {
      $('#modalVariantForm').modal('hide');
      reloadTable();
      Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
    }
  }).fail(function(xhr) {
    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Varian');
    const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: err });
  });
}

function deleteVariant(id) {
  Swal.fire({
    title: 'Hapus Varian Konsep?',
    text: 'Apakah Anda yakin ingin menghapus varian konsep #' + id + ' ini?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('variants/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          reloadTable();
          Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
        }
      }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus varian.';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
