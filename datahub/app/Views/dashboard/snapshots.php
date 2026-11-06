<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
  .platform-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
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
  .filter-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
  }
</style>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Main Container Card -->
<div class="card border-0 shadow-sm" style="border-radius: 10px;">
  <div class="card-body p-4">

    <!-- Card Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 mb-3 border-bottom" style="border-color: #e7edf3 !important;">
      <div>
        <h3 class="mb-1" style="color: #172b4d; font-size: 20px; font-weight: 700;">
          <i class="fas fa-history text-primary me-2"></i>Profile Snapshots
        </h3>
        <div style="color: #6b778c; font-size: 13px;">
          Histori log scraping performa &amp; statistik akun KOL
          <span style="display: inline-block; margin-left: 6px; padding: 2px 8px; border-radius: 10px; background: #e8f1fb; color: #1f4b6e; font-weight: 700; font-size: 12px;">
            <?= number_format($totalCount) ?> items
          </span>
        </div>
      </div>
      <div class="d-flex gap-2 mt-2 mt-md-0">
        <a href="<?= base_url('scraper') ?>" class="btn btn-primary btn-sm px-3 shadow-sm rounded-2">
          <i class="fas fa-cloud-download-alt me-1"></i> Buka Scraping Hub
        </a>
      </div>
    </div>

    <!-- Filter Toolbar -->
    <form method="GET" action="<?= base_url('snapshots') ?>">
      <div class="filter-card">
        <div class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Cari KOL / Username</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control border-start-0" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Nama KOL atau username...">
            </div>
          </div>
          <div class="col-md-2">
            <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Platform</label>
            <select class="form-select form-select-sm" name="platform">
              <option value="">Semua Platform</option>
              <?php foreach ($allPlatforms as $p): ?>
                <option value="<?= esc($p['id']) ?>" <?= ($filters['platform'] == $p['id'] || strtolower($filters['platform']) === strtolower($p['name'])) ? 'selected' : '' ?>>
                  <?= esc($p['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Dari Tanggal</label>
            <input type="date" class="form-control form-control-sm" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Sampai Tanggal</label>
            <input type="date" class="form-control form-control-sm" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
              <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="<?= base_url('snapshots') ?>" class="btn btn-light btn-sm border" title="Reset Filter">
              <i class="fas fa-undo"></i>
            </a>
          </div>
        </div>
      </div>
    </form>

    <!-- Snapshots Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="table-snapshots" style="border: 1px solid #f1f5f9;">
        <thead style="background: #f8fafc; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">
          <tr>
            <th width="40" class="text-center">ID</th>
            <th>KOL</th>
            <th>Platform</th>
            <th>Username</th>
            <th class="text-end">Followers</th>
            <th class="text-end">Avg Views (30)</th>
            <th class="text-end">Avg ER (30)</th>
            <th class="text-center">Posts</th>
            <th>Waktu Capture</th>
            <th class="text-center" width="90">Aksi</th>
          </tr>
        </thead>
        <tbody style="font-size: 13.5px;">
          <?php if (empty($snapshots)): ?>
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">
                <i class="fas fa-database fa-3x d-block mb-3 opacity-25"></i>
                Belum ada histori snapshot data yang tersimpan. Jalankan scraping untuk mengumpulkan histori profil.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($snapshots as $s): ?>
              <?php
                $pCode = strtolower($s['platform_code'] ?? $s['platform_name'] ?? '');
                $pIcon = match(true) {
                  str_contains($pCode, 'tiktok')    => 'fab fa-tiktok',
                  str_contains($pCode, 'instagram') => 'fab fa-instagram',
                  str_contains($pCode, 'youtube')   => 'fab fa-youtube',
                  str_contains($pCode, 'twitter') || $pCode === 'x' => 'fab fa-twitter',
                  str_contains($pCode, 'facebook')  => 'fab fa-facebook',
                  default => 'fas fa-globe',
                };
                $badgeClass = match(true) {
                  str_contains($pCode, 'tiktok')    => 'tiktok',
                  str_contains($pCode, 'instagram') => 'instagram',
                  str_contains($pCode, 'youtube')   => 'youtube',
                  str_contains($pCode, 'twitter') || $pCode === 'x' => 'twitter',
                  str_contains($pCode, 'facebook')  => 'facebook',
                  default => 'bg-secondary',
                };

                // Decode snapshot_data if present to retrieve total_posts or extra details
                $extraData = [];
                if (!empty($s['snapshot_data'])) {
                  $extraData = json_decode($s['snapshot_data'], true) ?? [];
                }
                $totalPosts = $extraData['total_posts'] ?? 0;
              ?>
              <tr>
                <td class="text-center text-muted fw-bold text-xs">#<?= $s['id'] ?></td>
                <td>
                  <div class="fw-bold" style="color: #172b4d;">
                    <?= esc($s['kol_name'] ?? 'KOL #' . $s['kol_id']) ?>
                  </div>
                </td>
                <td>
                  <span class="platform-badge <?= $badgeClass ?>">
                    <i class="<?= $pIcon ?>"></i> <?= esc($s['platform_name'] ?? 'Platform') ?>
                  </span>
                </td>
                <td>
                  <?php if (!empty($s['profile_url'])): ?>
                    <a href="<?= esc($s['profile_url']) ?>" target="_blank" class="text-decoration-none fw-semibold" style="color: #4F46E5;">
                      @<?= esc($s['username'] ?? '-') ?> <i class="fas fa-external-link-alt text-xs ms-1"></i>
                    </a>
                  <?php else: ?>
                    <span class="text-dark">@<?= esc($s['username'] ?? '-') ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-end stat-number">
                  <?= number_format($s['followers'] ?? $s['followers_count'] ?? 0) ?>
                </td>
                <td class="text-end stat-number">
                  <?= number_format($s['avg_views'] ?? $s['average_views_last_30'] ?? 0) ?>
                </td>
                <td class="text-end stat-er">
                  <?= number_format(($s['avg_er'] ?? $s['average_er_last_30'] ?? 0) * 100, 2) ?>%
                </td>
                <td class="text-center fw-semibold text-muted">
                  <?= $totalPosts > 0 ? number_format($totalPosts) : '-' ?>
                </td>
                <td class="text-muted text-xs">
                  <i class="far fa-clock me-1"></i>
                  <?= date('d M Y, H:i', strtotime($s['captured_at'] ?? $s['snapshot_date'] ?? 'now')) ?>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2"
                            onclick='showSnapshotDetail(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                            title="Lihat Detail Snapshot">
                      <i class="fas fa-eye text-xs"></i>
                    </button>
                    <?php if ($isAdmin): ?>
                      <a href="<?= base_url('snapshots/delete/' . $s['id']) ?>"
                         class="btn btn-outline-danger btn-xs py-1 px-2"
                         onclick="return confirm('Apakah Anda yakin ingin menghapus snapshot rekaman ini?')"
                         title="Hapus Snapshot">
                        <i class="fas fa-trash-alt text-xs"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Modal Detail Snapshot -->
<div class="modal fade" id="modalSnapshotDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
      <div class="modal-header border-bottom py-3">
        <div>
          <h5 class="modal-title fw-bold" style="color: #172b4d;" id="modalSnapshotKolName">Detail Snapshot</h5>
          <span class="text-xs text-muted" id="modalSnapshotMeta">Platform Info</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3 mb-4">
          <div class="col-6">
            <div class="p-3 bg-light rounded-3 text-center border">
              <div class="text-xs text-muted text-uppercase fw-bold">Followers</div>
              <div class="fs-4 fw-bold text-dark mt-1" id="modalSnapFollowers">0</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light rounded-3 text-center border">
              <div class="text-xs text-muted text-uppercase fw-bold">Avg Views</div>
              <div class="fs-4 fw-bold text-dark mt-1" id="modalSnapViews">0</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light rounded-3 text-center border">
              <div class="text-xs text-muted text-uppercase fw-bold">Avg ER (Engagement)</div>
              <div class="fs-4 fw-bold text-success mt-1" id="modalSnapER">0%</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light rounded-3 text-center border">
              <div class="text-xs text-muted text-uppercase fw-bold">Waktu Capture</div>
              <div class="fs-6 fw-bold text-muted mt-2" id="modalSnapDate">-</div>
            </div>
          </div>
        </div>

        <div id="modalRawDataSection" style="display:none;">
          <label class="form-label text-xs fw-bold text-muted text-uppercase">Snapshot Data Log (JSON)</label>
          <pre class="bg-dark text-light p-3 rounded-3 text-xs mb-0" id="modalSnapJson" style="max-height: 180px; overflow-y: auto;"></pre>
        </div>
      </div>
      <div class="modal-footer border-top py-2 bg-light rounded-bottom-4">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
function showSnapshotDetail(s) {
  document.getElementById('modalSnapshotKolName').innerText = s.kol_name || ('KOL #' + s.kol_id);
  document.getElementById('modalSnapshotMeta').innerText = (s.platform_name || 'Platform') + (s.username ? ' (@' + s.username + ')' : '');
  document.getElementById('modalSnapFollowers').innerText = Number(s.followers || s.followers_count || 0).toLocaleString();
  document.getElementById('modalSnapViews').innerText = Number(s.avg_views || s.average_views_last_30 || 0).toLocaleString();
  document.getElementById('modalSnapER').innerText = (Number(s.avg_er || s.average_er_last_30 || 0) * 100).toFixed(2) + '%';
  document.getElementById('modalSnapDate').innerText = s.captured_at || s.snapshot_date || '-';

  var rawSec = document.getElementById('modalRawDataSection');
  var pre = document.getElementById('modalSnapJson');
  if (s.snapshot_data) {
    try {
      var parsed = JSON.parse(s.snapshot_data);
      pre.textContent = JSON.stringify(parsed, null, 2);
      rawSec.style.display = 'block';
    } catch(e) {
      pre.textContent = s.snapshot_data;
      rawSec.style.display = 'block';
    }
  } else {
    rawSec.style.display = 'none';
  }

  var modal = new bootstrap.Modal(document.getElementById('modalSnapshotDetail'));
  modal.show();
}
</script>

<?= $this->endSection() ?>

