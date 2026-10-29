<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-spider text-danger me-2"></i>Monitoring Scraping &amp; Apify Jobs
        </h3>
        <p class="text-muted small mb-0">Antrian dan riwayat penarikan otomatis metrik video konten, engagement, dan profil KOL.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadJobTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalNewJob()">
          <i class="fas fa-plus me-1"></i> Trigger Scrape Baru
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Stat Cards Overview -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-tasks fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Jobs</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                <i class="far fa-clock fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Queued (Antri)</span>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['queued']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-spinner fa-spin fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Running (Proses)</span>
                <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['running']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-check-circle fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Succeeded</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['succeeded']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3">
                <i class="fas fa-exclamation-triangle fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Failed (Gagal)</span>
                <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['failed']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-0 py-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Status</label>
            <select class="form-select form-select-sm" id="filterStatus" onchange="reloadJobTable()">
              <option value="">-- Semua Status --</option>
              <option value="QUEUED">QUEUED (Dalam Antrian)</option>
              <option value="RUNNING">RUNNING (Sedang Berjalan)</option>
              <option value="SUCCEEDED">SUCCEEDED (Berhasil)</option>
              <option value="FAILED">FAILED (Gagal)</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Tipe Target</label>
            <select class="form-select form-select-sm" id="filterActorType" onchange="reloadJobTable()">
              <option value="">-- Semua Tipe --</option>
              <option value="post">Video Postingan</option>
              <option value="profile">Profil KOL</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableScrapingJobs" style="width: 100%;">
            <thead class="bg-light text-secondary border-bottom">
              <tr>
                <th style="width: 70px;">ID</th>
                <th style="width: 120px;">Tipe</th>
                <th>Target URL &amp; Platform</th>
                <th>KOL &amp; Kampanye</th>
                <th style="width: 130px;">Status</th>
                <th style="width: 150px;">Waktu Eksekusi</th>
                <th style="width: 100px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Trigger Scrape Baru -->
<div class="modal fade" id="modalNewScrape" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formNewScrape" onsubmit="submitNewScrape(event)">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-spider me-2"></i>Trigger Scraping Job Baru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipe Scrape <span class="text-danger">*</span></label>
            <select class="form-select" id="newActorType" name="actor_type" required onchange="togglePostingSelect()">
              <option value="post">Video Postingan Konten (TikTok / IG / YouTube)</option>
              <option value="profile">Profil Akun KOL (Followers, Engagement, Bio)</option>
            </select>
          </div>

          <div class="mb-3" id="groupPostingSelect">
            <label class="form-label fw-semibold">Pilih dari Video Post Terdaftar</label>
            <select class="form-select" id="newPostingId" name="posting_id" onchange="autoFillUrl()">
              <option value="">-- Input URL Manual / Standalone --</option>
              <?php foreach ($postings as $p): ?>
                <option value="<?= $p['id'] ?>" data-url="<?= esc($p['live_url']) ?>">
                  #<?= $p['id'] ?> <?= esc($p['kol_name']) ?> — <?= esc($p['campaign_title']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Memilih postingan otomatis menghubungkan metrik hasil scraping ke campaign terkait.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Target URL <span class="text-danger">*</span></label>
            <input type="url" class="form-control font-monospace" id="newTargetUrl" name="target_url" placeholder="https://www.tiktok.com/@user/video/..." required>
            <small class="text-muted">Mendukung link video TikTok, Instagram Reels / Post, dan YouTube Shorts/Video.</small>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger rounded-2 px-4" id="btnSubmitScrape">
            <i class="fas fa-paper-plane me-1"></i> Masukkan Antrian
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detail JSON Data -->
<div class="modal fade" id="modalJobData" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="jobDataTitle"><i class="fas fa-code text-info me-2"></i>Response Data Scraping (JSON)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted small" id="jobDataMeta">Memuat status...</span>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyJsonData()">
            <i class="fas fa-copy me-1"></i> Salin JSON
          </button>
        </div>
        <pre class="bg-light border rounded-3 p-3 font-monospace small mb-0" style="max-height: 420px; overflow-y: auto;" id="jobDataPre">Memuat response data...</pre>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtScraping = null;
let currentJsonRaw = '';

$(document).ready(function() {
    dtScraping = $('#tableScrapingJobs').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('scraping-jobs/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.status     = $('#filterStatus').val();
                d.actor_type = $('#filterActorType').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center' },
            { data: 'type' },
            { data: 'target_url' },
            { data: 'context' },
            { data: 'status', className: 'text-center' },
            { data: 'created_at' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari URL, status, KOL...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ jobs",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadJobTable() {
    dtScraping.ajax.reload();
}

function modalNewJob() {
    $('#formNewScrape')[0].reset();
    togglePostingSelect();
    $('#modalNewScrape').modal('show');
}

function togglePostingSelect() {
    const type = $('#newActorType').val();
    if (type === 'profile') {
        $('#groupPostingSelect').slideUp();
        $('#newPostingId').val('');
        $('#newTargetUrl').attr('placeholder', 'https://www.tiktok.com/@username atau https://instagram.com/username');
    } else {
        $('#groupPostingSelect').slideDown();
        $('#newTargetUrl').attr('placeholder', 'https://www.tiktok.com/@user/video/...');
    }
}

function autoFillUrl() {
    const selected = $('#newPostingId option:selected');
    const url = selected.data('url');
    if (url) {
        $('#newTargetUrl').val(url);
    }
}

function submitNewScrape(e) {
    e.preventDefault();
    const btn = $('#btnSubmitScrape');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

    $.post('<?= base_url('scraping-jobs/trigger') ?>', $('#formNewScrape').serialize(), function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Masukkan Antrian');
        if (res.success) {
            $('#modalNewScrape').modal('hide');
            dtScraping.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Masukkan Antrian');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function viewJobData(id) {
    $('#jobDataPre').text('Memuat response data...');
    $('#jobDataMeta').text('Job #' + id);
    $('#modalJobData').modal('show');

    $.get('<?= base_url('scraping-jobs/get-one/') ?>' + id, function(res) {
        if (res.success && res.data) {
            const row = res.data;
            $('#jobDataMeta').text('Job #' + row.id + ' • Status: ' + row.status + ' • Waktu: ' + (row.finished_at || row.created_at));
            if (row.response_data) {
                try {
                    const parsed = JSON.parse(row.response_data);
                    currentJsonRaw = JSON.stringify(parsed, null, 2);
                    $('#jobDataPre').text(currentJsonRaw);
                } catch(e) {
                    currentJsonRaw = row.response_data;
                    $('#jobDataPre').text(row.response_data);
                }
            } else {
                $('#jobDataPre').text('Tidak ada response data payload untuk job ini.');
            }
        }
    }).fail(function() {
        $('#jobDataPre').text('Gagal memuat detail data.');
    });
}

function retryJob(id) {
    Swal.fire({
        title: 'Scrape Ulang?',
        text: 'Job #' + id + ' akan dikembalikan ke status antrian (QUEUED) untuk dieksekusi ulang.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Jalankan Ulang',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('scraping-jobs/retry/') ?>' + id, {}, function(res) {
                if (res.success) {
                    dtScraping.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
                }
            }).fail(function() {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan pada server' });
            });
        }
    });
}

function copyJsonData() {
    if (!currentJsonRaw) return;
    navigator.clipboard.writeText(currentJsonRaw).then(function() {
        Swal.fire({ icon: 'success', title: 'Tersalin', text: 'JSON berhasil disalin ke clipboard!', timer: 1200, showConfirmButton: false });
    });
}
</script>
<?= $this->endSection() ?>
