<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-star-half-alt text-warning me-2"></i>Score Histories</h3>
        <p class="text-muted small mb-0">Audit riwayat perhitungan dan histori evaluasi skor performa KOL (Lifetime &amp; Kolaborasi).</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('kols') ?>" class="text-decoration-none">KOLs</a></li>
          <li class="breadcrumb-item active" aria-current="page">Score Histories</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-primary">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Riwayat Skor</span>
                <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format((int)($metrics['total_scores'] ?? 0), 0, ',', '.') ?></h3>
                <small class="text-muted" style="font-size: 11px;">Mencakup <?= (int)($metrics['total_kols'] ?? 0) ?> KOL aktif</small>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                <i class="fas fa-database fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-purple" style="border-left-color: #6f42c1 !important;">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Avg Lifetime Score</span>
                <h3 class="fw-bold mb-0 text-purple mt-1" style="color: #6f42c1;"><?= number_format((float)($metrics['avg_lifetime'] ?? 0), 2) ?></h3>
                <small class="text-muted" style="font-size: 11px;"><?= (int)($metrics['total_lifetime'] ?? 0) ?> kalkulasi lifetime</small>
              </div>
              <div class="rounded-circle p-3" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                <i class="fas fa-chart-line fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-info">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Avg Collab Score</span>
                <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format((float)($metrics['avg_collab'] ?? 0), 2) ?></h3>
                <small class="text-muted" style="font-size: 11px;"><?= (int)($metrics['total_collab'] ?? 0) ?> per kampanye</small>
              </div>
              <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                <i class="fas fa-handshake fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-success">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Skor Tertinggi</span>
                <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format((float)($metrics['max_score'] ?? 0), 2) ?></h3>
                <small class="text-muted" style="font-size: 11px;">Skor terendah: <?= number_format((float)($metrics['min_score'] ?? 0), 2) ?></small>
              </div>
              <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                <i class="fas fa-trophy fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
          
          <!-- Filters -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div style="min-width: 170px;">
              <select class="form-select form-select-sm rounded-2" id="filterScoreType" onchange="reloadScoreHistoriesTable()">
                <option value="">Semua Tipe Skor</option>
                <?php foreach ($scoreTypes as $st): ?>
                  <option value="<?= $st ?>" <?= ($filters['score_type'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 200px;">
              <select class="form-select form-select-sm rounded-2" id="filterKol" onchange="reloadScoreHistoriesTable()">
                <option value="">Semua Talent / KOL</option>
                <?php foreach ($allKols as $k): ?>
                  <option value="<?= $k['id'] ?>" <?= ($filters['kol_id'] ?? '') == $k['id'] ? 'selected' : '' ?>><?= esc($k['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 200px;">
              <select class="form-select form-select-sm rounded-2" id="filterCampaign" onchange="reloadScoreHistoriesTable()">
                <option value="">Semua Kampanye</option>
                <?php foreach ($allCampaigns as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($filters['campaign_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-2" title="Reset Filter" onclick="resetScoreHistoriesFilters()">
              <i class="fas fa-undo"></i>
            </button>
          </div>

          <!-- Actions & Exports -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('kols') ?>" class="btn btn-outline-primary btn-sm rounded-2 px-3">
              <i class="fas fa-users me-1"></i> Direktori KOL
            </a>
            <div class="btn-group">
              <a href="<?= base_url('score-histories/export') ?>" class="btn btn-outline-success btn-sm rounded-2">
                <i class="fas fa-file-excel me-1"></i> Excel / CSV
              </a>
              <a href="<?= base_url('score-histories/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2">
                <i class="fas fa-file-pdf me-1"></i> Print / PDF
              </a>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 d-none" id="btnBulkDeleteScoreHistories" onclick="bulkDeleteSelectedScoreHistories()">
              <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="bulkCountScoreHistories">0</span>)
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="scoreHistoriesTable" style="width: 100%;">
            <thead class="table-light text-uppercase text-secondary" style="font-size: 11px; letter-spacing: 0.5px;">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllScoreHistories">
                </th>
                <th>KOL / Talent</th>
                <th>Kampanye Terkait</th>
                <th>Tipe Skor</th>
                <th>Nilai Skor Performa</th>
                <th>Waktu Perhitungan</th>
                <th style="width: 130px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody style="font-size: 13px;">
              <!-- DataTables AJAX Loaded -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Snapshot Riwayat Skor -->
<div class="modal fade" id="modalScoreSnapshot" tabindex="-1" aria-labelledby="modalScoreSnapshotLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold" id="modalScoreSnapshotLabel">
          <i class="fas fa-certificate text-warning me-2"></i>Detail Evaluasi Skor KOL
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="modalScoreSnapshotBody">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted small mt-2">Memuat detail evaluasi skor...</p>
        </div>
      </div>
      <div class="modal-footer bg-light border-top py-2">
        <button type="button" class="btn btn-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Tutup</button>
        <a href="#" id="modalScoreDetailLink" class="btn btn-primary btn-sm rounded-2 px-3">Buka Halaman Lengkap</a>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let scoreHistoriesTable = null;

$(document).ready(function() {
  scoreHistoriesTable = $('#scoreHistoriesTable').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    order: [[0, 'desc']],
    ajax: {
      url: '<?= base_url('score-histories/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.score_type  = $('#filterScoreType').val();
        d.kol_id      = $('#filterKol').val();
        d.campaign_id = $('#filterCampaign').val();
        d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
      }
    },
    columns: [
      { data: 'checkbox', orderable: false, className: 'text-center' },
      { data: 'kol', orderable: true },
      { data: 'campaign', orderable: true },
      { data: 'score_type', orderable: true },
      { data: 'score', orderable: true },
      { data: 'calculated_at', orderable: true },
      { data: 'actions', orderable: false, className: 'text-center' }
    ],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari riwayat, nama KOL, tipe skor...",
      lengthMenu: "_MENU_ data per halaman",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ entri skor",
      infoEmpty: "Tidak ada data riwayat skor yang cocok",
      zeroRecords: "Data riwayat skor tidak ditemukan",
      processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
    },
    drawCallback: function() {
      updateBulkButtonState();
      $('#checkAllScoreHistories').prop('checked', false);
    }
  });

  // Check all handling
  $('#checkAllScoreHistories').on('change', function() {
    const isChecked = $(this).is(':checked');
    $('.hist-row-check').prop('checked', isChecked);
    updateBulkButtonState();
  });

  $(document).on('change', '.hist-row-check', function() {
    updateBulkButtonState();
    const total = $('.hist-row-check').length;
    const checked = $('.hist-row-check:checked').length;
    $('#checkAllScoreHistories').prop('checked', total > 0 && total === checked);
  });
});

function reloadScoreHistoriesTable() {
  if (scoreHistoriesTable) {
    scoreHistoriesTable.ajax.reload(null, false);
  }
}

function resetScoreHistoriesFilters() {
  $('#filterScoreType').val('');
  $('#filterKol').val('');
  $('#filterCampaign').val('');
  reloadScoreHistoriesTable();
}

function updateBulkButtonState() {
  const checkedCount = $('.hist-row-check:checked').length;
  $('#bulkCountScoreHistories').text(checkedCount);
  if (checkedCount > 0) {
    $('#btnBulkDeleteScoreHistories').removeClass('d-none');
  } else {
    $('#btnBulkDeleteScoreHistories').addClass('d-none');
  }
}

function viewScoreSnapshot(id) {
  const modal = new bootstrap.Modal(document.getElementById('modalScoreSnapshot'));
  $('#modalScoreSnapshotBody').html(`
    <div class="text-center py-4">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted small mt-2">Memuat snapshot skor...</p>
    </div>
  `);
  $('#modalScoreDetailLink').attr('href', '<?= base_url('score-histories/view') ?>/' + id);
  modal.show();

  $.ajax({
    url: '<?= base_url('score-histories/get-one') ?>/' + id,
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (!res.success || !res.data) {
        $('#modalScoreSnapshotBody').html('<div class="alert alert-danger mb-0">Gagal memuat data riwayat skor.</div>');
        return;
      }
      const item = res.data;
      const scoreVal = parseFloat(item.score || 0);

      let badgeBg = 'success';
      let grade = 'A (Excellent)';
      if (scoreVal < 55) {
        badgeBg = 'danger';
        grade = 'D (Poor)';
      } else if (scoreVal < 70) {
        badgeBg = 'warning text-dark';
        grade = 'C (Fair)';
      } else if (scoreVal < 85) {
        badgeBg = 'primary';
        grade = 'B (Good)';
      }

      let typeBadge = item.score_type === 'COLLABORATION'
        ? '<span class="badge bg-info text-white">COLLABORATION</span>'
        : '<span class="badge text-white" style="background-color: #6f42c1;">LIFETIME</span>';

      let html = `
        <div class="row g-3">
          <div class="col-md-7 border-end">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">
                ${escapeHtml(item.kol_name ? item.kol_name.charAt(0).toUpperCase() : 'K')}
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">${escapeHtml(item.kol_name || 'KOL #' + item.kol_id)}</h5>
                <span class="text-muted small">${escapeHtml(item.kol_email || '-')} &bull; ${escapeHtml(item.kol_phone || '-')}</span>
              </div>
            </div>

            <div class="card bg-light border-0 p-3 mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-xs text-uppercase text-muted fw-bold">Skor Performa:</span>
                ${typeBadge}
              </div>
              <div class="d-flex align-items-center gap-3">
                <h2 class="fw-bold mb-0 text-${badgeBg.replace(' text-dark', '')}">${scoreVal.toFixed(2)}</h2>
                <div>
                  <span class="badge bg-${badgeBg} px-2 py-1">${grade}</span>
                  <div class="text-muted small mt-1">Skala 0 - 100 poin evaluasi</div>
                </div>
              </div>
              <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-${badgeBg.replace(' text-dark', '')}" role="progressbar" style="width: ${Math.min(100, Math.max(0, scoreVal))}%;"></div>
              </div>
            </div>

            <div>
              <label class="form-label text-xs fw-bold text-uppercase text-muted">Kampanye Terkait:</label>
              <div class="p-2 bg-white border rounded small">
                <i class="fas fa-bullhorn text-primary me-2"></i>
                <strong>${escapeHtml(item.campaign_title || 'Tidak Terkait Kampanye Spesifik (Evaluasi Lifetime Global)')}</strong>
              </div>
            </div>
          </div>

          <div class="col-md-5">
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-info me-1"></i>Metadata Audit</h6>
            <ul class="list-group list-group-flush small">
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">ID Riwayat:</span>
                <span class="fw-bold">#${item.id}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">ID Talent (KOL):</span>
                <span class="fw-semibold">#${item.kol_id}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">ID Partisipasi Kampanye:</span>
                <span class="text-dark">${item.campaign_kol_id ? '#' + item.campaign_kol_id : '<span class="text-muted fst-italic">N/A</span>'}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Waktu Perhitungan:</span>
                <span class="fw-semibold text-dark">${item.calculated_at}</span>
              </li>
            </ul>
          </div>
        </div>
      `;
      $('#modalScoreSnapshotBody').html(html);
    },
    error: function() {
      $('#modalScoreSnapshotBody').html('<div class="alert alert-danger mb-0">Terjadi kesalahan koneksi server saat mengambil data.</div>');
    }
  });
}

function deleteScoreHistory(id) {
  Swal.fire({
    title: 'Hapus Riwayat Skor?',
    text: 'Data riwayat skor #' + id + ' akan dihapus permanen dari sistem.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '<?= base_url('score-histories/delete') ?>/' + id;
    }
  });
}

function bulkDeleteSelectedScoreHistories() {
  const selectedIds = [];
  $('.hist-row-check:checked').each(function() {
    selectedIds.push($(this).val());
  });

  if (selectedIds.length === 0) return;

  Swal.fire({
    title: 'Hapus ' + selectedIds.length + ' Riwayat Skor Terpilih?',
    text: 'Seluruh riwayat skor yang dipilih akan dihapus permanen.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus Semua!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= base_url('score-histories/bulk-delete') ?>',
        type: 'POST',
        data: {
          ids: selectedIds,
          '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Berhasil!', res.message, 'success');
            reloadScoreHistoriesTable();
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Error!', 'Terjadi kesalahan server saat menghapus data.', 'error');
        }
      });
    }
  });
}

function escapeHtml(text) {
  if (!text) return '';
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
<?= $this->endSection() ?>
