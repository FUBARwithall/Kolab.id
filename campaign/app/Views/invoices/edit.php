<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Invoice #<?= esc($invoice['invoice_number']) ?></h3>
        <p class="text-muted small mb-0">Perbarui rincian nominal tagihan, potongan pajak, tanggal jatuh tempo, dan dokumen lampiran.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>" class="text-decoration-none">Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Rincian Tagihan Invoice</h5>
            <div class="d-flex gap-2">
              <a href="<?= base_url('invoices/view/' . $invoice['id']) ?>" class="btn btn-outline-info btn-sm rounded-2">
                <i class="fas fa-eye me-1"></i> Lihat Detail
              </a>
              <a href="<?= base_url('invoices/print-pdf/' . $invoice['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-print me-1"></i> Cetak PDF
              </a>
              <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
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

            <?php if (session()->has('success')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> <?= session('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form action="<?= base_url('invoices/edit_save/' . $invoice['id']) ?>" method="POST" enctype="multipart/form-data" id="formEditInvoice">
              <?= csrf_field() ?>

              <div class="row g-3">
                <!-- Penugasan KOL -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Penugasan KOL &amp; Kampanye <span class="text-danger">*</span></label>
                  <select class="form-select" id="campaign_kol_id" name="campaign_kol_id" required onchange="onKolSelect(this)">
                    <?php foreach ($assignedKols as $ak): ?>
                      <option value="<?= $ak['id'] ?>" 
                              data-amount="<?= (float)$ak['deal_amount'] ?>" 
                              data-has-npwp="<?= !empty($ak['npwp']) ? '1' : '0' ?>"
                              data-npwp="<?= esc($ak['npwp'] ?? '') ?>"
                              <?= (int)$invoice['campaign_kol_id'] === (int)$ak['id'] ? 'selected' : '' ?>>
                        <?= esc($ak['kol_name']) ?> — <?= esc($ak['campaign_title'] ?: ($ak['campaign_name'] ?? 'Kampanye')) ?> 
                        (Deal: Rp <?= number_format((float)$ak['deal_amount'], 0, ',', '.') ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Nomor Invoice -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Nomor Invoice <span class="text-danger">*</span></label>
                  <input type="text" class="form-control font-monospace fw-bold text-primary bg-light" name="invoice_no" id="invoice_no" value="<?= esc($invoice['invoice_number']) ?>" readonly>
                  <div class="form-text">Nomor invoice resmi.</div>
                </div>

                <!-- Status Tagihan -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Status Tagihan</label>
                  <select class="form-select fw-semibold" id="status" name="status">
                    <option value="SUBMITTED" <?= $invoice['status'] === 'SUBMITTED' ? 'selected' : '' ?>>SUBMITTED (Diajukan)</option>
                    <option value="VERIFIED" <?= in_array($invoice['status'], ['VERIFIED', 'APPROVED'], true) ? 'selected' : '' ?>>VERIFIED (Disetujui / Siap Cair)</option>
                    <option value="PARTIAL" <?= $invoice['status'] === 'PARTIAL' ? 'selected' : '' ?>>PARTIAL (Dibayar Sebagian)</option>
                    <option value="PAID" <?= $invoice['status'] === 'PAID' ? 'selected' : '' ?>>PAID (Lunas Terbayar)</option>
                    <option value="REJECTED" <?= $invoice['status'] === 'REJECTED' ? 'selected' : '' ?>>REJECTED (Ditolak)</option>
                    <option value="CANCELLED" <?= $invoice['status'] === 'CANCELLED' ? 'selected' : '' ?>>CANCELLED (Dibatalkan)</option>
                    <option value="DRAFT" <?= in_array($invoice['status'], ['DRAFT', 'UNPAID'], true) ? 'selected' : '' ?>>DRAFT / UNPAID</option>
                  </select>
                </div>

                <!-- Tanggal Jatuh Tempo -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Tanggal Jatuh Tempo (Due Date)</label>
                  <input type="date" class="form-control" name="due_date" id="due_date" value="<?= !empty($invoice['due_date']) ? date('Y-m-d', strtotime($invoice['due_date'])) : '' ?>">
                </div>

                <!-- Tanggal Terbit -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Tanggal Terbit</label>
                  <input type="text" class="form-control bg-light" value="<?= date('d F Y, H:i', strtotime($invoice['created_at'])) ?>" readonly>
                </div>

                <!-- Gross Fee (Subtotal) -->
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Gross Fee / Subtotal (IDR) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="any" class="form-control fw-bold" name="subtotal" id="subtotal" value="<?= (float)$invoice['subtotal'] ?>" required oninput="recalculateTax()">
                  </div>
                </div>

                <!-- Jenis Pajak -->
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Jenis Pemotongan Pajak</label>
                  <select class="form-select" id="tax_type" name="tax_type" onchange="recalculateTax()">
                    <option value="PPh21" <?= $invoice['tax_type'] === 'PPh21' ? 'selected' : '' ?>>PPh 21 (2.5% NPWP / 3% Non-NPWP)</option>
                    <option value="PPh23" <?= $invoice['tax_type'] === 'PPh23' ? 'selected' : '' ?>>PPh 23 (2.0% Jasa Agensi / Manajemen)</option>
                    <option value="NONE" <?= $invoice['tax_type'] === 'NONE' ? 'selected' : '' ?>>Tanpa Pajak (0%)</option>
                  </select>
                  <div class="form-text" id="npwpStatusInfo">
                    <?= !empty($invoice['kol_npwp']) ? '<span class="text-success"><i class="fas fa-check-circle me-1"></i>NPWP: ' . esc($invoice['kol_npwp']) . ' (2.5%)</span>' : '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Non-NPWP (3.0%)</span>' ?>
                  </div>
                </div>

                <!-- Potongan Pajak -->
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Potongan Pajak (IDR)</label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="any" class="form-control text-danger fw-semibold" name="tax_amount" id="tax_amount" value="<?= (float)$invoice['tax_amount'] ?>" oninput="onTaxAmountChange()">
                  </div>
                </div>

                <!-- Grand Total Card -->
                <div class="col-md-12">
                  <div class="card bg-light border-0 p-3 rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Bersih yang Ditransfer (Net Payout)</div>
                        <h4 class="mb-0 fw-bold text-success" id="grandTotalDisplay">Rp <?= number_format((float)$invoice['grand_total'], 0, ',', '.') ?></h4>
                      </div>
                      <input type="hidden" name="grand_total" id="grand_total" value="<?= (float)$invoice['grand_total'] ?>">
                      <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i> Net Payable
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Lampiran File -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Ganti File Lampiran Invoice (Opsional)</label>
                  <input type="file" class="form-control" name="invoice_file" id="invoice_file" accept=".pdf,.png,.jpg,.jpeg">
                  <?php if (!empty($invoice['invoice_file'])): ?>
                    <div class="mt-2 small text-muted">
                      File saat ini: 
                      <a href="<?= base_url($invoice['invoice_file']) ?>" target="_blank" class="fw-semibold text-primary">
                        <i class="fas fa-paperclip me-1"></i> <?= basename($invoice['invoice_file']) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex gap-2 justify-content-end">
                <a href="<?= base_url('invoices') ?>" class="btn btn-secondary rounded-2 px-3">
                  <i class="fas fa-times me-1"></i> Batal
                </a>
                <button type="submit" name="save_type" value="stay" class="btn btn-outline-primary rounded-2 px-3">
                  <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
                <button type="submit" name="save_type" value="back" class="btn btn-primary rounded-2 px-4 shadow-sm">
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
let hasNpwpSelected = <?= !empty($invoice['kol_npwp']) ? 'true' : 'false' ?>;

function onKolSelect(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    hasNpwpSelected = opt.dataset.hasNpwp === '1';
    const npwpNo = opt.dataset.npwp || '';

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
</script>
<?= $this->endSection() ?>
