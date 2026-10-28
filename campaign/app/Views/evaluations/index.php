<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-medal text-warning me-2"></i>Evaluasi Kinerja Internal</h3>
        <p class="text-muted small mb-0">Scorecard KPI tim operasional berbasis matriks matematis: ketepatan waktu posting, kecepatan review draft, dan pengiriman sampel.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Evaluasi Kinerja</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- KPI Team Aggregates -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="fas fa-trophy fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Rata-rata Skor Tim</div>
              <h4 class="mb-0 fw-bold text-dark"><?= $teamSummary['avg_team_score'] ?? 0 ?> <span class="text-muted fs-6 fw-normal">/ 100</span></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-business-time fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">On-Time Posting Tim</div>
              <h4 class="mb-0 fw-bold text-dark"><?= $teamSummary['avg_on_time_rate'] ?? 0 ?>%</h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
              <i class="fas fa-stopwatch fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Rata-rata TAT Review</div>
              <h4 class="mb-0 fw-bold text-dark"><?= $teamSummary['avg_tat'] ?? 0 ?> <span class="text-muted fs-6 fw-normal">jam</span></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-users fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Distribusi Grade</div>
              <div class="d-flex gap-1 mt-1">
                <span class="badge bg-success" title="Grade A"><?= $teamSummary['grade_a_count'] ?? 0 ?> A</span>
                <span class="badge bg-primary" title="Grade B"><?= $teamSummary['grade_b_count'] ?? 0 ?> B</span>
                <span class="badge bg-warning text-dark" title="Grade C"><?= $teamSummary['grade_c_count'] ?? 0 ?> C</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Scorecard Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <form method="GET" action="<?= base_url('evaluations') ?>" class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" name="campaign_id" style="min-width: 220px;" onchange="this.form.submit()">
              <option value="">Semua Kampanye (Akumulasi)</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($campaignId == $c['id']) ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </form>

          <div>
            <a href="<?= base_url('evaluations/export-excel') ?><?= $campaignId ? ('?campaign_id=' . $campaignId) : '' ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 shadow-sm">
              <i class="fas fa-file-excel me-1"></i> Export Scorecard (.CSV)
            </a>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Staff Operasional</th>
                <th style="width: 170px;">1. On-Time Posting (40 pts)</th>
                <th style="width: 170px;">2. TAT Review Video (30 pts)</th>
                <th style="width: 170px;">3. Logistik Sampel (30 pts)</th>
                <th style="width: 180px;">Total Skor KPI</th>
                <th style="width: 130px;" class="text-center">Grade Predikat</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($staffKpis)): ?>
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">Belum ada data evaluasi operasional tercatat.</td>
                </tr>
              <?php else: ?>
                <?php $idx = 1; foreach ($staffKpis as $row): ?>
                  <tr>
                    <td class="text-center text-muted small"><?= $idx++ ?></td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-weight: bold; font-size: 13px;">
                          <?= strtoupper(substr($row['staff_name'], 0, 1)) ?>
                        </div>
                        <div>
                          <div class="fw-bold text-dark"><?= esc($row['staff_name']) ?></div>
                          <small class="text-muted"><?= esc($row['staff_email']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark"><?= $row['on_time_rate'] ?>% On-Time</div>
                      <div class="small text-muted"><?= $row['on_time_points'] ?> / 40 Poin (<?= $row['posted_count'] ?>/<?= $row['total_tasks'] ?> VT)</div>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark"><?= $row['avg_tat_hours'] ?> Jam TAT</div>
                      <div class="small text-muted"><?= $row['tat_points'] ?> / 30 Poin</div>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark"><?= $row['avg_dispatch_days'] ?> Hari Kirim</div>
                      <div class="small text-muted"><?= $row['dispatch_points'] ?> / 30 Poin</div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold text-dark fs-6"><?= $row['total_score'] ?></span>
                        <span class="text-muted small">/ 100</span>
                      </div>
                      <div class="progress" style="height: 6px;">
                        <div class="progress-bar <?= $row['total_score'] >= 85 ? 'bg-success' : ($row['total_score'] >= 65 ? 'bg-primary' : 'bg-warning') ?>" role="progressbar" style="width: <?= min(100, $row['total_score']) ?>%;"></div>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge <?= $row['grade_class'] ?> px-3 py-1 fs-6">
                        Grade <?= $row['grade'] ?>
                      </span>
                      <div class="text-muted mt-1" style="font-size: 11px;"><?= $row['grade_label'] ?></div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Scoring Criteria Legend Card -->
    <div class="card border-0 shadow-sm rounded-3 bg-light">
      <div class="card-body p-4">
        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Panduan Standar Penilaian KPI Operasional (100 Poin)</h6>
        <div class="row g-3 small">
          <div class="col-md-4">
            <div class="card border p-3 h-100 bg-white">
              <strong class="text-primary mb-1 d-block">1. Ketepatan Waktu Posting (Max 40 Poin)</strong>
              <div>&bull; &ge; 90% Tepat Waktu: <strong>40 Poin</strong></div>
              <div>&bull; 75% - 89% Tepat Waktu: <strong>30 Poin</strong></div>
              <div>&bull; 50% - 74% Tepat Waktu: <strong>20 Poin</strong></div>
              <div>&bull; &lt; 50% Tepat Waktu: <strong>10 Poin</strong></div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border p-3 h-100 bg-white">
              <strong class="text-info mb-1 d-block">2. Kecepatan Review Draft (Max 30 Poin)</strong>
              <div>&bull; &le; 2 Jam TAT: <strong>30 Poin</strong></div>
              <div>&bull; &le; 8 Jam TAT: <strong>25 Poin</strong></div>
              <div>&bull; &le; 24 Jam TAT: <strong>15 Poin</strong></div>
              <div>&bull; &gt; 24 Jam TAT: <strong>5 Poin</strong></div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border p-3 h-100 bg-white">
              <strong class="text-success mb-1 d-block">3. Pengiriman Sampel (Max 30 Poin)</strong>
              <div>&bull; &le; 1 Hari Kirim: <strong>30 Poin</strong></div>
              <div>&bull; &le; 3 Hari Kirim: <strong>25 Poin</strong></div>
              <div>&bull; &le; 5 Hari Kirim: <strong>15 Poin</strong></div>
              <div>&bull; &gt; 5 Hari Kirim: <strong>5 Poin</strong></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
