<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$userRoles = (array) ($session->get('roles') ?? []);
$isAdmin = in_array('admin', $userRoles, true);
$isLeader = in_array('leader', $userRoles, true);
$isStaff = in_array('staff', $userRoles, true);
?>

<div class="container-fluid">
    <!-- Role Welcome Banner -->
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
                            ? 'Otorisasi persetujuan brief, negosiasi komersial, validasi kontrak, dan finalisasi tagihan invoice.' 
                            : ($isStaff 
                                ? 'Fokus operasional: monitoring kampanye aktif, review draft video, dan logistik sampel produk.' 
                                : 'Akses penuh seluruh manajemen kampanye, integrasi keuangan, direktori talent, dan audit sistem.') ?>
                    </p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('kols') ?>" class="btn btn-warning text-dark fw-bold rounded-pill px-3 py-2 shadow-sm text-xs">
                    <i class="fas fa-user-astronaut me-1"></i> Direktori KOL
                </a>
                <a href="<?= base_url('drafts') ?>" class="btn btn-light fw-bold rounded-pill px-3 py-2 text-primary shadow-sm text-xs">
                    <i class="fas fa-video me-1"></i> Review Draft (<?= number_format($pending_drafts) ?>)
                </a>
                <a href="<?= base_url('rankings') ?>" class="btn btn-outline-light rounded-pill px-3 py-2 text-xs">
                    <i class="fas fa-trophy text-warning me-1"></i> Leaderboard
                </a>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         BARIS 1: TOP 4 STAT CARDS (HORIZONTAL GRID)
         ═══════════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- 1) Total Profil KOL -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Total Profil KOL</span>
                    <div class="rounded-3 bg-light text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-dark"><?= number_format($total_kols) ?></div>
                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center text-xs">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="fas fa-check-circle me-1"></i> <?= number_format($active_kols) ?> Aktif
                    </span>
                    <a href="<?= base_url('kols') ?>" class="text-primary fw-semibold text-decoration-none">Direktori &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 2) Campaign Aktif -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Campaign Aktif</span>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-dark"><?= number_format($active_campaigns) ?></div>
                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center text-xs text-muted">
                    <span>Anggaran: <strong class="text-dark">Rp <?= number_format($total_budget, 0, ',', '.') ?></strong></span>
                    <a href="<?= base_url('campaigns') ?>" class="text-primary fw-semibold text-decoration-none">Kelola &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 3) Menunggu Persetujuan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Menunggu Persetujuan</span>
                    <div class="rounded-3 bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-dark"><?= number_format($pending_approvals) ?></div>
                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center text-xs text-muted">
                    <span>Brief: <?= $pending_briefs ?> &bull; Draft: <?= $pending_drafts ?></span>
                    <a href="<?= base_url('briefs') ?>" class="text-warning fw-semibold text-decoration-none">Review &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 4) Konten Live / Posted -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Konten Live / Posted</span>
                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-dark"><?= number_format($live_postings) ?></div>
                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center text-xs text-muted">
                    <span>Progress: <strong><?= number_format($live_postings) ?></strong> / <?= number_format($total_tasks) ?> Tasks</span>
                    <span class="text-success fw-bold">
                        (<?= $total_tasks > 0 ? round(($live_postings / $total_tasks) * 100) : 0 ?>%)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         BARIS 2: MAIN GRID (2 KOLOM: KIRI & KANAN)
         ═══════════════════════════════════════════════ -->
    <div class="row g-4">
        
        <!-- KOLOM KIRI (col-lg-8): Ringkasan Keuangan, Jadwal & Campaign Terbaru -->
        <div class="col-lg-8">
            
            <!-- A. Ringkasan Keuangan & Arus Kas -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                        <i class="fas fa-money-bill-wave text-success me-2"></i> Ringkasan Keuangan &amp; Arus Kas
                    </h5>
                    <a href="<?= base_url('finance') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 text-xs">
                        Finance Hub &rarr;
                    </a>
                </div>
                <div class="card-body p-4">
                    <!-- 4 Mini Stats Grid -->
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted text-xs text-uppercase fw-semibold mb-1">Total Deals</div>
                                <div class="fw-bold text-dark fs-6">Rp <?= number_format($total_deals, 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted text-xs text-uppercase fw-semibold mb-1">Ter-Invoice</div>
                                <div class="fw-bold text-dark fs-6">Rp <?= number_format($total_invoiced, 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted text-xs text-uppercase fw-semibold mb-1">Sudah Dibayar</div>
                                <div class="fw-bold text-success fs-6">Rp <?= number_format($total_paid, 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted text-xs text-uppercase fw-semibold mb-1">Sisa Outstanding</div>
                                <div class="fw-bold text-danger fs-6">Rp <?= number_format($total_outstanding, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Clean Cash Progress Bar -->
                    <div class="pt-2 border-top">
                        <div class="d-flex justify-content-between text-xs font-semibold text-muted mb-1">
                            <span>Realisasi Pembayaran Tagihan</span>
                            <span class="text-success fw-bold"><?= $pct_cash_paid ?>% Terbayar</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?= $pct_cash_paid ?>%"></div>
                            <div class="progress-bar bg-danger-subtle" style="width: <?= max(0, 100 - $pct_cash_paid) ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-xs text-muted mt-2">
                            <span><i class="fas fa-circle text-success me-1"></i> Terbayar: <strong>Rp <?= number_format($total_paid, 0, ',', '.') ?></strong></span>
                            <span><i class="fas fa-circle text-danger me-1"></i> Outstanding: <strong class="text-danger">Rp <?= number_format($total_outstanding, 0, ',', '.') ?></strong></span>
                        </div>
                    </div>

                    <!-- Pending Invoices Mini Alert -->
                    <?php if (!empty($pending_invoices)): ?>
                        <div class="mt-3 p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-xs fw-bold text-muted text-uppercase">Tagihan Menunggu Pembayaran</span>
                                <a href="<?= base_url('invoices') ?>" class="text-primary text-xs text-decoration-none fw-semibold">Kelola Invoices &rarr;</a>
                            </div>
                            <?php foreach ($pending_invoices as $inv): ?>
                                <div class="d-flex justify-content-between align-items-center py-1.5 border-top text-xs">
                                    <div>
                                        <div class="fw-bold text-dark"><?= esc($inv['kol_name']) ?></div>
                                        <div class="text-muted text-xs"><?= esc($inv['invoice_number']) ?> &bull; <?= esc($inv['campaign_name']) ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-danger">Rp <?= number_format($inv['grand_total'], 0, ',', '.') ?></div>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-xs">
                                            <?= $inv['days_left'] < 0 ? 'Overdue ' . abs($inv['days_left']) . 'H' : $inv['days_left'] . ' hari lagi' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- B. Tabel Jadwal Posting & Deadline Terdekat -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                        <i class="fas fa-calendar-check text-primary me-2"></i> Jadwal Posting &amp; Deadline Terdekat
                    </h5>
                    <a href="<?= base_url('tasks') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 text-xs">
                        Semua Tasks &rarr;
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Target Tanggal</th>
                                <th>KOL &amp; Campaign</th>
                                <th>Task / Deliverable</th>
                                <th>Platform</th>
                                <th>Status</th>
                                <th>Urgensi</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($upcoming_deadlines)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-success text-xs">
                                        <i class="fas fa-check-circle me-1"></i> Semua jadwal posting on-track, tidak ada tugas yang mendekati batas waktu atau terlambat.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($upcoming_deadlines as $d): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark text-xs text-nowrap">
                                            <?= date('d M Y', strtotime($d['deadline'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-xs"><?= esc($d['kol_name']) ?></div>
                                            <div class="text-muted text-xs"><?= esc($d['campaign_name']) ?></div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-primary text-xs"><?= esc($d['title']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-xs"><?= esc($d['platform_name'] ?: 'TikTok') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-xs">
                                                <?= str_replace('_', ' ', $d['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($d['days_left'] < 0): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xs">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Overdue <?= abs($d['days_left']) ?>H
                                                </span>
                                            <?php elseif ($d['days_left'] == 0): ?>
                                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle text-xs">Hari Ini</span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle text-xs"><?= $d['days_left'] ?> hari lagi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= base_url('tasks') ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs">
                                                Detail &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- C. Campaign Terbaru Table -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                        <i class="fas fa-layer-group text-primary me-2"></i> Campaign Terbaru
                    </h5>
                    <?php if ($isAdmin || $isLeader): ?>
                        <a href="<?= base_url('campaigns') ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-1 text-xs">
                            <i class="fas fa-plus me-1"></i> Buat Campaign Baru
                        </a>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama Campaign</th>
                                <th>Budget Total</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCampaigns)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted text-xs">Belum ada campaign terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentCampaigns as $camp): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark text-xs">
                                            <a href="<?= base_url('campaigns/view/' . $camp['id']) ?>" class="text-dark text-decoration-none">
                                                <?= esc($camp['title']) ?>
                                            </a>
                                        </td>
                                        <td class="fw-bold text-success text-xs">Rp <?= number_format($camp['budget_total'], 0, ',', '.') ?></td>
                                        <td class="text-muted text-xs">
                                            <?= date('d M Y', strtotime($camp['start_date'])) ?> &ndash; <?= date('d M Y', strtotime($camp['end_date'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">
                                                <?= esc($camp['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= base_url('campaigns/view/' . $camp['id']) ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs">
                                                Lihat &rarr;
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

        <!-- KOLOM KANAN (col-lg-4): Production Funnel, Expiring Contracts & Live Feed -->
        <div class="col-lg-4">
            
            <!-- A. Alur Kerja Produksi (Funnel Timeline) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                        <i class="fas fa-cogs text-primary me-2"></i> Alur Produksi Konten
                    </h5>
                    <a href="<?= base_url('tasks') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 text-xs">
                        Lihat Alur &rarr;
                    </a>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        <!-- Step 1 -->
                        <a href="<?= base_url('tasks') ?>" class="text-decoration-none p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between hover-shadow">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
                                <div>
                                    <div class="fw-bold text-dark text-xs">Monitoring Tugas &amp; Brief</div>
                                    <div class="text-muted text-xs">Seluruh penugasan &amp; brief KOL</div>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border text-xs"><?= $prod_monitoring ?> Tasks</span>
                        </a>

                        <!-- Step 2 -->
                        <a href="<?= base_url('drafts') ?>" class="text-decoration-none p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between hover-shadow">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">2</span>
                                <div>
                                    <div class="fw-bold text-dark text-xs">Review Draft Video</div>
                                    <div class="text-muted text-xs">Menunggu &amp; revisi draft</div>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle text-xs"><?= $prod_review_draft ?> Tasks</span>
                        </a>

                        <!-- Step 3 -->
                        <a href="<?= base_url('postings') ?>" class="text-decoration-none p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between hover-shadow">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="badge bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">3</span>
                                <div>
                                    <div class="fw-bold text-dark text-xs">Live Posting &amp; Tayang</div>
                                    <div class="text-muted text-xs">Draft disetujui &amp; metrik tayang</div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle text-xs"><?= $prod_live_posting ?> Tasks</span>
                        </a>

                        <!-- Step 4 -->
                        <a href="<?= base_url('kalender') ?>" class="text-decoration-none p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between hover-shadow">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="badge bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">4</span>
                                <div>
                                    <div class="fw-bold text-dark text-xs">Kalender Jadwal Tayang</div>
                                    <div class="text-muted text-xs">Jadwal distribusi postingan</div>
                                </div>
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle text-xs"><?= $prod_schedule ?> Jadwal</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- B. Kontrak Akan Berakhir / Expired -->
            <?php if (!empty($expiring_contracts)): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                            <i class="fas fa-file-contract text-warning me-2"></i> Kontrak Akan Berakhir
                        </h5>
                        <a href="<?= base_url('contracts') ?>" class="text-primary text-xs text-decoration-none fw-semibold">Semua &rarr;</a>
                    </div>
                    <div class="card-body p-3">
                        <?php foreach ($expiring_contracts as $ec): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-xs">
                                <div>
                                    <div class="fw-bold text-dark"><?= esc($ec['kol_name']) ?></div>
                                    <div class="text-muted text-xs"><?= esc($ec['contract_number']) ?> &bull; <?= esc($ec['campaign_name']) ?></div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-xs">
                                        <?= $ec['days_left'] < 0 ? 'Expired' : $ec['days_left'] . ' hari lagi' ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- C. Konten Live Terbaru (Activity Feed) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                        <i class="fas fa-video text-success me-2"></i> Konten Live Terbaru
                    </h5>
                    <a href="<?= base_url('postings') ?>" class="text-primary text-xs text-decoration-none fw-semibold">Semua Postings &rarr;</a>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($recent_postings)): ?>
                        <div class="text-center py-4 text-muted text-xs">Belum ada konten live posting.</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2.5">
                            <?php foreach ($recent_postings as $p): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-xs">
                                    <div class="overflow-hidden me-2">
                                        <div class="fw-bold text-dark text-truncate"><?= esc($p['kol_name'] ?: 'KOL') ?></div>
                                        <div class="text-muted text-xs text-truncate"><?= esc($p['task_title']) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                        <span class="badge bg-light text-dark border text-xs">
                                            <i class="<?= esc($p['platform_icon']) ?> text-primary me-1"></i>
                                            <?= esc($p['platform_name'] ?: 'Medsos') ?>
                                        </span>
                                        <?php if (!empty($p['live_url'])): ?>
                                            <a href="<?= esc($p['live_url']) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5" title="Buka Konten">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
    // Dashboard page script initialization
});
</script>
<?= $this->endSection() ?>
