<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-info me-2"></i>Briefs &amp; Guidelines</h3>
        <p class="text-muted small mb-0">Instruksi konten, storyboard, do's &amp; don'ts, dan proses approval brief sebelum produksi video dimulai.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Briefs</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="filterCampaignBrief" style="min-width: 220px;" onchange="reloadBriefTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterStatusBrief" style="min-width: 170px;" onchange="reloadBriefTable()">
              <option value="">Semua Status</option>
              <option value="PENDING">Pending Review</option>
              <option value="APPROVED">Approved</option>
              <option value="REVISION_NEEDED">Revision Needed</option>
            </select>
          </div>

          <div>
            <button type="button" class="btn btn-primary rounded-2 btn-sm px-3 shadow-sm" onclick="addBriefModal()">
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
                <th style="width: 50px;" class="text-center">#</th>
                <th>Judul Brief &amp; Target Task</th>
                <th>Kampanye</th>
                <th>Talent KOL &amp; Submitter</th>
                <th style="width: 150px;">Status Approval</th>
                <th style="width: 140px;">Dibuat Tanggal</th>
                <th style="width: 130px;" class="text-center">Aksi</th>
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
              <input type="number" class="form-control" id="briefTaskId" name="task_id" required placeholder="ID Task (e.g. 1)">
              <small class="text-muted">ID Tugas VT dari modul Tasks Engine.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Judul Brief <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="briefTitle" name="title" required placeholder="Contoh: Brief Video Unboxing Metafora Parfume">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Petunjuk Konten &amp; Guidelines <span class="text-danger">*</span></label>
            <textarea class="form-control" id="briefGuidelines" name="guidelines" rows="4" required placeholder="Tuliskan storyline, hook 3 detik awal, call-to-action (CTA), angle kamera, dsb..."></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-success"><i class="fas fa-check-circle me-1"></i>Do's (Hal yang Harus Dilakukan)</label>
              <textarea class="form-control" id="briefDos" name="dos" rows="3" placeholder="- Tampilkan botol parfum di 3 detik pertama&#10;- Gunakan sound trending"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-danger"><i class="fas fa-times-circle me-1"></i>Don'ts (Hal yang Dilarang)</label>
              <textarea class="form-control" id="briefDonts" name="donts" rows="3" placeholder="- Jangan sebut merk kompetitor&#10;- Jangan klaim berlebihan"></textarea>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">URL Referensi / Moodboard</label>
            <input type="url" class="form-control" id="briefRefUrl" name="reference_url" placeholder="https://drive.google.com/... atau link contoh VT">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveBrief">Simpan &amp; Ajukan</button>
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
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Review / Approval Brief -->
<div class="modal fade" id="modalReviewBrief" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check me-2"></i>Review &amp; Keputusan Brief</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="revBriefId">
        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan Review / Instruksi Revisi</label>
          <textarea class="form-control" id="revNotes" rows="4" placeholder="Tuliskan catatan detail jika meminta revisi atau ucapan konfirmasi jika menyetujui..."></textarea>
        </div>
        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-danger rounded-2 px-3" onclick="submitBriefDecision('revision')">
            <i class="fas fa-undo me-1"></i> Minta Revisi
          </button>
          <button type="button" class="btn btn-success rounded-2 px-4" onclick="submitBriefDecision('approve')">
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
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'title' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'status' },
            { data: 'created' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari brief, tugas, kampanye...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ brief",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadBriefTable() {
    dtBriefs.ajax.reload();
}

function addBriefModal() {
    $('#formBrief')[0].reset();
    $('#briefId').val('');
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
            $('#briefGuidelines').val(b.guidelines);
            $('#briefDos').val(b.dos || '');
            $('#briefDonts').val(b.donts || '');
            $('#briefRefUrl').val(b.reference_url || '');
            $('#briefFormTitle').html('<i class="fas fa-edit me-2"></i>Edit Brief');
            $('#modalBriefForm').modal('show');
        }
    });
}

function saveBrief(e) {
    e.preventDefault();
    const btn = $('#btnSaveBrief');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('briefs/save') ?>', $('#formBrief').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan & Ajukan');
        if (res.success) {
            $('#modalBriefForm').modal('hide');
            dtBriefs.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan & Ajukan');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function viewBrief(id) {
    $('#modalViewBrief').modal('show');
    $('#viewBriefBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

    $.get('<?= base_url('briefs/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const b = res.data;
            let histHtml = '';
            if (b.history && b.history.length > 0) {
                histHtml = b.history.map(h => `
                    <div class="border rounded-2 p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between">
                            <strong>${h.reviewer_name || 'Reviewer'}</strong>
                            <span class="badge ${h.status === 'APPROVED' ? 'bg-success' : 'bg-danger'}">${h.status}</span>
                        </div>
                        <div class="small text-muted mt-1">${h.notes || '-'}</div>
                        <div class="text-muted text-end" style="font-size: 10.5px;">${h.created_at}</div>
                    </div>
                `).join('');
            } else {
                histHtml = '<div class="text-muted small fst-italic">Belum ada riwayat approval</div>';
            }

            $('#viewBriefBody').html(`
                <h5 class="fw-bold text-dark">${b.title}</h5>
                <div class="text-muted small mb-3">Kampanye: <strong>${b.campaign_title}</strong> &bull; KOL: <strong>${b.kol_name}</strong></div>
                <div class="card bg-light border-0 mb-3 p-3">
                    <h6 class="fw-bold mb-1">Petunjuk &amp; Guidelines</h6>
                    <div class="text-secondary" style="white-space: pre-line;">${b.guidelines}</div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded-2 p-2 border-success-subtle bg-success-subtle text-success">
                            <strong class="d-block mb-1"><i class="fas fa-check me-1"></i>Do's:</strong>
                            <div class="small" style="white-space: pre-line;">${b.dos || '-'}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-2 p-2 border-danger-subtle bg-danger-subtle text-danger">
                            <strong class="d-block mb-1"><i class="fas fa-times me-1"></i>Don'ts:</strong>
                            <div class="small" style="white-space: pre-line;">${b.donts || '-'}</div>
                        </div>
                    </div>
                </div>
                ${b.reference_url ? `<div class="mb-3"><strong>Moodboard / Referensi:</strong> <a href="${b.reference_url}" target="_blank">${b.reference_url}</a></div>` : ''}
                <hr>
                <h6 class="fw-bold mb-2"><i class="fas fa-history me-1"></i>Riwayat Review &amp; Keputusan</h6>
                ${histHtml}
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

    $.post(endpoint, { notes: notes }, function(res) {
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
</script>
<?= $this->endSection() ?>
