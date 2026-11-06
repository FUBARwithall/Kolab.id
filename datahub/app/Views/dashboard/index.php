<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<?php
if (!function_exists('dash_format_short')) {
    function dash_format_short($num) {
        $num = (float)$num;
        if ($num >= 1000000000) return number_format($num / 1000000000, 1) . 'B';
        if ($num >= 1000000) return number_format($num / 1000000, 1) . 'M';
        if ($num >= 1000) return number_format($num / 1000, 1) . 'K';
        return number_format($num);
    }
}
?>

<style>
    /* Stats Grid Styling */
    .dash-stats-grid {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -8px 20px -8px;
    }
    .dash-stats-grid .dash-widget-col {
        flex: 1 1 calc(25% - 16px);
        width: 25%;
        padding: 0 8px;
        margin-bottom: 12px;
    }
    .dash-widget {
        background: #fff;
        border-radius: 12px;
        padding: 20px 14px;
        min-height: 110px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #E2E8F0;
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .dash-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.08);
    }
    .widget-title {
        font-size: 12px;
        color: #94A3B8;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }
    .widget-number {
        font-size: 28px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.1;
    }
    .widget-subtext {
        font-size: 11.5px;
        color: #64748B;
        margin-top: 4px;
        font-weight: 500;
    }
    .text-success-green { color: #059669; }
    .text-danger-red { color: #DC2626; }
    .text-primary-blue { color: #2563EB; }
    .text-purple { color: #7C3AED; }
    .text-amber { color: #D97706; }

    /* Custom Box Panels */
    .dash-box {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .dash-box-header {
        padding: 16px 20px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dash-box-title {
        font-weight: 700;
        font-size: 15px;
        color: #0F172A;
        margin: 0;
        display: flex;
        align-items: center;
    }
    .dash-box-body {
        padding: 18px 20px;
    }

    /* Action item row */
    .action-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-radius: 10px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        margin-bottom: 12px;
        transition: background 0.15s;
    }
    .action-item-row:last-child {
        margin-bottom: 0;
    }
    .action-item-row:hover {
        background: #F1F5F9;
    }

    @media (max-width: 768px) {
        .dash-stats-grid .dash-widget-col {
            flex: 1 1 calc(50% - 8px);
            width: 50%;
            padding: 0 4px;
        }
        .widget-number {
            font-size: 22px;
        }
    }
</style>

<?php if ($isKol): ?>
  <!-- ==================== TAMPILAN KHUSUS ROLE: KOL / CREATOR ==================== -->
  <div class="card border-0 shadow-sm rounded-4 mb-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
    <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-3 shadow" style="width: 58px; height: 58px;">
          <i class="fas fa-user-astronaut"></i>
        </div>
        <div>
          <span class="badge bg-warning text-dark mb-1 fw-bold text-uppercase" style="letter-spacing: 0.05em; font-size: 10px;">
            PORTAL INTELIJEN PASAR — MODE CREATOR
          </span>
          <h3 class="fw-bold mb-1 fs-4">Halo, <?= esc(session('name') ?? 'Creator') ?>!</h3>
          <p class="mb-0 text-white-50 text-sm">
            Pantau performa profil Anda, ikuti kampanye terbuka, dan akses rate card endorsement.
          </p>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= base_url('performance-card/my-card') ?>" class="btn btn-warning fw-bold rounded-pill px-3 py-2 shadow-sm">
          <i class="fas fa-id-card me-1"></i> Performance Card Saya
        </a>
        <a href="<?= base_url('open-campaigns') ?>" class="btn btn-outline-light rounded-pill px-3 py-2">
          <i class="fas fa-bullhorn me-1"></i> Cari Kampanye
        </a>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ==================== ROW 1: KEY PERFORMANCE INDICATORS (4 CARDS) ==================== -->
<div class="dash-stats-grid">
    <div class="dash-widget-col">
        <div class="dash-widget">
            <div class="widget-number"><?= number_format($total_kols) ?></div>
            <div class="widget-title">Active KOLs</div>
            <div class="widget-subtext">Verified Roster</div>
        </div>
    </div>
    <div class="dash-widget-col">
        <div class="dash-widget">
            <div class="widget-number text-primary-blue"><?= dash_format_short($total_reach) ?></div>
            <div class="widget-title">Network Reach</div>
            <div class="widget-subtext">Total Followers</div>
        </div>
    </div>
    <div class="dash-widget-col">
        <div class="dash-widget">
            <div class="widget-number text-success-green"><?= number_format($campaign_stats->active_campaigns ?? 0) ?></div>
            <div class="widget-title">Active Campaigns</div>
            <div class="widget-subtext">IDR <?= dash_format_short($campaign_stats->active_budget ?? 0) ?> budget</div>
        </div>
    </div>
    <div class="dash-widget-col">
        <div class="dash-widget">
            <div class="widget-number text-purple"><?= dash_format_short($content_stats->total_views ?? 0) ?></div>
            <div class="widget-title">Delivered Views</div>
            <div class="widget-subtext"><?= number_format(($content_stats->avg_er ?? 0) * 100, 2) ?>% Avg ER</div>
        </div>
    </div>
</div>

<!-- ==================== SECTION 1: URGENT ACTION CENTER ==================== -->
<div class="row">
    <div class="col-12">
        <div class="dash-box" style="border-top: 3px solid #DC2626;">
            <div class="dash-box-header">
                <h3 class="dash-box-title">
                    <i class="fas fa-bolt text-danger me-2"></i> Urgent Action Center
                </h3>
                <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-semibold text-xs">
                    <?= $action_items['total_actions'] ?> Attention Needed
                </span>
            </div>
            <div class="dash-box-body">
                <!-- Action Item 1: Content Drafts -->
                <div class="action-item-row">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 38px; height: 38px; font-size: 16px;">
                            <i class="fas fa-file-video"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark text-sm">Content Drafts to Review</div>
                            <div class="text-muted text-xs">Submitted content drafts awaiting approval</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-2.5 py-1 <?= $action_items['pending_drafts'] > 0 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $action_items['pending_drafts'] ?>
                        </span>
                        <a href="<?= base_url('my-tasks') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs fw-semibold">
                            Open &rarr;
                        </a>
                    </div>
                </div>

                <!-- Action Item 2: Overdue Posting Deadlines -->
                <div class="action-item-row">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width: 38px; height: 38px; font-size: 16px;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark text-sm">Overdue Posting Deadlines</div>
                            <div class="text-muted text-xs">Tasks past deadline date without live post</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-2.5 py-1 <?= $action_items['overdue_tasks'] > 0 ? 'bg-warning text-dark' : 'bg-success' ?>">
                            <?= $action_items['overdue_tasks'] ?>
                        </span>
                        <a href="<?= base_url('my-tasks') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs fw-semibold">
                            Open &rarr;
                        </a>
                    </div>
                </div>

                <!-- Action Item 3: Briefs Pending Approval -->
                <div class="action-item-row">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-purple" style="width: 38px; height: 38px; font-size: 16px; background: rgba(124, 58, 237, 0.1);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark text-sm">Briefs Pending Approval</div>
                            <div class="text-muted text-xs">Campaign briefs awaiting review / revision</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-2.5 py-1 <?= $action_items['pending_briefs'] > 0 ? 'bg-purple text-white' : 'bg-success' ?>" style="<?= $action_items['pending_briefs'] > 0 ? 'background-color:#7C3AED!important;' : '' ?>">
                            <?= $action_items['pending_briefs'] ?>
                        </span>
                        <a href="<?= base_url('my-tasks') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs fw-semibold">
                            Open &rarr;
                        </a>
                    </div>
                </div>

                <!-- Action Item 4: Seeding Packages -->
                <div class="action-item-row">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 38px; height: 38px; font-size: 16px;">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark text-sm">Seeding Packages Pending</div>
                            <div class="text-muted text-xs">Product packages ready for courier dispatch</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-2.5 py-1 <?= $action_items['pending_shipments'] > 0 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $action_items['pending_shipments'] ?>
                        </span>
                        <a href="<?= base_url('kols') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs fw-semibold">
                            Ship &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== SECTION 2: NETWORK COMPOSITION ==================== -->
<div class="row">
    <div class="col-12">
        <div class="dash-box" style="border-top: 3px solid #10B981;">
            <div class="dash-box-header">
                <h3 class="dash-box-title">
                    <i class="fas fa-chart-pie text-success me-2"></i> Network Composition
                </h3>
            </div>
            <div class="dash-box-body">
                <div class="row align-items-center">
                    <!-- Left: Donut Chart KOLs by Tier -->
                    <div class="col-lg-5 text-center mb-4 mb-lg-0">
                        <h6 class="fw-bold text-dark mb-3 text-sm">KOLs by Tier</h6>
                        <div style="height: 220px; position: relative; max-width: 300px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                            <?php if (empty($kols_by_tier)): ?>
                                <p class="text-muted small">No active KOL tier data</p>
                            <?php else: ?>
                                <canvas id="tierChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Right: Platform Distribution Progress Bars -->
                    <div class="col-lg-7">
                        <h6 class="fw-bold text-dark mb-3 text-sm">Platform Distribution</h6>
                        <div class="pe-lg-3">
                            <?php 
                            $maxPlat = 1;
                            if (!empty($platform_distribution)) {
                                foreach ($platform_distribution as $pd) {
                                    if ($pd->total > $maxPlat) $maxPlat = $pd->total;
                                }
                            }
                            ?>
                            <?php if (empty($platform_distribution)): ?>
                                <p class="text-center text-muted py-3">Belum ada akun media sosial terdaftar.</p>
                            <?php else: ?>
                                <?php foreach ($platform_distribution as $pd): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between text-xs fw-bold mb-1 text-dark">
                                            <span>
                                                <?php
                                                    $pName = strtolower($pd->platform_name);
                                                    $pIcon = match(true) {
                                                        str_contains($pName, 'tiktok') => '<i class="fab fa-tiktok me-1 text-dark"></i>',
                                                        str_contains($pName, 'instagram') => '<i class="fab fa-instagram me-1 text-danger"></i>',
                                                        str_contains($pName, 'youtube') => '<i class="fab fa-youtube me-1 text-danger"></i>',
                                                        default => '<i class="fas fa-globe me-1 text-muted"></i>',
                                                    };
                                                    echo $pIcon . esc($pd->platform_name);
                                                ?>
                                            </span>
                                            <span class="text-muted"><?= number_format($pd->total) ?> accounts</span>
                                        </div>
                                        <div class="progress" style="height: 8px; border-radius: 6px; background: #F1F5F9;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= round(($pd->total / $maxPlat) * 100) ?>%; border-radius: 6px; background: #4F46E5;">
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
</div>

<!-- ==================== SECTION 3: STALE KOL PROFILES (NEEDS SYNC) ==================== -->
<div class="row">
    <div class="col-12">
        <div class="dash-box" style="border-top: 3px solid #F59E0B;">
            <div class="dash-box-header">
                <h3 class="dash-box-title">
                    <i class="fas fa-sync-alt text-warning me-2"></i> Stale KOL Profiles (Needs Sync)
                </h3>
                <a href="<?= base_url('scraper') ?>" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-3 fw-bold">
                    <i class="fas fa-robot me-1"></i> Buka Scraper Hub &rarr;
                </a>
            </div>
            <div class="dash-box-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 text-xs text-uppercase text-muted">KOL</th>
                                <th class="text-xs text-uppercase text-muted">Platform</th>
                                <th class="text-xs text-uppercase text-muted">Followers</th>
                                <th class="text-xs text-uppercase text-muted">Last Synced</th>
                                <th class="text-end pe-3 text-xs text-uppercase text-muted">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stale_kols)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-success fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i> Seluruh data metrik KOL sudah termutakhirkan!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($stale_kols as $stale): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2 text-xs fw-bold" style="width: 30px; height: 30px;">
                                                    <?= strtoupper(substr($stale->kol_name ?? 'K', 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark text-sm"><?= esc($stale->kol_name) ?></div>
                                                    <div class="text-muted text-xs">@<?= esc($stale->username ?? '-') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-xs">
                                                <?= esc($stale->platform_name) ?>
                                            </span>
                                        </td>
                                        <td class="text-sm fw-semibold text-dark">
                                            <?= number_format($stale->followers_count ?? 0) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 text-xs">
                                                <?= $stale->last_scraped_at ? date('d M Y', strtotime($stale->last_scraped_at)) : 'Belum Pernah' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= base_url('scraper') ?>" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                                <i class="fas fa-sync-alt me-1"></i> Sync Sekarang
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
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<!-- Chart.js CDN for Tier Donut Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('tierChart');
    if (ctx) {
        <?php
        $tierLabels = [];
        $tierCounts = [];
        if (!empty($kols_by_tier)) {
            foreach ($kols_by_tier as $t) {
                $tierLabels[] = $t->tier_name;
                $tierCounts[] = (int) $t->total;
            }
        }
        ?>
        const labels = <?= json_encode($tierLabels) ?>;
        const data = <?= json_encode($tierCounts) ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#4F46E5', // Indigo
                        '#06B6D4', // Cyan
                        '#10B981', // Green
                        '#F59E0B', // Amber
                        '#EF4444', // Red
                        '#8B5CF6'  // Purple
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11, family: "'Source Sans Pro', sans-serif" }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
