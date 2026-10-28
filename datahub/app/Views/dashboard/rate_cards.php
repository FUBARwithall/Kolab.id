<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
    <div>
      <h5 class="card-title mb-0 fw-bold text-dark fs-6">
        <i class="fas fa-tags text-warning me-2"></i> <?= esc($title) ?>
      </h5>
      <p class="text-muted text-xs mb-0">Total <?= count($rateCards) ?> paket rate card terdaftar</p>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">KOL</th>
            <th>Paket Layanan</th>
            <th>Platform</th>
            <th>Harga Endorsement</th>
            <th>Catatan & Term</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rateCards)): ?>
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">Belum ada rate card tersimpan.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rateCards as $r): ?>
              <tr>
                <td class="ps-3 fw-bold text-dark"><?= esc($r['kol_name'] ?? 'KOL #' . $r['kol_id']) ?></td>
                <td class="fw-bold text-dark"><?= esc($r['service_name'] ?? 'Video Post') ?></td>
                <td><span class="badge bg-secondary text-uppercase"><?= esc($r['platform'] ?? 'All') ?></span></td>
                <td><span class="fw-bold text-success">Rp <?= number_format($r['rate_amount'] ?? 0, 0, ',', '.') ?></span></td>
                <td class="text-muted text-xs"><?= esc($r['notes'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
