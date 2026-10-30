<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Breadcrumb & Header -->
    <div class="row mb-3">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark fs-4 fw-bold">
                <i class="fas fa-user-astronaut text-primary me-2"></i> Direktori &amp; Performance KOL
            </h1>
            <p class="text-muted text-xs mb-0">Talent discovery, metrik jangkauan audiens, dan intelligence performa KOL (Khusus View)</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end text-xs">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item text-muted">Komunikasi &amp; Talent</li>
                <li class="breadcrumb-item active">Direktori KOL</li>
            </ol>
        </div>
    </div>

    <!-- Alert Context Portal -->
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between py-2 px-3 mb-4 rounded-3">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle text-info fs-5 me-3"></i>
            <div>
                <strong class="text-dark text-sm">Mode View-Only Terkoneksi:</strong>
                <span class="text-muted text-xs ms-1">Modul ini menampilkan profil, metrik platform, dan rate card KOL untuk evaluasi perencana kampanye. Pengelolaan master data KOL (tambah/edit akun) dilakukan terpusat di portal <strong>DataHub</strong>.</span>
            </div>
        </div>
        <a href="http://localhost:8087/datahub/kols" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 ms-3 text-nowrap fw-semibold">
            <i class="fas fa-external-link-alt me-1"></i> Buka Master di DataHub
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <form id="filter-form" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label text-xs fw-semibold text-muted mb-1">Filter Kategori / Niche</label>
                    <select name="category_id" id="filter-category" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-xs fw-semibold text-muted mb-1">Status Verifikasi</label>
                    <select name="status" id="filter-status" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua Status</option>
                        <option value="APPROVED">Aktif / Disetujui</option>
                        <option value="PENDING">Menunggu Review</option>
                        <option value="REJECTED">Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-xs fw-semibold text-muted mb-1">Status Keamanan</label>
                    <select name="is_blacklisted" id="filter-blacklisted" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua Akun</option>
                        <option value="0">Hanya Akun Aman (Whitelist)</option>
                        <option value="1">Akun Blacklist</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end pt-3">
                    <button type="button" id="btn-reset-filter" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                        <i class="fas fa-redo me-1"></i> Reset Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                <i class="fas fa-list text-primary me-2"></i> Daftar Talent &amp; Influencer
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 text-xs">
                Real-Time Data
            </span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="table-kols" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="min-width: 220px;">Talent &amp; Domisili</th>
                            <th>Kategori / Niche</th>
                            <th style="min-width: 180px;">Channel Platform</th>
                            <th>Total Followers</th>
                            <th>Avg. ER%</th>
                            <th>Rating Review</th>
                            <th>Status</th>
                            <th class="text-end" style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
    var table = $('#table-kols').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= base_url('kols/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.category_id    = $('#filter-category').val();
                d.status         = $('#filter-status').val();
                d.is_blacklisted = $('#filter-blacklisted').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-muted fw-bold ps-3' },
            { data: 'profile' },
            { data: 'categories' },
            { data: 'channels' },
            { data: 'followers' },
            { data: 'avg_er' },
            { data: 'rating' },
            { data: 'status' },
            { data: 'actions', className: 'text-end pe-3' }
        ],
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            search: "Cari Talent / Handle:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data KOL",
            emptyTable: "Tidak ada data KOL yang sesuai filter.",
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat data...'
        }
    });

    $('#filter-category, #filter-status, #filter-blacklisted').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-form')[0].reset();
        table.ajax.reload();
    });
});
</script>
<?= $this->endSection() ?>
