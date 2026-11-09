<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-edit text-primary me-2"></i>Tulis Artikel Baru</h3>
        <p class="text-muted small mb-0">Buat artikel baru untuk publikasi di landing page dan portal Kolab.id.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('articles') ?>" class="text-decoration-none">Artikel</a></li>
          <li class="breadcrumb-item active" aria-current="page">Baru</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('articles/add_save'); ?>" method="post" enctype="multipart/form-data" id="form_article">
      <?= csrf_field(); ?>
      <input type="hidden" name="save_type" id="save_type" value="back">
      <input type="hidden" name="cover_image_name" id="cover_image_name" value="">
      <input type="hidden" name="cover_image_uuid" id="cover_image_uuid" value="">

      <div class="row g-4">
        <!-- Main Form Column (Left) -->
        <div class="col-12 col-lg-8">
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">

              <!-- Title -->
              <div class="mb-3">
                <label for="title" class="form-label fw-bold">Judul Artikel <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" name="title" id="title" placeholder="Masukkan judul artikel yang menarik..." value="<?= old('title'); ?>" required maxlength="200">
                <div class="form-text">Maksimal 200 karakter.</div>
              </div>

              <!-- Slug -->
              <div class="mb-3">
                <label for="slug" class="form-label fw-bold">Slug URL</label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted small">/artikel/</span>
                  <input type="text" class="form-control" name="slug" id="slug" placeholder="otomatis-dari-judul" value="<?= old('slug'); ?>">
                </div>
                <div class="form-text">Kosongkan untuk membuat slug otomatis dari judul artikel.</div>
              </div>

              <!-- Excerpt -->
              <div class="mb-3">
                <label for="excerpt" class="form-label fw-bold">Ringkasan Singkat (Excerpt)</label>
                <textarea class="form-control" name="excerpt" id="excerpt" rows="3" maxlength="500" placeholder="Ringkasan singkat 1-2 kalimat untuk kartu artikel..."><?= old('excerpt'); ?></textarea>
                <div class="form-text">Maksimal 500 karakter. Jika kosong, akan diambil otomatis dari konten.</div>
              </div>

              <!-- Content -->
              <div class="mb-3">
                <label for="content" class="form-label fw-bold">Konten Artikel <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace" name="content" id="content" rows="15" placeholder="Tuliskan naskah isi artikel lengkap..." required><?= old('content'); ?></textarea>
              </div>

            </div>
          </div>

          <!-- SEO Metadata Box -->
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-search text-secondary me-2"></i>Pengaturan SEO &amp; Meta Tags</h5>
            </div>
            <div class="card-body p-4">
              <div class="mb-3">
                <label for="meta_title" class="form-label fw-semibold">Meta Title</label>
                <input type="text" class="form-control" name="meta_title" id="meta_title" placeholder="Judul khusus untuk Google / mesin pencari (opsional)" value="<?= old('meta_title'); ?>" maxlength="200">
              </div>
              <div class="mb-0">
                <label for="meta_description" class="form-label fw-semibold">Meta Description</label>
                <textarea class="form-control" name="meta_description" id="meta_description" rows="2" maxlength="320" placeholder="Deskripsi ringkas yang tampil di hasil pencarian Google..."><?= old('meta_description'); ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Column (Right) -->
        <div class="col-12 col-lg-4">
          <!-- Publication Settings -->
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-cog text-secondary me-2"></i>Publikasi</h5>
            </div>
            <div class="card-body p-4">
              <div class="mb-3">
                <label for="status" class="form-label fw-semibold">Status Artikel</label>
                <select class="form-select" name="status" id="status">
                  <option value="DRAFT" <?= (old('status') === 'DRAFT') ? 'selected' : ''; ?>>DRAFT (Simpan Sementara)</option>
                  <option value="PUBLISHED" <?= (old('status') === 'PUBLISHED' || empty(old('status'))) ? 'selected' : ''; ?>>PUBLISHED (Langsung Tayang)</option>
                  <option value="ARCHIVED" <?= (old('status') === 'ARCHIVED') ? 'selected' : ''; ?>>ARCHIVED (Diarsipkan)</option>
                </select>
              </div>

              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="is_featured" id="is_featured" value="1" <?= old('is_featured') ? 'checked' : ''; ?>>
                <label class="form-check-label fw-semibold" for="is_featured">Jadikan Artikel Pilihan (Featured)</label>
              </div>

              <hr>

              <!-- Category -->
              <div class="mb-3">
                <label for="category" class="form-label fw-semibold">Kategori</label>
                <input type="text" class="form-control" name="category" id="category" placeholder="Contoh: Insight, Strategi, Riset" value="<?= old('category'); ?>" list="category_list">
                <datalist id="category_list">
                  <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                      <?php $cName = is_object($cat) ? $cat->category : ($cat['category'] ?? ''); ?>
                      <option value="<?= esc($cName); ?>">
                    <?php endforeach; ?>
                  <?php endif; ?>
                </datalist>
                <div class="form-text">Pilih dari saran atau ketik kategori baru.</div>
              </div>

              <!-- Tags -->
              <div class="mb-0">
                <label for="tags" class="form-label fw-semibold">Tagar / Tags</label>
                <input type="text" class="form-control" name="tags" id="tags" placeholder="KOL, Campaign, Influencer" value="<?= old('tags'); ?>">
                <div class="form-text">Pisahkan beberapa tag dengan tanda koma (,).</div>
              </div>
            </div>
          </div>

          <!-- Cover Image Upload -->
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-image text-secondary me-2"></i>Gambar Sampul (Cover)</h5>
            </div>
            <div class="card-body p-4 text-center">
              <div id="cover_preview_wrapper" class="mb-3 d-none">
                <img id="cover_preview" src="" alt="Cover Preview" class="img-fluid rounded border shadow-sm" style="max-height: 200px; width: 100%; object-fit: cover;">
              </div>

              <div class="mb-3 text-start">
                <label for="cover_image" class="form-label fw-semibold">Pilih File Gambar</label>
                <input class="form-control" type="file" name="cover_image" id="cover_image" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3 d-grid gap-2">
              <button type="button" class="btn btn-primary rounded-2 shadow-sm py-2" id="btn_save_back">
                <i class="fas fa-check me-1"></i> Simpan &amp; Kembali
              </button>
              <button type="button" class="btn btn-outline-primary rounded-2 py-2" id="btn_save_stay">
                <i class="fas fa-save me-1"></i> Simpan Saja
              </button>
              <a href="<?= site_url('articles'); ?>" class="btn btn-light border rounded-2 py-2 text-secondary">
                <i class="fas fa-times me-1"></i> Batal
              </a>
            </div>
          </div>

        </div>
      </div>
    </form>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
    // Slug auto-generation from Title
    var slugEdited = false;
    $('#slug').on('input', function() {
        slugEdited = $(this).val().trim() !== '';
    });

    $('#title').on('input', function() {
        if (!slugEdited) {
            var slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#slug').val(slug);
        }
    });

    // Cover image preview
    $('#cover_image').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(evt) {
                $('#cover_preview').attr('src', evt.target.result);
                $('#cover_preview_wrapper').removeClass('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            $('#cover_preview_wrapper').addClass('d-none');
        }
    });

    // Save buttons
    $('#btn_save_back').on('click', function() {
        $('#save_type').val('back');
        $('#form_article').submit();
    });

    $('#btn_save_stay').on('click', function() {
        $('#save_type').val('stay');
        $('#form_article').submit();
    });

    // Keyboard shortcuts: Ctrl+s -> Save Stay, Ctrl+d -> Save Back, Ctrl+x -> Cancel
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('#btn_save_stay').click();
        }
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            $('#btn_save_back').click();
        }
        if (e.ctrlKey && e.key === 'x') {
            e.preventDefault();
            window.location.href = '<?= site_url('articles'); ?>';
        }
    });
});
</script>
<?= $this->endSection() ?>
