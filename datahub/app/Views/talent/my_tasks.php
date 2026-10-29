<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fas fa-tasks text-info me-2"></i> Daftar Tugas &amp; Deadline Konten Saya
          </h5>
          <small class="text-muted">Kelola deliverable konten, draft review, dan jadwal posting kampanye</small>
        </div>
        <span class="badge bg-info text-white px-3 py-2 rounded-pill fw-semibold">
          <?= count($tasks) ?> Tugas Aktif
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">No. Tugas &amp; Judul</th>
                <th>Kampanye</th>
                <th>Fee Deliverable</th>
                <th>Status Pengerjaan</th>
                <th>Batas Waktu (Deadline)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($tasks)): ?>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-2 text-success opacity-50"></i>
                    <div>Tidak ada tugas pending saat ini. Semua tugas telah terselesaikan!</div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                  <tr>
                    <td class="ps-3">
                      <div class="fw-bold text-dark">
                        <span class="badge bg-secondary me-1">#<?= (int) ($task['task_number'] ?? 1) ?></span>
                        <?= esc($task['title'] ?? 'Deliverable Konten Video') ?>
                      </div>
                    </td>
                    <td>
                      <small class="fw-semibold text-primary"><?= esc($task['campaign_title'] ?? '-') ?></small>
                    </td>
                    <td class="fw-bold text-dark">
                      Rp <?= number_format((float) ($task['rate_amount'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td>
                      <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 rounded-pill" style="font-size: 10px;">
                        <?= esc($task['status'] ?? 'DRAFT') ?>
                      </span>
                    </td>
                    <td>
                      <small class="text-danger fw-semibold">
                        <i class="fas fa-calendar-alt me-1"></i> <?= !empty($task['deadline']) && $task['deadline'] !== '0000-00-00' ? esc(date('d M Y', strtotime($task['deadline']))) : '-' ?>
                      </small>
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
</div>

<?= $this->endSection() ?>
