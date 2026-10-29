<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-video text-warning me-2"></i>Review Draft Video</h3>
        <p class="text-muted small mb-0">Platform review video kreasi KOL dengan feedback spesifik berbasis timestamp detik/menit.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Review Draft</li>
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
            <select class="form-select form-select-sm rounded-2" id="filterCampaignDraft" style="min-width: 220px;" onchange="reloadDraftTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterStatusDraft" style="min-width: 170px;" onchange="reloadDraftTable()">
              <option value="">Semua Status Review</option>
              <option value="PENDING">Pending Review</option>
              <option value="APPROVED">Approved</option>
              <option value="REVISION_NEEDED">Revisi Diperlukan</option>
            </select>
          </div>

          <div>
            <button type="button" class="btn btn-warning text-dark rounded-2 btn-sm px-3 shadow-sm fw-semibold" onclick="uploadDraftModal()">
              <i class="fas fa-upload me-1"></i> Unggah Draft Video
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableDrafts" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Target Tugas &amp; Video Preview</th>
                <th>Kampanye</th>
                <th>Talent KOL</th>
                <th style="width: 90px;" class="text-center">Versi</th>
                <th style="width: 150px;">Status</th>
                <th style="width: 140px;">Diunggah</th>
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

<!-- Modal Unggah Draft Video -->
<div class="modal fade" id="modalUploadDraft" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formUploadDraft" onsubmit="submitDraftVideo(event)">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title fw-bold"><i class="fas fa-video me-2"></i>Unggah Draft Video KOL</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Target Tugas (Task ID) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="draftTaskId" name="task_id" value="<?= esc($taskId ?? '') ?>" required placeholder="Contoh: 1">
            <small class="text-muted">Masukkan ID Task VT yang ingin diunggah videonya.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Link / URL Video Draft <span class="text-danger">*</span></label>
            <input type="url" class="form-control" id="draftVideoUrl" name="video_url" required placeholder="https://drive.google.com/... atau link video cloud">
            <small class="text-muted">Pastikan akses share dibuka untuk publik / tim KOLAB.ID.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan dari KOL / Kreator</label>
            <textarea class="form-control" id="draftNotes" name="notes" rows="3" placeholder="Contoh: Sudah disesuaikan dengan brief, bagian CTA ditaruh di detik 00:45..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning text-dark rounded-2 px-4 fw-semibold" id="btnSubmitDraft">Kirim Draft</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detail & Timestamp Notes Draft -->
<div class="modal fade" id="modalViewDraft" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewDraftTitle"><i class="fas fa-video text-warning me-2"></i>Detail Draft Video &amp; Feedback</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewDraftBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Review Draft (Approve / Revision) -->
<div class="modal fade" id="modalReviewDraft" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check me-2"></i>Review Draft Video</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="revDraftId">
        
        <div class="alert alert-info py-2 px-3 small mb-3">
          <i class="fas fa-info-circle me-1"></i> Berikan umpan balik yang terukur. Jika ada detik tertentu yang perlu diganti, gunakan kolom Timestamp Feedback.
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan Evaluasi Keseluruhan</label>
          <textarea class="form-control" id="draftRevNotes" rows="3" placeholder="Evaluasi umum tentang tone, lighting, pembawaan KOL, kesesuaian produk..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-danger"><i class="fas fa-stopwatch me-1"></i>Timestamp Feedback Spesifik (Opsional)</label>
          <textarea class="form-control font-monospace" id="draftTimestampNotes" rows="3" placeholder="00:05 - Kurang jelas bagian label produk&#10;00:22 - Audio agak kresek saat bicara harga&#10;00:40 - Jangan lupa tambahkan tagar #MetaforaParfume"></textarea>
        </div>

        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-danger rounded-2 px-3" onclick="submitDraftDecision('revision')">
            <i class="fas fa-undo me-1"></i> Minta Revisi Draft
          </button>
          <button type="button" class="btn btn-success rounded-2 px-4" onclick="submitDraftDecision('approve')">
            <i class="fas fa-check me-1"></i> Setujui Video (Approve)
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtDrafts = null;

$(document).ready(function() {
    dtDrafts = $('#tableDrafts').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('drafts/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignDraft').val();
                d.status = $('#filterStatusDraft').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'task' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'version', className: 'text-center' },
            { data: 'status' },
            { data: 'submitted' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari draft video, task, kampanye...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ draft",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadDraftTable() {
    dtDrafts.ajax.reload();
}

function uploadDraftModal() {
    $('#formUploadDraft')[0].reset();
    $('#modalUploadDraft').modal('show');
}

function submitDraftVideo(e) {
    e.preventDefault();
    const btn = $('#btnSubmitDraft');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mengunggah...');

    $.post('<?= base_url('drafts/submit') ?>', $('#formUploadDraft').serialize(), function(res) {
        btn.prop('disabled', false).text('Kirim Draft');
        if (res.success) {
            $('#modalUploadDraft').modal('hide');
            dtDrafts.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Kirim Draft');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function viewDraft(id) {
    $('#modalViewDraft').modal('show');
    $('#viewDraftBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

    $.get('<?= base_url('drafts/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const d = res.data;
            let histHtml = '';
            if (d.history && d.history.length > 0) {
                histHtml = d.history.map(h => `
                    <div class="border rounded-2 p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between">
                            <strong>${h.reviewer_name || 'Reviewer'}</strong>
                            <span class="badge ${h.status === 'APPROVED' ? 'bg-success' : 'bg-danger'}">${h.status}</span>
                        </div>
                        <div class="small text-muted mt-1">${h.notes || '-'}</div>
                        ${h.timestamp_notes ? `<div class="mt-2 p-2 bg-white rounded border small font-monospace text-danger">${h.timestamp_notes}</div>` : ''}
                        <div class="text-muted text-end" style="font-size: 10.5px;">${h.created_at}</div>
                    </div>
                `).join('');
            } else {
                histHtml = '<div class="text-muted small fst-italic">Belum ada riwayat review</div>';
            }

            $('#viewDraftBody').html(`
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">${d.task_title || ('Task #' + d.task_number)} (Versi ${d.version})</h5>
                    <a href="${d.video_url}" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3"><i class="fas fa-play me-1"></i>Tonton Video</a>
                </div>
                <div class="text-muted small mb-3">Kampanye: <strong>${d.campaign_title}</strong> &bull; KOL: <strong>${d.kol_name}</strong></div>
                <div class="card bg-light border-0 mb-3 p-3">
                    <h6 class="fw-bold mb-1">Catatan Terakhir Reviewer</h6>
                    <div class="text-secondary">${d.review_notes || 'Belum ada catatan'}</div>
                    ${d.timestamp_notes ? `<div class="mt-2 p-2 bg-white rounded border small font-monospace text-danger"><strong>Timestamp Feedback:</strong><br>${d.timestamp_notes}</div>` : ''}
                </div>
                <hr>
                <h6 class="fw-bold mb-2"><i class="fas fa-history me-1"></i>Riwayat Review &amp; Revisi</h6>
                ${histHtml}
            `);
        }
    });
}

function reviewDraftModal(id) {
    $('#revDraftId').val(id);
    $('#draftRevNotes').val('');
    $('#draftTimestampNotes').val('');
    $('#modalReviewDraft').modal('show');
}

function submitDraftDecision(action) {
    const id = $('#revDraftId').val();
    const notes = $('#draftRevNotes').val();
    const timestampNotes = $('#draftTimestampNotes').val();
    const endpoint = action === 'approve' ? '<?= base_url('drafts/approve/') ?>' + id : '<?= base_url('drafts/revision/') ?>' + id;

    $.post(endpoint, {
        notes: notes,
        timestamp_notes: timestampNotes
    }, function(res) {
        if (res.success) {
            $('#modalReviewDraft').modal('hide');
            dtDrafts.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}
</script>
<?= $this->endSection() ?>
