<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-video text-primary me-2"></i>Detail Riwayat Review Draft Video</h3>
        <p class="text-muted small mb-0">Rincian catatan keputusan evaluasi konten video draft KOL oleh Leader.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('draft-review-histories') ?>" class="text-decoration-none">Draft Review Histories</a></li>
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
            <?php 
              $act = strtoupper(trim((string)$hist['resolved_action']));
              $badgeClass = match ($act) {
                'APPROVED' => 'bg-success text-white',
                'REVISION', 'REVISION_NEEDED' => 'bg-warning text-dark',
                'SUBMITTED', 'PENDING' => 'bg-primary text-white',
                default => 'bg-secondary text-white'
              };
            ?>
            <span class="badge <?= $badgeClass ?> px-3 py-2 fs-6 rounded-pill">
              <?= esc($act) ?>
            </span>
            <div>
              <h4 class="fw-bold mb-0 text-dark">Draft #<?= $hist['draft_id'] ?> (Versi <?= $hist['draft_version'] ?? '1' ?>)</h4>
              <small class="text-muted">Target Tugas: <?= esc($hist['task_title'] ?: ('VT #' . ($hist['task_number'] ?? '1'))) ?> &bull; Review Dilakukan: <?= date('d M Y H:i:s', strtotime($hist['created_at'])) ?> WIB</small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('draft-review-histories/single-pdf/' . $hist['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Lembar PDF
            </a>
            <a href="<?= base_url('drafts/view/' . $hist['draft_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-external-link-alt me-1"></i> Buka Detail Draft
            </a>
            <a href="<?= base_url('draft-review-histories') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Reviewer Notes & Video Information -->
      <div class="col-lg-8">
        
        <!-- Catatan Reviewer -->
        <?php 
          $alertClass = match ($act) {
            'APPROVED' => 'alert-success border-success-subtle',
            'REVISION', 'REVISION_NEEDED' => 'alert-warning border-warning-subtle',
            'SUBMITTED', 'PENDING' => 'alert-info border-info-subtle',
            default => 'alert-secondary border-secondary-subtle'
          };
        ?>
        <div class="alert <?= $alertClass ?> border shadow-sm rounded-3 mb-4 p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0"><i class="fas fa-comment-dots me-2"></i>Catatan Reviewer / Leader:</h6>
            <span class="badge <?= $badgeClass ?>"><?= $act ?></span>
          </div>
          <div class="small" style="white-space: pre-line; line-height: 1.6;">
            <?= !empty($hist['resolved_note']) ? esc($hist['resolved_note']) : '<em class="text-muted">Tidak ada catatan review yang dilampirkan.</em>' ?>
          </div>
        </div>

        <?php if (!empty($hist['timestamp_notes'])): ?>
          <div class="alert alert-warning border border-warning-subtle shadow-sm rounded-3 mb-4 p-3">
            <h6 class="fw-bold mb-1 text-dark"><i class="fas fa-stopwatch text-warning me-2"></i>Catatan Detik / Menit Tertentu (Timestamp Feedback)</h6>
            <div class="small text-secondary" style="white-space: pre-line; line-height: 1.6;"><?= esc($hist['timestamp_notes']) ?></div>
          </div>
        <?php endif; ?>

        <!-- Video Draft Information -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-play-circle text-primary me-2"></i>Informasi Video Draft &amp; Tautan</h5>
          </div>
          <div class="card-body p-4">
            <div class="mb-3">
              <label class="form-label text-muted small fw-semibold">Tautan Video Draft (TikTok / Google Drive / URL):</label>
              <?php if (!empty($hist['video_url'])): ?>
                <div class="input-group">
                  <input type="text" class="form-control" value="<?= esc($hist['video_url']) ?>" readonly>
                  <a href="<?= esc($hist['video_url']) ?>" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-1"></i> Buka Video
                  </a>
                </div>
              <?php else: ?>
                <div class="alert alert-light border text-muted small mb-0">Belum ada tautan video yang dilampirkan.</div>
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <label class="form-label text-muted small fw-semibold">Rencana Caption:</label>
              <div class="bg-light p-3 rounded-3 text-secondary" style="white-space: pre-line; line-height: 1.6; font-size: 13.5px;">
                <?= esc($hist['draft_caption'] ?: 'Tidak ada teks caption yang dilampirkan.') ?>
              </div>
            </div>

            <?php if (!empty($hist['draft_hashtag'])): ?>
              <div>
                <label class="form-label text-muted small fw-semibold">Hashtag Rekomendasi:</label>
                <div class="text-primary fw-semibold small"><?= esc($hist['draft_hashtag']) ?></div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Riwayat Review Lainnya untuk Draft Ini -->
        <?php if (!empty($allHistories) && count($allHistories) > 1): ?>
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-primary me-2"></i>Semua Riwayat Review untuk Draft Ini (<?= count($allHistories) ?> Log)</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-3">ID</th>
                      <th>Keputusan</th>
                      <th>Catatan Review</th>
                      <th>Reviewer</th>
                      <th>Waktu Review</th>
                      <th class="text-end pe-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($allHistories as $oh): ?>
                      <tr class="<?= $oh['id'] == $hist['id'] ? 'table-primary bg-opacity-10 fw-bold' : '' ?>">
                        <td class="ps-3">#<?= $oh['id'] ?></td>
                        <td>
                          <?php
                            $oact = strtoupper(trim((string)$oh['resolved_action']));
                            $obadge = match ($oact) {
                              'APPROVED' => 'bg-success',
                              'REVISION', 'REVISION_NEEDED' => 'bg-warning text-dark',
                              'SUBMITTED', 'PENDING' => 'bg-primary',
                              default => 'bg-secondary'
                            };
                          ?>
                          <span class="badge <?= $obadge ?> px-2 py-1"><?= $oact ?></span>
                        </td>
                        <td>
                          <div class="text-truncate" style="max-width: 250px;" title="<?= esc($oh['resolved_note']) ?>">
                            <?= esc($oh['resolved_note'] ?: '-') ?>
                          </div>
                        </td>
                        <td><?= esc($oh['reviewer_name'] ?? 'Reviewer') ?></td>
                        <td><?= date('d M Y H:i', strtotime($oh['created_at'])) ?></td>
                        <td class="text-end pe-3">
                          <?php if ($oh['id'] != $hist['id']): ?>
                            <a href="<?= base_url('draft-review-histories/view/' . $oh['id']) ?>" class="btn btn-outline-primary btn-xs py-0 px-2 rounded">Buka</a>
                          <?php else: ?>
                            <span class="badge bg-primary text-white">Aktif</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Right Column: Metadata Details -->
      <div class="col-lg-4">
        
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Target &amp; Peninjau</h6>
          </div>
          <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td class="text-muted" style="width: 140px;">ID Draft</td>
                <td class="fw-bold text-dark">#<?= $hist['draft_id'] ?> (v<?= $hist['draft_version'] ?? '1' ?>)</td>
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
                <td class="text-muted">Reviewer / Leader</td>
                <td>
                  <div class="fw-semibold text-dark"><i class="fas fa-user-check me-1 text-info"></i><?= esc($hist['reviewer_name'] ?? 'Reviewer') ?></div>
                  <?php if (!empty($hist['reviewer_email'])): ?>
                    <small class="text-muted"><?= esc($hist['reviewer_email']) ?></small>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Keputusan</td>
                <td><span class="badge <?= $badgeClass ?> px-2 py-1"><?= $act ?></span></td>
              </tr>
              <tr>
                <td class="text-muted">Waktu Review</td>
                <td><?= date('d M Y H:i:s', strtotime($hist['created_at'])) ?> WIB</td>
              </tr>
            </table>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
