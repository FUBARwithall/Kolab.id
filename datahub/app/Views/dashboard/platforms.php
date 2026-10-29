<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
    <div>
      <h5 class="card-title mb-0 fw-bold text-dark fs-6">
        <i class="fas fa-hashtag text-info me-2"></i> <?= esc($title) ?>
      </h5>
      <p class="text-muted text-xs mb-0">Total <?= count($platforms) ?> akun media sosial terdata &amp; tersinkronisasi</p>
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
            <th>Avg Views</th>
            <th>Engagement Rate</th>
            <th>Status Scraping</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($platforms)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">Belum ada akun media sosial tersimpan.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($platforms as $p): ?>
              <tr>
                <td class="ps-3 fw-bold text-dark"><?= esc($p['kol_name'] ?? 'KOL #' . $p['kol_id']) ?></td>
                <td>
                  <?php 
                    $plat = strtolower($p['platform_name'] ?? $p['platform'] ?? '');
                    $badgeBg = 'bg-secondary';
                    $icon = $p['platform_icon'] ?? 'fas fa-globe';
                    if (str_contains($plat, 'tiktok')) $badgeBg = 'bg-dark';
                    elseif (str_contains($plat, 'instagram')) $badgeBg = 'bg-danger';
                    elseif (str_contains($plat, 'youtube')) $badgeBg = 'bg-danger';
                    elseif (str_contains($plat, 'twitter') || str_contains($plat, 'x')) $badgeBg = 'bg-black';
                    elseif (str_contains($plat, 'facebook')) $badgeBg = 'bg-primary';
                  ?>
                  <span class="badge <?= $badgeBg ?> text-uppercase">
                    <i class="<?= esc($icon) ?> me-1"></i> <?= esc($p['platform_name'] ?? $p['platform'] ?? 'Other') ?>
                  </span>
                </td>
                <td class="fw-bold">
                  <?php if (!empty($p['profile_url'])): ?>
                    <a href="<?= esc($p['profile_url']) ?>" target="_blank" class="text-dark text-decoration-none">
                      @<?= esc($p['username']) ?> <i class="fas fa-external-link-alt text-xs text-muted"></i>
                    </a>
                  <?php else: ?>
                    @<?= esc($p['username']) ?>
                  <?php endif; ?>
                </td>
                <td><span class="fw-bold text-success"><?= number_format($p['followers_count'] ?? 0) ?></span></td>
                <td><span class="fw-bold text-primary"><?= number_format($p['avg_views'] ?? 0) ?></span></td>
                <td>
                  <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                    <?= number_format(((float)($p['avg_er'] ?? $p['engagement_rate'] ?? 0)) * 100, 2) ?>%
                  </span>
                </td>
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
