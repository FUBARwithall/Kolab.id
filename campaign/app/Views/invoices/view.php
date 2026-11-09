<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Detail Invoice #<?= esc($invoice['invoice_number']) ?></h3>
        <p class="text-muted small mb-0">Informasi lengkap rincian honorarium talent KOL, pemotongan pajak, rekening transfer, dan riwayat pencairan dana.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>" class="text-decoration-none">Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-9">
        
        <!-- Header Actions Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div>
                <span class="text-muted small">Status Invoice:</span>
                <div>
                  <?php if ($invoice['status'] === 'PAID'): ?>
                    <span class="badge bg-success px-3 py-2"><i class="fas fa-check-double me-1"></i>LUNAS (PAID)</span>
                  <?php elseif (in_array($invoice['status'], ['VERIFIED', 'APPROVED'])): ?>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2"><i class="fas fa-check me-1"></i>TERVERIFIKASI (SIAP CAIR)</span>
                  <?php elseif ($invoice['status'] === 'PARTIAL'): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2"><i class="fas fa-adjust me-1"></i>SEBAGIAN (PARTIAL)</span>
                  <?php elseif ($invoice['status'] === 'SUBMITTED'): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2"><i class="far fa-clock me-1"></i>MENUNGGU VERIFIKASI</span>
                  <?php elseif ($invoice['status'] === 'REJECTED'): ?>
                    <span class="badge bg-danger px-3 py-2"><i class="fas fa-times me-1"></i>DITOLAK</span>
                  <?php else: ?>
                    <span class="badge bg-secondary px-3 py-2"><?= esc($invoice['status']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="border-start ps-3">
                <span class="text-muted small">Jatuh Tempo:</span>
                <div class="fw-bold text-dark">
                  <?= !empty($invoice['due_date']) ? date('d F Y', strtotime($invoice['due_date'])) : '-' ?>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2">
              <a href="<?= base_url('invoices/print-pdf/' . $invoice['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-print me-1"></i> Cetak PDF
              </a>
              <a href="<?= base_url('invoices/edit/' . $invoice['id']) ?>" class="btn btn-outline-warning btn-sm rounded-2">
                <i class="fas fa-edit me-1"></i> Edit
              </a>
              <?php if ($invoice['status'] === 'SUBMITTED'): ?>
                <button type="button" class="btn btn-success btn-sm rounded-2" onclick="verifyInvoice(<?= $invoice['id'] ?>)">
                  <i class="fas fa-check me-1"></i> Verifikasi Invoice
                </button>
              <?php endif; ?>
              <?php if (in_array($invoice['status'], ['VERIFIED', 'APPROVED', 'PARTIAL'])): ?>
                <button type="button" class="btn btn-primary btn-sm rounded-2" onclick="payInvoiceModal(<?= $invoice['id'] ?>, <?= (float)$invoice['grand_total'] ?>)">
                  <i class="fas fa-money-bill-wave me-1"></i> Catat Pembayaran
                </button>
              <?php endif; ?>
              <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
              </a>
            </div>
          </div>
        </div>

        <!-- Invoice Details Sheet -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-body p-4 p-md-5">
            <!-- Header Brand & Invoice No -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
              <div>
                <h3 class="fw-bold mb-1 text-primary">KOLAB<span class="text-dark">.ID</span></h3>
                <div class="text-muted small">Platform Manajemen Kampanye Influencer Marketing</div>
                <div class="text-muted small">Jakarta, Indonesia &bull; support@kolab.id</div>
              </div>
              <div class="text-end">
                <h4 class="fw-bold mb-1 font-monospace text-dark"><?= esc($invoice['invoice_number']) ?></h4>
                <div class="text-muted small">Tanggal Terbit: <?= date('d F Y', strtotime($invoice['created_at'])) ?></div>
                <?php if (!empty($invoice['due_date'])): ?>
                  <div class="text-muted small">Jatuh Tempo: <?= date('d F Y', strtotime($invoice['due_date'])) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Talent & Campaign Row -->
            <div class="row g-4 mb-4">
              <div class="col-md-6">
                <div class="text-muted small fw-bold text-uppercase mb-2">Penerima Honorarium (Talent KOL):</div>
                <h5 class="fw-bold mb-1 text-dark"><?= esc($invoice['kol_name']) ?></h5>
                <div class="small text-muted mb-1"><i class="fab fa-whatsapp text-success me-1"></i> <?= esc($invoice['kol_whatsapp'] ?: '-') ?></div>
                <div class="small text-muted mb-1"><i class="fas fa-id-card text-secondary me-1"></i> NIK: <?= esc($invoice['kol_nik'] ?: '-') ?></div>
                <div class="small text-muted mb-1"><i class="fas fa-receipt text-secondary me-1"></i> NPWP: <?= esc($invoice['kol_npwp'] ?: 'Tidak ada (Tarif Non-NPWP)') ?></div>
                <div class="small text-muted"><i class="fas fa-map-marker-alt text-secondary me-1"></i> Kota: <?= esc($invoice['kol_city'] ?: 'Indonesia') ?></div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small fw-bold text-uppercase mb-2">Tujuan Rekening Pembayaran:</div>
                <div class="p-3 bg-light rounded-3 border">
                  <div class="text-muted small">Nama Bank:</div>
                  <div class="fw-bold text-dark fs-6"><?= esc($invoice['bank_name'] ?: '-') ?></div>
                  <div class="text-muted small mt-2">Nomor Rekening:</div>
                  <div class="fw-bold font-monospace text-primary fs-5"><?= esc($invoice['bank_account'] ?: '-') ?></div>
                  <div class="text-muted small mt-2">Atas Nama Rekening:</div>
                  <div class="fw-bold text-dark"><?= esc($invoice['bank_holder'] ?: '-') ?></div>
                </div>
              </div>
            </div>

            <!-- Kampanye Info -->
            <div class="mb-4 p-3 bg-light rounded-3 border">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <div class="text-muted small">Kampanye Terkait:</div>
                  <h6 class="fw-bold text-dark mb-0"><?= esc($invoice['campaign_title'] ?: ($invoice['campaign_name'] ?? 'N/A')) ?></h6>
                  <?php if (!empty($invoice['brand_name'])): ?>
                    <small class="text-muted">Brand: <b><?= esc($invoice['brand_name']) ?></b></small>
                  <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                  <span class="badge bg-secondary-subtle text-secondary border">Penugasan KOL #<?= esc($invoice['campaign_kol_id']) ?></span>
                </div>
              </div>
            </div>

            <!-- Financial Table -->
            <div class="table-responsive mb-4">
              <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Deskripsi Layanan / SOW Konten</th>
                    <th style="width: 100px;" class="text-center">Qty</th>
                    <th style="width: 180px;" class="text-end">Harga Satuan</th>
                    <th style="width: 180px;" class="text-end">Total (IDR)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark">Jasa Pembuatan &amp; Publikasi Konten KOL (Deliverable)</div>
                      <small class="text-muted">Kampanye: <?= esc($invoice['campaign_title'] ?: ($invoice['campaign_name'] ?? 'N/A')) ?></small>
                    </td>
                    <td class="text-center font-monospace"><?= esc($invoice['total_vt'] ?: 1) ?> VT</td>
                    <td class="text-end font-monospace">Rp <?= number_format((float)$invoice['subtotal'], 0, ',', '.') ?></td>
                    <td class="text-end font-monospace fw-bold">Rp <?= number_format((float)$invoice['subtotal'], 0, ',', '.') ?></td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-end fw-semibold">Dasar Pengenaan Pajak (DPP / Subtotal):</td>
                    <td class="text-end font-monospace fw-bold">Rp <?= number_format((float)$invoice['subtotal'], 0, ',', '.') ?></td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-end text-danger">
                      Potongan Pajak <?= esc($invoice['tax_type']) ?> (<?= ((float)$invoice['tax_rate'] * 100) ?>%):
                    </td>
                    <td class="text-end font-monospace text-danger fw-semibold">- Rp <?= number_format((float)$invoice['tax_amount'], 0, ',', '.') ?></td>
                  </tr>
                  <tr class="table-light">
                    <td colspan="3" class="text-end fw-bold fs-6">Total Bersih yang Ditransfer (Net Payout):</td>
                    <td class="text-end font-monospace fw-bold text-success fs-5">Rp <?= number_format((float)$invoice['grand_total'], 0, ',', '.') ?></td>
                  </tr>
                  <?php if (!empty($invoice['amount_paid']) && (float)$invoice['amount_paid'] > 0): ?>
                    <tr>
                      <td colspan="3" class="text-end text-muted">Total Dana yang Telah Dicairkan:</td>
                      <td class="text-end font-monospace fw-bold text-primary">Rp <?= number_format((float)$invoice['amount_paid'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                      <td colspan="3" class="text-end text-muted">Sisa Tagihan (Balance Due):</td>
                      <td class="text-end font-monospace fw-bold <?= (float)$invoice['balance_due'] > 0 ? 'text-danger' : 'text-success' ?>">
                        Rp <?= number_format((float)$invoice['balance_due'], 0, ',', '.') ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Lampiran File -->
            <?php if (!empty($invoice['invoice_file'])): ?>
              <div class="mb-4 p-3 bg-light rounded-3 border">
                <div class="fw-semibold text-dark mb-1"><i class="fas fa-paperclip text-primary me-2"></i>Dokumen Lampiran Tagihan:</div>
                <?php foreach (explode(',', $invoice['invoice_file']) as $f): ?>
                  <?php $f = trim($f); ?>
                  <a href="<?= base_url($f) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-2 me-2 mt-1">
                    <i class="fas fa-download me-1"></i> <?= basename($f) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Riwayat Pembayaran -->
            <h6 class="fw-bold mb-3"><i class="fas fa-history text-secondary me-2"></i>Riwayat Pembayaran &amp; Pencairan Dana</h6>
            <?php if (!empty($invoice['payments'])): ?>
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle border">
                  <thead class="table-light">
                    <tr>
                      <th>Tanggal Pembayaran</th>
                      <th>Metode</th>
                      <th>Nomor Referensi Transfer</th>
                      <th class="text-end">Nominal Dicairkan</th>
                      <th>Diproses Oleh</th>
                      <th class="text-center">Bukti Transfer</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($invoice['payments'] as $p): ?>
                      <tr>
                        <td><?= date('d M Y, H:i', strtotime($p['paid_at'])) ?></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($p['payment_method']) ?></span></td>
                        <td class="font-monospace fw-semibold text-primary"><?= esc($p['bank_reference'] ?: '-') ?></td>
                        <td class="text-end font-monospace fw-bold text-success">Rp <?= number_format((float)$p['amount_paid'], 0, ',', '.') ?></td>
                        <td><?= esc($p['payer_name'] ?: 'Finance Dept') ?></td>
                        <td class="text-center">
                          <?php if (!empty($p['transfer_receipt_file'])): ?>
                            <a href="<?= base_url($p['transfer_receipt_file']) ?>" target="_blank" class="btn btn-xs btn-outline-info rounded-2">
                              <i class="fas fa-image me-1"></i> Bukti
                            </a>
                          <?php else: ?>
                            <span class="text-muted small">-</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-muted small fst-italic p-3 bg-light rounded-3 border text-center">
                Belum ada catatan pencairan dana transfer untuk invoice ini.
              </div>
            <?php endif; ?>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Modal Pencairan Dana -->
<div class="modal fade" id="modalPayInvoice" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formPayInvoice" onsubmit="submitPayment(event)">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Catat Pembayaran Invoice</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="payInvoiceId" name="invoice_id" value="<?= $invoice['id'] ?>">
          <div class="mb-3">
            <label class="form-label text-muted small">Nomor Invoice</label>
            <div class="fw-bold font-monospace text-primary fs-6"><?= esc($invoice['invoice_number']) ?></div>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small">Nilai Net Ditransfer</label>
            <div class="fw-bold text-success fs-5">Rp <?= number_format((float)$invoice['grand_total'], 0, ',', '.') ?></div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah yang Ditransfer (IDR) <span class="text-danger">*</span></label>
            <input type="number" step="any" class="form-control" name="amount_paid" id="payAmountPaid" value="<?= (float)$invoice['grand_total'] ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Metode Pembayaran</label>
            <select class="form-select" name="payment_method">
              <option value="BANK_TRANSFER">Bank Transfer (BCA / Mandiri / BNI / BRI)</option>
              <option value="E_WALLET">E-Wallet (GoPay / OVO / Dana)</option>
              <option value="CASH">Tunai (Petty Cash)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nomor Referensi Bank / Bukti Mutasi</label>
            <input type="text" class="form-control" name="bank_reference" id="payBankRef" placeholder="Contoh: TRX-12345678">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success rounded-2 px-4" id="btnSubmitPayment">Konfirmasi Bayar &amp; Lunasi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function verifyInvoice(id) {
    Swal.fire({
        title: 'Verifikasi Invoice?',
        text: 'Invoice akan diverifikasi dan disetujui untuk diteruskan ke proses pencairan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('invoices/verify/') ?>' + id, function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Terverifikasi', text: res.message }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }
    });
}

function payInvoiceModal(id, grandTotal) {
    $('#payInvoiceId').val(id);
    if (grandTotal) {
        $('#payAmountPaid').val(grandTotal);
    }
    $('#modalPayInvoice').modal('show');
}

function submitPayment(e) {
    e.preventDefault();
    const id = $('#payInvoiceId').val();
    const btn = $('#btnSubmitPayment');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

    $.post('<?= base_url('invoices/save-payment/') ?>' + id, $('#formPayInvoice').serialize(), function(res) {
        btn.prop('disabled', false).text('Konfirmasi Bayar & Lunasi');
        if (res.success) {
            $('#modalPayInvoice').modal('hide');
            Swal.fire({ icon: 'success', title: 'Lunas', text: res.message }).then(() => {
                window.location.reload();
            });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Konfirmasi Bayar & Lunasi');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}
</script>
<?= $this->endSection() ?>
