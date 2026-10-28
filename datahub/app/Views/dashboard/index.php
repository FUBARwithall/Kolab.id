<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<?php if ($isKol): ?>
  <!-- ==================== TAMPILAN KHUSUS ROLE: KOL / CREATOR ==================== -->
  <div class="card border-0 shadow-sm rounded-3 mb-4 bg-gradient text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
    <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-3 shadow" style="width: 60px; height: 60px;">
          <i class="fas fa-user-astronaut"></i>
        </div>
        <div>
          <span class="badge bg-warning text-dark mb-1 fw-bold text-uppercase" style="letter-spacing: 0.05em; font-size: 10px;">
            PORTAL INTELIJEN PASAR — MODE CREATOR
          </span>
          <h3 class="fw-bold mb-1 fs-4">Halo, <?= esc(session('name') ?? 'Creator') ?>!</h3>
          <p class="mb-0 text-white-50 text-sm">
            Pantau benchmark rate card industri, bandingkan performa metrik, dan optimalkan nilai endorsement Anda.
          </p>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= base_url('my-profile') ?>" class="btn btn-warning fw-bold rounded-pill px-3 py-2 shadow-sm">
          <i class="fas fa-id-card me-1"></i> Profil Performa Saya
        </a>
        <a href="<?= base_url('rate-cards') ?>" class="btn btn-outline-light rounded-pill px-3 py-2">
          <i class="fas fa-tags me-1"></i> Benchmark Rates
        </a>
      </div>
    </div>
  </div>

  <!-- Creator Benchmark Metrics -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="fas fa-users fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-xs text-uppercase fw-bold">Database Pasar</div>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalKols) ?> KOL</h4>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs">
          <a href="<?= base_url('kols') ?>" class="text-success fw-bold text-decoration-none">Cek Direktori Pasar &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="fas fa-tag fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-xs text-uppercase fw-bold">Rate Cards</div>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalRates) ?> Tipe Paket</h4>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs">
          <a href="<?= base_url('rate-cards') ?>" class="text-info fw-bold text-decoration-none">Lihat Standar Harga &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="fas fa-layer-group fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-xs text-uppercase fw-bold">Tier Influencer</div>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalTiers) ?> Level Tier</h4>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs">
          <a href="<?= base_url('tiers') ?>" class="text-primary fw-bold text-decoration-none">Klasifikasi Followers &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="fas fa-shapes fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-xs text-uppercase fw-bold">Kategori Niche</div>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalCategories) ?> Sektor</h4>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs">
          <a href="<?= base_url('categories') ?>" class="text-warning fw-bold text-decoration-none">Eksplor Niche &rarr;</a>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- ==================== TAMPILAN KHUSUS ROLE: SUPERADMIN ==================== -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <span class="badge bg-danger text-white mb-1"><i class="fas fa-shield-alt me-1"></i> SUPERADMIN DATAHUB</span>
      <h4 class="fw-bold text-dark mb-0">Control Center Database & Scraper</h4>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('scraper') ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-robot me-1"></i> Buka Scraper Apify
      </a>
      <a href="<?= base_url('kols') ?>" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-plus me-1"></i> Tambah Data KOL
      </a>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body d-flex align-items-center p-3">
          <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
            <i class="fas fa-users fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase text-xs fw-bold">Total Database KOL</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalKols) ?></h3>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
          <span><?= number_format($approvedKols) ?> approved</span>
          <a href="<?= base_url('kols') ?>" class="text-success fw-semibold text-decoration-none">Kelola &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body d-flex align-items-center p-3">
          <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
            <i class="fas fa-hashtag fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase text-xs fw-bold">Akun Media Sosial</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalPlatforms) ?></h3>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
          <span>TikTok, IG, YouTube</span>
          <a href="<?= base_url('platforms') ?>" class="text-info fw-semibold text-decoration-none">Lihat &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body d-flex align-items-center p-3">
          <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
            <i class="fas fa-layer-group fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase text-xs fw-bold">Tier Influencer</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalTiers) ?></h3>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
          <span>Nano s/d Mega Tier</span>
          <a href="<?= base_url('tiers') ?>" class="text-primary fw-semibold text-decoration-none">Atur &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body d-flex align-items-center p-3">
          <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
            <i class="fas fa-tags fa-2x"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase text-xs fw-bold">Rate Cards Terdaftar</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalRates) ?></h3>
          </div>
        </div>
        <div class="card-footer bg-light border-0 py-1.5 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
          <span>Paket Konten</span>
          <a href="<?= base_url('rate-cards') ?>" class="text-warning fw-semibold text-decoration-none">Lihat &rarr;</a>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Top KOLs Benchmark / Database Table -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
      <i class="fas fa-users text-success me-2"></i> <?= $isKol ? 'Riset Benchmark KOL di Pasar' : 'Database KOL Terbaru Terdaftar' ?>
    </h5>
    <a href="<?= base_url('kols') ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
      Lihat Semua Data &rarr;
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Nama KOL / Kreator</th>
            <th>Kota</th>
            <th>Kontak / Channel</th>
            <th>Status Profil</th>
            <th class="text-end pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentKols)): ?>
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">
                Belum ada data KOL.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentKols as $k): ?>
              <tr>
                <td class="ps-3">
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light border me-2 d-flex align-items-center justify-content-center text-muted fw-bold" style="width: 38px; height: 38px;">
                      <?= strtoupper(substr($k['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="fw-bold text-dark"><?= esc($k['name']) ?></div>
                      <div class="text-muted text-xs">ID: #<?= $k['id'] ?></div>
                    </div>
                  </div>
                </td>
                <td><?= esc($k['city'] ?? '-') ?></td>
                <td>
                  <?php if ($isAdmin): ?>
                    <span class="badge bg-light text-dark border"><?= esc($k['whatsapp']) ?></span>
                  <?php else: ?>
                    <span class="badge bg-light text-muted border">Privat (Verified)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($k['approval_status'] === 'APPROVED'): ?>
                    <span class="badge bg-success">APPROVED</span>
                  <?php elseif ($k['approval_status'] === 'PENDING'): ?>
                    <span class="badge bg-warning text-dark">PENDING</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><?= esc($k['approval_status']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-end pe-3">
                  <a href="<?= base_url('kols') ?>" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-xs">
                    <i class="fas fa-chart-line me-1"></i> Metrik
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
