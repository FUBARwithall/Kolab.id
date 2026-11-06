<?php
$uri = service('uri');
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

$session = session();
$userName = $session->get('name') ?? 'Pengguna';
$userEmail = $session->get('email') ?? '';
$userRoles = (array) ($session->get('roles') ?? []);
$isAdmin = in_array('admin', $userRoles, true);
$isKol = in_array('kol', $userRoles, true) || (!$isAdmin);

$portalHome = base_url('dashboard');

// Helper to check active link
$isActive = function(string $route) use ($currentPath): string {
    $cleanRoute = trim($route, '/');
    if ($cleanRoute === '' || $cleanRoute === 'dashboard') {
        return ($currentPath === '' || $currentPath === 'datahub' || $currentPath === 'datahub/dashboard' || $currentPath === 'dashboard') ? 'active' : '';
    }
    return (str_contains($currentPath, $cleanRoute)) ? 'active' : '';
};

// Check group active states for menu-open
$isKolGroupActive = $isActive('kols') !== '' || $isActive('snapshots') !== '' || $isActive('kol-profile-snapshots') !== '';
$isMasterGroupActive = $isActive('tiers') !== '' || $isActive('platforms') !== '' || $isActive('categories') !== '' || $isActive('my-profile') !== '';
$isConfigGroupActive = $isActive('settings') !== '';

// Talent group checks
$isTalentProfileActive = $isActive('my-profile') !== '' || $isActive('performance-card') !== '' || $isActive('my-rates') !== '';
$isTalentCampaignActive = $isActive('open-campaigns') !== '' || $isActive('my-applications') !== '' || $isActive('my-tasks') !== '' || $isActive('my-contracts') !== '';
?>

<!-- Minimalist Sidebar Styling -->
<style>
  .main-sidebar.sidebar-custom-dark {
    background-color: #1e293b !important;
    border-right: 1px solid #0f172a;
  }
  .sidebar-custom-dark .brand-link {
    background-color: #ffffff !important;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 12px 18px !important;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    height: 57px;
  }
  .sidebar-custom-dark .brand-link img {
    max-height: 32px;
    width: auto;
    object-fit: contain;
  }
  .sidebar-custom-dark .nav-sidebar .nav-link {
    color: #94a3b8 !important;
    font-size: 14px;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: 6px;
    transition: all 0.15s ease-in-out;
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
  }
  .sidebar-custom-dark .nav-sidebar .nav-link:hover {
    color: #f8fafc !important;
    background-color: rgba(255, 255, 255, 0.05);
  }
  .sidebar-custom-dark .nav-sidebar .nav-link.active {
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
    font-weight: 600;
  }
  .sidebar-custom-dark .nav-sidebar .nav-link .nav-icon {
    font-size: 16px;
    width: 24px;
    margin-right: 10px;
    text-align: center;
    color: #94a3b8;
  }
  .sidebar-custom-dark .nav-sidebar .nav-link:hover .nav-icon,
  .sidebar-custom-dark .nav-sidebar .nav-link.active .nav-icon {
    color: #ffffff;
  }
  .sidebar-custom-dark .nav-treeview {
    display: none;
    background-color: rgba(0, 0, 0, 0.15);
    border-radius: 6px;
    margin-top: 2px;
    padding: 4px 0;
    list-style: none;
  }
  .sidebar-custom-dark .nav-item.menu-open > .nav-treeview {
    display: block;
  }
  .sidebar-custom-dark .nav-treeview .nav-link {
    padding-left: 36px !important;
    font-size: 13.5px;
    color: #94a3b8 !important;
  }
  .sidebar-custom-dark .nav-treeview .nav-link .nav-icon {
    font-size: 11px;
    width: 18px;
    margin-right: 8px;
  }
  .sidebar-custom-dark .nav-treeview .nav-link.active {
    color: #ffffff !important;
    font-weight: 600;
  }
  .sidebar-custom-dark .nav-sidebar .right {
    margin-left: auto;
    transition: transform 0.2s ease-in-out;
    font-size: 11px;
    color: #64748b;
  }
  .sidebar-custom-dark .nav-item.menu-open > .nav-link .right {
    transform: rotate(-90deg);
  }
</style>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-custom-dark elevation-2">
  <!-- Brand Logo Bar -->
  <a href="<?= $portalHome ?>" class="brand-link">
    <img src="<?= base_url('asset/img/kolab-logo.png') ?>" alt="KOLAB.ID" class="brand-image-xs">
  </a>

  <!-- Sidebar -->
  <div class="sidebar px-2 py-3">
    <nav class="mt-1">
      <ul class="nav nav-pills nav-sidebar flex-column gap-1" role="menu">

        <?php if ($isAdmin): ?>
          <!-- ==================== MENU ADMIN (SIMPLE VIEW) ==================== -->
          
          <!-- 1. Dashboard -->
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $isActive('dashboard') ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <!-- 2. KOL Management (Treeview) -->
          <li class="nav-item <?= $isKolGroupActive ? 'menu-open' : '' ?>">
            <a href="javascript:;" class="nav-link treeview-toggle <?= $isKolGroupActive ? 'active' : '' ?>">
              <i class="nav-icon fas fa-users"></i>
              <p class="d-flex align-items-center w-100 mb-0">
                KOL Management
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= base_url('kols') ?>" class="nav-link <?= $isActive('kols') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>KOL Directory</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('snapshots') ?>" class="nav-link <?= ($isActive('snapshots') !== '' || $isActive('kol-profile-snapshots') !== '') ? 'active' : '' ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Profile Snapshots</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- 3. Master Data (Treeview) -->
          <li class="nav-item <?= $isMasterGroupActive ? 'menu-open' : '' ?>">
            <a href="javascript:;" class="nav-link treeview-toggle <?= $isMasterGroupActive ? 'active' : '' ?>">
              <i class="nav-icon fas fa-coins"></i>
              <p class="d-flex align-items-center w-100 mb-0">
                Master Data
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= base_url('tiers') ?>" class="nav-link <?= $isActive('tiers') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tiers</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('platforms') ?>" class="nav-link <?= $isActive('platforms') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Platforms</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('categories') ?>" class="nav-link <?= $isActive('categories') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Categories</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('my-profile') ?>" class="nav-link <?= $isActive('my-profile') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>User</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- 4. Scraping Hub -->
          <li class="nav-item">
            <a href="<?= base_url('scraper') ?>" class="nav-link <?= $isActive('scraper') ?>">
              <i class="nav-icon fas fa-cloud-download-alt"></i>
              <p>Scraping Hub</p>
            </a>
          </li>

          <!-- 5. Konfigurasi (Treeview) -->
          <li class="nav-item <?= $isConfigGroupActive ? 'menu-open' : '' ?>">
            <a href="javascript:;" class="nav-link treeview-toggle <?= $isConfigGroupActive ? 'active' : '' ?>">
              <i class="nav-icon fas fa-cogs"></i>
              <p class="d-flex align-items-center w-100 mb-0">
                Konfigurasi
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= base_url('settings') ?>" class="nav-link <?= $isActive('settings') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Settings</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/campaign/crud-generator/index.php" target="_blank" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>CRUD Generator</p>
                </a>
              </li>
            </ul>
          </li>

        <?php else: ?>
          <!-- ==================== MENU TALENT / KOL ==================== -->

          <!-- 1. Dashboard -->
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $isActive('dashboard') ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <!-- 2. Profil & Performa -->
          <li class="nav-item <?= $isTalentProfileActive ? 'menu-open' : '' ?>">
            <a href="javascript:;" class="nav-link treeview-toggle <?= $isTalentProfileActive ? 'active' : '' ?>">
              <i class="nav-icon fas fa-user-circle"></i>
              <p class="d-flex align-items-center w-100 mb-0">
                Profil &amp; Performa
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= base_url('my-profile') ?>" class="nav-link <?= $isActive('my-profile') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Profil Saya</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('performance-card/my-card') ?>" class="nav-link <?= $isActive('performance-card') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Performance Card</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('my-rates') ?>" class="nav-link <?= $isActive('my-rates') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Rate Card Saya</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- 3. Kampanye & Pekerjaan -->
          <li class="nav-item <?= $isTalentCampaignActive ? 'menu-open' : '' ?>">
            <a href="javascript:;" class="nav-link treeview-toggle <?= $isTalentCampaignActive ? 'active' : '' ?>">
              <i class="nav-icon fas fa-bullhorn"></i>
              <p class="d-flex align-items-center w-100 mb-0">
                Kampanye
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= base_url('open-campaigns') ?>" class="nav-link <?= $isActive('open-campaigns') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Kampanye Terbuka</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('my-applications') ?>" class="nav-link <?= $isActive('my-applications') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Lamaran Saya</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('my-tasks') ?>" class="nav-link <?= $isActive('my-tasks') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tugas Saya</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('my-contracts') ?>" class="nav-link <?= $isActive('my-contracts') ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Kontrak Saya</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- 4. Keuangan -->
          <li class="nav-item">
            <a href="<?= base_url('my-invoices') ?>" class="nav-link <?= $isActive('my-invoices') ?>">
              <i class="nav-icon fas fa-receipt"></i>
              <p>Invoices Saya</p>
            </a>
          </li>

        <?php endif; ?>

        <!-- Divider & Logout (Semua Role) -->
        <li class="nav-item mt-4 pt-2 border-top border-secondary">
          <a href="<?= base_url('logout') ?>" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
            <p class="text-danger fw-semibold">Logout</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>

<script>
  window.addEventListener('DOMContentLoaded', function () {
    // Robust dropdown toggle
    if (typeof jQuery !== 'undefined') {
      $(document).on('click', '.sidebar-custom-dark .treeview-toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $item = $(this).closest('.nav-item');
        const $submenu = $item.find('> .nav-treeview');

        if ($item.hasClass('menu-open')) {
          $submenu.stop(true, true).slideUp(200, function () {
            $item.removeClass('menu-open');
          });
        } else {
          $item.addClass('menu-open');
          $submenu.stop(true, true).slideDown(200);
        }
      });
    } else {
      document.querySelectorAll('.sidebar-custom-dark .treeview-toggle').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          const parentItem = this.closest('.nav-item');
          if (!parentItem) return;

          const submenu = parentItem.querySelector(':scope > .nav-treeview');
          if (!submenu) return;

          const isOpen = parentItem.classList.contains('menu-open');
          if (isOpen) {
            parentItem.classList.remove('menu-open');
            submenu.style.display = 'none';
          } else {
            parentItem.classList.add('menu-open');
            submenu.style.display = 'block';
          }
        });
      });
    }
  });
</script>
