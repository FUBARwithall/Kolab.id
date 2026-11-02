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
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small text-uppercase fw-semibold">Skor Kerjasama</div>
                  <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold" onclick="openEvaluateScoreModal()">
                    <i class="fas fa-edit me-1"></i><?= !empty($campaign_kol['collaboration_score']) ? 'Edit' : 'Input' ?>
                  </button>
                </div>
                <div class="d-flex align-items-center gap-2 mt-1">
                  <?php if (!empty($campaign_kol['collaboration_score'])): ?>
                    <?php 
                      $sc = (float)$campaign_kol['collaboration_score'];
                      $scBg = $sc >= 85 ? 'success' : ($sc >= 70 ? 'primary' : ($sc >= 55 ? 'warning text-dark' : 'danger'));
                      $scGrade = $sc >= 85 ? 'A' : ($sc >= 70 ? 'B' : ($sc >= 55 ? 'C' : 'D'));
                    ?>
                    <span class="badge bg-<?= $scBg ?> fs-6 px-2 py-1 fw-bold"><?= number_format($sc, 2) ?></span>
                    <span class="badge bg-light text-dark border">Grade <?= $scGrade ?></span>
                  <?php else: ?>
                    <span class="text-muted fst-italic">Belum dinilai</span>
                  <?php endif; ?>
                </div>
                <div class="text-muted small mt-1">Evaluasi performa dealing</div>
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

        <!-- Score Histories Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
              <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-star-half-alt text-warning me-2"></i>Riwayat Skor &amp; Evaluasi Performa KOL</h6>
              <small class="text-muted">Audit trail riwayat penilaian kerjasama kampanye dan histori lifetime score talent</small>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-3" onclick="openEvaluateScoreModal()">
              <i class="fas fa-plus-circle me-1"></i> Beri Penilaian
            </button>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase text-secondary small">
                  <tr>
                    <th width="40" class="text-center">#</th>
                    <th>Tipe Evaluasi</th>
                    <th>Konteks Kampanye</th>
                    <th>Nilai Skor</th>
                    <th>Waktu Perhitungan</th>
                    <th width="120" class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody id="tbodyScoreHistories">
                  <?php if (empty($campaign_kol['score_histories'])): ?>
                    <tr>
                      <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fas fa-clipboard-list fa-2x text-secondary opacity-50 mb-2 d-block"></i>
                        Belum ada riwayat skor untuk dealing ini.
                        <div class="mt-2">
                          <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEvaluateScoreModal()">
                            <i class="fas fa-star me-1"></i> Input Nilai Sekarang
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($campaign_kol['score_histories'] as $idx => $sh): ?>
                      <?php
                        $scVal = (float)$sh['score'];
                        $isCollab = ($sh['score_type'] === 'COLLABORATION');
                        $scBg = $scVal >= 85 ? 'success' : ($scVal >= 70 ? 'primary' : ($scVal >= 55 ? 'warning text-dark' : 'danger'));
                        $grade = $scVal >= 85 ? 'Grade A' : ($scVal >= 70 ? 'Grade B' : ($scVal >= 55 ? 'Grade C' : 'Grade D'));
                      ?>
                      <tr>
                        <td class="text-center fw-bold text-muted"><?= $idx + 1 ?></td>
                        <td>
                          <?php if ($isCollab): ?>
                            <span class="badge bg-info text-white font-monospace"><i class="fas fa-handshake me-1"></i>COLLABORATION</span>
                          <?php else: ?>
                            <span class="badge text-white font-monospace" style="background-color: #6f42c1;"><i class="fas fa-chart-line me-1"></i>LIFETIME</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($isCollab && !empty($sh['campaign_title'])): ?>
                            <div class="fw-semibold text-dark small"><i class="fas fa-bullhorn text-primary me-1"></i><?= esc($sh['campaign_title']) ?></div>
                            <small class="text-muted">Dealing ID #<?= $campaign_kol['id'] ?></small>
                          <?php else: ?>
                            <span class="badge bg-light text-secondary border"><i class="fas fa-globe me-1"></i>Overall / Lifetime Rating</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-<?= $scBg ?> fs-6 px-2 py-1 fw-bold"><?= number_format($scVal, 2) ?></span>
                            <span class="badge bg-light text-dark border"><?= $grade ?></span>
                          </div>
                          <div class="progress mt-1" style="height: 4px; width: 110px;">
                            <div class="progress-bar bg-<?= str_contains($scBg, 'warning') ? 'warning' : ($scBg === 'danger' ? 'danger' : ($scBg === 'primary' ? 'primary' : 'success')) ?>" role="progressbar" style="width: <?= min(100, max(0, $scVal)) ?>%;"></div>
                          </div>
                        </td>
                        <td class="text-muted small">
                          <?= date('d M Y, H:i', strtotime($sh['calculated_at'])) ?> WIB
                        </td>
                        <td class="text-center">
                          <div class="btn-group btn-group-sm">
                            <a href="<?= base_url('score-histories/view/' . $sh['id']) ?>" class="btn btn-outline-primary" title="Lihat Detail Riwayat">
                              <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="<?= base_url('score-histories/single-pdf/' . $sh['id']) ?>" target="_blank" class="btn btn-outline-secondary" title="Cetak PDF">
                              <i class="fas fa-file-pdf"></i>
                            </a>
                          </div>
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

<!-- Modal Evaluasi Skor KOL -->
<div class="modal fade" id="modalEvaluateScore" tabindex="-1" aria-labelledby="modalEvaluateScoreLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold" id="modalEvaluateScoreLabel">
          <i class="fas fa-star text-warning me-2"></i>Evaluasi Skor Performa KOL
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEvaluateScore" onsubmit="submitScoreEvaluation(event)">
        <div class="modal-body p-4">
          <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                <?= strtoupper(substr(esc($campaign_kol['kol_name'] ?? 'K'), 0, 1)) ?>
              </div>
              <div>
                <div class="fw-bold text-dark"><?= esc($campaign_kol['kol_name'] ?? '-') ?></div>
                <small class="text-muted"><i class="fas fa-bullhorn text-primary me-1"></i><?= esc($campaign_kol['campaign_title'] ?? '-') ?></small>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-uppercase text-muted">Tipe Evaluasi:</label>
            <select class="form-select" name="score_type" id="inputScoreType" required>
              <option value="COLLABORATION" selected>COLLABORATION (Skor Evaluasi Kerjasama Kampanye Ini)</option>
              <option value="LIFETIME">LIFETIME (Akumulasi Rating Keseluruhan KOL)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs fw-bold text-uppercase text-muted">Nilai Skor (0.00 - 100.00):</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-award text-warning"></i></span>
              <input type="number" class="form-control form-control-lg fw-bold" name="score" id="inputScoreVal" min="0" max="100" step="0.01" placeholder="85.00" value="<?= !empty($campaign_kol['collaboration_score']) ? number_format((float)$campaign_kol['collaboration_score'], 2, '.', '') : '85.00' ?>" required>
              <span class="input-group-text bg-light text-muted">/ 100</span>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-outline-success btn-xs px-2 py-1 rounded" onclick="$('#inputScoreVal').val('95.00')">95 (A)</button>
              <button type="button" class="btn btn-outline-primary btn-xs px-2 py-1 rounded" onclick="$('#inputScoreVal').val('85.00')">85 (B)</button>
              <button type="button" class="btn btn-outline-warning btn-xs px-2 py-1 rounded text-dark" onclick="$('#inputScoreVal').val('70.00')">70 (C)</button>
              <button type="button" class="btn btn-outline-danger btn-xs px-2 py-1 rounded" onclick="$('#inputScoreVal').val('50.00')">50 (D)</button>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-top py-2">
          <button type="button" class="btn btn-secondary btn-sm rounded-2 px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" id="btnSubmitScore">
            <i class="fas fa-check me-1"></i> Simpan Skor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function openEvaluateScoreModal() {
  const modal = new bootstrap.Modal(document.getElementById('modalEvaluateScore'));
  modal.show();
}

function submitScoreEvaluation(e) {
  e.preventDefault();
  const btn = $('#btnSubmitScore');
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

  const scoreVal = $('#inputScoreVal').val();
  const scoreType = $('#inputScoreType').val();

  $.ajax({
    url: '<?= base_url('campaign-kols/save-score/' . $campaign_kol['id']) ?>',
    type: 'POST',
    data: {
      score: scoreVal,
      score_type: scoreType,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire({
          title: 'Berhasil!',
          text: res.message,
          icon: 'success',
          confirmButtonText: 'OK'
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Simpan Skor');
      }
    },
    error: function(xhr) {
      const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
      Swal.fire('Error!', err, 'error');
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Simpan Skor');
    }
  });
}
</script>
<?= $this->endSection() ?>
