<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-hand-holding-usd text-success me-2"></i>Master Rate Card KOL
        </h3>
        <p class="text-muted small mb-0">Katalog tarif komersial resmi per platform dan jenis konten kreator (Short Form, Live Shopping, Feed/Carousel, Long Form).</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('kol-rate-cards/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" id="btnExportCsv" title="Export CSV Data Rate Card">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddRateCard()">
          <i class="fas fa-plus me-1"></i> Tambah Rate Card
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
                <i class="fas fa-receipt fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Tarif Terdaftar</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_rate_cards']) ?></h3>
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
                <i class="fas fa-check-circle fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Rate Card Aktif</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['active_rate_cards']) ?></h3>
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
                <i class="fas fa-calculator fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Rata-Rata Tarif</span>
                <h3 class="fw-bold mb-0 text-warning">
                  Rp <?= number_format($stats['avg_rate'], 0, ',', '.') ?>
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
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-handshake fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Terbuka Nego</span>
                <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['negotiable_count']) ?></h3>
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
            <label class="form-label small text-muted mb-1">Tipe Konten</label>
            <select class="form-select form-select-sm" id="filterContentType" onchange="reloadTable()">
              <option value="">Semua Tipe</option>
              <option value="Short Form">Short Form Video / VT</option>
              <option value="Live Streaming">Live Streaming</option>
              <option value="Feed">Feed / Carousel</option>
              <option value="Long Form">Long Form / YouTube</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Status Nego</label>
            <select class="form-select form-select-sm" id="filterNego" onchange="reloadTable()">
              <option value="">Semua</option>
              <option value="1">Bisa Nego</option>
              <option value="0">Fixed / Nett</option>
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
          <table class="table table-hover align-middle mb-0" id="tableKolRateCards" style="width: 100%;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th width="35" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th width="50">ID</th>
                <th>KOL / Talent</th>
                <th>Platform</th>
                <th>Akun Handle</th>
                <th>Tipe Konten</th>
                <th>Tarif / Budget</th>
                <th>Nego</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th width="120" class="text-center">Aksi</th>
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

<!-- Modal Tambah / Edit Rate Card -->
<div class="modal fade" id="modalRateCardForm" tabindex="-1" aria-labelledby="modalRateCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalRateCardTitle">
          <i class="fas fa-hand-holding-usd me-2"></i>Tambah Rate Card KOL
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formRateCard" onsubmit="handleFormSubmit(event)">
        <div class="modal-body p-4">
          <input type="hidden" name="id" id="rateCardRecordId">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Pilih Akun Platform KOL <span class="text-danger">*</span></label>
              <select class="form-select" name="kol_platform_id" id="inputKolPlatformId" required onchange="handlePlatformSelectChange()">
                <option value="">-- Pilih Akun Media Sosial KOL --</option>
                <?php foreach ($kolPlatforms as $kp): ?>
                  <option value="<?= $kp['id'] ?>" data-kol="<?= $kp['kol_id'] ?>" data-platform="<?= $kp['platform_id'] ?>">
                    <?= esc($kp['kol_name']) ?> — <?= esc($kp['platform_name']) ?> (@<?= esc(ltrim($kp['username'], '@')) ?>) [<?= number_format((int)$kp['followers']) ?> followers]
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Pilih profil sosial media talent yang akan ditentukan tarifnya.</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Tipe Konten <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="content_type" id="inputContentType" list="contentTypePresets" placeholder="contoh: Short Form Video (VT)" required>
              <datalist id="contentTypePresets">
                <?php foreach ($contentTypes as $cName => $cMeta): ?>
                  <option value="<?= esc($cName) ?>"><?= esc($cMeta['desc']) ?></option>
                <?php endforeach; ?>
                <option value="1x Video TikTok (VT)"></option>
                <option value="TikTok Story (3 Frame)"></option>
                <option value="Live Streaming (60 Menit)"></option>
                <option value="Feed / Carousel Post (5 Slides)"></option>
                <option value="Instagram Reels"></option>
                <option value="Dedicated Long Form YouTube"></option>
                <option value="YouTube Ad Integration (60s)"></option>
              </datalist>
              <small class="text-muted">Pilih dari rekomendasi atau ketik format kustom.</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Tarif / Budget Nominal <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted fw-bold">Rp</span>
                <input type="number" class="form-control" name="rate" id="inputRate" placeholder="contoh: 5000000" min="0" step="1000" required>
              </div>
              <small class="text-muted" id="rateHelper">Masukkan angka tanpa titik atau koma.</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Mata Uang</label>
              <input type="text" class="form-control" name="currency_code" id="inputCurrencyCode" value="IDR" maxlength="3" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Status Negosiasi</label>
              <select class="form-select" name="negotiable" id="inputNegotiable">
                <option value="1">Bisa Nego (Negotiable)</option>
                <option value="0">Fixed / Nett (Non-Negotiable)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-dark">Status Keaktifan</label>
              <select class="form-select" name="is_active" id="inputIsActive">
                <option value="1">Aktif (Tampil di Katalog / Deals)</option>
                <option value="0">Nonaktif (Arsip / Ditangguhkan)</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold small text-dark">Catatan &amp; Cakupan Layanan</label>
              <textarea class="form-control" name="notes" id="inputNotes" rows="3" placeholder="Contoh: Termasuk 1x revisi konsep, penayangan di prime time, gratis link bio 3 hari."></textarea>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" id="btnSubmitRateCard">
            <i class="fas fa-save me-1"></i> Simpan Rate Card
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtKolRateCards;

$(document).ready(function() {
  dtKolRateCards = $('#tableKolRateCards').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= site_url('kol-rate-cards/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.platform_id  = $('#filterPlatform').val();
        d.kol_id       = $('#filterKol').val();
        d.content_type = $('#filterContentType').val();
        d.negotiable   = $('#filterNego').val();
        d.is_active    = $('#filterStatus').val();
      }
    },
    columns: [
      { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
      { data: 'id', className: 'text-muted small' },
      { data: 'kol_name' },
      { data: 'platform' },
      { data: 'username' },
      { data: 'content_type' },
      { data: 'rate' },
      { data: 'negotiable' },
      { data: 'status' },
      { data: 'created_at' },
      { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
    ],
    order: [[1, 'desc']],
    pageLength: 10,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari KOL, konten, tarif...",
      lengthMenu: "_MENU_ baris",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ rate card",
      infoEmpty: "Tidak ada data",
      zeroRecords: "Tidak ada rate card yang cocok",
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
    editRateCard(id);
  });

  // Delete button click
  $(document).on('click', '.btn-delete', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    deleteRateCard(id, name);
  });
});

function reloadTable() {
  if (dtKolRateCards) {
    dtKolRateCards.ajax.reload(null, false);
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

function modalAddRateCard() {
  $('#formRateCard')[0].reset();
  $('#rateCardRecordId').val('');
  $('#modalRateCardTitle').html('<i class="fas fa-plus-circle me-2"></i>Tambah Rate Card KOL');
  $('#modalRateCardForm').modal('show');
}

function editRateCard(id) {
  $.get('<?= site_url('kol-rate-cards/get-one') ?>/' + id, function(res) {
    if (res.success && res.data) {
      const row = res.data;
      $('#rateCardRecordId').val(row.id);
      $('#inputKolPlatformId').val(row.kol_platform_id);
      $('#inputContentType').val(row.content_type);
      $('#inputRate').val(row.final_rate || row.rate || row.rate_price);
      $('#inputCurrencyCode').val(row.currency_code || 'IDR');
      $('#inputNegotiable').val(row.negotiable);
      $('#inputIsActive').val(row.is_active);
      $('#inputNotes').val(row.notes);

      $('#modalRateCardTitle').html('<i class="fas fa-edit me-2"></i>Edit Rate Card #' + row.id);
      $('#modalRateCardForm').modal('show');
    } else {
      Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Tidak dapat memuat data.' });
    }
  }).fail(function() {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kegagalan server saat mengambil data.' });
  });
}

function handlePlatformSelectChange() {
  // auto trigger if needed
}

function handleFormSubmit(e) {
  e.preventDefault();
  const btn = $('#btnSubmitRateCard');
  const oldText = btn.html();
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

  const formData = $('#formRateCard').serialize();

  $.post('<?= site_url('kol-rate-cards/save') ?>', formData, function(res) {
    btn.prop('disabled', false).html(oldText);
    if (res.success) {
      $('#modalRateCardForm').modal('hide');
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
        html: res.message || 'Terjadi kesalahan validasi formulir.'
      });
    }
  }).fail(function() {
    btn.prop('disabled', false).html(oldText);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kegagalan jaringan atau server.' });
  });
}

function deleteRateCard(id, name) {
  Swal.fire({
    title: 'Hapus Rate Card?',
    html: 'Apakah Anda yakin ingin menghapus rate card <strong>' + name + '</strong>?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-rate-cards/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
          reloadTable();
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
      }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kegagalan sistem saat menghapus data.' });
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
    title: 'Hapus ' + selected.length + ' Rate Card?',
    text: 'Data tarif yang dihapus tidak dapat dipulihkan kembali.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus Semua',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-rate-cards/bulk-delete') ?>', { ids: selected }, function(res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
          reloadTable();
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
      }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kegagalan sistem saat menghapus massal.' });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
