<?php
$session = session();
$userName = $session->get('name') ?? 'Pengguna';
$userEmail = $session->get('email') ?? '';
$userRoles = (array) ($session->get('roles') ?? []);
$uri = service('uri');
$seg1 = strtolower($uri->getSegment(1) ?? 'campaign');
?>
<nav class="main-header navbar navbar-expand navbar-light bg-white border-bottom shadow-sm">
  <div class="container-fluid px-3">
    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar-full" href="javascript:;" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block ms-2">
        <?php if ($seg1 === 'kol'): ?>
          <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase">
            <i class="fas fa-user-astronaut me-1"></i> Portal Creator
          </span>
        <?php elseif ($seg1 === 'datahub'): ?>
          <span class="badge bg-success px-3 py-2 fw-bold text-uppercase">
            <i class="fas fa-database me-1"></i> KOL Data Hub
          </span>
        <?php else: ?>
          <span class="badge bg-primary px-3 py-2 fw-bold text-uppercase">
            <i class="fas fa-bullhorn me-1"></i> Campaign Management
          </span>
        <?php endif; ?>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ms-auto align-items-center gap-2">
      <!-- Notification Dropdown Menu (Sistem Notifikasi seperti Sistem Sebelumnya) -->
      <li class="nav-item dropdown" id="kol-notifications-container">
        <a class="nav-link position-relative d-flex align-items-center justify-content-center p-2 rounded-circle" 
           data-bs-toggle="dropdown" href="javascript:;" role="button" aria-expanded="false" title="Pemberitahuan"
           style="width: 38px; height: 38px; color: #475569; transition: all 0.15s ease;">
          <i class="far fa-bell fs-5"></i>
          <span id="kol-notif-badge" class="badge rounded-pill bg-danger position-absolute" 
                style="display:none !important; top: 3px; right: 2px; font-size: 10px; padding: 2px 5px; line-height: 1;">
          </span>
        </a>
        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-0 mt-2" 
             style="width: 360px; max-width: 92vw; overflow: hidden; z-index: 1050;">
          <div class="px-3 py-2.5 bg-white border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-bell text-warning"></i>
              <span class="fw-bold text-dark" style="font-size: 13.5px;">Pemberitahuan</span>
            </div>
            <div id="kol-notif-header-action">
              <button type="button" onclick="markAllKolNotificationsRead(event)" class="btn btn-sm btn-light border py-1 px-2 text-primary fw-semibold" style="font-size: 11px; border-radius: 4px;" title="Tandai semua sebagai sudah dibaca">
                <i class="fas fa-check-double me-1"></i> Tandai Dibaca
              </button>
            </div>
          </div>
          <ul class="list-unstyled mb-0" id="kol-notif-menu" style="max-height: 360px; overflow-y: auto; padding: 0;">
            <li class="p-4 text-center text-muted small">
              <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary d-block"></i> Memuat pemberitahuan...
            </li>
          </ul>
          <div class="py-2 px-3 text-center bg-light border-top">
            <a href="<?= base_url('notifications') ?>" class="text-primary fw-bold text-decoration-none" style="font-size: 12px;">
              Lihat Semua Notifikasi &rarr;
            </a>
          </div>
        </div>
      </li>

      <!-- User Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" href="javascript:;">
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 700; font-size: 13px;">
            <?= strtoupper(substr($userName, 0, 1)) ?>
          </div>
          <span class="d-none d-md-inline fw-semibold text-dark fs-7"><?= esc($userName) ?></span>
          <i class="fas fa-chevron-down fs-8 text-muted"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2 mt-2" style="min-width: 220px;">
          <div class="px-3 py-2 border-bottom">
            <div class="fw-bold text-dark text-truncate"><?= esc($userName) ?></div>
            <div class="text-muted text-xs text-truncate"><?= esc($userEmail) ?></div>
            <span class="badge bg-light text-primary border mt-1 text-uppercase text-xs">
              <?= esc(!empty($userRoles) ? implode(', ', $userRoles) : 'User') ?>
            </span>
          </div>
          <div class="p-1">
            <a href="<?= base_url('notifications') ?>" class="dropdown-item rounded text-dark fw-semibold d-flex align-items-center gap-2 py-2">
              <i class="fas fa-bell text-warning"></i> Pemberitahuan
            </a>
            <a href="<?= base_url('logout') ?>" class="dropdown-item rounded text-danger fw-semibold d-flex align-items-center gap-2 py-2">
              <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
          </div>
        </div>
      </li>
    </ul>
  </div>
</nav>

<script>
function refreshKolNotifications() {
    var notifUrl = '<?= base_url('notifications/inbox') ?>';
    fetch(notifUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        if (res.success && res.notifications) {
            var unread = res.unread_count || 0;
            var badgeEl = document.getElementById('kol-notif-badge');
            var actionEl = document.getElementById('kol-notif-header-action');

            if (unread > 0) {
                badgeEl.textContent = unread > 99 ? '99+' : unread;
                badgeEl.style.setProperty('display', 'inline-block', 'important');
                if (actionEl) {
                    actionEl.innerHTML = '<button type="button" onclick="markAllKolNotificationsRead(event)" class="btn btn-sm btn-light border py-1 px-2 text-primary fw-semibold" style="font-size: 11px; border-radius: 4px;"><i class="fas fa-check-double me-1"></i> Tandai Dibaca</button>';
                }
            } else {
                badgeEl.textContent = '';
                badgeEl.style.setProperty('display', 'none', 'important');
                if (actionEl) {
                    actionEl.innerHTML = '<span style="font-size: 11px; color: #10b981; font-weight: 500;"><i class="fas fa-check-circle me-1"></i> Semua dibaca</span>';
                }
            }

            var menuEl = document.getElementById('kol-notif-menu');
            if (!menuEl) return;

            var html = '';
            if (res.notifications.length > 0) {
                res.notifications.slice(0, 10).forEach(function(item) {
                    var bgStyle = item.is_unread ? 'background: #f8fafc;' : 'background: #ffffff;';
                    var unreadDot = item.is_unread ? '<span style="display:inline-block; width:6px; height:6px; background:#2563eb; border-radius:50%; margin-right:6px;" title="Belum dibaca"></span>' : '';
                    
                    html += '<li class="kol-notif-item ' + (item.is_unread ? 'is-unread' : 'is-read') + '" style="' + bgStyle + ' border-bottom: 1px solid #f1f5f9; padding: 10px 14px; transition: background 0.12s ease;">' +
                        '<a href="' + item.url + '" style="display: block; color: inherit; text-decoration: none; padding: 0; white-space: normal;">' +
                            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px;">' +
                                '<div style="font-weight: 700; font-size: 12.5px; color: ' + (item.is_unread ? '#0f172a' : '#475569') + '; display: flex; align-items: center;">' +
                                    unreadDot + item.title +
                                '</div>' +
                                '<span class="badge ' + (item.badge_class || 'bg-secondary') + '" style="font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 600;">' + item.badge + '</span>' +
                            '</div>' +
                            '<div style="font-size: 11.5px; color: ' + (item.is_unread ? '#334155' : '#64748b') + '; line-height: 1.4; margin-bottom: 4px;">' + item.message + '</div>' +
                            '<div style="font-size: 10.5px; color: #94a3b8;"><i class="far fa-clock me-1"></i>' + item.time_ago + '</div>' +
                        '</a>' +
                    '</li>';
                });
            } else {
                html = '<li style="padding: 28px 16px; text-align: center; color: #94a3b8; font-size: 12px;"><i class="fas fa-bell-slash fa-2x mb-2 d-block" style="opacity:0.3;"></i> Belum ada pemberitahuan baru.</li>';
            }
            menuEl.innerHTML = html;
        }
    })
    .catch(function(err) {
        console.error(err);
    });
}

function markAllKolNotificationsRead(e) {
    if (e) e.preventDefault();
    var actionEl = document.getElementById('kol-notif-header-action');
    if (actionEl) {
        actionEl.innerHTML = '<span style="font-size: 11px; color: #94a3b8;">Menyimpan...</span>';
    }

    fetch('<?= base_url('notifications/mark-read') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        if (res.success) {
            var badgeEl = document.getElementById('kol-notif-badge');
            if (badgeEl) {
                badgeEl.textContent = '';
                badgeEl.style.setProperty('display', 'none', 'important');
            }
            if (actionEl) {
                actionEl.innerHTML = '<span style="font-size: 11px; color: #10b981; font-weight: 500;"><i class="fas fa-check-circle me-1"></i> Semua dibaca</span>';
            }
            // Update items visually to read state
            document.querySelectorAll('#kol-notif-menu li.kol-notif-item').forEach(function(el) {
                el.style.background = '#ffffff';
                var dot = el.querySelector('span[title="Belum dibaca"]');
                if (dot) dot.remove();
            });
        }
    })
    .catch(function(err) {
        refreshKolNotifications();
    });
}

// Initial fetch & polling every 25 seconds
document.addEventListener('DOMContentLoaded', function() {
    refreshKolNotifications();
    setInterval(refreshKolNotifications, 25000);
});
</script>
