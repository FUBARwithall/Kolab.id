<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-video text-warning me-2"></i>Detail Draft Video #<?= $draft['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Spesifikasi materi video draft, caption, tagar, dan log audit ulasan revisi/approval tim.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= base_url('drafts') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <?php $dUrl = $draft['live_draft_url'] ?: ($draft['draft_url'] ?: $draft['video_url']); ?>
        <?php if (!empty($dUrl)): ?>
          <a href="<?= esc($dUrl) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2 px-3 me-2">
            <i class="fas fa-play text-danger me-1"></i> Tonton Video Draft
          </a>
        <?php endif; ?>

        <?php $status = strtoupper((string)$draft['status']); ?>
        <?php if (in_array($status, ['DRAFT', 'REVISION', 'REVISION_NEEDED'])): ?>
          <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 me-2" onclick="submitForReview(<?= $draft['id'] ?>)">
            <i class="fas fa-paper-plane me-1"></i> Submit untuk Review
          </button>
        <?php endif; ?>

        <?php if (in_array($status, ['SUBMITTED', 'PENDING'])): ?>
          <button type="button" class="btn btn-outline-warning btn-sm rounded-2 px-3 me-2" onclick="openRevisionModal(<?= $draft['id'] ?>)">
            <i class="fas fa-undo me-1"></i> Minta Revisi
          </button>
          <button type="button" class="btn btn-success btn-sm rounded-2 px-3 shadow-sm" onclick="openApproveModal(<?= $draft['id'] ?>)">
            <i class="fas fa-check me-1"></i> Setujui Draft (Approve)
          </button>
        <?php endif; ?>
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

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #0284c7 !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Versi Draft</span>
            <h3 class="fw-bold mb-0 text-dark mt-1">v<?= $draft['version'] ?></h3>
            <small class="text-muted">Iterasi pembuatan konten</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #10b981 !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Status Ulasan</span>
            <div class="mt-2">
              <?php if ($status === 'APPROVED'): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 px-3 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>
              <?php elseif (in_array($status, ['REVISION', 'REVISION_NEEDED'])): ?>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6 px-3 py-1"><i class="fas fa-exclamation-circle me-1"></i>REVISION</span>
              <?php elseif (in_array($status, ['SUBMITTED', 'PENDING'])): ?>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-6 px-3 py-1"><i class="far fa-clock me-1"></i>SUBMITTED</span>
              <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border fs-6 px-3 py-1">DRAFT</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #8b5cf6 !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Talent Kreator</span>
            <h5 class="fw-bold mb-0 text-dark mt-1 text-truncate"><?= esc($draft['kol_name'] ?: '-') ?></h5>
            <small class="text-muted"><?= esc($draft['kol_whatsapp'] ?: '-') ?></small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f59e0b !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Total Riwayat Review</span>
            <h3 class="fw-bold mb-0 text-warning mt-1"><?= count($history) ?> Log</h3>
            <small class="text-muted">Catatan revisi &amp; approval</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Spesifikasi Metadata Draft Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark">
          <i class="fas fa-info-circle text-primary me-2"></i>Spesifikasi Metadata Draft Video
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered mb-0 align-middle">
            <tbody>
              <tr>
                <td style="width: 220px;" class="bg-light fw-semibold text-secondary">ID Draft</td>
                <td class="fw-bold text-dark">#<?= $draft['id'] ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Target Tugas / Task</td>
                <td>
                  <div class="fw-bold text-dark"><?= esc($draft['task_title'] ?: ('VT #' . $draft['task_number'])) ?></div>
                  <small class="text-muted">Task ID: #<?= $draft['task_id'] ?> &bull; No. VT: <?= esc($draft['task_number'] ?: '-') ?> &bull; Status Tugas: <span class="badge bg-secondary-subtle text-dark border"><?= esc($draft['task_status'] ?: '-') ?></span></small>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Kampanye</td>
                <td class="fw-semibold text-primary"><?= esc($draft['campaign_title'] ?: '-') ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Talent KOL</td>
                <td>
                  <div class="fw-bold text-dark"><?= esc($draft['kol_name'] ?: '-') ?></div>
                  <small class="text-muted">WA: <?= esc($draft['kol_whatsapp'] ?: '-') ?></small>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Link / URL Video Draft</td>
                <td>
                  <?php if (!empty($dUrl)): ?>
                    <a href="<?= esc($dUrl) ?>" target="_blank" class="fw-bold text-primary text-break">
                      <?= esc($dUrl) ?> <i class="fas fa-external-link-alt ms-1 small"></i>
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Caption Video</td>
                <td class="text-secondary"><?= !empty($draft['caption']) ? nl2br(esc($draft['caption'])) : '<span class="text-muted">-</span>' ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Hashtag / Tagar</td>
                <td class="text-secondary"><?= !empty($draft['hashtag']) ? nl2br(esc($draft['hashtag'])) : '<span class="text-muted">-</span>' ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Catatan dari KOL / Kreator</td>
                <td class="text-secondary"><?= !empty($draft['notes']) ? nl2br(esc($draft['notes'])) : '<span class="text-muted">-</span>' ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Catatan Terakhir Reviewer</td>
                <td>
                  <?php if (!empty($draft['review_notes'])): ?>
                    <div class="fw-semibold text-dark"><?= nl2br(esc($draft['review_notes'])) ?></div>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>

                  <?php if (!empty($draft['timestamp_notes'])): ?>
                    <div class="mt-2 p-2 bg-light border rounded small font-monospace text-danger">
                      <strong>Timestamp Feedback:</strong><br><?= nl2br(esc($draft['timestamp_notes'])) ?>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Diajukan Oleh</td>
                <td><?= esc($draft['creator_name'] ?: 'Staff / System') ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Reviewer Terakhir</td>
                <td><?= esc($draft['reviewer_name'] ?: '-') ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Waktu Submit &amp; Review</td>
                <td class="text-muted small">
                  Disubmit: <?= !empty($draft['submitted_at']) ? date('d M Y H:i:s', strtotime($draft['submitted_at'])) : '-' ?> &bull; 
                  Direview: <?= !empty($draft['reviewed_at']) ? date('d M Y H:i:s', strtotime($draft['reviewed_at'])) : '-' ?>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Dibuat &amp; Terakhir Diperbarui</td>
                <td class="text-muted small">
                  Dibuat: <?= !empty($draft['created_at']) ? date('d M Y H:i:s', strtotime($draft['created_at'])) : '-' ?> &bull; 
                  Diperbarui: <?= !empty($draft['updated_at']) ? date('d M Y H:i:s', strtotime($draft['updated_at'])) : '-' ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Riwayat Ulasan & Log Review (Audit Trail) -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark">
          <i class="fas fa-history text-info me-2"></i>Riwayat Ulasan &amp; Revisi (Review Audit Trail)
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th>Log ID</th>
                <th>Aksi Review</th>
                <th>Reviewer</th>
                <th>Catatan / Feedback</th>
                <th>Timestamp Notes</th>
                <th>Waktu Log</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($history)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">Belum ada catatan ulasan untuk draft ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($history as $h): ?>
                  <?php
                    $act = strtoupper((string)($h['action_name'] ?: ($h['action'] ?? ($h['status'] ?? ''))));
                    $badgeClass = match ($act) {
                      'APPROVED' => 'bg-success-subtle text-success border border-success-subtle',
                      'REVISION', 'REVISION_NEEDED' => 'bg-danger-subtle text-danger border border-danger-subtle',
                      'SUBMITTED', 'PENDING' => 'bg-warning-subtle text-warning border border-warning-subtle',
                      default => 'bg-secondary-subtle text-secondary border',
                    };
                  ?>
                  <tr>
                    <td class="fw-bold">#<?= $h['id'] ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= esc($act) ?></span></td>
                    <td class="fw-semibold text-dark"><?= esc($h['reviewer_name'] ?: 'Reviewer') ?></td>
                    <td><?= esc($h['review_note'] ?: ($h['note'] ?? ($h['notes'] ?? '-'))) ?></td>
                    <td>
                      <?php if (!empty($h['timestamp_notes'])): ?>
                        <span class="badge bg-light text-danger border font-monospace"><?= esc($h['timestamp_notes']) ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= !empty($h['created_at']) ? date('d M Y H:i:s', strtotime($h['created_at'])) : '-' ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Minta Revisi -->
<div class="modal fade" id="modalRevision" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formRevision" onsubmit="submitRevision(event)">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-undo me-2"></i>Minta Revisi Draft Video</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Revisi Evaluasi <span class="text-danger">*</span></label>
            <textarea class="form-control" name="notes" id="revisionNote" rows="3" required placeholder="Jelaskan hal-hal yang perlu diperbaiki oleh kreator..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-danger"><i class="fas fa-stopwatch me-1"></i>Timestamp Feedback Spesifik (Opsional)</label>
            <textarea class="form-control font-monospace" name="timestamp_notes" rows="2" placeholder="00:15 - Label terpotong&#10;00:45 - Volume audio terlalu kecil"></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger rounded-2 px-4" id="btnSubmitRevision">Kirim Permintaan Revisi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Approve Draft -->
<div class="modal fade" id="modalApprove" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formApprove" onsubmit="submitApprove(event)">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-check me-2"></i>Setujui Draft Video (Approve)</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-3">Apakah Anda yakin materi draft video ini sudah sesuai dengan brief &amp; guideline kampanye?</p>
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Approval (Opsional)</label>
            <textarea class="form-control" name="notes" id="approveNote" rows="2" placeholder="Video disetujui, siap dijadwalkan tayang live..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success rounded-2 px-4" id="btnSubmitApprove">Ya, Setujui Draft</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function submitForReview(id) {
    Swal.fire({
        title: 'Submit Draft untuk Review?',
        text: 'Draft video akan diajukan ke Leader untuk proses quality control.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0284c7',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Ya, Ajukan Review',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('drafts/submit-review') ?>/' + id, function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => { location.reload(); });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat mengajukan draft.', 'error');
            });
        }
    });
}

function openRevisionModal(id) {
    $('#revisionNote').val('');
    $('#modalRevision').modal('show');
}

function submitRevision(e) {
    e.preventDefault();
    const btn = $('#btnSubmitRevision');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...');

    $.post('<?= base_url('drafts/revision/' . $draft['id']) ?>', $('#formRevision').serialize(), function(res) {
        btn.prop('disabled', false).text('Kirim Permintaan Revisi');
        if (res.success) {
            $('#modalRevision').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Revisi Terkirim!',
                text: res.message,
                timer: 1800,
                showConfirmButton: false
            }).then(() => { location.reload(); });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).fail(function() {
        btn.prop('disabled', false).text('Kirim Permintaan Revisi');
        Swal.fire('Error', 'Gagal memproses revisi draft.', 'error');
    });
}

function openApproveModal(id) {
    $('#approveNote').val('Draft video disetujui, siap tayang.');
    $('#modalApprove').modal('show');
}

function submitApprove(e) {
    e.preventDefault();
    const btn = $('#btnSubmitApprove');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyetujui...');

    $.post('<?= base_url('drafts/approve/' . $draft['id']) ?>', $('#formApprove').serialize(), function(res) {
        btn.prop('disabled', false).text('Ya, Setujui Draft');
        if (res.success) {
            $('#modalApprove').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Disetujui!',
                text: res.message,
                timer: 1800,
                showConfirmButton: false
            }).then(() => { location.reload(); });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).fail(function() {
        btn.prop('disabled', false).text('Ya, Setujui Draft');
        Swal.fire('Error', 'Gagal menyetujui draft video.', 'error');
    });
}
</script>
<?= $this->endSection() ?>
