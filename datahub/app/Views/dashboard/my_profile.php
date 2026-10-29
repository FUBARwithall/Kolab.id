<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row g-4 mb-4">
  <!-- Profile Card -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 text-center overflow-hidden">
      <div class="bg-gradient p-4 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <div class="rounded-circle bg-white text-success d-inline-flex align-items-center justify-content-center shadow mb-3 fw-bold" style="width: 80px; height: 80px; font-size: 32px;">
          <?= strtoupper(substr($kol['name'] ?? 'K', 0, 1)) ?>
        </div>
        <h4 class="fw-bold mb-0 text-white"><?= esc($kol['name'] ?? 'Nama Kreator') ?></h4>
        <span class="badge bg-white text-success mt-1 fw-bold text-uppercase px-3 py-1">
          <i class="fas fa-check-circle me-1"></i> <?= esc($kol['approval_status'] ?? 'APPROVED') ?>
        </span>
      </div>
      <div class="card-body p-4 text-start">
        <div class="mb-3 pb-3 border-bottom">
          <div class="text-muted text-xs text-uppercase fw-bold mb-1">Domisili</div>
          <div class="fw-bold text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= esc($kol['city'] ?? 'Jakarta Selatan') ?></div>
        </div>
        <div class="mb-3 pb-3 border-bottom">
          <div class="text-muted text-xs text-uppercase fw-bold mb-1">WhatsApp Resmi</div>
          <div class="fw-bold text-dark"><i class="fab fa-whatsapp text-success me-1"></i> <?= esc($kol['whatsapp'] ?? '-') ?></div>
        </div>
        <div class="mb-3">
          <div class="text-muted text-xs text-uppercase fw-bold mb-1">Rekening Pembayaran</div>
          <div class="fw-bold text-dark"><i class="fas fa-university text-primary me-1"></i> <?= esc($kol['bank_name'] ?? 'BCA') ?> — <?= esc($kol['bank_account'] ?? '••••••••') ?></div>
          <div class="text-muted text-xs">a.n <?= esc($kol['bank_holder'] ?? $kol['name'] ?? '-') ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Connected Platforms & Metrics -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-dark fs-6">
          <i class="fas fa-share-alt text-info me-2"></i> Akun Media Sosial &amp; Metrik
        </h5>
        <span class="badge bg-info text-white rounded-pill px-3 py-1"><?= count($platforms) ?> Channels</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Platform</th>
                <th>Username</th>
                <th>Followers / Subscribers</th>
                <th>Avg Views</th>
                <th>Avg ER</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($platforms)): ?>
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">Belum ada akun medsos terhubung.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($platforms as $p): ?>
                  <tr>
                    <td class="ps-3">
                      <span class="badge bg-dark text-uppercase px-2.5 py-1">
                        <i class="<?= esc($p['platform_icon'] ?? 'fas fa-globe') ?> me-1"></i>
                        <?= esc($p['platform_name'] ?? $p['platform'] ?? 'tiktok') ?>
                      </span>
                    </td>
                    <td class="fw-bold text-dark">
                      <?php if (!empty($p['profile_url'])): ?>
                        <a href="<?= esc($p['profile_url']) ?>" target="_blank" class="text-decoration-none text-dark">
                          @<?= esc($p['username']) ?> <i class="fas fa-external-link-alt text-xs text-muted"></i>
                        </a>
                      <?php else: ?>
                        @<?= esc($p['username']) ?>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="fw-bold text-success"><?= number_format($p['followers_count'] ?? 0) ?></span>
                    </td>
                    <td>
                      <span class="fw-bold text-primary"><?= number_format($p['avg_views'] ?? 0) ?></span>
                    </td>
                    <td>
                      <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">
                        <?= number_format(((float)($p['avg_er'] ?? $p['engagement_rate'] ?? 0)) * 100, 2) ?>%
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Rate Card Saya -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-dark fs-6">
          <i class="fas fa-tags text-warning me-2"></i> Rate Card &amp; Paket Konten
        </h5>
        <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><?= count($rates) ?> Paket Terdaftar</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Paket Layanan</th>
                <th>Platform</th>
                <th>Harga Normal</th>
                <th>Catatan &amp; Term</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rates)): ?>
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">Belum ada rate card khusus. Silakan tambahkan tarif penawaran.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($rates as $r): ?>
                  <tr>
                    <td class="ps-3 fw-bold text-dark"><?= esc($r['content_type'] ?? $r['service_name'] ?? 'Video Endorsement') ?></td>
                    <td><span class="badge bg-secondary"><?= esc($r['platform_name'] ?? $r['platform'] ?? 'Semua') ?></span></td>
                    <td class="fw-bold text-success">Rp <?= number_format((float)($r['rate_price'] ?? $r['rate_amount'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-muted text-xs"><?= esc($r['notes'] ?? '-') ?></td>
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
