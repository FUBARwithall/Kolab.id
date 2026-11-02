<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-spider text-danger me-2"></i>Detail Scraping Job #<?= $job['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Inspeksi eksekusi Apify Actor, payload respons JSON, dan metrik penarikan otomatis.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('apify-jobs') ?>" class="text-decoration-none">Apify Jobs</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $job['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php
      // Status formatting
      $statusClass = 'bg-secondary';
      $statusIcon = 'far fa-clock';
      switch ($job['status']) {
        case 'SUCCEEDED':
        case 'SUCCESS':
          $statusClass = 'bg-success';
          $statusIcon = 'fas fa-check-circle';
          break;
        case 'RUNNING':
          $statusClass = 'bg-info text-dark';
          $statusIcon = 'fas fa-spinner fa-spin';
          break;
        case 'FAILED':
          $statusClass = 'bg-danger';
          $statusIcon = 'fas fa-exclamation-triangle';
          break;
        case 'QUEUED':
        default:
          $statusClass = 'bg-warning text-dark';
          $statusIcon = 'far fa-clock';
          break;
      }

      // Calculate execution duration
      $durationText = '-';
      if (!empty($job['started_at']) && !empty($job['finished_at'])) {
        $startTime = strtotime($job['started_at']);
        $endTime = strtotime($job['finished_at']);
        $diffSeconds = max(0, $endTime - $startTime);
        if ($diffSeconds < 60) {
          $durationText = $diffSeconds . ' detik';
        } else {
          $durationText = floor($diffSeconds / 60) . ' m ' . ($diffSeconds % 60) . ' s';
        }
      }

      // Platform badge
      $platformBadge = '<span class="badge bg-secondary"><i class="fas fa-link me-1"></i>Web Link</span>';
      if (stripos($job['target_url'], 'tiktok.com') !== false) {
        $platformBadge = '<span class="badge bg-dark"><i class="fab fa-tiktok me-1"></i>TikTok</span>';
      } elseif (stripos($job['target_url'], 'instagram.com') !== false) {
        $platformBadge = '<span class="badge" style="background:#e1306c; color:#fff;"><i class="fab fa-instagram me-1"></i>Instagram</span>';
      } elseif (stripos($job['target_url'], 'youtu') !== false) {
        $platformBadge = '<span class="badge bg-danger"><i class="fab fa-youtube me-1"></i>YouTube</span>';
      }

      // Parse JSON payload metrics if present
      $parsedJson = !empty($job['response_data']) ? json_decode($job['response_data'], true) : null;
      $parsedMetrics = [];
      if (is_array($parsedJson)) {
        // Try common Apify TikTok/IG actor fields
        $dataItem = isset($parsedJson[0]) && is_array($parsedJson[0]) ? $parsedJson[0] : $parsedJson;
        if (isset($dataItem['playCount']) || isset($dataItem['viewsCount']) || isset($dataItem['videoViewCount'])) {
          $parsedMetrics['views'] = $dataItem['playCount'] ?? ($dataItem['viewsCount'] ?? $dataItem['videoViewCount']);
        }
        if (isset($dataItem['diggCount']) || isset($dataItem['likesCount'])) {
          $parsedMetrics['likes'] = $dataItem['diggCount'] ?? $dataItem['likesCount'];
        }
        if (isset($dataItem['commentCount']) || isset($dataItem['commentsCount'])) {
          $parsedMetrics['comments'] = $dataItem['commentCount'] ?? $dataItem['commentsCount'];
        }
        if (isset($dataItem['shareCount']) || isset($dataItem['sharesCount'])) {
          $parsedMetrics['shares'] = $dataItem['shareCount'] ?? $dataItem['sharesCount'];
        }
      }
    ?>

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm <?= $statusClass ?>" style="width: 52px; height: 52px; font-size: 1.25rem;">
              <i class="<?= $statusIcon ?>"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">Job #<?= $job['id'] ?></h4>
                <span class="badge <?= $statusClass ?> px-2 py-1"><?= $job['status'] ?></span>
                <span class="badge bg-light text-secondary border">Percobaan ke-<?= $job['attempt_number'] ?? 1 ?></span>
              </div>
              <small class="text-muted">
                Run ID: <code class="text-dark fw-semibold"><?= esc($job['external_run_id'] ?? '-') ?></code> &bull; Dibuat: <?= date('d M Y, H:i:s', strtotime($job['created_at'])) ?> WIB
              </small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-warning btn-sm rounded-2" onclick="retryJob(<?= $job['id'] ?>)">
              <i class="fas fa-redo me-1"></i> Re-queue / Retry
            </button>
            <a href="<?= base_url('apify-jobs/single-pdf/' . $job['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Bukti Audit PDF
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2" onclick="deleteJob(<?= $job['id'] ?>)">
              <i class="fas fa-trash me-1"></i> Hapus Job
            </button>
            <a href="<?= base_url('apify-jobs') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Antrian
            </a>
          </div>
        </div>
      </div>
    </div>

    <?php if ($job['status'] === 'FAILED' || !empty($job['error_message'])): ?>
      <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-start gap-3 mb-4">
        <i class="fas fa-exclamation-triangle fs-4 mt-1"></i>
        <div class="flex-grow-1">
          <h6 class="alert-heading fw-bold mb-1">Scraping Gagal / Exception Terdeteksi</h6>
          <p class="mb-0 font-monospace small" style="white-space: pre-wrap;"><?= esc($job['error_message'] ?? 'Tidak ada pesan error spesifik dari Apify Actor.') ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Summary Metrics parsed from JSON (if available) -->
    <?php if (!empty($parsedMetrics)): ?>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 text-center">
              <span class="text-muted small d-block">Video Views</span>
              <h4 class="fw-bold mb-0 text-primary"><?= number_format($parsedMetrics['views'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 text-center">
              <span class="text-muted small d-block">Likes / Diggs</span>
              <h4 class="fw-bold mb-0 text-danger"><?= number_format($parsedMetrics['likes'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 text-center">
              <span class="text-muted small d-block">Comments</span>
              <h4 class="fw-bold mb-0 text-success"><?= number_format($parsedMetrics['comments'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 text-center">
              <span class="text-muted small d-block">Shares</span>
              <h4 class="fw-bold mb-0 text-warning"><?= number_format($parsedMetrics['shares'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Left Column: Details -->
      <div class="col-lg-6">

        <!-- Target & Platform Details -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-bullseye text-primary me-2"></i>Target &amp; Parameter Eksekusi</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 170px;">Target URL</td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <?= $platformBadge ?>
                        <a href="<?= esc($job['target_url']) ?>" target="_blank" class="font-monospace text-primary text-truncate d-inline-block" style="max-width: 280px;" title="<?= esc($job['target_url']) ?>">
                          <?= esc($job['target_url']) ?>
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm p-1 px-2" title="Copy URL" onclick="copyText('<?= esc($job['target_url']) ?>')">
                          <i class="far fa-copy"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Actor Type</td>
                    <td>
                      <?php if ($job['actor_type'] === 'post'): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-play me-1"></i>Video Post Content</span>
                      <?php else: ?>
                        <span class="badge bg-purple-subtle text-purple border border-purple-subtle px-2 py-1" style="background:#ede7f6;color:#673ab7;border-color:#d1c4e9;"><i class="fas fa-user-circle me-1"></i>Profile KOL Metrics</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Attempt Number</td>
                    <td><span class="badge bg-light text-dark border">Percobaan ke-<?= $job['attempt_number'] ?? 1 ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">External Run ID</td>
                    <td><code class="text-dark fw-bold"><?= esc($job['external_run_id'] ?? '-') ?></code></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Relasi Konten & Kampanye -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-link text-info me-2"></i>Entitas Terkait (KOL, Campaign &amp; Posting)</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 170px;">Talent (KOL)</td>
                    <td>
                      <?php if (!empty($job['kol_name'])): ?>
                        <div class="fw-bold text-dark"><?= esc($job['kol_name']) ?></div>
                        <small class="text-muted"><?= esc($job['kol_email'] ?? '-') ?></small>
                        <?php if (!empty($job['kol_id'])): ?>
                          <div class="mt-1">
                            <a href="<?= base_url('kols/view/' . $job['kol_id']) ?>" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:11px;">
                              <i class="fas fa-user me-1"></i> Buka KOL #<?= $job['kol_id'] ?>
                            </a>
                          </div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-muted fst-italic">Standalone / Tidak Terikat Talent</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Kampanye Terkait</td>
                    <td>
                      <?php if (!empty($job['campaign_title'])): ?>
                        <div class="fw-semibold text-dark"><?= esc($job['campaign_title']) ?></div>
                        <small class="text-muted">Brand: <?= esc($job['campaign_brand'] ?? '-') ?></small>
                      <?php else: ?>
                        <span class="text-muted fst-italic">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Posting Record</td>
                    <td>
                      <?php if (!empty($job['posting_id'])): ?>
                        <div class="d-flex align-items-center gap-2">
                          <span class="badge bg-info-subtle text-info border border-info-subtle">Posting #<?= $job['posting_id'] ?></span>
                          <?php if (!empty($job['postings_live_url'])): ?>
                            <a href="<?= esc($job['postings_live_url']) ?>" target="_blank" class="small text-truncate text-primary" style="max-width: 200px;">
                              <?= esc($job['postings_live_url']) ?>
                            </a>
                          <?php endif; ?>
                        </div>
                        <?php if (!empty($job['posting_status'])): ?>
                          <div class="small text-muted mt-1">Status: <?= esc($job['posting_status']) ?></div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-muted fst-italic">Bukan dari Posting ID</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Task Terkait</td>
                    <td>
                      <?php if (!empty($job['task_name'])): ?>
                        <div class="fw-semibold text-dark"><?= esc($job['task_name']) ?></div>
                        <small class="text-muted">Step: <?= esc($job['task_step'] ?? '-') ?> &bull; Status: <?= esc($job['task_status'] ?? '-') ?></small>
                      <?php else: ?>
                        <span class="text-muted fst-italic">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Timeline Eksekusi -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-secondary me-2"></i>Timeline &amp; Durasi Scraping</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 170px;">Waktu Dibuat</td>
                    <td><?= date('d F Y, H:i:s', strtotime($job['created_at'])) ?> WIB</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Mulai Eksekusi</td>
                    <td><?= !empty($job['started_at']) ? date('d F Y, H:i:s', strtotime($job['started_at'])) . ' WIB' : '<span class="text-muted fst-italic">Belum dimulai</span>' ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Selesai</td>
                    <td><?= !empty($job['finished_at']) ? date('d F Y, H:i:s', strtotime($job['finished_at'])) . ' WIB' : '<span class="text-muted fst-italic">Belum selesai</span>' ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Durasi Scraping</td>
                    <td><span class="badge bg-light text-dark border px-2 py-1 fw-bold"><?= $durationText ?></span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Response JSON Payload -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-code text-warning me-2"></i>Apify Response Payload</h5>
            <?php if (!empty($formattedJson)): ?>
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyResponsePayload()">
                <i class="far fa-copy me-1"></i> Salin JSON
              </button>
            <?php endif; ?>
          </div>
          <div class="card-body p-3 d-flex flex-column">
            <?php if (!empty($formattedJson)): ?>
              <p class="text-muted small mb-2">Payload mentah hasil scraping aktor Apify yang disimpan dalam database:</p>
              <pre id="responseJsonBlock" class="bg-dark text-light p-3 rounded-3 flex-grow-1 font-monospace" style="max-height: 600px; overflow-y: auto; font-size: 11.5px; border: 1px solid #343a40;"><code><?= esc($formattedJson) ?></code></pre>
            <?php else: ?>
              <div class="text-center py-5 my-auto text-muted">
                <i class="fas fa-box-open fa-3x mb-3 text-secondary opacity-50"></i>
                <p class="mb-0 fw-semibold">Belum ada respons data tersimpan.</p>
                <small>Respons payload JSON akan muncul di sini setelah scraping berhasil dieksekusi oleh Apify Actor.</small>
              </div>
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
function copyText(text) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(function() {
      alert('Teks berhasil disalin ke clipboard!');
    });
  } else {
    const temp = $('<textarea>');
    $('body').append(temp);
    temp.val(text).select();
    document.execCommand('copy');
    temp.remove();
    alert('Teks berhasil disalin!');
  }
}

function copyResponsePayload() {
  const code = $('#responseJsonBlock').text();
  copyText(code);
}

function retryJob(id) {
  if (!confirm('Apakah Anda yakin ingin memasukkan kembali Job #' + id + ' ke antrian (QUEUED)?')) return;

  $.ajax({
    url: '<?= base_url('apify-jobs/retry') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        location.reload();
      } else {
        alert(res.message || 'Gagal re-queue job');
      }
    },
    error: function(xhr) {
      alert('Terjadi kesalahan sistem saat mencoba re-queue job.');
    }
  });
}

function deleteJob(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus data Job #' + id + '? Tindakan ini tidak dapat dibatalkan.')) return;

  $.ajax({
    url: '<?= base_url('apify-jobs/delete') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        window.location.href = '<?= base_url('apify-jobs') ?>';
      } else {
        alert(res.message || 'Gagal menghapus job');
      }
    },
    error: function(xhr) {
      alert('Terjadi kesalahan saat menghapus job.');
    }
  });
}
</script>
<?= $this->endSection() ?>
