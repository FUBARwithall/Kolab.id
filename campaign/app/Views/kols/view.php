<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Breadcrumb & Header -->
    <div class="row mb-3">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark fs-4 fw-bold">
                <i class="fas fa-id-badge text-primary me-2"></i> Detail Profil KOL
            </h1>
            <p class="text-muted text-xs mb-0">Informasi komprehensif profil talent, channel media sosial, dan rate card komersial</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end text-xs">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('kols') ?>">Direktori KOL</a></li>
                <li class="breadcrumb-item active"><?= esc($kol['name']) ?></li>
            </ol>
        </div>
    </div>

    <?php
    $kolName = $kol['name'];
    $initials = strtoupper(substr($kolName, 0, 1));
    $parts = explode(' ', trim($kolName));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr($parts[1], 0, 1));
    }
    $tierName = $kol['tier']['name'] ?? 'Nano';
    $tierBadgeColors = [
        'Nano'  => 'bg-secondary',
        'Micro' => 'bg-info',
        'Mid'   => 'bg-primary',
        'Macro' => 'bg-warning text-dark',
        'Mega'  => 'bg-danger',
    ];
    $tierColor = $tierBadgeColors[$tierName] ?? 'bg-secondary';
    ?>

    <!-- Compact KOL Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 64px; height: 64px; font-size: 24px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <?= esc($initials) ?>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="fw-bold text-dark fs-4 mb-0"><?= esc($kolName) ?></h3>
                            <span class="badge <?= $tierColor ?> text-uppercase px-2.5 py-1 text-xs">Tier <?= esc($tierName) ?></span>
                            <?php if (!empty($kol['is_blacklisted'])): ?>
                                <span class="badge bg-danger px-2.5 py-1 text-xs"><i class="fas fa-ban me-1"></i> Blacklisted</span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 text-xs"><i class="fas fa-check-circle me-1"></i> Akun Terverifikasi</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted text-xs mt-1">
                            <span><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= esc($kol['city'] ?: 'Indonesia') ?></span>
                            <span>&bull;</span>
                            <span><i class="fab fa-whatsapp text-success me-1"></i> <?= esc($kol['whatsapp'] ?: '-') ?></span>
                            <span>&bull;</span>
                            <span>
                                <?php if (!empty($kol['categories'])): ?>
                                    <i class="fas fa-tags text-primary me-1"></i>
                                    <?= implode(', ', array_map(fn($c) => esc($c['name']), $kol['categories'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">Umum / Lifestyle</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('kols') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="<?= base_url('kols/performance-card/' . $kol['id']) ?>" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                        <i class="fas fa-id-card-alt text-warning me-1"></i> Performance Card
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Top Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Total Reach Audiens</span>
                    <i class="fas fa-users text-primary fs-5"></i>
                </div>
                <div class="fs-4 fw-bold text-dark">
                    <?= number_format($kol['total_followers']) ?>
                </div>
                <small class="text-muted text-xs mt-auto pt-2">
                    Akumulasi <?= count($kol['platforms']) ?> platform aktif
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Rata-rata ER%</span>
                    <i class="fas fa-chart-line text-success fs-5"></i>
                </div>
                <div class="fs-4 fw-bold text-success">
                    <?= number_format($kol['avg_er'] * 100, 2) ?>%
                </div>
                <small class="text-muted text-xs mt-auto pt-2">
                    <?php if ($kol['avg_er'] * 100 >= 5): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">High Engagement</span>
                    <?php elseif ($kol['avg_er'] * 100 >= 2): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-xs">Good Engagement</span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-xs">Standard</span>
                    <?php endif; ?>
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Rata-rata Views</span>
                    <i class="fas fa-play-circle text-danger fs-5"></i>
                </div>
                <div class="fs-4 fw-bold text-dark">
                    <?= number_format($kol['avg_views']) ?>
                </div>
                <small class="text-muted text-xs mt-auto pt-2">
                    Per video postingan media sosial
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-xs fw-bold text-muted">Rating Performa Kampanye</span>
                    <i class="fas fa-star text-warning fs-5"></i>
                </div>
                <div class="fs-4 fw-bold text-warning">
                    <?= $kol['avg_rating'] > 0 ? number_format($kol['avg_rating'], 1) . ' <span class="fs-6 text-muted fw-normal">/ 5.0</span>' : '-' ?>
                </div>
                <small class="text-muted text-xs mt-auto pt-2">
                    <?= count($kol['reviews']) ?> review dari tim operasional
                </small>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs border-0 px-3 pt-2" id="kolTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active fw-semibold text-xs py-3" id="tab-overview-btn" data-bs-toggle="tab" href="#tab-overview" role="tab">
                        <i class="fas fa-user me-1 text-primary"></i> Overview &amp; Profil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-xs py-3" id="tab-platforms-btn" data-bs-toggle="tab" href="#tab-platforms" role="tab">
                        <i class="fas fa-share-alt me-1 text-info"></i> Channel Platform (<?= count($kol['platforms']) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-xs py-3" id="tab-rates-btn" data-bs-toggle="tab" href="#tab-rates" role="tab">
                        <i class="fas fa-tags me-1 text-success"></i> Rate Cards &amp; Komersial (<?= count($kol['rate_cards']) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-xs py-3" id="tab-campaigns-btn" data-bs-toggle="tab" href="#tab-campaigns" role="tab">
                        <i class="fas fa-bullhorn me-1 text-warning"></i> Riwayat Kampanye (<?= count($kol['campaigns']) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-xs py-3" id="tab-reviews-btn" data-bs-toggle="tab" href="#tab-reviews" role="tab">
                        <i class="fas fa-star me-1 text-warning"></i> Review &amp; Evaluasi (<?= count($kol['reviews']) ?>)
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="kolTabsContent">

                <!-- TAB 1: OVERVIEW -->
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-dark text-sm border-bottom pb-2 mb-3">
                                <i class="fas fa-address-card text-primary me-2"></i> Data Identitas &amp; Kontak
                            </h6>
                            <dl class="row text-xs mb-0">
                                <dt class="col-sm-4 text-muted">Nama Lengkap</dt>
                                <dd class="col-sm-8 fw-semibold text-dark"><?= esc($kol['name']) ?></dd>

                                <dt class="col-sm-4 text-muted">Nomor WhatsApp</dt>
                                <dd class="col-sm-8 text-dark">
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $kol['whatsapp']) ?>" target="_blank" class="text-success text-decoration-none fw-semibold">
                                        <i class="fab fa-whatsapp me-1"></i> <?= esc($kol['whatsapp'] ?: '-') ?>
                                    </a>
                                </dd>

                                <dt class="col-sm-4 text-muted">Domisili / Kota</dt>
                                <dd class="col-sm-8 text-dark"><?= esc($kol['city'] ?: '-') ?></dd>

                                <dt class="col-sm-4 text-muted">Alamat Lengkap</dt>
                                <dd class="col-sm-8 text-dark"><?= esc($kol['address'] ?: '-') ?> (<?= esc($kol['postal_code'] ?: '-') ?>)</dd>

                                <dt class="col-sm-4 text-muted">Kategori Konten</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($kol['categories'])): ?>
                                        <?php foreach ($kol['categories'] as $cat): ?>
                                            <span class="badge bg-light text-dark border me-1 text-xs"><?= esc($cat['name']) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </dd>

                                <dt class="col-sm-4 text-muted">Terdaftar Sejak</dt>
                                <dd class="col-sm-8 text-muted"><?= date('d F Y', strtotime($kol['created_at'])) ?></dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark text-sm border-bottom pb-2 mb-3">
                                <i class="fas fa-wallet text-success me-2"></i> Data Finansial &amp; Catatan
                            </h6>
                            <dl class="row text-xs mb-0">
                                <dt class="col-sm-4 text-muted">Nama Bank</dt>
                                <dd class="col-sm-8 fw-semibold text-dark"><?= esc($kol['bank_name'] ?: '-') ?></dd>

                                <dt class="col-sm-4 text-muted">Nomor Rekening</dt>
                                <dd class="col-sm-8 text-dark font-monospace"><?= esc($kol['bank_account'] ?: '-') ?></dd>

                                <dt class="col-sm-4 text-muted">Atas Nama Rekening</dt>
                                <dd class="col-sm-8 text-dark"><?= esc($kol['bank_holder'] ?: '-') ?></dd>

                                <dt class="col-sm-4 text-muted">Nomor NPWP</dt>
                                <dd class="col-sm-8 text-dark font-monospace"><?= esc($kol['npwp'] ?: '-') ?></dd>

                                <dt class="col-sm-4 text-muted">Catatan Khusus</dt>
                                <dd class="col-sm-8 text-muted fst-italic"><?= esc($kol['notes'] ?: 'Tidak ada catatan operasional.') ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PLATFORMS -->
                <div class="tab-pane fade" id="tab-platforms" role="tabpanel">
                    <div class="row g-3">
                        <?php if (empty($kol['platforms'])): ?>
                            <div class="col-12 text-center py-4 text-muted text-xs">Belum ada akun platform media sosial yang terhubung.</div>
                        <?php else: ?>
                            <?php foreach ($kol['platforms'] as $p): ?>
                                <div class="col-md-6">
                                    <div class="card border rounded-3 p-3 shadow-none bg-light-subtle h-100">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="<?= esc($p['platform_icon']) ?> fs-4 text-primary"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark text-sm"><?= esc($p['platform_name']) ?></h6>
                                                    <span class="text-muted text-xs">@<?= esc($p['username']) ?></span>
                                                </div>
                                            </div>
                                            <?php if (!empty($p['profile_url'])): ?>
                                                <a href="<?= esc($p['profile_url']) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs">
                                                    <i class="fas fa-external-link-alt me-1"></i> Buka Profil
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row g-2 text-center pt-1">
                                            <div class="col-4 border-end">
                                                <span class="text-muted text-xs d-block">Followers</span>
                                                <strong class="text-dark fs-6"><?= number_format($p['followers_count']) ?></strong>
                                            </div>
                                            <div class="col-4 border-end">
                                                <span class="text-muted text-xs d-block">Avg. Views</span>
                                                <strong class="text-dark fs-6"><?= number_format($p['avg_views']) ?></strong>
                                            </div>
                                            <div class="col-4">
                                                <span class="text-muted text-xs d-block">Avg. ER</span>
                                                <strong class="text-success fs-6"><?= number_format($p['avg_er'] * 100, 2) ?>%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB 3: RATE CARDS -->
                <div class="tab-pane fade" id="tab-rates" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Platform</th>
                                    <th>Format Konten</th>
                                    <th>Harga Rate Card</th>
                                    <th>Status Nego</th>
                                    <th>Keterangan / Deliverables</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kol['rate_cards'])): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted text-xs">Belum ada rate card yang terdaftar.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($kol['rate_cards'] as $rc): ?>
                                        <tr>
                                            <td>
                                                <i class="<?= esc($rc['platform_icon']) ?> text-primary me-1"></i>
                                                <strong><?= esc($rc['platform_name']) ?></strong>
                                            </td>
                                            <td class="fw-semibold text-dark"><?= esc($rc['content_type']) ?></td>
                                            <td class="fw-bold text-success">
                                                Rp <?= number_format($rc['rate_price'], 0, ',', '.') ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($rc['negotiable'])): ?>
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle text-xs">Bisa Nego</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-xs">Harga Pas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted text-xs"><?= esc($rc['notes'] ?: '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 4: CAMPAIGNS -->
                <div class="tab-pane fade" id="tab-campaigns" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Kampanye</th>
                                    <th>Periode</th>
                                    <th>Target VT</th>
                                    <th>Fee Disepakati</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kol['campaigns'])): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted text-xs">Belum ada riwayat penugasan kampanye untuk KOL ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($kol['campaigns'] as $c): ?>
                                        <tr>
                                            <td class="text-muted fw-bold">#<?= $c['campaign_id'] ?></td>
                                            <td class="fw-bold text-dark"><?= esc($c['campaign_title']) ?></td>
                                            <td class="text-muted text-xs">
                                                <?= date('d M Y', strtotime($c['start_date'])) ?> &ndash; <?= date('d M Y', strtotime($c['end_date'])) ?>
                                            </td>
                                            <td><span class="badge bg-primary rounded-pill"><?= (int)($c['allocated_vt_count'] ?? 1) ?> VT</span></td>
                                            <td class="fw-bold text-success">Rp <?= number_format($c['deal_rate'] ?? 0, 0, ',', '.') ?></td>
                                            <td><span class="badge bg-success-subtle text-success border border-success-subtle"><?= esc($c['status'] ?? 'ACTIVE') ?></span></td>
                                            <td class="text-end">
                                                <a href="<?= base_url('campaigns/view/' . $c['campaign_id']) ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs">
                                                    Lihat Kampanye
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 5: REVIEWS -->
                <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
                    <div class="row g-3">
                        <?php if (empty($kol['reviews'])): ?>
                            <div class="col-12 text-center py-4 text-muted text-xs">Belum ada review performa dari internal tim.</div>
                        <?php else: ?>
                            <?php foreach ($kol['reviews'] as $rev): ?>
                                <div class="col-md-6">
                                    <div class="card border rounded-3 p-3 shadow-none bg-light-subtle">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $rev['rating'] ? 'text-warning' : 'text-muted-light' ?>"></i>
                                                <?php endfor; ?>
                                                <strong class="ms-1 text-dark text-xs"><?= $rev['rating'] ?>.0</strong>
                                            </div>
                                            <small class="text-muted text-xs"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                                        </div>
                                        <p class="text-dark text-xs mb-2"><?= esc($rev['review_text']) ?></p>
                                        <div class="d-flex justify-content-between text-xs text-muted border-top pt-2">
                                            <span>Kampanye: <strong><?= esc($rev['campaign_title'] ?: 'Umum') ?></strong></span>
                                            <span>Oleh: <?= esc($rev['reviewer_name'] ?: 'Staff Operasional') ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
    // Tabs behavior
    $('#kolTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
});
</script>
<?= $this->endSection() ?>
