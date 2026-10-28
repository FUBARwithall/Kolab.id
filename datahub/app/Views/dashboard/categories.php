<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3">
    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
      <i class="fas fa-list text-primary me-2"></i> <?= esc($title) ?>
    </h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 60px;">ID</th>
            <th>Nama Kategori</th>
            <th>Slug</th>
            <th>Deskripsi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $c): ?>
            <tr>
              <td class="ps-3 fw-bold text-muted">#<?= $c['id'] ?></td>
              <td class="fw-bold text-dark"><i class="fas fa-tag text-primary me-1"></i> <?= esc($c['name']) ?></td>
              <td><code><?= esc($c['slug'] ?? '-') ?></code></td>
              <td class="text-muted text-sm"><?= esc($c['description'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
