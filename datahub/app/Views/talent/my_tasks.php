<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
.section-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    border: none;
}
.card-header-custom {
    background: #fff;
    border-bottom: 1.5px solid #e9eef6;
    border-radius: 10px 10px 0 0;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #172b4d;
    margin-bottom: 2px;
}
.section-subtitle { font-size: 13px; color: #6b778c; }
.count-badge {
    background: #e8f1fb;
    color: #1f4b6e;
    font-weight: 700;
    font-size: 13px;
    border-radius: 20px;
    padding: 4px 14px;
}
/* Stat cards */
.stat-card {
    background: #fff;
    border-radius: 10px;
    border: 1.5px solid #e4ebf5;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    height: 100%;
}
.stat-card .stat-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-card .stat-num  { font-size: 26px; font-weight: 800; color: #172b4d; line-height: 1; }
.stat-card .stat-lbl  { font-size: 12px; color: #6b778c; margin-top: 2px; }

/* Filter pills */
.filter-pills {
    display: flex; gap: 8px; flex-wrap: wrap;
    padding: 14px 24px;
    border-bottom: 1px solid #e9eef6;
}
.filter-pill {
    background: #f0f4fa; color: #52606d;
    border: 1px solid #dbe3ef;
    border-radius: 20px; padding: 4px 14px;
    font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s;
    text-decoration: none;
}
.filter-pill:hover, .filter-pill.active {
    background: #172b4d; color: #fff; border-color: #172b4d;
}

/* Deliverable card */
.deliverable-card {
    background: #fff;
    border-radius: 10px;
    border: 1.5px solid #e4ebf5;
    box-shadow: 0 1px 6px rgba(23,43,77,0.05);
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: box-shadow 0.15s, transform 0.15s;
}
.deliverable-card:hover {
    box-shadow: 0 4px 16px rgba(23,43,77,0.11);
    transform: translateY(-2px);
}
.task-num-badge {
    display: inline-block;
    font-size: 10px; font-weight: 700;
    background: #e8f1fb; color: #1f4b6e;
    border-radius: 20px; padding: 2px 10px;
    margin-bottom: 8px;
}
.task-title { font-size: 15px; font-weight: 700; color: #172b4d; margin-bottom: 4px; line-height: 1.35; }
.task-camp  { font-size: 12px; color: #2563eb; font-weight: 600; margin-bottom: 10px; }
.task-chip {
    display: inline-block;
    font-size: 11px; font-weight: 600;
    border-radius: 20px; padding: 3px 10px;
    margin-right: 4px;
    margin-bottom: 4px;
}
.chip-draft          { background: #fff3e0; color: #b45309; }
.chip-draft_submitted{ background: #e8f1fb; color: #1f4b6e; }
.chip-draft_approved { background: #e3f9e5; color: #166534; }
.chip-posted         { background: #ede9fe; color: #5b21b6; }
.chip-completed      { background: #d1fae5; color: #065f46; }
.chip-fee { background: #f0fdf4; color: #166534; font-weight: 700; }
.chip-deadline { background: #fef9e7; color: #92400e; }

.deliverable-actions {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px dashed #e2e8f0;
}

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

.empty-state { text-align:center; padding:60px 20px; }
.empty-state i { font-size:48px; color:#d0daea; margin-bottom:16px; display:block; }
.empty-state h6 { color:#172b4d; font-weight:700; font-size:16px; }
.empty-state p  { color:#6b778c; font-size:13px; }
</style>

<?php
// Hitung stat dari $tasks
$total    = count($tasks);
$draft    = count(array_filter($tasks, fn($t) => in_array($t['status'] ?? '', ['DRAFT', 'WAITING_DRAFT', 'BRIEF_APPROVED', 'BRIEF_SUBMITTED', 'REVISION_DRAFT', 'DRAFT_REVISION'])));
$inReview = count(array_filter($tasks, fn($t) => in_array($t['status'] ?? '', ['DRAFT_SUBMITTED', 'IN_REVIEW_DRAFT', 'PENDING_REVIEW'])));
$done     = count(array_filter($tasks, fn($t) => in_array($t['status'] ?? '', ['DRAFT_APPROVED', 'APPROVED_DRAFT', 'POSTED', 'COMPLETED', 'DONE'])));
?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e8f1fb;color:#1f4b6e;"><i class="fas fa-tasks"></i></div>
      <div>
        <div class="stat-num"><?= $total ?></div>
        <div class="stat-lbl">Total Tugas</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fff3e0;color:#b45309;"><i class="fas fa-pencil-alt"></i></div>
      <div>
        <div class="stat-num"><?= $draft ?></div>
        <div class="stat-lbl">Perlu Upload Draft</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#ede9fe;color:#5b21b6;"><i class="fas fa-eye"></i></div>
      <div>
        <div class="stat-num"><?= $inReview ?></div>
        <div class="stat-lbl">Dalam Review Brand</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#d1fae5;color:#065f46;"><i class="fas fa-check-circle"></i></div>
      <div>
        <div class="stat-num"><?= $done ?></div>
        <div class="stat-lbl">Disetujui / Selesai</div>
      </div>
    </div>
  </div>
</div>

<!-- Main card -->
<div class="row mb-4">
  <div class="col-12">
    <div class="section-card">

      <!-- Header -->
      <div class="card-header-custom">
        <div>
          <div class="section-title"><i class="fas fa-film me-2" style="color:#5b21b6;"></i> Daftar Tugas &amp; Deliverable</div>
          <div class="section-subtitle">Kelola draft konten, review, dan jadwal posting kampanye</div>
        </div>
        <span class="count-badge"><?= $total ?> Tugas</span>
      </div>

      <!-- Filter pills -->
      <div class="filter-pills">
        <a href="#" class="filter-pill active" data-filter="all">Semua</a>
        <a href="#" class="filter-pill" data-filter="DRAFT">Perlu Draft</a>
        <a href="#" class="filter-pill" data-filter="IN_REVIEW">Sedang Direview</a>
        <a href="#" class="filter-pill" data-filter="APPROVED">Disetujui</a>
        <a href="#" class="filter-pill" data-filter="POSTED">Sudah Tayang</a>
      </div>

      <!-- Cards Grid -->
      <div class="p-4">
        <?php if (empty($tasks)): ?>
          <div class="empty-state">
            <i class="fas fa-check-double"></i>
            <h6>Tidak Ada Tugas Aktif</h6>
            <p>Semua tugas telah selesai, atau belum ada penugasan baru.</p>
          </div>
        <?php else: ?>
          <div class="row g-3" id="task-list">
            <?php foreach ($tasks as $task): ?>
              <?php
                $st = strtoupper((string)($task['status'] ?? 'DRAFT'));

                // Normalisasi status kelompok
                $isDraftWaiting = in_array($st, ['DRAFT', 'WAITING_DRAFT', 'BRIEF_APPROVED', 'BRIEF_SUBMITTED', 'REVISION_DRAFT', 'DRAFT_REVISION']);
                $isUnderReview  = in_array($st, ['DRAFT_SUBMITTED', 'IN_REVIEW_DRAFT', 'PENDING_REVIEW']);
                $isApproved     = in_array($st, ['DRAFT_APPROVED', 'APPROVED_DRAFT']);
                $isPosted       = in_array($st, ['POSTED', 'COMPLETED', 'DONE']);
                $isRevision     = in_array($st, ['REVISION_DRAFT', 'DRAFT_REVISION']);

                $filterGroup = match(true) {
                    $isDraftWaiting => 'DRAFT',
                    $isUnderReview  => 'IN_REVIEW',
                    $isApproved     => 'APPROVED',
                    $isPosted       => 'POSTED',
                    default         => 'DRAFT',
                };

                $chipClass = match(true) {
                    $isDraftWaiting => 'chip-draft',
                    $isUnderReview  => 'chip-draft_submitted',
                    $isApproved     => 'chip-draft_approved',
                    $isPosted       => 'chip-posted',
                    default         => 'chip-draft',
                };

                $stLabel = match(true) {
                    $isRevision     => 'Perlu Revisi Draft',
                    $isDraftWaiting => 'Menunggu Draft Video',
                    $isUnderReview  => 'Sedang Ditinjau Brand',
                    $isApproved     => 'Disetujui • Siap Posting',
                    $isPosted       => 'Postingan Live Selesai',
                    default         => $st,
                };

                $isOverdue = !empty($task['deadline']) && strtotime($task['deadline']) < time() && !$isPosted;
                $nextVer   = (int)($task['next_draft_version'] ?? 1);
              ?>
              <div class="col-md-6 col-lg-4 task-item" data-filter="<?= $filterGroup ?>">
                <div class="deliverable-card">
                  <div>
                    <span class="task-num-badge">
                      <i class="fas fa-hashtag me-1"></i>Tugas <?= (int)($task['task_number'] ?? 1) ?>
                    </span>
                    <div class="task-title"><?= esc($task['title'] ?? 'Deliverable Konten') ?></div>
                    <div class="task-camp">
                      <i class="fas fa-bullhorn me-1"></i><?= esc($task['campaign_title'] ?? '-') ?>
                    </div>
                  </div>

                  <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="task-chip <?= $chipClass ?>"><?= $stLabel ?></span>
                    <?php if (!empty($task['rate_amount']) && $task['rate_amount'] > 0): ?>
                      <span class="task-chip chip-fee">
                        Rp <?= number_format((float)$task['rate_amount'], 0, ',', '.') ?>
                      </span>
                    <?php endif; ?>
                    <?php if (!empty($task['deadline'])): ?>
                      <span class="task-chip chip-deadline <?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                        <i class="fas fa-clock me-1"></i>
                        <?= date('d M Y', strtotime($task['deadline'])) ?>
                        <?= $isOverdue ? '⚠️ Terlewat' : '' ?>
                      </span>
                    <?php endif; ?>
                  </div>

                  <!-- Deliverable Actions -->
                  <div class="deliverable-actions">
                    <!-- Action: Lihat Detail Campaign & Brief -->
                    <button type="button" class="btn btn-sm w-100 fw-semibold mb-2 btn-open-camp-detail"
                      onclick="openCampaignDetailModal(<?= (int)$task['campaign_id'] ?>, '<?= esc(addslashes($task['campaign_title'] ?? 'Detail Campaign')) ?>', '<?= esc(addslashes($task['brief_note'] ?? '')) ?>', '<?= esc(addslashes($task['brief_file'] ?? '')) ?>')"
                      style="border-radius: 8px; font-size: 12.5px; border: 1.5px solid #cbd5e1; color: #1e293b; background: #f8fafc; transition: all 0.15s;">
                      <i class="fas fa-eye text-primary me-1"></i> Detail Campaign &amp; Brief
                    </button>

                    <?php if ($isDraftWaiting): ?>
                      <!-- Action: Upload Draft Video / Revisi -->
                      <button type="button" class="btn btn-sm btn-primary w-100 fw-bold btn-open-modal-draft"
                        data-id="<?= (int)$task['id'] ?>"
                        data-title="<?= esc($task['title']) ?>"
                        data-version="<?= $nextVer ?>"
                        data-is-revision="<?= $isRevision ? '1' : '0' ?>"
                        style="background: #2563eb; border-color: #1d4ed8; border-radius: 8px; padding: 8px 14px; font-size: 13px;">
                        <i class="fas fa-cloud-upload-alt me-1"></i>
                        <?= $isRevision ? 'Upload Revisi (v' . $nextVer . ')' : 'Upload Draft Video' ?>
                      </button>
                    <?php elseif ($isUnderReview): ?>
                      <!-- Action: In review state -->
                      <div class="text-center p-2 rounded-2" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                        <div style="font-size: 12px; font-weight: 700; color: #1e40af;">
                          <i class="fas fa-clock me-1"></i> Menunggu Konfirmasi Review Brand
                        </div>
                        <?php if (!empty($task['draft_url'])): ?>
                          <a href="<?= esc($task['draft_url']) ?>" target="_blank" class="btn btn-sm btn-link text-primary p-0 mt-1" style="font-size: 11.5px; text-decoration: none;">
                            <i class="fas fa-external-link-alt me-1"></i> Lihat Link Draft yang Dikirim
                          </a>
                        <?php endif; ?>
                      </div>
                    <?php elseif ($isApproved || $isPosted): ?>
                      <!-- Action: Submit / Update Live URL -->
                      <button type="button" class="btn btn-sm btn-success w-100 fw-bold btn-open-modal-live mb-1"
                        data-id="<?= (int)$task['id'] ?>"
                        data-title="<?= esc($task['title']) ?>"
                        data-url="<?= esc($task['live_url'] ?? '') ?>"
                        data-platform="<?= esc($task['platform_name'] ?? 'Social Media') ?>"
                        style="background: #059669; border-color: #047857; border-radius: 8px; padding: 8px 14px; font-size: 13px;">
                        <i class="fas fa-link me-1"></i>
                        <?= !empty($task['live_url']) ? 'Perbarui Link Live' : 'Submit Link Postingan Live' ?>
                      </button>
                      <?php if (!empty($task['live_url'])): ?>
                        <a href="<?= esc($task['live_url']) ?>" target="_blank" class="btn btn-sm btn-outline-success w-100 py-1" style="font-size: 11.5px; border-radius: 6px;">
                          <i class="fas fa-external-link-alt me-1"></i> Buka Postingan Live Tayang
                        </a>
                      <?php endif; ?>
                    <?php endif; ?>
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

<!-- ============================================================================== -->
<!-- MODAL: UPLOAD DRAFT VIDEO (KOL) -->
<!-- ============================================================================== -->
<div class="modal fade" id="modalSubmitDraft" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.18);">
      <div class="modal-header" style="background: #0f172a; color: #ffffff; padding: 14px 20px;">
        <h5 class="modal-title mb-0 fw-bold" style="font-size: 15px;" id="draftModalHeader">
          <i class="fas fa-cloud-upload-alt text-primary me-2"></i> Upload Draft Video
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formKolsSubmitDraft">
        <?= csrf_field() ?>
        <input type="hidden" name="task_id" id="draft_task_id">

        <div class="modal-body" style="padding: 20px;">
          <!-- Task Info Badge -->
          <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 12.5px; color: #1e40af;">
            Task: <strong id="draftTaskTitle">-</strong> <span class="badge bg-primary ms-1" id="draftVersionBadge">v1</span>
          </div>

          <!-- Input Draft URL -->
          <div class="mb-3">
            <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #1e293b;">
              Link Draft Video (Google Drive / Preview Link) <span class="text-danger">*</span>
            </label>
            <input type="url" class="form-control" name="draft_url" id="input_draft_url" placeholder="https://drive.google.com/file/d/... atau tautan video preview" required style="border-radius: 6px;">
            <small class="text-muted" style="font-size: 11px; margin-top: 4px; display: block;">
              Pastikan akses sharing Google Drive telah diatur ke <strong>"Anyone with the link can view"</strong> agar tim brand dapat meninjau video.
            </small>
          </div>

          <!-- Input Caption & Hashtag -->
          <div class="mb-3">
            <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #1e293b;">
              Rencana Caption &amp; Hashtag <small class="text-muted font-normal">(Opsional)</small>
            </label>
            <textarea class="form-control" name="caption" id="input_draft_caption" rows="3" placeholder="Tuliskan draft caption dan hashtag yang akan digunakan pada postingan..." style="border-radius: 6px; font-size: 13px;"></textarea>
          </div>

          <!-- Input Catatan untuk Brand -->
          <div class="mb-2">
            <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #1e293b;">
              Catatan untuk Tim Brand <small class="text-muted font-normal">(Opsional)</small>
            </label>
            <input type="text" class="form-control" name="notes" id="input_draft_notes" placeholder="Contoh: Sudah disesuaikan hook di awal video sesuai brief" style="border-radius: 6px; font-size: 13px;">
          </div>
        </div>

        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px;">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 rounded-3" id="btnDraftSubmit" style="background: #2563eb; border-color: #1d4ed8;">
            <i class="fas fa-paper-plane me-1"></i> Kirim Draft
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL: SUBMIT LINK LIVE POSTING -->
<!-- ============================================================================== -->
<div class="modal fade" id="modalSubmitLive" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.18);">
      <div class="modal-header" style="background: #065f46; color: #ffffff; padding: 14px 20px;">
        <h5 class="modal-title mb-0 fw-bold" style="font-size: 15px;">
          <i class="fas fa-link text-white me-2"></i> Submit Link Postingan Live
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formKolsSubmitLive">
        <?= csrf_field() ?>
        <input type="hidden" name="task_id" id="live_task_id">

        <div class="modal-body" style="padding: 20px;">
          <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 12.5px; color: #065f46;">
            Task: <strong id="liveTaskTitle">-</strong> &bull; Platform: <strong id="livePlatformLabel">Social Media</strong>
          </div>

          <div class="mb-2">
            <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #1e293b;">
              Link Postingan Video Live <span class="text-danger">*</span>
            </label>
            <input type="url" class="form-control" name="live_url" id="input_live_url" placeholder="https://www.tiktok.com/@username/video/... atau https://instagram.com/p/..." required style="border-radius: 6px;">
            <small class="text-muted" style="font-size: 11px; margin-top: 4px; display: block;">
              Tempelkan tautan URL postingan yang sudah tayang secara publik di platform media sosial Anda.
            </small>
          </div>
        </div>

        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px;">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success btn-sm fw-bold px-3 rounded-3" id="btnLiveSubmit" style="background: #059669; border-color: #047857;">
            <i class="fas fa-check-circle me-1"></i> Simpan Link Live
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL: DETAIL CAMPAIGN & BRIEF (SESUAI SISTEM SEBELUMNYA) -->
<!-- ============================================================================== -->
<div class="modal fade" id="modal-campaign-detail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 12px 36px rgba(15,23,42,0.18);overflow:hidden;">
      <div class="modal-header py-3 px-4" style="background:#172b4d;color:#fff;">
        <div class="d-flex align-items-center gap-2">
          <i class="fas fa-bullhorn text-warning fs-5"></i>
          <div>
            <h6 class="modal-title fw-bold text-white mb-0" id="detail-camp-title">Detail Campaign &amp; Brief</h6>
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

                <!-- Catatan Brief Khusus Task (dari Tugas Saya jika ada) -->
                <div class="spec-item" id="detail-task-note-box" style="display:none;">
                  <div class="spec-label">
                    <i class="fas fa-comment-dots text-warning"></i> CATATAN BRIEF KHUSUS PENUGASAN INI
                  </div>
                  <div class="spec-val" id="detail-task-note-content" style="color:#0f172a; font-weight:500;"></div>
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
      </div>
    </div>
  </div>
</div>

<script>
(function() {
    'use strict';

    // Filter pills
    document.querySelectorAll('.filter-pill').forEach(function(pill) {
        pill.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.filter-pill').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            var f = this.dataset.filter;
            document.querySelectorAll('.task-item').forEach(function(item) {
                item.style.display = (f === 'all' || item.dataset.filter === f) ? '' : 'none';
            });
        });
    });

    // Open Modal Draft
    document.querySelectorAll('.btn-open-modal-draft').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var title = this.dataset.title;
            var ver = this.dataset.version || '1';
            var isRev = this.dataset.isRevision === '1';

            document.getElementById('draft_task_id').value = id;
            document.getElementById('draftTaskTitle').textContent = title;
            document.getElementById('draftVersionBadge').textContent = 'v' + ver;
            document.getElementById('draftModalHeader').innerHTML = '<i class="fas fa-cloud-upload-alt text-primary me-2"></i> ' + (isRev ? 'Upload Revisi Draft Video' : 'Upload Draft Video');
            document.getElementById('input_draft_url').value = '';
            document.getElementById('input_draft_caption').value = '';
            document.getElementById('input_draft_notes').value = '';

            var modalEl = document.getElementById('modalSubmitDraft');
            new bootstrap.Modal(modalEl).show();
        });
    });

    // Handle Draft Submit AJAX
    var formDraft = document.getElementById('formKolsSubmitDraft');
    if (formDraft) {
        formDraft.addEventListener('submit', function(e) {
            e.preventDefault();

            var btn = document.getElementById('btnDraftSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengirim...';

            var formData = new FormData(this);

            fetch('<?= base_url('my-tasks/submit-draft') ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Kirim Draft';

                if (res.success) {
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res.message || 'Gagal mengirim draft.');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Kirim Draft';
                alert('Terjadi kesalahan jaringan.');
            });
        });
    }

    // Open Modal Live
    document.querySelectorAll('.btn-open-modal-live').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var title = this.dataset.title;
            var url = this.dataset.url || '';
            var platform = this.dataset.platform || 'Social Media';

            document.getElementById('live_task_id').value = id;
            document.getElementById('liveTaskTitle').textContent = title;
            document.getElementById('livePlatformLabel').textContent = platform;
            document.getElementById('input_live_url').value = url;

            var modalEl = document.getElementById('modalSubmitLive');
            new bootstrap.Modal(modalEl).show();
        });
    });

    // Handle Live Submit AJAX
    var formLive = document.getElementById('formKolsSubmitLive');
    if (formLive) {
        formLive.addEventListener('submit', function(e) {
            e.preventDefault();

            var btn = document.getElementById('btnLiveSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            var formData = new FormData(this);

            fetch('<?= base_url('my-tasks/submit-live') ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Simpan Link Live';

                if (res.success) {
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res.message || 'Gagal menyimpan link live.');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Simpan Link Live';
                alert('Terjadi kesalahan jaringan.');
            });
        });
    }

})();

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function openCampaignDetailModal(id, customTitle, taskNote, briefFile) {
    var modalEl = document.getElementById('modal-campaign-detail');
    var modal = new bootstrap.Modal(modalEl);

    // Reset to loading state
    document.getElementById('detail-camp-title').textContent = customTitle || 'Memuat Data Campaign...';
    document.getElementById('detail-desc').textContent = 'Memuat rincian deskripsi...';
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
    document.getElementById('detail-qc-list').innerHTML = '<div class="text-muted small py-2"><i class="fas fa-spinner fa-spin me-1"></i> Memuat breakdown kriteria...</div>';

    // Reset metric tiles
    document.getElementById('metric-period').textContent = '...';
    document.getElementById('metric-period-sub').textContent = '...';
    document.getElementById('metric-deadline').textContent = '...';
    document.getElementById('metric-deadline-sub').textContent = '...';
    document.getElementById('metric-slot-val').textContent = '...';
    document.getElementById('metric-slot-prog').style.width = '0%';
    document.getElementById('metric-slot-sub').textContent = '...';
    document.getElementById('metric-target-er').textContent = '...';

    // Show task notes if passed
    var taskNoteBox = document.getElementById('detail-task-note-box');
    var taskNoteContent = document.getElementById('detail-task-note-content');
    if (taskNoteBox && taskNoteContent) {
        if (taskNote && taskNote.trim() !== '') {
            taskNoteBox.style.display = 'block';
            taskNoteContent.textContent = taskNote;
        } else {
            taskNoteBox.style.display = 'none';
        }
    }

    modal.show();

    fetch('<?= base_url('open-campaigns/detail') ?>/' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) {
            document.getElementById('detail-desc').innerHTML = '<div class="alert alert-danger py-2">' + (res.message || 'Gagal memuat rincian.') + '</div>';
            return;
        }

        var c = res.campaign;
        document.getElementById('detail-camp-title').textContent = c.title || c.name || 'Detail Campaign';
        document.getElementById('detail-desc').textContent = c.description || 'Tidak ada deskripsi rinci.';

        // 4 Metric Tiles: Periode, Batas Daftar, Slot, Target ER
        document.getElementById('metric-period').textContent = c.period_range_fmt || (c.start_date + ' - ' + c.end_date);
        document.getElementById('metric-period-sub').textContent = c.period_sub || 'Sesuai jadwal';
        document.getElementById('metric-deadline').textContent = c.application_deadline_fmt || 'Fleksibel';
        document.getElementById('metric-deadline').style.color = (c.application_deadline ? '#dc2626' : '#0f172a');
        document.getElementById('metric-deadline-sub').textContent = c.deadline_sub || 'Tanpa batas waktu';
        document.getElementById('metric-slot-val').textContent = c.slot_val_fmt || (c.accepted_count + ' Terisi');
        document.getElementById('metric-slot-val').style.color = (c.is_slots_full ? '#dc2626' : '#16a34a');
        document.getElementById('metric-slot-prog').style.width = (c.slot_pct || 0) + '%';
        document.getElementById('metric-slot-prog').style.background = (c.is_slots_full ? '#dc2626' : '#10b981');
        document.getElementById('metric-slot-sub').textContent = c.slot_sub || 'Slot tersedia';
        document.getElementById('metric-target-er').textContent = c.target_er_fmt || '-';

        // Products
        var prods = res.products || [];
        var prodsGrid = document.getElementById('detail-products-grid');
        document.getElementById('detail-products-header').textContent = 'PRODUK YANG DIGUNAKAN (' + prods.length + ' PRODUK)';
        if (prods.length === 0) {
            prodsGrid.innerHTML = '<div class="col-12 text-muted small py-2">Tidak ada produk spesifik yang ditugaskan pada kampanye ini.</div>';
        } else {
            var htmlProds = '';
            prods.forEach(function(p) {
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
        var hashWrap = document.getElementById('detail-hashtags-wrap');
        var hashtags = c.hashtags || [];
        if (hashtags.length === 0 && c.hashtag) {
            hashtags = c.hashtag.split(' ').filter(function(t) { return t.trim() !== ''; });
        }
        if (hashtags.length === 0) {
            hashWrap.innerHTML = '<span class="text-muted small">Tidak ada kewajiban hashtag khusus</span>';
        } else {
            var htmlHash = '';
            hashtags.forEach(function(h) {
                htmlHash += '<span class="hashtag-pill">' + escHtml(h) + '</span>';
            });
            hashWrap.innerHTML = htmlHash;
        }

        // SOW / Brief File
        var sowBox = document.getElementById('detail-sow-box');
        var sowContent = document.getElementById('detail-sow-content');
        var fileToDownload = briefFile || c.sow;
        if (sowBox && sowContent) {
            if (fileToDownload) {
                sowBox.style.display = 'block';
                var fileUrl = briefFile ? ('<?= base_url('uploads/tasks') ?>/' + escHtml(briefFile)) : ('<?= base_url('uploads/campaigns') ?>/' + escHtml(c.sow));
                sowContent.innerHTML = '<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:12px;"><i class="fas fa-file-download me-1"></i> ' + escHtml(fileToDownload) + '</a>';
            } else {
                sowBox.style.display = 'none';
            }
        }

        // Qualification: Platforms
        var platWrap = document.getElementById('detail-platforms-wrap');
        var plats = res.required_platforms || [];
        if (plats.length === 0) {
            platWrap.innerHTML = '<span class="text-muted small">Terbuka untuk semua platform</span>';
        } else {
            var htmlPlat = '';
            plats.forEach(function(pl) {
                var pcode = (pl.name || '').toLowerCase();
                var pcls = 'camp-platform-tiktok';
                var picon = 'fab fa-tiktok';
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
        var tiersWrap = document.getElementById('detail-tiers-wrap');
        var tiers = res.allowed_tiers || [];
        if (tiers.length === 0) {
            tiersWrap.innerHTML = '<span class="text-muted small">Semua Tier</span>';
        } else {
            var htmlTier = '';
            tiers.forEach(function(t) {
                htmlTier += '<span class="tier-pill">' + escHtml(t.name) + '</span>';
            });
            tiersWrap.innerHTML = htmlTier;
        }

        // Categories
        var catWrap = document.getElementById('detail-categories-wrap');
        var cats = res.allowed_categories || [];
        if (cats.length === 0) {
            catWrap.innerHTML = '<span class="text-muted small">Semua Kategori</span>';
        } else {
            var htmlCat = '';
            cats.forEach(function(cat) {
                htmlCat += '<span class="cat-pill">' + escHtml(cat.name) + '</span>';
            });
            catWrap.innerHTML = htmlCat;
        }

        // Budget
        document.getElementById('detail-budget-campaign').textContent = 'Rp ' + (c.budget_fmt || '0') + ' (' + (c.campaign_type || 'PAID') + ')';

        // Evaluation Summary
        var ev = res.evaluation || {};
        var evalBadge = document.getElementById('detail-eval-badge');
        var evalFraction = document.getElementById('detail-eval-fraction');
        var evalProg = document.getElementById('detail-eval-progress');
        var evalNote = document.getElementById('detail-eval-note');
        var evalNoteBox = document.getElementById('detail-eval-note-box');
        var evalCard = document.getElementById('detail-eval-card');

        evalBadge.textContent = ev.summary || 'Kriteria Terpenuhi';
        var pct = ev.percentage !== undefined ? ev.percentage : 100;
        var passedCnt = ev.passed_count !== undefined ? ev.passed_count : 6;
        var totalCnt = ev.total_criteria !== undefined ? ev.total_criteria : 6;

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
        var qcWrap = document.getElementById('detail-qc-list');
        var criteria = ev.criteria || [];
        if (criteria.length === 0) {
            qcWrap.innerHTML = '<div class="text-muted small">Data kriteria evaluasi belum tersedia.</div>';
        } else {
            var htmlQc = '';
            criteria.forEach(function(crit) {
                var isPassed = crit.passed;
                var itemCls = isPassed ? 'passed' : 'failed';
                var iconCls = isPassed ? 'pass' : 'fail';
                var iconFa = isPassed ? 'fa-check' : 'fa-times';
                var badgeCls = isPassed ? 'pass' : 'fail';
                var badgeText = isPassed ? 'Lulus' : 'Belum Sesuai';
                var colorActual = isPassed ? '#059669' : '#dc2626';

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
    })
    .catch(function(err) {
        console.error(err);
        document.getElementById('detail-desc').innerHTML = '<div class="alert alert-danger py-2">Gagal menghubungi server untuk memuat detail campaign.</div>';
    });
}
</script>

<?= $this->endSection() ?>
