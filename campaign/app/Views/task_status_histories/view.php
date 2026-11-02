<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i>Detail Riwayat Status Task</h3>
        <p class="text-muted small mb-0">Rincian catatan dan audit trail transisi alur kerja task kampanye.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('task-status-histories') ?>" class="text-decoration-none">Task Status Histories</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $history['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <?php 
              $fromSt = !empty($history['from_status']) ? strtoupper(trim((string)$history['from_status'])) : null;
              $toSt   = strtoupper(trim((string)($history['to_status'] ?: ($history['status'] ?: 'DRAFT'))));

              $badgeClass = match ($toSt) {
                'BRIEF_APPROVED', 'DRAFT_APPROVED', 'APPROVED_DRAFT', 'COMPLETED', 'DONE' => 'bg-success text-white',
                'BRIEF_REVISION', 'DRAFT_REVISION', 'REVISION', 'REVISION_DRAFT' => 'bg-warning text-dark',
                'BRIEF_SUBMITTED', 'DRAFT_SUBMITTED', 'PENDING_REVIEW', 'IN_REVIEW_DRAFT', 'WAITING_DRAFT' => 'bg-info text-white',
                'SCHEDULED', 'POSTED' => 'bg-primary text-white',
                'CANCELLED' => 'bg-danger text-white',
                default => 'bg-secondary text-white'
              };
            ?>
            <div class="d-flex align-items-center gap-2">
              <?php if ($fromSt && $fromSt !== $toSt): ?>
                <span class="badge bg-light text-dark border px-3 py-2 fs-6 rounded-pill"><?= esc($fromSt) ?></span>
                <i class="fas fa-arrow-right text-muted"></i>
              <?php endif; ?>
              <span class="badge <?= $badgeClass ?> px-3 py-2 fs-6 rounded-pill">
                <?= esc($toSt) ?>
              </span>
            </div>
            <div>
              <h4 class="fw-bold mb-0 text-dark"><?= esc($history['task_title'] ?: ('Task #' . $history['task_id'])) ?></h4>
              <small class="text-muted">Task No: #<?= esc($history['task_number'] ?? '1') ?> &bull; Dicatat: <?= date('d M Y, H:i:s', strtotime($history['created_at'])) ?> WIB</small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('task-status-histories/single-pdf/' . $history['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Lembar PDF
            </a>
            <a href="<?= base_url('tasks/view/' . $history['task_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-external-link-alt me-1"></i> Buka Detail Task
            </a>
            <a href="<?= base_url('task-status-histories') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Notes & Task Info -->
      <div class="col-lg-8">
        
        <!-- Catatan Transisi Status -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-comment-dots text-primary me-2"></i>Catatan Perubahan Status</h5>
          </div>
          <div class="card-body p-4">
            <div class="p-3 bg-light rounded-3 border">
              <div class="small text-dark" style="white-space: pre-line; line-height: 1.7; font-size: 14px;">
                <?= !empty($history['resolved_note']) ? esc($history['resolved_note']) : '<em class="text-muted">Tidak ada catatan transisi yang dilampirkan.</em>' ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Task & Campaign Overview -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-bullhorn text-primary me-2"></i>Informasi Kampanye &amp; Tugas</h5>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted text-xs text-uppercase fw-semibold">Judul Task:</label>
                <div class="fw-bold text-dark fs-6"><?= esc($history['task_title'] ?: ('Task #' . $history['task_id'])) ?></div>
              </div>
              <div class="col-md-6">
                <label class="text-muted text-xs text-uppercase fw-semibold">Status Task Saat Ini:</label>
                <div>
                  <span class="badge bg-primary px-3 py-1.5"><?= esc($history['task_current_status'] ?? '-') ?></span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted text-xs text-uppercase fw-semibold">Kampanye:</label>
                <div class="fw-semibold text-primary"><?= esc($history['campaign_title'] ?? '-') ?></div>
              </div>
              <div class="col-md-6">
                <label class="text-muted text-xs text-uppercase fw-semibold">KOL / Kreator:</label>
                <div class="fw-semibold text-dark">
                  <i class="fas fa-user-circle text-secondary me-1"></i>
                  <?= esc($history['kol_name'] ?? 'KOL') ?>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted text-xs text-uppercase fw-semibold">Tipe Konten:</label>
                <div class="text-dark"><?= esc($history['content_type'] ?? 'Video Feed') ?></div>
              </div>
              <div class="col-md-6">
                <label class="text-muted text-xs text-uppercase fw-semibold">Deadline Target:</label>
                <div class="text-dark">
                  <i class="far fa-calendar-alt text-muted me-1"></i>
                  <?= !empty($history['task_deadline']) ? date('d M Y', strtotime($history['task_deadline'])) : '-' ?>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Audit Metadata & User Info -->
      <div class="col-lg-4">
        
        <!-- Metadata PIC / Aktor -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-shield text-info me-2"></i>Aktor Perubahan (PIC)</h6>
          </div>
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="fas fa-user-edit fa-lg"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0"><?= esc($history['changed_by_name'] ?? 'System') ?></h6>
                <small class="text-muted"><?= esc($history['changed_by_email'] ?? 'Otomatisasi Sistem') ?></small>
              </div>
            </div>
            <hr class="my-2">
            <ul class="list-group list-group-flush small">
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">User ID:</span>
                <span class="fw-semibold">#<?= $history['changed_by'] ?: '-' ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Role Aksi:</span>
                <span class="badge bg-light text-dark border">Operational / Leader</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Waktu Catat:</span>
                <span class="fw-semibold"><?= date('d/m/Y H:i:s', strtotime($history['created_at'])) ?></span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Alur 12-State Task Progression -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-project-diagram text-primary me-2"></i>Tahapan Alur Siklus Task</h6>
          </div>
          <div class="card-body p-3">
            <ul class="timeline-widget list-unstyled mb-0" style="font-size: 12px;">
              <li class="mb-2 d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold">1. Penugasan &amp; Brief</span>
              </li>
              <li class="mb-2 d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-info"></i>
                <span class="fw-semibold">2. Pengiriman Sampel Produk</span>
              </li>
              <li class="mb-2 d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-primary"></i>
                <span class="fw-semibold">3. Review Draft Konten Video</span>
              </li>
              <li class="mb-2 d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-warning"></i>
                <span class="fw-semibold">4. Live Posting &amp; Scraping</span>
              </li>
              <li class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold">5. Pembayaran &amp; Selesai</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Hapus Riwayat -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 text-center">
            <a href="javascript:void(0)" onclick="confirmDelete(<?= $history['id'] ?>)" class="btn btn-outline-danger btn-sm w-100 rounded-2">
              <i class="fas fa-trash me-1"></i> Hapus Entri Riwayat Ini
            </a>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function confirmDelete(id) {
  Swal.fire({
    title: 'Hapus Riwayat?',
    text: 'Entri riwayat status ini akan dihapus permanen dari sistem.',
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
</script>
<?= $this->endSection() ?>
