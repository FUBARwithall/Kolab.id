<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fas fa-file-contract text-danger me-2"></i> Kontrak Kerja Sama Saya
          </h5>
          <small class="text-muted">Dokumen perjanjian resmi, klausul komitmen, dan legalitas kampanye</small>
        </div>
        <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold">
          <?= count($contracts) ?> Dokumen Kontrak
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Nomor Kontrak</th>
                <th>Kampanye</th>
                <th>Tanggal Penandatanganan</th>
                <th>Status Tanda Tangan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($contracts)): ?>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-file-signature fa-3x mb-2 text-secondary opacity-50"></i>
                    <div>Belum ada dokumen kontrak yang diterbitkan.</div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($contracts as $c): ?>
                  <tr>
                    <td class="ps-3">
                      <div class="fw-bold text-dark"><?= esc($c['contract_number'] ?? 'CTR-KOLAB-' . $c['id']) ?></div>
                    </td>
                    <td>
                      <small class="fw-semibold text-primary"><?= esc($c['campaign_title'] ?? '-') ?></small>
                    </td>
                    <td>
                      <small class="text-muted"><?= esc($c['signed_at'] ?? 'Belum Ditandatangani') ?></small>
                    </td>
                    <td>
                      <?php if (!empty($c['signed_at'])): ?>
                        <span class="badge bg-success px-2 py-1 rounded-pill" style="font-size: 10px;">
                          <i class="fas fa-check-circle me-1"></i> TERTANDATANGAN
                        </span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 10px;">
                          <i class="fas fa-clock me-1"></i> MENUNGGU TTD
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <button type="button" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fas fa-eye me-1"></i> Lihat Klausul
                      </button>
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
