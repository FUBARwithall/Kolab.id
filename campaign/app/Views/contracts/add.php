<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-contract text-primary me-2"></i>Buat Surat Kontrak Baru</h3>
        <p class="text-muted small mb-0">Generate surat perjanjian kerjasama (MoU) untuk penugasan kampanye influencer.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('contracts') ?>" class="text-decoration-none">Kontrak</a></li>
          <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
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
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-plus-circle text-success me-2"></i>Form Pembuatan Kontrak</h5>
            <a href="<?= base_url('contracts') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>

          <div class="card-body p-4">
            <?php if (session()->has('error')): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> <?= session('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form action="<?= base_url('contracts/add_save') ?>" method="POST" enctype="multipart/form-data" id="formAddContract">
              <?= csrf_field() ?>

              <div class="row g-3">
                <!-- Pilih Penugasan KOL -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Pilih Penugasan Talent KOL <span class="text-danger">*</span></label>
                  <select class="form-select" id="campaign_kol_id" name="campaign_kol_id" required>
                    <option value="">-- Pilih Penugasan Influencer / Deal Kampanye --</option>
                    <?php foreach ($assignedKols as $ak): ?>
                      <option value="<?= $ak['id'] ?>">
                        <?= esc($ak['kol_name']) ?> &bull; <?= esc($ak['campaign_title'] ?: ($ak['campaign_name'] ?? 'Kampanye')) ?> 
                        (Fee SOW: Rp <?= number_format((float)$ak['deal_amount'], 0, ',', '.') ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Pilih deal kampanye yang telah disepakati untuk digeneratekan naskah surat kontrak resmi.</div>
                </div>

                <!-- Template Kontrak -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Master Template Klausul Kontrak</label>
                  <select class="form-select" id="template_id" name="template_id">
                    <option value="">-- Gunakan Template Standar Kolab.id --</option>
                    <?php if (!empty($contractTemplates)): ?>
                      <?php foreach ($contractTemplates as $tpl): ?>
                        <option value="<?= $tpl['id'] ?>"><?= esc($tpl['title']) ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                  <div class="form-text">Template klausul pasal, SOW, hak cipta, dan ketentuan pembayaran.</div>
                </div>

                <!-- Tanggal Kadaluarsa -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Masa Berlaku / Tanggal Berakhir (Expired)</label>
                  <input type="date" class="form-control" name="expired_at" id="expired_at" value="<?= date('Y-m-d', strtotime('+90 days')) ?>">
                  <div class="form-text">Batas waktu pelaksanaan kerjasama dan hak tayang materi konten.</div>
                </div>

                <!-- File Dokumen MoU Tambahan (Opsional) -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Unggah File Dokumen Kontrak Fisik (Opsional)</label>
                  <input type="file" class="form-control" name="contract_file" id="contract_file" accept=".pdf,.doc,.docx,.jpg,.png">
                  <div class="form-text">Format: PDF, Word, atau Gambar (Maks. 10MB). Biarkan kosong jika ingin digenerate otomatis oleh sistem.</div>
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
                  <i class="fas fa-check-circle me-1"></i> Generate &amp; Terbitkan Kontrak
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
