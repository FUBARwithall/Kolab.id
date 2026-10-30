<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-boxes text-primary me-2"></i>Detail Varian Kampanye #<?= esc($detail['id']) ?>
        </h3>
        <p class="text-muted small mb-0">Rincian spesifikasi varian produk sampel yang didistribusikan pada kampanye <?= esc($detail['campaign_title'] ?: $detail['campaign_name']) ?>.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('campaign-variants') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 shadow-sm" onclick="deleteRecord(<?= (int)$detail['id'] ?>)">
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
              <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                <i class="fas fa-bullhorn fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Kampanye Terhubung</span>
                <h5 class="fw-bold mb-0 text-dark">
                  <a href="<?= base_url('campaigns/view/' . $detail['campaign_id']) ?>" class="text-decoration-none text-primary">
                    <?= esc($detail['campaign_title'] ?: $detail['campaign_name']) ?>
                  </a>
                </h5>
                <span class="badge bg-secondary-subtle text-secondary border mt-1" style="font-size: 11px;">
                  Status: <?= esc($detail['campaign_status'] ?? 'DRAFT') ?>
                </span>
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
                <i class="fas fa-box-open fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Varian Produk Sampel</span>
                <h5 class="fw-bold mb-0 text-dark"><?= esc($detail['variant_name'] ?? 'N/A') ?></h5>
                <span class="badge bg-light text-dark border mt-1">
                  SKU: <?= esc($detail['variant_sku'] ?? '-') ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-warehouse fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Ketersediaan Stok Sampel</span>
                <h4 class="fw-bold mb-0 text-success">
                  <?= number_format((int)($detail['variant_stock_qty'] ?? 0)) ?> <span class="fs-7 text-muted fw-normal">unit</span>
                </h4>
                <?php if ((int)($detail['variant_stock_qty'] ?? 0) > 0): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0 mt-1" style="font-size: 11px;">Stok Aman</span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0 mt-1" style="font-size: 11px;">Stok Kosong</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Kolom Kiri: Spesifikasi Produk & Varian -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-dark">
              <i class="fas fa-info-circle text-info me-2"></i>Spesifikasi Varian Produk
            </h5>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped align-middle mb-0">
              <tbody>
                <tr>
                  <th class="ps-4 text-muted fw-semibold" style="width: 35%;">ID Relasi</th>
                  <td class="fw-bold text-dark">#<?= esc($detail['id']) ?></td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Produk Induk</th>
                  <td>
                    <span class="fw-bold text-dark"><?= esc($detail['product_name'] ?? 'N/A') ?></span>
                    <span class="badge bg-light text-secondary border ms-2">SKU: <?= esc($detail['product_sku'] ?? '-') ?></span>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Harga Pokok Produk</th>
                  <td>
                    Rp <?= number_format((float)($detail['product_unit_price'] ?? 0), 0, ',', '.') ?>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Nama Varian</th>
                  <td class="fw-bold text-primary">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-6">
                      <?= esc($detail['variant_name'] ?? 'N/A') ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">SKU Varian</th>
                  <td><code><?= esc($detail['variant_sku'] ?? '-') ?></code></td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Harga Tambahan Varian</th>
                  <td>
                    <?php $add = (float)($detail['variant_additional_price'] ?? 0); ?>
                    <?php if ($add > 0): ?>
                      <span class="fw-bold text-success">+ Rp <?= number_format($add, 0, ',', '.') ?></span>
                    <?php else: ?>
                      <span class="text-muted">Standard (Rp 0)</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Stok Unit Varian</th>
                  <td class="fw-semibold text-dark">
                    <?= number_format((int)($detail['variant_stock_qty'] ?? 0)) ?> unit
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Catatan Relasi</th>
                  <td><?= esc($detail['notes'] ?: '-') ?></td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Tanggal Ditambahkan</th>
                  <td>
                    <i class="far fa-calendar-alt text-muted me-1"></i>
                    <?= !empty($detail['created_at']) ? date('d M Y H:i:s', strtotime($detail['created_at'])) : '-' ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Detail Kampanye & Varian Lain Terkait -->
      <div class="col-lg-6">
        <!-- Card Detail Kampanye -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-dark">
              <i class="fas fa-bullhorn text-warning me-2"></i>Informasi Kampanye Terkait
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
              <h5 class="fw-bold mb-0 me-3 text-dark"><?= esc($detail['campaign_title'] ?: $detail['campaign_name']) ?></h5>
              <span class="badge bg-primary"><?= esc($detail['campaign_status'] ?? 'DRAFT') ?></span>
            </div>
            <p class="text-muted small mb-3">
              <?= esc($detail['campaign_description'] ?: 'Tidak ada deskripsi detail kampanye.') ?>
            </p>
            <div class="row g-2 mb-2">
              <div class="col-sm-6">
                <small class="text-muted d-block">Total Anggaran Kampanye</small>
                <span class="fw-bold text-success">
                  Rp <?= number_format((float)($detail['campaign_budget'] ?? 0), 0, ',', '.') ?>
                </span>
              </div>
              <div class="col-sm-6">
                <small class="text-muted d-block">Periode Pelaksanaan</small>
                <span class="fw-semibold text-dark">
                  <?= !empty($detail['campaign_start_date']) ? date('d M Y', strtotime($detail['campaign_start_date'])) : '-' ?>
                  &mdash;
                  <?= !empty($detail['campaign_end_date']) ? date('d M Y', strtotime($detail['campaign_end_date'])) : '-' ?>
                </span>
              </div>
            </div>
            <div class="mt-3">
              <a href="<?= base_url('campaigns/view/' . $detail['campaign_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
                <i class="fas fa-external-link-alt me-1"></i> Buka Manajemen Kampanye
              </a>
            </div>
          </div>
        </div>

        <!-- Card Varian Lain Terkait dalam Kampanye -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
              <i class="fas fa-layer-group text-primary me-2"></i>Varian Lain dalam Kampanye Ini
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
              <?= count($otherVariants) ?> Varian Lain
            </span>
          </div>
          <div class="card-body p-0">
            <?php if (!empty($otherVariants)): ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                  <thead class="table-light">
                    <tr>
                      <th>Produk &amp; Varian</th>
                      <th>SKU</th>
                      <th>Stok</th>
                      <th width="70" class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($otherVariants as $ov): ?>
                      <tr>
                        <td>
                          <div class="fw-bold text-dark"><?= esc($ov['variant_name']) ?></div>
                          <small class="text-muted"><?= esc($ov['product_name']) ?></small>
                        </td>
                        <td><code><?= esc($ov['variant_sku']) ?></code></td>
                        <td>
                          <span class="badge bg-light text-dark border">
                            <?= number_format((int)$ov['stock_qty']) ?> unit
                          </span>
                        </td>
                        <td class="text-center">
                          <a href="<?= base_url('campaign-variants/view/' . $ov['id']) ?>" class="btn btn-outline-info btn-sm py-0 px-2" title="Lihat">
                            <i class="fas fa-eye fa-xs"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="p-4 text-center text-muted">
                <i class="fas fa-box-open fa-2x mb-2 d-block text-secondary"></i>
                Belum ada varian produk lain yang didaftarkan pada kampanye ini.
              </div>
            <?php endif; ?>
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
        title: 'Hapus Varian Kampanye?',
        text: 'Apakah Anda yakin ingin menghapus relasi varian ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url('campaign-variants/delete') ?>/' + id,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '<?= site_url('campaign-variants') ?>';
                        });
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memproses penghapusan data.', 'error');
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
