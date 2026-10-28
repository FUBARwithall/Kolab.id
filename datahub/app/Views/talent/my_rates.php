<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fas fa-money-bill-wave text-success me-2"></i> Rate Card &amp; Portofolio Tarif Saya
          </h5>
          <small class="text-muted">Kelola tarif standar postingan video, live streaming, dan endorse per platform</small>
        </div>
        <button type="button" class="btn btn-sm btn-success rounded-pill fw-semibold shadow-sm">
          <i class="fas fa-plus me-1"></i> Tambah Layanan Tarif
        </button>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Platform</th>
                <th>Jenis Konten / Layanan</th>
                <th>Tarif Dasar (Price)</th>
                <th>Status Ketersediaan</th>
                <th>Terakhir Diperbarui</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rates)): ?>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-tags fa-3x mb-2 text-secondary opacity-50"></i>
                    <div>Belum ada rate card yang diatur. Silakan tambahkan tarif penawaran Anda.</div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($rates as $r): ?>
                  <tr>
                    <td class="ps-3">
                      <span class="badge bg-dark px-2 py-1"><?= esc($r['platform_name'] ?? 'Social Media') ?></span>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= esc($r['service_type'] ?? 'Dedicated Video Post') ?></div>
                      <small class="text-muted"><?= esc($r['notes'] ?? 'Termasuk hak pemakaian iklan 30 hari') ?></small>
                    </td>
                    <td class="fw-bold text-success fs-6">
                      Rp <?= number_format((float) ($r['price'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td>
                      <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 10px;">
                        <i class="fas fa-check-circle me-1"></i> AKTIF
                      </span>
                    </td>
                    <td>
                      <small class="text-muted"><?= esc($r['updated_at'] ?? $r['created_at'] ?? '-') ?></small>
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
