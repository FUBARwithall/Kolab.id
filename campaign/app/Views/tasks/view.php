<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-tasks text-primary me-2"></i>Detail Task #<?= esc($task['id']) ?> &mdash; <?= esc($task['title'] ?: 'VT #' . $task['task_number']) ?>
        </h3>
        <p class="text-muted small mb-0">Rincian siklus hidup produksi konten: briefing, draft review, penayangan live, metrik performa &amp; riwayat status.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('tasks') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 me-2 shadow-sm" onclick="openEditTaskModal(<?= (int)$task['id'] ?>)">
          <i class="fas fa-edit me-1"></i> Edit Tugas
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3 shadow-sm" onclick="deleteTask(<?= (int)$task['id'] ?>, '<?= esc($task['title'] ?: 'Task #' . $task['task_number']) ?>')">
          <i class="fas fa-trash-alt me-1"></i> Hapus
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- 4 Key Metric Highlight Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-wallet fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Tarif / Fee Konten</span>
                <h4 class="fw-bold mb-0 text-dark">
                  Rp <?= number_format((float)($task['final_budget'] ?: $task['rate_amount'] ?: 0), 0, ',', '.') ?>
                </h4>
                <small class="text-muted"><?= esc($task['currency_code'] ?: 'IDR') ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                <i class="far fa-calendar-alt fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Target Deadline</span>
                <h5 class="fw-bold mb-0 text-dark">
                  <?= !empty($task['final_deadline']) ? date('d M Y', strtotime($task['final_deadline'])) : 'Belum di-set' ?>
                </h5>
                <?php 
                  $deadlineDate = !empty($task['final_deadline']) ? date('Y-m-d', strtotime($task['final_deadline'])) : null;
                  $isDone = in_array($task['status'], ['POSTED', 'COMPLETED', 'DONE'], true);
                  if ($deadlineDate):
                    if ($isDone): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle mt-1"><i class="fas fa-check-circle me-1"></i>On-Time</span>
                    <?php elseif (date('Y-m-d') > $deadlineDate): ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1"><i class="fas fa-exclamation-triangle me-1"></i>Terlambat (Late)</span>
                    <?php else: ?>
                      <span class="badge bg-info-subtle text-info border border-info-subtle mt-1"><i class="far fa-clock me-1"></i>Tepat Waktu</span>
                    <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-calculator fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Biaya Per Tayang (CPV)</span>
                <h4 class="fw-bold mb-0 text-info">
                  Rp <?= number_format((float)($task['cpv'] ?: ($task['posting_metrics']['actual_cpv'] ?? 0)), 0, ',', '.') ?>
                </h4>
                <small class="text-muted">Per View Target</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-traffic-light fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Status Operasional</span>
                <h6 class="fw-bold mb-0 text-success mt-1">
                  <span class="badge bg-primary px-2 py-1"><?= esc($task['status']) ?></span>
                </h6>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none mt-1" onclick="openUpdateStatusModal()">
                  <i class="fas fa-pencil-alt fa-xs me-1"></i>Ubah Status
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Post Performance Card (Apify & Metrics) -->
    <?php 
      $liveUrl = $task['posting']['live_url'] ?? ($task['live_url'] ?? '');
      $metrics = $task['posting_metrics'] ?? null;
      $views   = (int)($metrics['views_count'] ?? 0);
      $likes   = (int)($metrics['likes_count'] ?? 0);
      $comments = (int)($metrics['comments_count'] ?? 0);
      $shares  = (int)($metrics['shares_count'] ?? 0);
      $saves   = (int)($metrics['saves_count'] ?? 0);
      $totalEng = $likes + $comments + $shares + $saves;
      $er = ($views > 0) ? round(($totalEng / $views) * 100, 2) : 0.00;
    ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="border-top: 4px solid #198754 !important;">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3 border-bottom mb-3">
          <div>
            <h5 class="fw-bold mb-1 text-dark">
              <i class="fas fa-chart-line text-success me-2"></i>Live Post Performance &amp; Metrik Video
            </h5>
            <div class="small text-muted">
              Live URL: 
              <?php if (!empty($liveUrl)): ?>
                <a href="<?= esc($liveUrl) ?>" target="_blank" rel="noopener noreferrer" class="fw-semibold text-primary text-decoration-none">
                  <?= esc($liveUrl) ?> <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                </a>
              <?php else: ?>
                <span class="fst-italic">Belum ada URL postingan live yang diset.</span>
                <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 ms-2" onclick="setLiveUrlModal(<?= (int)$task['id'] ?>)">
                  <i class="fas fa-plus me-1"></i>Input Live URL
                </button>
              <?php endif; ?>
            </div>
          </div>
          <div class="d-flex gap-2">
            <?php if (!empty($liveUrl)): ?>
              <button type="button" class="btn btn-outline-success btn-sm rounded-2 px-3 shadow-sm" onclick="syncVideoMetrics(<?= (int)$task['id'] ?>)" id="btnSyncMetrics">
                <i class="fas fa-sync-alt me-1"></i> Sinkronisasi Metrik Video
              </button>
              <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-3 shadow-sm" onclick="setLiveUrlModal(<?= (int)$task['id'] ?>)">
                <i class="fas fa-edit me-1"></i> Edit Live URL
              </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- 6 Performance Counters -->
        <div class="row g-3 text-center">
          <div class="col-6 col-md-2">
            <div class="bg-light rounded-3 p-3 border">
              <div class="text-uppercase text-muted fs-8 fw-bold"><i class="fas fa-eye text-primary me-1"></i>Views</div>
              <h3 class="fw-bold mb-0 text-dark mt-1" id="valViews"><?= number_format($views) ?></h3>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="bg-light rounded-3 p-3 border">
              <div class="text-uppercase text-muted fs-8 fw-bold"><i class="fas fa-heart text-danger me-1"></i>Likes</div>
              <h3 class="fw-bold mb-0 text-dark mt-1" id="valLikes"><?= number_format($likes) ?></h3>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="bg-light rounded-3 p-3 border">
              <div class="text-uppercase text-muted fs-8 fw-bold"><i class="fas fa-comments text-info me-1"></i>Comments</div>
              <h3 class="fw-bold mb-0 text-dark mt-1" id="valComments"><?= number_format($comments) ?></h3>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="bg-light rounded-3 p-3 border">
              <div class="text-uppercase text-muted fs-8 fw-bold"><i class="fas fa-share text-success me-1"></i>Shares</div>
              <h3 class="fw-bold mb-0 text-dark mt-1" id="valShares"><?= number_format($shares) ?></h3>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="bg-light rounded-3 p-3 border">
              <div class="text-uppercase text-muted fs-8 fw-bold"><i class="fas fa-bookmark text-warning me-1"></i>Saves</div>
              <h3 class="fw-bold mb-0 text-dark mt-1" id="valSaves"><?= number_format($saves) ?></h3>
            </div>
          </div>
          <div class="col-6 col-md-2">
            <div class="bg-light rounded-3 p-3 border">
              <div class="text-uppercase text-muted fs-8 fw-bold"><i class="fas fa-percentage text-purple me-1"></i>ER (%)</div>
              <h3 class="fw-bold mb-0 text-dark mt-1" id="valEr"><?= number_format($er, 2) ?>%</h3>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Main Content Details Row -->
    <div class="row g-4">
      <!-- Kolom Kiri: Spesifikasi Tugas & Talent -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-dark">
              <i class="fas fa-info-circle text-primary me-2"></i>Informasi &amp; Spesifikasi Tugas
            </h5>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped align-middle mb-0">
              <tbody>
                <tr>
                  <th class="ps-4 text-muted fw-semibold" style="width: 35%;">ID Tugas</th>
                  <td class="fw-bold text-dark">#<?= esc($task['id']) ?></td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Nomor Urut VT</th>
                  <td><span class="badge bg-secondary-subtle text-secondary border">VT #<?= esc($task['task_number']) ?></span></td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Judul Video / SOW</th>
                  <td class="fw-bold text-dark"><?= esc($task['title'] ?: 'Task #' . $task['task_number']) ?></td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Kampanye</th>
                  <td>
                    <a href="<?= base_url('campaigns/view/' . $task['campaign_id']) ?>" class="fw-bold text-primary text-decoration-none">
                      <?= esc($task['campaign_title']) ?>
                    </a>
                    <span class="badge bg-light text-dark border ms-1"><?= esc($task['campaign_status']) ?></span>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Talent KOL</th>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: bold; font-size: 11px;">
                        <?= strtoupper(substr($task['kol_name'] ?? 'K', 0, 1)) ?>
                      </div>
                      <div>
                        <div class="fw-bold text-dark"><?= esc($task['kol_name']) ?></div>
                        <small class="text-muted"><i class="fab fa-whatsapp text-success me-1"></i><?= esc($task['kol_whatsapp'] ?: '-') ?></small>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Platform &amp; Format</th>
                  <td>
                    <span class="badge bg-dark px-2 py-1"><i class="<?= esc($task['platform_icon'] ?: 'fas fa-share-alt') ?> me-1"></i><?= esc($task['platform_name'] ?: 'Video') ?></span>
                    <span class="badge bg-light text-dark border ms-1"><?= esc($task['content_type'] ?: 'Short Form Video') ?></span>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Konsep Konten (Varian)</th>
                  <td>
                    <?php if (!empty($task['variant_name'])): ?>
                      <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><?= esc($task['variant_name']) ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Tarif Disepakati</th>
                  <td class="fw-bold text-success fs-6">
                    Rp <?= number_format((float)($task['final_budget'] ?: $task['rate_amount'] ?: 0), 0, ',', '.') ?>
                  </td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Catatan Operasional</th>
                  <td><?= esc($task['notes'] ?: '-') ?></td>
                </tr>
                <tr>
                  <th class="ps-4 text-muted fw-semibold">Dibuat Pada</th>
                  <td><?= !empty($task['created_at']) ? date('d M Y H:i:s', strtotime($task['created_at'])) : '-' ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Card Logistik Pengiriman Sampel -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-dark">
              <i class="fas fa-shipping-fast text-warning me-2"></i>Status Logistik Sampel Produk
            </h5>
          </div>
          <div class="card-body p-4">
            <?php if (!empty($task['shipment'])): ?>
              <?php $sh = $task['shipment']; ?>
              <div class="row g-3">
                <div class="col-sm-6">
                  <small class="text-muted d-block">Ekspedisi / Kurir</small>
                  <span class="fw-bold text-dark"><?= esc($sh['courier_name'] ?: 'Kurir Standar') ?></span>
                </div>
                <div class="col-sm-6">
                  <small class="text-muted d-block">Nomor Resi Pengiriman</small>
                  <code><?= esc($sh['tracking_number'] ?: '-') ?></code>
                </div>
                <div class="col-sm-6">
                  <small class="text-muted d-block">Status Pengiriman</small>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><?= esc($sh['shipping_status'] ?? 'DELIVERED') ?></span>
                </div>
                <div class="col-sm-6">
                  <small class="text-muted d-block">Diterima Pada</small>
                  <span class="fw-semibold text-dark"><?= !empty($sh['delivered_at']) ? date('d M Y H:i', strtotime($sh['delivered_at'])) : '-' ?></span>
                </div>
              </div>
            <?php else: ?>
              <div class="text-center py-3 text-muted">
                <i class="fas fa-box-open fa-2x mb-2 d-block text-secondary"></i>
                Tidak ada data logistik paket sampel khusus untuk tugas ini.
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Kolom Kanan: Briefing, Draft Review, Sentimen & Audit Log -->
      <div class="col-lg-6">
        
        <!-- Accordion Navigasi Tab -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom p-2">
            <ul class="nav nav-pills nav-fill gap-1" id="detailTabs" role="tablist">
              <li class="nav-item">
                <button class="nav-link active py-2 rounded-2" id="tab-brief-btn" data-bs-toggle="pill" data-bs-target="#tab-brief">
                  <i class="fas fa-file-alt me-1"></i> Brief &amp; SOW
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link py-2 rounded-2" id="tab-drafts-btn" data-bs-toggle="pill" data-bs-target="#tab-drafts">
                  <i class="fas fa-video me-1"></i> Review Draft (<?= count($task['drafts'] ?? []) ?>)
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link py-2 rounded-2" id="tab-sentiment-btn" data-bs-toggle="pill" data-bs-target="#tab-sentiment">
                  <i class="fas fa-smile me-1"></i> Sentimen
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link py-2 rounded-2" id="tab-logs-btn" data-bs-toggle="pill" data-bs-target="#tab-logs">
                  <i class="fas fa-history me-1"></i> Audit Trail
                </button>
              </li>
            </ul>
          </div>
          <div class="card-body p-4">
            <div class="tab-content" id="detailTabsContent">

              <!-- Tab 1: Brief & SOW -->
              <div class="tab-pane fade show active" id="tab-brief">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="fw-bold text-dark mb-0"><i class="fas fa-file-contract text-primary me-2"></i>Pedoman Konten &amp; Brief</h6>
                  <?php if (!empty($task['brief'])): ?>
                    <span class="badge bg-info"><?= esc($task['brief']['approval_status']) ?></span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($task['brief'])): ?>
                  <?php $b = $task['brief']; ?>
                  <div class="mb-3">
                    <label class="text-muted small d-block">Judul / Konsep Brief</label>
                    <div class="fw-bold text-dark"><?= esc($b['title']) ?></div>
                  </div>
                  <div class="mb-3">
                    <label class="text-muted small d-block">Guidelines / Arahan</label>
                    <div class="p-3 bg-light rounded-2 small text-secondary" style="white-space: pre-wrap;"><?= esc($b['guidelines']) ?></div>
                  </div>
                  <?php if (!empty($b['dos']) || !empty($b['donts'])): ?>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label class="text-success small fw-bold"><i class="fas fa-check me-1"></i>Do's</label>
                        <div class="p-2 bg-success-subtle rounded-2 small text-success"><?= esc($b['dos'] ?: '-') ?></div>
                      </div>
                      <div class="col-sm-6">
                        <label class="text-danger small fw-bold"><i class="fas fa-times me-1"></i>Don'ts</label>
                        <div class="p-2 bg-danger-subtle rounded-2 small text-danger"><?= esc($b['donts'] ?: '-') ?></div>
                      </div>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($b['reference_url'])): ?>
                    <div>
                      <label class="text-muted small d-block">Link Referensi Video</label>
                      <a href="<?= esc($b['reference_url']) ?>" target="_blank" class="small text-primary text-decoration-none">
                        <?= esc($b['reference_url']) ?> <i class="fas fa-external-link-alt fa-xs"></i>
                      </a>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="text-center py-4 text-muted">
                    <i class="fas fa-file-alt fa-2x mb-2 d-block text-secondary"></i>
                    Belum ada brief khusus yang dilampirkan pada tugas ini.
                  </div>
                <?php endif; ?>
              </div>

              <!-- Tab 2: Draft Review History -->
              <div class="tab-pane fade" id="tab-drafts">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-video text-warning me-2"></i>Histori Versi Draft Video</h6>
                <?php if (!empty($task['drafts'])): ?>
                  <div class="d-flex flex-column gap-3">
                    <?php foreach ($task['drafts'] as $d): ?>
                      <div class="border rounded-3 p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <span class="badge bg-primary px-2 py-1">Versi <?= esc($d['version']) ?></span>
                          <span class="badge <?= ($d['status'] === 'APPROVED') ? 'bg-success' : (($d['status'] === 'REVISION_NEEDED') ? 'bg-danger' : 'bg-warning') ?>">
                            <?= esc($d['status']) ?>
                          </span>
                        </div>
                        <div class="mb-2">
                          <a href="<?= esc($d['video_url']) ?>" target="_blank" class="fw-bold text-primary text-decoration-none">
                            <i class="fas fa-play-circle me-1"></i>Buka Video Draft v<?= esc($d['version']) ?>
                          </a>
                        </div>
                        <?php if (!empty($d['review_notes'])): ?>
                          <div class="small text-muted bg-white p-2 rounded border mb-2">
                            <strong>Reviewer Note:</strong> <?= esc($d['review_notes']) ?>
                          </div>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size: 11px;">
                          Diupload: <?= date('d M Y H:i', strtotime($d['submitted_at'])) ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="text-center py-4 text-muted">
                    <i class="fas fa-video fa-2x mb-2 d-block text-secondary"></i>
                    Belum ada draft video yang diunggah oleh kreator.
                  </div>
                <?php endif; ?>
              </div>

              <!-- Tab 3: Sentiment Analysis -->
              <div class="tab-pane fade" id="tab-sentiment">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="fw-bold text-dark mb-0"><i class="fas fa-smile text-info me-2"></i>Analisis Sentimen Komentar</h6>
                  <button type="button" class="btn btn-outline-info btn-sm rounded-2" onclick="triggerSentimentAnalysis(<?= (int)$task['id'] ?>)" id="btnAnalyzeSentiment">
                    <i class="fas fa-magic me-1"></i> Jalankan Analisis
                  </button>
                </div>
                <?php if (!empty($task['sentiment'])): ?>
                  <?php $sent = $task['sentiment']; ?>
                  <div class="text-center p-3 bg-light rounded-3 border mb-3">
                    <div class="text-muted small mb-1">Skor Sentimen Positif</div>
                    <h2 class="fw-bold text-success mb-0" id="sentimentScore"><?= number_format((float)$sent['sentiment_score'], 1) ?>%</h2>
                    <small class="text-muted">Dianalisis: <?= date('d M Y H:i', strtotime($sent['analyzed_at'])) ?></small>
                  </div>
                  <div class="row g-2 text-center">
                    <div class="col-4">
                      <div class="p-2 bg-success-subtle rounded-2 text-success">
                        <small class="d-block fw-bold">Positif</small>
                        <h5 class="mb-0 fw-bold" id="sentimentPos"><?= number_format((int)$sent['positive_count']) ?></h5>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="p-2 bg-secondary-subtle rounded-2 text-secondary">
                        <small class="d-block fw-bold">Netral</small>
                        <h5 class="mb-0 fw-bold" id="sentimentNeu"><?= number_format((int)$sent['neutral_count']) ?></h5>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="p-2 bg-danger-subtle rounded-2 text-danger">
                        <small class="d-block fw-bold">Negatif</small>
                        <h5 class="mb-0 fw-bold" id="sentimentNeg"><?= number_format((int)$sent['negative_count']) ?></h5>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="text-center py-4 text-muted">
                    <i class="fas fa-comments fa-2x mb-2 d-block text-secondary"></i>
                    Belum ada hasil analisis sentimen komentar untuk tugas ini.
                  </div>
                <?php endif; ?>
              </div>

              <!-- Tab 4: Audit Logs -->
              <div class="tab-pane fade" id="tab-logs">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-history text-secondary me-2"></i>Log Perubahan Status &amp; Budget</h6>
                
                <!-- Status Histories -->
                <div class="mb-4">
                  <h6 class="text-muted small fw-bold text-uppercase mb-2">Riwayat Status Operasional</h6>
                  <?php if (!empty($task['status_histories'])): ?>
                    <div class="table-responsive">
                      <table class="table table-sm table-bordered align-middle" style="font-size: 12px;">
                        <thead class="table-light">
                          <tr>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Catatan</th>
                            <th>Waktu</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($task['status_histories'] as $sh): ?>
                            <tr>
                              <td><span class="badge bg-secondary"><?= esc($sh['from_status']) ?></span></td>
                              <td><span class="badge bg-primary"><?= esc($sh['to_status']) ?></span></td>
                              <td><?= esc($sh['notes'] ?: '-') ?></td>
                              <td><?= date('d M Y H:i', strtotime($sh['created_at'])) ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <div class="text-muted small">Belum ada catatan transisi status.</div>
                  <?php endif; ?>
                </div>

                <!-- Budget Histories -->
                <div>
                  <h6 class="text-muted small fw-bold text-uppercase mb-2">Riwayat Penyesuaian Tarif / Budget</h6>
                  <?php if (!empty($task['budget_histories'])): ?>
                    <div class="table-responsive">
                      <table class="table table-sm table-bordered align-middle" style="font-size: 12px;">
                        <thead class="table-light">
                          <tr>
                            <th>Tarif Lama</th>
                            <th>Tarif Baru</th>
                            <th>Alasan</th>
                            <th>Waktu</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($task['budget_histories'] as $bh): ?>
                            <tr>
                              <td>Rp <?= number_format((float)$bh['old_rate'], 0, ',', '.') ?></td>
                              <td class="fw-bold text-success">Rp <?= number_format((float)$bh['new_rate'], 0, ',', '.') ?></td>
                              <td><?= esc($bh['reason'] ?: '-') ?></td>
                              <td><?= date('d M Y H:i', strtotime($bh['created_at'])) ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <div class="text-muted small">Belum ada riwayat perubahan tarif.</div>
                  <?php endif; ?>
                </div>

              </div>

            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- Modal Ubah Status Operasional -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formUpdateStatus" onsubmit="handleUpdateStatus(event)">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-traffic-light me-2"></i>Ubah Status Operasional</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Status Baru</label>
            <select class="form-select" name="status" id="selectNewStatus" required>
              <option value="DRAFT">DRAFT (Persiapan SOW)</option>
              <option value="BRIEF_SUBMITTED">BRIEF SUBMITTED (Brief Terkirim)</option>
              <option value="BRIEF_APPROVED">BRIEF APPROVED (Brief Disetujui)</option>
              <option value="DRAFT_SUBMITTED">DRAFT REVIEW (Draft Video Masuk)</option>
              <option value="DRAFT_REVISION">DRAFT REVISION (Perlu Revisi)</option>
              <option value="DRAFT_APPROVED">DRAFT APPROVED (Draft Disetujui)</option>
              <option value="SCHEDULED">SCHEDULED (Dijadwalkan)</option>
              <option value="POSTED">POSTED (Tayang Live)</option>
              <option value="COMPLETED">COMPLETED (Selesai &amp; Lunas)</option>
              <option value="CANCELLED">CANCELLED (Dibatalkan)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Transisi</label>
            <textarea class="form-control" name="notes" id="statusTransitionNotes" rows="2" placeholder="Catatan alasan perubahan status..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveStatus">Simpan Status</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Input URL Postingan Live -->
<div class="modal fade" id="modalLiveUrl" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formLiveUrl" onsubmit="saveLiveUrl(event)">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-globe me-2"></i>Input URL Postingan Live</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="liveTaskId" name="task_id" value="<?= (int)$task['id'] ?>">
          <div class="mb-3">
            <label class="form-label fw-semibold">Platform</label>
            <select class="form-select" id="livePlatform" name="platform_id" required>
              <option value="<?= $task['platform_id'] ?: 1 ?>" selected><?= esc($task['platform_name'] ?: 'TikTok') ?></option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">URL Postingan Live</label>
            <input type="url" class="form-control" id="livePostUrl" name="post_url" required value="<?= esc($liveUrl) ?>" placeholder="https://www.tiktok.com/@username/video/...">
            <small class="text-muted">Pastikan URL publik dan dapat diakses untuk penarikan metrik otomatis.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Waktu Penayangan</label>
            <input type="datetime-local" class="form-control" id="livePostedAt" name="posted_at" value="<?= date('Y-m-d\TH:i') ?>">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success rounded-2 px-4" id="btnSaveLiveUrl">Simpan &amp; Publish</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
const taskId = <?= (int)$task['id'] ?>;

function openUpdateStatusModal() {
    $('#selectNewStatus').val('<?= esc($task['status']) ?>');
    $('#statusTransitionNotes').val('');
    $('#modalUpdateStatus').modal('show');
}

function handleUpdateStatus(e) {
    e.preventDefault();
    const btn = $('#btnSaveStatus');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.ajax({
        url: '<?= base_url('tasks/update-status/') ?>' + taskId,
        type: 'POST',
        data: $('#formUpdateStatus').serialize(),
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).text('Simpan Status');
            if (res.success) {
                $('#modalUpdateStatus').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Status Diperbarui',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function() {
            btn.prop('disabled', false).text('Simpan Status');
            Swal.fire('Error', 'Gagal memperbarui status tugas.', 'error');
        }
    });
}

function setLiveUrlModal(id) {
    $('#modalLiveUrl').modal('show');
}

function saveLiveUrl(e) {
    e.preventDefault();
    const btn = $('#btnSaveLiveUrl');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.ajax({
        url: '<?= base_url('tasks/set-live-url/') ?>' + taskId,
        type: 'POST',
        data: $('#formLiveUrl').serialize(),
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).text('Simpan & Publish');
            if (res.success) {
                $('#modalLiveUrl').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function() {
            btn.prop('disabled', false).text('Simpan & Publish');
            Swal.fire('Error', 'Gagal menyimpan URL live.', 'error');
        }
    });
}

function syncVideoMetrics(id) {
    const btn = $('#btnSyncMetrics');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sinkronisasi...');

    $.ajax({
        url: '<?= base_url('tasks/sync-posting-metrics/') ?>' + id,
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> Sinkronisasi Metrik Video');
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Metrik Disinkronkan',
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
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> Sinkronisasi Metrik Video');
            Swal.fire('Error', 'Terjadi kesalahan sistem saat sinkronisasi metrik.', 'error');
        }
    });
}

function triggerSentimentAnalysis(id) {
    const btn = $('#btnAnalyzeSentiment');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menganalisis...');

    $.ajax({
        url: '<?= base_url('tasks/analyze-sentiment/') ?>' + id,
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Jalankan Analisis');
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Analisis Selesai',
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
            btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Jalankan Analisis');
            Swal.fire('Error', 'Gagal memproses analisis sentimen.', 'error');
        }
    });
}

function deleteTask(id, title) {
    Swal.fire({
        title: 'Hapus Tugas Produksi?',
        html: `Apakah Anda yakin ingin menghapus tugas <strong>${title}</strong>? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('tasks/delete') ?>/' + id,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '<?= site_url('tasks') ?>';
                        });
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memproses penghapusan tugas.', 'error');
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
