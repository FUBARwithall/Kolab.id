<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i>Task Status Histories</h3>
        <p class="text-muted small mb-0">Audit trail riwayat pergantian status alur kerja tugas (Brief, Draft, Posting & Selesai).</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('tasks') ?>" class="text-decoration-none">Tasks</a></li>
          <li class="breadcrumb-item active" aria-current="page">Status Histories</li>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Riwayat Status</span>
                <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format((int)($metrics['total_logs'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                <i class="fas fa-history fa-lg"></i>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Approved / Selesai</span>
                <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format((int)($metrics['total_approved'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                <i class="fas fa-check-double fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-warning">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Status Revisi</span>
                <h3 class="fw-bold mb-0 text-warning mt-1"><?= number_format((int)($metrics['total_revision'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                <i class="fas fa-undo fa-lg"></i>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Proses / Submitted</span>
                <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format((int)($metrics['total_submitted'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                <i class="fas fa-spinner fa-lg"></i>
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
              <select class="form-select form-select-sm rounded-2" id="filterStatus" onchange="reloadTaskStatusTable()">
                <option value="">Semua Status</option>
                <?php foreach ($allStatuses as $st): ?>
                  <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 200px;">
              <select class="form-select form-select-sm rounded-2" id="filterCampaign" onchange="reloadTaskStatusTable()">
                <option value="">Semua Kampanye</option>
                <?php foreach ($allCampaigns as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($filters['campaign_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 200px;">
              <select class="form-select form-select-sm rounded-2" id="filterTask" onchange="reloadTaskStatusTable()">
                <option value="">Semua Task</option>
                <?php foreach ($allTasks as $t): ?>
                  <option value="<?= $t['id'] ?>" <?= ($filters['task_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>#<?= $t['id'] ?> - <?= esc($t['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 170px;">
              <select class="form-select form-select-sm rounded-2" id="filterChangedBy" onchange="reloadTaskStatusTable()">
                <option value="">Semua PIC / Aktor</option>
                <?php foreach ($allUsers as $u): ?>
                  <option value="<?= $u['id'] ?>" <?= ($filters['changed_by'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-2" title="Reset Filter" onclick="resetTaskStatusFilters()">
              <i class="fas fa-undo"></i>
            </button>
          </div>

          <!-- Actions & Exports -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('tasks') ?>" class="btn btn-outline-primary btn-sm rounded-2 px-3">
              <i class="fas fa-tasks me-1"></i> Data Tasks
            </a>
            <div class="btn-group">
              <a href="<?= base_url('task-status-histories/export') ?>" class="btn btn-outline-success btn-sm rounded-2">
                <i class="fas fa-file-excel me-1"></i> Excel / CSV
              </a>
              <a href="<?= base_url('task-status-histories/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2">
                <i class="fas fa-file-pdf me-1"></i> Print / PDF
              </a>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 d-none" id="btnBulkDeleteTaskStatus" onclick="bulkDeleteSelectedTaskStatus()">
              <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="bulkCountTaskStatus">0</span>)
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="taskStatusHistoriesTable" style="width: 100%;">
            <thead class="table-light text-uppercase text-secondary" style="font-size: 11px; letter-spacing: 0.5px;">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllTaskStatus">
                </th>
                <th>Task Terkait</th>
                <th>Perubahan Status</th>
                <th>Catatan Transisi</th>
                <th>Kampanye &amp; KOL</th>
                <th>Diubah Oleh</th>
                <th>Waktu Transisi</th>
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

<!-- Modal Snapshot Riwayat -->
<div class="modal fade" id="modalHistorySnapshot" tabindex="-1" aria-labelledby="modalHistorySnapshotLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold" id="modalHistorySnapshotLabel">
          <i class="fas fa-clipboard-check text-primary me-2"></i>Detail Transisi Status Task
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="modalHistorySnapshotBody">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted small mt-2">Memuat snapshot riwayat...</p>
        </div>
      </div>
      <div class="modal-footer bg-light border-top py-2">
        <button type="button" class="btn btn-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Tutup</button>
        <a href="#" id="modalDetailLink" class="btn btn-primary btn-sm rounded-2 px-3">Buka Halaman Lengkap</a>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let taskStatusTable = null;

$(document).ready(function() {
  taskStatusTable = $('#taskStatusHistoriesTable').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    order: [[6, 'desc']],
    ajax: {
      url: '<?= base_url('task-status-histories/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.status      = $('#filterStatus').val();
        d.campaign_id = $('#filterCampaign').val();
        d.task_id     = $('#filterTask').val();
        d.changed_by  = $('#filterChangedBy').val();
      },
      error: function(xhr, err, code) {
        console.error('DataTables load error:', err, code, xhr.responseText);
      }
    },
    columns: [
      { data: 'checkbox', orderable: false, className: 'text-center' },
      { data: 'task', orderable: true },
      { data: 'status', orderable: true },
      { data: 'note', orderable: true },
      { data: 'campaign', orderable: true },
      { data: 'changed_by', orderable: true },
      { data: 'created_at', orderable: true },
      { data: 'actions', orderable: false, className: 'text-center' }
    ],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari riwayat, task, PIC, catatan...",
      lengthMenu: "_MENU_ data per halaman",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ entri riwayat",
      infoEmpty: "Tidak ada data riwayat yang cocok",
      zeroRecords: "Data riwayat status tidak ditemukan",
      processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
    },
    drawCallback: function() {
      updateBulkButtonState();
      $('#checkAllTaskStatus').prop('checked', false);
    }
  });

  // Check all handling
  $('#checkAllTaskStatus').on('change', function() {
    const isChecked = $(this).is(':checked');
    $('.hist-row-check').prop('checked', isChecked);
    updateBulkButtonState();
  });

  $(document).on('change', '.hist-row-check', function() {
    updateBulkButtonState();
    const total = $('.hist-row-check').length;
    const checked = $('.hist-row-check:checked').length;
    $('#checkAllTaskStatus').prop('checked', total > 0 && total === checked);
  });
});

function reloadTaskStatusTable() {
  if (taskStatusTable) {
    taskStatusTable.ajax.reload(null, false);
  }
}

function resetTaskStatusFilters() {
  $('#filterStatus').val('');
  $('#filterCampaign').val('');
  $('#filterTask').val('');
  $('#filterChangedBy').val('');
  reloadTaskStatusTable();
}

function updateBulkButtonState() {
  const checkedCount = $('.hist-row-check:checked').length;
  $('#bulkCountTaskStatus').text(checkedCount);
  if (checkedCount > 0) {
    $('#btnBulkDeleteTaskStatus').removeClass('d-none');
  } else {
    $('#btnBulkDeleteTaskStatus').addClass('d-none');
  }
}

function viewHistorySnapshot(id) {
  const modal = new bootstrap.Modal(document.getElementById('modalHistorySnapshot'));
  $('#modalHistorySnapshotBody').html(`
    <div class="text-center py-4">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted small mt-2">Memuat snapshot riwayat...</p>
    </div>
  `);
  $('#modalDetailLink').attr('href', '<?= base_url('task-status-histories/view') ?>/' + id);
  modal.show();

  $.ajax({
    url: '<?= base_url('task-status-histories/get-one') ?>/' + id,
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (!res.success || !res.data) {
        $('#modalHistorySnapshotBody').html('<div class="alert alert-danger mb-0">Gagal memuat data riwayat.</div>');
        return;
      }
      const item = res.data;
      const fromSt = item.from_status ? item.from_status.toUpperCase() : '-';
      const toSt = (item.to_status || item.status || 'DRAFT').toUpperCase();

      let html = `
        <div class="row g-3">
          <div class="col-md-7 border-end">
            <h6 class="fw-bold text-dark mb-1">${escapeHtml(item.task_title || 'Task #' + item.task_id)}</h6>
            <div class="text-muted small mb-3">
              Task No: <span class="badge bg-light text-dark border">#${item.task_number || 1}</span> &bull; 
              Kampanye: <strong class="text-primary">${escapeHtml(item.campaign_title || '-')}</strong> &bull;
              KOL: <strong>${escapeHtml(item.kol_name || '-')}</strong>
            </div>

            <div class="card bg-light border-0 p-3 mb-3">
              <div class="text-xs text-uppercase text-muted fw-bold mb-1">Perubahan Status:</div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary font-monospace px-2 py-1">${fromSt}</span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-primary font-monospace px-2 py-1">${toSt}</span>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label text-xs fw-bold text-uppercase text-muted">Catatan Transisi:</label>
              <div class="p-3 bg-white border rounded small">
                ${escapeHtml(item.resolved_note || item.note || item.notes || 'Tidak ada catatan.')}
              </div>
            </div>
          </div>

          <div class="col-md-5">
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-info me-1"></i>Informasi Audit</h6>
            <ul class="list-group list-group-flush small">
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">ID Riwayat:</span>
                <span class="fw-bold">#${item.id}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Aktor / PIC:</span>
                <span class="fw-semibold text-dark">${escapeHtml(item.changed_by_name || 'System')}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Email:</span>
                <span class="text-muted">${escapeHtml(item.changed_by_email || '-')}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Waktu Catat:</span>
                <span class="fw-semibold">${item.created_at}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Status Task Sekarang:</span>
                <span class="badge bg-success">${escapeHtml(item.task_current_status || '-')}</span>
              </li>
            </ul>
          </div>
        </div>
      `;
      $('#modalHistorySnapshotBody').html(html);
    },
    error: function() {
      $('#modalHistorySnapshotBody').html('<div class="alert alert-danger mb-0">Terjadi kesalahan koneksi server saat mengambil detail.</div>');
    }
  });
}

function deleteHistory(id) {
  Swal.fire({
    title: 'Hapus Riwayat?',
    text: 'Data riwayat perubahan status #' + id + ' akan dihapus permanen dari sistem audit.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '<?= base_url('task-status-histories/delete') ?>/' + id;
    }
  });
}

function bulkDeleteSelectedTaskStatus() {
  const selectedIds = [];
  $('.hist-row-check:checked').each(function() {
    selectedIds.push($(this).val());
  });

  if (selectedIds.length === 0) return;

  Swal.fire({
    title: 'Hapus ' + selectedIds.length + ' Riwayat Terpilih?',
    text: 'Seluruh riwayat status yang dipilih akan dihapus permanen.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus Semua!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= base_url('task-status-histories/bulk-delete') ?>',
        type: 'POST',
        data: {
          ids: selectedIds,
          '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Berhasil!', res.message, 'success');
            reloadTaskStatusTable();
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
