<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Master Template Kontrak Kerjasama</h4>
            <p class="text-muted small mb-0">Kelola format draf perjanjian legalitas, klausul pasal SOW, kompensasi fee, hak cipta materi, dan placeholder otomatis.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('contracts') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Kontrak
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="addTemplateModal()">
                <i class="fas fa-plus me-1"></i> Tambah Template Baru
            </button>
        </div>
    </div>

    <!-- Info Box: Available Placeholders -->
    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
        <h6 class="fw-bold mb-2"><i class="fas fa-tags me-1"></i> Tag Placeholder yang Didukung:</h6>
        <div class="d-flex flex-wrap gap-2 text-xs font-monospace">
            <span class="badge bg-white text-dark border"><code>{nomor_kontrak}</code></span>
            <span class="badge bg-white text-dark border"><code>{tanggal}</code></span>
            <span class="badge bg-white text-dark border"><code>{brand_name}</code></span>
            <span class="badge bg-white text-dark border"><code>{campaign_title}</code></span>
            <span class="badge bg-white text-dark border"><code>{kol_name}</code></span>
            <span class="badge bg-white text-dark border"><code>{kol_nik}</code></span>
            <span class="badge bg-white text-dark border"><code>{kol_address}</code></span>
            <span class="badge bg-white text-dark border"><code>{kol_whatsapp}</code></span>
            <span class="badge bg-white text-dark border"><code>{deal_amount}</code></span>
            <span class="badge bg-white text-dark border"><code>{total_vt}</code></span>
            <span class="badge bg-white text-dark border"><code>{kol_bank_name}</code></span>
            <span class="badge bg-white text-dark border"><code>{kol_bank_account}</code></span>
        </div>
        <small class="text-muted d-block mt-2">Tag-tag di atas akan otomatis digantikan dengan data riil saat dokumen kontrak digenerate untuk KOL.</small>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tplTable" class="table table-hover align-middle mb-0" style="width: 100%;">
                    <thead class="table-light text-uppercase text-xs fw-bold text-muted">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="min-width: 200px;">Judul Template</th>
                            <th>Cuplikan Isi Klausul</th>
                            <th style="width: 120px;" class="text-center">Status</th>
                            <th style="width: 150px;">Tanggal Dibuat</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Template (Add/Edit) -->
<div class="modal fade" id="modalTplForm" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalTplTitle"><i class="fas fa-file-contract me-2"></i>Kelola Template Kontrak</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="tplForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="tplId" name="id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Template Kontrak <span class="text-danger">*</span></label>
                        <input type="text" id="tplTitle" name="title" class="form-control" placeholder="Contoh: Perjanjian Kerjasama Eksklusif KOL (Full SOW)" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Naskah Klausul &amp; Pasal Perjanjian <span class="text-danger">*</span></label>
                        <textarea id="tplBody" name="body_template" class="form-control font-monospace small" rows="12" placeholder="Tuliskan draf pasal perjanjian di sini... Gunakan placeholder {kol_name}, {deal_amount}, dll." required style="line-height: 1.5;"></textarea>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="tplIsActive" name="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="tplIsActive">Aktifkan template ini untuk pembuatan kontrak baru</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnSaveTpl">Simpan Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview Template -->
<div class="modal fade" id="modalTplPreview" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="previewTitle"><i class="fas fa-eye me-2"></i>Preview Naskah Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="card bg-light border p-3 font-monospace small" style="white-space: pre-wrap; line-height: 1.6;" id="previewBody">
                    Memuat...
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let tplTable;

$(document).ready(function() {
    tplTable = $('#tplTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('contract-templates/get-datatable') ?>',
            type: 'POST'
        },
        columns: [
            { data: 'id', className: 'text-center fw-bold text-muted' },
            { data: 'title' },
            { data: 'snippet' },
            { data: 'status', className: 'text-center' },
            { data: 'created' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm me-2"></div> Memuat template...',
            search: "_INPUT_",
            searchPlaceholder: "Cari template kontrak...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ template",
            zeroRecords: "Template tidak ditemukan"
        }
    });
});

function reloadTplTable() {
    tplTable.ajax.reload(null, false);
}

function addTemplateModal() {
    $('#modalTplTitle').html('<i class="fas fa-plus-circle me-2"></i>Tambah Template Kontrak Baru');
    $('#tplId').val('');
    $('#tplTitle').val('');
    $('#tplBody').val('');
    $('#tplIsActive').prop('checked', true);
    new bootstrap.Modal(document.getElementById('modalTplForm')).show();
}

function editTemplate(id) {
    $.get('<?= base_url('contract-templates/get-one/') ?>' + id, function(res) {
        if (res.success) {
            $('#modalTplTitle').html('<i class="fas fa-edit me-2"></i>Edit Template Kontrak #' + id);
            $('#tplId').val(res.data.id);
            $('#tplTitle').val(res.data.title);
            $('#tplBody').val(res.data.body_template);
            $('#tplIsActive').prop('checked', res.data.is_active == 1);
            new bootstrap.Modal(document.getElementById('modalTplForm')).show();
        }
    });
}

function previewTemplate(id) {
    $.get('<?= base_url('contract-templates/get-one/') ?>' + id, function(res) {
        if (res.success) {
            $('#previewTitle').text(res.data.title);
            $('#previewBody').text(res.data.body_template);
            new bootstrap.Modal(document.getElementById('modalTplPreview')).show();
        }
    });
}

function deleteTemplate(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus template kontrak ini?')) return;

    $.post('<?= base_url('contract-templates/delete/') ?>' + id, function(res) {
        if (res.success) {
            reloadTplTable();
            alert(res.message);
        }
    });
}

$('#tplForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSaveTpl');
    btn.prop('disabled', true).text('Menyimpan...');

    $.post('<?= base_url('contract-templates/save') ?>', $(this).serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan Template');
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalTplForm')).hide();
            reloadTplTable();
            alert(res.message);
        } else {
            alert(res.message || 'Gagal menyimpan');
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan Template');
        alert(xhr.responseJSON?.message || 'Terjadi kesalahan sistem');
    });
});
</script>
<?= $this->endSection() ?>
