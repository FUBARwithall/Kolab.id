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
.task-title { font-size: 15px; font-weight: 700; color: #172b4d; margin-bottom: 4px; }
.task-camp  { font-size: 12px; color: #2563eb; font-weight: 600; margin-bottom: 10px; }
.task-chip {
    display: inline-block;
    font-size: 11px; font-weight: 600;
    border-radius: 20px; padding: 3px 10px;
    margin-right: 4px;
}
.chip-draft          { background: #fff3e0; color: #b45309; }
.chip-draft_submitted{ background: #e8f1fb; color: #1f4b6e; }
.chip-draft_approved { background: #e3f9e5; color: #166534; }
.chip-posted         { background: #ede9fe; color: #5b21b6; }
.chip-completed      { background: #d1fae5; color: #065f46; }
.chip-fee { background: #f0fdf4; color: #166534; font-weight: 700; }
.chip-deadline { background: #fef9e7; color: #92400e; }

.empty-state { text-align:center; padding:60px 20px; }
.empty-state i { font-size:48px; color:#d0daea; margin-bottom:16px; display:block; }
.empty-state h6 { color:#172b4d; font-weight:700; font-size:16px; }
.empty-state p  { color:#6b778c; font-size:13px; }
</style>

<?php
// Hitung stat dari $tasks
$total      = count($tasks);
$draft      = count(array_filter($tasks, fn($t) => in_array($t['status'] ?? '', ['DRAFT', 'DRAFT_SUBMITTED'])));
$inReview   = count(array_filter($tasks, fn($t) => ($t['status'] ?? '') === 'DRAFT_APPROVED'));
$done       = count(array_filter($tasks, fn($t) => in_array($t['status'] ?? '', ['POSTED', 'COMPLETED'])));
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
        <div class="stat-lbl">Draft / Pending</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#ede9fe;color:#5b21b6;"><i class="fas fa-eye"></i></div>
      <div>
        <div class="stat-num"><?= $inReview ?></div>
        <div class="stat-lbl">Dalam Review</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#d1fae5;color:#065f46;"><i class="fas fa-check-circle"></i></div>
      <div>
        <div class="stat-num"><?= $done ?></div>
        <div class="stat-lbl">Selesai / Posted</div>
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
        <a href="#" class="filter-pill" data-filter="DRAFT">Draft</a>
        <a href="#" class="filter-pill" data-filter="DRAFT_SUBMITTED">Submitted</a>
        <a href="#" class="filter-pill" data-filter="DRAFT_APPROVED">Approved</a>
        <a href="#" class="filter-pill" data-filter="POSTED">Posted</a>
        <a href="#" class="filter-pill" data-filter="COMPLETED">Completed</a>
      </div>

      <!-- Cards -->
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
                $st = $task['status'] ?? 'DRAFT';
                $chipClass = match($st) {
                    'DRAFT'          => 'chip-draft',
                    'DRAFT_SUBMITTED'=> 'chip-draft_submitted',
                    'DRAFT_APPROVED' => 'chip-draft_approved',
                    'POSTED'         => 'chip-posted',
                    'COMPLETED'      => 'chip-completed',
                    default          => 'chip-draft',
                };
                $stLabel = match($st) {
                    'DRAFT'          => 'Draft',
                    'DRAFT_SUBMITTED'=> 'Submitted',
                    'DRAFT_APPROVED' => 'Approved',
                    'POSTED'         => 'Posted',
                    'COMPLETED'      => 'Completed',
                    default          => $st,
                };
                $isOverdue = !empty($task['deadline']) && strtotime($task['deadline']) < time() && !in_array($st, ['POSTED','COMPLETED']);
              ?>
              <div class="col-md-6 col-lg-4 task-item" data-filter="<?= esc($st) ?>">
                <div class="deliverable-card">
                  <span class="task-num-badge">
                    <i class="fas fa-hashtag me-1"></i>Tugas <?= (int)($task['task_number'] ?? 1) ?>
                  </span>
                  <div class="task-title"><?= esc($task['title'] ?? 'Deliverable Konten') ?></div>
                  <div class="task-camp">
                    <i class="fas fa-bullhorn me-1"></i><?= esc($task['campaign_title'] ?? '-') ?>
                  </div>
                  <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="task-chip <?= $chipClass ?>"><?= $stLabel ?></span>
                    <?php if (!empty($task['rate_amount']) && $task['rate_amount'] > 0): ?>
                      <span class="task-chip chip-fee">
                        Rp <?= number_format((float)$task['rate_amount'], 0, ',', '.') ?>
                      </span>
                    <?php endif; ?>
                    <?php if (!empty($task['deadline'])): ?>
                      <span class="task-chip chip-deadline <?= $isOverdue ? 'text-danger' : '' ?>">
                        <i class="fas fa-clock me-1"></i>
                        <?= date('d M Y', strtotime($task['deadline'])) ?>
                        <?= $isOverdue ? '⚠️' : '' ?>
                      </span>
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

<script>
document.querySelectorAll('.filter-pill').forEach(pill => {
    pill.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        const f = this.dataset.filter;
        document.querySelectorAll('.task-item').forEach(item => {
            item.style.display = (f === 'all' || item.dataset.filter === f) ? '' : 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>
