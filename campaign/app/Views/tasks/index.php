<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i>Tasks Engine</h3>
        <p class="text-muted small mb-0">Pengendali alur kerja operasional 12-state: dari briefing, produksi, review video, hingga penayangan live VT.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tasks</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-list-ul fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Seluruh Tugas</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['total'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-secondary-subtle text-secondary p-3 me-3">
              <i class="fas fa-file-alt fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Draft / Persiapan SOW</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['draft'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="fas fa-video fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Review Draft Video</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['draft_in_review'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-globe fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Postingan Tayang Live</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['live_posted'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter and Main Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <!-- Stage Filter Pills -->
          <ul class="nav nav-pills gap-1" id="stageTabs">
            <li class="nav-item">
              <button class="nav-link <?= ($stage === 'all' || empty($stage)) ? 'active' : '' ?> py-1 px-3 rounded-pill" onclick="filterStage('all')">
                <i class="fas fa-layer-group me-1"></i> Semua Tugas
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link <?= ($stage === 'sow') ? 'active' : '' ?> py-1 px-3 rounded-pill" onclick="filterStage('sow')">
                <i class="fas fa-file-contract me-1"></i> SOW &amp; Briefing
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link <?= ($stage === 'draft') ? 'active' : '' ?> py-1 px-3 rounded-pill" onclick="filterStage('draft')">
                <i class="fas fa-video me-1"></i> Review Draft
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link <?= ($stage === 'posting') ? 'active' : '' ?> py-1 px-3 rounded-pill" onclick="filterStage('posting')">
                <i class="fas fa-paper-plane me-1"></i> Live Posting
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link <?= ($stage === 'completed') ? 'active' : '' ?> py-1 px-3 rounded-pill" onclick="filterStage('completed')">
                <i class="fas fa-check-double me-1"></i> Selesai
              </button>
            </li>
          </ul>

          <!-- Campaign Filter Dropdown -->
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="filterCampaign" style="min-width: 200px;" onchange="reloadTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($campaignId == $c['id']) ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableTasks" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Judul Tugas &amp; Target VT</th>
                <th>Kampanye</th>
                <th>Talent KOL</th>
                <th style="width: 140px;">Deadline</th>
                <th style="width: 150px;">Status Produksi</th>
                <th style="width: 140px;" class="text-center">Aksi Operasional</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Detail & Timeline Tugas -->
<div class="modal fade" id="modalTaskDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="taskDetailTitle"><i class="fas fa-tasks me-2"></i>Detail &amp; Timeline Tugas</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="taskDetailBody">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="text-muted mt-2">Memuat data tugas...</div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Atur Jadwal / Deadline -->
<div class="modal fade" id="modalSchedule" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formSchedule" onsubmit="saveSchedule(event)">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title fw-bold"><i class="far fa-calendar-alt text-primary me-2"></i>Atur Jadwal &amp; Judul Tugas</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="schedTaskId" name="id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Judul Video / SOW</label>
            <input type="text" class="form-control" id="schedTitle" name="title" required placeholder="Contoh: Video Review Pemakaian 7 Hari">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Target Deadline Posting</label>
            <input type="date" class="form-control" id="schedDeadline" name="deadline" required>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveSchedule">Simpan Jadwal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Input URL Postingan Live -->
<div class="modal fade" id="modalLiveUrl" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formLiveUrl" onsubmit="saveLiveUrl(event)">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-globe me-2"></i>Input URL Postingan Live</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="liveTaskId" name="task_id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Platform</label>
            <select class="form-select" id="livePlatform" name="platform" required>
              <option value="tiktok">TikTok Video</option>
              <option value="instagram">Instagram Reel / Post</option>
              <option value="youtube">YouTube Shorts / Video</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">URL Postingan Live</label>
            <input type="url" class="form-control" id="livePostUrl" name="post_url" required placeholder="https://www.tiktok.com/@username/video/...">
            <small class="text-muted">Pastikan URL publik dan dapat diakses untuk penarikan metrik otomatis.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Waktu Penayangan</label>
            <input type="datetime-local" class="form-control" id="livePostedAt" name="posted_at">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success rounded-2 px-4" id="btnSaveLiveUrl">Simpan &amp; Publish</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let currentStage = '<?= $stage ?>';
let dtTasks = null;

$(document).ready(function() {
    initDatatable();
});

function initDatatable() {
    dtTasks = $('#tableTasks').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('tasks/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.stage = currentStage;
                d.campaign_id = $('#filterCampaign').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'title' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'deadline' },
            { data: 'status' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari tugas, KOL, kampanye...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ tugas",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
}

function filterStage(stage) {
    currentStage = stage;
    $('#stageTabs .nav-link').removeClass('active');
    $(`#stageTabs button[onclick="filterStage('${stage}')"]`).addClass('active');
    dtTasks.ajax.reload();
}

function reloadTable() {
    dtTasks.ajax.reload();
}

function viewTaskDetails(id) {
    $('#modalTaskDetail').modal('show');
    $('#taskDetailBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><div class="text-muted mt-2">Memuat data tugas...</div></div>');

    $.get('<?= base_url('tasks/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const t = res.data;
            let draftsHtml = '';
            if (t.drafts && t.drafts.length > 0) {
                draftsHtml = t.drafts.map(d => `
                    <div class="border rounded-2 p-2 mb-2 bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary me-2">v${d.version}</span>
                            <a href="${d.video_url}" target="_blank" class="fw-semibold text-dark text-truncate d-inline-block" style="max-width: 320px;">${d.video_url}</a>
                            <div class="small text-muted">${d.review_notes || 'Tidak ada catatan'}</div>
                        </div>
                        <span class="badge ${d.status === 'APPROVED' ? 'bg-success' : (d.status === 'REVISION_NEEDED' ? 'bg-danger' : 'bg-warning')}">${d.status}</span>
                    </div>
                `).join('');
            } else {
                draftsHtml = '<div class="text-muted small fst-italic">Belum ada draft video diunggah</div>';
            }

            let liveHtml = '';
            if (t.posting) {
                liveHtml = `
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-0">
                        <div>
                            <div class="fw-bold"><i class="fas fa-check-circle me-1"></i>Postingan Tayang Live</div>
                            <a href="${t.posting.live_url}" target="_blank" class="small text-success text-decoration-underline">${t.posting.live_url}</a>
                        </div>
                        <div class="text-end small">
                            <div>Views: <strong>${t.posting_metrics ? Number(t.posting_metrics.views_count).toLocaleString('id-ID') : 0}</strong></div>
                            <div>Likes: <strong>${t.posting_metrics ? Number(t.posting_metrics.likes_count).toLocaleString('id-ID') : 0}</strong></div>
                        </div>
                    </div>
                `;
            } else {
                liveHtml = '<div class="text-muted small fst-italic">Belum ada URL postingan live</div>';
            }

            $('#taskDetailBody').html(`
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Kampanye</label>
                        <div class="fw-bold text-dark fs-6">${t.campaign_title}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Talent KOL</label>
                        <div class="fw-bold text-dark fs-6">${t.kol_name} (${t.kol_city || '-'})</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Target SOW</label>
                        <div class="fw-semibold text-dark">VT #${t.task_number} &bull; Rp ${Number(t.rate_amount).toLocaleString('id-ID')}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Deadline</label>
                        <div class="fw-semibold text-primary">${t.deadline ? new Date(t.deadline).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : 'Belum di-set'}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Status Produksi</label>
                        <div><span class="badge bg-primary">${t.status}</span></div>
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-video text-warning me-2"></i>Histori Draft Video</h6>
                ${draftsHtml}
                <hr>
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-globe text-success me-2"></i>Status Live &amp; Analytics</h6>
                ${liveHtml}
            `);
        }
    });
}

function setScheduleModal(id) {
    $.get('<?= base_url('tasks/get-one/') ?>' + id, function(res) {
        if (res.success) {
            $('#schedTaskId').val(res.data.id);
            $('#schedTitle').val(res.data.title || `Task #${res.data.task_number}`);
            $('#schedDeadline').val(res.data.deadline || '');
            $('#modalSchedule').modal('show');
        }
    });
}

function saveSchedule(e) {
    e.preventDefault();
    const id = $('#schedTaskId').val();
    const btn = $('#btnSaveSchedule');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('tasks/set-schedule/') ?>' + id, $('#formSchedule').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan Jadwal');
        if (res.success) {
            $('#modalSchedule').modal('hide');
            dtTasks.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan Jadwal');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function setLiveUrlModal(id) {
    $('#liveTaskId').val(id);
    $('#livePostUrl').val('');
    $('#livePostedAt').val(new Date().toISOString().slice(0, 16));
    $('#modalLiveUrl').modal('show');
}

function saveLiveUrl(e) {
    e.preventDefault();
    const taskId = $('#liveTaskId').val();
    const postUrl = $('#livePostUrl').val();
    const postedAt = $('#livePostedAt').val();
    const platform = $('#livePlatform').val();
    const btn = $('#btnSaveLiveUrl');

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('postings/save') ?>', {
        task_id: taskId,
        post_url: postUrl,
        posted_at: postedAt,
        platform: platform
    }, function(res) {
        btn.prop('disabled', false).text('Simpan & Publish');
        if (res.success) {
            $('#modalLiveUrl').modal('hide');
            dtTasks.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'URL Live berhasil disimpan dan status tugas diubah ke POSTED!', timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan & Publish');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}
</script>
<?= $this->endSection() ?>
