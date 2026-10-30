<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i>Tasks Engine</h3>
        <p class="text-muted small mb-0">Pengendali alur kerja operasional konten VT: briefing, produksi, review video, pengiriman sampel hingga penayangan live &amp; analitik.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('tasks/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" id="btnExportCsv" title="Export CSV Data Tugas">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddTask()">
          <i class="fas fa-plus me-1"></i> Tambah Tugas
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
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
          
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

          <!-- Bulk Actions Button -->
          <div>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 d-none" id="btnBulkDelete" onclick="handleBulkDelete()">
              <i class="fas fa-trash me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
          </div>
        </div>

        <!-- Filter Row -->
        <div class="row g-2 align-items-center pt-2 border-top">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Kampanye</label>
            <select class="form-select form-select-sm" id="filterCampaign" onchange="reloadTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($campaignId == $c['id']) ? 'selected' : '' ?>><?= esc($c['title'] ?: $c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter KOL / Talent</label>
            <select class="form-select form-select-sm" id="filterKol" onchange="reloadTable()">
              <option value="">Semua KOL</option>
              <?php foreach ($allKols as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($kolId == $k['id']) ? 'selected' : '' ?>><?= esc($k['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Platform</label>
            <select class="form-select form-select-sm" id="filterPlatform" onchange="reloadTable()">
              <option value="">Semua Platform</option>
              <?php foreach ($allPlatforms as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($platformId == $p['id']) ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Konsep Konten</label>
            <select class="form-select form-select-sm" id="filterVariant" onchange="reloadTable()">
              <option value="">Semua Konsep</option>
              <?php foreach ($allVariants as $v): ?>
                <option value="<?= $v['id'] ?>"><?= esc($v['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableTasks" style="width: 100%;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th width="35" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th width="50">ID</th>
                <th>Judul &amp; No. Task</th>
                <th>Kampanye</th>
                <th>Talent KOL</th>
                <th>Platform</th>
                <th>Budget / Tarif</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Link Live</th>
                <th>CPV</th>
                <th>CPE</th>
                <th width="140" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Tambah / Edit Tugas -->
<div class="modal fade" id="modalTaskForm" tabindex="-1" aria-labelledby="modalTaskFormTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalTaskFormTitle">
          <i class="fas fa-tasks me-2"></i>Tambah Tugas Produksi VT
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formTask" onsubmit="handleTaskSubmit(event)">
        <div class="modal-body p-4">
          <input type="hidden" name="id" id="taskFormId" value="">

          <div class="row g-3">
            <!-- Dealing Kampanye-KOL -->
            <div class="col-md-12">
              <label for="taskCampaignKolId" class="form-label fw-semibold">Pilih Dealing Kampanye &mdash; KOL <span class="text-danger">*</span></label>
              <select class="form-select" name="campaign_kol_id" id="taskCampaignKolId" required>
                <option value="">-- Pilih Kampanye &amp; KOL --</option>
                <?php foreach ($campaignKols as $ck): ?>
                  <option value="<?= $ck['id'] ?>" data-amount="<?= $ck['deal_amount'] ?>">
                    <?= esc($ck['campaign_title']) ?> &mdash; <?= esc($ck['kol_name']) ?> (Deal: Rp <?= number_format((float)$ck['deal_amount'], 0, ',', '.') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Relasi kampanye dan talent KOL yang ditugaskan untuk memproduksi konten.</div>
            </div>

            <!-- Judul Tugas & SOW -->
            <div class="col-md-8">
              <label for="taskTitle" class="form-label fw-semibold">Judul Video / SOW Konten <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" id="taskTitle" required placeholder="Contoh: Video Review Pemakaian 7 Hari & Unboxing">
            </div>

            <!-- Task Number -->
            <div class="col-md-4">
              <label for="taskNumber" class="form-label fw-semibold">Nomor Urut VT <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="task_number" id="taskNumber" min="1" value="1" required>
              <div class="form-text">Contoh: VT #1, VT #2, dsb.</div>
            </div>

            <!-- Platform & Konsep Konten (Variant) -->
            <div class="col-md-6">
              <label for="taskPlatformId" class="form-label fw-semibold">Platform Publikasi</label>
              <select class="form-select" name="platform_id" id="taskPlatformId">
                <option value="">-- Pilih Platform --</option>
                <?php foreach ($allPlatforms as $p): ?>
                  <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label for="taskVariantId" class="form-label fw-semibold">Konsep Konten (Varian)</label>
              <select class="form-select" name="variant_id" id="taskVariantId">
                <option value="">-- Pilih Konsep Konten --</option>
                <?php foreach ($allVariants as $v): ?>
                  <option value="<?= $v['id'] ?>"><?= esc($v['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Tarif / Fee & Deadline -->
            <div class="col-md-6">
              <label for="taskRateAmount" class="form-label fw-semibold">Tarif / Fee Per VT (IDR) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" class="form-control" name="rate_amount" id="taskRateAmount" min="0" step="1000" required placeholder="1000000">
              </div>
            </div>

            <div class="col-md-6">
              <label for="taskDeadline" class="form-label fw-semibold">Target Deadline Posting</label>
              <input type="date" class="form-control" name="deadline" id="taskDeadline">
            </div>

            <!-- Status Operasional -->
            <div class="col-md-6">
              <label for="taskStatus" class="form-label fw-semibold">Status Operasional</label>
              <select class="form-select" name="status" id="taskStatus">
                <option value="DRAFT">DRAFT (Persiapan SOW)</option>
                <option value="BRIEF_SUBMITTED">BRIEF SUBMITTED (Brief Terkirim)</option>
                <option value="BRIEF_APPROVED">BRIEF APPROVED (Brief Disetujui)</option>
                <option value="DRAFT_SUBMITTED">DRAFT REVIEW (Draft Video Masuk)</option>
                <option value="DRAFT_REVISION">DRAFT REVISION (Perlu Revisi)</option>
                <option value="DRAFT_APPROVED">DRAFT APPROVED (Draft Disetujui)</option>
                <option value="SCHEDULED">SCHEDULED (Dijadwalkan)</option>
                <option value="POSTED">POSTED (Tayang Live)</option>
                <option value="COMPLETED">COMPLETED (Selesai &amp; Lunas)</option>
                <option value="CANCELLED">CANCELLED (Dibatalkan)</option>
              </select>
            </div>

            <!-- Catatan Operasional -->
            <div class="col-md-6">
              <label for="taskNotes" class="form-label fw-semibold">Catatan Internal Operasional</label>
              <textarea class="form-control" name="notes" id="taskNotes" rows="2" placeholder="Catatan khusus PIC / tim konten..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light px-4 py-3">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSubmitTask">
            <i class="fas fa-save me-1"></i> Simpan Tugas
          </button>
        </div>
      </form>
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
            <select class="form-select" id="livePlatform" name="platform_id" required>
              <?php foreach ($allPlatforms as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
              <?php endforeach; ?>
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
const modalTaskFormElement = document.getElementById('modalTaskForm');
let modalTaskFormInstance;

$(document).ready(function() {
    modalTaskFormInstance = new bootstrap.Modal(modalTaskFormElement);
    initDatatable();

    // Check all checkbox
    $('#checkAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Single row checkbox
    $(document).on('change', '.row-checkbox', function() {
        const totalRows = $('.row-checkbox').length;
        const totalChecked = $('.row-checkbox:checked').length;
        $('#checkAll').prop('checked', totalRows > 0 && totalRows === totalChecked);
        updateBulkDeleteButton();
    });

    // Edit button click
    $(document).on('click', '.btn-edit-task', function() {
        const id = $(this).data('id');
        openEditTaskModal(id);
    });

    // Delete button click
    $(document).on('click', '.btn-delete-task', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        deleteTask(id, title);
    });
});

function initDatatable() {
    dtTasks = $('#tableTasks').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('tasks/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.stage       = currentStage;
                d.campaign_id = $('#filterCampaign').val();
                d.kol_id      = $('#filterKol').val();
                d.platform_id = $('#filterPlatform').val();
                d.variant_id  = $('#filterVariant').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            { data: 'id', name: 'tasks.id' },
            { data: 'title', name: 'tasks.title' },
            { data: 'campaign', name: 'campaigns.title' },
            { data: 'kol', name: 'kols.name' },
            { data: 'platform', name: 'platforms.name' },
            { data: 'budget', name: 'tasks.budget' },
            { data: 'deadline', name: 'tasks.deadline' },
            { data: 'status', name: 'tasks.status' },
            { data: 'live_url', orderable: false, searchable: false },
            { data: 'cpv', name: 'tasks.cpv' },
            { data: 'cpe', name: 'tasks.cpe' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari tugas, KOL, kampanye, platform...",
            lengthMenu: "Tampilkan _MENU_ baris",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tugas",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Tidak ada data tugas yang cocok",
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat data...',
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        drawCallback: function() {
            $('#checkAll').prop('checked', false);
            updateBulkDeleteButton();
        }
    });
}

function filterStage(stage) {
    currentStage = stage;
    $('#stageTabs .nav-link').removeClass('active');
    $(`#stageTabs button[onclick="filterStage('${stage}')"]`).addClass('active');
    reloadTable();
}

function reloadTable() {
    dtTasks.ajax.reload(null, false);
    updateExportLink();
}

function updateExportLink() {
    const campaignId = $('#filterCampaign').val();
    const kolId      = $('#filterKol').val();
    let url = '<?= site_url('tasks/export') ?>';
    const params = [];
    if (campaignId) params.push('campaign_id=' + encodeURIComponent(campaignId));
    if (kolId)      params.push('kol_id=' + encodeURIComponent(kolId));
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    $('#btnExportCsv').attr('href', url);
}

function updateBulkDeleteButton() {
    const checkedCount = $('.row-checkbox:checked').length;
    $('#selectedCount').text(checkedCount);
    if (checkedCount > 0) {
        $('#btnBulkDelete').removeClass('d-none');
    } else {
        $('#btnBulkDelete').addClass('d-none');
    }
}

function modalAddTask() {
    document.getElementById('formTask').reset();
    document.getElementById('taskFormId').value = '';
    document.getElementById('modalTaskFormTitle').innerHTML = '<i class="fas fa-tasks me-2"></i>Tambah Tugas Produksi VT';
    modalTaskFormInstance.show();
}

function openEditTaskModal(id) {
    Swal.fire({
        title: 'Memuat data...',
        text: 'Mengambil informasi tugas',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.get('<?= base_url('tasks/get-one/') ?>' + id, function(res) {
        Swal.close();
        if (res.success && res.data) {
            const t = res.data;
            document.getElementById('taskFormId').value = t.id;
            document.getElementById('taskCampaignKolId').value = t.campaign_kol_id;
            document.getElementById('taskTitle').value = t.title || '';
            document.getElementById('taskNumber').value = t.task_number || 1;
            document.getElementById('taskPlatformId').value = t.platform_id || '';
            document.getElementById('taskVariantId').value = t.variant_id || '';
            document.getElementById('taskRateAmount').value = t.rate_amount || t.budget || 0;
            document.getElementById('taskDeadline').value = t.deadline || t.estimated_posting_date || '';
            document.getElementById('taskStatus').value = t.status || 'DRAFT';
            document.getElementById('taskNotes').value = t.notes || '';

            document.getElementById('modalTaskFormTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Tugas #' + t.id;
            modalTaskFormInstance.show();
        } else {
            Swal.fire('Error', res.message || 'Gagal memuat tugas.', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Terjadi kesalahan sistem saat mengambil data tugas.', 'error');
    });
}

function handleTaskSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('formTask');
    const formData = new FormData(form);
    const btnSubmit = document.getElementById('btnSubmitTask');
    const originalText = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    $.ajax({
        url: '<?= base_url('tasks/save') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;

            if (res.success) {
                modalTaskFormInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                reloadTable();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    html: res.message
                });
            }
        },
        error: function(xhr) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
            Swal.fire({
                icon: 'error',
                title: 'Error Jaringan',
                text: 'Terjadi kesalahan saat berkomunikasi dengan server.'
            });
        }
    });
}

function setScheduleModal(id) {
    $.get('<?= base_url('tasks/get-one/') ?>' + id, function(res) {
        if (res.success) {
            $('#schedTaskId').val(res.data.id);
            $('#schedTitle').val(res.data.title || `Task #${res.data.task_number}`);
            $('#schedDeadline').val(res.data.deadline || res.data.estimated_posting_date || '');
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
            reloadTable();
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
    const platformId = $('#livePlatform').val();
    const btn = $('#btnSaveLiveUrl');

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('tasks/set-live-url/') ?>' + taskId, {
        post_url: postUrl,
        posted_at: postedAt,
        platform_id: platformId
    }, function(res) {
        btn.prop('disabled', false).text('Simpan & Publish');
        if (res.success) {
            $('#modalLiveUrl').modal('hide');
            reloadTable();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan & Publish');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function deleteTask(id, title) {
    Swal.fire({
        title: 'Hapus Tugas Produksi?',
        html: `Apakah Anda yakin ingin menghapus tugas <strong>${title}</strong>? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('tasks/delete') ?>/' + id,
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
                        reloadTable();
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memproses penghapusan tugas.', 'error');
                }
            });
        }
    });
}

function handleBulkDelete() {
    const selectedIds = [];
    $('.row-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu tugas untuk dihapus.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Hapus ' + selectedIds.length + ' Tugas Terpilih?',
        text: 'Tugas yang dipilih akan dihapus secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus Semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('tasks/bulk-delete') ?>',
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
                        reloadTable();
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal menghapus tugas secara massal.', 'error');
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
