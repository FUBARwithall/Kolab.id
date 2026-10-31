<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-signature text-primary me-2"></i>Briefs &amp; Guidelines Konten</h3>
        <p class="text-muted small mb-0">Instruksi konten, storyboard, do's &amp; don'ts, dan proses approval brief sebelum produksi video dimulai.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('tasks?view=sow') ?>" class="text-decoration-none">Tasks SOW</a></li>
          <li class="breadcrumb-item active" aria-current="page">Briefs</li>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Briefs</span>
                <h3 class="fw-bold mb-0 text-dark mt-1" id="statTotal"><?= (int)($metrics['total'] ?? 0) ?></h3>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                <i class="fas fa-file-alt fa-lg"></i>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Pending Review</span>
                <h3 class="fw-bold mb-0 text-info mt-1" id="statPending"><?= (int)($metrics['pending'] ?? 0) ?></h3>
              </div>
              <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                <i class="fas fa-clock fa-lg"></i>
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
                <h3 class="fw-bold mb-0 text-success mt-1" id="statApproved"><?= (int)($metrics['approved'] ?? 0) ?></h3>
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
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Perlu Revisi</span>
                <h3 class="fw-bold mb-0 text-warning mt-1" id="statRevision"><?= (int)($metrics['revision'] ?? 0) ?></h3>
              </div>
              <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                <i class="fas fa-history fa-lg"></i>
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
          
          <!-- Filter Controls -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div style="min-width: 220px;">
              <select class="form-select form-select-sm rounded-2" id="filterCampaignBrief" onchange="reloadBriefTable()">
                <option value="">Semua Kampanye</option>
                <?php foreach ($allCampaigns as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 170px;">
              <select class="form-select form-select-sm rounded-2" id="filterStatusBrief" onchange="reloadBriefTable()">
                <option value="">Semua Status</option>
                <option value="PENDING">Pending Review / Draft</option>
                <option value="APPROVED">Disetujui (Approved)</option>
                <option value="REVISION_NEEDED">Revisi (Revision Needed)</option>
                <option value="REJECTED">Ditolak (Rejected)</option>
              </select>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-2" title="Reset Filter" onclick="resetFilters()">
              <i class="fas fa-undo"></i>
            </button>
          </div>

          <!-- Actions & Exports -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('tasks?view=sow') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3">
              <i class="fas fa-tasks me-1"></i> Mode Tasks SOW
            </a>
            <div class="btn-group">
              <a href="<?= base_url('briefs/export') ?>" class="btn btn-outline-success btn-sm rounded-2">
                <i class="fas fa-file-excel me-1"></i> Excel / CSV
              </a>
              <a href="<?= base_url('briefs/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2">
                <i class="fas fa-file-pdf me-1"></i> Print / PDF
              </a>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 d-none" id="btnBulkDelete" onclick="bulkDeleteSelected()">
              <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="bulkCount">0</span>)
            </button>
            <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="addBriefModal()">
              <i class="fas fa-plus me-1"></i> Buat Brief Baru
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableBriefs" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllBriefs" onchange="toggleSelectAll(this)">
                </th>
                <th>Judul Brief &amp; Target Task</th>
                <th>Kampanye</th>
                <th>Talent KOL &amp; Submitter</th>
                <th style="width: 140px;" class="text-center">Status</th>
                <th style="width: 140px;">Dibuat Tanggal</th>
                <th style="width: 170px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Form Buat / Edit Brief -->
<div class="modal fade" id="modalBriefForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formBrief" onsubmit="saveBrief(event)">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="briefFormTitle"><i class="fas fa-file-alt me-2"></i>Form Brief Konten</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="briefId" name="id">
          
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Target Tugas (Task / VT) <span class="text-danger">*</span></label>
              <select class="form-select" id="briefTaskId" name="task_id" required>
                <option value="">-- Pilih Tugas / VT Target --</option>
                <?php foreach ($availableTasks as $t): ?>
                  <option value="<?= $t['id'] ?>">#<?= $t['id'] ?> (VT #<?= esc($t['task_number']) ?>) - <?= esc($t['title']) ?> [<?= esc($t['kol_name']) ?>]</option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Pilih tugas video yang akan dibuatkan brief.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Judul Brief <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="briefTitle" name="title" required placeholder="Contoh: Brief Video Unboxing Metafora Parfume">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Petunjuk Konten &amp; Guidelines <span class="text-danger">*</span></label>
            <textarea class="form-control" id="briefContent" name="content" rows="4" required placeholder="Tuliskan storyline lengkap, hook 3 detik awal, call-to-action (CTA), angle kamera, dsb..."></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-success"><i class="fas fa-check-circle me-1"></i>Do's (Hal yang Harus Dilakukan)</label>
              <textarea class="form-control" id="briefDos" name="dos" rows="3" placeholder="- Tampilkan botol parfum di 3 detik pertama&#10;- Gunakan sound trending"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-danger"><i class="fas fa-times-circle me-1"></i>Don'ts (Hal yang Dilarang)</label>
              <textarea class="form-control" id="briefDonts" name="donts" rows="3" placeholder="- Jangan sebut merk kompetitor&#10;- Jangan klaim khasiat berlebihan"></textarea>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">URL Referensi / Moodboard</label>
              <input type="url" class="form-control" id="briefRefUrl" name="reference_url" placeholder="https://drive.google.com/... atau link contoh VT">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status Awal</label>
              <select class="form-select" id="briefStatus" name="status">
                <option value="DRAFT">DRAFT (Disimpan saja)</option>
                <option value="PENDING_REVIEW" selected>PENDING REVIEW (Ajukan ke Leader)</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4 shadow-sm" id="btnSaveBrief">Simpan &amp; Proses</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal View Detail Brief -->
<div class="modal fade" id="modalViewBrief" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewBriefTitle"><i class="fas fa-file-contract text-primary me-2"></i>Detail Brief</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewBriefBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light justify-content-between">
        <div id="viewBriefActions"></div>
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Review / Approval Brief (Leader Action) -->
<div class="modal fade" id="modalReviewBrief" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check me-2"></i>Review &amp; Keputusan Brief</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="revBriefId">
        <div class="alert alert-info py-2 small mb-3">
          <i class="fas fa-info-circle me-1"></i> Leader dapat langsung menyetujui brief untuk memajukan tugas ke tahap <strong>WAITING_DRAFT</strong>, atau meminta revisi dengan catatan perbaikan.
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan Review / Catatan Revisi</label>
          <textarea class="form-control" id="revNotes" rows="4" placeholder="Tuliskan catatan detail jika meminta revisi atau ucapan konfirmasi persetujuan..."></textarea>
        </div>
        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-warning rounded-2 px-3 fw-semibold" onclick="submitBriefDecision('revision')">
            <i class="fas fa-undo me-1"></i> Minta Revisi
          </button>
          <button type="button" class="btn btn-success rounded-2 px-4 fw-semibold" onclick="submitBriefDecision('approve')">
            <i class="fas fa-check me-1"></i> Setujui (Approve)
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtBriefs = null;

$(document).ready(function() {
    dtBriefs = $('#tableBriefs').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('briefs/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignBrief').val();
                d.approval_status = $('#filterStatusBrief').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'title' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'status', className: 'text-center' },
            { data: 'created' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari brief, tugas, kampanye, KOL...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ brief",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            updateBulkDeleteButton();
            $('.brief-row-check').on('change', updateBulkDeleteButton);
        }
    });

    // Check URL action
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'add') {
        addBriefModal();
    } else if (urlParams.get('action') === 'edit' && urlParams.get('id')) {
        editBrief(urlParams.get('id'));
    }
});

function reloadBriefTable() {
    dtBriefs.ajax.reload();
}

function resetFilters() {
    $('#filterCampaignBrief').val('');
    $('#filterStatusBrief').val('');
    reloadBriefTable();
}

function toggleSelectAll(master) {
    $('.brief-row-check').prop('checked', master.checked);
    updateBulkDeleteButton();
}

function updateBulkDeleteButton() {
    const selected = $('.brief-row-check:checked');
    const count = selected.length;
    $('#bulkCount').text(count);
    if (count > 0) {
        $('#btnBulkDelete').removeClass('d-none');
    } else {
        $('#btnBulkDelete').addClass('d-none');
        $('#checkAllBriefs').prop('checked', false);
    }
}

function addBriefModal() {
    $('#formBrief')[0].reset();
    $('#briefId').val('');
    $('#briefTaskId').val('');
    $('#briefStatus').val('PENDING_REVIEW');
    $('#briefFormTitle').html('<i class="fas fa-plus me-2"></i>Buat Brief Baru');
    $('#modalBriefForm').modal('show');
}

function editBrief(id) {
    $.get('<?= base_url('briefs/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const b = res.data;
            $('#briefId').val(b.id);
            $('#briefTaskId').val(b.task_id);
            $('#briefTitle').val(b.title);
            $('#briefContent').val(b.content || b.guidelines || '');
            $('#briefDos').val(b.dos || '');
            $('#briefDonts').val(b.donts || '');
            $('#briefRefUrl').val(b.reference_url || '');
            $('#briefStatus').val(b.status || 'PENDING_REVIEW');
            $('#briefFormTitle').html('<i class="fas fa-edit me-2"></i>Edit Brief #' + b.id);
            $('#modalBriefForm').modal('show');
        }
    }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mengambil data brief.' });
    });
}

function saveBrief(e) {
    e.preventDefault();
    const btn = $('#btnSaveBrief');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('briefs/save') ?>', $('#formBrief').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan & Proses');
        if (res.success) {
            $('#modalBriefForm').modal('hide');
            dtBriefs.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan & Proses');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function viewBrief(id) {
    $('#modalViewBrief').modal('show');
    $('#viewBriefBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
    $('#viewBriefActions').html('');

    $.get('<?= base_url('briefs/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const b = res.data;
            let histHtml = '';
            if (b.history && b.history.length > 0) {
                histHtml = b.history.map(h => `
                    <div class="border rounded-2 p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-dark"><i class="fas fa-user-check me-1"></i>${h.reviewer_name || 'Reviewer'}</strong>
                            <span class="badge ${h.status === 'APPROVED' ? 'bg-success' : 'bg-warning'}">${h.status}</span>
                        </div>
                        <div class="small text-muted mt-1">${h.full_note || h.notes || h.note || '-'}</div>
                        <div class="text-muted text-end" style="font-size: 10.5px;">${h.created_at}</div>
                    </div>
                `).join('');
            } else {
                histHtml = '<div class="text-muted small fst-italic">Belum ada riwayat approval.</div>';
            }

            let versHtml = '';
            if (b.versions && b.versions.length > 0) {
                versHtml = b.versions.map(v => `
                    <div class="border rounded-2 p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-primary"><i class="fas fa-code-branch me-1"></i>Versi ${v.version}</strong>
                            <small class="text-muted">${v.snapshot_at || v.created_at}</small>
                        </div>
                        <div class="small text-secondary mt-1" style="max-height: 80px; overflow-y: auto;">${v.content}</div>
                        ${v.content_diff ? `<div class="small text-danger mt-1"><strong>Alasan revisi:</strong> ${v.content_diff}</div>` : ''}
                    </div>
                `).join('');
            } else {
                versHtml = '<div class="text-muted small fst-italic">Belum ada revisi sebelumnya (Versi 1).</div>';
            }

            $('#viewBriefBody').html(`
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">${b.title}</h5>
                        <div class="text-muted small mt-1">Kampanye: <strong>${b.campaign_title}</strong> &bull; KOL: <strong>${b.kol_name}</strong> &bull; Task: <strong>${b.task_title || ('VT #' + b.task_number)}</strong></div>
                    </div>
                    <span class="badge ${b.status === 'APPROVED' ? 'bg-success' : (b.status === 'REVISION' ? 'bg-warning' : 'bg-primary')} px-3 py-2 fs-6">${b.status || b.approval_status}</span>
                </div>
                <hr>
                <div class="card bg-light border-0 mb-3 p-3">
                    <h6 class="fw-bold mb-1 text-dark"><i class="fas fa-align-left me-1"></i>Petunjuk &amp; Guidelines Konten</h6>
                    <div class="text-secondary" style="white-space: pre-line;">${b.content || b.guidelines}</div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded-2 p-2 border-success-subtle bg-success-subtle text-success">
                            <strong class="d-block mb-1"><i class="fas fa-check-circle me-1"></i>Do's:</strong>
                            <div class="small" style="white-space: pre-line;">${b.dos || '-'}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-2 p-2 border-danger-subtle bg-danger-subtle text-danger">
                            <strong class="d-block mb-1"><i class="fas fa-times-circle me-1"></i>Don'ts:</strong>
                            <div class="small" style="white-space: pre-line;">${b.donts || '-'}</div>
                        </div>
                    </div>
                </div>
                ${b.reference_url ? `<div class="mb-3 p-2 bg-light rounded-2 border"><strong>Moodboard / Referensi:</strong> <a href="${b.reference_url}" target="_blank" class="text-decoration-underline">${b.reference_url}</a></div>` : ''}
                
                <ul class="nav nav-tabs mt-4" id="briefTab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" id="hist-tab" data-bs-toggle="tab" data-bs-target="#tabHist" type="button" role="tab">Riwayat Approval (${b.history ? b.history.length : 0})</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="vers-tab" data-bs-toggle="tab" data-bs-target="#tabVers" type="button" role="tab">Riwayat Versi / Revisi (${b.versions ? b.versions.length : 0})</button>
                  </li>
                </ul>
                <div class="tab-content pt-3" id="briefTabContent">
                  <div class="tab-pane fade show active" id="tabHist" role="tabpanel">${histHtml}</div>
                  <div class="tab-pane fade" id="tabVers" role="tabpanel">${versHtml}</div>
                </div>
            `);

            $('#viewBriefActions').html(`
                <a href="<?= base_url('briefs/view/') ?>${b.id}" class="btn btn-outline-info rounded-2 btn-sm"><i class="fas fa-external-link-alt me-1"></i>Buka Halaman Lengkap</a>
                <a href="<?= base_url('briefs/single-pdf/') ?>${b.id}" target="_blank" class="btn btn-outline-secondary rounded-2 btn-sm"><i class="fas fa-print me-1"></i>Cetak Brief</a>
            `);
        }
    });
}

function reviewBriefModal(id) {
    $('#revBriefId').val(id);
    $('#revNotes').val('');
    $('#modalReviewBrief').modal('show');
}

function submitBriefDecision(action) {
    const id = $('#revBriefId').val();
    const notes = $('#revNotes').val();
    const endpoint = action === 'approve' ? '<?= base_url('briefs/approve/') ?>' + id : '<?= base_url('briefs/revision/') ?>' + id;

    if (action === 'revision' && !notes.trim()) {
        Swal.fire({ icon: 'warning', title: 'Catatan Diperlukan', text: 'Harap masukkan catatan revisi untuk KOL/Staff.' });
        return;
    }

    $.post(endpoint, { notes: notes, note: notes }, function(res) {
        if (res.success) {
            $('#modalReviewBrief').modal('hide');
            dtBriefs.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function deleteBrief(id) {
    Swal.fire({
        title: 'Hapus Brief ini?',
        text: 'Data brief yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('briefs/delete/') ?>' + id, function(res) {
                if (res.success) {
                    dtBriefs.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Dihapus', text: res.message, timer: 1500, showConfirmButton: false });
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus brief.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err });
            });
        }
    });
}

function bulkDeleteSelected() {
    const selected = $('.brief-row-check:checked');
    const ids = [];
    selected.each(function() { ids.push($(this).val()); });

    if (ids.length === 0) return;

    Swal.fire({
        title: `Hapus ${ids.length} Brief terpilih?`,
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('briefs/bulk-delete') ?>', { ids: ids }, function(res) {
                if (res.success) {
                    dtBriefs.ajax.reload(null, false);
                    $('#checkAllBriefs').prop('checked', false);
                    updateBulkDeleteButton();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus bulk brief.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err });
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
