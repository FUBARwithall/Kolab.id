<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1"><i class="fas fa-robot text-danger me-2"></i>Apify Scraper Engine</h4>
    <p class="text-muted text-sm mb-0">Sinkronisasi metrik KOL otomatis via Apify Scraper Cloud API</p>
  </div>
  <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
    <i class="fas fa-circle me-1" style="font-size:8px"></i> Live Engine
  </span>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Stats Summary -->
<div class="row g-3 mb-4">
  <?php
  $statCards = [
    ['label' => 'Total Jobs',  'key' => 'total',     'icon' => 'fa-list',           'color' => 'secondary'],
    ['label' => 'Queued',      'key' => 'QUEUED',     'icon' => 'fa-clock',          'color' => 'warning'],
    ['label' => 'Running',     'key' => 'RUNNING',    'icon' => 'fa-sync-alt fa-spin','color' => 'primary'],
    ['label' => 'Succeeded',   'key' => 'SUCCEEDED',  'icon' => 'fa-check-circle',   'color' => 'success'],
    ['label' => 'Failed',      'key' => 'FAILED',     'icon' => 'fa-times-circle',   'color' => 'danger'],
  ];
  foreach ($statCards as $sc):
  ?>
  <div class="col-6 col-md">
    <div class="card border-0 shadow-sm rounded-4 text-center py-3">
      <div class="card-body py-2">
        <i class="fas <?= $sc['icon'] ?> text-<?= $sc['color'] ?> mb-2" style="font-size:1.4rem"></i>
        <div class="fw-bold fs-4 text-dark"><?= $summary[$sc['key']] ?? 0 ?></div>
        <div class="text-muted text-xs"><?= $sc['label'] ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <!-- Left: Run Form -->
  <div class="col-lg-4">

    <!-- Run Scraper Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-danger text-white py-3 rounded-top-4">
        <h6 class="card-title mb-0 fw-bold"><i class="fas fa-play-circle me-2"></i>Jalankan Batch Scraper</h6>
      </div>
      <div class="card-body p-4">
        <form method="POST" action="<?= base_url('scraper/run') ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Platform Target</label>
            <select name="platform" class="form-select">
              <option value="all">Semua Platform</option>
              <option value="tiktok">TikTok</option>
              <option value="instagram">Instagram</option>
              <option value="youtube">YouTube</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Tipe Scraping</label>
            <select name="actor_type" class="form-select">
              <option value="profile">Profile Metrics (followers, ER)</option>
              <option value="post">Recent Posts (views, likes)</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Batas Akun (Batch)</label>
            <select name="batch_limit" class="form-select">
              <option value="5">5 Akun (Cepat)</option>
              <option value="10" selected>10 Akun (Rekomendasi)</option>
              <option value="25">25 Akun (Besar)</option>
              <option value="50">50 Akun (Penuh)</option>
            </select>
          </div>
          <button type="submit" class="btn btn-danger w-100 rounded-pill py-2 fw-bold shadow-sm">
            <i class="fas fa-sync-alt me-2"></i>Mulai Scraping Sekarang
          </button>
        </form>
      </div>
    </div>

    <!-- Status Card -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">
        <div class="fw-bold text-dark text-sm mb-3"><i class="fas fa-info-circle text-primary me-1"></i>Status Konektor</div>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom text-xs">
          <span class="text-muted">Apify API Token:</span>
          <span class="badge bg-success">Configured (.env)</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom text-xs">
          <span class="text-muted">Rate Limiting:</span>
          <span class="fw-bold text-dark">60 req / min</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom text-xs">
          <span class="text-muted">Webhook Endpoint:</span>
          <span class="fw-bold text-success text-truncate" style="max-width:120px" title="<?= base_url('scraper/webhook/' . md5(env('KOL_SHARED_SECRET', 'kolab-ci4-shared-secret-key-2026'))) ?>">Aktif</span>
        </div>
        <div class="d-flex justify-content-between align-items-center text-xs">
          <span class="text-muted">Snapshot Tracking:</span>
          <span class="fw-bold text-success">Aktif</span>
        </div>
      </div>
    </div>

  </div>

  <!-- Right: Job History Table -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between rounded-top-4">
        <h6 class="card-title mb-0 fw-bold text-dark">
          <i class="fas fa-history text-secondary me-2"></i>Riwayat Job Scraping (50 Terakhir)
        </h6>
        <span class="badge bg-secondary rounded-pill px-3"><?= count($jobs) ?> Jobs</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="jobsTable">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Job ID</th>
                <th>Platform</th>
                <th>Target</th>
                <th>Tipe</th>
                <th>Status</th>
                <th>Waktu</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($jobs)): ?>
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-robot fa-3x d-block mb-3 opacity-25"></i>
                    Belum ada riwayat antrean scraping. Klik <strong>"Mulai Scraping"</strong> untuk memulai.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($jobs as $j): ?>
                  <?php
                    $statusColor = match($j['status'] ?? '') {
                        'QUEUED'    => 'warning text-dark',
                        'RUNNING'   => 'primary',
                        'SUCCEEDED' => 'success',
                        'FAILED'    => 'danger',
                        default     => 'secondary',
                    };
                    $platformIcon = match($j['platform'] ?? 'other') {
                        'tiktok'    => '<i class="fab fa-tiktok"></i>',
                        'instagram' => '<i class="fab fa-instagram text-danger"></i>',
                        'youtube'   => '<i class="fab fa-youtube text-danger"></i>',
                        default     => '<i class="fas fa-globe text-muted"></i>',
                    };
                  ?>
                  <tr id="job-row-<?= $j['id'] ?>">
                    <td class="ps-3 fw-bold text-muted text-sm">#<?= $j['id'] ?></td>
                    <td><?= $platformIcon ?> <span class="text-xs ms-1"><?= esc(ucfirst($j['platform'] ?? 'other')) ?></span></td>
                    <td class="fw-bold text-dark text-sm">@<?= esc($j['target_username'] ?? '-') ?></td>
                    <td><span class="badge bg-light text-dark border text-xs"><?= esc($j['actor_type'] ?? 'profile') ?></span></td>
                    <td>
                      <span class="badge bg-<?= $statusColor ?> rounded-pill px-2" id="job-status-<?= $j['id'] ?>">
                        <?= $j['status'] === 'RUNNING' ? '<i class="fas fa-circle-notch fa-spin me-1"></i>' : '' ?>
                        <?= esc($j['status'] ?? 'QUEUED') ?>
                      </span>
                    </td>
                    <td class="text-muted text-xs"><?= date('d M H:i', strtotime($j['created_at'] ?? 'now')) ?></td>
                    <td class="text-center">
                      <?php if (in_array($j['status'], ['QUEUED', 'RUNNING'])): ?>
                        <button class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0"
                                onclick="pollJobStatus(<?= $j['id'] ?>)" title="Cek Status">
                          <i class="fas fa-sync-alt text-xs"></i>
                        </button>
                      <?php elseif ($j['status'] === 'SUCCEEDED' && !empty($j['response_data'])): ?>
                        <button class="btn btn-xs btn-outline-success rounded-pill px-2 py-0"
                                onclick="viewResult(<?= $j['id'] ?>, <?= htmlspecialchars(json_encode($j['response_data']), ENT_QUOTES) ?>)"
                                title="Lihat Hasil">
                          <i class="fas fa-eye text-xs"></i>
                        </button>
                      <?php else: ?>
                        <span class="text-muted text-xs">—</span>
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
  </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header">
        <h6 class="modal-title fw-bold"><i class="fas fa-code me-2"></i>Hasil Scraping</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <pre id="resultJson" class="bg-light rounded p-3 text-xs" style="max-height:400px;overflow:auto"></pre>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
// Poll job status via AJAX
function pollJobStatus(jobId) {
  const badge = document.getElementById('job-status-' + jobId);
  if (badge) badge.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i> Checking...';

  fetch('<?= base_url('scraper/job-status') ?>/' + jobId)
    .then(r => r.json())
    .then(data => {
      if (badge) {
        const colorMap = { QUEUED: 'warning text-dark', RUNNING: 'primary', SUCCEEDED: 'success', FAILED: 'danger' };
        const cls = colorMap[data.status] || 'secondary';
        badge.className = 'badge bg-' + cls + ' rounded-pill px-2';
        badge.innerHTML = data.status;
      }
    })
    .catch(() => {
      if (badge) badge.innerHTML = 'ERROR';
    });
}

// View result JSON
function viewResult(jobId, rawJson) {
  let parsed;
  try { parsed = JSON.parse(rawJson); } catch(e) { parsed = rawJson; }
  document.getElementById('resultJson').textContent = JSON.stringify(parsed, null, 2);
  new bootstrap.Modal(document.getElementById('resultModal')).show();
}

// Auto-poll RUNNING jobs every 10s
document.addEventListener('DOMContentLoaded', function() {
  const runningJobs = document.querySelectorAll('[id^="job-status-"]');
  runningJobs.forEach(el => {
    if (el.textContent.trim().includes('RUNNING') || el.textContent.trim().includes('QUEUED')) {
      const jobId = el.id.replace('job-status-', '');
      setInterval(() => pollJobStatus(parseInt(jobId)), 10000);
    }
  });
});
</script>
<?= $this->endSection() ?>

