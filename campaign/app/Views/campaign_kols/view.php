<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-7">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-handshake text-primary me-2"></i>Dealing KOL #<?= $campaign_kol['id'] ?>
        </h3>
        <p class="text-muted small mb-0">
          Campaign: <strong class="text-dark"><?= esc($campaign_kol['campaign_title'] ?? '-') ?></strong> &bull; 
          Talent: <strong class="text-primary"><?= esc($campaign_kol['kol_name'] ?? '-') ?></strong> (<?= esc($campaign_kol['tier_name'] ?? 'Nano') ?>)
        </p>
      </div>
      <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('campaign-kols') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="<?= site_url('campaign-kols/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-file-csv me-1"></i> Export
        </a>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <div class="row g-4">
      <!-- Left Column: Deal Info & Tasks List -->
      <div class="col-lg-7">

        <!-- Deal Specs Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Rincian Kesepakatan (Deal Specifications)</h6>
            <?php
              $st = $campaign_kol['status'] ?? 'ASSIGNED';
              $badgeClass = match ($st) {
                'ACTIVE'      => 'bg-success',
                'ASSIGNED'    => 'bg-info text-dark',
                'NEGOTIATION' => 'bg-warning text-dark',
                'COMPLETED'   => 'bg-primary',
                'REJECTED'    => 'bg-danger',
                default       => 'bg-secondary',
              };
            ?>
            <span class="badge <?= $badgeClass ?> px-3 py-2 fw-semibold fs-7"><?= $st ?></span>
          </div>

          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Campaign Terkait</div>
                <div class="fw-bold fs-6 text-dark mt-1"><?= esc($campaign_kol['campaign_title'] ?? '-') ?></div>
                <div class="text-muted small">Budget Campaign: Rp <?= number_format($campaign_kol['campaign_budget'] ?? 0, 0, ',', '.') ?></div>
              </div>

              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Talent KOL</div>
                <div class="fw-bold fs-6 text-primary mt-1">
                  <?= esc($campaign_kol['kol_name'] ?? '-') ?>
                  <span class="badge bg-secondary-subtle text-secondary border ms-1"><?= esc($campaign_kol['tier_name'] ?? 'Nano') ?></span>
                </div>
                <div class="text-muted small"><?= !empty($campaign_kol['kol_whatsapp']) ? ('WA: ' . esc($campaign_kol['kol_whatsapp'])) : 'Kota: ' . esc($campaign_kol['kol_city'] ?? '-') ?></div>
              </div>

              <div class="col-sm-4">
                <div class="text-muted small text-uppercase fw-semibold">Nilai Deal (Fee)</div>
                <div class="fw-bold fs-5 text-success mt-1">
                  Rp <?= number_format($campaign_kol['deal_amount'] ?? 0, 0, ',', '.') ?>
                </div>
                <div class="text-muted small">Mata Uang: <?= esc($campaign_kol['currency_code'] ?? 'IDR') ?></div>
              </div>

              <div class="col-sm-4">
                <div class="text-muted small text-uppercase fw-semibold">Target Konten Video</div>
                <div class="fw-bold fs-5 text-dark mt-1">
                  <?= (int)($campaign_kol['jumlah_vt'] ?? 0) ?> VT
                </div>
                <div class="text-muted small">Total Tasks: <?= (int)($campaign_kol['task_count'] ?? 0) ?></div>
              </div>

              <div class="col-sm-4">
                <div class="text-muted small text-uppercase fw-semibold">Skor Kerjasama</div>
                <div class="fw-bold fs-5 text-dark mt-1">
                  <?= $campaign_kol['collaboration_score'] ? number_format((float)$campaign_kol['collaboration_score'], 2) : '-' ?>
                </div>
                <div class="text-muted small">Evaluasi Leader</div>
              </div>

              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Tanggal Deal</div>
                <div class="fw-semibold text-dark mt-1">
                  <?= $campaign_kol['deal_date'] ? date('d F Y', strtotime($campaign_kol['deal_date'])) : '-' ?>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="text-muted small text-uppercase fw-semibold">Ditugaskan Oleh</div>
                <div class="fw-semibold text-dark mt-1">
                  <?= esc($campaign_kol['assigned_by_name'] ?? 'Sistem') ?>
                  <span class="text-muted small ms-1">(<?= $campaign_kol['assigned_at'] ? date('d M Y H:i', strtotime($campaign_kol['assigned_at'])) : '-' ?>)</span>
                </div>
              </div>

              <?php if (!empty($campaign_kol['rejection_reason'])): ?>
                <div class="col-12">
                  <div class="alert alert-danger mb-0 rounded-2 p-3">
                    <strong class="d-block mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Alasan Penolakan:</strong>
                    <?= nl2br(esc($campaign_kol['rejection_reason'])) ?>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($campaign_kol['deal_scope'])): ?>
                <div class="col-12 mt-3">
                  <div class="text-muted small text-uppercase fw-semibold mb-2">Dokumen Brief / SOW</div>
                  <div class="d-flex flex-wrap gap-2">
                    <?php foreach (explode(',', $campaign_kol['deal_scope']) as $fn): $fn = trim($fn); if (!$fn) continue; ?>
                      <a href="<?= base_url('uploads/campaign_kols/' . $fn) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-paperclip me-1"></i> <?= esc($fn) ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Tasks Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks text-primary me-2"></i>Daftar Tugas Konten Video (Tasks VT)</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
              <?= count($campaign_kol['tasks'] ?? []) ?> Tasks Terdaftar
            </span>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase text-secondary small">
                  <tr>
                    <th width="40" class="text-center">#</th>
                    <th>Judul Task</th>
                    <th>Platform</th>
                    <th>Content Type</th>
                    <th class="text-end">Budget VT</th>
                    <th>Status</th>
                    <th>Est. Posting</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($campaign_kol['tasks'])): ?>
                    <tr>
                      <td colspan="7" class="text-center py-4 text-muted">Belum ada tugas konten video yang dibuat.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($campaign_kol['tasks'] as $t): ?>
                      <tr>
                        <td class="text-center fw-bold text-muted"><?= $t['task_number'] ?></td>
                        <td>
                          <div class="fw-semibold text-dark"><?= esc($t['title']) ?></div>
                          <?php if (!empty($t['notes'])): ?>
                            <small class="text-muted"><?= esc($t['notes']) ?></small>
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="badge bg-light text-dark border">
                            <i class="<?= esc($t['platform_icon'] ?? 'fas fa-share-alt') ?> me-1"></i>
                            <?= esc($t['platform_name'] ?? 'TikTok') ?>
                          </span>
                        </td>
                        <td>
                          <span class="badge bg-secondary-subtle text-secondary border"><?= esc($t['content_type']) ?></span>
                        </td>
                        <td class="text-end fw-bold text-dark">
                          Rp <?= number_format($t['budget'] ?? 0, 0, ',', '.') ?>
                        </td>
                        <td>
                          <span class="badge bg-info-subtle text-info border"><?= esc($t['status'] ?? 'DRAFT') ?></span>
                        </td>
                        <td class="text-muted small">
                          <?= $t['estimated_posting_date'] ? date('d M Y', strtotime($t['estimated_posting_date'])) : '-' ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Shipping & Contracts -->
      <div class="col-lg-5">

        <!-- Shipping Sample Product Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-shipping-fast text-success me-2"></i>Pengiriman Sampel Produk</h6>
            <?php
              $shipStatus = $campaign_kol['shipment']['status'] ?? 'NOT_SENT';
              $shBadge = match ($shipStatus) {
                'SHIPPED'   => 'bg-info',
                'DELIVERED' => 'bg-success',
                'RETURNED'  => 'bg-danger',
                default     => 'bg-secondary',
              };
            ?>
            <span class="badge <?= $shBadge ?> px-3 py-1"><?= $shipStatus ?></span>
          </div>

          <div class="card-body p-4">
            <?php if (empty($campaign_kol['shipment'])): ?>
              <div class="text-center py-4 text-muted">
                <i class="fas fa-box-open fa-3x text-secondary opacity-50 mb-3 d-block"></i>
                Belum ada data pengiriman sampel untuk dealing ini.
              </div>
            <?php else: $sh = $campaign_kol['shipment']; ?>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <div class="text-muted small text-uppercase">Penerima</div>
                  <div class="fw-bold text-dark"><?= esc($sh['recipient_name'] ?? '-') ?></div>
                  <small class="text-muted"><?= esc($sh['recipient_phone'] ?? '-') ?></small>
                </div>
                <div class="col-6">
                  <div class="text-muted small text-uppercase">Kurir &amp; Resi</div>
                  <div class="fw-bold text-dark"><?= esc($sh['courier'] ?? '-') ?></div>
                  <div class="fw-bold text-primary small"><i class="fas fa-barcode me-1"></i><?= esc($sh['tracking_no'] ?? 'Belum ada resi') ?></div>
                </div>
                <div class="col-12 mt-2">
                  <div class="text-muted small text-uppercase">Alamat Pengiriman</div>
                  <div class="small text-dark"><?= nl2br(esc($sh['shipping_address'] ?? '-')) ?></div>
                </div>
                <div class="col-6 mt-2">
                  <div class="text-muted small text-uppercase">Ongkir</div>
                  <div class="fw-bold text-dark">Rp <?= number_format($sh['shipping_fee'] ?? 0, 0, ',', '.') ?></div>
                </div>
              </div>

              <!-- Shipment Items -->
              <h6 class="fw-bold text-dark small text-uppercase mt-3 mb-2">Item Produk yang Dikirim:</h6>
              <div class="list-group list-group-flush border rounded-2">
                <?php if (empty($sh['items'])): ?>
                  <div class="list-group-item text-muted small py-2">Tidak ada item sampel tercatat.</div>
                <?php else: ?>
                  <?php foreach ($sh['items'] as $item): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                      <div>
                        <div class="fw-semibold text-dark small"><?= esc($item['product_name'] ?? 'Produk') ?></div>
                        <small class="text-muted">SKU: <?= esc($item['product_sku'] ?? '-') ?></small>
                      </div>
                      <span class="badge bg-primary rounded-pill"><?= (int)$item['qty'] ?> pcs</span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Contracts Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-contract text-primary me-2"></i>Kontrak Kerjasama (MoU)</h6>
            <a href="<?= site_url('contracts') ?>" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">Lihat Semua</a>
          </div>

          <div class="card-body p-4">
            <?php if (empty($campaign_kol['contracts'])): ?>
              <div class="text-center py-3 text-muted small">
                Belum ada kontrak yang diterbitkan untuk dealing ini. Kontrak dibuat otomatis saat status menjadi DEAL (ASSIGNED/ACTIVE).
              </div>
            <?php else: ?>
              <?php foreach ($campaign_kol['contracts'] as $ctr): ?>
                <div class="border rounded-3 p-3 mb-2 bg-light d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-bold text-dark"><i class="fas fa-file-alt text-primary me-2"></i><?= esc($ctr['contract_no'] ?? $ctr['contract_number'] ?? 'CTR') ?></div>
                    <small class="text-muted">Dibuat: <?= date('d M Y', strtotime($ctr['created_at'])) ?></small>
                  </div>
                  <div>
                    <span class="badge bg-info-subtle text-info border px-2 py-1"><?= esc($ctr['status'] ?? 'DRAFT') ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Invoices Card -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Invoice / Tagihan KOL</h6>
            <a href="<?= site_url('invoices') ?>" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">Lihat Semua</a>
          </div>

          <div class="card-body p-4">
            <?php if (empty($campaign_kol['invoices'])): ?>
              <div class="text-center py-3 text-muted small">
                Belum ada tagihan/invoice yang diterbitkan. Invoice diterbitkan oleh pihak KOL melalui Portal KOL setelah MoU ditandatangani.
              </div>
            <?php else: ?>
              <?php foreach ($campaign_kol['invoices'] as $inv): ?>
                <div class="border rounded-3 p-3 mb-2 bg-light d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-bold text-dark"><i class="fas fa-receipt text-success me-2"></i><?= esc($inv['invoice_number'] ?? 'INV') ?></div>
                    <div class="fw-bold text-success small mt-1">Rp <?= number_format($inv['amount'] ?? 0, 0, ',', '.') ?></div>
                  </div>
                  <div>
                    <span class="badge bg-success-subtle text-success border px-2 py-1"><?= esc($inv['status'] ?? 'UNPAID') ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
