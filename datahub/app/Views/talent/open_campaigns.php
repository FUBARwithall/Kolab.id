<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fas fa-bullhorn text-warning me-2"></i> Peluang Kampanye Terbuka
          </h5>
          <small class="text-muted">Pilih dan ajukan kolaborasi untuk kampanye brand yang sedang aktif</small>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
          <?= count($campaigns) ?> Kampanye Tersedia
        </span>
      </div>
      <div class="card-body p-4">
        <?php if (empty($campaigns)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-bullhorn fa-3x mb-3 text-secondary opacity-50"></i>
            <h6>Belum Ada Kampanye Terbuka</h6>
            <p class="small">Saat ini belum ada kampanye baru yang membuka penerimaan kreator.</p>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($campaigns as $camp): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 border border-light shadow-sm rounded-3">
                  <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div>
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-success-subtle text-success fw-bold text-uppercase" style="font-size: 10px;">
                          <?= esc($camp['status'] ?? 'ACTIVE') ?>
                        </span>
                        <small class="text-muted" style="font-size: 11px;">
                          <i class="fas fa-users me-1 text-primary"></i> <?= (int) ($camp['total_applicants'] ?? 0) ?> Pelamar
                        </small>
                      </div>
                      <h6 class="fw-bold text-dark mb-1"><?= esc($camp['title']) ?></h6>
                      <p class="text-muted small mb-2" style="font-size: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= esc($camp['description'] ?? 'Kolaborasi kampanye brand bersama KOL & Creator pilihan.') ?>
                      </p>
                    </div>
                    <div class="pt-2 border-top mt-2">
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="text-muted" style="font-size: 11px;">Alokasi Budget</small>
                        <span class="fw-bold text-success" style="font-size: 13px;">Rp <?= number_format((float) ($camp['budget_total'] ?? 0), 0, ',', '.') ?></span>
                      </div>
                      <div class="d-flex align-items-center justify-content-between mb-3">
                        <small class="text-muted" style="font-size: 11px;">Periode</small>
                        <small class="text-dark fw-semibold" style="font-size: 11px;"><?= esc($camp['start_date'] ?? '-') ?> s/d <?= esc($camp['end_date'] ?? '-') ?></small>
                      </div>
                      <button type="button" class="btn btn-warning btn-sm w-100 fw-bold rounded-pill text-dark shadow-sm">
                        <i class="fas fa-paper-plane me-1"></i> Lamar Kampanye
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
