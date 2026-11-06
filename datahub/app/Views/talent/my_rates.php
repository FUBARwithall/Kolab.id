<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
.section-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    border: none;
}
.card-header-custom {
    background: #fff;
    border-bottom: 1.5px solid #e9eef6;
    border-radius: 10px 10px 0 0;
    padding: 18px 24px;
    display: flex; align-items: center; justify-content: space-between;
}
.section-title  { font-size: 20px; font-weight: 700; color: #172b4d; margin-bottom: 2px; }
.section-subtitle { font-size: 13px; color: #6b778c; }
.count-badge {
    background: #e8f1fb; color: #1f4b6e;
    font-weight: 700; font-size: 13px;
    border-radius: 20px; padding: 4px 14px;
}
/* Table */
.rates-table thead th {
    background: #f6f8fb;
    color: #52606d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
    border-bottom: 1.5px solid #e4ebf5;
    padding: 12px 16px;
    white-space: nowrap;
}
.rates-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f4fa;
    font-size: 13px;
}
.rates-table tbody tr:last-child td { border-bottom: none; }
.rates-table tbody tr:hover { background: #fafbfd; }
/* Platform badge */
.platform-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 700;
    border-radius: 20px; padding: 4px 12px;
}
.plat-tiktok   { background: #1a1a2e; color: #fff; }
.plat-instagram{ background: linear-gradient(135deg,#f58529,#dd2a7b,#8134af,#515bd4); color: #fff; }
.plat-youtube  { background: #ff0000; color: #fff; }
.plat-twitter  { background: #1da1f2; color: #fff; }
.plat-facebook { background: #1877f2; color: #fff; }
.plat-default  { background: #e8f1fb; color: #1f4b6e; }
/* Nego badge */
.nego-yes { background: #e3f9e5; color: #166534; font-size: 11px; font-weight: 700; border-radius: 20px; padding: 3px 10px; }
.nego-no  { background: #f0f4fa; color: #52606d; font-size: 11px; font-weight: 700; border-radius: 20px; padding: 3px 10px; }
/* Action buttons */
.btn-add-rate {
    background: #1f4b6e; color: #fff;
    border: none; border-radius: 20px;
    padding: 7px 20px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: background 0.15s;
}
.btn-add-rate:hover { background: #17395a; color: #fff; }
.btn-del-rate {
    background: #fdecea; color: #c0392b;
    border: none; border-radius: 8px;
    padding: 5px 12px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: background 0.15s;
}
.btn-del-rate:hover { background: #c0392b; color: #fff; }
/* Empty state */
.empty-state { text-align:center; padding:60px 20px; }
.empty-state i { font-size:48px; color:#d0daea; margin-bottom:16px; display:block; }
.empty-state h6 { color:#172b4d; font-weight:700; font-size:16px; }
.empty-state p  { color:#6b778c; font-size:13px; }
/* Modal */
.modal-add-rate .modal-content { border-radius: 10px; border: none; }
.modal-add-rate .modal-header  { background: #172b4d; border-radius: 10px 10px 0 0; }
</style>

<div class="row mb-4">
  <div class="col-12">
    <div class="section-card">

      <!-- Header -->
      <div class="card-header-custom">
        <div>
          <div class="section-title">
            <i class="fas fa-tags me-2" style="color:#166534;"></i> Rate Card &amp; Tarif Layanan Saya
          </div>
          <div class="section-subtitle">Kelola tarif standar postingan video, live streaming, dan endorse per platform</div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="count-badge"><?= count($rates) ?> Layanan</span>
          <button type="button" class="btn-add-rate" data-bs-toggle="modal" data-bs-target="#modal-add-rate">
            <i class="fas fa-plus me-1"></i> Tambah Layanan
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="rates-table w-100">
          <thead>
            <tr>
              <th style="width:40px;">#</th>
              <th>Platform</th>
              <th>Jenis Konten / Layanan</th>
              <th>Tarif (Rp)</th>
              <th>Negosiasi</th>
              <th>Catatan</th>
              <th style="width:80px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rates)): ?>
              <tr>
                <td colspan="7">
                  <div class="empty-state">
                    <i class="fas fa-tags"></i>
                    <h6>Belum Ada Rate Card</h6>
                    <p>Tambahkan tarif layanan Anda agar brand dapat melihat harga kolaborasi.</p>
                    <button type="button" class="btn-add-rate" data-bs-toggle="modal" data-bs-target="#modal-add-rate">
                      <i class="fas fa-plus me-1"></i> Tambah Sekarang
                    </button>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($rates as $i => $r): ?>
                <?php
                  $pName = strtolower($r['platform_name'] ?? '');
                  $pClass = match(true) {
                      str_contains($pName, 'tiktok')    => 'plat-tiktok',
                      str_contains($pName, 'instagram') => 'plat-instagram',
                      str_contains($pName, 'youtube')   => 'plat-youtube',
                      str_contains($pName, 'twitter')   => 'plat-twitter',
                      str_contains($pName, 'facebook')  => 'plat-facebook',
                      default                            => 'plat-default',
                  };
                ?>
                <tr>
                  <td class="text-muted fw-semibold"><?= $i + 1 ?></td>
                  <td>
                    <span class="platform-badge <?= $pClass ?>">
                      <i class="<?= esc($r['platform_icon'] ?? 'fas fa-globe') ?>"></i>
                      <?= esc($r['platform_name'] ?? 'Universal') ?>
                    </span>
                  </td>
                  <td>
                    <div class="fw-bold" style="color:#172b4d;"><?= esc($r['content_type'] ?? '-') ?></div>
                  </td>
                  <td>
                    <span class="fw-bold" style="color:#166534;font-size:14px;">
                      Rp <?= number_format((float)($r['rate_price'] ?? 0), 0, ',', '.') ?>
                    </span>
                  </td>
                  <td>
                    <?php if (!empty($r['negotiable']) && $r['negotiable']): ?>
                      <span class="nego-yes"><i class="fas fa-check me-1"></i>Bisa Nego</span>
                    <?php else: ?>
                      <span class="nego-no">Harga Pas</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <small style="color:#6b778c;"><?= esc($r['notes'] ?? '-') ?></small>
                  </td>
                  <td>
                    <a href="<?= base_url('my-rates/delete/' . $r['id']) ?>"
                       class="btn-del-rate"
                       onclick="return confirm('Hapus rate card ini?')">
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

<!-- Modal Tambah Rate -->
<div class="modal fade modal-add-rate" id="modal-add-rate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title text-white fw-bold">
          <i class="fas fa-plus-circle me-2"></i> Tambah Rate Card Baru
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 py-3">
        <form method="post" action="<?= base_url('my-rates/add') ?>">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Platform <span class="text-danger">*</span></label>
            <select name="platform_id" class="form-select" required>
              <option value="">— Pilih Platform —</option>
              <?php foreach ($platforms as $plat): ?>
                <option value="<?= (int)$plat['platform_id'] ?>"><?= esc($plat['platform_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Jenis Konten / Layanan <span class="text-danger">*</span></label>
            <select name="content_type" class="form-select" required>
              <option value="">— Pilih Jenis —</option>
              <?php foreach (($canonicalTypes ?? []) as $ctKey => $ctVal): ?>
                <option value="<?= esc($ctKey) ?>"><?= esc($ctKey) ?></option>
              <?php endforeach; ?>
              <option value="__other__">Lainnya (isi manual)</option>
            </select>
            <input type="text" name="content_type_custom" class="form-control mt-2 d-none"
                   id="custom-type-input" placeholder="Contoh: Brand Ambassador">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Tarif (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="rate_price" class="form-control" placeholder="Contoh: 5000000" min="0" required>
            </div>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="negotiable" id="chk-negotiable" value="1">
            <label class="form-check-label fw-semibold" for="chk-negotiable" style="font-size:13px;">
              Harga bisa dinegosiasikan
            </label>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:13px;">Catatan (opsional)</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Sudah termasuk konsep dan editing..."></textarea>
          </div>

          <button type="submit" class="btn-add-rate w-100">
            <i class="fas fa-check me-1"></i> Simpan Rate Card
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle custom type input
document.querySelector('select[name="content_type"]')?.addEventListener('change', function() {
    const inp = document.getElementById('custom-type-input');
    if (this.value === '__other__') {
        inp.classList.remove('d-none');
        inp.setAttribute('required','required');
    } else {
        inp.classList.add('d-none');
        inp.removeAttribute('required');
    }
});
// Override content_type on submit if custom
document.querySelector('#modal-add-rate form')?.addEventListener('submit', function() {
    const sel = this.querySelector('select[name="content_type"]');
    const inp = document.getElementById('custom-type-input');
    if (sel.value === '__other__' && inp.value.trim()) {
        sel.value = inp.value.trim();
    }
});
</script>

<?= $this->endSection() ?>
