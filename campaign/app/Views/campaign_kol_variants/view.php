<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-7">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-layer-group text-primary me-2"></i>Alokasi Varian KOL #<?= $item['id'] ?>
        </h3>
        <p class="text-muted small mb-0">
          Campaign: <strong class="text-dark"><?= esc($item['campaign_title'] ?? '-') ?></strong> &bull; 
          Talent: <strong class="text-primary"><?= esc($item['kol_name'] ?? '-') ?></strong>
        </p>
      </div>
      <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('campaign-kol-variants') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="<?= site_url('campaign-kols/view/' . $item['campaign_kol_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2 px-3">
          <i class="fas fa-handshake me-1"></i> Lihat Dealing KOL
        </a>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Rincian Alokasi Konsep Varian</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 fw-semibold">
              Qty: <?= (int)$item['qty'] ?> pcs
            </span>
          </div>
          <div class="card-body p-4">
            <div class="row g-4">
              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Campaign Terkait</div>
                <div class="fw-bold fs-6 text-dark mt-1">
                  <a href="<?= site_url('campaigns/view/' . $item['campaign_id']) ?>" class="text-decoration-none text-dark">
                    <?= esc($item['campaign_title'] ?? '-') ?> <i class="fas fa-external-link-alt text-muted small ms-1"></i>
                  </a>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Talent KOL Ditugaskan</div>
                <div class="fw-bold fs-6 text-primary mt-1">
                  <a href="<?= site_url('campaign-kols/view/' . $item['campaign_kol_id']) ?>" class="text-decoration-none text-primary">
                    <i class="fas fa-user-circle me-1"></i> <?= esc($item['kol_name'] ?? '-') ?>
                  </a>
                </div>
                <div class="text-muted small">Dealing #<?= $item['campaign_kol_id'] ?> &bull; Status: <span class="badge bg-light text-dark border"><?= esc($item['deal_status'] ?? '-') ?></span></div>
              </div>

              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Konsep Varian Konten / Varian Produk</div>
                <div class="fw-bold fs-5 text-dark mt-1">
                  <i class="fas fa-layer-group text-info me-2"></i><?= esc($item['variant_name'] ?? '-') ?>
                </div>
                <div class="text-muted small">SKU: <?= esc($item['variant_sku'] ?? '-') ?> &bull; Produk Induk: <?= esc($item['product_name'] ?? '-') ?></div>
              </div>

              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Jumlah Alokasi (Qty)</div>
                <div class="fw-bold fs-5 text-success mt-1">
                  <?= (int)$item['qty'] ?> pcs
                </div>
                <div class="text-muted small">Alokasi sampel fisik / tugas video</div>
              </div>

              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Dibuat Pada</div>
                <div class="fw-semibold text-dark mt-1">
                  <?= $item['created_at'] ? date('d F Y, H:i', strtotime($item['created_at'])) : '-' ?>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2" onclick="deleteSingle(<?= $item['id'] ?>)">
              <i class="fas fa-trash-alt me-1"></i> Hapus Alokasi
            </button>
            <a href="<?= site_url('campaign-kol-variants') ?>" class="btn btn-primary btn-sm rounded-2 px-4 shadow-sm">
              <i class="fas fa-check me-1"></i> Selesai
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteSingle(id) {
  Swal.fire({
    title: 'Hapus Alokasi #' + id + '?',
    text: 'Aksi ini tidak dapat dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= site_url('campaign-kol-variants/delete/') ?>' + id,
        type: 'POST',
        data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: 'Terhapus',
              text: res.message,
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              window.location.href = '<?= site_url('campaign-kol-variants') ?>';
            });
          } else {
            Swal.fire('Gagal', res.message, 'error');
          }
        }
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
