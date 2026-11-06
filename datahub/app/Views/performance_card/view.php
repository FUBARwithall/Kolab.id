<?php if (!$isIframe): ?>
<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<?php else: ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    body { background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
  </style>
</head>
<body class="p-3">
<?php endif; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<style>
/* Performance Master Card Styling */
.pmc-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
  overflow: hidden;
}

.pmc-header {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #ffffff;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.pmc-profile {
  display: flex;
  align-items: center;
  gap: 18px;
}

.pmc-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.2);
  background: #334155;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.pmc-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
}

.pmc-meta {
  font-size: 13px;
  color: #94a3b8;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 6px;
}

.pmc-badge-tier {
  background: #3b82f6;
  color: #ffffff;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.pmc-badge-cat {
  background: rgba(255, 255, 255, 0.12);
  color: #e2e8f0;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 6px;
}

.pmc-actions {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.pmc-body {
  padding: 24px;
}

.pmc-section-title {
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 8px;
}

/* Metric Highlights */
.pmc-stat-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 16px;
  text-align: center;
}

.pmc-stat-label {
  font-size: 11.5px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
}

.pmc-stat-val {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  margin-top: 4px;
}

/* Platform Sub-cards */
.pmc-platforms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 30px;
}

.pmc-platform-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.pmc-p-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 10px;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 12px;
}

.pmc-p-name {
  font-weight: 700;
  font-size: 13.5px;
  color: #0f172a;
  display: flex;
  align-items: center;
  gap: 8px;
}

.pmc-p-stat-row {
  display: flex;
  justify-content: space-between;
  font-size: 12.5px;
  margin-bottom: 6px;
}

.pmc-p-stat-row .label-text {
  color: #64748b;
}

.pmc-p-stat-row .value-text {
  font-weight: 700;
  color: #0f172a;
}

/* Rate Card Table Matrix */
.pmc-rate-card-table {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 30px;
}

/* Chart Canvas Wrapper */
.pmc-chart-wrapper {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 20px;
  position: relative;
  min-height: 280px;
  width: 100%;
}

/* Print CSS */
@media print {
  .pmc-actions, .breadcrumb, .main-header, .main-sidebar, .main-footer, .btn, .nav {
    display: none !important;
  }
  body, .content-wrapper, .wrapper {
    background: #ffffff !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .pmc-card {
    border: none !important;
    box-shadow: none !important;
  }
  .pmc-header {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<div class="pmc-card">
  <!-- Card Header -->
  <div class="pmc-header">
    <div class="pmc-profile">
      <img class="pmc-avatar" src="<?= esc($kol['safe_avatar']) ?>" alt="<?= esc($kol['name']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($kol['name']) ?>&background=3b82f6&color=fff'" referrerpolicy="no-referrer">
      <div>
        <h1 class="pmc-title">
          <?= esc($kol['name']) ?>
          <?php if (!empty($kol['tier']['name'])): ?>
            <span class="pmc-badge-tier"><?= esc($kol['tier']['name']) ?> TIER</span>
          <?php endif; ?>
        </h1>
        <div class="pmc-meta">
          <?php if (!empty($kol['city'])): ?>
            <span><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= esc($kol['city']) ?></span>
          <?php endif; ?>
          <?php if (!empty($kol['categories'])): ?>
            <span class="opacity-50">|</span>
            <?php foreach ($kol['categories'] as $cat): ?>
              <span class="pmc-badge-cat"><i class="fas fa-tag me-1"></i><?= esc($cat['name']) ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if (!empty($kol['avg_rating'])): ?>
            <span class="opacity-50">|</span>
            <span class="text-warning"><i class="fas fa-star me-1"></i><?= $kol['avg_rating'] ?> / 5.0</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Actions (Print, Export, Share) -->
    <div class="pmc-actions">
      <?php if (!$isIframe): ?>
        <a href="<?= base_url('kols') ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
          <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
      <?php endif; ?>

      <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Cetak / PDF
      </button>

      <?php if ($isAdmin): ?>
        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="copyShareUrl()">
          <i class="fas fa-share-alt me-1"></i> Share / Embed
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Card Body -->
  <div class="pmc-body">

    <!-- Top Key Metrics -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="pmc-stat-box">
          <div class="pmc-stat-label">Total Followers</div>
          <div class="pmc-stat-val text-primary"><?= number_format((int)$kol['total_followers']) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="pmc-stat-box">
          <div class="pmc-stat-label">Avg. Views (30H)</div>
          <div class="pmc-stat-val text-info"><?= number_format((int)$kol['avg_views']) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="pmc-stat-box">
          <div class="pmc-stat-label">Engagement Rate (ER)</div>
          <div class="pmc-stat-val text-success"><?= $kol['avg_er'] ?>%</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="pmc-stat-box">
          <div class="pmc-stat-label">Channels Aktif</div>
          <div class="pmc-stat-val text-dark"><?= count($kol['platforms'] ?? []) ?></div>
        </div>
      </div>
    </div>

    <!-- Section 1: Social Media Channels Breakdown -->
    <div class="pmc-section-title">
      <i class="fas fa-hashtag text-primary"></i> Rincian Akun Media Sosial &amp; Metrik Kanal
    </div>

    <?php if (!empty($kol['platforms'])): ?>
      <div class="pmc-platforms-grid">
        <?php foreach ($kol['platforms'] as $p): ?>
          <?php
            $pCode = strtolower($p['platform_code'] ?? '');
            $icon = 'fa-globe';
            $iconColor = '#3b82f6';
            if ($pCode === 'instagram') { $icon = 'fa-brands fa-instagram'; $iconColor = '#e1306c'; }
            elseif ($pCode === 'tiktok') { $icon = 'fa-brands fa-tiktok'; $iconColor = '#000000'; }
            elseif ($pCode === 'youtube') { $icon = 'fa-brands fa-youtube'; $iconColor = '#ef4444'; }
          ?>
          <div class="pmc-platform-card">
            <div class="pmc-p-head">
              <span class="pmc-p-name">
                <i class="<?= $icon ?>" style="color: <?= $iconColor ?>; font-size: 17px;"></i>
                <?= esc($p['platform_name']) ?>
              </span>
              <?php if (!empty($p['profile_url'])): ?>
                <a href="<?= esc($p['profile_url']) ?>" target="_blank" class="text-xs text-primary text-decoration-none fw-semibold">
                  @<?= esc($p['username']) ?> <i class="fas fa-external-link-alt text-xs"></i>
                </a>
              <?php else: ?>
                <span class="text-xs text-muted">@<?= esc($p['username']) ?></span>
              <?php endif; ?>
            </div>
            <div class="pmc-p-stat-row">
              <span class="label-text">Followers</span>
              <span class="value-text"><?= number_format((int)($p['followers_count'] ?? $p['followers'] ?? 0)) ?></span>
            </div>
            <div class="pmc-p-stat-row">
              <span class="label-text">Avg Views</span>
              <span class="value-text"><?= number_format((int)($p['avg_views'] ?? 0)) ?></span>
            </div>
            <div class="pmc-p-stat-row">
              <span class="label-text">Engagement Rate</span>
              <span class="value-text text-success"><?= number_format((float)($p['avg_er'] ?? 0), 2) ?>%</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-light border text-muted py-3 text-center mb-4">
        Belum ada akun platform sosial yang ditautkan ke profil ini.
      </div>
    <?php endif; ?>

    <!-- Section 2: Rate Card Layanan -->
    <div class="pmc-section-title">
      <i class="fas fa-tags text-warning"></i> Matriks Rate Card &amp; Penawaran Komersial
    </div>

    <?php if (!empty($kol['rate_cards'])): ?>
      <div class="pmc-rate-card-table table-responsive mb-4">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Platform</th>
              <th>Jenis Konten</th>
              <th>Estimasi Tarif</th>
              <th>Status Negosiasi</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($kol['rate_cards'] as $rc): ?>
              <tr>
                <td class="ps-3 fw-bold text-dark">
                  <i class="fas fa-circle text-xs text-primary me-1"></i> <?= esc($rc['platform_name'] ?? 'Universal') ?>
                </td>
                <td class="fw-semibold text-secondary"><?= esc($rc['content_type']) ?></td>
                <td class="fw-bold text-success fs-6">
                  Rp <?= number_format((float)($rc['rate_price'] ?? $rc['rate'] ?? 0), 0, ',', '.') ?>
                </td>
                <td>
                  <?php if (!empty($rc['negotiable'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Bisa Nego</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Harga Tetap</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted text-xs"><?= esc($rc['notes'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-light border text-muted py-3 text-center mb-4">
        Belum ada rate card yang dipublikasikan oleh kreator ini.
      </div>
    <?php endif; ?>

    <!-- Section 3: Growth Trends -->
    <div class="pmc-section-title">
      <i class="fas fa-chart-line text-info"></i> Tren Pertumbuhan Metrik (6 Bulan Terakhir)
    </div>

    <?php if (!empty($kol['chart']['labels'])): ?>
      <div class="pmc-chart-wrapper mb-3">
        <canvas id="growthChart" height="90"></canvas>
      </div>
    <?php else: ?>
      <div class="text-center text-muted py-4 border rounded-3 bg-light mb-3">
        <i class="fas fa-chart-area fa-2x text-muted mb-2"></i>
        <p class="mb-0 text-xs">Belum ada rekaman histori snapshot performa. Jalankan scraper untuk melacak tren.</p>
      </div>
    <?php endif; ?>

    <!-- Metadata Watermark -->
    <div class="d-flex justify-content-between align-items-center text-muted text-xs border-top pt-3 mt-4">
      <span><i class="fas fa-shield-alt text-success me-1"></i> Terverifikasi oleh <strong>KOLAB.ID Intelligence Hub</strong></span>
      <span>KOL #<?= esc($kol['id']) ?> &bull; Generated: <?= date('d M Y, H:i') ?> WIB</span>
    </div>

  </div>
</div>

<script>
// Copy signed embed URL helper
function copyShareUrl() {
  const currentUrl = window.location.origin + '<?= base_url('performance-card/view/' . $kol['id']) ?>?is_iframe=true';
  navigator.clipboard.writeText(currentUrl).then(() => {
    alert('Link Share / Embed kartu berhasil disalin ke clipboard:\n' + currentUrl);
  });
}

// Chart.js Growth Trend
<?php if (!empty($kol['chart']['labels'])): ?>
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('growthChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= json_encode($kol['chart']['labels']) ?>,
        datasets: [
          {
            label: 'Avg. Views',
            data: <?= json_encode($kol['chart']['views']) ?>,
            borderColor: '#0284c7',
            backgroundColor: 'rgba(2, 132, 199, 0.1)',
            fill: true,
            tension: 0.3,
            yAxisID: 'y',
          },
          {
            label: 'Engagement Rate (%)',
            data: <?= json_encode($kol['chart']['er']) ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderDash: [5, 5],
            fill: false,
            tension: 0.3,
            yAxisID: 'y1',
          }
        ]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: { display: true, text: 'Rata-rata Views' },
            grid: { color: '#f1f5f9' }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: { display: true, text: 'ER (%)' },
            grid: { drawOnChartArea: false }
          }
        }
      }
    });
  }
});
<?php endif; ?>
</script>

<?php if (!$isIframe): ?>
<?= $this->endSection() ?>
<?php else: ?>
</body>
</html>
<?php endif; ?>

