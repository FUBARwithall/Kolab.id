<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3">
    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
      <i class="fas fa-layer-group text-primary me-2"></i> <?= esc($title) ?>
    </h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 60px;">ID</th>
            <th>Nama Tier</th>
            <th>Batas Minimum Followers</th>
            <th>Batas Maksimum Followers</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tiers as $t): ?>
            <tr>
              <td class="ps-3 fw-bold text-muted">#<?= $t['id'] ?></td>
              <td>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1.5 fs-7">
                  <i class="fas fa-award me-1"></i> <?= esc($t['name']) ?>
                </span>
              </td>
              <td class="fw-bold text-dark"><?= number_format($t['min_followers'] ?? 0) ?> Followers</td>
              <td class="fw-bold text-dark"><?= !empty($t['max_followers']) ? number_format($t['max_followers']) . ' Followers' : 'Tak Terbatas' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
