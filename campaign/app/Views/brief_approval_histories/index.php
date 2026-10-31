<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-clipboard-check text-primary me-2"></i>Brief Approval Histories</h3>
        <p class="text-muted small mb-0">Arsip riwayat review &amp; persetujuan brief oleh Leader (Approved, Revision, Rejected).</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('briefs') ?>" class="text-decoration-none">Briefs</a></li>
          <li class="breadcrumb-item active" aria-current="page">Approval Histories</li>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Riwayat Review</span>
                <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format((int)($metrics['total_logs'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                <i class="fas fa-list-check fa-lg"></i>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Disetujui (Approved)</span>
                <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format((int)($metrics['total_approved'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                <i class="fas fa-check-circle fa-lg"></i>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Diminta Revisi</span>
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
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-danger">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Ditolak (Rejected)</span>
                <h3 class="fw-bold mb-0 text-danger mt-1"><?= number_format((int)($metrics['total_rejected'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                <i class="fas fa-times-circle fa-lg"></i>
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
              <select class="form-select form-select-sm rounded-2" id="filterStatus" onchange="reloadApprovalTable()">
                <option value="">Semua Status</option>
                <option value="APPROVED" <?= ($filters['status'] ?? '') === 'APPROVED' ? 'selected' : '' ?>>APPROVED</option>
                <option value="REVISION" <?= in_array(($filters['status'] ?? ''), ['REVISION', 'REVISION_NEEDED']) ? 'selected' : '' ?>>REVISION</option>
                <option value="REJECTED" <?= ($filters['status'] ?? '') === 'REJECTED' ? 'selected' : '' ?>>REJECTED</option>
              </select>
            </div>
            <div style="min-width: 200px;">
              <select class="form-select form-select-sm rounded-2" id="filterCampaign" onchange="reloadApprovalTable()">
                <option value="">Semua Kampanye</option>
                <?php foreach ($allCampaigns as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($filters['campaign_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 200px;">
              <select class="form-select form-select-sm rounded-2" id="filterBrief" onchange="reloadApprovalTable()">
                <option value="">Semua Brief</option>
                <?php foreach ($allBriefs as $b): ?>
                  <option value="<?= $b['id'] ?>" <?= ($filters['brief_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>#<?= $b['id'] ?> - <?= esc($b['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 170px;">
              <select class="form-select form-select-sm rounded-2" id="filterReviewer" onchange="reloadApprovalTable()">
                <option value="">Semua Reviewer</option>
                <?php foreach ($allUsers as $u): ?>
                  <option value="<?= $u['id'] ?>" <?= ($filters['acted_by'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-2" title="Reset Filter" onclick="resetApprovalFilters()">
              <i class="fas fa-undo"></i>
            </button>
          </div>

          <!-- Actions & Exports -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('briefs') ?>" class="btn btn-outline-primary btn-sm rounded-2 px-3">
              <i class="fas fa-file-alt me-1"></i> Data Briefs
            </a>
            <div class="btn-group">
              <a href="<?= base_url('brief-approval-histories/export') ?>" class="btn btn-outline-success btn-sm rounded-2">
                <i class="fas fa-file-excel me-1"></i> Excel / CSV
              </a>
              <a href="<?= base_url('brief-approval-histories/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2">
                <i class="fas fa-file-pdf me-1"></i> Print / PDF
              </a>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 d-none" id="btnBulkDeleteApproval" onclick="bulkDeleteSelectedApproval()">
              <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="bulkCountApproval">0</span>)
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableBriefApprovalHistories" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllApproval" onchange="toggleSelectAllApproval(this)">
                </th>
                <th>Target Brief &amp; Task</th>
                <th style="width: 110px;" class="text-center">Status</th>
                <th>Catatan Reviewer (Note)</th>
                <th>Kampanye &amp; KOL</th>
                <th style="width: 150px;">Reviewer / Acted By</th>
                <th style="width: 140px;">Waktu Review</th>
                <th style="width: 140px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal View Detail Brief Approval History Snapshot -->
<div class="modal fade" id="modalViewApprovalHist" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewApprovalHistTitle"><i class="fas fa-clipboard-check text-primary me-2"></i>Detail Riwayat Review Brief</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewApprovalHistBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light justify-content-between">
        <div id="viewApprovalHistActions"></div>
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtApproval = null;

$(document).ready(function() {
    dtApproval = $('#tableBriefApprovalHistories').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('brief-approval-histories/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.status      = $('#filterStatus').val();
                d.campaign_id = $('#filterCampaign').val();
                d.brief_id    = $('#filterBrief').val();
                d.acted_by    = $('#filterReviewer').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'brief' },
            { data: 'status', className: 'text-center' },
            { data: 'note' },
            { data: 'campaign' },
            { data: 'reviewer' },
            { data: 'created_at' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ],
        order: [[6, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari brief, reviewer, catatan...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ riwayat",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            updateBulkDeleteButtonApproval();
            $('.hist-row-check').on('change', updateBulkDeleteButtonApproval);
        }
    });
});

function reloadApprovalTable() {
    dtApproval.ajax.reload();
}

function resetApprovalFilters() {
    $('#filterStatus').val('');
    $('#filterCampaign').val('');
    $('#filterBrief').val('');
    $('#filterReviewer').val('');
    reloadApprovalTable();
}

function toggleSelectAllApproval(master) {
    $('.hist-row-check').prop('checked', master.checked);
    updateBulkDeleteButtonApproval();
}

function updateBulkDeleteButtonApproval() {
    const selected = $('.hist-row-check:checked');
    const count = selected.length;
    $('#bulkCountApproval').text(count);
    if (count > 0) {
        $('#btnBulkDeleteApproval').removeClass('d-none');
    } else {
        $('#btnBulkDeleteApproval').addClass('d-none');
        $('#checkAllApproval').prop('checked', false);
    }
}

function viewHistorySnapshot(id) {
    $('#modalViewApprovalHist').modal('show');
    $('#viewApprovalHistBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
    $('#viewApprovalHistActions').html('');

    $.get('<?= base_url('brief-approval-histories/get-one/') ?>/' + id, function(res) {
        if (res.success) {
            const h = res.data;
            const statusUpper = (h.status || '').toUpperCase();
            let badgeClass = 'bg-secondary';
            if (statusUpper === 'APPROVED') badgeClass = 'bg-success';
            else if (statusUpper === 'REVISION' || statusUpper === 'REVISION_NEEDED') badgeClass = 'bg-warning text-dark';
            else if (statusUpper === 'REJECTED') badgeClass = 'bg-danger';

            $('#viewApprovalHistBody').html(`
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">${h.briefs_title || ('Brief #' + h.brief_id)}</h5>
                        <div class="text-muted small">Target Brief ID: <strong>#${h.brief_id}</strong> &bull; Task: <strong>${h.task_title || ('VT #' + (h.task_number || '1'))}</strong> &bull; Kampanye: <strong>${h.campaign_title || '-'}</strong></div>
                    </div>
                    <span class="badge ${badgeClass} px-3 py-2 fs-6">${statusUpper}</span>
                </div>

                <div class="alert alert-light border mb-3">
                    <strong class="d-block mb-1 text-dark"><i class="fas fa-comment-dots me-1 text-primary"></i>Catatan Reviewer:</strong>
                    <div class="text-secondary small" style="white-space: pre-line;">${h.resolved_note || '<em class="text-muted">Tidak ada catatan review yang dilampirkan.</em>'}</div>
                </div>

                <div class="card bg-light border-0 p-3 mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-file-alt me-1 text-primary"></i>Pedoman Konten (Guidelines):</h6>
                    <div class="text-secondary small" style="white-space: pre-line; line-height: 1.6; max-height: 180px; overflow-y: auto;">
                        ${h.brief_guidelines || h.brief_content || '<em class="text-muted">Tidak ada panduan tertulis</em>'}
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Direview Oleh (Leader/Reviewer)</small>
                        <strong class="text-dark"><i class="fas fa-user-check me-1 text-info"></i>${h.reviewer_name || 'Reviewer'}</strong>
                        ${h.reviewer_email ? `<br><small class="text-muted">${h.reviewer_email}</small>` : ''}
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted d-block">Waktu Review</small>
                        <strong class="text-dark"><i class="far fa-clock me-1 text-secondary"></i>${h.created_at} WIB</strong>
                    </div>
                </div>
            `);

            $('#viewApprovalHistActions').html(`
                <a href="<?= base_url('brief-approval-histories/view/') ?>/${h.id}" class="btn btn-outline-primary btn-sm rounded-2"><i class="fas fa-external-link-alt me-1"></i>Halaman Penuh</a>
                <a href="<?= base_url('brief-approval-histories/single-pdf/') ?>/${h.id}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2"><i class="fas fa-print me-1"></i>Cetak PDF</a>
            `);
        }
    }).fail(function() {
        $('#viewApprovalHistBody').html('<div class="alert alert-danger mb-0">Gagal memuat detail riwayat approval.</div>');
    });
}

function deleteApprovalHistory(id) {
    Swal.fire({
        title: 'Hapus Riwayat ini?',
        text: 'Catatan log approval brief ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('brief-approval-histories/delete/') ?>/' + id, function(res) {
                if (res.success) {
                    dtApproval.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Dihapus', text: res.message, timer: 1500, showConfirmButton: false });
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus riwayat.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err });
            });
        }
    });
}

function bulkDeleteSelectedApproval() {
    const selected = $('.hist-row-check:checked');
    const ids = [];
    selected.each(function() { ids.push($(this).val()); });

    if (ids.length === 0) return;

    Swal.fire({
        title: `Hapus ${ids.length} riwayat approval terpilih?`,
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('brief-approval-histories/bulk-delete') ?>', { ids: ids }, function(res) {
                if (res.success) {
                    dtApproval.ajax.reload(null, false);
                    $('#checkAllApproval').prop('checked', false);
                    updateBulkDeleteButtonApproval();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus bulk riwayat.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err });
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
