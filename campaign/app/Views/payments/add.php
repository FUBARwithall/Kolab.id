<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-hand-holding-usd text-success me-2"></i>Catat Pencairan Pembayaran</h3>
        <p class="text-muted small mb-0">Input bukti pengeluaran kas/bank untuk pelunasan tagihan invoice fee talent KOL.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('payments') ?>" class="text-decoration-none">Payments</a></li>
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
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Form Transaksi Pembayaran</h5>
            <a href="<?= base_url('payments') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
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

            <form action="<?= base_url('payments/add_save') ?>" method="POST" enctype="multipart/form-data" id="formAddPayment">
              <?= csrf_field() ?>

              <div class="row g-3">
                <!-- Pilih Invoice -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Pilih Invoice Tagihan <span class="text-danger">*</span></label>
                  <select class="form-select" id="invoice_id" name="invoice_id" required onchange="onInvoiceSelect(this.value)">
                    <option value="">-- Pilih Tagihan Invoice yang Akan Dibayarkan --</option>
                    <?php foreach ($invoices as $inv): ?>
                      <?php
                        $grandTotal = (float)($inv['grand_total'] ?? 0);
                        $totalPaid  = (float)($inv['total_paid'] ?? 0);
                        $balance    = max(0, $grandTotal - $totalPaid);
                      ?>
                      <option value="<?= $inv['id'] ?>" <?= ($selectedInvId === (int)$inv['id']) ? 'selected' : '' ?>>
                        <?= esc($inv['invoice_number'] ?? '') ?> &bull; <?= esc($inv['kol_name'] ?? '') ?> — <?= esc(($inv['campaign_title'] ?? '') ?: ($inv['campaign_name'] ?? 'Kampanye')) ?> 
                        (Sisa Tagihan: Rp <?= number_format($balance, 0, ',', '.') ?> &bull; Status: <?= esc($inv['status'] ?? 'PENDING') ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Info Rekening & Sisa Tagihan Box -->
                <div class="col-md-12" id="invoiceBalanceCard" style="display: none;">
                  <div class="card bg-light border-0 p-3 rounded-3">
                    <div class="row g-3">
                      <div class="col-md-6 border-end-md">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Rekening Tujuan Transfer:</div>
                        <div class="fw-bold text-dark fs-6" id="boxBankName">-</div>
                        <div class="fw-bold font-monospace text-primary fs-5 mt-1" id="boxBankAccount">-</div>
                        <div class="small text-muted" id="boxBankHolder">-</div>
                      </div>
                      <div class="col-md-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Rincian Finansial Tagihan:</div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                          <span>Total Net Invoice:</span>
                          <span class="fw-bold text-dark" id="boxGrandTotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                          <span>Telah Dibayar Sebelumnya:</span>
                          <span class="fw-bold text-primary" id="boxTotalPaid">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                          <span class="fw-bold text-dark">Sisa Tagihan (Balance Due):</span>
                          <h5 class="mb-0 fw-bold text-success" id="boxBalanceDue">Rp 0</h5>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Jumlah Pembayaran -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Jumlah yang Ditransfer (IDR) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="any" class="form-control fw-bold text-success fs-5" name="amount_paid" id="amount_paid" placeholder="0" required>
                  </div>
                  <div class="form-text">Nominal riil yang ditransfer dari rekening perusahaan.</div>
                </div>

                <!-- Tanggal & Jam Pembayaran -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Tanggal &amp; Waktu Pembayaran <span class="text-danger">*</span></label>
                  <input type="datetime-local" class="form-control" name="paid_at" id="paid_at" value="<?= date('Y-m-d\TH:i') ?>" required>
                  <div class="form-text">Waktu transaksi transfer berhasil dilakukan.</div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                  <select class="form-select fw-semibold" name="payment_method" id="payment_method">
                    <option value="Bank Transfer (BCA)">Bank Transfer (BCA)</option>
                    <option value="Bank Transfer (Mandiri)">Bank Transfer (Mandiri)</option>
                    <option value="Bank Transfer (BNI)">Bank Transfer (BNI)</option>
                    <option value="Bank Transfer (BRI)">Bank Transfer (BRI)</option>
                    <option value="Bank Transfer (BSI)">Bank Transfer (BSI)</option>
                    <option value="E-Wallet (GoPay)">E-Wallet (GoPay)</option>
                    <option value="E-Wallet (OVO)">E-Wallet (OVO)</option>
                    <option value="E-Wallet (Dana)">E-Wallet (Dana)</option>
                    <option value="Cash / Tunai">Cash / Tunai</option>
                  </select>
                </div>

                <!-- Nomor Referensi Bank -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Nomor Referensi Transfer Bank</label>
                  <input type="text" class="form-control font-monospace" name="bank_reference" id="bank_reference" placeholder="Contoh: TRX-12345678" value="TRX-<?= strtoupper(substr(md5(uniqid()), 0, 8)) ?>">
                  <div class="form-text">Nomor referensi atau ID transaksi mutasi bank.</div>
                </div>

                <!-- Bukti Transfer File -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Unggah Bukti Transfer / Mutasi Kas</label>
                  <input type="file" class="form-control" name="transfer_receipt_file" id="transfer_receipt_file" accept=".jpg,.jpeg,.png,.pdf">
                  <div class="form-text">Format: JPG, PNG, atau PDF (Maks. 5MB).</div>
                </div>

                <!-- Catatan Transaksi -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Catatan / Keterangan (Opsional)</label>
                  <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Catatan tambahan mengenai pelunasan tagihan ini..."></textarea>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex gap-2 justify-content-end">
                <a href="<?= base_url('payments') ?>" class="btn btn-secondary rounded-2 px-3">
                  <i class="fas fa-times me-1"></i> Batal
                </a>
                <button type="submit" name="save_type" value="stay" class="btn btn-outline-primary rounded-2 px-3">
                  <i class="fas fa-save me-1"></i> Simpan &amp; Lanjut Edit
                </button>
                <button type="submit" name="save_type" value="back" class="btn btn-success rounded-2 px-4 shadow-sm" id="btnSubmit">
                  <i class="fas fa-check-circle me-1"></i> Konfirmasi Pembayaran &amp; Simpan
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
function onInvoiceSelect(invoiceId) {
    if (!invoiceId) {
        $('#invoiceBalanceCard').slideUp();
        $('#amount_paid').val('');
        return;
    }

    $.get('<?= base_url('payments/get_invoice_balance/') ?>' + invoiceId, function(res) {
        if (res.success) {
            $('#boxBankName').text(res.bank_name || '-');
            $('#boxBankAccount').text(res.bank_account || '-');
            $('#boxBankHolder').text('a/n ' + (res.bank_holder || '-'));

            $('#boxGrandTotal').text('Rp ' + Number(res.grand_total).toLocaleString('id-ID'));
            $('#boxTotalPaid').text('Rp ' + Number(res.total_paid).toLocaleString('id-ID'));
            $('#boxBalanceDue').text('Rp ' + Number(res.balance).toLocaleString('id-ID'));

            $('#amount_paid').val(res.balance > 0 ? res.balance : res.grand_total);
            $('#invoiceBalanceCard').slideDown();
        }
    });
}

$(document).ready(function() {
    const invId = $('#invoice_id').val();
    if (invId) {
        onInvoiceSelect(invId);
    }
});
</script>
<?= $this->endSection() ?>
