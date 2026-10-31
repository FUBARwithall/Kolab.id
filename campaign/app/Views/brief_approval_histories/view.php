<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-clipboard-check text-primary me-2"></i>Detail Riwayat Approval Brief</h3>
        <p class="text-muted small mb-0">Rincian catatan keputusan review dan persetujuan brief oleh Leader.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('brief-approval-histories') ?>" class="text-decoration-none">Approval Histories</a></li>
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
              $st = strtoupper(trim((string)$hist['status']));
              $badgeClass = match ($st) {
                'APPROVED' => 'bg-success text-white',
                'REVISION', 'REVISION_NEEDED' => 'bg-warning text-dark',
                'REJECTED' => 'bg-danger text-white',
                default => 'bg-secondary text-white'
              };
            ?>
            <span class="badge <?= $badgeClass ?> px-3 py-2 fs-6 rounded-pill">
              <?= esc($st) ?>
            </span>
            <div>
              <h4 class="fw-bold mb-0 text-dark"><?= esc($hist['briefs_title'] ?? ('Brief #' . $hist['brief_id'])) ?></h4>
              <small class="text-muted">Target Brief ID: #<?= $hist['brief_id'] ?> &bull; Review Dilakukan: <?= date('d M Y H:i:s', strtotime($hist['created_at'])) ?> WIB</small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('brief-approval-histories/single-pdf/' . $hist['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Lembar PDF
            </a>
            <a href="<?= base_url('briefs/view/' . $hist['brief_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-external-link-alt me-1"></i> Buka Detail Brief
            </a>
            <a href="<?= base_url('brief-approval-histories') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Reviewer Notes & Brief Contents -->
      <div class="col-lg-8">
        
        <!-- Catatan Reviewer -->
        <?php 
          $alertClass = match ($st) {
            'APPROVED' => 'alert-success border-success-subtle',
            'REVISION', 'REVISION_NEEDED' => 'alert-warning border-warning-subtle',
            'REJECTED' => 'alert-danger border-danger-subtle',
            default => 'alert-secondary border-secondary-subtle'
          };
        ?>
        <div class="alert <?= $alertClass ?> border shadow-sm rounded-3 mb-4 p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0"><i class="fas fa-comment-dots me-2"></i>Catatan Reviewer / Leader:</h6>
            <span class="badge <?= $badgeClass ?>"><?= $st ?></span>
          </div>
          <div class="small" style="white-space: pre-line; line-height: 1.6;">
            <?= !empty($hist['resolved_note']) ? esc($hist['resolved_note']) : '<em class="text-muted">Tidak ada catatan tertulis yang ditinggalkan oleh reviewer.</em>' ?>
          </div>
        </div>

        <!-- Pedoman Brief & Storyline -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-align-left text-primary me-2"></i>Pedoman Konten (Brief Guidelines)</h5>
          </div>
          <div class="card-body p-4">
            <div class="bg-light p-3 rounded-3 text-secondary" style="white-space: pre-line; line-height: 1.7; font-size: 14px;">
              <?= esc($hist['brief_guidelines'] ?: ($hist['brief_content'] ?: 'Tidak ada teks panduan konten.')) ?>
            </div>
          </div>
        </div>

        <?php if (!empty($hist['brief_dos']) || !empty($hist['brief_donts'])): ?>
          <div class="row g-3 mb-4">
            <?php if (!empty($hist['brief_dos'])): ?>
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-success">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-success mb-2"><i class="fas fa-check-circle me-1"></i>Do's (Hal yang Harus Dilakukan)</h6>
                    <div class="small text-secondary" style="white-space: pre-line;"><?= esc($hist['brief_dos']) ?></div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <?php if (!empty($hist['brief_donts'])): ?>
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-danger">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-danger mb-2"><i class="fas fa-times-circle me-1"></i>Don'ts (Hal yang Dilarang)</h6>
                    <div class="small text-secondary" style="white-space: pre-line;"><?= esc($hist['brief_donts']) ?></div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Riwayat Review Lainnya untuk Brief Ini -->
        <?php if (!empty($allHistories) && count($allHistories) > 1): ?>
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-primary me-2"></i>Semua Riwayat Review untuk Brief Ini (<?= count($allHistories) ?> Log)</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-3">ID</th>
                      <th>Status</th>
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
                            $ost = strtoupper(trim((string)$oh['status']));
                            $obadge = match ($ost) {
                              'APPROVED' => 'bg-success',
                              'REVISION', 'REVISION_NEEDED' => 'bg-warning text-dark',
                              'REJECTED' => 'bg-danger',
                              default => 'bg-secondary'
                            };
                          ?>
                          <span class="badge <?= $obadge ?> px-2 py-1"><?= $ost ?></span>
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
                            <a href="<?= base_url('brief-approval-histories/view/' . $oh['id']) ?>" class="btn btn-outline-primary btn-xs py-0 px-2 rounded">Buka</a>
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
                <td><span class="badge <?= $badgeClass ?> px-2 py-1"><?= $st ?></span></td>
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
