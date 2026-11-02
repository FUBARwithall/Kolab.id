<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
    <div>
      <h5 class="card-title mb-0 fw-bold text-dark fs-6">
        <i class="fas fa-id-card text-primary me-2"></i> Performance Card Generator
      </h5>
      <p class="text-muted text-xs mb-0">Pilih profil KOL untuk menghasilkan kartu performa eksekutif</p>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 60px;">ID</th>
            <th>Nama KOL</th>
            <th>Domisili</th>
            <th>Platform</th>
            <th>Total Follower</th>
            <th>Status</th>
            <th class="text-end pe-3" style="width: 160px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($kols)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">Belum ada data KOL.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($kols as $k): ?>
              <tr>
                <td class="ps-3 fw-bold text-muted">#<?= $k['id'] ?></td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                      <?= strtoupper(substr($k['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="fw-bold text-dark"><?= esc($k['name']) ?></div>
                    </div>
                  </div>
                </td>
                <td><?= esc($k['city'] ?? '-') ?></td>
                <td>
                  <span class="badge bg-info text-white rounded-pill px-2.5 py-1">
                    <?= (int) ($k['platform_count'] ?? 0) ?> Channels
                  </span>
                </td>
                <td class="fw-bold text-dark"><?= number_format((int) ($k['total_followers'] ?? 0)) ?></td>
                <td>
                  <span class="badge <?= $k['approval_status'] === 'APPROVED' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= esc($k['approval_status']) ?>
                  </span>
                </td>
                <td class="text-end pe-3">
                  <a href="<?= base_url('performance-card/view/' . $k['id']) ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                    <i class="fas fa-eye me-1"></i> Buka Card
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

