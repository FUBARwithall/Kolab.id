<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Detail Snapshot Posting Metric</h3>
        <p class="text-muted small mb-0">Rincian metrik performa video live per snapshot penarikan data scraping.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('posting-metrics') ?>" class="text-decoration-none">Posting Metrics</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $metric['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div>
            <h4 class="fw-bold mb-0 text-dark">Snapshot Metric #<?= $metric['id'] ?> &bull; Posting #<?= $metric['posting_id'] ?></h4>
            <small class="text-muted">
              Diambil pada: <?= date('d M Y H:i:s', strtotime($metric['snapshot_at'] ?: $metric['created_at'])) ?> WIB
              &bull; Platform: <strong class="text-primary"><?= esc($metric['platform_name'] ?? 'TikTok') ?></strong>
            </small>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('posting-metrics/single-pdf/' . $metric['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak / PDF
            </a>
            <a href="<?= esc($metric['postings_live_url'] ?? '#') ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-2">
              <i class="fas fa-external-link-alt me-1"></i> Buka Video Live
            </a>
            <a href="<?= base_url('posting-metrics') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Metrics Cards 4 Grids -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-primary">
          <div class="card-body p-3">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Total Views</span>
            <h3 class="fw-bold mb-0 text-primary mt-1"><?= number_format((int)$metric['resolved_views'], 0, ',', '.') ?></h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-success">
          <div class="card-body p-3">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Total Likes</span>
            <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format((int)$metric['resolved_likes'], 0, ',', '.') ?></h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-info">
          <div class="card-body p-3">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Total Comments</span>
            <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format((int)$metric['resolved_comments'], 0, ',', '.') ?></h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-warning">
          <div class="card-body p-3">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Total Shares</span>
            <h3 class="fw-bold mb-0 text-warning mt-1"><?= number_format((int)$metric['resolved_shares'], 0, ',', '.') ?></h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Details -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Target &amp; Penayangan</h6>
          </div>
          <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td class="text-muted" style="width: 160px;">URL Postingan Live</td>
                <td><a href="<?= esc($metric['postings_live_url'] ?? '#') ?>" target="_blank" class="text-primary text-break"><?= esc($metric['postings_live_url'] ?? '-') ?></a></td>
              </tr>
              <tr>
                <td class="text-muted">Kampanye</td>
                <td class="fw-bold text-dark"><?= esc($metric['campaign_title'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Talent KOL</td>
                <td class="fw-semibold text-dark"><?= esc($metric['kol_name'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Task Target</td>
                <td><?= esc($metric['task_title'] ?: ('VT #' . ($metric['task_number'] ?? '1'))) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Tanggal Posting</td>
                <td><?= !empty($metric['posted_date']) ? date('d F Y', strtotime($metric['posted_date'])) : '-' ?></td>
              </tr>
              <tr>
                <td class="text-muted">Waktu Snapshot</td>
                <td class="fw-bold text-primary"><?= date('d F Y H:i:s', strtotime($metric['snapshot_at'] ?: $metric['created_at'])) ?> WIB</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-calculator text-success me-2"></i>Efisiensi Biaya &amp; Rasio Metrik</h6>
          </div>
          <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td class="text-muted" style="width: 160px;">Engagement Rate (ER)</td>
                <td class="fw-bold text-success fs-6"><?= number_format((float)$metric['engagement_rate'], 2) ?>%</td>
              </tr>
              <tr>
                <td class="text-muted">Cost Per View (CPV)</td>
                <td class="fw-bold text-primary fs-6">Rp <?= number_format((float)$metric['resolved_cpv'], 2, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Cost Per Action (CPA)</td>
                <td class="fw-semibold text-dark">Rp <?= number_format((float)$metric['cpa'], 2, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted">View Rate</td>
                <td class="fw-semibold text-dark"><?= number_format((float)$metric['view_rate'], 2) ?>%</td>
              </tr>
              <tr>
                <td class="text-muted">Rata-rata Views</td>
                <td><?= number_format((float)$metric['average_views'], 0, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Median Views</td>
                <td><?= number_format((float)$metric['median_views'], 0, ',', '.') ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Column: Audiens & Timeline History -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users text-info me-2"></i>Audiens &amp; Jangkauan</h6>
          </div>
          <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td class="text-muted" style="width: 160px;">Followers Talent</td>
                <td class="fw-bold text-dark"><?= number_format((int)$metric['followers'], 0, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Impressions</td>
                <td class="fw-bold text-dark"><?= number_format((int)$metric['impressions'], 0, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Reach</td>
                <td class="fw-bold text-dark"><?= number_format((int)$metric['reach'], 0, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Saves / Bookmark</td>
                <td class="fw-bold text-dark"><?= number_format((int)$metric['resolved_saves'], 0, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Total Likes Profil</td>
                <td><?= number_format((int)$metric['total_likes'], 0, ',', '.') ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Timeline of Snapshots for this posting -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-secondary me-2"></i>Riwayat Snapshot Posting Ini</h6>
            <span class="badge bg-light text-dark border"><?= count($allSnapshots) ?> Snapshot</span>
          </div>
          <div class="card-body p-3">
            <?php if (!empty($allSnapshots)): ?>
              <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#ID</th>
                      <th>Waktu</th>
                      <th class="text-end">Views</th>
                      <th class="text-end">Likes</th>
                      <th class="text-center">ER (%)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($allSnapshots as $s): ?>
                      <tr class="<?= $s['id'] == $metric['id'] ? 'table-primary fw-bold' : '' ?>">
                        <td><a href="<?= base_url('posting-metrics/view/' . $s['id']) ?>">#<?= $s['id'] ?></a></td>
                        <td><?= date('d/m H:i', strtotime($s['snapshot_at'] ?: $s['created_at'])) ?></td>
                        <td class="text-end"><?= number_format((int)($s['views'] ?: $s['views_count']), 0, ',', '.') ?></td>
                        <td class="text-end"><?= number_format((int)($s['likes'] ?: $s['likes_count']), 0, ',', '.') ?></td>
                        <td class="text-center"><?= number_format((float)$s['engagement_rate'], 2) ?>%</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center py-4 text-muted small">Hanya ada 1 snapshot ini.</div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
