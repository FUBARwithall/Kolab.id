<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar text-success me-2"></i>Postings &amp; Analytics Hub</h3>
        <p class="text-muted small mb-0">Pemantauan performa video live secara aktual: views, likes, shares, engagement rate (ER), CPV, dan analisis sentimen IndoBERT.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Postings &amp; Analytics</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
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
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="filterCampaignPost" style="min-width: 220px;" onchange="reloadPostingTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterPlatformPost" style="min-width: 150px;" onchange="reloadPostingTable()">
              <option value="">Semua Platform</option>
              <?php foreach ($platforms as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="text-muted small">
            <i class="fas fa-info-circle me-1 text-primary"></i> Analisis sentimen IndoBERT menganalisis respons komentar publik.
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tablePostings" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Konten VT &amp; Link Post</th>
                <th>Kampanye</th>
                <th>Talent KOL</th>
                <th style="width: 110px;">Views</th>
                <th style="width: 150px;">Engagement</th>
                <th style="width: 110px;">CPV Aktual</th>
                <th style="width: 110px;">Sentimen</th>
                <th style="width: 110px;" class="text-center">Aksi</th>
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
            }
        },
        columns: [
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'url' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'views' },
            { data: 'er' },
            { data: 'cpv' },
            { data: 'sentiment', className: 'text-center' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari postingan, URL, KOL...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ postingan",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadPostingTable() {
    dtPostings.ajax.reload();
}

function viewPostingDetail(id) {
    $('#modalViewPosting').modal('show');
    $('#viewPostingBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

    $.get('<?= base_url('postings/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const p = res.data;
            const views = Number(p.views_count || 0);
            const likes = Number(p.likes_count || 0);
            const comments = Number(p.comments_count || 0);
            const shares = Number(p.shares_count || 0);
            const saves = Number(p.saves_count || 0);
            const interactions = likes + comments + shares + saves;
            const er = views > 0 ? ((interactions / views) * 100).toFixed(2) : '0.00';

            let sentimentHtml = '';
            if (p.sentiment_score !== null) {
                sentimentHtml = `
                    <div class="row g-2 text-center mt-2">
                        <div class="col-4">
                            <div class="border rounded-2 p-2 bg-success-subtle text-success">
                                <div class="small fw-semibold">Positif</div>
                                <h5 class="mb-0 fw-bold">${p.positive_count || 0}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-2 p-2 bg-warning-subtle text-warning">
                                <div class="small fw-semibold">Netral</div>
                                <h5 class="mb-0 fw-bold">${p.neutral_count || 0}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-2 p-2 bg-danger-subtle text-danger">
                                <div class="small fw-semibold">Negatif</div>
                                <h5 class="mb-0 fw-bold">${p.negative_count || 0}</h5>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                sentimentHtml = `
                    <div class="alert alert-light border text-center p-3">
                        <div class="text-muted small mb-2">Belum ada analisis sentimen IndoBERT untuk konten ini.</div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="runSentimentAnalysis(${p.task_id})"><i class="fas fa-brain me-1"></i>Jalankan IndoBERT Analyzer</button>
                    </div>
                `;
            }

            $('#viewPostingBody').html(`
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-dark">${p.platform_name || 'Video'}</span>
                    <a href="${p.live_url}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3"><i class="fas fa-external-link-alt me-1"></i>Buka Video Asli</a>
                </div>
                <h5 class="fw-bold text-dark mb-1">${p.task_title || ('Task #' + p.task_number)}</h5>
                <div class="text-muted small mb-3">Kampanye: <strong>${p.campaign_title}</strong> &bull; KOL: <strong>${p.kol_name}</strong></div>
                
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="card bg-light border-0 p-3 text-center">
                            <div class="text-muted small">Total Views</div>
                            <h4 class="fw-bold text-dark mb-0">${views.toLocaleString('id-ID')}</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light border-0 p-3 text-center">
                            <div class="text-muted small">Engagement Rate</div>
                            <h4 class="fw-bold text-success mb-0">${er}%</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light border-0 p-3 text-center">
                            <div class="text-muted small">Actual CPV</div>
                            <h4 class="fw-bold text-dark mb-0">Rp ${Number(p.actual_cpv || 0).toLocaleString('id-ID', {minimumFractionDigits: 2})}</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light border-0 p-3 text-center">
                            <div class="text-muted small">Interaksi Total</div>
                            <h4 class="fw-bold text-primary mb-0">${interactions.toLocaleString('id-ID')}</h4>
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 bg-white mb-3">
                    <h6 class="fw-bold mb-2"><i class="fas fa-brain text-primary me-2"></i>Analisis Sentimen Komentar (IndoBERT)</h6>
                    ${sentimentHtml}
                </div>
                <div class="text-muted small text-end">Terakhir diperbarui: ${p.last_scraped_at || '-'}</div>
            `);
        }
    });
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
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('postings/update-metrics/') ?>' + id, $('#formMetrics').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan Metrik');
        if (res.success) {
            $('#modalUpdateMetrics').modal('hide');
            dtPostings.ajax.reload(null, false);
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
            dtPostings.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Analisis Selesai', text: `Skor Sentimen Positif: ${res.score}%`, timer: 2000, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghubungi microservice sentimen';
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: err });
    });
}
</script>
<?= $this->endSection() ?>
