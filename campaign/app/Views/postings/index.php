<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar text-success me-2"></i>Postings &amp; Analytics Hub</h3>
        <p class="text-muted small mb-0">Pemantauan performa video live secara aktual: views, likes, shares, engagement rate (ER), CPV, dan analisis sentimen IndoBERT.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= base_url('postings/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" id="btnExportCsv" title="Export CSV Data Postings">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" onclick="reloadPostingTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- KPI Metrics Row -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-video fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Postingan Live</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['total_postings'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-eye fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Video Views</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['total_views'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="fas fa-chart-line fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Rata-rata ER</div>
              <h4 class="mb-0 fw-bold text-dark"><?= $metrics['avg_er'] ?? 0 ?>%</h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
              <i class="fas fa-heart fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Likes &amp; Komen</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format(($metrics['total_likes'] ?? 0) + ($metrics['total_comments'] ?? 0)) ?></h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Card & DataTables -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
          
          <div class="d-flex flex-wrap align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="filterCampaignPost" style="min-width: 200px;" onchange="reloadPostingTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title'] ?: $c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterPlatformPost" style="min-width: 140px;" onchange="reloadPostingTable()">
              <option value="">Semua Platform</option>
              <?php foreach ($platforms as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterStatusPost" style="min-width: 130px;" onchange="reloadPostingTable()">
              <option value="">Semua Status</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="TAKEN_DOWN">TAKEN DOWN</option>
              <option value="DELETED">DELETED</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 d-none" id="btnBulkDelete" onclick="handleBulkDelete()">
              <i class="fas fa-trash me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
            <div class="text-muted small">
              <i class="fas fa-info-circle me-1 text-primary"></i> IndoBERT otomatis mengukur kepuasan penonton video.
            </div>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tablePostings" style="width: 100%;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th style="width: 35px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th style="width: 45px;" class="text-center">#</th>
                <th>Konten VT &amp; Link Post</th>
                <th>Kampanye</th>
                <th>Talent KOL</th>
                <th style="width: 100px;">Views</th>
                <th style="width: 130px;">Engagement</th>
                <th style="width: 110px;">CPV Aktual</th>
                <th style="width: 90px;" class="text-center">Sentimen</th>
                <th style="width: 90px;" class="text-center">Status</th>
                <th style="width: 110px;">Waktu Tayang</th>
                <th style="width: 130px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal View Detail Metrik & Sentimen -->
<div class="modal fade" id="modalViewPosting" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewPostingTitle"><i class="fas fa-chart-line text-success me-2"></i>Detail Performa &amp; Sentimen Konten</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewPostingBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
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
          <input type="hidden" id="upPostingId" name="posting_id">
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah Tayangan (Views) <span class="text-danger">*</span></label>
            <input type="number" class="form-control fs-5 fw-bold" id="metricViews" name="views_count" required min="0" placeholder="0">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Likes</label>
              <input type="number" class="form-control" id="metricLikes" name="likes_count" min="0" placeholder="0">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Komentar</label>
              <input type="number" class="form-control" id="metricComments" name="comments_count" min="0" placeholder="0">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Shares (Bagikan)</label>
              <input type="number" class="form-control" id="metricShares" name="shares_count" min="0" placeholder="0">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Saves / Bookmark</label>
              <input type="number" class="form-control" id="metricSaves" name="saves_count" min="0" placeholder="0">
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
<script>
let dtPostings = null;

$(document).ready(function() {
    dtPostings = $('#tablePostings').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('postings/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignPost').val();
                d.platform_id = $('#filterPlatformPost').val();
                d.status      = $('#filterStatusPost').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'url' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'views' },
            { data: 'er' },
            { data: 'cpv' },
            { data: 'sentiment', className: 'text-center' },
            { data: 'status', className: 'text-center' },
            { data: 'posted_at' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari postingan, URL, KOL...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ postingan",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            $('#checkAll').prop('checked', false);
            updateBulkDeleteButton();
        }
    });

    // Handle Check All
    $('#checkAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Handle Individual Row Checkbox
    $(document).on('change', '.row-checkbox', function() {
        const total = $('.row-checkbox').length;
        const checked = $('.row-checkbox:checked').length;
        $('#checkAll').prop('checked', total > 0 && total === checked);
        updateBulkDeleteButton();
    });

    // Single Delete Handler
    $(document).on('click', '.btn-delete-posting', function() {
        const id = $(this).data('id');
        const name = $(this).data('name') || ('Postingan #' + id);

        Swal.fire({
            title: 'Hapus Postingan?',
            html: `Apakah Anda yakin ingin menghapus data <strong>${name}</strong>? Seluruh snapshot performa terkait juga akan dibersihkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('postings/delete') ?>/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            reloadPostingTable();
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memproses penghapusan data postingan.', 'error');
                    }
                });
            }
        });
    });

    // Trigger Scrape Handler
    $(document).on('click', '.btn-trigger-scrape', function() {
        const id = $(this).data('id');
        const $btn = $(this);

        Swal.fire({
            title: 'Memicu Scraping Apify?',
            text: 'Sistem akan mengeksekusi scraper dan mencatat snapshot performa baru untuk postingan ini.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-sync-alt me-1"></i> Ya, Scrape Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sedang Scraping...',
                    text: 'Mengambil metrik dan memperbarui statistik...',
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
                            });
                            reloadPostingTable();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
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

function updateBulkDeleteButton() {
    const count = $('.row-checkbox:checked').length;
    $('#selectedCount').text(count);
    if (count > 0) {
        $('#btnBulkDelete').removeClass('d-none');
    } else {
        $('#btnBulkDelete').addClass('d-none');
    }
}

function handleBulkDelete() {
    const selectedIds = [];
    $('.row-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu postingan untuk dihapus.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Hapus ' + selectedIds.length + ' Postingan?',
        text: 'Semua postingan terpilih beserta riwayat performanya akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus Semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('postings/bulk-delete') ?>',
                type: 'POST',
                data: { ids: selectedIds },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadPostingTable();
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal menghapus postingan secara massal.', 'error');
                }
            });
        }
    });
}

function reloadPostingTable() {
    if (dtPostings) {
        dtPostings.ajax.reload();
    }
}

function updateMetricsModal(id) {
    $.get('<?= base_url('postings/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const p = res.data;
            $('#upPostingId').val(p.id);
            $('#metricViews').val(p.views_count || 0);
            $('#metricLikes').val(p.likes_count || 0);
            $('#metricComments').val(p.comments_count || 0);
            $('#metricShares').val(p.shares_count || 0);
            $('#metricSaves').val(p.saves_count || 0);
            $('#modalUpdateMetrics').modal('show');
        }
    });
}

function savePostingMetrics(e) {
    e.preventDefault();
    const id = $('#upPostingId').val();
    const btn = $('#btnSaveMetrics');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

    $.post('<?= base_url('postings/update-metrics/') ?>' + id, $('#formMetrics').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan Metrik');
        if (res.success) {
            $('#modalUpdateMetrics').modal('hide');
            reloadPostingTable();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan Metrik');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function runSentimentAnalysis(taskId) {
    Swal.fire({
        title: 'Menjalankan IndoBERT Analyzer',
        text: 'Mengambil sampel komentar video dan menganalisis polaritas sentimen...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.post('<?= base_url('postings/analyze-sentiment/') ?>' + taskId, function(res) {
        if (res.success) {
            reloadPostingTable();
            Swal.fire({ icon: 'success', title: 'Analisis Selesai', text: `Skor Sentimen Positif: ${res.score}%`, timer: 2000, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghubungi microservice sentimen';
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: err });
    });
}
</script>
<?= $this->endSection() ?>