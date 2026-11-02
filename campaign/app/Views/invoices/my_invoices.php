<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Tagihan Saya (KOL Invoices)</h3>
        <p class="text-muted small mb-0">Daftar invoice honorarium kerja sama KOL yang diajukan ke brand dan status pencairan dana.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tagihan Saya</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <?php if (!empty($unlinked_kol)): ?>
      <div class="alert alert-warning border-0 shadow-sm rounded-3">
        <h5 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Akun Belum Terhubung ke Data Talent KOL</h5>
        <p class="mb-0">Hubungi tim admin/operasional agar profil akun Anda dihubungkan ke profil KOL untuk menerbitkan dan melihat rincian tagihan invoice.</p>
      </div>
    <?php else: ?>

    <?php
      $totalInvoices  = count($invoices);
      $totalNominal   = 0.0;
      $countSubmitted = 0;
      $countApproved  = 0;
      $countPaid      = 0;
      $totalPaidAll   = 0.0;

      foreach ($invoices as $inv) {
          $grandTotal = (float)($inv['grand_total'] ?? 0);
          $st = strtoupper((string)($inv['status'] ?? ''));
          $totalNominal += $grandTotal;

          if (in_array($st, ['SUBMITTED', 'DRAFT', 'UNPAID'], true)) {
              $countSubmitted++;
          } elseif (in_array($st, ['VERIFIED', 'APPROVED', 'PARTIAL'], true)) {
              $countApproved++;
          } elseif ($st === 'PAID') {
              $countPaid++;
              $totalPaidAll += $grandTotal;
          }
      }
    ?>

    <!-- KPI Finance Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-receipt fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Tagihan</div>
              <h5 class="mb-0 fw-bold text-dark"><?= $totalInvoices ?> Invoice</h5>
              <small class="text-muted">Rp <?= number_format($totalNominal, 0, ',', '.') ?></small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="far fa-clock fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Review Pajak Brand</div>
              <h5 class="mb-0 fw-bold text-warning"><?= $countSubmitted ?> Invoice</h5>
              <small class="text-muted">Menunggu verifikasi</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
              <i class="fas fa-money-check-alt fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Siap Dicairkan</div>
              <h5 class="mb-0 fw-bold text-info"><?= $countApproved ?> Invoice</h5>
              <small class="text-muted">Pajak terverifikasi</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-check-double fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Lunas Terbayar</div>
              <h5 class="mb-0 fw-bold text-success"><?= $countPaid ?> Invoice</h5>
              <small class="text-success fw-semibold">Rp <?= number_format($totalPaidAll, 0, ',', '.') ?></small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
          <i class="fas fa-money-bill-wave text-success me-2"></i>Daftar Tagihan &amp; Invoice Honorarium
        </h5>
        <div class="d-flex gap-2">
          <a href="<?= base_url('invoices/export') ?>" class="btn btn-outline-success btn-sm rounded-2">
            <i class="fas fa-file-excel me-1"></i> Export Excel
          </a>
        </div>
      </div>

      <div class="card-body p-0">
        <?php if (!empty($invoices)): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 40px;" class="text-center">#</th>
                  <th>No. Invoice &amp; Tanggal</th>
                  <th>Kampanye &amp; Brand</th>
                  <th>No. MoU / Kontrak</th>
                  <th>Rincian Honorarium</th>
                  <th>Status Tagihan</th>
                  <th>Bukti Potong Pajak</th>
                  <th style="width: 140px;" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($invoices as $inv): ?>
                  <?php
                    $grandTotal = (float)($inv['grand_total'] ?? 0);
                    $totalPaid  = (float)($inv['total_paid'] ?? 0);
                    $pctPaid    = $grandTotal > 0 ? min(100, round(($totalPaid / $grandTotal) * 100)) : 0;
                    $st         = strtoupper((string)($inv['status'] ?? ''));
                  ?>
                  <tr>
                    <td class="text-center"><?= $no++ ?></td>

                    <!-- No Invoice & Tanggal -->
                    <td>
                      <div class="font-monospace fw-bold text-primary fs-6">
                        <?= esc($inv['invoice_number'] ?? $inv['invoice_no']) ?>
                      </div>
                      <small class="text-muted d-block">
                        Terbit: <?= !empty($inv['created_at']) ? date('d M Y', strtotime($inv['created_at'])) : '-' ?>
                      </small>
                      <?php if (!empty($inv['due_date'])): ?>
                        <small class="text-secondary">
                          Jatuh Tempo: <?= date('d M Y', strtotime($inv['due_date'])) ?>
                        </small>
                      <?php endif; ?>
                    </td>

                    <!-- Kampanye & Brand -->
                    <td>
                      <div class="fw-bold text-dark"><?= esc($inv['campaign_title'] ?: ($inv['campaign_name'] ?? '-')) ?></div>
                      <?php if (!empty($inv['brand_name'])): ?>
                        <small class="text-muted">Brand: <b><?= esc($inv['brand_name']) ?></b></small>
                      <?php endif; ?>
                    </td>

                    <!-- No Kontrak -->
                    <td>
                      <?php if (!empty($inv['contract_no'])): ?>
                        <div class="font-monospace fw-semibold text-primary small">
                          <?= esc($inv['contract_no']) ?>
                        </div>
                      <?php else: ?>
                        <span class="text-muted small">-</span>
                      <?php endif; ?>
                    </td>

                    <!-- Rincian Honorarium -->
                    <td>
                      <div class="small text-muted">
                        Deal Bruto: <strong>Rp <?= number_format((float)$inv['subtotal'], 0, ',', '.') ?></strong>
                      </div>
                      <?php if ((float)$inv['tax_amount'] > 0): ?>
                        <div class="small text-danger">
                          Pot. <?= esc($inv['tax_type'] ?: 'PPh') ?>: -Rp <?= number_format((float)$inv['tax_amount'], 0, ',', '.') ?>
                        </div>
                      <?php endif; ?>
                      <div class="fw-bold text-success mt-1">
                        Net Diterima: Rp <?= number_format($grandTotal, 0, ',', '.') ?>
                      </div>
                    </td>

                    <!-- Status Tagihan -->
                    <td>
                      <?php if ($st === 'PAID'): ?>
                        <span class="badge bg-success px-2 py-1"><i class="fas fa-check-double me-1"></i>LUNAS</span>
                      <?php elseif ($st === 'PARTIAL'): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-adjust me-1"></i>SEBAGIAN (<?= $pctPaid ?>%)</span>
                      <?php elseif (in_array($st, ['VERIFIED', 'APPROVED'], true)): ?>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-check me-1"></i>SIAP CAIR</span>
                      <?php elseif ($st === 'SUBMITTED'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>REVIEW PAJAK</span>
                      <?php elseif ($st === 'REJECTED'): ?>
                        <span class="badge bg-danger px-2 py-1"><i class="fas fa-times me-1"></i>DITOLAK</span>
                      <?php else: ?>
                        <span class="badge bg-secondary px-2 py-1"><?= esc($st) ?></span>
                      <?php endif; ?>

                      <?php if ($totalPaid > 0): ?>
                        <div class="small text-muted mt-1">
                          Terbayar: <b>Rp <?= number_format($totalPaid, 0, ',', '.') ?></b>
                        </div>
                      <?php endif; ?>
                    </td>

                    <!-- Bukti Potong Pajak -->
                    <td>
                      <?php if (!empty($inv['bupot_file'])): ?>
                        <a href="<?= base_url($inv['bupot_file']) ?>" target="_blank" class="btn btn-warning btn-xs rounded-2 fw-semibold" title="Unduh Bukti Potong Pajak">
                          <i class="fas fa-certificate me-1"></i> Unduh Bupot
                        </a>
                        <?php if (!empty($inv['bupot_no'])): ?>
                          <div class="small font-monospace text-muted mt-1"><?= esc($inv['bupot_no']) ?></div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-muted small fst-italic">Belum diterbitkan</span>
                      <?php endif; ?>
                    </td>

                    <!-- Aksi -->
                    <td class="text-center">
                      <div class="d-flex gap-1 justify-content-center">
                        <a href="<?= base_url('invoices/print-pdf/' . $inv['id']) ?>" target="_blank" class="btn btn-primary btn-sm rounded-2 py-1 px-2" title="Cetak / Simpan PDF Invoice">
                          <i class="fas fa-print me-1"></i> PDF
                        </a>
                        <a href="<?= base_url('invoices/view/' . $inv['id']) ?>" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Lihat Rincian">
                          <i class="fas fa-eye"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50"></i>
            <h6 class="fw-bold">Belum Ada Tagihan yang Diterbitkan</h6>
            <p class="small text-muted">Tagihan invoice akan muncul otomatis saat Anda terpilih dalam kampanye dan kontrak kerja sama disetujui.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php endif; ?>

  </div>
</div>
<?= $this->endSection() ?>
