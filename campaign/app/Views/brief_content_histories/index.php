<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i>Brief Content Histories</h3>
        <p class="text-muted small mb-0">Arsip snapshot versi konten brief sebelum direvisi oleh Leader atau KOL.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('briefs') ?>" class="text-decoration-none">Briefs</a></li>
          <li class="breadcrumb-item active" aria-current="page">Content Histories</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-primary">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Snapshot Versi</span>
                <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format((int)($metrics['total_snapshots'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                <i class="fas fa-code-branch fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-info">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Brief Pernah Direvisi</span>
                <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format((int)($metrics['total_briefs_revised'] ?? 0), 0, ',', '.') ?></h3>
              </div>
              <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                <i class="fas fa-edit fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-4 border-success">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Versi Tertinggi</span>
                <h3 class="fw-bold mb-0 text-success mt-1">v<?= (int)($metrics['max_version'] ?? 1) ?></h3>
              </div>
              <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                <i class="fas fa-layer-group fa-lg"></i>
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
              <select class="form-select form-select-sm rounded-2" id="filterCampaignHist" onchange="reloadHistTable()">
                <option value="">Semua Kampanye</option>
                <?php foreach ($allCampaigns as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width: 240px;">
              <select class="form-select form-select-sm rounded-2" id="filterBriefHist" onchange="reloadHistTable()">
                <option value="">Semua Brief</option>
                <?php foreach ($allBriefs as $b): ?>
                  <option value="<?= $b['id'] ?>">#<?= $b['id'] ?> - <?= esc($b['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-2" title="Reset Filter" onclick="resetHistFilters()">
              <i class="fas fa-undo"></i>
            </button>
          </div>

          <!-- Actions & Exports -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('briefs') ?>" class="btn btn-outline-primary btn-sm rounded-2 px-3">
              <i class="fas fa-file-alt me-1"></i> Data Briefs
            </a>
            <div class="btn-group">
              <a href="<?= base_url('brief-content-histories/export') ?>" class="btn btn-outline-success btn-sm rounded-2">
                <i class="fas fa-file-excel me-1"></i> Excel / CSV
              </a>
              <a href="<?= base_url('brief-content-histories/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2">
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
          <table class="table table-hover align-middle mb-0" id="tableBriefHistories" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllHist" onchange="toggleSelectAll(this)">
                </th>
                <th>Target Brief &amp; Task</th>
                <th style="width: 80px;" class="text-center">Versi</th>
                <th>Judul Snapshot &amp; Alasan Revisi</th>
                <th>Kampanye &amp; KOL</th>
                <th style="width: 130px;">Dibuat Oleh</th>
                <th style="width: 140px;">Waktu Snapshot</th>
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

<!-- Modal View Detail Brief History Snapshot -->
<div class="modal fade" id="modalViewHist" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewHistTitle"><i class="fas fa-code-branch text-primary me-2"></i>Detail Snapshot Konten</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewHistBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light justify-content-between">
        <div id="viewHistActions"></div>
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtHist = null;

$(document).ready(function() {
    dtHist = $('#tableBriefHistories').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('brief-content-histories/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignHist').val();
                d.brief_id    = $('#filterBriefHist').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'brief' },
            { data: 'version', className: 'text-center' },
            { data: 'title' },
            { data: 'campaign' },
            { data: 'author' },
            { data: 'snapshot' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[6, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari snapshot, brief, judul, task...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ snapshot",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            updateBulkDeleteButton();
            $('.hist-row-check').on('change', updateBulkDeleteButton);
        }
    });
});

function reloadHistTable() {
    dtHist.ajax.reload();
}

function resetHistFilters() {
    $('#filterCampaignHist').val('');
    $('#filterBriefHist').val('');
    reloadHistTable();
}

function toggleSelectAll(master) {
    $('.hist-row-check').prop('checked', master.checked);
    updateBulkDeleteButton();
}

function updateBulkDeleteButton() {
    const selected = $('.hist-row-check:checked');
    const count = selected.length;
    $('#bulkCount').text(count);
    if (count > 0) {
        $('#btnBulkDelete').removeClass('d-none');
    } else {
        $('#btnBulkDelete').addClass('d-none');
        $('#checkAllHist').prop('checked', false);
    }
}

function viewHistModal(id) {
    $('#modalViewHist').modal('show');
    $('#viewHistBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
    $('#viewHistActions').html('');

    $.get('<?= base_url('brief-content-histories/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const h = res.data;

            $('#viewHistBody').html(`
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Snapshot Versi ${h.version} &bull; ${h.title || h.briefs_title}</h5>
                        <div class="text-muted small">Target Brief: <strong>${h.briefs_title} (#${h.brief_id})</strong> &bull; Task: <strong>${h.task_title || ('VT #' + h.task_number)}</strong> &bull; Kampanye: <strong>${h.campaign_title || '-'}</strong></div>
                    </div>
                    <span class="badge bg-primary px-3 py-2 fs-6">Versi ${h.version}</span>
                </div>

                ${h.content_diff ? `
                <div class="alert alert-warning border-warning-subtle mb-3">
                    <strong class="d-block mb-1 text-dark"><i class="fas fa-exclamation-triangle me-1 text-warning"></i>Catatan Alasan Revisi:</strong>
                    <div class="small text-secondary">${h.content_diff}</div>
                </div>` : ''}

                <div class="card bg-light border-0 p-3 mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-file-alt me-1 text-primary"></i>Isi Konten &amp; Storyline Versi Ini:</h6>
                    <div class="text-secondary small" style="white-space: pre-line; line-height: 1.6; max-height: 250px; overflow-y: auto;">
                        ${h.content || '<em class="text-muted">Tidak ada teks konten tersimpan</em>'}
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Editor / Pengambil Snapshot</small>
                        <strong class="text-dark"><i class="fas fa-user-edit me-1 text-secondary"></i>${h.author_name || 'Editor'}</strong>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted d-block">Waktu Snapshot</small>
                        <strong class="text-dark"><i class="far fa-clock me-1 text-secondary"></i>${h.snapshot_at || h.created_at} WIB</strong>
                    </div>
                </div>
            `);

            $('#viewHistActions').html(`
                <a href="<?= base_url('brief-content-histories/view/') ?>${h.id}" class="btn btn-outline-info btn-sm rounded-2"><i class="fas fa-external-link-alt me-1"></i>Halaman Penuh</a>
                <a href="<?= base_url('brief-content-histories/single-pdf/') ?>${h.id}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2"><i class="fas fa-print me-1"></i>Cetak PDF</a>
            `);
        }
    });
}

function deleteHistory(id) {
    Swal.fire({
        title: 'Hapus Snapshot ini?',
        text: 'Data snapshot versi konten brief ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('brief-content-histories/delete/') ?>' + id, function(res) {
                if (res.success) {
                    dtHist.ajax.reload(null, false);
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
    const selected = $('.hist-row-check:checked');
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
            $.post('<?= base_url('brief-content-histories/bulk-delete') ?>', { ids: ids }, function(res) {
                if (res.success) {
                    dtHist.ajax.reload(null, false);
                    $('#checkAllHist').prop('checked', false);
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
