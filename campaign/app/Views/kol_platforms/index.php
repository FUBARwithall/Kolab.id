<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-share-nodes text-primary me-2"></i>Akun Platform KOL
        </h3>
        <p class="text-muted small mb-0">Manajemen profil media sosial talent (TikTok, Instagram, YouTube, Twitter/X, Facebook) dan metrik audiens.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('kol-platforms/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" id="btnExportCsv" title="Export CSV Data Akun Platform">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddPlatform()">
          <i class="fas fa-plus me-1"></i> Tambah Akun Platform
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
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-share-nodes fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Akun Terhubung</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_accounts']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-check-circle fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Akun Terverifikasi</span>
                <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['verified_count']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-users fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Akumulasi Followers</span>
                <h3 class="fw-bold mb-0 text-success">
                  <?php 
                    $f = (int)$stats['total_followers'];
                    if ($f >= 1000000) {
                      echo number_format($f / 1000000, 1) . 'M';
                    } elseif ($f >= 1000) {
                      echo number_format($f / 1000, 1) . 'K';
                    } else {
                      echo number_format($f);
                    }
                  ?>
                </h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                <i class="fas fa-toggle-on fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Akun Aktif</span>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['active_count']) ?></h3>
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
            <label class="form-label small text-muted mb-1">Filter Platform</label>
            <select class="form-select form-select-sm" id="filterPlatform" onchange="reloadTable()">
              <option value="">Semua Platform</option>
              <?php foreach ($platforms as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter KOL / Talent</label>
            <select class="form-select form-select-sm" id="filterKol" onchange="reloadTable()">
              <option value="">Semua KOL</option>
              <?php foreach ($kols as $k): ?>
                <option value="<?= $k['id'] ?>"><?= esc($k['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Status Verified</label>
            <select class="form-select form-select-sm" id="filterVerified" onchange="reloadTable()">
              <option value="">Semua</option>
              <option value="1">Verified (Centang Biru)</option>
              <option value="0">Biasa</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Status Akun</label>
            <select class="form-select form-select-sm" id="filterStatus" onchange="reloadTable()">
              <option value="">Semua</option>
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
          <div class="col-md-2 text-md-end pt-3">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 d-none" id="btnBulkDelete" onclick="handleBulkDelete()">
              <i class="fas fa-trash me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableKolPlatforms" style="width: 100%;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th width="35" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th width="50">ID</th>
                <th>KOL / Talent</th>
                <th>Platform</th>
                <th>Username</th>
                <th>Followers</th>
                <th>Avg Views</th>
                <th>Avg ER</th>
                <th>Verified</th>
                <th>Status</th>
                <th>Last Synced</th>
                <th width="140" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- DataTables AJAX -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Tambah / Edit Akun Platform -->
<div class="modal fade" id="modalPlatformForm" tabindex="-1" aria-labelledby="modalPlatformTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalPlatformTitle">
          <i class="fas fa-share-nodes me-2"></i>Tambah Akun Platform KOL
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formPlatform" onsubmit="handleFormSubmit(event)">
        <div class="modal-body p-4">
          <input type="hidden" name="id" id="platformRecordId">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Pilih KOL / Talent <span class="text-danger">*</span></label>
              <select class="form-select" name="kol_id" id="inputKolId" required>
                <option value="">-- Pilih KOL --</option>
                <?php foreach ($kols as $k): ?>
                  <option value="<?= $k['id'] ?>"><?= esc($k['name']) ?> <?= !empty($k['email']) ? '('.esc($k['email']).')' : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Pilih Platform <span class="text-danger">*</span></label>
              <select class="form-select" name="platform_id" id="inputPlatformId" required onchange="handlePlatformChange()">
                <option value="">-- Pilih Platform --</option>
                <?php foreach ($platforms as $p): ?>
                  <option value="<?= $p['id'] ?>" data-code="<?= esc($p['code'] ?? '') ?>" data-base="<?= esc($p['base_url'] ?? '') ?>">
                    <?= esc($p['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Username Handle <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted">@</span>
                <input type="text" class="form-control" name="username" id="inputUsername" placeholder="contoh: jessicajane99" required oninput="autoGenerateUrl()">
              </div>
              <small class="text-muted">Masukkan username media sosial tanpa spasi.</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">URL Profil Langsung</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-link"></i></span>
                <input type="url" class="form-control" name="profile_url" id="inputProfileUrl" placeholder="https://instagram.com/jessicajane99">
              </div>
              <small class="text-muted">Otomatis terisi jika dibiarkan kosong.</small>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold small text-dark">Jumlah Followers</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-users"></i></span>
                <input type="number" class="form-control" name="followers" id="inputFollowers" value="0" min="0">
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold small text-dark">Average Views</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-eye"></i></span>
                <input type="number" class="form-control" name="avg_views" id="inputAvgViews" value="0" min="0">
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold small text-dark">Avg Engagement Rate (%)</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-chart-line"></i></span>
                <input type="number" step="0.0001" class="form-control" name="avg_er" id="inputAvgEr" value="0.0000" min="0" max="100">
              </div>
              <small class="text-muted">Contoh: 0.0450 atau 4.5</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Status Verified (Centang Biru)</label>
              <select class="form-select" name="is_verified" id="inputIsVerified">
                <option value="0">Tidak (Biasa)</option>
                <option value="1">Ya (Centang Biru / Verified)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Status Akun</label>
              <select class="form-select" name="is_active" id="inputIsActive">
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" id="btnSubmitPlatform">
            <i class="fas fa-save me-1"></i> Simpan Akun Platform
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtKolPlatforms;

$(document).ready(function() {
  dtKolPlatforms = $('#tableKolPlatforms').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= site_url('kol-platforms/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.platform_id = $('#filterPlatform').val();
        d.kol_id      = $('#filterKol').val();
        d.is_verified = $('#filterVerified').val();
        d.is_active   = $('#filterStatus').val();
      }
    },
    columns: [
      { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
      { data: 'id', className: 'text-muted small' },
      { data: 'kol_name' },
      { data: 'platform_badge' },
      { data: 'username' },
      { data: 'followers' },
      { data: 'avg_views' },
      { data: 'avg_er' },
      { data: 'is_verified' },
      { data: 'status' },
      { data: 'last_synced_at' },
      { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
    ],
    order: [[1, 'desc']],
    pageLength: 10,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari KOL, username, platform...",
      lengthMenu: "_MENU_ baris",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ akun platform",
      infoEmpty: "Tidak ada data",
      zeroRecords: "Tidak ada akun platform yang cocok",
      paginate: {
        previous: '<i class="fas fa-chevron-left"></i>',
        next: '<i class="fas fa-chevron-right"></i>'
      }
    },
    drawCallback: function() {
      updateBulkUi();
    }
  });

  // Check all handling
  $('#checkAll').on('change', function() {
    const isChecked = $(this).is(':checked');
    $('.item-check').prop('checked', isChecked);
    updateBulkUi();
  });

  $(document).on('change', '.item-check', function() {
    updateBulkUi();
  });

  // Edit button click
  $(document).on('click', '.btn-edit', function() {
    const id = $(this).data('id');
    editPlatform(id);
  });

  // Sync button click
  $(document).on('click', '.btn-sync', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    syncPlatform(id, name, $(this));
  });

  // Delete button click
  $(document).on('click', '.btn-delete', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    deletePlatform(id, name);
  });
});

function reloadTable() {
  if (dtKolPlatforms) {
    dtKolPlatforms.ajax.reload(null, false);
  }
}

function updateBulkUi() {
  const selectedCount = $('.item-check:checked').length;
  $('#selectedCount').text(selectedCount);
  if (selectedCount > 0) {
    $('#btnBulkDelete').removeClass('d-none');
  } else {
    $('#btnBulkDelete').addClass('d-none');
    $('#checkAll').prop('checked', false);
  }
}

function modalAddPlatform() {
  $('#formPlatform')[0].reset();
  $('#platformRecordId').val('');
  $('#modalPlatformTitle').html('<i class="fas fa-plus-circle me-2"></i>Tambah Akun Platform KOL');
  $('#modalPlatformForm').modal('show');
}

function editPlatform(id) {
  $.get('<?= site_url('kol-platforms/get-one') ?>/' + id, function(res) {
    if (res.success && res.data) {
      const row = res.data;
      $('#platformRecordId').val(row.id);
      $('#inputKolId').val(row.kol_id);
      $('#inputPlatformId').val(row.platform_id);
      $('#inputUsername').val(row.username);
      $('#inputProfileUrl').val(row.profile_url);
      $('#inputFollowers').val(row.followers);
      $('#inputAvgViews').val(row.avg_views);
      $('#inputAvgEr').val(row.avg_er);
      $('#inputIsVerified').val(row.is_verified);
      $('#inputIsActive').val(row.is_active);

      $('#modalPlatformTitle').html('<i class="fas fa-edit me-2"></i>Edit Akun Platform #' + row.id);
      $('#modalPlatformForm').modal('show');
    } else {
      Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Tidak dapat memuat data.' });
    }
  }).fail(function() {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem saat memuat akun platform.' });
  });
}

function autoGenerateUrl() {
  const selectedOpt = $('#inputPlatformId option:selected');
  const baseUrl = selectedOpt.data('base') || '';
  const username = $('#inputUsername').val().trim().replace(/^@+/, '');
  
  if (baseUrl && username && !$('#inputProfileUrl').val()) {
    $('#inputProfileUrl').val(baseUrl.replace(/\/+$/, '') + '/' + username);
  }
}

function handlePlatformChange() {
  autoGenerateUrl();
}

function handleFormSubmit(e) {
  e.preventDefault();
  const btn = $('#btnSubmitPlatform');
  const oldText = btn.html();
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

  const formData = $('#formPlatform').serialize();

  $.post('<?= site_url('kol-platforms/save') ?>', formData, function(res) {
    btn.prop('disabled', false).html(oldText);
    if (res.success) {
      $('#modalPlatformForm').modal('hide');
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: res.message,
        timer: 1500,
        showConfirmButton: false
      });
      reloadTable();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Peringatan',
        html: res.message || 'Terjadi kesalahan dalam validasi formulir.'
      });
    }
  }).fail(function() {
    btn.prop('disabled', false).html(oldText);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kegagalan jaringan atau server.' });
  });
}

function syncPlatform(id, name, btnEl) {
  const icon = btnEl.find('i');
  icon.addClass('fa-spin');
  btnEl.prop('disabled', true);

  $.get('<?= site_url('kol-platforms/sync') ?>/' + id, function(res) {
    icon.removeClass('fa-spin');
    btnEl.prop('disabled', false);

    if (res.success) {
      Swal.fire({
        icon: 'success',
        title: 'Sinkronisasi Berhasil',
        text: res.message,
        timer: 2000,
        showConfirmButton: false
      });
      reloadTable();
    } else {
      Swal.fire({
        icon: 'warning',
        title: 'Sinkronisasi Parsial',
        text: res.message
      });
    }
  }).fail(function() {
    icon.removeClass('fa-spin');
    btnEl.prop('disabled', false);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi service sinkronisasi.' });
  });
}

function deletePlatform(id, name) {
  Swal.fire({
    title: 'Hapus Akun Platform?',
    html: 'Apakah Anda yakin ingin menghapus akun platform <strong>' + name + '</strong>?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-platforms/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
          reloadTable();
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
      }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada server saat menghapus akun.' });
      });
    }
  });
}

function handleBulkDelete() {
  const selected = [];
  $('.item-check:checked').each(function() {
    selected.push($(this).val());
  });

  if (selected.length === 0) return;

  Swal.fire({
    title: 'Hapus ' + selected.length + ' Akun Platform?',
    text: 'Data akun yang dihapus tidak dapat dipulihkan kembali.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus Semua',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-platforms/bulk-delete') ?>', { ids: selected }, function(res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
          reloadTable();
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
      }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem saat menghapus massal.' });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
