<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-medal text-warning me-2"></i>Evaluasi Kinerja Internal</h3>
        <p class="text-muted small mb-0">Scorecard KPI tim operasional dan leader berbasis matriks matematis: ketepatan posting, kecepatan review, dan efisiensi budget &amp; views.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Evaluasi Kinerja</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Filter & Tab Switcher Bar -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
      <ul class="nav nav-pills" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active rounded-pill px-4 fw-semibold" id="tab-staff-btn" data-bs-toggle="pill" data-bs-target="#tab-staff" type="button" role="tab">
            <i class="fas fa-user-cog me-1"></i> Tim Operasional (Staff)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link rounded-pill px-4 fw-semibold ms-2" id="tab-leader-btn" data-bs-toggle="pill" data-bs-target="#tab-leader" type="button" role="tab">
            <i class="fas fa-user-tie me-1"></i> Leader KOL &amp; Strategist
          </button>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <form method="GET" action="<?= base_url('evaluations') ?>" class="d-flex align-items-center gap-2">
          <select class="form-select form-select-sm rounded-2 shadow-sm" name="campaign_id" style="min-width: 220px;" onchange="this.form.submit()">
            <option value="">Semua Kampanye (Akumulasi)</option>
            <?php foreach ($allCampaigns as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($campaignId == $c['id']) ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </form>

        <a href="<?= base_url('evaluations/export-excel') ?><?= $campaignId ? ('?campaign_id=' . $campaignId) : '' ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 shadow-sm text-nowrap">
          <i class="fas fa-file-excel me-1"></i> Export Scorecard (.CSV)
        </a>
      </div>
    </div>

    <div class="tab-content" id="pills-tabContent">

      <!-- ==================== TAB 1: STAFF OPERASIONAL ==================== -->
      <div class="tab-pane fade show active" id="tab-staff" role="tabpanel">
        
        <!-- KPI Team Aggregates -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
              <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                  <i class="fas fa-trophy fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Rata-rata Skor Staff</div>
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

        <!-- Staff Scorecard Table -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="card-title fw-bold text-dark mb-0"><i class="fas fa-clipboard-list text-primary me-2"></i>Scorecard KPI Personil Operasional Lapangan</h6>
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

        <!-- Legend Card Staff -->
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

      <!-- ==================== TAB 2: LEADER KOL & STRATEGIST ==================== -->
      <div class="tab-pane fade" id="tab-leader" role="tabpanel">
        
        <!-- KPI Leader Aggregates -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
              <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                  <i class="fas fa-user-tie fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Rata-rata Skor Leader</div>
                  <h4 class="mb-0 fw-bold text-dark"><?= $leaderSummary['avg_leader_score'] ?? 0 ?> <span class="text-muted fs-6 fw-normal">/ 100</span></h4>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
              <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                  <i class="fas fa-eye fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Target Views Achievement</div>
                  <h4 class="mb-0 fw-bold text-dark"><?= $leaderSummary['avg_view_rate'] ?? 0 ?>%</h4>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
              <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                  <i class="fas fa-coins fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Efisiensi Serapan Budget</div>
                  <h4 class="mb-0 fw-bold text-dark"><?= $leaderSummary['avg_budget_rate'] ?? 0 ?>%</h4>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
              <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                  <i class="fas fa-chart-line fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Total Realisasi Views</div>
                  <h4 class="mb-0 fw-bold text-dark"><?= number_format($leaderSummary['tot_actual_views'] ?? 0) ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Leader Scorecard Table -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="card-title fw-bold text-dark mb-0"><i class="fas fa-chess-king text-warning me-2"></i>Scorecard KPI Strategis Leader KOL &amp; Campaign Manager</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" style="width: 100%;">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>Leader KOL / Manager</th>
                    <th style="width: 170px;">1. Views vs Target (40 pts)</th>
                    <th style="width: 170px;">2. Budget vs Realisasi (35 pts)</th>
                    <th style="width: 150px;">3. Engagement (25 pts)</th>
                    <th style="width: 130px;">Cost / View (CPV)</th>
                    <th style="width: 160px;">Total Skor KPI</th>
                    <th style="width: 130px;" class="text-center">Grade Predikat</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($leaderKpis)): ?>
                    <tr>
                      <td colspan="8" class="text-center py-4 text-muted">Belum ada data leader tercatat.</td>
                    </tr>
                  <?php else: ?>
                    <?php $idx = 1; foreach ($leaderKpis as $row): ?>
                      <tr>
                        <td class="text-center text-muted small"><?= $idx++ ?></td>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-weight: bold; font-size: 13px;">
                              <?= strtoupper(substr($row['leader_name'], 0, 1)) ?>
                            </div>
                            <div>
                              <div class="fw-bold text-dark"><?= esc($row['leader_name']) ?></div>
                              <small class="text-muted"><?= esc($row['leader_email']) ?> • <?= $row['total_campaigns'] ?> Kampanye</small>
                            </div>
                          </div>
                        </td>
                        <td>
                          <div class="fw-semibold text-dark"><?= number_format($row['actual_views']) ?> / <?= number_format($row['target_views']) ?></div>
                          <div class="small text-muted"><?= $row['view_rate'] ?>% Target (<?= $row['view_points'] ?>/40 pts)</div>
                        </td>
                        <td>
                          <div class="fw-semibold text-dark">Rp <?= number_format($row['actual_spent'] / 1000000, 1) ?>M / Rp <?= number_format($row['total_budget'] / 1000000, 1) ?>M</div>
                          <div class="small text-muted"><?= $row['budget_rate'] ?>% Serapan (<?= $row['budget_points'] ?>/35 pts)</div>
                        </td>
                        <td>
                          <div class="fw-semibold text-dark"><?= number_format($row['actual_engagements']) ?></div>
                          <div class="small text-muted"><?= $row['eng_points'] ?> / 25 Poin</div>
                        </td>
                        <td>
                          <span class="badge bg-light text-dark border font-monospace">Rp <?= number_format($row['cpv'], 2) ?></span>
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

        <!-- Legend Card Leader -->
        <div class="card border-0 shadow-sm rounded-3 bg-light">
          <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Panduan Standar Penilaian KPI Leader Strategis (100 Poin)</h6>
            <div class="row g-3 small">
              <div class="col-md-4">
                <div class="card border p-3 h-100 bg-white">
                  <strong class="text-primary mb-1 d-block">1. Target Views Kampanye (Max 40 Poin)</strong>
                  <div>&bull; &ge; 100% Target Views: <strong>40 Poin</strong></div>
                  <div>&bull; 80% - 99% Target Views: <strong>30 Poin</strong></div>
                  <div>&bull; 60% - 79% Target Views: <strong>20 Poin</strong></div>
                  <div>&bull; &lt; 60% Target Views: <strong>10 Poin</strong></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border p-3 h-100 bg-white">
                  <strong class="text-warning mb-1 d-block">2. Efisiensi Alokasi Budget (Max 35 Poin)</strong>
                  <div>&bull; 80% - 100% Sesuai Budget: <strong>35 Poin</strong></div>
                  <div>&bull; 101% - 110% Overspend Ringan: <strong>25 Poin</strong></div>
                  <div>&bull; 50% - 79% Underspend: <strong>25 Poin</strong></div>
                  <div>&bull; &gt; 110% Overspend Tinggi: <strong>15 Poin</strong></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border p-3 h-100 bg-white">
                  <strong class="text-success mb-1 d-block">3. Total Interaksi &amp; SOW (Max 25 Poin)</strong>
                  <div>&bull; &gt; 10.000 Engagements: <strong>25 Poin</strong></div>
                  <div>&bull; 2.000 - 10.000 Engagements: <strong>20 Poin</strong></div>
                  <div>&bull; &lt; 2.000 Engagements: <strong>15 Poin</strong></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
</div>
<?= $this->endSection() ?>
