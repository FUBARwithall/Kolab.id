<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-hand-holding-usd text-success me-2"></i>Detail Rate Card #<?= esc($rateCard['id']) ?>
        </h3>
        <p class="text-muted small mb-0">Rincian paket tarif komersial kreator <?= esc($rateCard['kol_name']) ?> pada <?= esc($rateCard['platform_name']) ?>.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('kol-rate-cards') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 me-2 shadow-sm" onclick="deleteRecord(<?= (int)$rateCard['id'] ?>)">
          <i class="fas fa-trash-alt me-1"></i> Hapus
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- 3 Highlight Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-coins fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Nominal Tarif</span>
                <h3 class="fw-bold mb-0 text-success">
                  Rp <?= number_format((float)($rateCard['final_rate'] ?: $rateCard['rate'] ?: $rateCard['rate_price']), 0, ',', '.') ?>
                  <span class="fs-7 text-muted fw-normal"><?= esc($rateCard['currency_code'] ?: 'IDR') ?></span>
                </h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-handshake fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Status Negosiasi</span>
                <div class="mt-1">
                  <?php if ($rateCard['negotiable'] == 1): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6"><i class="fas fa-check-circle me-1"></i> Terbuka untuk Negosiasi</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-6">Fixed Rate (Harga Pas)</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-toggle-on fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Status Keaktifan</span>
                <div class="mt-1">
                  <?php if ($rateCard['is_active'] == 1): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6"><i class="fas fa-check me-1"></i> Aktif Digunakan</span>
                  <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-6"><i class="fas fa-ban me-1"></i> Nonaktif</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <!-- Card 1: Rate Card Package Details -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-receipt text-primary me-2"></i>Rincian Paket Komersial
            </h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr>
                  <td class="bg-light fw-semibold text-secondary" style="width: 35%;">ID Paket</td>
                  <td>#<?= esc($rateCard['id']) ?></td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Tipe Konten</td>
                  <td>
                    <span class="badge bg-primary px-3 py-1 fs-6 fw-semibold">
                      <?= esc($rateCard['content_type']) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Nominal Tarif</td>
                  <td>
                    <span class="fw-bold fs-6 text-dark">
                      Rp <?= number_format((float)($rateCard['final_rate'] ?: $rateCard['rate'] ?: $rateCard['rate_price']), 0, ',', '.') ?>
                    </span>
                    <span class="badge bg-light text-dark border ms-1"><?= esc($rateCard['currency_code'] ?: 'IDR') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Fleksibilitas Nego</td>
                  <td>
                    <?= $rateCard['negotiable'] == 1 ? '<span class="text-success fw-semibold"><i class="fas fa-check me-1"></i>Bisa Nego</span>' : '<span class="text-secondary">Fixed Price</span>' ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Catatan Layanan</td>
                  <td>
                    <div class="text-dark small"><?= nl2br(esc($rateCard['notes'] ?: 'Tidak ada catatan khusus.')) ?></div>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Tanggal Dibuat</td>
                  <td>
                    <span class="text-secondary small">
                      <?= !empty($rateCard['created_at']) ? esc(date('d M Y, H:i', strtotime($rateCard['created_at']))) : '-' ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Terakhir Diperbarui</td>
                  <td>
                    <span class="text-secondary small">
                      <?= !empty($rateCard['updated_at']) ? esc(date('d M Y, H:i', strtotime($rateCard['updated_at']))) : '-' ?>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Card 2: Platform Account & Talent Details -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-user-astronaut text-success me-2"></i>Informasi Akun Platform &amp; Talent
            </h6>
            <?php if (!empty($rateCard['kol_id'])): ?>
              <a href="<?= site_url('kols/performance-card/' . $rateCard['kol_id']) ?>" class="btn btn-outline-success btn-sm py-0 px-2 rounded-2" target="_blank">
                <i class="fas fa-chart-line me-1"></i> Performance Card
              </a>
            <?php endif; ?>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr>
                  <td class="bg-light fw-semibold text-secondary" style="width: 35%;">Nama KOL</td>
                  <td>
                    <div class="fw-bold fs-6 text-dark"><?= esc($rateCard['kol_name']) ?></div>
                    <?php if (!empty($rateCard['kol_id'])): ?>
                      <a href="<?= site_url('kols/view/' . $rateCard['kol_id']) ?>" class="small text-primary text-decoration-none">
                        Lihat Master KOL <i class="fas fa-chevron-right small"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Platform Media Sosial</td>
                  <td>
                    <span class="badge bg-dark px-2 py-1 fw-semibold">
                      <i class="<?= esc($rateCard['platform_icon']) ?> me-1"></i><?= esc($rateCard['platform_name']) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Akun Handle</td>
                  <td>
                    <?php
                      $handle = $rateCard['platform_username'] ? '@' . ltrim($rateCard['platform_username'], '@') : '-';
                      $profUrl = $rateCard['platform_profile_url'];
                    ?>
                    <?php if (!empty($profUrl)): ?>
                      <a href="<?= esc($profUrl) ?>" target="_blank" rel="noopener noreferrer" class="fw-bold text-primary text-decoration-none">
                        <?= esc($handle) ?> <i class="fas fa-external-link-alt fa-xs"></i>
                      </a>
                    <?php else: ?>
                      <span class="fw-bold text-dark"><?= esc($handle) ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Followers Akun</td>
                  <td>
                    <span class="fw-semibold text-dark">
                      <?= number_format((int)($rateCard['platform_followers'] ?: 0)) ?> followers
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">WhatsApp Kontak</td>
                  <td>
                    <span class="text-secondary small"><?= esc($rateCard['kol_whatsapp'] ?: '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Email Kontak</td>
                  <td>
                    <span class="text-secondary small"><?= esc($rateCard['kol_email'] ?: '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Kota Domisili</td>
                  <td>
                    <span class="text-dark small"><?= esc($rateCard['kol_city'] ?: '-') ?></span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function deleteRecord(id) {
  Swal.fire({
    title: 'Hapus Rate Card?',
    text: 'Data tarif komersial ini akan dihapus secara permanen dari sistem.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-rate-cards/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: res.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '<?= site_url('kol-rate-cards') ?>';
          });
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
      }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kegagalan jaringan atau server.' });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
