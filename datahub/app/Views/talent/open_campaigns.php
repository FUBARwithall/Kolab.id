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
.btn-lamar {
    background: #1f4b6e;
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 7px 20px;
    font-size: 13px;
    font-weight: 600;
    width: 100%;
    transition: background 0.15s;
}
.btn-lamar:hover { background: #17395a; color: #fff; }
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

                    <button type="button" class="btn-lamar"
                      onclick="openApplyModal(<?= (int)$camp['id'] ?>, '<?= esc(addslashes($camp['title'])) ?>', <?= (float)($camp['budget_total'] ?? 0) ?>)">
                      <i class="fas fa-paper-plane me-1"></i> Lamar Kampanye
                    </button>
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
function openApplyModal(id, title, budget) {
    document.getElementById('apply-campaign-id').value = id;
    document.getElementById('modal-camp-title').textContent = title;
    document.getElementById('modal-camp-budget').textContent = 'Rp ' + budget.toLocaleString('id-ID');
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
