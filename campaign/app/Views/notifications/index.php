<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
.notif-container {
    max-width: 960px;
    margin: 0 auto;
}
.notif-card-header {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 1px 3px rgba(15,23,42,0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 18px;
}
.notif-filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.notif-filter-chip {
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.notif-filter-chip:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.notif-filter-chip.active {
    background: #1f4b6e;
    color: #ffffff;
    border-color: #1f4b6e;
}
.notif-filter-chip .chip-count {
    background: rgba(0,0,0,0.08);
    font-size: 11px;
    padding: 1px 6px;
    border-radius: 10px;
}
.notif-filter-chip.active .chip-count {
    background: rgba(255,255,255,0.25);
    color: #fff;
}
.notif-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 10px;
    box-shadow: 0 1px 2px rgba(15,23,42,0.04);
    transition: all 0.15s ease;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.notif-item:hover {
    box-shadow: 0 4px 12px rgba(15,23,42,0.08);
    border-color: #cbd5e1;
}
.notif-item.is-unread {
    background: #f8fafc;
    border-left: 4px solid #2563eb;
}
.notif-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.notif-content {
    flex-grow: 1;
    min-width: 0;
}
.notif-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 4px;
}
.notif-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
}
.notif-unread-dot {
    width: 7px;
    height: 7px;
    background: #2563eb;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.notif-message {
    font-size: 12.5px;
    color: #334155;
    line-height: 1.45;
    margin-bottom: 8px;
}
.notif-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: #94a3b8;
}
.notif-empty {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
}
</style>

<div class="notif-container">
  <!-- Card Header -->
  <div class="notif-card-header">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
      <div>
        <div class="d-flex align-items-center gap-2">
          <h4 class="fw-bold mb-0 text-dark" style="font-size:20px;">
            <i class="fas fa-bell text-warning me-1"></i> Pusat Pemberitahuan
          </h4>
          <?php if ($unreadCount > 0): ?>
            <span class="badge bg-danger rounded-pill px-2.5 py-1" id="unread-count-badge" style="font-size:11px;">
              <?= $unreadCount ?> Baru
            </span>
          <?php endif; ?>
        </div>
        <div class="text-muted mt-1" style="font-size:12.5px;">
          Pantau update aktivitas campaign, brief video, approval kontrak, shipment, dan invoice.
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3 py-1.5 rounded-3" onclick="markAllReadPage()">
          <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
        </button>
        <button type="button" class="btn btn-sm btn-light border fw-semibold px-3 py-1.5 rounded-3 text-secondary" onclick="window.location.reload()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
      </div>
    </div>

    <!-- Search box -->
    <div class="position-relative mt-3">
      <i class="fas fa-search position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);font-size:13px;"></i>
      <input type="text" id="notifSearchInput" class="form-control rounded-3 ps-5 py-2" placeholder="Cari notifikasi berdasarkan campaign, task, invoice, dll..." style="font-size:13px;border-color:#cbd5e1;">
    </div>
  </div>

  <!-- Filter chips -->
  <div class="notif-filter-chips">
    <a href="javascript:;" class="notif-filter-chip active" data-filter="all">
      Semua <span class="chip-count"><?= $totalCount ?></span>
    </a>
    <a href="javascript:;" class="notif-filter-chip" data-filter="actionable">
      <i class="fas fa-bolt text-warning"></i> Butuh Aksi <span class="chip-count"><?= $actionableCount ?></span>
    </a>
    <a href="javascript:;" class="notif-filter-chip" data-filter="contract">
      <i class="fas fa-file-signature text-success"></i> Kontrak <span class="chip-count"><?= $categoryCounts['contract'] ?? 0 ?></span>
    </a>
    <a href="javascript:;" class="notif-filter-chip" data-filter="vt">
      <i class="fas fa-video text-primary"></i> Draft VT <span class="chip-count"><?= $categoryCounts['vt'] ?? 0 ?></span>
    </a>
    <a href="javascript:;" class="notif-filter-chip" data-filter="deadline">
      <i class="fas fa-hourglass-half text-danger"></i> Deadline <span class="chip-count"><?= $categoryCounts['deadline'] ?? 0 ?></span>
    </a>
    <a href="javascript:;" class="notif-filter-chip" data-filter="application">
      <i class="fas fa-paper-plane text-info"></i> Pendaftaran <span class="chip-count"><?= $categoryCounts['application'] ?? 0 ?></span>
    </a>
    <a href="javascript:;" class="notif-filter-chip" data-filter="shipment">
      <i class="fas fa-shipping-fast text-warning"></i> Pengiriman <span class="chip-count"><?= $categoryCounts['shipment'] ?? 0 ?></span>
    </a>
    <a href="javascript:;" class="notif-filter-chip" data-filter="invoice">
      <i class="fas fa-file-invoice-dollar text-cyan"></i> Tagihan <span class="chip-count"><?= $categoryCounts['invoice'] ?? 0 ?></span>
    </a>
  </div>

  <!-- Notification List -->
  <div id="notifListContainer">
    <?php if (empty($notifications)): ?>
      <div class="notif-empty">
        <i class="fas fa-bell-slash fa-3x text-muted mb-3 d-block" style="opacity:0.4;"></i>
        <h6 class="fw-bold text-dark mb-1">Belum Ada Pemberitahuan</h6>
        <p class="text-muted small mb-0">Semua aktivitas dan notifikasi terkait kampanye akan muncul di sini.</p>
      </div>
    <?php else: ?>
      <?php foreach ($notifications as $n): ?>
        <?php
          $cat = $n['category'] ?? 'other';
          $isUnread = !empty($n['is_unread']);
          $isActionable = !empty($n['is_actionable']);
          $iconMeta = match($cat) {
              'contract'    => ['bg' => '#ecfdf5', 'color' => '#059669', 'icon' => 'fas fa-file-signature'],
              'vt'          => ['bg' => '#eff6ff', 'color' => '#2563eb', 'icon' => 'fas fa-video'],
              'deadline'    => ['bg' => '#fef2f2', 'color' => '#dc2626', 'icon' => 'fas fa-hourglass-half'],
              'application' => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'icon' => 'fas fa-paper-plane'],
              'shipment'    => ['bg' => '#fffbeb', 'color' => '#d97706', 'icon' => 'fas fa-shipping-fast'],
              'invoice'     => ['bg' => '#ecfeff', 'color' => '#0891b2', 'icon' => 'fas fa-file-invoice-dollar'],
              default       => ['bg' => '#f8fafc', 'color' => '#475569', 'icon' => 'fas fa-bell'],
          };
        ?>
        <div class="notif-item <?= $isUnread ? 'is-unread' : '' ?>"
             data-category="<?= esc($cat) ?>"
             data-actionable="<?= $isActionable ? '1' : '0' ?>"
             data-text="<?= esc(strtolower($n['title'] . ' ' . strip_tags($n['message']))) ?>">
          
          <div class="notif-icon-wrap" style="background:<?= $iconMeta['bg'] ?>; color:<?= $iconMeta['color'] ?>;">
            <i class="<?= $iconMeta['icon'] ?>"></i>
          </div>

          <div class="notif-content">
            <div class="notif-title-row">
              <div class="notif-title">
                <?php if ($isUnread): ?>
                  <span class="notif-unread-dot" title="Belum dibaca"></span>
                <?php endif; ?>
                <span><?= esc($n['title']) ?></span>
              </div>
              <span class="badge <?= esc($n['badge_class']) ?>" style="font-size:10px; font-weight:600; padding:3px 8px; border-radius:6px;">
                <?= esc($n['badge']) ?>
              </span>
            </div>

            <div class="notif-message">
              <?= $n['message'] ?>
            </div>

            <div class="notif-meta-row">
              <span><i class="far fa-clock me-1"></i> <?= esc($n['time_ago']) ?></span>
              <?php if (!empty($n['url']) && !empty($n['action_text'])): ?>
                <a href="<?= esc($n['url']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size:11.5px; border-radius:6px;">
                  <?= esc($n['action_text']) ?> &rarr;
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
// Filter chips click handling
document.querySelectorAll('.notif-filter-chip').forEach(function(chip) {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.notif-filter-chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        filterNotifications();
    });
});

// Search input handling
document.getElementById('notifSearchInput').addEventListener('input', function() {
    filterNotifications();
});

function filterNotifications() {
    var activeChip = document.querySelector('.notif-filter-chip.active');
    var filter = activeChip ? activeChip.getAttribute('data-filter') : 'all';
    var keyword = document.getElementById('notifSearchInput').value.toLowerCase().trim();

    var items = document.querySelectorAll('#notifListContainer .notif-item');
    var visibleCount = 0;

    items.forEach(function(item) {
        var cat = item.getAttribute('data-category');
        var isActionable = item.getAttribute('data-actionable') === '1';
        var text = item.getAttribute('data-text') || '';

        var matchCategory = true;
        if (filter === 'actionable') {
            matchCategory = isActionable;
        } else if (filter !== 'all') {
            matchCategory = (cat === filter);
        }

        var matchSearch = true;
        if (keyword.length > 0) {
            matchSearch = (text.indexOf(keyword) !== -1);
        }

        if (matchCategory && matchSearch) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    var emptyEl = document.getElementById('notif-filter-empty');
    if (visibleCount === 0 && items.length > 0) {
        if (!emptyEl) {
            var div = document.createElement('div');
            div.id = 'notif-filter-empty';
            div.className = 'notif-empty';
            div.innerHTML = '<i class="fas fa-search fa-2x text-muted mb-2 d-block" style="opacity:0.4;"></i><h6 class="fw-bold text-dark mb-1">Tidak Ada Notifikasi Sesuai Filter</h6><p class="text-muted small mb-0">Coba ubah filter kategori atau kata kunci pencarian Anda.</p>';
            document.getElementById('notifListContainer').appendChild(div);
        } else {
            emptyEl.style.display = 'block';
        }
    } else if (emptyEl) {
        emptyEl.style.display = 'none';
    }
}

function markAllReadPage() {
    fetch('<?= base_url('notifications/mark-read') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('#notifListContainer .notif-item').forEach(item => {
                item.classList.remove('is-unread');
                var dot = item.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
            });
            var badge = document.getElementById('unread-count-badge');
            if (badge) badge.remove();
            if (typeof refreshHeaderNotifications === 'function') {
                refreshHeaderNotifications();
            }
        }
    })
    .catch(err => console.error(err));
}
</script>

<?= $this->endSection() ?>

