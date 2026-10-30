<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-layer-group text-primary me-2"></i>Detail Variant Konsep #<?= esc($variant['id']) ?>
        </h3>
        <p class="text-muted small mb-0">Informasi spesifikasi format deliverable konten dan status keaktifan dalam sistem.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('variants') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="editVariant(<?= (int)$variant['id'] ?>)">
          <i class="fas fa-edit me-1"></i> Edit Varian
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-info-circle text-primary me-2"></i>Rincian Variant Konsep Konten
            </h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr>
                  <td class="bg-light fw-semibold text-secondary" style="width: 30%;">ID Varian</td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1 fs-6">
                      #<?= esc($variant['id']) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Nama Konsep</td>
                  <td>
                    <div class="fw-bold fs-5 text-dark"><?= esc($variant['name']) ?></div>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Kode Konsep</td>
                  <td>
                    <span class="badge bg-light text-primary border border-primary-subtle font-monospace px-3 py-1 fs-6">
                      <?= esc($variant['code']) ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Deskripsi &amp; Panduan</td>
                  <td>
                    <div class="text-secondary">
                      <?= nl2br(esc($variant['description'] ?: 'Tidak ada deskripsi tambahan.')) ?>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Status Keaktifan</td>
                  <td>
                    <?php if ((int)$variant['is_active'] === 1): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                        <i class="fas fa-check-circle me-1"></i>AKTIF (Dapat dipilih di kampanye &amp; penugasan)
                      </span>
                    <?php else: ?>
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">
                        <i class="fas fa-times-circle me-1"></i>NONAKTIF
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Waktu Pembuatan</td>
                  <td>
                    <span class="text-muted small">
                      <?= !empty($variant['created_at']) ? date('d F Y, H:i:s', strtotime($variant['created_at'])) : '-' ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="bg-light fw-semibold text-secondary">Pembaruan Terakhir</td>
                  <td>
                    <span class="text-muted small">
                      <?= !empty($variant['updated_at']) ? date('d F Y, H:i:s', strtotime($variant['updated_at'])) : '-' ?>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
            <a href="<?= site_url('variants') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
              <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 me-2" onclick="deleteVariant(<?= (int)$variant['id'] ?>)">
                <i class="fas fa-trash-alt me-1"></i> Hapus
              </button>
              <button type="button" class="btn btn-primary btn-sm rounded-2 px-3" onclick="editVariant(<?= (int)$variant['id'] ?>)">
                <i class="fas fa-edit me-1"></i> Edit Konsep
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Edit Variant -->
<div class="modal fade" id="modalVariantForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formVariant" onsubmit="submitVariantForm(event)">
        <input type="hidden" name="id" id="varId" value="<?= esc($variant['id']) ?>">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">
            <i class="fas fa-edit me-2"></i>Edit Varian Konsep #<?= esc($variant['id']) ?>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Konsep Konten <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" id="varName" required value="<?= esc($variant['name']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Konsep <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-monospace" name="code" id="varCode" required value="<?= esc($variant['code']) ?>" style="text-transform: uppercase;">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi Konsep</label>
            <textarea class="form-control" name="description" id="varDesc" rows="3"><?= esc($variant['description']) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status Keaktifan</label>
            <select class="form-select" name="is_active" id="varIsActive">
              <option value="1" <?= (int)$variant['is_active'] === 1 ? 'selected' : '' ?>>Aktif</option>
              <option value="0" <?= (int)$variant['is_active'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" id="btnSubmitVar">
            <i class="fas fa-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function editVariant(id) {
  $('#modalVariantForm').modal('show');
}

function submitVariantForm(e) {
  e.preventDefault();
  const btn = $('#btnSubmitVar');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

  $.post('<?= site_url('variants/save') ?>', $('#formVariant').serialize(), function(res) {
    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Perubahan');
    if (res.success) {
      $('#modalVariantForm').modal('hide');
      Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false }).then(() => {
        location.reload();
      });
    }
  }).fail(function(xhr) {
    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Perubahan');
    const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: err });
  });
}

function deleteVariant(id) {
  Swal.fire({
    title: 'Hapus Varian Konsep?',
    text: 'Apakah Anda yakin ingin menghapus varian konsep #' + id + ' ini?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= site_url('variants/delete') ?>/' + id, {}, function(res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false }).then(() => {
            window.location.href = '<?= site_url('variants') ?>';
          });
        }
      }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus varian.';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
