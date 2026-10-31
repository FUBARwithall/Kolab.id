<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Detail Snapshot Versi Konten</h3>
        <p class="text-muted small mb-0">Arsip snapshot versi konten brief sebelum permintaan revisi.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('brief-content-histories') ?>" class="text-decoration-none">Content Histories</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $hist['id'] ?></li>
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
            <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
              <i class="fas fa-code-branch me-1"></i>Versi <?= $hist['version'] ?>
            </span>
            <div>
              <h4 class="fw-bold mb-0 text-dark"><?= esc($hist['title'] ?: ($hist['briefs_title'] ?? ('Snapshot #' . $hist['id']))) ?></h4>
              <small class="text-muted">Target Brief: #<?= $hist['brief_id'] ?> &bull; Waktu Snapshot: <?= date('d M Y H:i:s', strtotime($hist['snapshot_at'] ?: $hist['created_at'])) ?> WIB</small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('brief-content-histories/single-pdf/' . $hist['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak / PDF
            </a>
            <a href="<?= base_url('briefs/view/' . $hist['brief_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-external-link-alt me-1"></i> Buka Brief Terkini
            </a>
            <a href="<?= base_url('brief-content-histories') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Content of Snapshot -->
      <div class="col-lg-8">
        
        <?php if (!empty($hist['content_diff'])): ?>
          <div class="alert alert-warning border border-warning-subtle shadow-sm rounded-3 mb-4 p-3">
            <h6 class="fw-bold mb-1 text-dark"><i class="fas fa-comment-dots text-warning me-2"></i>Catatan Alasan Revisi oleh Leader</h6>
            <div class="small text-secondary" style="white-space: pre-line;"><?= esc($hist['content_diff']) ?></div>
          </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-align-left text-primary me-2"></i>Isi Konten &amp; Guidelines Versi <?= $hist['version'] ?></h5>
          </div>
          <div class="card-body p-4">
            <div class="bg-light p-3 rounded-3 text-secondary" style="white-space: pre-line; line-height: 1.7; font-size: 14px;">
              <?= esc($hist['content'] ?: 'Tidak ada teks isi konten tersimpan.') ?>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Metadata -->
      <div class="col-lg-4">
        
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Brief &amp; Target</h6>
          </div>
          <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td class="text-muted" style="width: 140px;">Judul Brief</td>
                <td class="fw-bold text-dark"><?= esc($hist['briefs_title'] ?? ('Brief #' . $hist['brief_id'])) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Target Tugas / VT</td>
                <td class="fw-semibold text-primary"><?= esc($hist['task_title'] ?: ('VT #' . ($hist['task_number'] ?? '1'))) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Kampanye</td>
                <td class="fw-semibold text-dark"><?= esc($hist['campaign_title'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Talent KOL</td>
                <td class="fw-semibold text-dark"><?= esc($hist['kol_name'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Editor / Author</td>
                <td><i class="fas fa-user-edit me-1 text-secondary"></i><?= esc($hist['author_name'] ?? 'Editor') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Waktu Snapshot</td>
                <td><?= date('d M Y H:i:s', strtotime($hist['snapshot_at'] ?: $hist['created_at'])) ?> WIB</td>
              </tr>
            </table>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
