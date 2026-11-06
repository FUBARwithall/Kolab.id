<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<?php
if (!function_exists('format_metric_number')) {
    function format_metric_number($num) {
        if ($num === null || $num === '') return '0';
        $n = (float)$num;
        if ($n >= 1000000) return round($n / 1000000, 1) . 'M';
        if ($n >= 1000) return round($n / 1000, 1) . 'K';
        return number_format($n);
    }
}
?>

<style>
  .platform-list-card {
    background: #ffffff;
    border: 0;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    overflow: hidden;
    margin-bottom: 24px;
  }
  .platform-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e7edf3;
    flex-wrap: wrap;
  }
  .platform-list-title {
    margin: 0;
    color: #172b4d;
    font-size: 20px;
    font-weight: 700;
  }
  .platform-list-subtitle {
    margin-top: 4px;
    color: #6b778c;
    font-size: 13px;
  }
  .platform-count-badge {
    display: inline-block;
    margin-left: 6px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #e8f1fb;
    color: #1f4b6e;
    font-weight: 700;
    font-size: 12px;
  }
  .platform-table thead tr th {
    background: #f6f8fb;
    color: #52606d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
    border-bottom: 1px solid #e7edf3;
    padding: 12px 14px;
  }
  .platform-table tbody tr td {
    padding: 12px 14px;
    vertical-align: middle;
    font-size: 13.5px;
    border-bottom: 1px solid #f1f5f9;
  }
</style>

<div class="platform-list-card">
  <!-- Header Bar -->
  <div class="platform-list-header">
    <div>
      <h3 class="platform-list-title">Akun Media Sosial &amp; Metrik Platform</h3>
      <div class="platform-list-subtitle">
        Kelola kanal media sosial terhubung, statistik followers, views, dan engagement rate
        <span class="platform-count-badge"><?= count($platforms) ?> Akun Terhubung</span>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('scraper') ?>" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 fw-bold">
        <i class="fas fa-robot me-1"></i> Buka Scraper Hub
      </a>
      <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Cetak / Export
      </button>
    </div>
  </div>

  <!-- Table Body -->
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 platform-table">
      <thead>
        <tr>
          <th style="width: 50px;">ID</th>
          <th>KOL / Pemilik</th>
          <th>Platform</th>
          <th>Username / URL Profil</th>
          <th>Total Followers</th>
          <th>Avg Views (30D)</th>
          <th>Avg ER (%)</th>
          <th>Status Sinkronisasi</th>
          <th class="text-end pe-4">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($platforms)): ?>
          <tr>
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="fas fa-hashtag fa-2x mb-2 d-block opacity-25"></i>
              Belum ada data kanal platform yang terdaftar.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($platforms as $p): ?>
            <tr>
              <td class="fw-bold text-muted">#<?= $p['id'] ?></td>
              <td>
                <div class="fw-bold text-dark"><?= esc($p['kol_name'] ?? 'KOL #' . $p['kol_id']) ?></div>
                <small class="text-muted text-xs">ID KOL: #<?= $p['kol_id'] ?></small>
              </td>
              <td>
                <?php 
                  $plat = strtolower($p['platform_name'] ?? '');
                  $badgeBg = 'bg-secondary';
                  $icon = 'fas fa-globe';
                  if (str_contains($plat, 'tiktok')) { $badgeBg = 'bg-dark'; $icon = 'fab fa-tiktok'; }
                  elseif (str_contains($plat, 'instagram')) { $badgeBg = 'bg-danger'; $icon = 'fab fa-instagram'; }
                  elseif (str_contains($plat, 'youtube')) { $badgeBg = 'bg-danger'; $icon = 'fab fa-youtube'; }
                ?>
                <span class="badge <?= $badgeBg ?> text-uppercase rounded-pill px-2.5 py-1 text-xs">
                  <i class="<?= $icon ?> me-1"></i> <?= esc($p['platform_name'] ?? 'Other') ?>
                </span>
              </td>
              <td>
                <?php
                  $pUrl = $p['profile_url'] ?: 'https://' . $plat . '.com/' . ltrim($p['username'], '@');
                ?>
                <a href="<?= esc($pUrl) ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                  @<?= esc($p['username']) ?> <i class="fas fa-external-link-alt text-xs text-primary ms-1"></i>
                </a>
              </td>
              <td>
                <span class="fw-bold text-success fs-6"><?= format_metric_number($p['followers_count'] ?? 0) ?></span>
              </td>
              <td>
                <span class="fw-bold text-primary"><?= format_metric_number($p['avg_views'] ?? 0) ?></span>
              </td>
              <td>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 text-xs">
                  <?= number_format(((float)($p['avg_er'] ?? 0)) * 100, 2) ?>%
                </span>
              </td>
              <td>
                <?php if (!empty($p['last_scraped_at'])): ?>
                  <span class="badge bg-light text-dark border text-xs">
                    <i class="fas fa-check-circle text-success me-1"></i> <?= date('d M Y', strtotime($p['last_scraped_at'])) ?>
                  </span>
                <?php else: ?>
                  <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 text-xs">
                    Belum di-sync
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-end pe-4">
                <a href="<?= base_url('scraper') ?>" class="btn btn-outline-primary btn-xs rounded-pill px-2.5 py-1 text-xs fw-semibold">
                  <i class="fas fa-sync-alt me-1"></i> Sync
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
