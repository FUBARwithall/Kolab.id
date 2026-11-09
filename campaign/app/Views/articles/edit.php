<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-pencil-alt text-primary me-2"></i>Edit Artikel</h3>
        <p class="text-muted small mb-0">Perbarui konten artikel, gambar sampul, atau status publikasi.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('articles') ?>" class="text-decoration-none">Artikel</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit #<?= (int) $article->id; ?></li>
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

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('articles/edit_save/' . $article->id); ?>" method="post" enctype="multipart/form-data" id="form_article">
      <?= csrf_field(); ?>
      <input type="hidden" name="id" value="<?= (int) $article->id; ?>">
      <input type="hidden" name="save_type" id="save_type" value="stay">
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
                <input type="text" class="form-control form-control-lg" name="title" id="title" placeholder="Masukkan judul artikel..." value="<?= esc(old('title', $article->title)); ?>" required maxlength="200">
                <div class="form-text">Maksimal 200 karakter.</div>
              </div>

              <!-- Slug -->
              <div class="mb-3">
                <label for="slug" class="form-label fw-bold">Slug URL</label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted small">/artikel/</span>
                  <input type="text" class="form-control" name="slug" id="slug" placeholder="slug-artikel" value="<?= esc(old('slug', $article->slug)); ?>">
                </div>
                <div class="form-text">URL ramah SEO untuk artikel ini.</div>
              </div>

              <!-- Excerpt -->
              <div class="mb-3">
                <label for="excerpt" class="form-label fw-bold">Ringkasan Singkat (Excerpt)</label>
                <textarea class="form-control" name="excerpt" id="excerpt" rows="3" maxlength="500" placeholder="Ringkasan singkat 1-2 kalimat..."><?= esc(old('excerpt', $article->excerpt)); ?></textarea>
                <div class="form-text">Maksimal 500 karakter.</div>
              </div>

              <!-- Content -->
              <div class="mb-3">
                <label for="content" class="form-label fw-bold">Konten Artikel <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace" name="content" id="content" rows="15" placeholder="Tuliskan naskah isi artikel lengkap..." required><?= esc(old('content', $article->content)); ?></textarea>
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
                <input type="text" class="form-control" name="meta_title" id="meta_title" placeholder="Judul khusus untuk hasil pencarian Google" value="<?= esc(old('meta_title', $article->meta_title)); ?>" maxlength="200">
              </div>
              <div class="mb-0">
                <label for="meta_description" class="form-label fw-semibold">Meta Description</label>
                <textarea class="form-control" name="meta_description" id="meta_description" rows="2" maxlength="320" placeholder="Deskripsi ringkas yang tampil di hasil pencarian Google..."><?= esc(old('meta_description', $article->meta_description)); ?></textarea>
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
                  <option value="DRAFT" <?= (old('status', $article->status) === 'DRAFT') ? 'selected' : ''; ?>>DRAFT (Simpan Sementara)</option>
                  <option value="PUBLISHED" <?= (old('status', $article->status) === 'PUBLISHED') ? 'selected' : ''; ?>>PUBLISHED (Tayang)</option>
                  <option value="ARCHIVED" <?= (old('status', $article->status) === 'ARCHIVED') ? 'selected' : ''; ?>>ARCHIVED (Diarsipkan)</option>
                </select>
              </div>

              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="is_featured" id="is_featured" value="1" <?= old('is_featured', $article->is_featured) ? 'checked' : ''; ?>>
                <label class="form-check-label fw-semibold" for="is_featured">Jadikan Artikel Pilihan (Featured)</label>
              </div>

              <div class="small text-muted mb-2">
                <div><i class="fas fa-user me-1"></i> Penulis: <strong><?= esc($article->author_name ?: 'Admin'); ?></strong></div>
                <div><i class="fas fa-eye me-1"></i> Dilihat: <strong><?= (int) $article->views; ?> kali</strong></div>
                <div><i class="fas fa-calendar-alt me-1"></i> Dibuat: <?= esc($article->created_at ?: '-'); ?></div>
                <?php if (!empty($article->published_at)): ?>
                  <div><i class="fas fa-check me-1"></i> Diterbitkan: <?= esc($article->published_at); ?></div>
                <?php endif; ?>
              </div>

              <hr>

              <!-- Category -->
              <div class="mb-3">
                <label for="category" class="form-label fw-semibold">Kategori</label>
                <input type="text" class="form-control" name="category" id="category" placeholder="Contoh: Insight, Strategi" value="<?= esc(old('category', $article->category)); ?>" list="category_list">
                <datalist id="category_list">
                  <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                      <?php $cName = is_object($cat) ? $cat->category : ($cat['category'] ?? ''); ?>
                      <option value="<?= esc($cName); ?>">
                    <?php endforeach; ?>
                  <?php endif; ?>
                </datalist>
              </div>

              <!-- Tags -->
              <div class="mb-0">
                <label for="tags" class="form-label fw-semibold">Tagar / Tags</label>
                <input type="text" class="form-control" name="tags" id="tags" placeholder="KOL, Campaign, Influencer" value="<?= esc(old('tags', $article->tags)); ?>">
                <div class="form-text">Pisahkan dengan tanda koma (,).</div>
              </div>
            </div>
          </div>

          <!-- Cover Image Upload -->
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-image text-secondary me-2"></i>Gambar Sampul (Cover)</h5>
            </div>
            <div class="card-body p-4 text-center">
              <?php
                $cover = $article->cover_image ?? $article->featured_image;
              ?>
              <div id="cover_preview_wrapper" class="mb-3 <?= empty($cover) ? 'd-none' : ''; ?>">
                <?php
                  $coverUrl = '';
                  if (!empty($cover)) {
                    $coverUrl = (strpos($cover, 'http://') === 0 || strpos($cover, 'https://') === 0)
                      ? $cover
                      : base_url('uploads/articles/' . basename($cover));
                  }
                ?>
                <img id="cover_preview" src="<?= esc($coverUrl); ?>" alt="Cover Preview" class="img-fluid rounded border shadow-sm" style="max-height: 200px; width: 100%; object-fit: cover;">
              </div>

              <?php if (!empty($cover)): ?>
                <div class="form-check text-start mb-3">
                  <input class="form-check-input" type="checkbox" name="remove_cover" id="remove_cover" value="1">
                  <label class="form-check-label text-danger small fw-semibold" for="remove_cover">
                    <i class="fas fa-trash me-1"></i> Hapus Gambar Sampul Saat Ini
                  </label>
                </div>
              <?php endif; ?>

              <div class="mb-3 text-start">
                <label for="cover_image" class="form-label fw-semibold">Ganti File Gambar</label>
                <input class="form-control" type="file" name="cover_image" id="cover_image" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3 d-grid gap-2">
              <button type="button" class="btn btn-primary rounded-2 shadow-sm py-2" id="btn_save_stay">
                <i class="fas fa-save me-1"></i> Simpan Perubahan
              </button>
              <button type="button" class="btn btn-outline-primary rounded-2 py-2" id="btn_save_back">
                <i class="fas fa-check me-1"></i> Simpan &amp; Kembali
              </button>
              <a href="<?= site_url('articles/view/' . $article->id); ?>" class="btn btn-outline-info rounded-2 py-2">
                <i class="fas fa-eye me-1"></i> Pratinjau Artikel
              </a>
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
        }
    });

    // Save buttons
    $('#btn_save_stay').on('click', function() {
        $('#save_type').val('stay');
        $('#form_article').submit();
    });

    $('#btn_save_back').on('click', function() {
        $('#save_type').val('back');
        $('#form_article').submit();
    });

    // Keyboard shortcuts
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
