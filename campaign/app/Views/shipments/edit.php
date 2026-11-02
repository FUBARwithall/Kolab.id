<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-edit text-primary me-2"></i>Edit Pengiriman Sampel #<?= $shipment['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Perbarui nomor resi, ekspedisi kurir, barang sampel yang dikirim, atau status logistik.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipments') ?>" class="text-decoration-none">Pengiriman Sampel</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('shipments/view/' . $shipment['id']) ?>" class="text-decoration-none">#<?= $shipment['id'] ?></a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
      <div class="col-lg-10 col-xl-9">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-shipping-fast text-info me-2"></i>Edit Formulir Pengiriman #<?= $shipment['id'] ?>
              </h5>
              <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('shipments/view/' . $shipment['id']) ?>" class="btn btn-outline-info btn-sm rounded-2">
                  <i class="fas fa-eye me-1"></i> Detail
                </a>
                <a href="<?= base_url('shipments') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                  <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
              </div>
            </div>
          </div>
          <div class="card-body p-4">

            <div id="ajaxAlertContainer"></div>

            <form action="<?= base_url('shipments/edit_save/' . $shipment['id']) ?>" method="POST" id="form_shipment">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $shipment['id'] ?>">
              <input type="hidden" name="save_type" id="save_type" value="back">

              <?php
                $curStatus = strtoupper($shipment['shipping_status'] ?: ($shipment['status'] ?: 'PENDING'));
                $curCourier = $shipment['courier_name'] ?: ($shipment['courier'] ?: 'JNE');
                $curTracking = $shipment['tracking_number'] ?: ($shipment['tracking_no'] ?: '');
                $curRecipient = $shipment['recipient_name'] ?: ($shipment['kol_name'] ?? '');
                $curPhone = $shipment['recipient_phone'] ?: ($shipment['kol_whatsapp'] ?? '');
                $curAddress = $shipment['shipping_address'] ?: ($shipment['kol_address'] ?? '');
              ?>

              <!-- Target KOL & Kampanye -->
              <div class="mb-4">
                <label for="campaign_kol_id" class="form-label fw-semibold text-dark">
                  Target Talent KOL &amp; Kampanye <span class="text-danger">*</span>
                </label>
                <select class="form-select form-select-lg" name="campaign_kol_id" id="campaign_kol_id" required onchange="onKolSelectChanged()">
                  <?php foreach ($assignedKols as $ak): ?>
                    <?php
                      $fullAddr = trim(($ak['address'] ?? '') . ' ' . ($ak['city'] ?? '') . ' ' . ($ak['postal_code'] ?? ''));
                      $isSel = ($ak['id'] == $shipment['campaign_kol_id']) ? 'selected' : '';
                    ?>
                    <option value="<?= $ak['id'] ?>"
                            data-name="<?= esc($ak['kol_name']) ?>"
                            data-phone="<?= esc($ak['whatsapp'] ?? '') ?>"
                            data-address="<?= esc($fullAddr) ?>"
                            data-city="<?= esc($ak['city'] ?? '') ?>"
                            <?= $isSel ?>>
                      <?= esc($ak['kol_name']) ?> — Kampanye: <?= esc($ak['campaign_title']) ?> (<?= esc($ak['city'] ?: 'Kota belum ada') ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Recipient Info Fields -->
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label for="recipient_name" class="form-label fw-semibold text-dark">
                    Nama Lengkap Penerima <span class="text-danger">*</span>
                  </label>
                  <input type="text" class="form-control" name="recipient_name" id="recipient_name" value="<?= esc(old('recipient_name', $curRecipient)) ?>" placeholder="Nama penerima paket" required maxlength="150">
                </div>
                <div class="col-md-6">
                  <label for="recipient_phone" class="form-label fw-semibold text-dark">
                    Nomor WhatsApp / HP Penerima
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-success"><i class="fab fa-whatsapp"></i></span>
                    <input type="text" class="form-control" name="recipient_phone" id="recipient_phone" value="<?= esc(old('recipient_phone', $curPhone)) ?>" placeholder="08xxxxxxxxxx" maxlength="30">
                  </div>
                </div>
                <div class="col-md-12">
                  <label for="shipping_address" class="form-label fw-semibold text-dark">
                    Alamat Lengkap Pengiriman <span class="text-danger">*</span>
                  </label>
                  <textarea class="form-control" name="shipping_address" id="shipping_address" rows="3" placeholder="Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos..." required><?= esc(old('shipping_address', $curAddress)) ?></textarea>
                </div>
              </div>

              <hr class="my-4">

              <!-- Courier & Logistics Info -->
              <h6 class="fw-bold text-dark mb-3"><i class="fas fa-truck text-secondary me-2"></i>Detail Jasa Ekspedisi</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <label for="courier_name" class="form-label fw-semibold text-dark">
                    Jasa Kurir / Ekspedisi <span class="text-danger">*</span>
                  </label>
                  <select class="form-select" name="courier_name" id="courier_name" required>
                    <?php
                      $couriers = ['JNE', 'J&T', 'SiCepat', 'Anteraja', 'GoSend', 'GrabExpress', 'Lion Parcel', 'Ninja Xpress', 'Kurir Internal', 'Lainnya'];
                      foreach ($couriers as $cr):
                    ?>
                      <option value="<?= $cr ?>" <?= ($curCourier === $cr) ? 'selected' : '' ?>><?= $cr ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="tracking_number" class="form-label fw-semibold text-dark">
                    Nomor Resi / AWB <span class="text-danger">*</span>
                  </label>
                  <input type="text" class="form-control font-monospace" name="tracking_number" id="tracking_number" value="<?= esc(old('tracking_number', $curTracking)) ?>" placeholder="Contoh: JP1234567890" required maxlength="100">
                </div>
                <div class="col-md-4">
                  <label for="shipping_fee" class="form-label fw-semibold text-dark">
                    Biaya Ongkir (Rp)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted">Rp</span>
                    <input type="number" class="form-control font-monospace" name="shipping_fee" id="shipping_fee" value="<?= esc(old('shipping_fee', (float)($shipment['shipping_fee'] ?? 0))) ?>" min="0" step="500">
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="shipping_status" class="form-label fw-semibold text-dark">
                    Status Pengiriman <span class="text-danger">*</span>
                  </label>
                  <select class="form-select" name="shipping_status" id="shipping_status" required>
                    <option value="PENDING" <?= ($curStatus === 'PENDING') ? 'selected' : '' ?>>PENDING (Persiapan Packing)</option>
                    <option value="PROCESSING" <?= ($curStatus === 'PROCESSING') ? 'selected' : '' ?>>PROCESSING (Proses Gudang)</option>
                    <option value="SHIPPED" <?= ($curStatus === 'SHIPPED') ? 'selected' : '' ?>>SHIPPED (Sudah Diserahkan ke Ekspedisi)</option>
                    <option value="DELIVERED" <?= ($curStatus === 'DELIVERED') ? 'selected' : '' ?>>DELIVERED (Sudah Diterima KOL)</option>
                    <option value="RETURN_PENDING" <?= ($curStatus === 'RETURN_PENDING') ? 'selected' : '' ?>>RETURN PENDING (Menunggu Retur)</option>
                    <option value="RETURNED" <?= ($curStatus === 'RETURNED') ? 'selected' : '' ?>>RETURNED (Paket Retur Diterima)</option>
                  </select>
                  <div class="form-text small">Perubahan status ke SHIPPED/DELIVERED atau pembatalan otomatis memperbarui stok barang sampel.</div>
                </div>
                <div class="col-md-6">
                  <label for="notes" class="form-label fw-semibold text-dark">Catatan Tambahan Pengiriman</label>
                  <input type="text" class="form-control" name="notes" id="notes" value="<?= esc(old('notes', $shipment['notes'] ?? '')) ?>" placeholder="Contoh: Titip satpam, barang fragile...">
                </div>
              </div>

              <hr class="my-4">

              <!-- Shipped Items Repeater -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-boxes text-success me-2"></i>Daftar Barang Sampel yang Dikirim</h6>
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill" onclick="addItemRow()">
                  <i class="fas fa-plus me-1"></i> Tambah Barang
                </button>
              </div>

              <div id="itemsContainer" class="mb-4">
                <!-- Dynamically populated item rows -->
              </div>

              <hr class="my-4">

              <!-- Form Buttons -->
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <a href="<?= base_url('shipments') ?>" class="btn btn-outline-secondary rounded-2" id="btn_cancel">
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
const availableProducts = <?= json_encode($allProducts) ?>;
const existingItems = <?= json_encode($shipment['items'] ?? []) ?>;
let itemIdx = 0;

function onKolSelectChanged() {
  const sel = $('#campaign_kol_id option:selected');
  if (sel.val()) {
    if (!$('#recipient_name').val() || $('#recipient_name').val() === '') {
      $('#recipient_name').val(sel.data('name') || '');
    }
    if (!$('#recipient_phone').val() || $('#recipient_phone').val() === '') {
      $('#recipient_phone').val(sel.data('phone') || '');
    }
    if (!$('#shipping_address').val() || $('#shipping_address').val() === '') {
      $('#shipping_address').val(sel.data('address') || '');
    }
  }
}

function addItemRow(prodId = '', varId = '', qty = 1) {
  itemIdx++;
  let optionsHtml = '<option value="">-- Pilih Produk Sampel --</option>';
  availableProducts.forEach(p => {
    const isSel = (p.id == prodId) ? 'selected' : '';
    optionsHtml += `<option value="${p.id}" ${isSel}>${p.name} (Stok: ${p.stock_qty || p.stock || 0})</option>`;
  });

  const rowHtml = `
    <div class="card border bg-light p-3 mb-2 rounded-3 item-row" id="itemRow_${itemIdx}">
      <div class="row g-2 align-items-center">
        <div class="col-md-5">
          <label class="small text-muted fw-semibold">Produk</label>
          <select class="form-select form-select-sm product-select" name="items[${itemIdx}][product_id]" onchange="onProductRowChanged(${itemIdx})" required>
            ${optionsHtml}
          </select>
        </div>
        <div class="col-md-4">
          <label class="small text-muted fw-semibold">Varian (Opsional)</label>
          <select class="form-select form-select-sm variant-select" name="items[${itemIdx}][variant_id]" id="varSelect_${itemIdx}">
            <option value="">Standar / Non-Varian</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="small text-muted fw-semibold">Jumlah (Pcs)</label>
          <input type="number" class="form-control form-select-sm font-monospace" name="items[${itemIdx}][qty]" value="${qty}" min="1" required>
        </div>
        <div class="col-md-1 text-end pt-3">
          <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1" style="width: 30px; height: 30px;" onclick="$('#itemRow_${itemIdx}').remove()">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
    </div>
  `;

  $('#itemsContainer').append(rowHtml);
  if (prodId) {
    onProductRowChanged(itemIdx, varId);
  }
}

function onProductRowChanged(idx, selectedVarId = '') {
  const prodId = $(`#itemRow_${idx} .product-select`).val();
  const $varSelect = $(`#varSelect_${idx}`);
  $varSelect.html('<option value="">Standar / Non-Varian</option>');

  const prod = availableProducts.find(p => p.id == prodId);
  if (prod && prod.variants && prod.variants.length > 0) {
    prod.variants.forEach(v => {
      const isSel = (v.id == selectedVarId) ? 'selected' : '';
      $varSelect.append(`<option value="${v.id}" ${isSel}>${v.name} (Stok: ${v.stock_qty})</option>`);
    });
  }
}

$(document).ready(function() {
  // Populate existing items
  if (existingItems && existingItems.length > 0) {
    existingItems.forEach(it => {
      addItemRow(it.product_id, it.variant_id, it.qty);
    });
  } else {
    addItemRow();
  }

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

    const form = $('#form_shipment')[0];
    const formData = new FormData(form);
    const $btn = $(this);
    const origHtml = $btn.html();

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
    $('#ajaxAlertContainer').html('');

    $.ajax({
      url: $('#form_shipment').attr('action'),
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
            window.location.href = res.redirect || '<?= base_url('shipments') ?>';
          } else {
            $('#ajaxAlertContainer').html(
              '<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">' +
                '<i class="fas fa-check-circle me-2"></i>' + (res.message || 'Perubahan pengiriman berhasil disimpan.') +
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
