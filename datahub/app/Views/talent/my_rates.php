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
        <button type="button" class="btn btn-sm btn-success rounded-pill fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-add-rate">
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
                <th>Negosiasi</th>
                <th>Terakhir Diperbarui</th>
                <th class="text-end pe-3">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rates)): ?>
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fas fa-tags fa-3x mb-2 text-secondary opacity-50"></i>
                    <div>Belum ada rate card yang diatur. Silakan tambahkan tarif penawaran Anda.</div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($rates as $r): ?>
                  <tr>
                    <td class="ps-3">
                      <span class="badge bg-dark px-2.5 py-1">
                        <i class="<?= esc($r['platform_icon'] ?? 'fas fa-globe') ?> me-1"></i>
                        <?= esc($r['platform_name'] ?? 'Universal') ?>
                      </span>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= esc($r['content_type'] ?? 'Dedicated Video') ?></div>
                      <small class="text-muted"><?= esc($r['notes'] ?? '-') ?></small>
                    </td>
                    <td class="fw-bold text-success fs-6">
                      Rp <?= number_format((float) ($r['rate_price'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td>
                      <?php if (!empty($r['negotiable'])): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 10px;">
                          Bisa Nego
                        </span>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill" style="font-size: 10px;">
                          Harga Pas
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <small class="text-muted"><?= esc($r['updated_at'] ?? $r['created_at'] ?? '-') ?></small>
                    </td>
                    <td class="text-end pe-3">
                      <a href="<?= base_url('my-rates/delete/' . $r['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 text-xs" onclick="return confirm('Hapus rate card ini?')">
                        <i class="fas fa-trash"></i>
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
  </div>
</div>

<!-- Modal Tambah Layanan Tarif -->
<div class="modal fade" id="modal-add-rate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-success text-white rounded-top-4 py-3">
        <h5 class="modal-title fw-bold fs-6"><i class="fas fa-plus-circle me-2"></i> Tambah Layanan Rate Card</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?= base_url('my-rates/save') ?>">
        <?= csrf_field() ?>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Platform Media Sosial</label>
            <select name="platform_id" class="form-select" required>
              <option value="">-- Pilih Platform --</option>
              <?php foreach ($platforms as $p): ?>
                <option value="<?= $p['platform_id'] ?? $p['id'] ?>">
                  <?= esc($p['platform_name'] ?? 'Platform') ?> (@<?= esc($p['username'] ?? '') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Jenis Konten / Layanan</label>
            <select name="content_type" class="form-select" required>
              <option value="">-- Pilih Jenis Konten --</option>
              <?php if (!empty($canonicalTypes)): ?>
                <?php foreach ($canonicalTypes as $type => $info): ?>
                  <option value="<?= esc($type) ?>"><?= esc($type) ?> (<?= esc($info['desc']) ?>)</option>
                <?php endforeach; ?>
              <?php else: ?>
                <option value="Short Form Video">Short Form Video (TikTok/Reels/Shorts)</option>
                <option value="Feed / Carousel">Feed / Carousel (Postingan IG/FB)</option>
                <option value="Live Streaming">Live Streaming</option>
                <option value="Long Form Video">Long Form Video (YouTube Review)</option>
              <?php endif; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Tarif Harga (Rp)</label>
            <input type="number" name="rate_price" class="form-control" placeholder="Contoh: 1500000" min="50000" step="10000" required>
          </div>

          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" name="negotiable" value="1" id="switchNego" checked>
            <label class="form-check-label text-sm fw-semibold text-dark" for="switchNego">
              Tarif dapat dinegosiasikan (Bisa Nego)
            </label>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-muted text-uppercase">Catatan / Term Tambahan</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Termasuk 1x revisi minor, tayang minimal 30 hari."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">Simpan Rate Card</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
