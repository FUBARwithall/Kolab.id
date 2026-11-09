<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Detail Pembayaran #<?= esc($payment['id']) ?></h3>
        <p class="text-muted small mb-0">Rincian bukti pengeluaran dana kas/bank untuk pelunasan invoice talent.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('payments') ?>" class="text-decoration-none">Payments</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detail #<?= esc($payment['id']) ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <!-- Top Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a href="<?= base_url('payments') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
      </a>
      <div class="d-flex gap-2">
        <a href="<?= base_url('payments/receipt-pdf/' . $payment['id']) ?>" target="_blank" class="btn btn-danger btn-sm rounded-2 shadow-sm">
          <i class="fas fa-print me-1"></i> Cetak Bukti Pembayaran (Voucher)
        </a>
        <a href="<?= base_url('payments/edit/' . $payment['id']) ?>" class="btn btn-warning btn-sm rounded-2 shadow-sm">
          <i class="fas fa-edit me-1"></i> Edit Transaksi
        </a>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Detail Transaksi & Rincian Finansial -->
      <div class="col-lg-8">
        <!-- Card 1: Rincian Pembayaran -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt text-success me-2"></i>Transaksi Pencairan Dana</h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold fs-6">
              LUNAS / BERHASIL
            </span>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Nominal yang Dicairkan</div>
                <div class="text-success fw-bold fs-3">
                  Rp <?= number_format((float)($payment['amount_paid'] ?? $payment['amount']), 0, ',', '.') ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Waktu Transaksi Transfer</div>
                <div class="fw-bold text-dark fs-5">
                  <i class="far fa-calendar-alt text-muted me-1"></i>
                  <?= !empty($payment['paid_at']) ? date('d M Y, H:i', strtotime($payment['paid_at'])) : '-' ?> WIB
                </div>
              </div>

              <div class="col-12"><hr class="my-2"></div>

              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Metode Pengiriman</div>
                <div class="fw-semibold text-dark">
                  <i class="fas fa-university text-primary me-1"></i> <?= esc($payment['payment_method'] ?? 'Bank Transfer') ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Nomor Referensi Bank / TRX ID</div>
                <div class="fw-bold font-monospace text-dark">
                  <?= esc($payment['bank_reference'] ?: '-') ?>
                </div>
              </div>

              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Dicatat Oleh (Staff/Finance)</div>
                <div class="text-muted">
                  <i class="fas fa-user-check text-success me-1"></i> <?= esc($payment['payer_name'] ?: 'Staff Finance') ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Waktu Input Sistem</div>
                <div class="text-muted small">
                  <?= !empty($payment['created_at']) ? date('d M Y, H:i:s', strtotime($payment['created_at'])) : '-' ?>
                </div>
              </div>

              <div class="col-12">
                <div class="text-muted small text-uppercase fw-bold mb-1">Catatan Transaksi</div>
                <div class="p-3 bg-light rounded-2 text-dark small">
                  <?= !empty($payment['notes']) ? nl2br(esc($payment['notes'])) : '<em class="text-muted">Tidak ada catatan khusus.</em>' ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Tagihan Invoice Terkait -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Dokumen Tagihan Invoice Terkait</h5>
            <?php if (!empty($payment['invoice_id'])): ?>
              <a href="<?= base_url('invoices/view/' . $payment['invoice_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
                <i class="fas fa-external-link-alt me-1"></i> Buka Detail Invoice
              </a>
            <?php endif; ?>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Nomor Tagihan Invoice</div>
                <div class="fw-bold font-monospace text-dark fs-6">
                  <?= esc($payment['invoice_number'] ?? '-') ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Status Pembayaran Invoice</div>
                <div>
                  <?php
                    $invStatus = strtoupper($payment['invoice_status'] ?? 'PENDING');
                    $badgeCls = match ($invStatus) {
                        'PAID'      => 'bg-success',
                        'PARTIAL'   => 'bg-warning text-dark',
                        'VERIFIED'  => 'bg-info text-dark',
                        'CANCELLED' => 'bg-danger',
                        default     => 'bg-secondary'
                    };
                  ?>
                  <span class="badge <?= $badgeCls ?> px-2 py-1"><?= $invStatus ?></span>
                </div>
              </div>

              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total Tagihan (Grand Total)</div>
                <div class="fw-bold text-dark">
                  Rp <?= number_format((float)($payment['invoice_grand_total'] ?? 0), 0, ',', '.') ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Jatuh Tempo (Due Date)</div>
                <div class="text-dark">
                  <?= !empty($payment['due_date']) ? date('d M Y', strtotime($payment['due_date'])) : '-' ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Profil Penerima & Lampiran Bukti Transfer -->
      <div class="col-lg-4">
        <!-- Card 3: Profil Penerima Dana -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-user-circle text-info me-2"></i>Penerima Dana (KOL)</h5>
          </div>
          <div class="card-body p-3">
            <div class="d-flex align-items-center mb-3">
              <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-weight: 700; font-size: 1.2rem;">
                <?= strtoupper(substr($payment['kol_name'] ?? 'K', 0, 1)) ?>
              </div>
              <div>
                <h6 class="mb-0 fw-bold text-dark"><?= esc($payment['kol_name'] ?? 'Talent KOL') ?></h6>
                <small class="text-muted"><?= esc($payment['campaign_title'] ?? 'Kampanye') ?></small>
              </div>
            </div>

            <div class="bg-light p-3 rounded-2 border">
              <div class="text-muted small text-uppercase fw-bold mb-1">Rekening Tujuan:</div>
              <div class="fw-bold text-dark"><?= esc($payment['bank_name'] ?: '-') ?></div>
              <div class="fw-bold font-monospace text-primary fs-6 mt-1"><?= esc($payment['bank_account'] ?: '-') ?></div>
              <div class="small text-muted">a/n <?= esc($payment['bank_holder'] ?: '-') ?></div>
            </div>
          </div>
        </div>

        <!-- Card 4: Lampiran Bukti Transfer -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-paperclip text-secondary me-2"></i>Bukti Transfer</h5>
          </div>
          <div class="card-body p-3 text-center">
            <?php
              $file = $payment['transfer_receipt_file'] ?? $payment['payment_proof_file'] ?? null;
            ?>
            <?php if (!empty($file)): ?>
              <?php
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
              ?>
              <?php if ($isImage): ?>
                <a href="<?= base_url($file) ?>" target="_blank" title="Klik untuk memperbesar">
                  <img src="<?= base_url($file) ?>" alt="Bukti Transfer" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 260px; object-fit: contain;">
                </a>
              <?php else: ?>
                <div class="p-4 bg-light rounded text-center mb-2">
                  <i class="fas fa-file-pdf text-danger fa-3x mb-2"></i>
                  <div class="small fw-bold text-dark"><?= esc(basename($file)) ?></div>
                </div>
              <?php endif; ?>

              <div class="d-grid mt-2">
                <a href="<?= base_url($file) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-2">
                  <i class="fas fa-download me-1"></i> Unduh / Buka File Bukti
                </a>
              </div>
            <?php else: ?>
              <div class="py-4 text-muted">
                <i class="fas fa-image fa-3x mb-2 text-black-50"></i>
                <p class="small mb-0">Belum ada file bukti transfer yang dilampirkan.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
