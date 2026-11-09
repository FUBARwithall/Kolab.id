<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-newspaper text-primary me-2"></i>Artikel &amp; Blog CMS</h3>
        <p class="text-muted small mb-0">Kelola artikel dan publikasi konten landing page Kolab.id.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Artikel</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold">
              <i class="fas fa-layer-group me-1"></i> Total: <?= (int) ($articles_counts ?? count($articles)); ?> Artikel
            </span>
            <span class="badge bg-light text-secondary border px-3 py-2 d-none d-md-inline-block">
              <i class="fas fa-rss me-1"></i> Feed: <code>/articles/feed</code>
            </span>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('articles/export?' . http_build_query(['q' => $q ?? '', 'f' => $f ?? '', 'status' => $status ?? '', 'category' => $category ?? ''])); ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 shadow-sm">
              <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
            <a href="<?= site_url('articles/export_pdf?' . http_build_query(['q' => $q ?? '', 'f' => $f ?? '', 'status' => $status ?? '', 'category' => $category ?? ''])); ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 px-3 shadow-sm">
              <i class="fas fa-print me-1"></i> Cetak Rekap
            </a>
            <a href="<?= site_url('articles/add'); ?>" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm">
              <i class="fas fa-plus me-1"></i> Tulis Artikel Baru
            </a>
          </div>
        </div>
      </div>

      <div class="card-body">
        <!-- Filter Bar -->
        <form name="form_articles" id="form_articles" action="<?= site_url('articles/index'); ?>" method="get" class="mb-3">
          <div class="row g-2 p-3 bg-light rounded-3 border">
            <div class="col-12 col-md-3">
              <input type="text" class="form-control form-control-sm" name="q" id="filter" placeholder="Cari judul, ringkasan, tag..." value="<?= esc($q ?? ''); ?>">
            </div>
            <div class="col-6 col-md-2">
              <select class="form-select form-select-sm" name="f" id="field">
                <option value="">Semua kolom</option>
                <option <?= (($f ?? '') === 'title') ? 'selected' : ''; ?> value="title">Judul</option>
                <option <?= (($f ?? '') === 'slug') ? 'selected' : ''; ?> value="slug">Slug URL</option>
                <option <?= (($f ?? '') === 'author_name') ? 'selected' : ''; ?> value="author_name">Penulis</option>
                <option <?= (($f ?? '') === 'tags') ? 'selected' : ''; ?> value="tags">Tags</option>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <select class="form-select form-select-sm" name="status" id="status_filter">
                <option value="">Semua status</option>
                <option value="DRAFT" <?= (($status ?? '') === 'DRAFT') ? 'selected' : ''; ?>>Draft</option>
                <option value="PUBLISHED" <?= (($status ?? '') === 'PUBLISHED') ? 'selected' : ''; ?>>Published</option>
                <option value="ARCHIVED" <?= (($status ?? '') === 'ARCHIVED') ? 'selected' : ''; ?>>Archived</option>
              </select>
            </div>
            <div class="col-8 col-md-3">
              <select class="form-select form-select-sm" name="category" id="category_filter">
                <option value="">Semua kategori</option>
                <?php if (!empty($categories)): ?>
                  <?php foreach ($categories as $cat): ?>
                    <?php $cName = is_object($cat) ? $cat->category : ($cat['category'] ?? ''); ?>
                    <option value="<?= esc($cName); ?>" <?= (($category ?? '') === $cName) ? 'selected' : ''; ?>><?= esc($cName); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div class="col-4 col-md-2 d-flex gap-1">
              <button type="submit" class="btn btn-primary btn-sm rounded-2 flex-grow-1" id="sbtn" title="Terapkan Filter">
                <i class="fas fa-search me-1"></i> Filter
              </button>
              <a href="<?= site_url('articles'); ?>" class="btn btn-outline-secondary btn-sm rounded-2" id="reset" title="Reset Filter">
                <i class="fas fa-undo"></i>
              </a>
            </div>
          </div>

          <div class="d-flex align-items-center gap-2 mt-3 mb-2 flex-wrap">
            <div class="d-flex align-items-center gap-2" style="max-width: 250px;">
              <select class="form-select form-select-sm" name="bulk" id="bulk">
                <option value="">Aksi Massal (Bulk)</option>
                <option value="delete">Hapus Terpilih</option>
              </select>
              <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3" id="apply_bulk">
                Terapkan
              </button>
            </div>
            <div class="text-muted small ms-auto">
              Menampilkan <strong><?= count($articles); ?></strong> dari <strong><?= (int) ($articles_counts ?? count($articles)); ?></strong> artikel
            </div>
          </div>
        </form>

        <!-- Articles Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 border rounded-3" id="tableArticles" style="width: 100%;">
            <thead class="table-light">
              <tr style="font-size: 12px; text-transform: uppercase;">
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="check_all" title="Pilih semua">
                </th>
                <th style="width: 70px;">Cover</th>
                <th>Judul &amp; URL Slug</th>
                <th style="width: 140px;">Kategori</th>
                <th style="width: 130px;">Penulis</th>
                <th style="width: 110px;">Status</th>
                <th style="width: 130px;">Terbit</th>
                <th style="width: 80px;" class="text-center">Views</th>
                <th style="width: 140px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $article): ?>
                  <?php
                    $id = is_object($article) ? $article->id : $article['id'];
                    $title = is_object($article) ? $article->title : $article['title'];
                    $slug = is_object($article) ? $article->slug : $article['slug'];
                    $cat = is_object($article) ? $article->category : ($article['category'] ?? '');
                    $author = is_object($article) ? $article->author_name : ($article['author_name'] ?? 'Admin');
                    $st = strtoupper((string) (is_object($article) ? $article->status : ($article['status'] ?? 'DRAFT')));
                    $isFeat = is_object($article) ? $article->is_featured : ($article['is_featured'] ?? 0);
                    $views = (int) (is_object($article) ? $article->views : ($article['views'] ?? 0));
                    $pubAt = is_object($article) ? $article->published_at : ($article['published_at'] ?? null);
                    $cover = is_object($article) ? ($article->cover_image ?? $article->featured_image) : ($article['cover_image'] ?? $article['featured_image'] ?? '');
                  ?>
                  <tr id="row-<?= (int) $id; ?>">
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input check-item" name="id[]" value="<?= (int) $id; ?>">
                    </td>
                    <td>
                      <?php if (!empty($cover)): ?>
                        <?php
                          $coverUrl = (strpos($cover, 'http://') === 0 || strpos($cover, 'https://') === 0)
                            ? $cover
                            : base_url('uploads/articles/' . basename($cover));
                        ?>
                        <img src="<?= esc($coverUrl); ?>" alt="" class="rounded border shadow-xs" style="width: 56px; height: 38px; object-fit: cover;">
                      <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light text-muted border rounded" style="width: 56px; height: 38px;">
                          <i class="fas fa-image"></i>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= esc($title); ?></div>
                      <div class="small font-monospace">
                        <a href="<?= base_url('artikel/' . esc($slug)); ?>" target="_blank" class="text-decoration-none text-muted">/artikel/<?= esc($slug); ?></a>
                      </div>
                      <?php if (!empty($isFeat)): ?>
                        <span class="badge bg-info-subtle text-info border border-info-subtle mt-1" style="font-size: 10px;">
                          <i class="fas fa-star me-1"></i>Featured
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($cat)): ?>
                        <span class="badge bg-light text-dark border"><?= esc($cat); ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td><?= esc($author ?: 'Admin'); ?></td>
                    <td>
                      <?php if ($st === 'PUBLISHED'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>PUBLISHED</span>
                      <?php elseif ($st === 'ARCHIVED'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-archive me-1"></i>ARCHIVED</span>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-file-alt me-1"></i>DRAFT</span>
                      <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                      <?= !empty($pubAt) ? date('d M Y H:i', strtotime($pubAt)) : '<span class="fst-italic">-</span>'; ?>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-light text-dark border"><i class="fas fa-eye me-1"></i><?= $views; ?></span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex gap-1 justify-content-center">
                        <a href="<?= site_url('articles/view/' . $id); ?>" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail"><i class="fas fa-eye"></i></a>
                        <a href="<?= site_url('articles/edit/' . $id); ?>" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                        <?php if ($st !== 'PUBLISHED'): ?>
                          <button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2 js-status" data-id="<?= (int) $id; ?>" data-action="publish" title="Publish"><i class="fas fa-check"></i></button>
                        <?php else: ?>
                          <button type="button" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2 js-status" data-id="<?= (int) $id; ?>" data-action="unpublish" title="Unpublish"><i class="fas fa-pause"></i></button>
                        <?php endif; ?>
                        <?php if ($st !== 'ARCHIVED'): ?>
                          <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2 js-status" data-id="<?= (int) $id; ?>" data-action="archive" title="Archive"><i class="fas fa-archive"></i></button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2 remove-data" data-id="<?= (int) $id; ?>" data-href="<?= site_url('articles/delete/' . $id); ?>" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center py-5 text-muted">
                    <i class="fas fa-newspaper fa-3x mb-3 text-secondary opacity-50"></i>
                    <p class="mb-2">Belum ada artikel yang sesuai kriteria pencarian.</p>
                    <a href="<?= site_url('articles/add'); ?>" class="btn btn-sm btn-primary rounded-2">
                      <i class="fas fa-plus me-1"></i> Tulis Artikel Baru
                    </a>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if (!empty($pagination)): ?>
          <div class="d-flex justify-content-end mt-3">
            <?= $pagination; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
    // Select all checkboxes
    $('#check_all').on('change', function() {
        $('.check-item').prop('checked', $(this).is(':checked'));
    });

    $('.check-item').on('change', function() {
        if ($('.check-item:checked').length === $('.check-item').length) {
            $('#check_all').prop('checked', true);
        } else {
            $('#check_all').prop('checked', false);
        }
    });

    // Delete single article
    $(document).on('click', '.remove-data', function(e) {
        e.preventDefault();
        var url = $(this).data('href');
        var id = $(this).data('id');

        if (confirm('Apakah Anda yakin ingin menghapus artikel ini? Data yang dihapus tidak dapat dipulihkan.')) {
            $.ajax({
                url: url,
                type: 'POST',
                data: { id: id, ajax: 1 },
                dataType: 'json',
                success: function(res) {
                    if (res && res.success) {
                        $('#row-' + id).fadeOut(300, function() { $(this).remove(); });
                    } else {
                        alert(res.message || 'Gagal menghapus artikel.');
                    }
                },
                error: function() {
                    window.location.href = url;
                }
            });
        }
    });

    // Quick status toggle (Publish / Unpublish / Archive)
    $(document).on('click', '.js-status', function() {
        var btn = $(this);
        var id = btn.data('id');
        var action = btn.data('action');
        var url = '<?= site_url('articles/'); ?>' + action + '/' + id;

        btn.prop('disabled', true);
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res && res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Gagal mengubah status artikel.');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Terjadi kesalahan koneksi.');
                btn.prop('disabled', false);
            }
        });
    });

    // Bulk action apply
    $('#apply_bulk').on('click', function() {
        var action = $('#bulk').val();
        if (!action) {
            alert('Silakan pilih jenis aksi massal terlebih dahulu.');
            return;
        }

        var checkedIds = [];
        $('.check-item:checked').each(function() {
            checkedIds.push($(this).val());
        });

        if (checkedIds.length === 0) {
            alert('Silakan pilih minimal satu artikel.');
            return;
        }

        if (action === 'delete') {
            if (confirm('Apakah Anda yakin ingin menghapus ' + checkedIds.length + ' artikel terpilih?')) {
                $.ajax({
                    url: '<?= site_url('articles/delete'); ?>',
                    type: 'POST',
                    data: { id: checkedIds, ajax: 1 },
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.success) {
                            location.reload();
                        } else {
                            alert(res.message || 'Gagal menghapus artikel terpilih.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan koneksi.');
                    }
                });
            }
        }
    });

    // Keyboard Shortcuts (Ctrl+a -> Add, Ctrl+f -> Search focus)
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'a' && !$(e.target).is('input, textarea')) {
            e.preventDefault();
            window.location.href = '<?= site_url('articles/add'); ?>';
        }
        if (e.ctrlKey && e.key === 'f' && !$(e.target).is('input, textarea')) {
            e.preventDefault();
            $('#filter').focus();
        }
    });
});
</script>
<?= $this->endSection() ?>
