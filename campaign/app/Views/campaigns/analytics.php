<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-7">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-primary text-uppercase px-2 py-1" style="font-size: 11px;">KAMPANYE ID #<?= $campaign['id'] ?></span>
          <?php
            $statusBadge = match (strtoupper($campaign['status'])) {
                'ACTIVE'    => '<span class="badge bg-success px-2 py-1"><i class="fas fa-play me-1"></i>AKTIF</span>',
                'COMPLETED' => '<span class="badge bg-secondary px-2 py-1"><i class="fas fa-check me-1"></i>SELESAI</span>',
                'CANCELLED' => '<span class="badge bg-danger px-2 py-1"><i class="fas fa-ban me-1"></i>DIBATALKAN</span>',
                default     => '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-edit me-1"></i>DRAFT</span>',
            };
            echo $statusBadge;
          ?>
        </div>
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-chart-line text-success me-2"></i>Analitik Konsolidasi: <?= esc($campaign['title']) ?>
        </h3>
        <p class="text-muted small mb-0">
          <i class="far fa-calendar-alt text-primary me-1"></i> Periode: <b><?= date('d M Y', strtotime($campaign['start_date'])) ?></b> s/d <b><?= date('d M Y', strtotime($campaign['end_date'])) ?></b>
        </p>
      </div>
      <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('campaigns/view/' . $campaign['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Detail Kampanye
        </a>
        <a href="<?= site_url('campaigns/export-single/' . $campaign['id']) ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" title="Export Analitik CSV">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-3" onclick="location.reload()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Section 1: Financial Health KPIs -->
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h6 class="text-uppercase fw-bold text-muted small mb-0"><i class="fas fa-wallet me-2 text-primary"></i>Kesehatan Anggaran &amp; Realisasi Finansial</h6>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Total Anggaran Kampanye</span>
            <h4 class="fw-bold mb-1 text-dark">Rp <?= number_format($financial['total_budget'], 0, ',', '.') ?></h4>
            <div class="progress mt-2" style="height: 6px;">
              <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $financial['budget_absorption_pct'] ?>%;"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 11px;">
              <span>Serapan Deal</span>
              <b><?= $financial['budget_absorption_pct'] ?>%</b>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Alokasi Deal KOL Terdaftar</span>
            <h4 class="fw-bold mb-1 text-primary">Rp <?= number_format($financial['total_deals'], 0, ',', '.') ?></h4>
            <div class="small text-muted mt-2">
              <i class="fas fa-users me-1 text-primary"></i> <b><?= count($kols) ?></b> talent terdaftar
            </div>
            <div class="small text-muted" style="font-size: 11px;">Sisa: Rp <?= number_format($financial['remaining_budget'], 0, ',', '.') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Invoice Ditagihkan</span>
            <h4 class="fw-bold mb-1 text-info">Rp <?= number_format($financial['total_invoiced'], 0, ',', '.') ?></h4>
            <div class="small text-muted mt-2">
              <i class="fas fa-file-invoice-dollar me-1 text-info"></i> Masuk antrean verifikasi
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Total Dana Terbayar</span>
            <h4 class="fw-bold mb-1 text-success">Rp <?= number_format($financial['total_paid'], 0, ',', '.') ?></h4>
            <div class="small <?= $financial['unpaid_balance'] > 0 ? 'text-warning' : 'text-muted' ?> mt-2" style="font-size: 11px;">
              <i class="fas fa-clock me-1"></i> Outstanding: Rp <?= number_format($financial['unpaid_balance'], 0, ',', '.') ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Reach & Engagement Performance KPIs -->
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h6 class="text-uppercase fw-bold text-muted small mb-0"><i class="fas fa-bullseye me-2 text-danger"></i>Performa Jangkauan &amp; Efisiensi Biaya</h6>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Total Views Aktual</span>
            <h4 class="fw-bold mb-1 text-danger">
              <?php
                $v = $performance['total_views'];
                if ($v >= 1000000) {
                  echo number_format($v / 1000000, 1) . 'M';
                } elseif ($v >= 1000) {
                  echo number_format($v / 1000, 1) . 'K';
                } else {
                  echo number_format($v);
                }
              ?>
              <span class="text-muted fs-7 fw-normal">(<?= number_format($v) ?>)</span>
            </h4>
            <div class="progress mt-2" style="height: 6px;">
              <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $performance['views_achievement_pct'] ?>%;"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 11px;">
              <span>Target: <?= number_format($performance['target_views']) ?></span>
              <b><?= $performance['views_achievement_pct'] ?>%</b>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Total Interaksi / Engagements</span>
            <h4 class="fw-bold mb-1 text-success"><?= number_format($performance['total_engagements']) ?></h4>
            <div class="small text-muted mt-2" style="font-size: 11.5px;">
              <span class="me-2"><i class="fas fa-heart text-danger me-1"></i><?= number_format($performance['total_likes']) ?></span>
              <span class="me-2"><i class="fas fa-comment text-primary me-1"></i><?= number_format($performance['total_comments']) ?></span>
              <span><i class="fas fa-share text-success me-1"></i><?= number_format($performance['total_shares']) ?></span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Average Engagement Rate</span>
            <h4 class="fw-bold mb-1 text-primary"><?= number_format($performance['avg_er'], 2) ?>%</h4>
            <div class="small text-muted mt-2" style="font-size: 11px;">
              <i class="fas fa-chart-pie me-1 text-primary"></i> Benchmark standar industri: 3.5%
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3">
            <span class="text-muted small d-block">Efisiensi Biaya (CPM &amp; CPE)</span>
            <h4 class="fw-bold mb-1 text-dark">
              Rp <?= number_format($performance['cpm'], 0, ',', '.') ?> <span class="fs-7 text-muted fw-normal">/1K views</span>
            </h4>
            <div class="small text-muted mt-2" style="font-size: 11px;">
              <i class="fas fa-tag me-1 text-success"></i> CPE: <b>Rp <?= number_format($performance['cpe'], 0, ',', '.') ?></b> per interaksi
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: Charts Visualisasi -->
    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-chart-bar text-primary me-2"></i>Komparasi Views &amp; Engagement Antar Talent
            </h6>
          </div>
          <div class="card-body p-3">
            <canvas id="chartPerformance" style="max-height: 280px; width: 100%;"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-chart-pie text-success me-2"></i>Proporsi Alokasi Deal KOL
            </h6>
          </div>
          <div class="card-body p-3 d-flex align-items-center justify-content-center">
            <canvas id="chartBudget" style="max-height: 250px; width: 100%;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 4: Breakdown Table per KOL -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">
          <i class="fas fa-table-list text-secondary me-2"></i>Rincian Kinerja Per Talent KOL (<?= count($kols) ?> Talent)
        </h6>
        <span class="badge bg-light text-dark border px-2 py-1">Konsolidasi Finansial &amp; Tugas</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light text-uppercase fs-7 text-muted">
              <tr>
                <th>Talent KOL</th>
                <th>Deal Fee</th>
                <th>Status Pembayaran</th>
                <th>Progress SOW</th>
                <th>Views</th>
                <th>Interaksi</th>
                <th>ER (%)</th>
                <th>CPM (IDR)</th>
                <th>CPE (IDR)</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($kols)): ?>
                <?php foreach ($kols as $k): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                          <?= strtoupper(substr($k['kol_name'], 0, 1)) ?>
                        </div>
                        <div>
                          <a href="<?= base_url('kols/view/' . $k['kol_id']) ?>" class="fw-bold text-dark text-decoration-none">
                            <?= esc($k['kol_name']) ?>
                          </a>
                          <div class="text-muted" style="font-size: 11px;">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($k['kol_city'] ?: '-') ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="fw-bold text-dark">
                      Rp <?= number_format($k['deal_amount'], 0, ',', '.') ?>
                    </td>
                    <td>
                      <?php if ($k['payment_status'] === 'PAID'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Lunas</span>
                      <?php elseif ($k['payment_status'] === 'PARTIAL'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Sebagian</span>
                      <?php elseif ($k['payment_status'] === 'UNPAID'): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Belum Dibayar</span>
                      <?php else: ?>
                        <span class="badge bg-light text-muted border px-2 py-1">Belum Ada Tagihan</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2" style="width: 120px;">
                        <div class="progress flex-grow-1" style="height: 6px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: <?= $k['task_progress'] ?>%;"></div>
                        </div>
                        <span class="small fw-semibold"><?= $k['task_completed'] ?>/<?= $k['task_total'] ?></span>
                      </div>
                    </td>
                    <td class="fw-bold text-dark">
                      <?= number_format($k['views']) ?>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border"><?= number_format($k['engagements']) ?></span>
                    </td>
                    <td>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold"><?= $k['er'] ?>%</span>
                    </td>
                    <td>
                      Rp <?= number_format($k['cpm'], 0, ',', '.') ?>
                    </td>
                    <td>
                      Rp <?= number_format($k['cpe'], 0, ',', '.') ?>
                    </td>
                    <td class="text-center">
                      <a href="<?= base_url('kols/performance-card/' . $k['kol_id']) ?>" class="btn btn-outline-primary btn-sm py-0 px-2 rounded-2" target="_blank" title="Buka Kartu Performa Talent">
                        <i class="fas fa-id-card me-1"></i> Kartu
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="10" class="text-center py-4 text-muted">Belum ada talent KOL yang dialokasikan pada kampanye ini.</td>
                </tr>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
  const chartLabels = <?= json_encode($chartData['labels'] ?? []) ?>;
  const chartViews = <?= json_encode($chartData['views'] ?? []) ?>;
  const chartEngagements = <?= json_encode($chartData['engagements'] ?? []) ?>;
  const chartDeals = <?= json_encode($chartData['deals'] ?? []) ?>;

  if (chartLabels.length > 0) {
    // 1. Bar Chart: Views vs Engagements
    const ctxPerf = document.getElementById('chartPerformance').getContext('2d');
    new Chart(ctxPerf, {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [
          {
            label: 'Views',
            data: chartViews,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            borderRadius: 4
          },
          {
            label: 'Engagements',
            data: chartEngagements,
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            borderRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    // 2. Doughnut Chart: Deal Distribution
    const ctxBudget = document.getElementById('chartBudget').getContext('2d');
    new Chart(ctxBudget, {
      type: 'doughnut',
      data: {
        labels: chartLabels,
        datasets: [{
          data: chartDeals,
          backgroundColor: [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#6f42c1', '#fd7e14', '#20c997', '#0dcaf0', '#6c757d'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }
});
</script>
<?= $this->endSection() ?>
