<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
.section-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    border: none;
}
.section-card .card-header {
    background: #fff;
    border-bottom: 1.5px solid #e9eef6;
    border-radius: 10px 10px 0 0;
    padding: 18px 24px;
}
.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #172b4d;
    margin-bottom: 2px;
}
.section-subtitle {
    font-size: 13px;
    color: #6b778c;
}
.count-badge {
    background: #e8f1fb;
    color: #1f4b6e;
    font-weight: 700;
    font-size: 13px;
    border-radius: 20px;
    padding: 4px 14px;
}
/* Filter chips */
.filter-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding: 14px 24px;
    border-bottom: 1px solid #e9eef6;
}
.filter-chip {
    background: #f0f4fa;
    color: #52606d;
    border: 1px solid #dbe3ef;
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
}
.filter-chip:hover, .filter-chip.active {
    background: #1f4b6e;
    color: #fff;
    border-color: #1f4b6e;
}
/* Campaign cards */
.open-camp-card {
    background: #fff;
    border-radius: 10px;
    border: 1.5px solid #e4ebf5;
    box-shadow: 0 1px 6px rgba(23,43,77,0.06);
    transition: box-shadow 0.18s, transform 0.18s;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.open-camp-card:hover {
    box-shadow: 0 4px 18px rgba(23,43,77,0.13);
    transform: translateY(-2px);
}
.camp-status-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-radius: 20px;
    padding: 3px 10px;
}
.camp-status-active  { background: #e3f9e5; color: #1a7f37; }
.camp-status-draft   { background: #f0f0f0; color: #6b778c; }
.camp-status-closed  { background: #fdecea; color: #c0392b; }
.camp-title {
    font-size: 15px;
    font-weight: 700;
    color: #172b4d;
    margin: 8px 0 4px;
}
.camp-desc {
    font-size: 12px;
    color: #6b778c;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 10px;
}
.camp-meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #6b778c;
    margin-bottom: 4px;
}
.camp-meta-row .val {
    font-weight: 600;
    color: #172b4d;
}
.camp-budget {
    font-size: 15px;
    font-weight: 700;
    color: #1a7f37;
}
.btn-detail-camp {
    background: #ffffff;
    color: #1f4b6e;
    border: 1.5px solid #1f4b6e;
    border-radius: 20px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-detail-camp:hover {
    background: #e8f1fb;
    color: #17395a;
    border-color: #17395a;
}
.badge-applied {
    background: #e3f9e5;
    color: #1a7f37;
    border: 1px solid #a7f3d0;
    border-radius: 20px;
    padding: 7px 14px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-lamar {
    background: #1f4b6e;
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 7px 16px;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-lamar:hover { background: #17395a; color: #fff; }

/* Detail Modal Styles matching CI3 */
.detail-content-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
}
.detail-card-header {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.product-card-item {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    height: 100%;
}
.product-card-title {
    font-weight: 600;
    color: #0f172a;
    font-size: 13px;
    line-height: 1.25;
}
.product-card-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.product-card-price {
    font-weight: 700;
    color: #10b981;
    font-size: 13px;
    text-align: right;
    white-space: nowrap;
}
.spec-list {
    display: flex;
    flex-direction: column;
}
.spec-item {
    border-bottom: 1px solid #f1f5f9;
    padding: 10px 0;
}
.spec-item:first-child {
    padding-top: 0;
}
.spec-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.spec-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.spec-val {
    font-size: 13.5px;
    color: #0f172a;
    line-height: 1.5;
}
.camp-platform-chip {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
}
.camp-platform-tiktok {
    background: #000000;
    color: #ffffff;
    border: 1px solid #000000;
}
.camp-platform-instagram {
    background: #fff0f5;
    color: #e1306c;
    border: 1px solid #fbcfe8;
}
.camp-platform-youtube {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.hashtag-pill {
    background: #eff6ff;
    color: #0284c7;
    border: 1px solid #bfdbfe;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 6px;
}
.tier-pill {
    background: #00bcd4;
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 6px;
}
.cat-pill {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    font-size: 11px;
    font-weight: 500;
    padding: 2px 8px;
    border-radius: 6px;
}

/* 4-Card Metric Grid: Periode, Batas Daftar, Slot, Target ER */
.metric-tile-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
@media (max-width: 991px) {
    .metric-tile-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 575px) {
    .metric-tile-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}
.metric-tile {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
}
.metric-tile .tile-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.metric-tile .tile-val {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}
.metric-tile .tile-sub {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
}

/* Breakdown Kriteria Evaluasi Akun (.qc-list) */
.qc-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 12px;
}
.qc-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.qc-item.passed {
    border-left: 3.5px solid #10b981;
}
.qc-item.failed {
    border-left: 3.5px solid #ef4444;
}
.qc-icon-wrap {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    flex-shrink: 0;
    margin-top: 2px;
}
.qc-icon-wrap.pass {
    background: #ecfdf5;
    color: #10b981;
}
.qc-icon-wrap.fail {
    background: #fef2f2;
    color: #ef4444;
}
.qc-content {
    flex-grow: 1;
    min-width: 0;
}
.qc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 3px;
}
.qc-title {
    font-weight: 700;
    font-size: 12px;
    color: #0f172a;
}
.qc-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    flex-shrink: 0;
}
.qc-badge.pass {
    background: #d1fae5;
    color: #065f46;
}
.qc-badge.fail {
    background: #fee2e2;
    color: #991b1b;
}
.qc-details {
    font-size: 11px;
    line-height: 1.4;
    color: #475569;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state i { font-size: 48px; color: #d0daea; margin-bottom: 16px; }
.empty-state h6 { color: #172b4d; font-weight: 700; font-size: 16px; }
.empty-state p  { color: #6b778c; font-size: 13px; }
</style>

<div class="row mb-4">
  <div class="col-12">
    <div class="section-card">
      <!-- Header -->
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <div class="section-title">
            <i class="fas fa-bullhorn text-warning me-2"></i> Peluang Kampanye Terbuka
          </div>
          <div class="section-subtitle">Pilih dan ajukan kolaborasi untuk kampanye brand yang sedang aktif</div>
        </div>
        <span class="count-badge"><?= count($campaigns) ?> Kampanye</span>
      </div>

      <!-- Filter chips -->
      <div class="filter-chips">
        <a href="#" class="filter-chip active" data-filter="all">Semua</a>
        <a href="#" class="filter-chip" data-filter="ACTIVE">Aktif</a>
        <a href="#" class="filter-chip" data-filter="VT">Video</a>
        <a href="#" class="filter-chip" data-filter="LIVE">Live</a>
        <a href="#" class="filter-chip" data-filter="ENDORSE">Endorse</a>
      </div>

      <!-- Cards -->
      <div class="p-4">
        <?php if (empty($campaigns)): ?>
          <div class="empty-state">
            <i class="fas fa-bullhorn"></i>
            <h6>Belum Ada Kampanye Terbuka</h6>
            <p>Saat ini belum ada kampanye baru yang membuka penerimaan kreator.</p>
          </div>
        <?php else: ?>
          <div class="row g-3" id="camp-list">
            <?php foreach ($campaigns as $camp): ?>
              <div class="col-md-6 col-lg-4 camp-item" data-filter="<?= esc($camp['status'] ?? 'ACTIVE') ?>">
                <div class="open-camp-card p-3">
                  <!-- Status + jumlah pelamar -->
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <?php
                      $st  = $camp['status'] ?? 'ACTIVE';
                      $stc = match($st) {
                          'ACTIVE'    => 'camp-status-active',
                          'DRAFT'     => 'camp-status-draft',
                          'CANCELLED' => 'camp-status-closed',
                          default     => 'camp-status-active',
                      };
                    ?>
                    <span class="camp-status-badge <?= $stc ?>"><?= esc($st) ?></span>
                    <small style="font-size:11px;color:#6b778c;">
                      <i class="fas fa-users me-1" style="color:#2563eb;"></i>
                      <?= (int)($camp['total_applicants'] ?? 0) ?> Pelamar
                    </small>
                  </div>

                  <!-- Title + desc -->
                  <div class="camp-title"><?= esc($camp['title']) ?></div>
                  <div class="camp-desc"><?= esc($camp['description'] ?? 'Kolaborasi kampanye brand bersama KOL & Creator pilihan.') ?></div>

                  <!-- Meta info -->
                  <div style="border-top:1px solid #e9eef6;padding-top:10px;margin-top:auto;">
                    <div class="camp-meta-row">
                      <span>Alokasi Budget</span>
                      <span class="camp-budget">Rp <?= number_format((float)($camp['budget_total'] ?? 0), 0, ',', '.') ?></span>
                    </div>
                    <div class="camp-meta-row" style="margin-bottom:12px;">
                      <span>Periode</span>
                      <span class="val"><?= esc($camp['start_date'] ?? '-') ?> s/d <?= esc($camp['end_date'] ?? '-') ?></span>
                    </div>

                    <div class="d-flex gap-2">
                      <button type="button" class="btn-detail-camp flex-fill"
                        onclick="openCampaignDetailModal(<?= (int)$camp['id'] ?>)">
                        <i class="fas fa-eye me-1"></i> Detail
                      </button>
                      <?php if (!empty($camp['has_applied'])): ?>
                        <span class="badge-applied flex-fill">
                          <i class="fas fa-check-circle me-1"></i> Terdaftar
                        </span>
                      <?php else: ?>
                        <button type="button" class="btn-lamar flex-fill"
                          onclick="openApplyModal(<?= (int)$camp['id'] ?>, '<?= esc(addslashes($camp['title'])) ?>', <?= (float)($camp['budget_total'] ?? 0) ?>)">
                          <i class="fas fa-paper-plane me-1"></i> Lamar
                        </button>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Kampanye -->
<div class="modal fade" id="modal-campaign-detail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 12px 36px rgba(15,23,42,0.18);overflow:hidden;">
      <div class="modal-header py-3 px-4" style="background:#172b4d;color:#fff;">
        <div class="d-flex align-items-center gap-2">
          <i class="fas fa-bullhorn text-warning fs-5"></i>
          <div>
            <h6 class="modal-title fw-bold text-white mb-0" id="detail-camp-title">Detail Campaign</h6>
            <span class="text-white-50" style="font-size:12px;" id="detail-camp-subtitle">Spesifikasi campaign, panduan konten &amp; kriteria brand</span>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body px-4 py-3" style="background:#f8fafc;">
        <!-- 4-Card Metric Grid: Periode, Batas Daftar, Kuota Slot, Target ER -->
        <div class="metric-tile-grid mb-3">
          <!-- 1. Periode -->
          <div class="metric-tile">
            <div class="tile-label"><i class="fas fa-calendar-check text-primary"></i> Periode</div>
            <div class="tile-val" id="metric-period">—</div>
            <div class="tile-sub" id="metric-period-sub">—</div>
          </div>

          <!-- 2. Batas Daftar -->
          <div class="metric-tile">
            <div class="tile-label"><i class="fas fa-clock text-danger"></i> Batas Daftar</div>
            <div class="tile-val" id="metric-deadline" style="color:#0f172a;">—</div>
            <div class="tile-sub" id="metric-deadline-sub">—</div>
          </div>

          <!-- 3. Kuota Slot -->
          <div class="metric-tile">
            <div class="tile-label"><i class="fas fa-users text-success"></i> Kuota Slot</div>
            <div class="tile-val" id="metric-slot-val" style="color:#16a34a;">—</div>
            <div class="progress" style="height:4px; margin:6px 0 3px 0; background:#e2e8f0; border-radius:2px; overflow:hidden;" id="metric-slot-prog-wrap">
              <div class="progress-bar" id="metric-slot-prog" style="width:0%; background:#10b981;"></div>
            </div>
            <div class="tile-sub" id="metric-slot-sub">—</div>
          </div>

          <!-- 4. Target ER -->
          <div class="metric-tile">
            <div class="tile-label"><i class="fas fa-chart-line text-success"></i> Target ER</div>
            <div class="tile-val" id="metric-target-er" style="color:#059669;">—</div>
            <div class="tile-sub">Engagement rate</div>
          </div>
        </div>

        <div class="row g-3">
          <!-- LEFT COLUMN (col-lg-7) -->
          <div class="col-lg-7">
            <!-- 1. Card: Deskripsi Campaign -->
            <div class="detail-content-card mb-3">
              <div class="detail-card-header">
                <i class="fas fa-align-left text-primary"></i> DESKRIPSI CAMPAIGN
              </div>
              <div id="detail-desc" style="color:#334155; font-size:13.5px; line-height:1.6; white-space:pre-wrap;">
                Memuat deskripsi...
              </div>
            </div>

            <!-- 2. Card: Produk yang Digunakan -->
            <div class="detail-content-card mb-3" id="detail-products-section">
              <div class="detail-card-header">
                <i class="fas fa-cube text-primary"></i> <span id="detail-products-header">PRODUK YANG DIGUNAKAN</span>
              </div>
              <div class="row g-2" id="detail-products-grid">
                <!-- Product item cards rendered via JS -->
              </div>
            </div>

            <!-- 3. Card: Panduan Konten & Brief -->
            <div class="detail-content-card mb-3">
              <div class="detail-card-header">
                <i class="fas fa-bookmark text-primary"></i> PANDUAN KONTEN &amp; BRIEF
              </div>
              
              <div class="spec-list">
                <!-- Objective -->
                <div class="spec-item">
                  <div class="spec-label">
                    <i class="fas fa-bullseye text-danger"></i> OBJECTIVE TARGET
                  </div>
                  <div class="spec-val" id="detail-objective">—</div>
                </div>

                <!-- Target Audience -->
                <div class="spec-item">
                  <div class="spec-label">
                    <i class="fas fa-users text-primary"></i> TARGET AUDIENCE
                  </div>
                  <div class="spec-val" id="detail-target-audience">—</div>
                </div>

                <!-- Wajib Hashtag -->
                <div class="spec-item" id="detail-hashtag-box">
                  <div class="spec-label">
                    <i class="fas fa-hashtag text-info"></i> # WAJIB HASHTAG
                  </div>
                  <div class="d-flex flex-wrap gap-1 mt-1" id="detail-hashtags-wrap">
                    <!-- Hashtags rendered via JS -->
                  </div>
                </div>

                <!-- Dokumen SOW / Brief File jika ada -->
                <div class="spec-item" id="detail-sow-box" style="display:none;">
                  <div class="spec-label">
                    <i class="fas fa-file-download text-success"></i> DOKUMEN BRIEF / SOW
                  </div>
                  <div class="mt-1" id="detail-sow-content"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN (col-lg-5) -->
          <div class="col-lg-5">
            <!-- 4. Card: Syarat Kualifikasi -->
            <div class="detail-content-card mb-3">
              <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                <div>
                  <h6 class="fw-bold mb-0" style="font-size:14px;color:#0f172a;">Syarat Kualifikasi</h6>
                  <div style="font-size:11px;color:#64748b;">Kriteria yang dicari oleh Brand</div>
                </div>
                <span class="badge bg-light text-secondary border fw-semibold" style="font-size:10px;">Kriteria Resmi</span>
              </div>

              <div class="spec-list">
                <!-- Platform yang Diwajibkan -->
                <div class="spec-item">
                  <div class="spec-label">
                    <i class="fas fa-share-alt text-primary"></i> PLATFORM YANG DIWAJIBKAN
                  </div>
                  <div class="d-flex flex-wrap gap-2 mt-1" id="detail-platforms-wrap">
                    <!-- Platform badges rendered via JS -->
                  </div>
                </div>

                <!-- Min Followers & Min ER -->
                <div class="spec-item">
                  <div class="row g-2">
                    <div class="col-6">
                      <div class="spec-label">
                        <i class="fas fa-users text-info"></i> MIN. FOLLOWERS
                      </div>
                      <div class="fw-bold text-dark mt-1" style="font-size:13.5px;" id="detail-min-followers">—</div>
                    </div>
                    <div class="col-6 border-start ps-3">
                      <div class="spec-label">
                        <i class="fas fa-chart-pie text-success"></i> MIN. ER
                      </div>
                      <div class="fw-bold text-dark mt-1" style="font-size:13.5px;" id="detail-min-er">—</div>
                    </div>
                  </div>
                </div>

                <!-- Tier KOL yang Memenuhi Syarat -->
                <div class="spec-item">
                  <div class="spec-label">
                    <i class="fas fa-star text-warning"></i> TIER KOL YANG MEMENUHI SYARAT
                  </div>
                  <div class="d-flex flex-wrap gap-1 mt-1" id="detail-tiers-wrap">
                    <!-- Tier pills rendered via JS -->
                  </div>
                </div>

                <!-- Kategori KOL -->
                <div class="spec-item">
                  <div class="spec-label">
                    <i class="fas fa-tags text-primary"></i> KATEGORI KOL
                  </div>
                  <div class="d-flex flex-wrap gap-1 mt-1" id="detail-categories-wrap">
                    <!-- Category pills rendered via JS -->
                  </div>
                </div>

                <!-- Alokasi Budget Campaign -->
                <div class="spec-item">
                  <div class="spec-label">
                    <i class="fas fa-money-bill-wave text-success"></i> ALOKASI BUDGET CAMPAIGN
                  </div>
                  <div class="fw-bold text-success mt-1" style="font-size:14px;" id="detail-budget-campaign">—</div>
                </div>
              </div>
            </div>

            <!-- 5. Card: Evaluasi Profil Anda -->
            <div class="detail-content-card" style="border-top:3.5px solid #f59e0b;" id="detail-eval-card">
              <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                <div>
                  <h6 class="fw-bold mb-0" style="font-size:14px;color:#0f172a;">Evaluasi Profil Anda</h6>
                  <div style="font-size:11px;color:#64748b;">Kecocokan metrik akun Anda</div>
                </div>
                <span class="badge fw-semibold" id="detail-eval-badge" style="background:#f59e0b; color:#fff; font-size:11px; border-radius:6px; padding:4px 8px;">
                  —
                </span>
              </div>

              <!-- Progress bar -->
              <div class="p-2 rounded mb-2" style="background:#f8fafc; border:1px solid #e2e8f0;">
                <div class="d-flex justify-content-between mb-1" style="font-size:11.5px;color:#64748b;">
                  <span>Kesesuaian Syarat:</span>
                  <strong id="detail-eval-fraction" style="color:#d97706;">—</strong>
                </div>
                <div class="progress" style="height:6px; background:#e2e8f0; border-radius:4px; overflow:hidden;">
                  <div class="progress-bar" id="detail-eval-progress" style="width:0%; background:#f59e0b;"></div>
                </div>
              </div>

              <!-- Alert note -->
              <div class="p-2 rounded d-flex align-items-start gap-2 mb-2" id="detail-eval-note-box" style="background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-size:12px;">
                <i class="fas fa-info-circle text-warning mt-1"></i>
                <span id="detail-eval-note">Memuat evaluasi...</span>
              </div>

              <!-- Breakdown Kriteria Evaluasi Akun (.qc-list) -->
              <div class="qc-list" id="detail-qc-list">
                <!-- Checklist items populated via JS -->
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer px-4 py-2 bg-light border-top d-flex justify-content-between">
        <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal" style="border-radius:20px;font-size:13px;">
          Tutup
        </button>
        <div>
          <button type="button" class="btn btn-primary px-4 fw-semibold" id="detail-camp-apply-btn" onclick="applyFromDetailModal()" style="border-radius:20px;font-size:13px;background:#1f4b6e;border-color:#1f4b6e;">
            <i class="fas fa-paper-plane me-1"></i> Lamar Kampanye Ini
          </button>
          <span class="badge-applied" id="detail-camp-applied-badge" style="display:none !important;">
            <i class="fas fa-check-circle me-1"></i> Sudah Mendaftar
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Apply Modal -->
<div class="modal fade" id="modal-apply" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:10px;border:none;">
      <div class="modal-header" style="background:#172b4d;border-radius:10px 10px 0 0;">
        <h6 class="modal-title text-white fw-bold">
          <i class="fas fa-paper-plane me-2"></i>Ajukan Lamaran Kampanye
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 py-3">
        <div class="mb-3 p-3 rounded" style="background:#e8f1fb;">
          <div style="font-size:13px;color:#1f4b6e;font-weight:700;" id="modal-camp-title">—</div>
          <div style="font-size:12px;color:#52606d;">Budget Tersedia: <strong id="modal-camp-budget">—</strong></div>
        </div>
        <form method="post" action="<?= base_url('apply-campaign') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="campaign_id" id="apply-campaign-id">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Tarif yang Diajukan (Rp)</label>
            <input type="number" name="proposed_rate" class="form-control" placeholder="Contoh: 5000000" min="0" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Pitching / Catatan</label>
            <textarea name="pitch_notes" class="form-control" rows="3" placeholder="Ceritakan pengalaman & ide konten kamu..."></textarea>
          </div>
          <button type="submit" class="btn w-100 fw-bold" style="background:#1f4b6e;color:#fff;border-radius:8px;">
            <i class="fas fa-check me-1"></i> Kirim Lamaran
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
let currentDetailCampaign = null;

function openCampaignDetailModal(id) {
    const modalEl = document.getElementById('modal-campaign-detail');
    const modal = new bootstrap.Modal(modalEl);

    document.getElementById('detail-camp-title').textContent = 'Memuat Data Campaign...';
    document.getElementById('detail-desc').textContent = 'Memuat deskripsi...';
    document.getElementById('metric-period').textContent = '...';
    document.getElementById('metric-period-sub').textContent = '...';
    document.getElementById('metric-deadline').textContent = '...';
    document.getElementById('metric-deadline-sub').textContent = '...';
    document.getElementById('metric-slot-val').textContent = '...';
    document.getElementById('metric-slot-sub').textContent = '...';
    document.getElementById('metric-slot-prog').style.width = '0%';
    document.getElementById('metric-target-er').textContent = '...';
    document.getElementById('detail-products-grid').innerHTML = '<div class="col-12 text-muted small py-2"><i class="fas fa-spinner fa-spin me-1"></i> Memuat produk...</div>';
    document.getElementById('detail-objective').textContent = '...';
    document.getElementById('detail-target-audience').textContent = '...';
    document.getElementById('detail-hashtags-wrap').innerHTML = '...';
    document.getElementById('detail-platforms-wrap').innerHTML = '...';
    document.getElementById('detail-min-followers').textContent = '...';
    document.getElementById('detail-min-er').textContent = '...';
    document.getElementById('detail-tiers-wrap').innerHTML = '...';
    document.getElementById('detail-categories-wrap').innerHTML = '...';
    document.getElementById('detail-budget-campaign').textContent = '...';
    document.getElementById('detail-eval-badge').textContent = '...';
    document.getElementById('detail-eval-fraction').textContent = '...';
    document.getElementById('detail-eval-progress').style.width = '0%';
    document.getElementById('detail-eval-note').textContent = 'Memuat evaluasi profil...';
    document.getElementById('detail-qc-list').innerHTML = '<div class="text-muted small py-2"><i class="fas fa-spinner fa-spin me-1"></i> Memuat kriteria profil...</div>';
    document.getElementById('detail-camp-apply-btn').style.display = 'none';
    document.getElementById('detail-camp-applied-badge').style.setProperty('display', 'none', 'important');

    modal.show();

    fetch('<?= base_url('open-campaigns/detail') ?>/' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('detail-desc').innerHTML = '<div class="alert alert-danger py-2">' + (data.message || 'Gagal memuat kampanye.') + '</div>';
            return;
        }
        const c = data.campaign;
        currentDetailCampaign = c;

        document.getElementById('detail-camp-title').textContent = c.title || c.name || 'Detail Campaign';
        document.getElementById('detail-desc').textContent = c.description || 'Tidak ada deskripsi rinci.';

        // 4 Main Metrics (Periode, Batas Daftar, Kuota Slot, Target ER)
        document.getElementById('metric-period').textContent = c.period_range_fmt || '—';
        document.getElementById('metric-period-sub').textContent = c.period_sub || '—';

        document.getElementById('metric-deadline').textContent = c.application_deadline_fmt || '—';
        document.getElementById('metric-deadline-sub').textContent = c.deadline_sub || '—';

        document.getElementById('metric-slot-val').textContent = c.slot_val_fmt || '—';
        document.getElementById('metric-slot-sub').textContent = c.slot_sub || '—';
        document.getElementById('metric-slot-prog').style.width = (c.slot_pct !== undefined ? c.slot_pct : 0) + '%';
        if (c.is_slots_full) {
            document.getElementById('metric-slot-val').style.color = '#dc2626';
            document.getElementById('metric-slot-prog').style.background = '#ef4444';
        } else {
            document.getElementById('metric-slot-val').style.color = '#16a34a';
            document.getElementById('metric-slot-prog').style.background = '#10b981';
        }

        document.getElementById('metric-target-er').textContent = c.target_er_fmt || '—';

        // Products
        const prods = data.products || [];
        const prodsGrid = document.getElementById('detail-products-grid');
        document.getElementById('detail-products-header').textContent = 'PRODUK YANG DIGUNAKAN (' + prods.length + ' PRODUK)';
        if (prods.length === 0) {
            prodsGrid.innerHTML = '<div class="col-12 text-muted small py-2">Tidak ada produk spesifik yang ditugaskan pada kampanye ini.</div>';
        } else {
            let htmlProds = '';
            prods.forEach(p => {
                htmlProds += '<div class="col-sm-6">' +
                    '<div class="product-card-item">' +
                        '<div>' +
                            '<div class="product-card-title">' + escHtml(p.name) + '</div>' +
                            '<div class="product-card-meta">' +
                                '<span class="badge bg-secondary" style="font-size:9px;padding:2px 5px;">' + escHtml(p.sku) + '</span>' +
                                '<span>' + escHtml(p.category) + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="product-card-price">' + (p.price_fmt || 'Rp 0') + '</div>' +
                    '</div>' +
                '</div>';
            });
            prodsGrid.innerHTML = htmlProds;
        }

        // Guidelines
        document.getElementById('detail-objective').textContent = c.objective || 'sales';
        document.getElementById('detail-target-audience').textContent = c.target_audience || 'Semua kalangan';

        // Hashtags
        const hashWrap = document.getElementById('detail-hashtags-wrap');
        let hashtags = c.hashtags || [];
        if (hashtags.length === 0 && c.hashtag) {
            hashtags = c.hashtag.split(' ').filter(t => t.trim() !== '');
        }
        if (hashtags.length === 0) {
            hashWrap.innerHTML = '<span class="text-muted small">Tidak ada kewajiban hashtag khusus</span>';
        } else {
            let htmlHash = '';
            hashtags.forEach(h => {
                htmlHash += '<span class="hashtag-pill">' + escHtml(h) + '</span>';
            });
            hashWrap.innerHTML = htmlHash;
        }

        // SOW
        const sowBox = document.getElementById('detail-sow-box');
        const sowContent = document.getElementById('detail-sow-content');
        if (sowBox && sowContent) {
            if (c.sow) {
                sowBox.style.display = 'block';
                sowContent.innerHTML = '<a href="<?= base_url('uploads/campaigns') ?>/' + escHtml(c.sow) + '" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:12px;"><i class="fas fa-file-download me-1"></i> ' + escHtml(c.sow) + '</a>';
            } else {
                sowBox.style.display = 'none';
            }
        }

        // Qualification: Platforms
        const platWrap = document.getElementById('detail-platforms-wrap');
        const plats = data.required_platforms || [];
        if (plats.length === 0) {
            platWrap.innerHTML = '<span class="text-muted small">Terbuka untuk semua platform</span>';
        } else {
            let htmlPlat = '';
            plats.forEach(pl => {
                const pcode = (pl.name || '').toLowerCase();
                let pcls = 'camp-platform-tiktok';
                let picon = 'fab fa-tiktok';
                if (pcode.indexOf('instagram') !== -1) {
                    pcls = 'camp-platform-instagram';
                    picon = 'fab fa-instagram';
                } else if (pcode.indexOf('youtube') !== -1) {
                    pcls = 'camp-platform-youtube';
                    picon = 'fab fa-youtube';
                }
                htmlPlat += '<span class="camp-platform-chip ' + pcls + '"><i class="' + picon + '"></i> ' + escHtml(pl.name) + '</span>';
            });
            platWrap.innerHTML = htmlPlat;
        }

        // Min Followers & ER
        document.getElementById('detail-min-followers').textContent = c.min_followers_fmt || 'Bebas';
        document.getElementById('detail-min-er').textContent = c.min_er_fmt || 'Bebas';

        // Tiers
        const tiersWrap = document.getElementById('detail-tiers-wrap');
        const tiers = data.allowed_tiers || [];
        if (tiers.length === 0) {
            tiersWrap.innerHTML = '<span class="text-muted small">Semua Tier</span>';
        } else {
            let htmlTier = '';
            tiers.forEach(t => {
                htmlTier += '<span class="tier-pill">' + escHtml(t.name) + '</span>';
            });
            tiersWrap.innerHTML = htmlTier;
        }

        // Categories
        const catWrap = document.getElementById('detail-categories-wrap');
        const cats = data.allowed_categories || [];
        if (cats.length === 0) {
            catWrap.innerHTML = '<span class="text-muted small">Semua Kategori</span>';
        } else {
            let htmlCat = '';
            cats.forEach(cat => {
                htmlCat += '<span class="cat-pill">' + escHtml(cat.name) + '</span>';
            });
            catWrap.innerHTML = htmlCat;
        }

        // Budget
        document.getElementById('detail-budget-campaign').textContent = 'Rp ' + (c.budget_fmt || '0') + ' (' + (c.campaign_type || 'PAID') + ')';

        // Evaluation
        const ev = data.evaluation || {};
        const evalBadge = document.getElementById('detail-eval-badge');
        const evalFraction = document.getElementById('detail-eval-fraction');
        const evalProg = document.getElementById('detail-eval-progress');
        const evalNote = document.getElementById('detail-eval-note');
        const evalNoteBox = document.getElementById('detail-eval-note-box');
        const evalCard = document.getElementById('detail-eval-card');

        evalBadge.textContent = ev.summary || 'Kriteria Terpenuhi';
        const pct = ev.percentage !== undefined ? ev.percentage : 100;
        const passedCnt = ev.passed_count !== undefined ? ev.passed_count : 6;
        const totalCnt = ev.total_criteria !== undefined ? ev.total_criteria : 6;

        evalFraction.textContent = passedCnt + ' / ' + totalCnt + ' (' + pct + '%)';
        evalProg.style.width = pct + '%';
        evalNote.textContent = ev.note || 'Catatan kualifikasi.';

        if (ev.is_qualified) {
            evalCard.style.borderTopColor = '#10b981';
            evalBadge.style.background = '#10b981';
            evalFraction.style.color = '#059669';
            evalProg.style.background = '#10b981';
            evalNoteBox.style.background = '#ecfdf5';
            evalNoteBox.style.borderColor = '#a7f3d0';
            evalNoteBox.style.color = '#065f46';
            evalNoteBox.querySelector('i').className = 'fas fa-check-circle text-success mt-1';
        } else {
            evalCard.style.borderTopColor = '#f59e0b';
            evalBadge.style.background = '#f59e0b';
            evalFraction.style.color = '#d97706';
            evalProg.style.background = '#f59e0b';
            evalNoteBox.style.background = '#fffbeb';
            evalNoteBox.style.borderColor = '#fde68a';
            evalNoteBox.style.color = '#92400e';
            evalNoteBox.querySelector('i').className = 'fas fa-info-circle text-warning mt-1';
        }

        // Breakdown Kriteria Evaluasi Akun (.qc-list)
        const qcWrap = document.getElementById('detail-qc-list');
        const criteria = ev.criteria || [];
        if (criteria.length === 0) {
            qcWrap.innerHTML = '<div class="text-muted small">Data kriteria evaluasi belum tersedia.</div>';
        } else {
            let htmlQc = '';
            criteria.forEach(crit => {
                const isPassed = crit.passed;
                const itemCls = isPassed ? 'passed' : 'failed';
                const iconCls = isPassed ? 'pass' : 'fail';
                const iconFa = isPassed ? 'fa-check' : 'fa-times';
                const badgeCls = isPassed ? 'pass' : 'fail';
                const badgeText = isPassed ? 'Lulus' : 'Belum Sesuai';
                const colorActual = isPassed ? '#059669' : '#dc2626';

                htmlQc += '<div class="qc-item ' + itemCls + '">' +
                    '<div class="qc-icon-wrap ' + iconCls + '">' +
                        '<i class="fas ' + iconFa + '"></i>' +
                    '</div>' +
                    '<div class="qc-content">' +
                        '<div class="qc-header">' +
                            '<span class="qc-title">' + escHtml(crit.label) + '</span>' +
                            '<span class="qc-badge ' + badgeCls + '">' + badgeText + '</span>' +
                        '</div>' +
                        '<div class="qc-details">' +
                            '<div>Syarat: <strong>' + escHtml(crit.required) + '</strong></div>' +
                            '<div>Profil Anda: <span style="font-weight:600; color:' + colorActual + ';">' + escHtml(crit.actual) + '</span></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });
            qcWrap.innerHTML = htmlQc;
        }

        if (data.has_applied) {
            document.getElementById('detail-camp-applied-badge').style.setProperty('display', 'inline-flex', 'important');
            document.getElementById('detail-camp-apply-btn').style.display = 'none';
        } else {
            document.getElementById('detail-camp-applied-badge').style.setProperty('display', 'none', 'important');
            document.getElementById('detail-camp-apply-btn').style.display = 'inline-flex';
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('detail-desc').innerHTML = '<div class="alert alert-danger py-2">Terjadi gangguan koneksi saat memuat rincian kampanye.</div>';
    });
}

function applyFromDetailModal() {
    const modalEl = document.getElementById('modal-campaign-detail');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
    if (currentDetailCampaign) {
        openApplyModal(currentDetailCampaign.id, currentDetailCampaign.title, currentDetailCampaign.budget_total);
    }
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function openApplyModal(id, title, budget) {
    document.getElementById('apply-campaign-id').value = id;
    document.getElementById('modal-camp-title').textContent = title;
    document.getElementById('modal-camp-budget').textContent = 'Rp ' + Number(budget).toLocaleString('id-ID');
    new bootstrap.Modal(document.getElementById('modal-apply')).show();
}

// Filter chips
document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const f = this.dataset.filter;
        document.querySelectorAll('.camp-item').forEach(item => {
            item.style.display = (f === 'all' || item.dataset.filter === f) ? '' : 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>
