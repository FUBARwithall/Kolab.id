<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$userRoles = (array) ($session->get('roles') ?? []);
$isAdmin = in_array('admin', $userRoles, true);
$isLeader = in_array('leader', $userRoles, true);
$isStaff = in_array('staff', $userRoles, true);
?>

<!-- Role Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-gradient text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);">
  <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold fs-3 shadow" style="width: 56px; height: 56px;">
        <i class="<?= $isLeader ? 'fas fa-user-tie' : ($isAdmin ? 'fas fa-user-shield' : 'fas fa-user-cog') ?>"></i>
      </div>
      <div>
        <span class="badge bg-light text-primary mb-1 fw-bold text-uppercase" style="letter-spacing: 0.05em; font-size: 10px;">
          <?= $isAdmin ? 'SUPERADMIN CAMPAIGN' : ($isLeader ? 'LEADER KOL ROLE' : 'STAFF OPERATIONAL ROLE') ?>
        </span>
        <h3 class="fw-bold mb-0 fs-4">Selamat Datang, <?= esc(session('name') ?? 'User') ?>!</h3>
        <p class="mb-0 text-white-50 text-sm">
          <?= $isLeader 
              ? 'Anda memegang otorisasi approval brief, negosiasi rate card, validasi kontrak dan finalisasi invoice.' 
              : ($isStaff 
                  ? 'Fokus operasional: monitoring kampanye aktif, review draft video, dan logistik sampel produk.' 
                  : 'Akses penuh seluruh operasional campaign, keuangan, dan developer tools.') ?>
        </p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('drafts') ?>" class="btn btn-warning fw-bold rounded-pill px-3 py-2 shadow-sm">
        <i class="fas fa-video me-1"></i> Review Draft Video (<?= number_format($pendingDrafts) ?>)
      </a>
      <?php if ($isAdmin || $isLeader): ?>
        <a href="<?= base_url('invoices') ?>" class="btn btn-outline-light rounded-pill px-3 py-2">
          <i class="fas fa-receipt me-1"></i> Invoices
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="fas fa-bullhorn fa-2x"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase text-xs fw-bold">Total Campaign</div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalCampaigns) ?></h3>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-1 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
                <span><?= number_format($activeCampaigns) ?> aktif berjalan</span>
                <a href="<?= base_url('campaigns') ?>" class="text-primary fw-semibold text-decoration-none">Kelola &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="fas fa-file-alt fa-2x"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase text-xs fw-bold">Briefs & Guidelines</div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalBriefs) ?></h3>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-1 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
                <span>Panduan konten</span>
                <a href="<?= base_url('briefs') ?>" class="text-info fw-semibold text-decoration-none">Lihat &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="fas fa-video fa-2x"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase text-xs fw-bold">Review Draft</div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($pendingDrafts) ?></h3>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-1 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
                <span>Menunggu review</span>
                <a href="<?= base_url('drafts') ?>" class="text-warning fw-semibold text-decoration-none">Review &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="fas fa-receipt fa-2x"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase text-xs fw-bold">Invoices & Finance</div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalInvoices) ?></h3>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-1 px-3 text-xs text-muted d-flex justify-content-between align-items-center">
                <span>Pembayaran kreator</span>
                <?php if ($isAdmin || $isLeader): ?>
                  <a href="<?= base_url('invoices') ?>" class="text-success fw-semibold text-decoration-none">Buka &rarr;</a>
                <?php else: ?>
                  <span class="badge bg-light text-muted border">Leader Otorisasi</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-dark">
            <i class="fas fa-clock text-primary me-2"></i> Campaign Terbaru
        </h5>
        <?php if ($isAdmin || $isLeader): ?>
          <a href="<?= base_url('campaigns') ?>" class="btn btn-sm btn-primary rounded-pill px-3">
              <i class="fas fa-plus me-1"></i> Buat Campaign Baru
          </a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Campaign</th>
                        <th>Brand</th>
                        <th>Budget Total</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentCampaigns)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-2 d-block text-secondary opacity-50"></i>
                                Belum ada campaign terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentCampaigns as $c): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark"><?= esc($c['title'] ?? '') ?></td>
                                <td><span class="badge bg-light text-dark border">Campaign</span></td>
                                <td class="fw-semibold">Rp <?= number_format((float) ($c['budget_total'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-muted text-xs"><?= esc($c['start_date']) ?> s/d <?= esc($c['end_date']) ?></td>
                                <td>
                                    <?php if ($c['status'] === 'active'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php elseif ($c['status'] === 'completed'): ?>
                                        <span class="badge bg-secondary">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?= esc($c['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= base_url('campaigns') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 text-xs">
                                        Detail
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
