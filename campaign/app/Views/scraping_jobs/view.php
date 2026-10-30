<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-spider text-danger me-2"></i>Detail Scraping Job #<?= esc($job['id']) ?>
        </h3>
        <p class="text-muted small mb-0">Informasi eksekusi, metadata target, dan payload response scraping Apify.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('scraping-jobs') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button type="button" class="btn btn-warning btn-sm rounded-2 px-3 me-2 shadow-sm" onclick="retryJob(<?= (int)$job['id'] ?>)">
          <i class="fas fa-redo me-1"></i> Re-Queue
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 shadow-sm" onclick="confirmDelete(<?= (int)$job['id'] ?>)">
          <i class="fas fa-trash-alt me-1"></i> Hapus
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

    <!-- Error Alert if Job Failed -->
    <?php if (!empty($job['error_message']) || $job['status'] === 'FAILED'): ?>
      <div class="alert alert-danger rounded-3 shadow-sm border-0 mb-4 p-3">
        <div class="d-flex align-items-start">
          <div class="me-3 fs-3 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="flex-grow-1">
            <h6 class="fw-bold mb-1">Scraping Job Gagal atau Menghadapi Masalah</h6>
            <div class="font-monospace small bg-white text-danger border border-danger-subtle p-2 rounded-2 mt-2">
              <?= esc($job['error_message'] ?: 'Unknown Apify runner exception or timeout.') ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <!-- Card 1: Execution Details -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-cogs text-primary me-2"></i>Status &amp; Metadata Eksekusi
            </h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr>
                  <td class="bg-light fw-semibold text-secondary" style="width: 35%;">Job ID</td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1 fs-6">
                      #<?= esc($job['id']) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Status</td>
                  <td>
                    <?php if ($job['status'] === 'SUCCEEDED'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                        <i class="fas fa-check-circle me-1"></i>SUCCEEDED
                      </span>
                    <?php elseif ($job['status'] === 'RUNNING'): ?>
                      <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>RUNNING
                      </span>
                    <?php elseif ($job['status'] === 'FAILED'): ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">
                        <i class="fas fa-exclamation-triangle me-1"></i>FAILED
                      </span>
                    <?php else: ?>
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1">
                        <i class="far fa-clock me-1"></i>QUEUED
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">External Run ID</td>
                  <td>
                    <div class="d-flex align-items-center justify-content-between">
                      <span class="font-monospace text-dark small">
                        <?= esc($job['external_run_id'] ?: '-') ?>
                      </span>
                      <?php if (!empty($job['external_run_id'])): ?>
                        <button class="btn btn-outline-secondary btn-sm py-0 px-2 rounded" onclick="copyText('<?= esc($job['external_run_id']) ?>')" title="Salin Run ID">
                          <i class="far fa-copy"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Attempt Number</td>
                  <td>
                    <span class="badge bg-light text-dark border">
                      Percobaan Ke-<?= esc($job['attempt_number'] ?: 1) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Started At</td>
                  <td>
                    <span class="text-muted small">
                      <?= !empty($job['started_at']) ? date('d M Y H:i:s', strtotime($job['started_at'])) : '<span class="fst-italic text-muted">Belum dimulai</span>' ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Finished At</td>
                  <td>
                    <span class="text-muted small">
                      <?= !empty($job['finished_at']) ? date('d M Y H:i:s', strtotime($job['finished_at'])) : '<span class="fst-italic text-muted">-</span>' ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Durasi Pengerjaan</td>
                  <td>
                    <?php
                    if (!empty($job['started_at']) && !empty($job['finished_at'])) {
                        $diff = strtotime($job['finished_at']) - strtotime($job['started_at']);
                        if ($diff < 60) {
                            echo '<span class="badge bg-primary-subtle text-primary">' . $diff . ' detik</span>';
                        } else {
                            echo '<span class="badge bg-primary-subtle text-primary">' . round($diff / 60, 1) . ' menit</span>';
                        }
                    } else {
                        echo '<span class="text-muted small">-</span>';
                    }
                    ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Waktu Dibuat</td>
                  <td>
                    <span class="text-muted small">
                      <?= date('d M Y H:i:s', strtotime($job['created_at'])) ?>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Card 2: Target & Business Context -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-bullseye text-danger me-2"></i>Target &amp; Relasi Kampanye
            </h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr>
                  <td class="bg-light fw-semibold text-secondary" style="width: 35%;">Tipe Scraping</td>
                  <td>
                    <?php if ($job['actor_type'] === 'post'): ?>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="fas fa-play me-1"></i>Video Content Post
                      </span>
                    <?php else: ?>
                      <span class="badge bg-purple-subtle text-purple border border-purple-subtle px-2 py-1" style="background-color:#ede7f6;color:#673ab7;border-color:#d1c4e9;">
                        <i class="fas fa-user-circle me-1"></i>KOL Profile
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Target URL</td>
                  <td>
                    <div class="text-break font-monospace small">
                      <a href="<?= esc($job['target_url']) ?>" target="_blank" class="text-primary text-decoration-none fw-semibold">
                        <?= esc($job['target_url']) ?> <i class="fas fa-external-link-alt ms-1 small"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Posting Relasi</td>
                  <td>
                    <?php if (!empty($job['posting_id'])): ?>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">Posting #<?= esc($job['posting_id']) ?></span>
                        <?php if (!empty($job['postings_live_url'])): ?>
                          <a href="<?= esc($job['postings_live_url']) ?>" target="_blank" class="small text-truncate d-inline-block text-secondary" style="max-width: 250px;">
                            <?= esc($job['postings_live_url']) ?>
                          </a>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <span class="text-muted small fst-italic">Standalone Target (Tidak terhubung ke posting)</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">KOL Name</td>
                  <td>
                    <?php if (!empty($job['kol_name'])): ?>
                      <div class="fw-semibold text-dark">
                        <i class="fas fa-user-check text-success me-1"></i><?= esc($job['kol_name']) ?>
                      </div>
                      <?php if (!empty($job['kol_id'])): ?>
                        <a href="<?= site_url('kols/view/' . $job['kol_id']) ?>" class="small text-primary text-decoration-none">
                          Lihat Profil Talent <i class="fas fa-chevron-right small"></i>
                        </a>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Kampanye</td>
                  <td>
                    <?php if (!empty($job['campaign_title'])): ?>
                      <div class="fw-semibold text-dark">
                        <i class="fas fa-bullhorn text-primary me-1"></i><?= esc($job['campaign_title']) ?>
                      </div>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Platform</td>
                  <td>
                    <?php
                    $url = strtolower($job['target_url'] ?? '');
                    if (strpos($url, 'tiktok') !== false) {
                        echo '<span class="badge bg-dark text-white"><i class="fab fa-tiktok me-1"></i>TikTok</span>';
                    } elseif (strpos($url, 'instagram') !== false) {
                        echo '<span class="badge text-white" style="background:#E1306C;"><i class="fab fa-instagram me-1"></i>Instagram</span>';
                    } elseif (strpos($url, 'youtube') !== false || strpos($url, 'youtu.be') !== false) {
                        echo '<span class="badge bg-danger text-white"><i class="fab fa-youtube me-1"></i>YouTube</span>';
                    } else {
                        echo '<span class="badge bg-secondary text-white"><i class="fas fa-globe me-1"></i>Web</span>';
                    }
                    ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 3: Response Data / JSON Viewer -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">
          <i class="fas fa-code text-info me-2"></i>Apify Response Payload (Data Terstruktur)
        </h6>
        <?php if (!empty($formattedJson)): ?>
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" onclick="copyJsonData()">
            <i class="far fa-copy me-1"></i> Salin JSON
          </button>
        <?php endif; ?>
      </div>
      <div class="card-body p-3">
        <?php if (!empty($formattedJson)): ?>
          <pre id="jsonViewer" class="bg-dark text-light p-3 rounded-3 font-monospace small mb-0" style="max-height: 500px; overflow-y: auto;"><?= esc($formattedJson) ?></pre>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="fas fa-database text-muted fa-3x mb-3 opacity-50"></i>
            <h6 class="text-muted fw-bold">Belum Ada Response Data</h6>
            <p class="text-muted small mb-0">Payload response akan muncul di sini setelah scraping job selesai dieksekusi dengan status SUCCEEDED.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function copyText(str) {
  navigator.clipboard.writeText(str).then(function() {
    alert('Disalin: ' + str);
  });
}

function copyJsonData() {
  const el = document.getElementById('jsonViewer');
  if (el) {
    navigator.clipboard.writeText(el.innerText).then(function() {
      alert('Response JSON berhasil disalin ke clipboard!');
    });
  }
}

function retryJob(jobId) {
  if (!confirm('Re-queue job #' + jobId + ' untuk dijalankan ulang?')) return;

  fetch('<?= site_url('scraping-jobs/retry') ?>/' + jobId, {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Terjadi kesalahan jaringan.');
  });
}

function confirmDelete(jobId) {
  if (!confirm('Apakah Anda yakin ingin menghapus scraping job #' + jobId + '?')) return;

  fetch('<?= site_url('scraping-jobs/delete') ?>/' + jobId, {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      window.location.href = '<?= site_url('scraping-jobs') ?>';
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Terjadi kesalahan saat menghapus job.');
  });
}
</script>
<?= $this->endSection() ?>
