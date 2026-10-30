<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-tags text-warning me-2"></i>Detail Kategori KOL #<?= esc($detail['id']) ?>
        </h3>
        <p class="text-muted small mb-0">Rincian asosiasi kategori niche kreatif pada talent influencer.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('kol-categories') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 me-2 shadow-sm" onclick="deleteRecord(<?= (int)$detail['id'] ?>)">
          <i class="fas fa-trash-alt me-1"></i> Hapus
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <div class="row g-3 mb-4">
      <!-- Card 1: Talent Information -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-user-astronaut text-primary me-2"></i>Informasi Talent KOL
            </h6>
            <?php if (!empty($detail['kol_id'])): ?>
              <a href="<?= site_url('kols/performance-card/' . $detail['kol_id']) ?>" class="btn btn-outline-primary btn-sm py-0 px-2 rounded-2" target="_blank">
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
                    <div class="fw-bold fs-6 text-dark"><?= esc($detail['kol_name']) ?></div>
                    <?php if (!empty($detail['kol_id'])): ?>
                      <a href="<?= site_url('kols/view/' . $detail['kol_id']) ?>" class="small text-primary text-decoration-none">
                        Lihat Profil Lengkap <i class="fas fa-chevron-right small"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Email Kontak</td>
                  <td>
                    <span class="text-secondary small"><?= esc($detail['kol_email'] ?: '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">No. Telepon / WA</td>
                  <td>
                    <span class="text-secondary small"><?= esc($detail['kol_phone'] ?: '-') ?></span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Card 2: Category Information -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-layer-group text-success me-2"></i>Informasi Kategori Niche
            </h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr>
                  <td class="bg-light fw-semibold text-secondary" style="width: 35%;">Nama Kategori</td>
                  <td>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 fs-6">
                      <i class="fas fa-tag me-1"></i><?= esc($detail['category_name']) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Slug Identifier</td>
                  <td>
                    <span class="font-monospace text-dark small"><?= esc($detail['category_slug'] ?: '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Deskripsi Niche</td>
                  <td>
                    <span class="text-secondary small"><?= esc($detail['category_desc'] ?: '-') ?></span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Metadata Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-body p-3 d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small d-block">ID Relasi Tagging</span>
          <span class="fw-bold font-monospace">#<?= esc($detail['id']) ?></span>
        </div>
        <div>
          <span class="text-muted small d-block">Ditambahkan Pada</span>
          <span class="text-dark small fw-semibold"><?= !empty($detail['created_at']) ? date('d F Y, H:i:s', strtotime($detail['created_at'])) : '-' ?></span>
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
    title: 'Hapus Kategori KOL?',
    text: 'Apakah Anda yakin ingin menghapus penugasan kategori ini dari profil KOL?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('kol-categories/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false }).then(() => {
            window.location.href = '<?= site_url('kol-categories') ?>';
          });
        }
      }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus kategori.';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
