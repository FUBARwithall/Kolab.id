<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-primary me-2"></i>Detail Artikel</h3>
        <p class="text-muted small mb-0">Pratinjau konten artikel publikasi blog Kolab.id.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= site_url('articles') ?>" class="text-decoration-none">Artikel</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detail #<?= (int) $article->id; ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <div class="row g-4">
      <!-- Article Content Column (Left) -->
      <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-body p-4 p-md-5">

            <?php
              $cover = $article->cover_image ?? $article->featured_image;
            ?>
            <?php if (!empty($cover)): ?>
              <?php
                $coverUrl = (strpos($cover, 'http://') === 0 || strpos($cover, 'https://') === 0)
                  ? $cover
                  : base_url('uploads/articles/' . basename($cover));
              ?>
              <div class="mb-4">
                <img src="<?= esc($coverUrl); ?>" alt="<?= esc($article->title); ?>" class="img-fluid rounded-3 border shadow-sm w-100" style="max-height: 420px; object-fit: cover;">
              </div>
            <?php endif; ?>

            <!-- Badges -->
            <div class="d-flex gap-2 align-items-center flex-wrap mb-3">
              <?php if (!empty($article->category)): ?>
                <span class="badge bg-primary px-3 py-2 fs-7 fw-semibold"><?= esc($article->category); ?></span>
              <?php endif; ?>

              <?php
                $st = strtoupper((string) ($article->status ?? 'DRAFT'));
              ?>
              <?php if ($st === 'PUBLISHED'): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2"><i class="fas fa-check-circle me-1"></i>PUBLISHED</span>
              <?php elseif ($st === 'ARCHIVED'): ?>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2"><i class="fas fa-archive me-1"></i>ARCHIVED</span>
              <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2"><i class="fas fa-file-alt me-1"></i>DRAFT</span>
              <?php endif; ?>

              <?php if (!empty($article->is_featured)): ?>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2"><i class="fas fa-star me-1"></i>Featured</span>
              <?php endif; ?>
            </div>

            <!-- Title -->
            <h1 class="fw-bold text-dark mb-3 lh-base" style="font-size: 2rem;">
              <?= esc($article->title); ?>
            </h1>

            <!-- Meta info bar -->
            <div class="d-flex align-items-center gap-3 text-muted small pb-3 mb-4 border-bottom flex-wrap">
              <span><i class="fas fa-user-circle me-1 text-secondary"></i> <?= esc($article->author_name ?: 'Admin'); ?></span>
              <span><i class="fas fa-calendar-alt me-1 text-secondary"></i> <?= !empty($article->published_at) ? date('d F Y H:i', strtotime($article->published_at)) : 'Belum Terbit'; ?></span>
              <span><i class="fas fa-eye me-1 text-secondary"></i> <?= (int) $article->views; ?> views</span>
            </div>

            <!-- Excerpt -->
            <?php if (!empty($article->excerpt)): ?>
              <div class="alert alert-light border-start border-4 border-primary p-3 mb-4 bg-light">
                <div class="fst-italic text-secondary fs-6 lh-base mb-0">
                  <?= esc($article->excerpt); ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="article-body lh-lg text-dark" style="font-size: 1.05rem;">
              <?= $article->content; ?>
            </div>

            <!-- Tags -->
            <?php if (!empty($article->tags)): ?>
              <div class="pt-4 mt-5 border-top">
                <span class="text-muted small me-2"><i class="fas fa-tags me-1"></i>Tags:</span>
                <?php foreach (array_filter(array_map('trim', explode(',', $article->tags))) as $tag): ?>
                  <span class="badge bg-light text-dark border me-1 px-2 py-1"><?= esc($tag); ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

      <!-- Article Sidebar Information (Right) -->
      <div class="col-12 col-lg-4">
        <!-- Metadata Info Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-secondary me-2"></i>Informasi Artikel</h5>
          </div>
          <div class="card-body p-4">
            <dl class="row mb-0 small">
              <dt class="col-4 text-muted">ID</dt>
              <dd class="col-8 fw-semibold">#<?= (int) $article->id; ?></dd>

              <dt class="col-4 text-muted">Slug URL</dt>
              <dd class="col-8">
                <a href="<?= base_url('artikel/' . esc($article->slug)); ?>" target="_blank" class="text-decoration-none font-monospace small">
                  /artikel/<?= esc($article->slug); ?> <i class="fas fa-external-link-alt ms-1"></i>
                </a>
              </dd>

              <dt class="col-4 text-muted">Status</dt>
              <dd class="col-8"><strong><?= esc($article->status); ?></strong></dd>

              <dt class="col-4 text-muted">Penulis</dt>
              <dd class="col-8"><?= esc($article->author_name ?: 'Admin'); ?></dd>

              <dt class="col-4 text-muted">Dibuat</dt>
              <dd class="col-8"><?= esc($article->created_at ?: '-'); ?></dd>

              <dt class="col-4 text-muted">Diperbarui</dt>
              <dd class="col-8"><?= esc($article->updated_at ?: '-'); ?></dd>

              <dt class="col-4 text-muted">Terbit</dt>
              <dd class="col-8"><?= !empty($article->published_at) ? esc($article->published_at) : '<span class="text-muted fst-italic">Draft</span>'; ?></dd>

              <dt class="col-4 text-muted">Views</dt>
              <dd class="col-8"><?= (int) $article->views; ?> kali</dd>

              <?php if (!empty($article->meta_title)): ?>
                <dt class="col-4 text-muted">Meta Title</dt>
                <dd class="col-8 text-break"><?= esc($article->meta_title); ?></dd>
              <?php endif; ?>

              <?php if (!empty($article->meta_description)): ?>
                <dt class="col-4 text-muted">Meta Desc</dt>
                <dd class="col-8 text-break"><?= esc($article->meta_description); ?></dd>
              <?php endif; ?>
            </dl>
          </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-body p-3 d-grid gap-2">
            <a href="<?= site_url('articles/edit/' . $article->id); ?>" class="btn btn-primary rounded-2 shadow-sm py-2">
              <i class="fas fa-pencil-alt me-1"></i> Edit Artikel Ini
            </a>
            <a href="<?= site_url('articles/single_pdf/' . $article->id); ?>" target="_blank" class="btn btn-outline-secondary rounded-2 py-2">
              <i class="fas fa-print me-1"></i> Cetak Dokumen / PDF
            </a>
            <a href="<?= site_url('articles'); ?>" class="btn btn-light border rounded-2 py-2 text-secondary">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
