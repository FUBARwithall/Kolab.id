<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-plus-circle text-primary me-2"></i>Tambah Produk Baru
        </h3>
        <p class="text-muted small mb-0">Daftarkan master produk atau inventaris sampel baru untuk kolaborasi influencer.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('products') ?>" class="text-decoration-none">Katalog Produk</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tambah Baru</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
          <div><?= session()->getFlashdata('error') ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
      <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center mb-1">
          <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
          <strong>Terdapat kesalahan pengisian formulir:</strong>
        </div>
        <ul class="mb-0 ps-4 small">
          <?php foreach (session()->getFlashdata('errors') as $err): ?>
            <li><?= esc($err) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-9">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-box-open text-primary me-2"></i>Formulir Master Produk
              </h5>
              <a href="<?= base_url('products') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Katalog
              </a>
            </div>
          </div>
          <div class="card-body p-4">

            <div id="ajaxAlertContainer"></div>

            <form action="<?= base_url('products/add_save') ?>" method="POST" id="form_products" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <input type="hidden" name="save_type" id="save_type" value="back">

              <div class="row g-3">

                <!-- SKU & Nama Produk -->
                <div class="col-md-4">
                  <label for="sku" class="form-label fw-semibold text-dark">
                    Kode SKU <span class="text-danger">*</span>
                  </label>
                  <input type="text" class="form-control font-monospace" name="sku" id="sku" placeholder="Contoh: PRD-001" value="<?= esc(old('sku')) ?>" required maxlength="50" style="text-transform: uppercase;">
                  <div class="form-text small">Kode unik identifikasi produk (maks. 50 karakter).</div>
                </div>

                <div class="col-md-8">
                  <label for="name" class="form-label fw-semibold text-dark">
                    Nama Produk <span class="text-danger">*</span>
                  </label>
                  <input type="text" class="form-control" name="name" id="name" placeholder="Nama lengkap produk sampel" value="<?= esc(old('name')) ?>" required maxlength="150">
                  <div class="form-text small">Nama produk yang akan ditampilkan kepada tim & KOL (maks. 150 karakter).</div>
                </div>

                <!-- Kategori & Status -->
                <div class="col-md-6">
                  <label for="category" class="form-label fw-semibold text-dark">
                    Kategori Produk
                  </label>
                  <input type="text" class="form-control" name="category" id="category" placeholder="Contoh: Skincare, Fashion, F&B" value="<?= esc(old('category')) ?>" list="categoryList" maxlength="100">
                  <datalist id="categoryList">
                    <option value="Skincare">
                    <option value="Cosmetics">
                    <option value="Fashion">
                    <option value="Food & Beverage">
                    <option value="Electronics & Gadgets">
                    <option value="Health & Wellness">
                    <option value="Home & Living">
                  </datalist>
                  <div class="form-text small">Kelompok kategori produk atau sampel.</div>
                </div>

                <div class="col-md-6">
                  <label for="is_active" class="form-label fw-semibold text-dark">
                    Status Publikasi <span class="text-danger">*</span>
                  </label>
                  <select class="form-select" name="is_active" id="is_active" required>
                    <option value="1" <?= old('is_active', '1') == '1' ? 'selected' : '' ?>>ACTIVE (Tersedia untuk Kampanye)</option>
                    <option value="0" <?= old('is_active') === '0' ? 'selected' : '' ?>>INACTIVE (Diarsipkan / Nonaktif)</option>
                  </select>
                  <div class="form-text small">Status ketersediaan untuk dipilih pada kampanye.</div>
                </div>

                <!-- Stok Awal & Harga Satuan -->
                <div class="col-md-6">
                  <label for="stock" class="form-label fw-semibold text-dark">
                    Stok Awal (Unit Sampel)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fas fa-cubes"></i></span>
                    <input type="number" class="form-control font-monospace" name="stock" id="stock" placeholder="0" value="<?= esc(old('stock', '0')) ?>" min="0" step="1">
                    <span class="input-group-text bg-light text-muted">Pcs / Unit</span>
                  </div>
                  <div class="form-text small">Jumlah fisik sampel yang siap dialokasikan ke kreator.</div>
                </div>

                <div class="col-md-6">
                  <label for="price" class="form-label fw-semibold text-dark">
                    Estimasi Harga Satuan (Rp)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted">Rp</span>
                    <input type="number" class="form-control font-monospace" name="price" id="price" placeholder="0" value="<?= esc(old('price', '0')) ?>" min="0" step="100">
                  </div>
                  <div class="form-text small">Perkiraan valuasi nominal per item produk.</div>
                </div>

                <!-- Foto Produk & Preview -->
                <div class="col-md-12">
                  <label for="image_file" class="form-label fw-semibold text-dark">Foto / Banner Produk</label>
                  <div class="d-flex align-items-center gap-3">
                    <div id="imagePreviewContainer" class="rounded-3 border d-flex align-items-center justify-content-center bg-light text-muted overflow-hidden" style="width: 80px; height: 80px; min-width: 80px;">
                      <i class="fas fa-image fa-2x text-secondary"></i>
                    </div>
                    <div class="flex-grow-1">
                      <input type="file" class="form-control" name="image_file" id="image_file" accept="image/png,image/jpeg,image/webp">
                      <div class="form-text small">Format file: JPG, PNG, WEBP. Maksimal ukuran 2 MB.</div>
                    </div>
                  </div>
                </div>

                <!-- Deskripsi Produk -->
                <div class="col-md-12">
                  <label for="description" class="form-label fw-semibold text-dark">Deskripsi &amp; Petunjuk Produk</label>
                  <textarea class="form-control" name="description" id="description" rows="4" placeholder="Keterangan lengkap produk, keunggulan, spesifikasi rasa/warna/ukuran, atau catatan khusus pengiriman sampel ke KOL..."><?= esc(old('description')) ?></textarea>
                  <div class="form-text small">Catatan ini akan terlihat saat pengiriman sampel atau briefing kampanye.</div>
                </div>

              </div>

              <hr class="my-4">

              <!-- Form Buttons -->
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <a href="<?= base_url('products') ?>" class="btn btn-outline-secondary rounded-2" id="btn_cancel">
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
$(document).ready(function() {

  // Preview Gambar
  $('#image_file').on('change', function(e) {
    var file = e.target.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function(evt) {
        $('#imagePreviewContainer').html('<img src="' + evt.target.result + '" class="w-100 h-100 object-fit-cover rounded-3" alt="Preview">');
      };
      reader.readAsDataURL(file);
    } else {
      $('#imagePreviewContainer').html('<i class="fas fa-image fa-2x text-secondary"></i>');
    }
  });

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

  // Handle Save Form via AJAX
  $('.btn_save_action').on('click', function(e) {
    e.preventDefault();
    var saveType = $(this).data('stype');
    $('#save_type').val(saveType);

    var form = $('#form_products')[0];
    var formData = new FormData(form);
    var $btn = $(this);
    var originalHtml = $btn.html();

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...');
    $('#ajaxAlertContainer').html('');

    $.ajax({
      url: $('#form_products').attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(res) {
        $btn.prop('disabled', false).html(originalHtml);

        if (res.success) {
          if (saveType === 'back') {
            window.location.href = res.redirect || '<?= base_url('products') ?>';
          } else {
            $('#ajaxAlertContainer').html(
              '<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">' +
                '<i class="fas fa-check-circle me-2"></i>' + (res.message || 'Produk berhasil disimpan.') +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
              '</div>'
            );
            if (res.id) {
              window.location.href = '<?= base_url('products/edit') ?>/' + res.id;
            }
          }
        } else {
          var errorMsg = res.message || 'Terjadi kesalahan validasi.';
          $('#ajaxAlertContainer').html(
            '<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">' +
              '<i class="fas fa-exclamation-triangle me-2"></i>' + errorMsg +
              '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
          );
          $('html, body').animate({ scrollTop: 0 }, 300);
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        var errText = 'Gagal menyimpan data produk. Silakan periksa kembali isian Anda.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errText = xhr.responseJSON.message;
        }
        $('#ajaxAlertContainer').html(
          '<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">' +
            '<i class="fas fa-exclamation-circle me-2"></i>' + errText +
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
