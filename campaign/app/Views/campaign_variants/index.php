<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-boxes text-primary me-2"></i>Varian Produk Kampanye
        </h3>
        <p class="text-muted small mb-0">Manajemen relasi varian produk sampel yang didistribusikan kepada KOL untuk konten promosi dan seeding kampanye.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('campaign-variants/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" id="btnExportCsv" title="Export CSV Relasi Varian">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddVariant()">
          <i class="fas fa-plus me-1"></i> Tambah Varian Kampanye
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

    <!-- 4 KPI Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-boxes fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Relasi Varian</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_relations']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                <i class="fas fa-bullhorn fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Kampanye Terkait</span>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['total_campaigns']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-tags fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Varian Produk Terdaftar</span>
                <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['total_variants']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-chart-pie fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Rata-Rata / Kampanye</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['avg_per_campaign'], 1) ?> <span class="fs-7 text-muted fw-normal">item</span></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Filter Kampanye</label>
            <select class="form-select form-select-sm" id="filterCampaign" onchange="reloadTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($campaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title'] ?: $c['name']) ?> (<?= esc($c['status']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Filter Produk Induk</label>
            <select class="form-select form-select-sm" id="filterProduct" onchange="reloadTable()">
              <option value="">Semua Produk</option>
              <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?> (<?= esc($p['sku']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 text-md-end pt-3">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 d-none" id="btnBulkDelete" onclick="handleBulkDelete()">
              <i class="fas fa-trash me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableCampaignVariants" style="width: 100%;">
            <thead class="table-light">
              <tr class="text-uppercase fs-7 text-muted">
                <th width="35" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th width="50">ID</th>
                <th>Kampanye</th>
                <th>Produk</th>
                <th>Nama Varian</th>
                <th>Harga Tambahan</th>
                <th>Stok Sampel</th>
                <th>Catatan</th>
                <th>Dibuat</th>
                <th width="120" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- DataTables AJAX -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Tambah / Edit Varian Kampanye -->
<div class="modal fade" id="modalVariantForm" tabindex="-1" aria-labelledby="modalVariantTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalVariantTitle">
          <i class="fas fa-boxes me-2"></i>Tambah Varian Kampanye
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formCampaignVariant" onsubmit="handleFormSubmit(event)">
        <div class="modal-body p-4">
          <input type="hidden" name="id" id="fieldId" value="">

          <div class="row g-3">
            <!-- Pilihan Kampanye -->
            <div class="col-md-12">
              <label for="fieldCampaignId" class="form-label fw-semibold">Pilih Kampanye <span class="text-danger">*</span></label>
              <select class="form-select" name="campaign_id" id="fieldCampaignId" required>
                <option value="">-- Pilih Kampanye Target --</option>
                <?php foreach ($campaigns as $c): ?>
                  <option value="<?= $c['id'] ?>">
                    <?= esc($c['title'] ?: $c['name']) ?> [Status: <?= esc($c['status']) ?>]
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Kampanye yang akan mendistribusikan sampel varian produk ini kepada kreator.</div>
            </div>

            <!-- Pilihan Varian Produk -->
            <div class="col-md-12">
              <label for="fieldVariantId" class="form-label fw-semibold">Pilih Varian Produk <span class="text-danger">*</span></label>
              <select class="form-select" name="variant_id" id="fieldVariantId" required>
                <option value="">-- Pilih Varian Produk --</option>
                <?php foreach ($variants as $v): ?>
                  <option value="<?= $v['id'] ?>" data-product="<?= $v['product_id'] ?>">
                    <?= esc($v['product_name']) ?> &mdash; <?= esc($v['name']) ?> (SKU: <?= esc($v['sku']) ?>) | Stok: <?= number_format($v['stock_qty']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Pilih spesifik varian produk sampel (ukuran, aroma, warna, dsb.).</div>
            </div>

            <!-- Catatan Distribusi / Review -->
            <div class="col-12">
              <label for="fieldNotes" class="form-label fw-semibold">Catatan / Keterangan Distribusi</label>
              <textarea class="form-control" name="notes" id="fieldNotes" rows="3" placeholder="Contoh: Varian sampel wajib disertakan dalam unboxing video..."></textarea>
              <div class="form-text">Catatan internal operasional untuk varian sampel kampanye ini.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light px-4 py-3">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSubmitForm">
            <i class="fas fa-save me-1"></i> Simpan Relasi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dataTable;
const modalVariantElement = document.getElementById('modalVariantForm');
let modalVariantInstance;

document.addEventListener('DOMContentLoaded', function() {
    modalVariantInstance = new bootstrap.Modal(modalVariantElement);

    // Inisialisasi DataTables AJAX
    dataTable = $('#tableCampaignVariants').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('campaign-variants/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaign').val();
                d.product_id  = $('#filterProduct').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            { data: 'id', name: 'campaign_variants.id' },
            { data: 'campaign', name: 'campaigns.title' },
            { data: 'product', name: 'products.name' },
            { data: 'variant', name: 'variants.name' },
            { data: 'additional', name: 'variants.additional_price' },
            { data: 'stock', name: 'variants.stock_qty' },
            { data: 'notes', name: 'campaign_variants.notes' },
            { data: 'created_at', name: 'campaign_variants.created_at' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari kampanye, produk, varian, SKU...",
            lengthMenu: "Tampilkan _MENU_ baris",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ varian",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Tidak ada varian kampanye yang cocok",
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

    // Handle check all checkbox
    $('#checkAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Handle single row checkbox
    $(document).on('change', '.row-checkbox', function() {
        const totalRows = $('.row-checkbox').length;
        const totalChecked = $('.row-checkbox:checked').length;
        $('#checkAll').prop('checked', totalRows > 0 && totalRows === totalChecked);
        updateBulkDeleteButton();
    });

    // Event listener Edit Button
    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        openEditModal(id);
    });

    // Event listener Delete Button
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        deleteRecord(id, name);
    });
});

function reloadTable() {
    dataTable.ajax.reload(null, false);
    updateExportLink();
}

function updateExportLink() {
    const campaignId = $('#filterCampaign').val();
    const productId  = $('#filterProduct').val();
    let url = '<?= site_url('campaign-variants/export') ?>';
    const params = [];
    if (campaignId) params.push('campaign_id=' + encodeURIComponent(campaignId));
    if (productId)  params.push('product_id=' + encodeURIComponent(productId));
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

function modalAddVariant() {
    document.getElementById('formCampaignVariant').reset();
    document.getElementById('fieldId').value = '';
    document.getElementById('modalVariantTitle').innerHTML = '<i class="fas fa-boxes me-2"></i>Tambah Varian Kampanye';
    modalVariantInstance.show();
}

function openEditModal(id) {
    Swal.fire({
        title: 'Memuat data...',
        text: 'Mengambil informasi varian kampanye',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: '<?= site_url('campaign-variants/get-one') ?>/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            Swal.close();
            if (res.success && res.data) {
                const item = res.data;
                document.getElementById('fieldId').value = item.id;
                document.getElementById('fieldCampaignId').value = item.campaign_id;
                document.getElementById('fieldVariantId').value = item.variant_id;
                document.getElementById('fieldNotes').value = item.notes || '';

                document.getElementById('modalVariantTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Varian Kampanye #' + item.id;
                modalVariantInstance.show();
            } else {
                Swal.fire('Error', res.message || 'Gagal memuat data.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat mengambil data.', 'error');
        }
    });
}

function handleFormSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('formCampaignVariant');
    const formData = new FormData(form);
    const btnSubmit = document.getElementById('btnSubmitForm');
    const originalText = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    $.ajax({
        url: '<?= site_url('campaign-variants/save') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;

            if (res.success) {
                modalVariantInstance.hide();
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

function deleteRecord(id, name) {
    Swal.fire({
        title: 'Hapus Varian Kampanye?',
        html: `Apakah Anda yakin ingin menghapus relasi varian <strong>${name}</strong>? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url('campaign-variants/delete') ?>/' + id,
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
                    Swal.fire('Error', 'Gagal memproses penghapusan data.', 'error');
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
        Swal.fire('Peringatan', 'Pilih minimal satu varian untuk dihapus.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Hapus ' + selectedIds.length + ' Varian Kampanye?',
        text: 'Varian produk yang dipilih akan dihapus dari kampanye terkait.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus Semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url('campaign-variants/bulk-delete') ?>',
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
                    Swal.fire('Error', 'Gagal menghapus varian kampanye secara massal.', 'error');
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
