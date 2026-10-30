<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-chart-line text-info me-2"></i>Postings Detail &amp; Performance Decay
        </h3>
        <p class="text-muted small mb-0">Pantau kurva peluruhan performa (views decay), engagement rate, dan riwayat pekerjaan scraping Apify.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= base_url('postings') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <?php if (!empty($posting['live_url'])): ?>
          <a href="<?= esc($posting['live_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-2 px-3 me-2">
            <i class="fas fa-external-link-alt me-1"></i> Buka Live URL
          </a>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="openUpdateMetricsModal()">
          <i class="fas fa-edit me-1"></i> Update Metrik
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm btn-trigger-scrape" data-id="<?= $posting['id'] ?>">
          <i class="fas fa-sync-alt me-1"></i> Trigger Scrape Sekarang
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- PHASE 7: PERFORMANCE DECAY & KPI SUMMARY CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #0284c7 !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Total Views Saat Ini</span>
            <h3 class="fw-bold mb-1 text-dark mt-1"><?= number_format($viewsNow) ?></h3>
            <span class="badge <?= $decayClass ?> px-2 py-1" style="font-size: 11px;">
              <?= $decayStatus ?>
            </span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #10b981 !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Engagement Rate (ER)</span>
            <h3 class="fw-bold mb-1 text-success mt-1"><?= number_format($erNow * 100, 2) ?>%</h3>
            <small class="text-muted">Interaksi penonton (likes, komentar, bagikan)</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #8b5cf6 !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Cost Per View (CPV)</span>
            <h3 class="fw-bold mb-1 text-purple mt-1" style="color: #6d28d9;">Rp <?= number_format($cpvNow, 2, ',', '.') ?></h3>
            <small class="text-muted">Efisiensi biaya tayang aktual</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f59e0b !important;">
          <div class="card-body p-3">
            <span class="text-uppercase text-muted fw-bold fs-7 d-block">Total Snapshot Terpantau</span>
            <h3 class="fw-bold mb-1 text-warning mt-1"><?= count($snapshots) ?> Kali</h3>
            <small class="text-muted">Pelacakan berkala (Day 1, 3, 7)</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Decay Curve Chart Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark">
          <i class="fas fa-chart-area text-primary me-2"></i>Kurva Peluruhan Performa &amp; Pertumbuhan Views (Performance Decay)
        </h5>
      </div>
      <div class="card-body p-4">
        <?php if (!empty($snapshots)): ?>
          <div style="height: 300px; width: 100%;">
            <canvas id="performanceDecayChart"></canvas>
          </div>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="far fa-clock fa-3x mb-3 text-secondary"></i>
            <p class="mb-0">Belum ada data snapshot metrik. Klik tombol <strong>"Trigger Scrape Sekarang"</strong> di kanan atas untuk merekam snapshot performa.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Postings Metadata & Specification Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark">
          <i class="fas fa-info-circle text-primary me-2"></i>Spesifikasi Metadata Postingan
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered mb-0 align-middle">
            <tbody>
              <tr>
                <td style="width: 220px;" class="bg-light fw-semibold text-secondary">ID Postingan</td>
                <td class="fw-bold text-dark">#<?= $posting['id'] ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Task / Tugas Video</td>
                <td>
                  <div class="fw-bold text-dark"><?= esc(($posting['task_title'] ?? '') ?: ('VT #' . ($posting['task_number'] ?? ''))) ?></div>
                  <small class="text-muted">Task Number: <?= esc($posting['task_number'] ?? '-') ?> &bull; Status: <span class="badge bg-secondary-subtle text-dark border"><?= esc($posting['task_status'] ?? 'POSTED') ?></span></small>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Kampanye</td>
                <td class="fw-semibold text-primary"><?= esc($posting['campaign_title'] ?: '-') ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Talent KOL / Creator</td>
                <td>
                  <div class="fw-bold text-dark"><?= esc($posting['kol_name'] ?: '-') ?></div>
                  <?php if (!empty($posting['kol_username'])): ?>
                    <small class="text-muted">@<?= esc($posting['kol_username']) ?></small>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Platform</td>
                <td>
                  <span class="badge bg-dark px-2 py-1">
                    <i class="<?= esc($posting['platform_icon'] ?: 'fas fa-video') ?> me-1"></i>
                    <?= esc($posting['platform_name'] ?: 'Video') ?>
                  </span>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Live URL Postingan</td>
                <td>
                  <?php if (!empty($posting['live_url'])): ?>
                    <a href="<?= esc($posting['live_url']) ?>" target="_blank" class="fw-bold text-primary text-break">
                      <?= esc($posting['live_url']) ?> <i class="fas fa-external-link-alt ms-1 small"></i>
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Actual Posted Date</td>
                <td><?= !empty($posting['actual_posted_at']) ? date('d M Y H:i:s', strtotime($posting['actual_posted_at'])) : (!empty($posting['posted_at']) ? date('d M Y H:i:s', strtotime($posting['posted_at'])) : '-') ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Posted At Source</td>
                <td><?= !empty($posting['posted_at_source']) ? esc($posting['posted_at_source']) : '-' ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Status Validasi Platform</td>
                <td>
                  <span class="badge bg-<?= ($posting['platform_validation_status'] ?? 'VALID') === 'VALID' ? 'success' : 'warning' ?>-subtle text-<?= ($posting['platform_validation_status'] ?? 'VALID') === 'VALID' ? 'success' : 'warning' ?> border">
                    <?= esc($posting['platform_validation_status'] ?: 'VALID') ?>
                  </span>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Status Validasi Tanggal</td>
                <td>
                  <span class="badge bg-<?= ($posting['date_validation_status'] ?? 'VALID') === 'VALID' ? 'success' : 'warning' ?>-subtle text-<?= ($posting['date_validation_status'] ?? 'VALID') === 'VALID' ? 'success' : 'warning' ?> border">
                    <?= esc($posting['date_validation_status'] ?: 'VALID') ?>
                  </span>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Caption Video</td>
                <td class="text-secondary"><?= !empty($posting['caption']) ? nl2br(esc($posting['caption'])) : '<span class="text-muted">-</span>' ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Status Postingan</td>
                <td>
                  <span class="badge bg-<?= ($posting['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'success' : 'secondary' ?>-subtle text-<?= ($posting['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'success' : 'secondary' ?> border">
                    <?= esc($posting['status'] ?: 'ACTIVE') ?>
                  </span>
                </td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Submitted By / Operator</td>
                <td><?= esc($posting['submitted_by'] ?: 'System') ?></td>
              </tr>
              <tr>
                <td class="bg-light fw-semibold text-secondary">Dibuat &amp; Terakhir Diperbarui</td>
                <td class="text-muted small">
                  Dibuat: <?= !empty($posting['created_at']) ? date('d M Y H:i:s', strtotime($posting['created_at'])) : '-' ?> &bull; 
                  Diperbarui: <?= !empty($posting['updated_at']) ? date('d M Y H:i:s', strtotime($posting['updated_at'])) : '-' ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Riwayat Snapshot Metrik (Event-Sourced Snapshots) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark">
          <i class="fas fa-layer-group text-success me-2"></i>Riwayat Snapshot Metrik (Performance Log)
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th>Snapshot ID</th>
                <th>Waktu Pengambilan</th>
                <th>Views</th>
                <th>Likes</th>
                <th>Komentar</th>
                <th>Shares</th>
                <th>Saves</th>
                <th>ER (%)</th>
                <th>CPV (IDR)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($snapshots)): ?>
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">Belum ada riwayat snapshot metrik untuk postingan ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach (array_reverse($snapshots) as $snap): ?>
                  <?php
                    $v = (int)($snap['views'] ?? $snap['views_count'] ?? 0);
                    $l = (int)($snap['likes'] ?? $snap['likes_count'] ?? 0);
                    $c = (int)($snap['comments'] ?? $snap['comments_count'] ?? 0);
                    $s = (int)($snap['shares'] ?? $snap['shares_count'] ?? 0);
                    $sv = (int)($snap['saves'] ?? $snap['saves_count'] ?? 0);
                    $er = (float)($snap['engagement_rate'] ?? 0) * 100;
                    $cpv = (float)($snap['cpv'] ?? $snap['actual_cpv'] ?? 0);
                  ?>
                  <tr>
                    <td class="fw-bold">#<?= $snap['id'] ?></td>
                    <td><?= !empty($snap['snapshot_at']) ? date('d M Y H:i:s', strtotime($snap['snapshot_at'])) : date('d M Y H:i:s', strtotime($snap['created_at'])) ?></td>
                    <td class="fw-bold text-dark"><?= number_format($v) ?></td>
                    <td><?= number_format($l) ?></td>
                    <td><?= number_format($c) ?></td>
                    <td><?= number_format($s) ?></td>
                    <td><?= number_format($sv) ?></td>
                    <td class="fw-semibold text-success"><?= number_format($er, 2) ?>%</td>
                    <td class="fw-semibold text-dark">Rp <?= number_format($cpv, 2, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Riwayat Pekerjaan Scraping Apify (Async Job History) -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark">
          <i class="fas fa-history text-info me-2"></i>Riwayat Pekerjaan Scraping Apify (Async Job Audit Trail)
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th>Run ID</th>
                <th>Percobaan Ke-</th>
                <th>Status</th>
                <th>Dimulai</th>
                <th>Selesai</th>
                <th>Target URL / Error Message</th>
              </tr>
            </thead>
            <tbody>
              <?php $jobs = $posting['apify_jobs'] ?? []; ?>
              <?php if (empty($jobs)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat job scraping untuk postingan ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                  <?php
                    $badgeClass = match ($job['status'] ?? 'QUEUED') {
                      'SUCCEEDED', 'SUCCESS' => 'bg-success-subtle text-success border border-success-subtle',
                      'RUNNING'              => 'bg-warning-subtle text-warning border border-warning-subtle',
                      'FAILED'               => 'bg-danger-subtle text-danger border border-danger-subtle',
                      default                => 'bg-secondary-subtle text-secondary border',
                    };
                  ?>
                  <tr>
                    <td style="font-family: monospace;" class="fw-bold text-dark"><?= esc($job['external_run_id'] ?: ('job_' . $job['id'])) ?></td>
                    <td>Attempt #<?= $job['attempt_number'] ?? 1 ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= esc($job['status'] ?? 'QUEUED') ?></span></td>
                    <td><?= !empty($job['started_at']) ? date('d M Y H:i:s', strtotime($job['started_at'])) : '-' ?></td>
                    <td><?= !empty($job['finished_at']) ? date('d M Y H:i:s', strtotime($job['finished_at'])) : '-' ?></td>
                    <td class="text-muted" style="max-width: 300px;"><?= esc($job['error_message'] ?: ($job['target_url'] ?: 'Operasi Selesai Sukses')) ?></td>
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

<!-- Modal Update Metrik Aktual -->
<div class="modal fade" id="modalUpdateMetrics" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formMetrics" onsubmit="savePostingMetrics(event)">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Update Metrik Aktual Postingan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="upPostingId" name="posting_id" value="<?= $posting['id'] ?>">
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah Tayangan (Views) <span class="text-danger">*</span></label>
            <input type="number" class="form-control fs-5 fw-bold" id="metricViews" name="views_count" required min="0" value="<?= $viewsNow ?>">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Likes</label>
              <input type="number" class="form-control" id="metricLikes" name="likes_count" min="0" value="<?= (int)($posting['likes_count'] ?? 0) ?>">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Komentar</label>
              <input type="number" class="form-control" id="metricComments" name="comments_count" min="0" value="<?= (int)($posting['comments_count'] ?? 0) ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Shares (Bagikan)</label>
              <input type="number" class="form-control" id="metricShares" name="shares_count" min="0" value="<?= (int)($posting['shares_count'] ?? 0) ?>">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Saves / Bookmark</label>
              <input type="number" class="form-control" id="metricSaves" name="saves_count" min="0" value="<?= (int)($posting['saves_count'] ?? 0) ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveMetrics">Simpan Metrik</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    <?php if (!empty($snapshots)): ?>
        const labels = <?= json_encode(array_map(function($s) {
            $t = !empty($s['snapshot_at']) ? $s['snapshot_at'] : $s['created_at'];
            return date('d M H:i', strtotime($t));
        }, $snapshots)); ?>;

        const dataViews = <?= json_encode(array_map(function($s) {
            return (int)($s['views'] ?? $s['views_count'] ?? 0);
        }, $snapshots)); ?>;

        const dataLikes = <?= json_encode(array_map(function($s) {
            return (int)($s['likes'] ?? $s['likes_count'] ?? 0);
        }, $snapshots)); ?>;

        const ctx = document.getElementById('performanceDecayChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Views',
                        data: dataViews,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.1)',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Likes / Interactions',
                        data: dataLikes,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.35,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Views' },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Likes' }
                    }
                }
            }
        });
    <?php endif; ?>

    // Trigger Scrape Handler
    $(document).on('click', '.btn-trigger-scrape', function() {
        const id = $(this).data('id');
        const $btn = $(this);
        
        Swal.fire({
            title: 'Memicu Scraping Apify?',
            text: 'Sistem akan mengirim instruksi scraping untuk memperbarui views dan engagement terkini dari live URL.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-sync-alt me-1"></i> Ya, Jalankan Scrape',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sedang Scraping...',
                    text: 'Memproses scraping dan menghitung metrik decay curve...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '<?= base_url('postings/trigger-scrape') ?>/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Scraping Berhasil!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal Scraping', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghubungi server untuk scraping.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });
});

function openUpdateMetricsModal() {
    $('#modalUpdateMetrics').modal('show');
}

function savePostingMetrics(e) {
    e.preventDefault();
    const id = $('#upPostingId').val();
    const btn = $('#btnSaveMetrics');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

    $.ajax({
        url: '<?= base_url('postings/update-metrics') ?>/' + id,
        type: 'POST',
        data: $('#formMetrics').serialize(),
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).text('Simpan Metrik');
            if (res.success) {
                $('#modalUpdateMetrics').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).text('Simpan Metrik');
            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
            Swal.fire('Gagal', err, 'error');
        }
    });
}
</script>
<?= $this->endSection() ?>