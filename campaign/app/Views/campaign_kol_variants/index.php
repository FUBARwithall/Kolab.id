<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-layer-group text-primary me-2"></i>Alokasi Varian KOL</h3>
        <p class="text-muted small mb-0">Pemetaan konsep kreatif konten dan varian produk yang ditugaskan kepada setiap talent KOL.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('campaign-kol-variants/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" title="Export CSV Data Alokasi Varian">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddAllocation()">
          <i class="fas fa-plus me-1"></i> Tambah Alokasi
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
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-layer-group fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Total Alokasi</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['total_allocations'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-handshake fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Dealing Terkait</div>
              <h4 class="mb-0 fw-bold text-success"><?= number_format($metrics['unique_deals'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
              <i class="fas fa-boxes fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Varian Digunakan</div>
              <h4 class="mb-0 fw-bold text-info"><?= number_format($metrics['unique_variants'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="fas fa-cubes fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Total Qty Alokasi</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['total_qty'] ?? 0) ?> pcs</h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <form id="filterForm" class="row g-2 align-items-center">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Cari Keyword</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
              <input type="text" class="form-control border-start-0" id="filterSearch" placeholder="Cari campaign, KOL, varian...">
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Campaign</label>
            <select class="form-select form-select-sm" id="filterCampaign">
              <option value="">Semua Campaign</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($campaignId == $c['id']) ? 'selected' : '' ?>>
                  <?= esc($c['title'] ?? $c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Konsep Varian</label>
            <select class="form-select form-select-sm" id="filterVariant">
              <option value="">Semua Varian</option>
              <?php foreach ($allVariants as $v): ?>
                <option value="<?= $v['id'] ?>" <?= ($variantId == $v['id']) ? 'selected' : '' ?>>
                  <?= esc($v['name']) ?> (<?= esc($v['product_name'] ?? 'Produk') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 d-flex gap-2 align-self-end mt-2 mt-md-0">
            <button type="button" class="btn btn-primary btn-sm rounded-2 w-100" onclick="applyFilter()">
              <i class="fas fa-filter me-1"></i> Filter
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 w-100" onclick="resetFilter()">
              <i class="fas fa-undo me-1"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle rounded-2" type="button" id="bulkActionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-cogs me-1"></i> Tindakan Massal
            </button>
            <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="bulkActionDropdown">
              <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="bulkDelete()"><i class="fas fa-trash-alt me-2"></i> Hapus yang Dipilih</a></li>
            </ul>
          </div>
          <span id="selectedCountBadge" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="display: none;">0 Dipilih</span>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableCkv" style="width: 100%;">
            <thead class="table-light text-uppercase text-secondary small fw-semibold">
              <tr>
                <th width="35" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th width="50">#ID</th>
                <th>Campaign Terkait</th>
                <th>Assigned Talent KOL</th>
                <th>Konsep Kreatif Konten (Varian)</th>
                <th width="80" class="text-center">Qty</th>
                <th width="140">Dibuat Pada</th>
                <th width="100" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ========================================== -->
<!-- MODAL: TAMBAH / EDIT ALOKASI VARIAN KOL    -->
<!-- ========================================== -->
<div class="modal fade" id="modalAllocVariant" tabindex="-1" aria-labelledby="modalAllocVariantLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fw-bold" id="modalAllocVariantLabel"><i class="fas fa-layer-group me-2"></i>Alokasi Varian Konten ke KOL</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formAllocVariant">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="alloc_id" value="">

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Penugasan Dealing KOL <span class="text-danger">*</span></label>
            <select class="form-select" name="campaign_kol_id" id="alloc_campaign_kol_id" required>
              <option value="">-- Pilih Penugasan Dealing KOL --</option>
              <?php foreach ($allDealings as $d): ?>
                <option value="<?= $d['id'] ?>">
                  <?= esc($d['campaign_title'] ?? 'Campaign') ?> &bull; <?= esc($d['kol_name'] ?? 'KOL') ?> (Deal #<?= $d['id'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text small">Pilih talent KOL dan campaign yang relevan.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Konsep Kreatif Konten / Varian Produk <span class="text-danger">*</span></label>
            <select class="form-select" name="variant_id" id="alloc_variant_id" required>
              <option value="">-- Pilih Konsep Varian --</option>
              <?php foreach ($allVariants as $v): ?>
                <option value="<?= $v['id'] ?>">
                  <?= esc($v['name']) ?> (SKU: <?= esc($v['sku']) ?> - <?= esc($v['product_name'] ?? 'Produk') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah Alokasi (Qty) <span class="text-danger">*</span></label>
            <input type="number" class="form-control fw-bold" name="qty" id="alloc_qty" min="1" max="50" value="1" required>
          </div>
        </div>

        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4 shadow-sm" id="btnSubmitAlloc">
            <i class="fas fa-save me-1"></i> Simpan Alokasi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let table;

$(document).ready(function() {
  initDataTable();

  $('#checkAll').on('change', function() {
    $('.row-checkbox').prop('checked', $(this).prop('checked'));
    updateSelectedCount();
  });

  $(document).on('change', '.row-checkbox', function() {
    updateSelectedCount();
  });

  $('#formAllocVariant').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSubmitAlloc');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
      url: '<?= site_url('campaign-kol-variants/save') ?>',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Alokasi');
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: res.message,
            timer: 1500,
            showConfirmButton: false
          });
          $('#modalAllocVariant').modal('hide');
          reloadTable();
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Alokasi');
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
      }
    });
  });
});

function initDataTable() {
  table = $('#tableCkv').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= site_url('campaign-kol-variants/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.campaign_id = $('#filterCampaign').val();
        d.variant_id = $('#filterVariant').val();
        d.search.value = $('#filterSearch').val();
      }
    },
    order: [[1, 'desc']],
    columns: [
      {
        data: 'id',
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function(data) {
          return '<input type="checkbox" class="form-check-input row-checkbox" value="' + data + '">';
        }
      },
      { data: 'id', className: 'text-muted small fw-bold' },
      {
        data: 'campaign_title',
        render: function(data, type, row) {
          return '<a href="<?= site_url('campaigns/view/') ?>' + row.campaign_id + '" class="fw-bold text-dark text-decoration-none">' +
                 data + '</a>';
        }
      },
      {
        data: 'kol_name',
        render: function(data, type, row) {
          return '<a href="<?= site_url('campaign-kols/view/') ?>' + row.campaign_kol_id + '" class="fw-semibold text-primary text-decoration-none">' +
                 '<i class="fas fa-user-circle me-1"></i>' + data + '</a>' +
                 '<div class="text-muted small">Dealing #' + row.campaign_kol_id + ' &bull; Status: <span class="badge bg-light text-dark border">' + row.deal_status + '</span></div>';
        }
      },
      {
        data: 'variant_name',
        render: function(data, type, row) {
          return '<div class="fw-bold text-dark"><i class="fas fa-layer-group text-info me-1"></i>' + data + '</div>' +
                 '<div class="text-muted small">SKU: ' + row.variant_sku + ' &bull; Produk: ' + (row.product_name || '-') + '</div>';
        }
      },
      {
        data: 'qty',
        className: 'text-center fw-bold',
        render: function(data) {
          return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">' + data + ' pcs</span>';
        }
      },
      {
        data: 'created_at',
        className: 'text-muted small'
      },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function(data, type, row) {
          return '<div class="btn-group btn-group-sm">' +
                 '<a href="<?= site_url('campaign-kol-variants/view/') ?>' + data + '" class="btn btn-outline-secondary" title="Detail"><i class="fas fa-eye"></i></a>' +
                 '<button type="button" class="btn btn-outline-primary" onclick="editAllocation(' + data + ')" title="Edit"><i class="fas fa-pencil-alt"></i></button>' +
                 '<button type="button" class="btn btn-outline-danger" onclick="deleteAllocation(' + data + ')" title="Hapus"><i class="fas fa-trash-alt"></i></button>' +
                 '</div>';
        }
      }
    ],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari...",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
      infoEmpty: "Data tidak ditemukan",
      zeroRecords: "Tidak ada data yang cocok",
      paginate: {
        first: "Awal",
        last: "Akhir",
        next: "&raquo;",
        previous: "&laquo;"
      }
    }
  });
}

function reloadTable() {
  table.ajax.reload(null, false);
  $('#checkAll').prop('checked', false);
  updateSelectedCount();
}

function applyFilter() {
  table.ajax.reload();
}

function resetFilter() {
  $('#filterSearch').val('');
  $('#filterCampaign').val('');
  $('#filterVariant').val('');
  table.ajax.reload();
}

function updateSelectedCount() {
  const count = $('.row-checkbox:checked').length;
  if (count > 0) {
    $('#selectedCountBadge').text(count + ' Dipilih').fadeIn(150);
  } else {
    $('#selectedCountBadge').fadeOut(150);
  }
}

function modalAddAllocation() {
  $('#formAllocVariant')[0].reset();
  $('#alloc_id').val('');
  $('#alloc_qty').val('1');
  $('#modalAllocVariantLabel').html('<i class="fas fa-layer-group me-2"></i>Tambah Alokasi Varian KOL');
  $('#modalAllocVariant').modal('show');
}

function editAllocation(id) {
  $.ajax({
    url: '<?= site_url('campaign-kol-variants/get-one/') ?>' + id,
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        const item = res.data;
        $('#alloc_id').val(item.id);
        $('#alloc_campaign_kol_id').val(item.campaign_kol_id);
        $('#alloc_variant_id').val(item.variant_id);
        $('#alloc_qty').val(item.qty || 1);
        $('#modalAllocVariantLabel').html('<i class="fas fa-edit me-2"></i>Edit Alokasi Varian KOL #' + item.id);
        $('#modalAllocVariant').modal('show');
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }
  });
}

function deleteAllocation(id) {
  Swal.fire({
    title: 'Hapus Alokasi #' + id + '?',
    text: 'Alokasi varian konten ini akan dihapus dari data penugasan KOL.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= site_url('campaign-kol-variants/delete/') ?>' + id,
        type: 'POST',
        data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Terhapus!', res.message, 'success');
            reloadTable();
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        }
      });
    }
  });
}

function bulkDelete() {
  const ids = [];
  $('.row-checkbox:checked').each(function() {
    ids.push($(this).val());
  });

  if (ids.length === 0) {
    Swal.fire('Pilih Data', 'Pilih setidaknya satu data alokasi varian.', 'info');
    return;
  }

  Swal.fire({
    title: 'Hapus ' + ids.length + ' Alokasi Terpilih?',
    text: 'Aksi ini tidak dapat dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus Semua!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= site_url('campaign-kol-variants/bulk-delete') ?>',
        type: 'POST',
        data: {
          ids: ids,
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Selesai', res.message, 'success');
            reloadTable();
          } else {
            Swal.fire('Gagal', res.message, 'error');
          }
        }
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
