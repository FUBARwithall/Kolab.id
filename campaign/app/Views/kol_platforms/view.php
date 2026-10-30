<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-share-nodes text-primary me-2"></i>Detail Akun Platform #<?= esc($platform['id']) ?>
        </h3>
        <p class="text-muted small mb-0">Rincian profil media sosial dan metrik performa talent <?= esc($platform['kol_name']) ?>.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('kol-platforms') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="<?= site_url('kol-platforms/sync/' . $platform['id']) ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-sync-alt me-1"></i> Sinkronkan Metrik
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 me-2 shadow-sm" onclick="deleteRecord(<?= (int)$platform['id'] ?>)">
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

    <!-- 4 KPI Metrics Header Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-users fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Followers</span>
                <h4 class="fw-bold mb-0 text-dark">
                  <?php
                    $f = (int)($platform['followers'] ?: 0);
                    if ($f >= 1000000) {
                      echo number_format($f / 1000000, 1) . 'M';
                    } elseif ($f >= 1000) {
                      echo number_format($f / 1000, 1) . 'K';
                    } else {
                      echo number_format($f);
                    }
                  ?>
                  <span class="text-muted fs-7 fw-normal">(<?= number_format($f) ?>)</span>
                </h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-eye fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Average Views</span>
                <h4 class="fw-bold mb-0 text-info">
                  <?php
                    $v = (int)($platform['avg_views'] ?: 0);
                    if ($v >= 1000000) {
                      echo number_format($v / 1000000, 1) . 'M';
                    } elseif ($v >= 1000) {
                      echo number_format($v / 1000, 1) . 'K';
                    } else {
                      echo number_format($v);
                    }
                  ?>
                  <span class="text-muted fs-7 fw-normal">(<?= number_format($v) ?>)</span>
                </h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-chart-line fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Avg Engagement Rate</span>
                <h4 class="fw-bold mb-0 text-success">
                  <?php
                    $er = (float)($platform['avg_er'] ?: 0.0);
                    echo number_format($er > 1.0 ? $er : ($er * 100), 2) . '%';
                  ?>
                </h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                <i class="fas fa-shield-alt fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Status Verifikasi</span>
                <div class="mt-1">
                  <?php if ($platform['is_verified'] == 1): ?>
                    <span class="badge bg-info text-white shadow-sm px-2 py-1"><i class="fas fa-check-circle me-1"></i> Centang Biru</span>
                  <?php else: ?>
                    <span class="badge bg-light text-muted border px-2 py-1">Akun Reguler</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <!-- Card 1: Platform Account Detail -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="<?= esc($platform['platform_icon'] ?: 'fas fa-share-nodes') ?> text-primary me-2"></i>Informasi Akun Platform
            </h6>
            <?php
              $profileUrl = $platform['profile_url'];
              if (empty($profileUrl) && !empty($platform['platform_base_url'])) {
                $profileUrl = rtrim($platform['platform_base_url'], '/') . '/' . ltrim($platform['username'], '@');
              }
            ?>
            <?php if (!empty($profileUrl)): ?>
              <a href="<?= esc($profileUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm py-0 px-2 rounded-2">
                <i class="fas fa-external-link-alt me-1"></i> Kunjungi Akun
              </a>
            <?php endif; ?>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr>
                  <td class="bg-light fw-semibold text-secondary" style="width: 35%;">Platform Media Sosial</td>
                  <td>
                    <span class="badge bg-dark px-2 py-1 fw-semibold">
                      <i class="<?= esc($platform['platform_icon']) ?> me-1"></i><?= esc($platform['platform_name']) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Username Handle</td>
                  <td>
                    <span class="fw-bold text-dark fs-6">@<?= esc(ltrim($platform['username'], '@')) ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">URL Profil</td>
                  <td>
                    <?php if (!empty($profileUrl)): ?>
                      <a href="<?= esc($profileUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-primary text-break small">
                        <?= esc($profileUrl) ?> <i class="fas fa-external-link-alt fa-xs"></i>
                      </a>
                    <?php else: ?>
                      <span class="text-muted fst-italic small">Belum diisi</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Status Keaktifan</td>
                  <td>
                    <?php if ($platform['is_active'] == 1): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check me-1"></i>Aktif</span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-ban me-1"></i>Nonaktif</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Terakhir Disinkron</td>
                  <td>
                    <span class="text-secondary small">
                      <?= !empty($platform['last_synced_at']) ? esc(date('d M Y, H:i:s', strtotime($platform['last_synced_at']))) : 'Belum pernah disinkron' ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Tanggal Dibuat</td>
                  <td>
                    <span class="text-secondary small">
                      <?= !empty($platform['created_at']) ? esc(date('d M Y, H:i', strtotime($platform['created_at']))) : '-' ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Terakhir Diperbarui</td>
                  <td>
                    <span class="text-secondary small">
                      <?= !empty($platform['updated_at']) ? esc(date('d M Y, H:i', strtotime($platform['updated_at']))) : '-' ?>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Card 2: Talent & Commercial Profile -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-user-astronaut text-success me-2"></i>Informasi Talent &amp; Profil KOL
            </h6>
            <?php if (!empty($platform['kol_id'])): ?>
              <a href="<?= site_url('kols/performance-card/' . $platform['kol_id']) ?>" class="btn btn-outline-success btn-sm py-0 px-2 rounded-2" target="_blank">
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
                    <div class="fw-bold fs-6 text-dark"><?= esc($platform['kol_name']) ?></div>
                    <?php if (!empty($platform['kol_id'])): ?>
                      <a href="<?= site_url('kols/view/' . $platform['kol_id']) ?>" class="small text-primary text-decoration-none">
                        Lihat Master KOL <i class="fas fa-chevron-right small"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Email Kontak</td>
                  <td>
                    <span class="text-secondary small"><?= esc($platform['kol_email'] ?: '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">WhatsApp Kontak</td>
                  <td>
                    <span class="text-secondary small"><?= esc($platform['kol_whatsapp'] ?: '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Kota Domisili</td>
                  <td>
                    <span class="text-dark small"><?= esc($platform['kol_city'] ?: '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Status Approval</td>
                  <td>
                    <?php if (($platform['kol_status'] ?? '') === 'APPROVED'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i> Approved</span>
                    <?php else: ?>
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><?= esc($platform['kol_status'] ?? 'PENDING') ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Rekening Pembayaran</td>
                  <td>
                    <?php if (!empty($platform['bank_name']) && !empty($platform['bank_account'])): ?>
                      <span class="fw-semibold text-dark small"><?= esc($platform['bank_name']) ?> - <?= esc($platform['bank_account']) ?></span>
                      <div class="text-muted fs-7">a.n <?= esc($platform['bank_holder'] ?: '-') ?></div>
                    <?php else: ?>
                      <span class="text-muted fst-italic small">Belum didaftarkan</span>
                    <?php endif; ?>
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
    title: 'Hapus Akun Platform?',
    text: 'Akun platform ini akan dihapus secara permanen dari basis data.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-platforms/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: res.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '<?= site_url('kol-platforms') ?>';
          });
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
      }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem saat menghapus data.' });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
