<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Surat Kontrak &amp; Klausul</h3>
        <p class="text-muted small mb-0">Ubah syarat &amp; ketentuan, masa berlaku kontrak, atau unggah dokumen revisi.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('contracts') ?>" class="text-decoration-none">Kontrak</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit #<?= esc($contract['id']) ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-9">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
              <i class="fas fa-file-contract text-primary me-2"></i>Edit Kontrak #<?= esc($contract['display_contract_no']) ?>
            </h5>
            <div class="d-flex gap-2">
              <a href="<?= base_url('contracts/view/' . $contract['id']) ?>" class="btn btn-outline-info btn-sm rounded-2">
                <i class="fas fa-eye me-1"></i> Lihat Detail
              </a>
              <a href="<?= base_url('contracts') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
              </a>
            </div>
          </div>

          <div class="card-body p-4">
            <?php if (session()->has('error')): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> <?= session('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form action="<?= base_url('contracts/edit_save/' . $contract['id']) ?>" method="POST" enctype="multipart/form-data" id="formEditContract">
              <?= csrf_field() ?>

              <div class="row g-3">
                <!-- Info Header Box -->
                <div class="col-md-12">
                  <div class="card bg-light border-0 p-3 rounded-3">
                    <div class="row g-2">
                      <div class="col-md-4">
                        <small class="text-muted d-block text-uppercase fw-bold">Nomor Surat Kontrak</small>
                        <span class="fw-bold font-monospace text-primary fs-6"><?= esc($contract['display_contract_no']) ?></span>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted d-block text-uppercase fw-bold">Talent KOL</small>
                        <span class="fw-bold text-dark"><?= esc($contract['kol_name'] ?? '-') ?></span>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted d-block text-uppercase fw-bold">Kampanye / Fee Deal</small>
                        <span class="fw-semibold text-dark"><?= esc($contract['campaign_title'] ?: ($contract['campaign_name'] ?? '-')) ?></span>
                        <div class="small text-success fw-bold">Rp <?= number_format((float)($contract['deal_amount'] ?? 0), 0, ',', '.') ?></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Status Kontrak -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Status Dokumen Kontrak <span class="text-danger">*</span></label>
                  <select class="form-select fw-semibold" name="status" id="status" required>
                    <?php
                      $statuses = [
                        'DRAFT'                  => 'DRAFT (Naskah Baru)',
                        'PENDING'                => 'PENDING (Menunggu TTD KOL)',
                        'WAITING_KOL_SIGNATURE'  => 'WAITING_KOL_SIGNATURE',
                        'ACTIVE'                 => 'ACTIVE (Resmi & Sah)',
                        'SIGNED'                 => 'SIGNED (Sudah Ditandatangani)',
                        'COMPLETED'              => 'COMPLETED (Kerjasama Selesai)',
                        'EXPIRED'                => 'EXPIRED (Kadaluarsa)',
                        'CANCELLED'              => 'CANCELLED (Dibatalkan)',
                      ];
                      $curStatus = strtoupper($contract['status'] ?? 'DRAFT');
                    ?>
                    <?php foreach ($statuses as $val => $lbl): ?>
                      <option value="<?= $val ?>" <?= ($curStatus === $val) ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Tanggal Kadaluarsa -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Masa Berlaku / Tanggal Berakhir (Expired)</label>
                  <input type="date" class="form-control" name="expired_at" id="expired_at" value="<?= esc($contract['expired_at'] ?? '') ?>">
                </div>

                <!-- Syarat & Ketentuan Khusus (S&K) -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Syarat &amp; Ketentuan Khusus (S&amp;K)</label>
                  <textarea class="form-control" name="terms_and_conditions" id="terms_and_conditions" rows="4"><?= esc($contract['terms_and_conditions'] ?? '') ?></textarea>
                  <div class="form-text">Poin-poin kewajiban talent, hak tayang materi iklan, dan tata cara pembayaran.</div>
                </div>

                <!-- Naskah Klausul Kontrak (HTML / Teks) -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Naskah Lengkap Klausul Kontrak</label>
                  <textarea class="form-control font-monospace small" name="contract_html" id="contract_html" rows="8"><?= esc($contract['contract_html'] ?: ($contract['clauses_rendered'] ?? '')) ?></textarea>
                  <div class="form-text">Naskah resmi surat perjanjian yang tampil pada dokumen cetak PDF.</div>
                </div>

                <!-- Upload Dokumen Fisik / PDF -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Unggah / Ganti Dokumen PDF Kontrak</label>
                  <?php if (!empty($contract['contract_file'])): ?>
                    <div class="alert alert-light border d-flex align-items-center justify-content-between p-2 mb-2">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-file-pdf text-danger fs-4 me-2"></i>
                        <span class="small font-monospace"><?= esc(basename($contract['contract_file'])) ?></span>
                      </div>
                      <a href="<?= base_url($contract['contract_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Buka File
                      </a>
                    </div>
                  <?php endif; ?>
                  <input type="file" class="form-control" name="contract_file" id="contract_file" accept=".pdf,.doc,.docx,.jpg,.png">
                  <div class="form-text">Biarkan kosong jika tidak ingin mengubah dokumen file lampiran.</div>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex gap-2 justify-content-end">
                <a href="<?= base_url('contracts') ?>" class="btn btn-secondary rounded-2 px-3">
                  <i class="fas fa-times me-1"></i> Batal
                </a>
                <button type="submit" name="save_type" value="stay" class="btn btn-outline-primary rounded-2 px-3">
                  <i class="fas fa-save me-1"></i> Simpan &amp; Lanjut Edit
                </button>
                <button type="submit" name="save_type" value="back" class="btn btn-primary rounded-2 px-4 shadow-sm" id="btnSubmit">
                  <i class="fas fa-check-circle me-1"></i> Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
