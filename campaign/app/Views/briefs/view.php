<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-contract text-primary me-2"></i>Detail Brief &amp; Guidelines</h3>
        <p class="text-muted small mb-0">Informasi lengkap instruksi brief konten, storyboard, dan riwayat persetujuan.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('briefs') ?>" class="text-decoration-none">Briefs</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $brief['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- Top Action Banner -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <span class="badge <?= ($brief['status'] === 'APPROVED' || $brief['approval_status'] === 'APPROVED') ? 'bg-success' : (($brief['status'] === 'REVISION' || $brief['approval_status'] === 'REVISION_NEEDED') ? 'bg-warning text-dark' : 'bg-primary') ?> px-3 py-2 fs-6 rounded-pill">
              <i class="fas <?= ($brief['status'] === 'APPROVED' || $brief['approval_status'] === 'APPROVED') ? 'fa-check-circle' : (($brief['status'] === 'REVISION' || $brief['approval_status'] === 'REVISION_NEEDED') ? 'fa-undo' : 'fa-clock') ?> me-1"></i>
              <?= esc($brief['status'] ?: $brief['approval_status']) ?>
            </span>
            <div>
              <h4 class="fw-bold mb-0 text-dark"><?= esc($brief['title']) ?></h4>
              <small class="text-muted">Brief ID: #<?= $brief['id'] ?> &bull; Dibuat pada: <?= date('d M Y H:i', strtotime($brief['created_at'])) ?> WIB</small>
            </div>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('briefs/single-pdf/' . $brief['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak / PDF
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-2" onclick="editBrief(<?= $brief['id'] ?>)">
              <i class="fas fa-edit me-1"></i> Edit Brief
            </button>
            <?php if ($brief['status'] !== 'APPROVED' && $brief['approval_status'] !== 'APPROVED'): ?>
              <button type="button" class="btn btn-warning btn-sm rounded-2 fw-semibold" onclick="reviewModal('revision')">
                <i class="fas fa-undo me-1"></i> Minta Revisi
              </button>
              <button type="button" class="btn btn-success btn-sm rounded-2 fw-semibold" onclick="reviewModal('approve')">
                <i class="fas fa-check me-1"></i> Setujui (Approve)
              </button>
            <?php endif; ?>
            <a href="<?= base_url('briefs') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Main Content Column -->
      <div class="col-lg-8">
        
        <!-- Guidelines & Storyline Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-align-left text-primary me-2"></i>Petunjuk Konten &amp; Guidelines</h5>
          </div>
          <div class="card-body p-4">
            <div class="bg-light p-3 rounded-3 text-secondary" style="white-space: pre-line; line-height: 1.7; font-size: 14.5px;">
              <?= esc($brief['content'] ?: $brief['guidelines']) ?>
            </div>

            <!-- Do's and Don'ts -->
            <div class="row g-3 mt-3">
              <div class="col-md-6">
                <div class="border rounded-3 p-3 border-success-subtle bg-success-subtle text-success h-100">
                  <h6 class="fw-bold mb-2"><i class="fas fa-check-circle me-1"></i>Do's (Hal yang Harus Dilakukan)</h6>
                  <div class="small" style="white-space: pre-line; line-height: 1.6;">
                    <?= esc($brief['dos'] ?: 'Tidak ada instruksi khusus.') ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded-3 p-3 border-danger-subtle bg-danger-subtle text-danger h-100">
                  <h6 class="fw-bold mb-2"><i class="fas fa-times-circle me-1"></i>Don'ts (Hal yang Dilarang)</h6>
                  <div class="small" style="white-space: pre-line; line-height: 1.6;">
                    <?= esc($brief['donts'] ?: 'Tidak ada larangan khusus.') ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Moodboard Reference -->
            <?php if (!empty($brief['reference_url'])): ?>
              <div class="mt-4 p-3 border rounded-3 bg-white">
                <strong class="d-block mb-1 text-dark"><i class="fas fa-link text-primary me-1"></i>Moodboard &amp; URL Referensi:</strong>
                <a href="<?= esc($brief['reference_url']) ?>" target="_blank" class="text-decoration-underline text-primary small">
                  <?= esc($brief['reference_url']) ?> <i class="fas fa-external-link-alt ms-1"></i>
                </a>
              </div>
            <?php endif; ?>

            <?php if (!empty($brief['file_path'])): ?>
              <div class="mt-3 p-3 border rounded-3 bg-white d-flex justify-content-between align-items-center">
                <div>
                  <strong class="text-dark"><i class="fas fa-paperclip text-secondary me-1"></i>File Lampiran Brief:</strong>
                  <div class="small text-muted"><?= esc($brief['file_path']) ?></div>
                </div>
                <a href="<?= base_url($brief['file_path']) ?>" download class="btn btn-outline-primary btn-sm rounded-2">
                  <i class="fas fa-download me-1"></i> Unduh File
                </a>
              </div>
            <?php endif; ?>

          </div>
        </div>

        <!-- Version History Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-code-branch text-info me-2"></i>Riwayat Versi Konten</h5>
            <span class="badge bg-light text-dark border"><?= count($brief['versions'] ?? []) ?> Versi Sebelumnya</span>
          </div>
          <div class="card-body p-4">
            <?php if (!empty($brief['versions'])): ?>
              <div class="timeline">
                <?php foreach ($brief['versions'] as $v): ?>
                  <div class="border rounded-3 p-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="badge bg-primary px-2 py-1">Versi <?= $v['version'] ?></span>
                      <small class="text-muted"><i class="far fa-clock me-1"></i><?= date('d M Y H:i', strtotime($v['snapshot_at'] ?: $v['created_at'])) ?> WIB</small>
                    </div>
                    <div class="small text-dark fw-semibold mb-1"><?= esc($v['title']) ?></div>
                    <div class="text-secondary small mb-2" style="max-height: 100px; overflow-y: auto; white-space: pre-line;"><?= esc($v['content']) ?></div>
                    <?php if (!empty($v['content_diff'])): ?>
                      <div class="alert alert-warning py-1 px-2 small mb-0">
                        <strong>Catatan Revisi:</strong> <?= esc($v['content_diff']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-4 text-muted">
                <i class="fas fa-file-alt fa-2x mb-2 text-secondary opacity-50"></i>
                <p class="mb-0 small">Belum ada riwayat revisi. Brief masih dalam versi awal (Original).</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Sidebar Column -->
      <div class="col-lg-4">
        
        <!-- Metadata Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-secondary me-2"></i>Informasi Target &amp; Tugas</h6>
          </div>
          <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td class="text-muted" style="width: 130px;">Kampanye</td>
                <td class="fw-semibold text-primary"><?= esc($brief['campaign_title']) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Target KOL</td>
                <td class="fw-bold text-dark"><?= esc($brief['kol_name']) ?></td>
              </tr>
              <tr>
                <td class="text-muted">WhatsApp KOL</td>
                <td><?= !empty($brief['kol_whatsapp']) ? '<a href="https://wa.me/' . esc($brief['kol_whatsapp']) . '" target="_blank" class="text-success"><i class="fab fa-whatsapp me-1"></i>' . esc($brief['kol_whatsapp']) . '</a>' : '-' ?></td>
              </tr>
              <tr>
                <td class="text-muted">Target Task / VT</td>
                <td class="fw-semibold text-dark">#<?= $brief['task_id'] ?> (<?= esc($brief['task_title'] ?: ('VT #' . $brief['task_number'])) ?>)</td>
              </tr>
              <tr>
                <td class="text-muted">Status Task</td>
                <td><span class="badge bg-secondary"><?= esc($brief['task_status']) ?></span></td>
              </tr>
              <tr>
                <td class="text-muted">Deadline Task</td>
                <td class="text-danger fw-semibold"><?= !empty($brief['task_deadline']) ? date('d M Y', strtotime($brief['task_deadline'])) : '-' ?></td>
              </tr>
              <tr>
                <td class="text-muted">Jumlah Revisi</td>
                <td><span class="badge bg-light text-dark border"><?= (int)($brief['revision_count'] ?? 0) ?>x Revisi</span></td>
              </tr>
              <tr>
                <td class="text-muted">Dibuat Oleh</td>
                <td class="fw-semibold text-dark"><?= esc($brief['submitter_name'] ?? 'Admin') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Tanggal Submit</td>
                <td><?= !empty($brief['submitted_at']) ? date('d M Y H:i', strtotime($brief['submitted_at'])) : '-' ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Approval Timeline Card -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-clipboard-check text-success me-2"></i>Log Persetujuan (Approval)</h6>
          </div>
          <div class="card-body p-3">
            <?php if (!empty($brief['history'])): ?>
              <div class="timeline">
                <?php foreach ($brief['history'] as $h): ?>
                  <div class="border rounded-2 p-2 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                      <strong class="text-dark"><i class="fas fa-user-shield me-1"></i><?= esc($h['reviewer_name'] ?? 'Reviewer') ?></strong>
                      <span class="badge <?= $h['status'] === 'APPROVED' ? 'bg-success' : 'bg-warning' ?>"><?= esc($h['status']) ?></span>
                    </div>
                    <div class="small text-secondary mt-1"><?= esc($h['full_note'] ?: ($h['notes'] ?: ($h['note'] ?: '-'))) ?></div>
                    <div class="text-muted text-end mt-1" style="font-size: 10px;"><?= date('d M Y H:i', strtotime($h['created_at'])) ?> WIB</div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-4 text-muted small">
                <i class="far fa-clock fa-2x mb-2 text-secondary opacity-50"></i>
                <p class="mb-0">Belum ada catatan review dari Leader.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- Modal Decision / Revision -->
<div class="modal fade" id="modalDecision" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="decModalTitle"><i class="fas fa-clipboard-check me-2"></i>Keputusan Brief</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="decAction">
        <div class="mb-3">
          <label class="form-label fw-semibold" id="decNotesLabel">Catatan Review</label>
          <textarea class="form-control" id="decNotes" rows="4" placeholder="Tuliskan catatan detail persetujuan atau poin-poin yang wajib direvisi..."></textarea>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary rounded-2 px-4 fw-semibold" id="btnSubmitDecision" onclick="executeDecision()">Konfirmasi</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function reviewModal(action) {
    $('#decAction').val(action);
    $('#decNotes').val('');
    if (action === 'approve') {
        $('#decModalTitle').html('<i class="fas fa-check-circle me-2 text-white"></i>Setujui Brief');
        $('#decNotesLabel').text('Catatan Persetujuan (Opsional)');
        $('#btnSubmitDecision').removeClass('btn-warning').addClass('btn-success').text('Setujui (Approve)');
    } else {
        $('#decModalTitle').html('<i class="fas fa-undo me-2 text-white"></i>Minta Revisi Brief');
        $('#decNotesLabel').text('Catatan Poin Revisi (Wajib Diisi)');
        $('#btnSubmitDecision').removeClass('btn-success').addClass('btn-warning text-dark').text('Kirim Catatan Revisi');
    }
    $('#modalDecision').modal('show');
}

function executeDecision() {
    const action = $('#decAction').val();
    const notes = $('#decNotes').val();
    const briefId = <?= $brief['id'] ?>;
    const endpoint = action === 'approve' ? '<?= base_url('briefs/approve/') ?>' + briefId : '<?= base_url('briefs/revision/') ?>' + briefId;

    if (action === 'revision' && !notes.trim()) {
        Swal.fire({ icon: 'warning', title: 'Catatan Diperlukan', text: 'Harap masukkan catatan revisi untuk KOL/Staff.' });
        return;
    }

    const btn = $('#btnSubmitDecision');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

    $.post(endpoint, { notes: notes, note: notes }, function(res) {
        btn.prop('disabled', false).text('Konfirmasi');
        if (res.success) {
            $('#modalDecision').modal('hide');
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false }).then(() => {
                window.location.reload();
            });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Konfirmasi');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}
</script>
<?= $this->endSection() ?>
