<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Pencairan Pembayaran</h3>
        <p class="text-muted small mb-0">Ubah detail transaksi pengeluaran kas atau perbarui bukti transfer.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('payments') ?>" class="text-decoration-none">Payments</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit #<?= esc($payment['id']) ?></li>
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
              <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Edit Pembayaran #<?= esc($payment['id']) ?>
            </h5>
            <div class="d-flex gap-2">
              <a href="<?= base_url('payments/view/' . $payment['id']) ?>" class="btn btn-outline-info btn-sm rounded-2">
                <i class="fas fa-eye me-1"></i> Lihat Detail
              </a>
              <a href="<?= base_url('payments') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
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

            <form action="<?= base_url('payments/edit_save/' . $payment['id']) ?>" method="POST" enctype="multipart/form-data" id="formEditPayment">
              <?= csrf_field() ?>

              <div class="row g-3">
                <!-- Pilih Invoice -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Tagihan Invoice Terkait <span class="text-danger">*</span></label>
                  <select class="form-select" id="invoice_id" name="invoice_id" required onchange="onInvoiceSelect(this.value)">
                    <option value="">-- Pilih Tagihan Invoice --</option>
                    <?php foreach ($invoices as $inv): ?>
                      <option value="<?= $inv['id'] ?>" <?= ((int)$payment['invoice_id'] === (int)$inv['id']) ? 'selected' : '' ?>>
                        <?= esc($inv['invoice_number'] ?? '') ?> &bull; <?= esc($inv['kol_name'] ?? '') ?> — <?= esc($inv['campaign_title'] ?? 'Kampanye') ?> 
                        (Total: Rp <?= number_format((float)($inv['grand_total'] ?? 0), 0, ',', '.') ?> &bull; Status: <?= esc($inv['status'] ?? 'PENDING') ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Info Rekening & Sisa Tagihan Box -->
                <div class="col-md-12" id="invoiceBalanceCard">
                  <div class="card bg-light border-0 p-3 rounded-3">
                    <div class="row g-3">
                      <div class="col-md-6 border-end-md">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Rekening Tujuan Transfer:</div>
                        <div class="fw-bold text-dark fs-6" id="boxBankName"><?= esc($payment['bank_name'] ?: '-') ?></div>
                        <div class="fw-bold font-monospace text-primary fs-5 mt-1" id="boxBankAccount"><?= esc($payment['bank_account'] ?: '-') ?></div>
                        <div class="small text-muted" id="boxBankHolder">a/n <?= esc($payment['bank_holder'] ?: '-') ?></div>
                      </div>
                      <div class="col-md-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Rincian Finansial Tagihan:</div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                          <span>Total Net Invoice:</span>
                          <span class="fw-bold text-dark" id="boxGrandTotal">Rp <?= number_format((float)($payment['invoice_grand_total'] ?? 0), 0, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                          <span>Status Tagihan:</span>
                          <span class="badge bg-secondary" id="boxInvoiceStatus"><?= esc($payment['invoice_status'] ?? 'PENDING') ?></span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                          <span class="fw-bold text-dark">Sisa Tagihan Saat Ini:</span>
                          <h5 class="mb-0 fw-bold text-success" id="boxBalanceDue">Rp -</h5>
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
                    <input type="number" step="any" class="form-control fw-bold text-success fs-5" name="amount_paid" id="amount_paid" value="<?= esc($payment['amount_paid'] ?? $payment['amount']) ?>" required>
                  </div>
                  <div class="form-text">Nominal riil yang dibayarkan ke talent.</div>
                </div>

                <!-- Tanggal & Jam Pembayaran -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Tanggal &amp; Waktu Pembayaran <span class="text-danger">*</span></label>
                  <?php
                    $dt = !empty($payment['paid_at']) ? date('Y-m-d\TH:i', strtotime($payment['paid_at'])) : date('Y-m-d\TH:i');
                  ?>
                  <input type="datetime-local" class="form-control" name="paid_at" id="paid_at" value="<?= $dt ?>" required>
                  <div class="form-text">Waktu transaksi mutasi kas/bank berhasil.</div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                  <select class="form-select fw-semibold" name="payment_method" id="payment_method">
                    <?php
                      $methods = [
                        'Bank Transfer (BCA)',
                        'Bank Transfer (Mandiri)',
                        'Bank Transfer (BNI)',
                        'Bank Transfer (BRI)',
                        'Bank Transfer (BSI)',
                        'E-Wallet (GoPay)',
                        'E-Wallet (OVO)',
                        'E-Wallet (Dana)',
                        'Cash / Tunai'
                      ];
                      $curMethod = $payment['payment_method'] ?? 'Bank Transfer (BCA)';
                    ?>
                    <?php foreach ($methods as $m): ?>
                      <option value="<?= $m ?>" <?= (strcasecmp($curMethod, $m) === 0) ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Nomor Referensi Bank -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Nomor Referensi Transfer Bank</label>
                  <input type="text" class="form-control font-monospace" name="bank_reference" id="bank_reference" value="<?= esc($payment['bank_reference'] ?? '') ?>" placeholder="TRX-XXXX">
                  <div class="form-text">Nomor referensi atau ID transaksi mutasi bank.</div>
                </div>

                <!-- Bukti Transfer File -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Unggah / Ganti Bukti Transfer</label>
                  <?php $receipt = $payment['transfer_receipt_file'] ?? $payment['payment_proof_file'] ?? null; ?>
                  <?php if (!empty($receipt)): ?>
                    <div class="alert alert-light border d-flex align-items-center justify-content-between p-2 mb-2">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-file-invoice text-primary fs-4 me-2"></i>
                        <div>
                          <span class="small fw-bold d-block">File Bukti Terpasang:</span>
                          <span class="small text-muted font-monospace"><?= esc(basename($receipt)) ?></span>
                        </div>
                      </div>
                      <div class="d-flex gap-2">
                        <a href="<?= base_url($receipt) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-external-link-alt me-1"></i> Buka File
                        </a>
                      </div>
                    </div>
                  <?php endif; ?>
                  <input type="file" class="form-control" name="transfer_receipt_file" id="transfer_receipt_file" accept=".jpg,.jpeg,.png,.pdf">
                  <div class="form-text">Biarkan kosong jika tidak ingin mengubah file bukti transfer (Maks. 5MB).</div>
                </div>

                <!-- Catatan Transaksi -->
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Catatan / Keterangan (Opsional)</label>
                  <textarea class="form-control" name="notes" id="notes" rows="3"><?= esc($payment['notes'] ?? '') ?></textarea>
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
                <button type="submit" name="save_type" value="back" class="btn btn-primary rounded-2 px-4 shadow-sm" id="btnSubmit">
                  <i class="fas fa-check-circle me-1"></i> Perbarui Pembayaran
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
        return;
    }

    $.get('<?= base_url('payments/get_invoice_balance/') ?>' + invoiceId, function(res) {
        if (res.success) {
            $('#boxBankName').text(res.bank_name || '-');
            $('#boxBankAccount').text(res.bank_account || '-');
            $('#boxBankHolder').text('a/n ' + (res.bank_holder || '-'));

            $('#boxGrandTotal').text('Rp ' + Number(res.grand_total).toLocaleString('id-ID'));
            $('#boxBalanceDue').text('Rp ' + Number(res.balance).toLocaleString('id-ID'));
            if (res.status) {
                $('#boxInvoiceStatus').text(res.status);
            }
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
