<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
  .category-list-card {
    background: #ffffff;
    border: 0;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    overflow: hidden;
    margin-bottom: 24px;
  }
  .category-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e7edf3;
    flex-wrap: wrap;
  }
  .category-list-title {
    margin: 0;
    color: #172b4d;
    font-size: 20px;
    font-weight: 700;
  }
  .category-list-subtitle {
    margin-top: 4px;
    color: #6b778c;
    font-size: 13px;
  }
  .category-count-badge {
    display: inline-block;
    margin-left: 6px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #e8f1fb;
    color: #1f4b6e;
    font-weight: 700;
    font-size: 12px;
  }
  .category-table thead tr th {
    background: #f6f8fb;
    color: #52606d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
    border-bottom: 1px solid #e7edf3;
    padding: 12px 14px;
  }
  .category-table tbody tr td {
    padding: 12px 14px;
    vertical-align: middle;
    font-size: 13.5px;
    border-bottom: 1px solid #f1f5f9;
  }
</style>

<div class="category-list-card">
  <!-- Header Bar -->
  <div class="category-list-header">
    <div>
      <h3 class="category-list-title"><?= esc($title) ?></h3>
      <div class="category-list-subtitle">
        Daftar sektor &amp; spesialisasi konten influencer
        <span class="category-count-badge"><?= count($categories) ?> Kategori Terdaftar</span>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Cetak / Export
      </button>
    </div>
  </div>

  <!-- Table Body -->
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 category-table">
      <thead>
        <tr>
          <th style="width: 60px;">ID</th>
          <th>Nama Kategori Niche</th>
          <th>Slug</th>
          <th>Deskripsi Sektor</th>
          <th>Jumlah KOL Terkait</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($categories)): ?>
          <tr>
            <td colspan="5" class="text-center py-5 text-muted">Belum ada data kategori tersimpan.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($categories as $c): ?>
            <tr>
              <td class="fw-bold text-muted">#<?= $c['id'] ?></td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 me-2.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 13px;">
                    <i class="fas fa-tag"></i>
                  </div>
                  <span class="fw-bold text-dark fs-6"><?= esc($c['name']) ?></span>
                </div>
              </td>
              <td>
                <code class="px-2 py-1 bg-light rounded text-xs text-primary"><?= esc($c['slug'] ?? '-') ?></code>
              </td>
              <td class="text-muted text-sm"><?= esc($c['description'] ?: 'Tidak ada deskripsi') ?></td>
              <td>
                <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 rounded-pill px-3 py-1 text-xs fw-bold">
                  <i class="fas fa-users me-1 text-info"></i> <?= (int)($c['kol_count'] ?? 0) ?> KOL
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
