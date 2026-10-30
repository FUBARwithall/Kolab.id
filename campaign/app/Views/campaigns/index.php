<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

  <!-- 1. Mini Dashboard Summary Cards -->
  <div class="row g-3 mb-4" id="summaryCards">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
            <i class="fas fa-bullhorn fa-lg"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Kampanye</div>
            <h4 class="mb-0 fw-bold text-dark" id="summaryTotalCampaigns"><?= esc($summary['total_campaigns'] ?? 0) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
            <i class="fas fa-play-circle fa-lg"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Kampanye Aktif</div>
            <h4 class="mb-0 fw-bold text-dark" id="summaryActiveCampaigns"><?= esc($summary['active_campaigns'] ?? 0) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
            <i class="fas fa-wallet fa-lg"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Anggaran</div>
            <h4 class="mb-0 fw-bold text-dark" id="summaryTotalBudget">Rp <?= number_format($summary['total_budget'] ?? 0, 0, ',', '.') ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center">
          <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
            <i class="fas fa-percentage fa-lg"></i>
          </div>
          <div>
            <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Serapan Anggaran (KOL)</div>
            <h4 class="mb-0 fw-bold text-dark" id="summaryAbsorption"><?= esc($summary['budget_absorption'] ?? 0) ?>% <small class="text-muted fs-6">(Rp <?= number_format($summary['total_spent'] ?? 0, 0, ',', '.') ?>)</small></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Main Data Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div class="d-flex align-items-center gap-2">
        <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-bullhorn text-primary me-2"></i>Daftar Master Kampanye</h5>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Filter Status -->
        <select class="form-select form-select-sm rounded-2 w-auto" id="filterStatus" onchange="reloadTable()">
          <option value="">Semua Status</option>
          <option value="ACTIVE">Aktif Saja</option>
          <option value="DRAFT">Draft</option>
          <option value="COMPLETED">Selesai</option>
          <option value="CANCELLED">Dibatalkan</option>
        </select>
        <a href="<?= base_url('campaigns/export') ?>" class="btn btn-outline-success btn-sm rounded-2" title="Export CSV Data Kampanye">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-primary btn-sm rounded-2 shadow-sm" onclick="openAddModal()">
          <i class="fas fa-plus me-1"></i> Buat Kampanye Baru
        </button>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table id="campaignTable" class="table table-hover align-middle w-100" style="font-size: 13.5px;">
          <thead class="table-light text-secondary text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
            <tr>
              <th style="width: 50px;">ID</th>
              <th>Nama Kampanye &amp; Rincian</th>
              <th style="width: 250px;">Alokasi Anggaran (Deal KOL)</th>
              <th style="width: 170px;">Periode Pelaksanaan</th>
              <th style="width: 110px;" class="text-center">Status</th>
              <th style="width: 120px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Modal: Tambah / Edit Kampanye -->
<div class="modal fade" id="campaignModal" tabindex="-1" aria-labelledby="campaignModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold" id="campaignModalLabel"><i class="fas fa-bullhorn me-2 text-primary"></i>Buat Kampanye Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="campaignForm">
        <input type="hidden" name="id" id="campaignId">
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Judul Kampanye <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" id="campaignTitle" placeholder="Contoh: Metafora Parfum Launching Day" required>
              <div class="invalid-feedback" id="err_title"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Total Anggaran (Rp) <span class="text-danger">*</span></label>
              <input type="number" step="100000" class="form-control" name="budget_total" id="campaignBudget" placeholder="0" required>
              <div class="invalid-feedback" id="err_budget_total"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Target Total Views</label>
              <input type="number" class="form-control" name="target_views" id="campaignTargetViews" placeholder="Contoh: 1000000">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="start_date" id="campaignStartDate" required>
              <div class="invalid-feedback" id="err_start_date"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="end_date" id="campaignEndDate" required>
              <div class="invalid-feedback" id="err_end_date"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status Kampanye <span class="text-danger">*</span></label>
              <select class="form-select" name="status" id="campaignStatus" required>
                <option value="DRAFT">DRAFT (Penyusunan Konsep)</option>
                <option value="ACTIVE">ACTIVE (Sedang Berjalan)</option>
                <option value="COMPLETED">COMPLETED (Selesai)</option>
                <option value="CANCELLED">CANCELLED (Dibatalkan)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Model Rekrutmen KOL</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" name="is_open_registration" value="1" id="campaignOpenReg">
                <label class="form-check-label fw-semibold" for="campaignOpenReg">Buka Pendaftaran (Open Campaign di Datahub)</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Produk yang Di-endorse</label>
              <div class="border rounded-2 p-2" style="max-height: 140px; overflow-y: auto;">
                <?php if (!empty($products)): ?>
                  <?php foreach ($products as $p): ?>
                    <div class="form-check">
                      <input class="form-check-input product-check" type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>" id="prod_<?= $p['id'] ?>">
                      <label class="form-check-label small" for="prod_<?= $p['id'] ?>">
                        <span class="fw-semibold text-dark"><?= esc($p['name']) ?></span> (SKU: <?= esc($p['sku']) ?> - Stok: <?= $p['stock_qty'] ?>)
                      </label>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted small">Belum ada data produk di master.</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Deskripsi &amp; Objektif Kampanye</label>
              <textarea class="form-control" name="description" id="campaignDesc" rows="3" placeholder="Target KPI, konsep promosi, atau ketentuan khusus..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveCampaign">
            <i class="fas fa-save me-1"></i> Simpan Kampanye
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let table;

$(document).ready(function() {
  table = $('#campaignTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('campaigns/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.filter_status = $('#filterStatus').val();
      }
    },
    columns: [
      { data: 'id' },
      { data: 'title' },
      { data: 'budget' },
      { data: 'period' },
      { data: 'status', className: 'text-center' },
      { data: 'action', orderable: false, className: 'text-center' }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari kampanye...",
      lengthMenu: "Tampilkan _MENU_ baris",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ kampanye",
      infoEmpty: "Tidak ada kampanye",
      zeroRecords: "Kampanye tidak ditemukan",
      processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data...',
      paginate: {
        first: "«",
        last: "»",
        next: "›",
        previous: "‹"
      }
    }
  });

  $('#campaignForm').on('submit', function(e) {
    e.preventDefault();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    let btn = $('#btnSaveCampaign');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

    $.ajax({
      url: '<?= base_url('campaigns/save') ?>',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Kampanye');
        if (res.success) {
          bootstrap.Modal.getInstance(document.getElementById('campaignModal')).hide();
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: res.message,
            timer: 1800,
            showConfirmButton: false
          });
          table.ajax.reload(null, false);
          if (res.summary) updateSummary(res.summary);
        } else {
          if (res.messages) {
            $.each(res.messages, function(field, msg) {
              $('#err_' + field).text(msg);
              $('[name="' + field + '"]').addClass('is-invalid');
            });
          } else {
            Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
          }
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Kampanye');
        Swal.fire('Error', 'Gagal menghubungi server.', 'error');
      }
    });
  });
});

function reloadTable() {
  table.ajax.reload();
}

function updateSummary(s) {
  $('#summaryTotalCampaigns').text(s.total_campaigns);
  $('#summaryActiveCampaigns').text(s.active_campaigns);
  $('#summaryTotalBudget').text('Rp ' + Number(s.total_budget).toLocaleString('id-ID'));
  $('#summaryAbsorption').html(s.budget_absorption + '% <small class="text-muted fs-6">(Rp ' + Number(s.total_spent).toLocaleString('id-ID') + ')</small>');
}

function openAddModal() {
  $('#campaignForm')[0].reset();
  $('#campaignId').val('');
  $('.product-check').prop('checked', false);
  $('.is-invalid').removeClass('is-invalid');
  $('.invalid-feedback').text('');
  $('#campaignModalLabel').html('<i class="fas fa-bullhorn me-2 text-primary"></i>Buat Kampanye Baru');
  new bootstrap.Modal(document.getElementById('campaignModal')).show();
}

function editCampaign(id) {
  $.get('<?= base_url('campaigns/get-one') ?>/' + id, function(res) {
    if (res.success && res.data) {
      let c = res.data;
      $('#campaignId').val(c.id);
      $('#campaignTitle').val(c.title);
      $('#campaignBudget').val(c.budget_total);
      $('#campaignTargetViews').val(c.target_views);
      $('#campaignStartDate').val(c.start_date);
      $('#campaignEndDate').val(c.end_date);
      $('#campaignStatus').val(c.status);
      $('#campaignOpenReg').prop('checked', c.is_open_registration == 1);
      $('#campaignDesc').val(c.description);

      $('.product-check').prop('checked', false);
      if (c.product_ids && c.product_ids.length > 0) {
        c.product_ids.forEach(function(pId) {
          $('#prod_' + pId).prop('checked', true);
        });
      }

      $('.is-invalid').removeClass('is-invalid');
      $('.invalid-feedback').text('');
      $('#campaignModalLabel').html('<i class="fas fa-edit me-2 text-primary"></i>Edit Data Kampanye');
      new bootstrap.Modal(document.getElementById('campaignModal')).show();
    } else {
      Swal.fire('Error', res.message || 'Data tidak ditemukan.', 'error');
    }
  });
}

function deleteCampaign(id) {
  Swal.fire({
    title: 'Hapus Kampanye ini?',
    text: "Data yang dihapus akan disimpan ke arsip (soft delete).",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= base_url('campaigns/delete') ?>/' + id, function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Selesai!',
            text: res.message,
            timer: 1800,
            showConfirmButton: false
          });
          table.ajax.reload(null, false);
          if (res.summary) updateSummary(res.summary);
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }
      }, 'json');
    }
  });
}
</script>
<?= $this->endSection() ?>
