<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Terbitkan Invoice Baru</h3>
        <p class="text-muted small mb-0">Buat tagihan fee KOL baru secara manual atau pilih penugasan kampanye yang telah disepakati.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>" class="text-decoration-none">Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tambah</li>
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
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-edit text-primary me-2"></i>Form Pembuatan Invoice</h5>
            <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
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

            <form action="<?= base_url('invoices/add_save') ?>" method="POST" enctype="multipart/form-data" id="formAddInvoice">
              <?= csrf_field() ?>

              <div class="row g-3">
                <!-- Penugasan KOL -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Pilih Penugasan KOL &amp; Kampanye <span class="text-danger">*</span></label>
                  <select class="form-select" id="campaign_kol_id" name="campaign_kol_id" required onchange="onKolSelect(this)">
                    <option value="">-- Pilih Penugasan KOL --</option>
                    <?php foreach ($assignedKols as $ak): ?>
                      <option value="<?= $ak['id'] ?>" 
                              data-amount="<?= (float)$ak['deal_amount'] ?>" 
                              data-has-npwp="<?= !empty($ak['npwp']) ? '1' : '0' ?>"
                              data-npwp="<?= esc($ak['npwp'] ?? '') ?>"
                              <?= old('campaign_kol_id') == $ak['id'] ? 'selected' : '' ?>>
                        <?= esc($ak['kol_name']) ?> — <?= esc($ak['campaign_title'] ?: ($ak['campaign_name'] ?? 'Kampanye')) ?> 
                        (Deal: Rp <?= number_format((float)$ak['deal_amount'], 0, ',', '.') ?><?= !empty($ak['npwp']) ? ' &bull; NPWP OK' : ' &bull; Non-NPWP' ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Pilih deal penugasan KOL yang akan diterbitkan tagihannya. Subtotal dan tarif pajak akan otomatis disesuaikan.</div>
                </div>

                <!-- Nomor Invoice (Auto) -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Nomor Invoice <span class="text-danger">*</span></label>
                  <input type="text" class="form-control font-monospace fw-bold text-primary bg-light" name="invoice_no" id="invoice_no" value="<?= old('invoice_no', $generated_invoice_no) ?>" readonly>
                  <div class="form-text">Nomor invoice otomatis digenerate secara berurutan dan unik.</div>
                </div>

                <!-- Tanggal Jatuh Tempo -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Tanggal Jatuh Tempo (Due Date)</label>
                  <input type="date" class="form-control" name="due_date" id="due_date" value="<?= old('due_date', date('Y-m-d', strtotime('+14 days'))) ?>">
                  <div class="form-text">Batas waktu pelunasan invoice (default 14 hari sejak penerbitan).</div>
                </div>

                <!-- Gross Fee (Subtotal) -->
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Gross Fee / Subtotal (IDR) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="any" class="form-control fw-bold" name="subtotal" id="subtotal" placeholder="0" value="<?= old('subtotal', 0) ?>" required oninput="recalculateTax()">
                  </div>
                  <div class="form-text">Nilai bruto honorarium sesuai kontrak / deal.</div>
                </div>

                <!-- Jenis Pajak -->
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Jenis Pemotongan Pajak</label>
                  <select class="form-select" id="tax_type" name="tax_type" onchange="recalculateTax()">
                    <option value="PPh21" <?= old('tax_type') === 'PPh21' ? 'selected' : '' ?>>PPh 21 (2.5% NPWP / 3% Non-NPWP)</option>
                    <option value="PPh23" <?= old('tax_type') === 'PPh23' ? 'selected' : '' ?>>PPh 23 (2.0% Jasa Agensi / Manajemen)</option>
                    <option value="NONE" <?= old('tax_type') === 'NONE' ? 'selected' : '' ?>>Tanpa Pajak (0%)</option>
                  </select>
                  <div class="form-text" id="npwpStatusInfo">Status NPWP: -</div>
                </div>

                <!-- Nilai Potongan Pajak -->
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Potongan Pajak (IDR)</label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="any" class="form-control text-danger fw-semibold" name="tax_amount" id="tax_amount" value="<?= old('tax_amount', 0) ?>" oninput="onTaxAmountChange()">
                  </div>
                  <div class="form-text">Nilai pajak yang dipotong dari honor bruto.</div>
                </div>

                <!-- Net Total / Grand Total -->
                <div class="col-md-12">
                  <div class="card bg-light border-0 p-3 rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Bersih yang Ditransfer (Net Payout)</div>
                        <h4 class="mb-0 fw-bold text-success" id="grandTotalDisplay">Rp 0</h4>
                      </div>
                      <input type="hidden" name="grand_total" id="grand_total" value="<?= old('grand_total', 0) ?>">
                      <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i> Net Payable
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Lampiran File Invoice -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Lampiran File Invoice / MoU (PDF / Gambar)</label>
                  <input type="file" class="form-control" name="invoice_file" id="invoice_file" accept=".pdf,.png,.jpg,.jpeg">
                  <div class="form-text">Opsional: Unggah dokumen invoice bertanda tangan, bukti transfer, atau form tagihan fisik.</div>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex gap-2 justify-content-end">
                <a href="<?= base_url('invoices') ?>" class="btn btn-secondary rounded-2 px-3">
                  <i class="fas fa-times me-1"></i> Batal
                </a>
                <button type="submit" name="save_type" value="stay" class="btn btn-outline-primary rounded-2 px-3">
                  <i class="fas fa-save me-1"></i> Simpan &amp; Lanjut Edit
                </button>
                <button type="submit" name="save_type" value="back" class="btn btn-primary rounded-2 px-4 shadow-sm" id="btnSubmit">
                  <i class="fas fa-check me-1"></i> Simpan &amp; Kembali ke Daftar
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

<?= $this->section('pageScript') ?>
<script>
let hasNpwpSelected = false;

function onKolSelect(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
        $('#npwpStatusInfo').text('Status NPWP: -');
        return;
    }

    const amount = parseFloat(opt.dataset.amount || 0);
    hasNpwpSelected = opt.dataset.hasNpwp === '1';
    const npwpNo = opt.dataset.npwp || '';

    if (amount > 0 && parseFloat($('#subtotal').val() || 0) === 0) {
        $('#subtotal').val(amount);
    }

    if (hasNpwpSelected) {
        $('#npwpStatusInfo').html('<span class="text-success"><i class="fas fa-check-circle me-1"></i>NPWP Terdaftar: ' + npwpNo + ' (Tarif 2.5%)</span>');
    } else {
        $('#npwpStatusInfo').html('<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Non-NPWP (Tarif 3.0%)</span>');
    }

    recalculateTax();
}

function recalculateTax() {
    const subtotal = parseFloat($('#subtotal').val() || 0);
    const taxType = $('#tax_type').val();
    let rate = 0;

    if (taxType === 'PPh21') {
        rate = hasNpwpSelected ? 0.025 : 0.030;
    } else if (taxType === 'PPh23') {
        rate = 0.020;
    } else {
        rate = 0;
    }

    const taxAmount = Math.round(subtotal * rate);
    const grandTotal = Math.max(0, subtotal - taxAmount);

    $('#tax_amount').val(taxAmount);
    $('#grand_total').val(grandTotal);
    $('#grandTotalDisplay').text('Rp ' + grandTotal.toLocaleString('id-ID'));
}

function onTaxAmountChange() {
    const subtotal = parseFloat($('#subtotal').val() || 0);
    const taxAmount = parseFloat($('#tax_amount').val() || 0);
    const grandTotal = Math.max(0, subtotal - taxAmount);

    $('#grand_total').val(grandTotal);
    $('#grandTotalDisplay').text('Rp ' + grandTotal.toLocaleString('id-ID'));
}

$(document).ready(function() {
    const kolSel = document.getElementById('campaign_kol_id');
    if (kolSel && kolSel.value) {
        onKolSelect(kolSel);
    } else {
        recalculateTax();
    }
});
</script>
<?= $this->endSection() ?>
