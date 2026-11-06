<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
  .platform-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
  }
  .platform-badge.tiktok { background: #000; color: #00f2ea; border: 1px solid #1e293b; }
  .platform-badge.instagram { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: #fff; }
  .platform-badge.youtube { background: #FF0000; color: #fff; }
  .platform-badge.twitter, .platform-badge.x { background: #0f1419; color: #fff; }
  .platform-badge.facebook { background: #1877F2; color: #fff; }

  .stat-number {
    font-weight: 700;
    font-size: 13.5px;
    color: #0f172a;
  }
  .stat-er {
    font-weight: 700;
    font-size: 13.5px;
    color: #059669;
  }
  .filter-row {
    display: flex;
    gap: 12px;
    align-items: flex-end;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
  }
  .filter-row .form-group {
    margin-bottom: 0;
    min-width: 160px;
  }
  .filter-row label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 4px;
    display: block;
  }
  .btn-bulk-scrape {
    background: linear-gradient(135deg, #4F46E5, #7C3AED) !important;
    color: #fff !important;
    border: none !important;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    transition: all 0.2s ease;
  }
  .btn-bulk-scrape:hover {
    background: linear-gradient(135deg, #4338CA, #6D28D9) !important;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
  }
  .nav-tabs-clean .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #64748b;
    font-weight: 600;
    padding: 10px 16px;
  }
  .nav-tabs-clean .nav-link.active {
    color: #4F46E5;
    border-bottom: 2px solid #4F46E5;
    background: transparent;
  }
</style>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Main Scraping Hub Card -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
  <div class="card-body p-4">

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 mb-3 border-bottom" style="border-color: #e7edf3 !important;">
      <div>
        <h3 class="mb-1" style="color: #172b4d; font-size: 22px; font-weight: 700;">
          <i class="fas fa-cloud-download-alt text-primary me-2"></i>Scraping Hub
        </h3>
        <p class="text-muted mb-0" style="font-size: 13.5px;">
          Active Platforms per KOL &bull; 
          <span style="display: inline-block; margin-left: 4px; padding: 2px 8px; border-radius: 10px; background: #fef3c7; color: #92400e; font-weight: 700; font-size: 12px;">
            <?= number_format($total) ?> platforms
          </span>
        </p>
      </div>
      <div class="d-flex gap-2 mt-2 mt-md-0">
        <button type="button" class="btn btn-bulk-scrape btn-sm px-3 py-2 rounded-2" id="btn_bulk_scrape">
          <i class="fas fa-sync-alt me-1"></i> Bulk Scrape
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-2" data-bs-toggle="collapse" data-bs-target="#apifyEnginePanel">
          <i class="fas fa-robot me-1"></i> Apify Cloud Batch
        </button>
      </div>
    </div>

    <!-- Collapsible Apify Engine Box -->
    <div class="collapse mb-4" id="apifyEnginePanel">
      <div class="p-3 bg-light rounded-3 border">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold text-dark mb-0"><i class="fas fa-cogs text-danger me-2"></i>Apify Cloud Scraper API Runner</h6>
          <span class="badge bg-danger">Engine Live</span>
        </div>
        <form method="POST" action="<?= base_url('scraper/run') ?>" class="row g-2 align-items-end">
          <?= csrf_field() ?>
          <div class="col-md-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Platform Target</label>
            <select name="platform" class="form-select form-select-sm">
              <option value="all">Semua Platform</option>
              <option value="tiktok">TikTok</option>
              <option value="instagram">Instagram</option>
              <option value="youtube">YouTube</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Tipe Scraping</label>
            <select name="actor_type" class="form-select form-select-sm">
              <option value="profile">Profile Metrics (followers, ER)</option>
              <option value="post">Recent Posts (views, likes)</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Batch Limit</label>
            <select name="batch_limit" class="form-select form-select-sm">
              <option value="5">5 Akun</option>
              <option value="10" selected>10 Akun</option>
              <option value="25">25 Akun</option>
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">
              <i class="fas fa-play me-1"></i> Enqueue Cloud Job
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Filters Row -->
    <form method="GET" action="<?= base_url('scraper') ?>">
      <div class="filter-row">
        <div class="form-group flex-grow-1">
          <label>KOL Name / Username</label>
          <input type="text" class="form-control form-control-sm" name="q" value="<?= esc($filter['kol_name'] ?? '') ?>" placeholder="Search...">
        </div>
        <div class="form-group">
          <label>Platform</label>
          <select class="form-select form-select-sm" name="platform">
            <option value="">All Platforms</option>
            <?php foreach ($allPlatforms as $p): ?>
              <?php
                $pId = (string)$p['id'];
                $pName = $p['name'];
                $selected = ($filter['platform_code'] == $pId || strtolower($filter['platform_code']) === strtolower($pName)) ? 'selected' : '';
              ?>
              <option value="<?= esc($pId) ?>" <?= $selected ?>><?= esc($pName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>From</label>
          <input type="date" class="form-control form-control-sm" name="date_from" value="<?= esc($filter['date_from'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>To</label>
          <input type="date" class="form-control form-control-sm" name="date_to" value="<?= esc($filter['date_to'] ?? '') ?>">
        </div>
        <div class="form-group d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-search me-1"></i> Filter
          </button>
          <a href="<?= base_url('scraper') ?>" class="btn btn-light btn-sm border" title="Reset Filter">
            <i class="fas fa-times"></i> Reset
          </a>
        </div>
      </div>
    </form>

    <!-- Snapshot / Active Platforms Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="table-scraping-monitor" style="border: 1px solid #f1f5f9;">
        <thead style="background: #f8fafc; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">
          <tr>
            <th width="30" class="text-center">
              <input type="checkbox" class="form-check-input" id="check_all" title="Select All">
            </th>
            <th>KOL Name</th>
            <th>Platform</th>
            <th>Username / Link</th>
            <th class="text-end">Followers</th>
            <th class="text-end">Avg Views (30)</th>
            <th class="text-end">Avg ER (30)</th>
            <th class="text-center">Posts Analyzed</th>
            <th>Last Synced At</th>
            <th class="text-center" width="90">Action</th>
          </tr>
        </thead>
        <tbody style="font-size: 13.5px;">
          <?php if (empty($snapshots)): ?>
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">
                <i class="fas fa-database fa-3x d-block mb-3 opacity-25"></i>
                No platform profiles found matching the filter.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($snapshots as $s): ?>
              <?php
                $codeLower = strtolower($s['platform_code'] ?? $s['platform_name'] ?? '');
                $iconClass = match(true) {
                  str_contains($codeLower, 'tiktok')    => 'fab fa-tiktok',
                  str_contains($codeLower, 'instagram') => 'fab fa-instagram',
                  str_contains($codeLower, 'youtube')   => 'fab fa-youtube',
                  str_contains($codeLower, 'twitter') || $codeLower === 'x' => 'fab fa-twitter',
                  str_contains($codeLower, 'facebook')  => 'fab fa-facebook',
                  default => 'fas fa-globe',
                };
                $badgeClass = match(true) {
                  str_contains($codeLower, 'tiktok')    => 'tiktok',
                  str_contains($codeLower, 'instagram') => 'instagram',
                  str_contains($codeLower, 'youtube')   => 'youtube',
                  str_contains($codeLower, 'twitter') || $codeLower === 'x' => 'twitter',
                  str_contains($codeLower, 'facebook')  => 'facebook',
                  default => 'bg-secondary',
                };
              ?>
              <tr id="row-platform-<?= $s['id'] ?>">
                <td class="text-center">
                  <input type="checkbox" class="form-check-input check-platform-item" name="kol_platform_id[]" value="<?= $s['id'] ?>">
                </td>
                <td>
                  <a href="<?= base_url('kols?q=' . urlencode($s['kol_name'] ?? '')) ?>" class="text-decoration-none fw-bold" style="color: #4F46E5;">
                    <?= esc($s['kol_name'] ?? 'KOL #' . $s['kol_id']) ?>
                  </a>
                </td>
                <td>
                  <span class="platform-badge <?= $badgeClass ?>">
                    <i class="<?= $iconClass ?>"></i> <?= esc($s['platform_name'] ?? 'Platform') ?>
                  </span>
                </td>
                <td>
                  <?php if (!empty($s['profile_url'])): ?>
                    <a href="<?= esc($s['profile_url']) ?>" target="_blank" class="text-decoration-none" style="color: #2563eb;">
                      @<?= esc($s['username'] ?: $s['profile_url']) ?> <i class="fas fa-external-link-alt text-xs ms-1"></i>
                    </a>
                  <?php else: ?>
                    <span class="text-dark">@<?= esc($s['username'] ?? '-') ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-end stat-number">
                  <?= number_format($s['followers_count'] ?? 0) ?>
                </td>
                <td class="text-end stat-number">
                  <?= number_format($s['avg_views'] ?? 0) ?>
                </td>
                <td class="text-end stat-er">
                  <?= number_format(($s['avg_er'] ?? 0) * 100, 2) ?>%
                </td>
                <td class="text-center fw-semibold text-muted">
                  <?= !empty($s['total_posts']) ? number_format($s['total_posts']) : '1' ?>
                </td>
                <td class="text-xs sync-col">
                  <?php if (!empty($s['last_scraped_at'])): ?>
                    <span class="text-muted"><i class="far fa-clock me-1"></i><?= date('d M Y, H:i', strtotime($s['last_scraped_at'])) ?></span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Never</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-outline-primary btn-xs py-1 px-2 single-scrape-btn"
                          data-id="<?= $s['id'] ?>"
                          title="Scrape platform ini sekarang">
                    <i class="fas fa-sync-alt text-xs"></i> Scrape
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Apify Recent Jobs Section -->
<div class="card border-0 shadow-sm" style="border-radius: 10px;">
  <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between rounded-top-4">
    <div>
      <h6 class="mb-0 fw-bold text-dark">
        <i class="fas fa-tasks text-secondary me-2"></i>Apify Scraper Engine Queue &bull; Riwayat Background Jobs
      </h6>
    </div>
    <div class="d-flex gap-2">
      <span class="badge bg-secondary"><?= count($jobs) ?> Jobs</span>
      <span class="badge bg-success"><?= $summary['SUCCEEDED'] ?? 0 ?> Succeeded</span>
      <span class="badge bg-warning text-dark"><?= $summary['QUEUED'] ?? 0 ?> Queued</span>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Job ID</th>
            <th>Platform</th>
            <th>Target</th>
            <th>Tipe</th>
            <th>Status</th>
            <th>Waktu Dibuat</th>
            <th class="text-center">Status Worker</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($jobs)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                Belum ada antrean job Apify saat ini.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach (array_slice($jobs, 0, 10) as $j): ?>
              <?php
                $statusColor = match($j['status'] ?? '') {
                  'QUEUED'    => 'warning text-dark',
                  'RUNNING'   => 'primary',
                  'SUCCEEDED' => 'success',
                  'FAILED'    => 'danger',
                  default     => 'secondary',
                };
              ?>
              <tr>
                <td class="ps-3 fw-bold text-muted">#<?= $j['id'] ?></td>
                <td><span class="fw-semibold"><?= esc(ucfirst($j['platform'] ?? 'Web')) ?></span></td>
                <td class="fw-bold text-dark">@<?= esc($j['target_username'] ?? '-') ?></td>
                <td><span class="badge bg-light text-dark border"><?= esc($j['actor_type'] ?? 'profile') ?></span></td>
                <td>
                  <span class="badge bg-<?= $statusColor ?> rounded-pill px-2">
                    <?= $j['status'] === 'RUNNING' ? '<i class="fas fa-spinner fa-spin me-1"></i>' : '' ?>
                    <?= esc($j['status'] ?? 'QUEUED') ?>
                  </span>
                </td>
                <td class="text-muted text-xs"><?= date('d M Y, H:i', strtotime($j['created_at'] ?? 'now')) ?></td>
                <td class="text-center text-xs">
                  <?php if ($j['status'] === 'SUCCEEDED'): ?>
                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>Data Synced</span>
                  <?php elseif ($j['status'] === 'RUNNING'): ?>
                    <span class="text-primary"><i class="fas fa-sync-alt fa-spin me-1"></i>Processing</span>
                  <?php else: ?>
                    <span class="text-muted"><i class="far fa-clock me-1"></i>In Queue</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const checkAll = document.getElementById('check_all');
  const checkItems = document.querySelectorAll('.check-platform-item');
  const btnBulkScrape = document.getElementById('btn_bulk_scrape');

  // Toggle All Checkboxes
  if (checkAll) {
    checkAll.addEventListener('change', function() {
      checkItems.forEach(item => {
        item.checked = checkAll.checked;
      });
    });
  }

  // Handle Bulk Scrape
  if (btnBulkScrape) {
    btnBulkScrape.addEventListener('click', function() {
      const selected = Array.from(document.querySelectorAll('.check-platform-item:checked')).map(cb => cb.value);
      if (selected.length === 0) {
        alert('Silakan pilih minimal satu akun platform KOL yang ingin di-scrape.');
        return;
      }

      if (!confirm('Jalankan background scraping untuk ' + selected.length + ' platform terpilih?')) {
        return;
      }

      btnBulkScrape.disabled = true;
      btnBulkScrape.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Scraping...';

      const formData = new FormData();
      selected.forEach(id => formData.append('kol_platform_ids[]', id));
      formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

      fetch('<?= base_url('scraper/bulk') ?>', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btnBulkScrape.disabled = false;
        btnBulkScrape.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Bulk Scrape';
        if (data.success) {
          alert(data.message || 'Scraping berhasil dipicu!');
          window.location.reload();
        } else {
          alert(data.message || 'Terjadi kesalahan saat memproses scraping.');
        }
      })
      .catch(err => {
        btnBulkScrape.disabled = false;
        btnBulkScrape.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Bulk Scrape';
        alert('Gagal menghubungi server.');
      });
    });
  }

  // Handle Single Scrape
  document.querySelectorAll('.single-scrape-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      const origHtml = this.innerHTML;
      this.disabled = true;
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

      const formData = new FormData();
      formData.append('kol_platform_ids[]', id);
      formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

      fetch('<?= base_url('scraper/bulk') ?>', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        if (data.success) {
          alert('Scraping untuk akun ini berhasil dijalankan!');
          window.location.reload();
        } else {
          alert(data.message || 'Gagal memproses scraping.');
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Gagal menghubungi server.');
      });
    });
  });
});
</script>

<?= $this->endSection() ?>
