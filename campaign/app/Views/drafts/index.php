<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-video text-warning me-2"></i>Review Draft Video KOL</h3>
        <p class="text-muted small mb-0">Platform review video kreasi KOL dengan feedback spesifik berbasis timestamp detik/menit dan kontrol guideline konten.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= base_url('drafts/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" id="btnExportCsv" title="Export CSV Data Draft Video">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadDraftTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-warning text-dark rounded-2 btn-sm px-3 shadow-sm fw-semibold" onclick="uploadDraftModal()">
          <i class="fas fa-upload me-1"></i> Unggah Draft Video
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
              <i class="fas fa-film fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Draft Terdaftar</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['total_drafts'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="far fa-clock fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Menunggu Review</div>
              <h4 class="mb-0 fw-bold text-warning"><?= number_format($metrics['submitted_count'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-check-circle fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Draft Disetujui (Approved)</div>
              <h4 class="mb-0 fw-bold text-success"><?= number_format($metrics['approved_count'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3">
              <i class="fas fa-undo fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Perlu Revisi</div>
              <h4 class="mb-0 fw-bold text-danger"><?= number_format($metrics['revision_count'] ?? 0) ?></h4>
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
            <select class="form-select form-select-sm rounded-2" id="filterCampaignDraft" style="min-width: 200px;" onchange="reloadDraftTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title'] ?: $c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterStatusDraft" style="min-width: 160px;" onchange="reloadDraftTable()">
              <option value="">Semua Status Review</option>
              <option value="SUBMITTED">Menunggu Review</option>
              <option value="APPROVED">Approved</option>
              <option value="REVISION">Revisi Diperlukan</option>
              <option value="DRAFT">Draft Belum Disubmit</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 d-none" id="btnBulkDelete" onclick="handleBulkDelete()">
              <i class="fas fa-trash me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
            <div class="text-muted small">
              <i class="fas fa-info-circle me-1 text-primary"></i> Leader &amp; Tim Staff dapat menyetujui atau meminta revisi per video.
            </div>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableDrafts" style="width: 100%;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th style="width: 35px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th style="width: 45px;" class="text-center">#</th>
                <th>Target Tugas &amp; Video Preview</th>
                <th>Kampanye</th>
                <th>Talent KOL</th>
                <th style="width: 80px;" class="text-center">Versi</th>
                <th style="width: 130px;">Status</th>
                <th style="width: 120px;">Diunggah</th>
                <th style="width: 140px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Unggah / Tambah Draft Video -->
<div class="modal fade" id="modalUploadDraft" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <form id="formUploadDraft" onsubmit="submitDraftVideo(event)">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title fw-bold" id="modalUploadDraftTitle"><i class="fas fa-video me-2"></i>Unggah Draft Video KOL</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="draftId" name="id" value="">

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Pilih Target Tugas (VT) <span class="text-danger">*</span></label>
              <select class="form-select" id="draftTaskId" name="task_id" required>
                <option value="">-- Pilih Tugas Target --</option>
                <?php foreach ($allTasks as $t): ?>
                  <option value="<?= $t['id'] ?>" <?= ($taskId ?? '') == $t['id'] ? 'selected' : '' ?>>
                    [VT #<?= esc($t['task_number'] ?: $t['id']) ?>] <?= esc($t['title']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Versi Draft <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="draftVersion" name="version" min="1" value="1" required>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Link / URL Video Draft <span class="text-danger">*</span></label>
              <input type="url" class="form-control" id="draftVideoUrl" name="draft_url" required placeholder="https://drive.google.com/... atau link video cloud / youtube">
              <small class="text-muted">Pastikan akses share dibuka publik agar tim dapat meninjau video.</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Caption Video (Opsional)</label>
              <textarea class="form-control" id="draftCaption" name="caption" rows="2" placeholder="Draft teks caption yang akan diunggah KOL..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Hashtag / Tagar (Opsional)</label>
              <textarea class="form-control" id="draftHashtag" name="hashtag" rows="2" placeholder="#BrandName #Campaign2026 ..."></textarea>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Catatan dari KOL / Kreator</label>
              <textarea class="form-control" id="draftNotes" name="notes" rows="2" placeholder="Contoh: Sudah disesuaikan dengan brief, bagian CTA ditaruh di detik 00:45..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning text-dark rounded-2 px-4 fw-semibold" id="btnSubmitDraft">Kirim Draft</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detail & Timestamp Notes Draft -->
<div class="modal fade" id="modalViewDraft" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewDraftTitle"><i class="fas fa-video text-warning me-2"></i>Detail Draft Video &amp; Feedback</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewDraftBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Review Draft (Approve / Revision) -->
<div class="modal fade" id="modalReviewDraft" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check me-2"></i>Review Draft Video</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="revDraftId">
        
        <div class="alert alert-info py-2 px-3 small mb-3">
          <i class="fas fa-info-circle me-1"></i> Berikan umpan balik yang terukur. Jika ada detik tertentu yang perlu diganti, gunakan kolom Timestamp Feedback.
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan Evaluasi Keseluruhan</label>
          <textarea class="form-control" id="draftRevNotes" rows="3" placeholder="Evaluasi umum tentang tone, lighting, pembawaan KOL, kesesuaian produk..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-danger"><i class="fas fa-stopwatch me-1"></i>Timestamp Feedback Spesifik (Opsional)</label>
          <textarea class="form-control font-monospace" id="draftTimestampNotes" rows="3" placeholder="00:05 - Kurang jelas bagian label produk&#10;00:22 - Audio agak kresek saat bicara harga&#10;00:40 - Jangan lupa tambahkan tagar #BrandName"></textarea>
        </div>

        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-danger rounded-2 px-3" onclick="submitDraftDecision('revision')">
            <i class="fas fa-undo me-1"></i> Minta Revisi Draft
          </button>
          <button type="button" class="btn btn-success rounded-2 px-4" onclick="submitDraftDecision('approve')">
            <i class="fas fa-check me-1"></i> Setujui Video (Approve)
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtDrafts = null;

$(document).ready(function() {
    dtDrafts = $('#tableDrafts').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('drafts/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignDraft').val();
                d.status      = $('#filterStatusDraft').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'task' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'version', className: 'text-center' },
            { data: 'status' },
            { data: 'submitted' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari draft video, task, kampanye...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ draft",
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

    // Submit for Review action handler
    $(document).on('click', '.btn-submit-review', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Ajukan Review Draft?',
            text: 'Draft video akan dikirim ke Leader untuk ditinjau.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Ya, Ajukan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= base_url('drafts/submit-review') ?>/' + id, function(res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        reloadDraftTable();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                });
            }
        });
    });

    // Approve action handler
    $(document).on('click', '.btn-approve-draft', function() {
        const id = $(this).data('id');
        const name = $(this).data('name') || ('Draft #' + id);

        Swal.fire({
            title: 'Setujui Draft Video?',
            html: `Apakah Anda yakin menyetujui video untuk <strong>${name}</strong>? Status tugas otomatis dinaikkan ke DRAFT APPROVED.`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check me-1"></i> Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= base_url('drafts/approve') ?>/' + id, { notes: 'Disetujui Leader' }, function(res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Disetujui!', text: res.message, timer: 1800, showConfirmButton: false });
                        reloadDraftTable();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                });
            }
        });
    });

    // Revision action handler
    $(document).on('click', '.btn-revision-draft', function() {
        const id = $(this).data('id');
        reviewDraftModal(id);
    });

    // Edit Draft handler
    $(document).on('click', '.btn-edit-draft', function() {
        const id = $(this).data('id');
        openEditDraftModal(id);
    });

    // Delete Draft handler
    $(document).on('click', '.btn-delete-draft', function() {
        const id = $(this).data('id');
        const name = $(this).data('name') || ('Draft #' + id);

        Swal.fire({
            title: 'Hapus Draft?',
            html: `Apakah Anda yakin ingin menghapus data <strong>${name}</strong>? Riwayat review terkait juga akan dibersihkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= base_url('drafts/delete') ?>/' + id, function(res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1800, showConfirmButton: false });
                        reloadDraftTable();
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Gagal memproses penghapusan draft.', 'error');
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
        Swal.fire('Peringatan', 'Pilih minimal satu draft untuk dihapus.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Hapus ' + selectedIds.length + ' Draft Video?',
        text: 'Semua draft terpilih beserta histori ulasannya akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus Semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('drafts/bulk-delete') ?>', { ids: selectedIds }, function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1800, showConfirmButton: false });
                    reloadDraftTable();
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Gagal menghapus draft secara massal.', 'error');
            });
        }
    });
}

function reloadDraftTable() {
    if (dtDrafts) {
        dtDrafts.ajax.reload();
    }
}

function uploadDraftModal() {
    $('#formUploadDraft')[0].reset();
    $('#draftId').val('');
    $('#modalUploadDraftTitle').html('<i class="fas fa-video me-2"></i>Unggah Draft Video KOL');
    $('#btnSubmitDraft').text('Kirim Draft');
    $('#modalUploadDraft').modal('show');
}

function openEditDraftModal(id) {
    $.get('<?= base_url('drafts/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const d = res.data;
            $('#draftId').val(d.id);
            $('#draftTaskId').val(d.task_id);
            $('#draftVersion').val(d.version);
            $('#draftVideoUrl').val(d.live_draft_url || d.draft_url || d.video_url);
            $('#draftCaption').val(d.caption || '');
            $('#draftHashtag').val(d.hashtag || '');
            $('#draftNotes').val(d.notes || d.review_notes || '');
            $('#modalUploadDraftTitle').html('<i class="fas fa-edit me-2"></i>Edit Draft Video #' + d.id);
            $('#btnSubmitDraft').text('Simpan Perubahan');
            $('#modalUploadDraft').modal('show');
        }
    });
}

function submitDraftVideo(e) {
    e.preventDefault();
    const btn = $('#btnSubmitDraft');
    const isEdit = !!$('#draftId').val();
    const endpoint = isEdit ? '<?= base_url('drafts/save') ?>' : '<?= base_url('drafts/submit') ?>';

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post(endpoint, $('#formUploadDraft').serialize(), function(res) {
        btn.prop('disabled', false).text(isEdit ? 'Simpan Perubahan' : 'Kirim Draft');
        if (res.success) {
            $('#modalUploadDraft').modal('hide');
            reloadDraftTable();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text(isEdit ? 'Simpan Perubahan' : 'Kirim Draft');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function reviewDraftModal(id) {
    $('#revDraftId').val(id);
    $('#draftRevNotes').val('');
    $('#draftTimestampNotes').val('');
    $('#modalReviewDraft').modal('show');
}

function submitDraftDecision(action) {
    const id = $('#revDraftId').val();
    const notes = $('#draftRevNotes').val();
    const timestampNotes = $('#draftTimestampNotes').val();
    const endpoint = action === 'approve' ? '<?= base_url('drafts/approve/') ?>' + id : '<?= base_url('drafts/revision/') ?>' + id;

    $.post(endpoint, {
        notes: notes,
        timestamp_notes: timestampNotes
    }, function(res) {
        if (res.success) {
            $('#modalReviewDraft').modal('hide');
            reloadDraftTable();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}
</script>
<?= $this->endSection() ?>
