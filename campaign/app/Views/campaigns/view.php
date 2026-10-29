<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

  <!-- 1. Campaign Header Banner -->
  <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
    <div class="card-body p-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary text-uppercase px-2 py-1" style="font-size: 11px;">KAMPANYE ID #<?= $campaign['id'] ?></span>
            <?php
              $statusBadge = match (strtoupper($campaign['status'])) {
                  'ACTIVE'    => '<span class="badge bg-success px-2 py-1"><i class="fas fa-play me-1"></i>AKTIF</span>',
                  'COMPLETED' => '<span class="badge bg-secondary px-2 py-1"><i class="fas fa-check me-1"></i>SELESAI</span>',
                  'CANCELLED' => '<span class="badge bg-danger px-2 py-1"><i class="fas fa-ban me-1"></i>DIBATALKAN</span>',
                  default     => '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-edit me-1"></i>DRAFT</span>',
              };
              echo $statusBadge;
            ?>
            <?php if ($campaign['is_open_registration'] == 1): ?>
              <span class="badge bg-info-subtle text-info border border-info px-2 py-1"><i class="fas fa-bullhorn me-1"></i>Pendaftaran Terbuka (Open Campaign)</span>
            <?php endif; ?>
          </div>
          <h3 class="fw-bold text-dark mb-1"><?= esc($campaign['title']) ?></h3>
          <p class="text-muted small mb-0"><i class="far fa-calendar-alt text-primary me-1"></i> Periode: <b><?= date('d M Y', strtotime($campaign['start_date'])) ?></b> s/d <b><?= date('d M Y', strtotime($campaign['end_date'])) ?></b></p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= base_url('campaigns') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
          </a>
          <button type="button" class="btn btn-primary btn-sm rounded-2 shadow-sm" onclick="openAssignKolModal()">
            <i class="fas fa-user-plus me-1"></i> Daftarkan KOL
          </button>
        </div>
      </div>

      <!-- Budget Progress Bar -->
      <div class="mt-4 pt-3 border-top">
        <div class="row align-items-center g-3">
          <div class="col-md-4">
            <div class="text-muted small">Total Anggaran</div>
            <div class="fs-5 fw-bold text-dark">Rp <?= number_format((float)$campaign['budget_total'], 0, ',', '.') ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Alokasi Deal KOL Terdaftar</div>
            <div class="fs-5 fw-bold text-primary">Rp <?= number_format((float)$campaign['total_spent'], 0, ',', '.') ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Sisa Anggaran Tersedia</div>
            <div class="fs-5 fw-bold <?= $campaign['remaining_budget'] < 0 ? 'text-danger' : 'text-success' ?>">
              Rp <?= number_format((float)$campaign['remaining_budget'], 0, ',', '.') ?>
            </div>
          </div>
        </div>
        <?php
          $budget = (float)$campaign['budget_total'];
          $spent = (float)$campaign['total_spent'];
          $pct = $budget > 0 ? min(100, round(($spent / $budget) * 100, 1)) : 0;
          $barColor = $pct > 90 ? 'bg-danger' : ($pct > 70 ? 'bg-warning' : 'bg-primary');
        ?>
        <div class="progress mt-3" style="height: 8px;">
          <div class="progress-bar <?= $barColor ?>" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mt-1">
          <span>Persentase Serapan: <b><?= $pct ?>%</b></span>
          <span>Target Total Views: <b><?= number_format((int)$campaign['target_views'], 0, ',', '.') ?></b> views</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Tab Navigation -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white p-0 border-bottom">
      <ul class="nav nav-tabs border-0 px-3 pt-2" id="campaignTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active fw-semibold" id="kols-tab" data-bs-toggle="tab" data-bs-target="#tab-kols" type="button" role="tab" aria-selected="true">
            <i class="fas fa-users text-primary me-2"></i>Talent KOL Terdaftar (<?= count($campaign['kols'] ?? []) ?>)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold" id="applications-tab" data-bs-toggle="tab" data-bs-target="#tab-applications" type="button" role="tab" aria-selected="false">
            <i class="fas fa-paper-plane text-info me-2"></i>Pelamar Terbuka (<?= count($campaign['applications'] ?? []) ?>)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#tab-metrics" type="button" role="tab" aria-selected="false">
            <i class="fas fa-chart-line text-success me-2"></i>Metrik Performa &amp; Analitik
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold" id="products-tab" data-bs-toggle="tab" data-bs-target="#tab-products" type="button" role="tab" aria-selected="false">
            <i class="fas fa-box text-warning me-2"></i>Produk Endorse (<?= count($campaign['products'] ?? []) ?>)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab" aria-selected="false">
            <i class="fas fa-info-circle text-secondary me-2"></i>Deskripsi &amp; Objektif
          </button>
        </li>
      </ul>
    </div>

    <div class="card-body p-4">
      <div class="tab-content" id="campaignTabContent">
        
        <!-- Tab 1: Daftar KOL -->
        <div class="tab-pane fade show active" id="tab-kols" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">Daftar Talent KOL yang Berpartisipasi</h6>
            <button type="button" class="btn btn-sm btn-primary rounded-2" onclick="openAssignKolModal()">
              <i class="fas fa-plus me-1"></i> Tambah KOL
            </button>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle" style="font-size: 13.5px;">
              <thead class="table-light">
                <tr>
                  <th style="width: 40px;">#</th>
                  <th>Nama Talent KOL</th>
                  <th style="width: 140px;">Deal Amount (Fee)</th>
                  <th style="width: 110px;" class="text-center">Target SOW</th>
                  <th style="width: 110px;" class="text-center">Kontrak</th>
                  <th style="width: 110px;" class="text-center">Pembayaran</th>
                  <th style="width: 140px;" class="text-center">Rating &amp; Review</th>
                  <th style="width: 80px;" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($campaign['kols'])): ?>
                  <?php foreach ($campaign['kols'] as $idx => $k): ?>
                    <tr>
                      <td><?= $idx + 1 ?></td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <?= strtoupper(substr($k['kol_name'], 0, 1)) ?>
                          </div>
                          <div>
                            <div class="fw-bold text-dark"><?= esc($k['kol_name']) ?></div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($k['city'] ?? '-') ?> • <?= esc($k['whatsapp'] ?? '-') ?></small>
                          </div>
                        </div>
                      </td>
                      <td class="fw-bold text-success">Rp <?= number_format((float)$k['deal_amount'], 0, ',', '.') ?></td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2" title="Klik untuk lihat rincian tugas VT" onclick="viewKolTasks(<?= $k['id'] ?>, '<?= esc(addslashes($k['kol_name'])) ?>')">
                          <i class="fas fa-video me-1"></i><?= $k['total_vt'] ?> VT
                        </button>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-light text-secondary border"><?= esc($k['contract_status']) ?></span>
                      </td>
                      <td class="text-center">
                        <?php if ($k['payment_status'] === 'PAID'): ?>
                          <span class="badge bg-success">LUNAS</span>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark">BELUM LUNAS</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <?php if (!empty($k['review'])): ?>
                          <div>
                            <span class="text-warning fw-bold fs-6">
                              <?= str_repeat('★', (int)$k['review']['rating']) . str_repeat('☆', 5 - (int)$k['review']['rating']) ?>
                            </span>
                            <small class="d-block text-muted text-xs"><?= esc($k['review']['rating']) ?>/5.0</small>
                          </div>
                          <button type="button" class="btn btn-link btn-xs p-0 text-primary text-decoration-none" onclick="openReviewModal(<?= $k['kol_id'] ?>, <?= $campaign['id'] ?>, '<?= esc(addslashes($k['kol_name'])) ?>', <?= (int)$k['review']['rating'] ?>, '<?= esc(addslashes($k['review']['review_text'] ?? '')) ?>')">
                            <small><i class="fas fa-edit me-1"></i>Edit Review</small>
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-outline-warning btn-xs rounded-pill px-2.5 py-1" onclick="openReviewModal(<?= $k['kol_id'] ?>, <?= $campaign['id'] ?>, '<?= esc(addslashes($k['kol_name'])) ?>', 5, '')">
                            <i class="fas fa-star me-1"></i> Beri Nilai
                          </button>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                          <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Lihat Rincian Tugas VT" onclick="viewKolTasks(<?= $k['id'] ?>, '<?= esc(addslashes($k['kol_name'])) ?>')">
                            <i class="fas fa-tasks"></i>
                          </button>
                          <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Penugasan" onclick="removeAssignedKol(<?= $k['id'] ?>, '<?= esc(addslashes($k['kol_name'])) ?>')">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada Talent KOL yang didaftarkan ke kampanye ini.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab 2: Pelamar Terbuka (Applications) -->
        <div class="tab-pane fade" id="tab-applications" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">Daftar Lamaran Masuk dari Kreator</h6>
            <span class="badge bg-info text-white rounded-pill px-3 py-1"><?= count($campaign['applications'] ?? []) ?> Pelamar</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle" style="font-size: 13.5px;">
              <thead class="table-light">
                <tr>
                  <th style="width: 40px;">#</th>
                  <th>Talent Pelamar</th>
                  <th style="width: 140px;">Tarif Penawaran</th>
                  <th>Ide Konten &amp; Pitching</th>
                  <th style="width: 130px;" class="text-center">Status Seleksi</th>
                  <th style="width: 110px;">Tgl Melamar</th>
                  <th style="width: 160px;" class="text-center">Aksi Seleksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($campaign['applications'])): ?>
                  <?php foreach ($campaign['applications'] as $aIdx => $app): ?>
                    <tr>
                      <td><?= $aIdx + 1 ?></td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="rounded-circle bg-info bg-opacity-10 text-info fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <?= strtoupper(substr($app['kol_name'], 0, 1)) ?>
                          </div>
                          <div>
                            <div class="fw-bold text-dark"><?= esc($app['kol_name']) ?></div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($app['city'] ?? '-') ?> • <?= esc($app['whatsapp'] ?? '-') ?></small>
                          </div>
                        </div>
                      </td>
                      <td class="fw-bold text-success">Rp <?= number_format((float)$app['proposed_rate'], 0, ',', '.') ?></td>
                      <td><small class="text-secondary"><?= esc($app['pitch_notes'] ?? '-') ?></small></td>
                      <td class="text-center">
                        <?php if ($app['status'] === 'ACCEPTED'): ?>
                          <span class="badge bg-success">DITERIMA</span>
                        <?php elseif ($app['status'] === 'REJECTED'): ?>
                          <span class="badge bg-danger">DITOLAK</span>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark">MENUNGGU REVIEW</span>
                        <?php endif; ?>
                      </td>
                      <td><small class="text-muted"><?= date('d M Y', strtotime($app['created_at'])) ?></small></td>
                      <td class="text-center">
                        <?php if ($app['status'] === 'APPLIED'): ?>
                          <button type="button" class="btn btn-success btn-xs rounded-pill px-2.5 py-1 me-1" onclick="openAcceptAppModal(<?= $app['id'] ?>, '<?= esc(addslashes($app['kol_name'])) ?>', <?= (float)$app['proposed_rate'] ?>, '<?= esc(addslashes($app['pitch_notes'] ?? '')) ?>')">
                            <i class="fas fa-check me-1"></i> Terima
                          </button>
                          <button type="button" class="btn btn-outline-danger btn-xs rounded-pill px-2.5 py-1" onclick="rejectApplication(<?= $app['id'] ?>, '<?= esc(addslashes($app['kol_name'])) ?>')">
                            <i class="fas fa-times me-1"></i> Tolak
                          </button>
                        <?php else: ?>
                          <small class="text-muted"><i class="fas fa-check-double text-success me-1"></i>Selesai ditinjau</small>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada pelamar terbuka yang mendaftar pada kampanye ini.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab 3: Metrik & Performa Live -->
        <div class="tab-pane fade" id="tab-metrics" role="tabpanel">
          <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
              <div class="p-3 border rounded-3 bg-light text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Views Aktual</div>
                <h3 class="fw-bold text-primary mb-0 mt-1"><?= number_format((int)$campaign['metrics']['total_views'], 0, ',', '.') ?></h3>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="p-3 border rounded-3 bg-light text-center">
                <div class="text-muted small text-uppercase fw-semibold">Total Likes</div>
                <h3 class="fw-bold text-success mb-0 mt-1"><?= number_format((int)$campaign['metrics']['total_likes'], 0, ',', '.') ?></h3>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="p-3 border rounded-3 bg-light text-center">
                <div class="text-muted small text-uppercase fw-semibold">Comments &amp; Shares</div>
                <h3 class="fw-bold text-info mb-0 mt-1"><?= number_format((int)($campaign['metrics']['total_comments'] + $campaign['metrics']['total_shares']), 0, ',', '.') ?></h3>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="p-3 border rounded-3 bg-light text-center">
                <div class="text-muted small text-uppercase fw-semibold">Postingan Live</div>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format((int)$campaign['metrics']['live_postings_count'], 0, ',', '.') ?></h3>
              </div>
            </div>
          </div>
          
          <!-- Efisiensi Biaya -->
          <?php
            $totViews = (int)$campaign['metrics']['total_views'];
            $totSpent = (float)$campaign['total_spent'];
            $cpv = $totViews > 0 ? round($totSpent / $totViews, 2) : 0;
            $totEngage = (int)$campaign['metrics']['total_likes'] + (int)$campaign['metrics']['total_comments'] + (int)$campaign['metrics']['total_shares'] + (int)$campaign['metrics']['total_saves'];
            $er = $totViews > 0 ? round(($totEngage / $totViews) * 100, 2) : 0;
          ?>
          <div class="p-3 border rounded-3 bg-white">
            <h6 class="fw-bold mb-3"><i class="fas fa-calculator text-primary me-2"></i>Kalkulasi Efisiensi Biaya Kampanye</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="border p-3 rounded-2">
                  <div class="text-muted small">Cost Per View (CPV Aktual)</div>
                  <div class="fs-4 fw-bold text-success">Rp <?= number_format($cpv, 2, ',', '.') ?> <small class="text-muted fs-6">/ view</small></div>
                  <div class="text-secondary small mt-1">Dihitung dari Total Deal KOL / Total Views Live</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border p-3 rounded-2">
                  <div class="text-muted small">Engagement Rate Rata-rata</div>
                  <div class="fs-4 fw-bold text-primary"><?= $er ?>%</div>
                  <div class="text-secondary small mt-1">Rasio Total Reaksi terhadap Total Penayangan Video</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 4: Produk Terkait -->
        <div class="tab-pane fade" id="tab-products" role="tabpanel">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>SKU</th>
                  <th>Nama Produk</th>
                  <th>Harga Satuan</th>
                  <th>Stok Sampel Tersedia</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($campaign['products'])): ?>
                  <?php foreach ($campaign['products'] as $p): ?>
                    <tr>
                      <td class="fw-semibold text-secondary"><?= esc($p['sku']) ?></td>
                      <td class="fw-bold text-dark"><?= esc($p['name']) ?></td>
                      <td>Rp <?= number_format((float)$p['unit_price'], 0, ',', '.') ?></td>
                      <td><span class="badge bg-primary"><?= $p['stock_qty'] ?> unit</span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">Belum ada produk yang dikaitkan ke kampanye ini.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab 5: Overview / Deskripsi -->
        <div class="tab-pane fade" id="tab-overview" role="tabpanel">
          <div class="p-3 bg-light rounded-3 border">
            <h6 class="fw-bold text-dark mb-2">Objektif &amp; Ketentuan Khusus</h6>
            <p class="mb-0 text-secondary" style="white-space: pre-line;"><?= esc($campaign['description'] ?: 'Tidak ada instruksi khusus yang dicantumkan.') ?></p>
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- Modal 1: Assign KOL ke Kampanye -->
<div class="modal fade" id="assignKolModal" tabindex="-1" aria-labelledby="assignKolModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold" id="assignKolModalLabel"><i class="fas fa-user-plus me-2 text-primary"></i>Daftarkan Talent KOL</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="assignKolForm">
        <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Talent KOL <span class="text-danger">*</span></label>
            <select class="form-select" name="kol_id" id="assignKolId" required>
              <option value="">-- Pilih dari Master KOL --</option>
              <?php if (!empty($availableKols)): ?>
                <?php foreach ($availableKols as $ak): ?>
                  <option value="<?= $ak['id'] ?>"><?= esc($ak['name']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Deal Amount / Fee Kerjasama (Rp) <span class="text-danger">*</span></label>
            <input type="number" step="50000" class="form-control" name="deal_amount" id="assignDealAmount" placeholder="Contoh: 5000000" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Target Jumlah Video Konten (VT) <span class="text-danger">*</span></label>
            <input type="number" min="1" max="10" class="form-control" name="total_vt" id="assignTotalVt" value="1" required>
            <small class="text-muted">Sistem akan otomatis membuat draf tugas sesuai jumlah VT.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan / Scope of Work</label>
            <textarea class="form-control" name="notes" id="assignNotes" rows="2" placeholder="Ketentuan endorse atau link brief..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveAssign">
            <i class="fas fa-check me-1"></i> Daftarkan KOL
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Review & Rating KOL -->
<div class="modal fade" id="modalReviewKol" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-dark mb-0">
          <i class="fas fa-star text-warning me-2"></i> Review &amp; Rating Kreator
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formReviewKol">
        <input type="hidden" name="kol_id" id="rev_kol_id">
        <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">
        <div class="modal-body p-4">
          <div class="alert alert-light border mb-3">
            <small class="text-muted d-block">Talent yang dinilai:</small>
            <div class="fw-bold text-dark fs-6" id="rev_kol_name">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold text-dark text-xs text-uppercase">Rating Kinerja (1 - 5 Bintang) <span class="text-danger">*</span></label>
            <select class="form-select" name="rating" id="rev_rating" required>
              <option value="5">★★★★★ (5/5) — Sangat Memuaskan / Luar Biasa</option>
              <option value="4">★★★★☆ (4/5) — Bagus / Sesuai Ekspektasi</option>
              <option value="3">★★★☆☆ (3/5) — Cukup / Standar</option>
              <option value="2">★★☆☆☆ (2/5) — Kurang Memuaskan / Terlambat</option>
              <option value="1">★☆☆☆☆ (1/5) — Sangat Buruk / Tidak Kooperatif</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold text-dark text-xs text-uppercase">Ulasan &amp; Catatan Evaluasi</label>
            <textarea class="form-control" name="review_text" id="rev_review_text" rows="3" placeholder="Tuliskan umpan balik mengenai responsivitas, ketepatan waktu pengiriman draf, dan kualitas video..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light border-top py-2.5">
          <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold text-dark" id="btnSubmitReview">
            <i class="fas fa-save me-1"></i> Simpan Ulasan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 3: Terima & Assign Pelamar Kampanye -->
<div class="modal fade" id="modalAcceptApp" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-dark mb-0">
          <i class="fas fa-check-circle text-success me-2"></i> Terima Lamaran &amp; Tugaskan KOL
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formAcceptApp">
        <input type="hidden" name="application_id" id="accept_app_id">
        <input type="hidden" name="action" value="ACCEPT">
        <div class="modal-body p-4">
          <div class="alert alert-light border mb-3">
            <small class="text-muted d-block">Talent Pelamar:</small>
            <div class="fw-bold text-dark fs-6" id="accept_kol_name">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold text-dark text-xs text-uppercase">Deal Amount Final (Rp) <span class="text-danger">*</span></label>
            <input type="number" step="50000" class="form-control" name="deal_amount" id="accept_deal_amount" required>
            <small class="text-muted text-xs">Nominal fee kerjasama akhir yang disepakati.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold text-dark text-xs text-uppercase">Target Jumlah Video Konten (VT) <span class="text-danger">*</span></label>
            <input type="number" min="1" max="10" class="form-control" name="total_vt" id="accept_total_vt" value="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold text-dark text-xs text-uppercase">Catatan Scope of Work</label>
            <textarea class="form-control" name="notes" id="accept_notes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light border-top py-2.5">
          <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold" id="btnSubmitAccept">
            <i class="fas fa-check me-1"></i> Setujui &amp; Buat Tugas
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function openAssignKolModal() {
  $('#assignKolForm')[0].reset();
  new bootstrap.Modal(document.getElementById('assignKolModal')).show();
}

$('#assignKolForm').on('submit', function(e) {
  e.preventDefault();
  let btn = $('#btnSaveAssign');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mendaftarkan...');

  $.ajax({
    url: '<?= base_url('campaign-kols/assign') ?>',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Daftarkan KOL');
      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('assignKolModal')).hide();
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: res.message,
          timer: 1800,
          showConfirmButton: false
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire('Gagal', res.message, 'error');
      }
    },
    error: function() {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Daftarkan KOL');
      Swal.fire('Error', 'Gagal memproses penugasan KOL.', 'error');
    }
  });
});

function removeAssignedKol(id, name) {
  Swal.fire({
    title: 'Hapus KOL ' + name + '?',
    text: "Penugasan beserta draf tugas video (VT) terkait akan dihapus.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= base_url('campaign-kols/remove') ?>/' + id, function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Terhapus!',
            text: res.message,
            timer: 1800,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }
      }, 'json');
    }
  });
}

function openReviewModal(kolId, campaignId, kolName, rating, reviewText) {
  $('#rev_kol_id').val(kolId);
  $('#rev_kol_name').text(kolName);
  $('#rev_rating').val(rating || 5);
  $('#rev_review_text').val(reviewText || '');
  new bootstrap.Modal(document.getElementById('modalReviewKol')).show();
}

$('#formReviewKol').on('submit', function(e) {
  e.preventDefault();
  let btn = $('#btnSubmitReview');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

  $.ajax({
    url: '<?= base_url('campaign-kols/submit-review') ?>',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Ulasan');
      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalReviewKol')).hide();
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: res.message,
          timer: 1800,
          showConfirmButton: false
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire('Gagal', res.message, 'error');
      }
    },
    error: function() {
      btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Ulasan');
      Swal.fire('Error', 'Gagal menyimpan ulasan.', 'error');
    }
  });
});

function openAcceptAppModal(appId, kolName, proposedRate, notes) {
  $('#accept_app_id').val(appId);
  $('#accept_kol_name').text(kolName);
  $('#accept_deal_amount').val(proposedRate);
  $('#accept_total_vt').val(1);
  $('#accept_notes').val(notes || '');
  new bootstrap.Modal(document.getElementById('modalAcceptApp')).show();
}

$('#formAcceptApp').on('submit', function(e) {
  e.preventDefault();
  let btn = $('#btnSubmitAccept');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyetujui...');

  $.ajax({
    url: '<?= base_url('campaign-kols/review-application') ?>',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Setujui &amp; Buat Tugas');
      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalAcceptApp')).hide();
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: res.message,
          timer: 1800,
          showConfirmButton: false
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire('Gagal', res.message, 'error');
      }
    },
    error: function() {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Setujui &amp; Buat Tugas');
      Swal.fire('Error', 'Gagal memproses persetujuan lamaran.', 'error');
    }
  });
});

function rejectApplication(appId, kolName) {
  Swal.fire({
    title: 'Tolak Lamaran ' + kolName + '?',
    text: "Status lamaran akan diubah menjadi Ditolak.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Tolak!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('<?= base_url('campaign-kols/review-application') ?>', {
        application_id: appId,
        action: 'REJECT'
      }, function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Ditolak!',
            text: res.message,
            timer: 1800,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }
      }, 'json');
    }
  });
}

function viewKolTasks(campaignKolId, kolName) {
  $('#modalKolTasksLabel').html('<i class="fas fa-tasks me-2 text-primary"></i>Rincian Tugas VT — ' + kolName);
  $('#modalKolTasksBody').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat rincian tugas...</div>');
  new bootstrap.Modal(document.getElementById('modalKolTasks')).show();

  $.get('<?= base_url('campaign-kols/tasks-json') ?>/' + campaignKolId, function(res) {
    if (res.success && res.tasks && res.tasks.length > 0) {
      let html = '<div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0" style="font-size:13px;"><thead class="table-light"><tr><th>No. VT</th><th>Judul Tugas</th><th>Fee Deliverable</th><th>Status</th><th>Deadline</th><th>Aksi</th></tr></thead><tbody>';
      res.tasks.forEach(t => {
        let deadline = t.deadline ? t.deadline : '<span class="text-muted small">Belum di-set</span>';
        html += `<tr>
          <td class="fw-bold">#${t.task_number}</td>
          <td><b>${t.title || 'Video VT #' + t.task_number}</b></td>
          <td class="text-success fw-bold">Rp ${Number(t.rate_amount || 0).toLocaleString('id-ID')}</td>
          <td><span class="badge bg-primary-subtle text-primary border border-primary px-2 py-0.5 rounded-pill" style="font-size:10px;">${t.status}</span></td>
          <td>${deadline}</td>
          <td>
            <a href="<?= base_url('tasks') ?>?campaign_id=<?= $campaign['id'] ?>" class="btn btn-outline-primary btn-xs rounded-pill px-2">Buka Task</a>
          </td>
        </tr>`;
      });
      html += '</tbody></table></div>';
      $('#modalKolTasksBody').html(html);
    } else {
      $('#modalKolTasksBody').html('<div class="text-center py-4 text-muted"><i class="fas fa-info-circle fa-2x mb-2 text-secondary opacity-50"></i><div>Belum ada tugas VT terdaftar untuk KOL ini.</div></div>');
    }
  }).fail(function() {
    $('#modalKolTasksBody').html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><div>Gagal memuat data tugas dari server.</div></div>');
  });
}
</script>

<!-- Modal Rincian Tugas KOL -->
<div class="modal fade" id="modalKolTasks" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-light border-bottom py-3">
        <h6 class="modal-title fw-bold text-dark mb-0" id="modalKolTasksLabel">Rincian Tugas VT</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3" id="modalKolTasksBody">
      </div>
      <div class="modal-footer bg-light py-2 border-top">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
