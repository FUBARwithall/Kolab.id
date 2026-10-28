<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row g-4 mb-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-danger text-white py-3 rounded-top-4">
        <h5 class="card-title mb-0 fw-bold fs-6"><i class="fas fa-play-circle me-2"></i> Jalankan Apify Scraper</h5>
      </div>
      <div class="card-body p-4">
        <p class="text-muted text-sm mb-3">
          Sinkronisasi metrik followers, engagement rate, dan statistik video terbaru langsung via Apify Scraper Cloud API.
        </p>
        <form method="POST" action="<?= base_url('scraper/run') ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Platform Target</label>
            <select name="platform" class="form-select">
              <option value="all">Semua Platform (TikTok & Instagram)</option>
              <option value="tiktok">Khusus TikTok</option>
              <option value="instagram">Khusus Instagram</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Batas Akun (Batch)</label>
            <select name="batch_limit" class="form-select">
              <option value="5">5 Akun (Cepat)</option>
              <option value="10" selected>10 Akun (Rekomendasi)</option>
              <option value="50">50 Akun (Penuh)</option>
            </select>
          </div>
          <button type="submit" class="btn btn-danger w-100 rounded-pill py-2.5 fw-bold shadow-sm">
            <i class="fas fa-sync-alt me-1"></i> Mulai Scraping Sekarang
          </button>
        </form>
      </div>
    </div>

    <!-- Scraper Config Card -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">
        <div class="fw-bold text-dark text-sm mb-2"><i class="fas fa-info-circle text-primary me-1"></i> Status Konektor:</div>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom text-xs">
          <span class="text-muted">Apify API Token:</span>
          <span class="badge bg-success">Configured (.env)</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom text-xs">
          <span class="text-muted">Rate Limiting:</span>
          <span class="fw-bold text-dark">60 req / min</span>
        </div>
        <div class="d-flex justify-content-between align-items-center text-xs">
          <span class="text-muted">Auto-Update Metrics:</span>
          <span class="fw-bold text-success">Aktif (Cron Job)</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-dark fs-6">
          <i class="fas fa-history text-secondary me-2"></i> Riwayat Job Scraping Apify
        </h5>
        <span class="badge bg-secondary rounded-pill px-3 py-1"><?= count($jobs) ?> Jobs Recorded</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Job ID</th>
                <th>Platform</th>
                <th>Target Username</th>
                <th>Status</th>
                <th>Waktu Eksekusi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($jobs)): ?>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-robot fa-3x text-muted mb-3 d-block opacity-25"></i>
                    Belum ada riwayat antrean scraping manual. Data scraper otomatis dijalankan via background worker.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($jobs as $j): ?>
                  <tr>
                    <td class="ps-3 fw-bold text-muted">#<?= $j['id'] ?></td>
                    <td><span class="badge bg-dark"><?= esc($j['platform'] ?? 'tiktok') ?></span></td>
                    <td class="fw-bold text-dark"><?= esc($j['target_username'] ?? '-') ?></td>
                    <td><span class="badge bg-success"><?= esc($j['status'] ?? 'DONE') ?></span></td>
                    <td class="text-muted text-xs"><?= date('d M Y H:i', strtotime($j['created_at'] ?? 'now')) ?></td>
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

<?= $this->endSection() ?>
