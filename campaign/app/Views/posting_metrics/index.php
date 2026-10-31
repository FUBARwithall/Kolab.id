<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Posting Metrics &amp; Performance Snapshots</h3>
        <p class="text-muted small mb-0">Riwayat snapshot berkala performa video live KOL (Views, Likes, Comments, Shares, ER, dan CPV).</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('postings') ?>" class="text-decoration-none">Postings</a></li>
          <li class="breadcrumb-item active" aria-current="page">Posting Metrics</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-primary">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Snapshots</span>
                <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format((int)($metrics['total_snapshots'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                <i class="fas fa-camera fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-info">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Views Terkumpul</span>
                <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format((int)($metrics['total_views'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                <i class="fas fa-eye fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-success">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Rata-rata ER (%)</span>
                <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format((float)($metrics['avg_er'] ?? 0), 2) ?>%</h3>
              </div>
              <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                <i class="fas fa-percentage fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-warning">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Rata-rata CPV</span>
                <h3 class="fw-bold mb-0 text-warning mt-1">Rp <?= number_format((float)($metrics['avg_cpv'] ?? 0), 2, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                <i class="fas fa-coins fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
          
          <!-- Filters -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div style="min-width: 220px;">
              <select class="form-select form-select-sm rounded-2" id="filterCampaignMetric" onchange="reloadMetricTable()">
                <option value="">Semua Kampanye</option>
                <?php foreach ($allCampaigns as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 240px;">
              <select class="form-select form-select-sm rounded-2" id="filterPostingMetric" onchange="reloadMetricTable()">
                <option value="">Semua Posting Live</option>
                <?php foreach ($allPostings as $p): ?>
                  <option value="<?= $p['id'] ?>">#<?= $p['id'] ?> - <?= esc($p['kol_name']) ?> [<?= esc(substr($p['live_url'], 0, 30)) ?>...]</option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-2" title="Reset Filter" onclick="resetMetricFilters()">
              <i class="fas fa-undo"></i>
            </button>
          </div>

          <!-- Actions & Exports -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('postings') ?>" class="btn btn-outline-primary btn-sm rounded-2 px-3">
              <i class="fas fa-video me-1"></i> Data Postings
            </a>
            <div class="btn-group">
              <a href="<?= base_url('posting-metrics/export') ?>" class="btn btn-outline-success btn-sm rounded-2">
                <i class="fas fa-file-excel me-1"></i> Excel / CSV
              </a>
              <a href="<?= base_url('posting-metrics/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2">
                <i class="fas fa-file-pdf me-1"></i> Print / PDF
              </a>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 d-none" id="btnBulkDelete" onclick="bulkDeleteSelected()">
              <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="bulkCount">0</span>)
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tablePostingMetrics" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllMetrics" onchange="toggleSelectAll(this)">
                </th>
                <th>Link Posting Live &amp; Target</th>
                <th>Kampanye &amp; Task</th>
                <th>Talent KOL</th>
                <th style="width: 100px;">Views</th>
                <th style="width: 80px;">Likes</th>
                <th style="width: 80px;">Comments</th>
                <th style="width: 80px;">Shares</th>
                <th style="width: 90px;" class="text-center">ER (%)</th>
                <th style="width: 100px;">CPV</th>
                <th style="width: 130px;">Waktu Snapshot</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal View Detail Metric Snapshot -->
<div class="modal fade" id="modalViewMetric" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewMetricTitle"><i class="fas fa-chart-bar text-primary me-2"></i>Detail Snapshot Metrik</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewMetricBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light justify-content-between">
        <div id="viewMetricActions"></div>
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtMetrics = null;

$(document).ready(function() {
    dtMetrics = $('#tablePostingMetrics').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('posting-metrics/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignMetric').val();
                d.posting_id  = $('#filterPostingMetric').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'posting' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'views', className: 'text-end fw-bold' },
            { data: 'likes', className: 'text-end' },
            { data: 'comments', className: 'text-end' },
            { data: 'shares', className: 'text-end' },
            { data: 'er', className: 'text-center' },
            { data: 'cpv', className: 'text-end' },
            { data: 'snapshot' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[10, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari URL, kampanye, KOL, task...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ snapshot",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            updateBulkDeleteButton();
            $('.metric-row-check').on('change', updateBulkDeleteButton);
        }
    });
});

function reloadMetricTable() {
    dtMetrics.ajax.reload();
}

function resetMetricFilters() {
    $('#filterCampaignMetric').val('');
    $('#filterPostingMetric').val('');
    reloadMetricTable();
}

function toggleSelectAll(master) {
    $('.metric-row-check').prop('checked', master.checked);
    updateBulkDeleteButton();
}

function updateBulkDeleteButton() {
    const selected = $('.metric-row-check:checked');
    const count = selected.length;
    $('#bulkCount').text(count);
    if (count > 0) {
        $('#btnBulkDelete').removeClass('d-none');
    } else {
        $('#btnBulkDelete').addClass('d-none');
        $('#checkAllMetrics').prop('checked', false);
    }
}

function viewMetricModal(id) {
    $('#modalViewMetric').modal('show');
    $('#viewMetricBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
    $('#viewMetricActions').html('');

    $.get('<?= base_url('posting-metrics/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const m = res.data;
            const liveUrl = m.postings_live_url || '#';
            const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
            const fmtDec = (n) => Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            $('#viewMetricBody').html(`
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Snapshot ID #${m.id}</h5>
                        <div class="text-muted small">Kampanye: <strong>${m.campaign_title || '-'}</strong> &bull; Talent: <strong>${m.kol_name || '-'}</strong> &bull; Task: <strong>${m.task_title || ('VT #' + m.task_number)}</strong></div>
                    </div>
                    <span class="badge bg-primary px-3 py-2 fs-6">Snapshot: ${m.snapshot_at || m.created_at}</span>
                </div>
                
                <div class="p-3 bg-light border rounded-3 mb-4">
                    <strong class="text-dark d-block mb-1"><i class="fas fa-link text-primary me-1"></i>URL Postingan Video Live:</strong>
                    <a href="${liveUrl}" target="_blank" class="text-decoration-underline text-primary small text-break">${liveUrl} <i class="fas fa-external-link-alt ms-1"></i></a>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-white text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Views</span>
                            <h4 class="fw-bold text-primary mb-0 mt-1">${fmt(m.resolved_views)}</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-white text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Likes</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">${fmt(m.resolved_likes)}</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-white text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Comments</span>
                            <h4 class="fw-bold text-info mb-0 mt-1">${fmt(m.resolved_comments)}</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-white text-center">
                            <span class="text-muted small text-uppercase fw-semibold">Shares</span>
                            <h4 class="fw-bold text-warning mb-0 mt-1">${fmt(m.resolved_shares)}</h4>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-white">
                            <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-percentage text-success me-2"></i>Efisiensi &amp; Engagement</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Engagement Rate</td>
                                    <td class="fw-bold text-success text-end">${fmtDec(m.engagement_rate)}%</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">View Rate</td>
                                    <td class="fw-bold text-dark text-end">${fmtDec(m.view_rate)}%</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Actual CPV</td>
                                    <td class="fw-bold text-primary text-end">Rp ${fmtDec(m.resolved_cpv)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Actual CPA</td>
                                    <td class="fw-bold text-dark text-end">Rp ${fmtDec(m.cpa)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-white">
                            <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-users text-primary me-2"></i>Audiens &amp; Jangkauan</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Followers Talent</td>
                                    <td class="fw-bold text-dark text-end">${fmt(m.followers)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Impressions</td>
                                    <td class="fw-bold text-dark text-end">${fmt(m.impressions)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Reach</td>
                                    <td class="fw-bold text-dark text-end">${fmt(m.reach)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Saves</td>
                                    <td class="fw-bold text-dark text-end">${fmt(m.resolved_saves)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            `);

            $('#viewMetricActions').html(`
                <a href="<?= base_url('posting-metrics/view/') ?>${m.id}" class="btn btn-outline-info btn-sm rounded-2"><i class="fas fa-external-link-alt me-1"></i>Halaman Penuh</a>
                <a href="<?= base_url('posting-metrics/single-pdf/') ?>${m.id}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2"><i class="fas fa-print me-1"></i>Cetak PDF</a>
            `);
        }
    });
}

function deleteMetric(id) {
    Swal.fire({
        title: 'Hapus Snapshot ini?',
        text: 'Data snapshot performa ini akan dihapus secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('posting-metrics/delete/') ?>' + id, function(res) {
                if (res.success) {
                    dtMetrics.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Dihapus', text: res.message, timer: 1500, showConfirmButton: false });
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus snapshot.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err });
            });
        }
    });
}

function bulkDeleteSelected() {
    const selected = $('.metric-row-check:checked');
    const ids = [];
    selected.each(function() { ids.push($(this).val()); });

    if (ids.length === 0) return;

    Swal.fire({
        title: `Hapus ${ids.length} snapshot terpilih?`,
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('posting-metrics/bulk-delete') ?>', { ids: ids }, function(res) {
                if (res.success) {
                    dtMetrics.ajax.reload(null, false);
                    $('#checkAllMetrics').prop('checked', false);
                    updateBulkDeleteButton();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus bulk snapshot.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err });
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
