<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-edit text-primary me-2"></i>Edit Item Sampel #<?= $item['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Perbarui jumlah unit atau produk sampel yang dialokasikan ke pengiriman.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipments') ?>" class="text-decoration-none">Pengiriman</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipment-items') ?>" class="text-decoration-none">Item Sampel</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit #<?= $item['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-check-circle fa-lg me-2"></i>
          <div><?= session()->getFlashdata('success') ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
          <div><?= session()->getFlashdata('error') ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-lg-8 col-xl-7">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-box text-success me-2"></i>Edit Alokasi Item Sampel #<?= $item['id'] ?>
              </h5>
              <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('shipment-items/view/' . $item['id']) ?>" class="btn btn-outline-info btn-sm rounded-2">
                  <i class="fas fa-eye me-1"></i> Detail
                </a>
                <a href="<?= base_url('shipment-items') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                  <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
              </div>
            </div>
          </div>
          <div class="card-body p-4">

            <div id="ajaxAlertContainer"></div>

            <form action="<?= base_url('shipment-items/edit_save/' . $item['id']) ?>" method="POST" id="form_shipment_item">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $item['id'] ?>">
              <input type="hidden" name="save_type" id="save_type" value="back">

              <!-- Target Shipment Selection -->
              <div class="mb-4">
                <label for="shipment_id" class="form-label fw-semibold text-dark">
                  Target Paket Pengiriman (Resi / Penerima) <span class="text-danger">*</span>
                </label>
                <select class="form-select form-select-lg" name="shipment_id" id="shipment_id" required>
                  <?php foreach ($allShipments as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($item['shipment_id'] == $s['id']) ? 'selected' : '' ?>>
                      #<?= $s['id'] ?> &bull; <?= esc($s['tracking_number'] ?: 'Draft Belum Resi') ?> (<?= esc($s['courier_name'] ?: 'Kurir') ?>) — Penerima: <?= esc($s['recipient_name'] ?: 'KOL') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Product Selection -->
              <div class="mb-3">
                <label for="product_id" class="form-label fw-semibold text-dark">
                  Produk Sampel <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="product_id" id="product_id" required onchange="onProductChanged()">
                  <?php foreach ($allProducts as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($item['product_id'] == $p['id']) ? 'selected' : '' ?>>
                      <?= esc($p['name']) ?> (SKU: <?= esc($p['sku']) ?> &bull; Stok: <?= $p['stock_qty'] ?? $p['stock'] ?? 0 ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Variant Selection -->
              <div class="mb-3">
                <label for="variant_id" class="form-label fw-semibold text-dark">
                  Varian Khusus Produk (Opsional)
                </label>
                <select class="form-select" name="variant_id" id="variant_id">
                  <option value="">Standar / Non-Varian</option>
                </select>
                <div class="form-text small">Pilih varian spesifik jika produk memiliki sub-varian.</div>
              </div>

              <!-- Quantity Input -->
              <div class="mb-4">
                <label for="qty" class="form-label fw-semibold text-dark">
                  Jumlah Sampel Dikirim (Qty) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted"><i class="fas fa-cubes"></i></span>
                  <input type="number" class="form-control font-monospace fs-5 fw-bold" name="qty" id="qty" value="<?= esc(old('qty', $item['qty'])) ?>" min="1" step="1" required>
                  <span class="input-group-text bg-light text-muted">Pcs / Unit</span>
                </div>
                <div class="form-text small">Perubahan angka kuantitas akan otomatis menyesuaikan saldo stok produk di inventaris gudang.</div>
              </div>

              <hr class="my-4">

              <!-- Form Buttons -->
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <a href="<?= base_url('shipment-items') ?>" class="btn btn-outline-secondary rounded-2" id="btn_cancel">
                  <i class="fas fa-undo me-1"></i> Batal (Ctrl+X)
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <button type="button" class="btn btn-info text-white rounded-2 btn_save_action" data-stype="stay" id="btn_save_stay" title="Simpan &amp; Tetap di Halaman (Ctrl+S)">
                    <i class="fas fa-save me-1"></i> Simpan &amp; Tetap di Sini (Ctrl+S)
                  </button>
                  <button type="button" class="btn btn-primary rounded-2 btn_save_action" data-stype="back" id="btn_save_back" title="Simpan &amp; Kembali ke Daftar (Ctrl+D)">
                    <i class="fas fa-check me-1"></i> Simpan &amp; Kembali (Ctrl+D)
                  </button>
                </div>
              </div>

            </form>

          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
const allProductsData = <?= json_encode($allProducts) ?>;
const curVariantId = <?= json_encode($item['variant_id'] ?? null) ?>;

function onProductChanged(selectedVarId = curVariantId) {
  const prodId = $('#product_id').val();
  const $varSelect = $('#variant_id');
  $varSelect.html('<option value="">Standar / Non-Varian</option>');

  const prod = allProductsData.find(p => p.id == prodId);
  if (prod && prod.variants && prod.variants.length > 0) {
    prod.variants.forEach(v => {
      const isSel = (v.id == selectedVarId) ? 'selected' : '';
      $varSelect.append(`<option value="${v.id}" ${isSel}>${v.name} (Stok: ${v.stock_qty})</option>`);
    });
  }
}

$(document).ready(function() {
  onProductChanged(curVariantId);

  // Hotkeys CI3 Parity
  $(document).on('keydown', function(e) {
    if (e.ctrlKey && e.key.toLowerCase() === 's') {
      e.preventDefault();
      $('#btn_save_stay').trigger('click');
    } else if (e.ctrlKey && e.key.toLowerCase() === 'd') {
      e.preventDefault();
      $('#btn_save_back').trigger('click');
    } else if (e.ctrlKey && e.key.toLowerCase() === 'x') {
      e.preventDefault();
      $('#btn_cancel').trigger('click');
    }
  });

  // AJAX submission handler
  $('.btn_save_action').on('click', function(e) {
    e.preventDefault();
    const saveType = $(this).data('stype');
    $('#save_type').val(saveType);

    const form = $('#form_shipment_item')[0];
    const formData = new FormData(form);
    const $btn = $(this);
    const origHtml = $btn.html();

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
    $('#ajaxAlertContainer').html('');

    $.ajax({
      url: $('#form_shipment_item').attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(res) {
        $btn.prop('disabled', false).html(origHtml);
        if (res.success) {
          if (saveType === 'back') {
            window.location.href = res.redirect || '<?= base_url('shipment-items') ?>';
          } else {
            $('#ajaxAlertContainer').html(
              '<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">' +
                '<i class="fas fa-check-circle me-2"></i>' + (res.message || 'Perubahan berhasil disimpan.') +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
              '</div>'
            );
            $('html, body').animate({ scrollTop: 0 }, 300);
          }
        } else {
          $('#ajaxAlertContainer').html(
            '<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">' +
              '<i class="fas fa-exclamation-triangle me-2"></i>' + (res.message || 'Gagal menyimpan perubahan.') +
              '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
          );
          $('html, body').animate({ scrollTop: 0 }, 300);
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(origHtml);
        const err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
        $('#ajaxAlertContainer').html(
          '<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">' +
            '<i class="fas fa-exclamation-circle me-2"></i>' + err +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
          '</div>'
        );
        $('html, body').animate({ scrollTop: 0 }, 300);
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
