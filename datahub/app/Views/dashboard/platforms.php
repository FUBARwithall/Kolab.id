<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
    <div>
      <h5 class="card-title mb-0 fw-bold text-dark fs-6">
        <i class="fas fa-hashtag text-info me-2"></i> <?= esc($title) ?>
      </h5>
      <p class="text-muted text-xs mb-0">Total <?= count($platforms) ?> akun media sosial terdata & tersinkronisasi</p>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">KOL / Pemilik</th>
            <th>Platform</th>
            <th>Username</th>
            <th>Followers</th>
            <th>Engagement Rate</th>
            <th>Status Scraping</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($platforms)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">Belum ada akun media sosial tersimpan.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($platforms as $p): ?>
              <tr>
                <td class="ps-3 fw-bold text-dark"><?= esc($p['kol_name'] ?? 'KOL #' . $p['kol_id']) ?></td>
                <td>
                  <?php if (strtolower($p['platform'] ?? '') === 'tiktok'): ?>
                    <span class="badge bg-dark"><i class="fab fa-tiktok me-1"></i> TikTok</span>
                  <?php elseif (strtolower($p['platform'] ?? '') === 'instagram'): ?>
                    <span class="badge bg-danger"><i class="fab fa-instagram me-1"></i> Instagram</span>
                  <?php elseif (strtolower($p['platform'] ?? '') === 'youtube'): ?>
                    <span class="badge bg-danger"><i class="fab fa-youtube me-1"></i> YouTube</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?= esc($p['platform'] ?? 'Other') ?></span>
                  <?php endif; ?>
                </td>
                <td class="fw-bold">@<?= esc($p['username']) ?></td>
                <td><span class="fw-bold text-success"><?= number_format($p['followers_count'] ?? 0) ?></span></td>
                <td><span class="badge bg-success bg-opacity-10 text-success fw-bold"><?= number_format($p['engagement_rate'] ?? 0, 2) ?>%</span></td>
                <td><span class="badge bg-light text-muted border">Up to date</span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
