<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fas fa-clipboard-list text-primary me-2"></i> Status Lamaran Kampanye Saya
          </h5>
          <small class="text-muted">Pantau status seleksi dan penawaran kerja sama yang Anda kirimkan ke Brand</small>
        </div>
        <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-semibold">
          <?= count($applications) ?> Lamaran
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Kampanye</th>
                <th>Proposed Rate</th>
                <th>Catatan / Pitch</th>
                <th>Status Seleksi</th>
                <th>Tanggal Pengajuan</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($applications)): ?>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-2 text-secondary opacity-50"></i>
                    <div>Belum ada lamaran kampanye yang diajukan.</div>
                    <a href="<?= base_url('open-campaigns') ?>" class="btn btn-sm btn-outline-warning mt-2 rounded-pill">
                      <i class="fas fa-search me-1"></i> Telusuri Kampanye Terbuka
                    </a>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($applications as $app): ?>
                  <tr>
                    <td class="ps-3">
                      <div class="fw-bold text-dark"><?= esc($app['campaign_title'] ?? 'Kampanye #' . $app['campaign_id']) ?></div>
                      <small class="text-muted"><?= esc($app['start_date'] ?? '-') ?> s/d <?= esc($app['end_date'] ?? '-') ?></small>
                    </td>
                    <td class="fw-bold text-success">
                      Rp <?= number_format((float) ($app['proposed_rate'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td>
                      <small class="text-secondary"><?= esc($app['pitch_notes'] ?? '-') ?></small>
                    </td>
                    <td>
                      <?php 
                        $statusClass = 'bg-warning text-dark';
                        if ($app['status'] === 'ACCEPTED') $statusClass = 'bg-success text-white';
                        if ($app['status'] === 'REJECTED') $statusClass = 'bg-danger text-white';
                      ?>
                      <span class="badge <?= $statusClass ?> px-2 py-1 rounded-pill text-uppercase" style="font-size: 10px;">
                        <?= esc($app['status']) ?>
                      </span>
                    </td>
                    <td>
                      <small class="text-muted"><?= esc($app['created_at'] ?? '-') ?></small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
