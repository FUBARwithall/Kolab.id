<?php
$uri = service('uri');
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

$session = session();
$userName = $session->get('name') ?? 'Pengguna';
$userEmail = $session->get('email') ?? '';
$userRoles = (array) ($session->get('roles') ?? []);
$isAdmin = in_array('admin', $userRoles, true);
$isLeader = in_array('leader', $userRoles, true);
$isStaff = in_array('staff', $userRoles, true);

// Configure portal identity
$portalHome = base_url('dashboard');
$portalLabel = 'CAMPAIGN';
$portalBadgeClass = 'bg-primary';
$portalAccent = '#0d6efd';

// Helper function to check active state
$isActive = function(string $route) use ($currentPath): string {
    $cleanRoute = trim($route, '/');
    if ($cleanRoute === '' || $cleanRoute === 'dashboard') {
        return ($currentPath === '' || $currentPath === 'campaign' || $currentPath === 'campaign/dashboard' || $currentPath === 'dashboard') ? 'active' : '';
    }
    return (str_contains($currentPath, $cleanRoute)) ? 'active' : '';
};
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-bg-dark sidebar-color-primary shadow">
  <div class="brand-container d-flex align-items-center justify-content-between px-3 py-2 border-bottom border-secondary">
    <a href="<?= $portalHome ?>" class="brand-link d-flex align-items-center gap-2 text-decoration-none">
      <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 34px; height: 34px; font-weight: 800; font-size: 15px; background: <?= $portalAccent ?>;">
        K
      </div>
      <div class="d-flex flex-column">
        <span class="brand-text fw-bold text-white fs-6 lh-1">KOLAB<span style="color: <?= $portalAccent ?>;">.ID</span></span>
        <span class="badge <?= $portalBadgeClass ?> mt-1 text-uppercase fw-bold" style="font-size: 8.5px; letter-spacing: 0.05em; width: fit-content;">
          <?= $portalLabel ?> PORTAL
        </span>
      </div>
    </a>
    <a class="pushmenu text-white-50" data-lte-toggle="sidebar-mini" href="javascript:;" role="button">
      <i class="fas fa-angle-double-left"></i>
    </a>
  </div>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- User panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center px-3 border-bottom border-secondary">
      <div class="image me-2">
        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 700;">
          <?= strtoupper(substr($userName, 0, 1)) ?>
        </div>
      </div>
      <div class="info overflow-hidden">
        <a href="javascript:;" class="d-block text-white fw-semibold text-truncate text-decoration-none" style="font-size: 13.5px;">
          <?= esc($userName) ?>
        </a>
        <div class="mt-1">
          <?php if ($isAdmin): ?>
            <span class="badge bg-danger text-uppercase" style="font-size: 8.5px;"><i class="fas fa-shield-alt me-1"></i> Superadmin</span>
          <?php elseif ($isLeader): ?>
            <span class="badge bg-primary text-uppercase" style="font-size: 8.5px;"><i class="fas fa-user-tie me-1"></i> Leader KOL</span>
          <?php else: ?>
            <span class="badge bg-info text-uppercase" style="font-size: 8.5px;"><i class="fas fa-user-cog me-1"></i> Staff Operational</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Sidebar Menu (RBAC Campaign Management based on Cicool DB) -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column gap-1" data-lte-toggle="treeview" role="menu" data-accordion="false">

        <!-- Header Role Status -->
        <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">
          <?php if ($isAdmin): ?>
            <span class="badge bg-danger text-white"><i class="fas fa-shield-alt me-1"></i> SUPERADMIN CONTROL</span>
          <?php elseif ($isLeader): ?>
            <span class="badge bg-primary text-white"><i class="fas fa-user-tie me-1"></i> STRATEGIC & APPROVAL</span>
          <?php else: ?>
            <span class="badge bg-info text-white"><i class="fas fa-user-cog me-1"></i> OPERASIONAL HARIAN</span>
          <?php endif; ?>
        </li>
        
        <!-- Dashboard Utama -->
        <li class="nav-item">
          <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $isActive('dashboard') ?>">
            <i class="nav-icon fas fa-home text-primary"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Section: PERENCANAAN (Cicool Legacy parent 101) -->
        <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">PERENCANAAN</li>
        <li class="nav-item">
          <a href="<?= base_url('campaigns') ?>" class="nav-link <?= $isActive('campaigns') ?>">
            <i class="nav-icon fas fa-bullhorn text-warning"></i>
            <p>
              Campaigns
              <?php if ($isAdmin || $isLeader): ?>
                <span class="badge bg-primary float-end">Master</span>
              <?php else: ?>
                <span class="badge bg-secondary float-end">Pantau</span>
              <?php endif; ?>
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= base_url('briefs') ?>" class="nav-link <?= $isActive('briefs') ?>">
            <i class="nav-icon fas fa-file-alt text-info"></i>
            <p><?= ($isAdmin || $isLeader) ? 'Briefs & Approval' : 'Briefs & Guidelines' ?></p>
          </a>
        </li>

        <!-- Section: PRODUKSI & KONTEN (Cicool Legacy parent 105) -->
        <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">PRODUKSI &amp; KONTEN</li>
        <li class="nav-item">
          <a href="<?= base_url('drafts') ?>" class="nav-link <?= $isActive('drafts') ?>">
            <i class="nav-icon fas fa-video text-warning"></i>
            <p>Review Draft Video</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= base_url('shipments') ?>" class="nav-link <?= $isActive('shipments') ?>">
            <i class="nav-icon fas fa-shipping-fast text-info"></i>
            <p>Pengiriman Sampel</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= base_url('postings') ?>" class="nav-link <?= $isActive('postings') ?>">
            <i class="nav-icon fas fa-chart-bar text-success"></i>
            <p>Postings &amp; Analytics</p>
          </a>
        </li>

        <!-- Section: STRATEGI & KEUANGAN (Cicool Legacy parent 139 & 110) -->
        <?php if ($isAdmin || $isLeader): ?>
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">STRATEGI &amp; KEUANGAN</li>
          <li class="nav-item">
            <a href="<?= base_url('invoices') ?>" class="nav-link <?= $isActive('invoices') ?>">
              <i class="nav-icon fas fa-receipt text-warning"></i>
              <p>Finance &amp; Invoices</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('contracts') ?>" class="nav-link <?= $isActive('contracts') ?>">
              <i class="nav-icon fas fa-file-signature text-primary"></i>
              <p>Kontrak KOL &amp; Komisi</p>
            </a>
          </li>
        <?php else: ?>
          <!-- Keuangan Operasional untuk Staff (Hanya Verifikasi Invoices Lapangan) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">KEUANGAN LAPANGAN</li>
          <li class="nav-item">
            <a href="<?= base_url('invoices') ?>" class="nav-link <?= $isActive('invoices') ?>">
              <i class="nav-icon fas fa-receipt text-warning"></i>
              <p>Verifikasi Invoices</p>
            </a>
          </li>
        <?php endif; ?>

        <!-- Section: CMS PUBLIK (Cicool Legacy parent 161 - Khusus Leader & Admin) -->
        <?php if ($isAdmin || $isLeader): ?>
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">KONTEN PUBLIK</li>
          <li class="nav-item">
            <a href="<?= base_url('articles') ?>" class="nav-link <?= $isActive('articles') ?>">
              <i class="nav-icon fas fa-newspaper text-info"></i>
              <p>Artikel Landing CMS</p>
            </a>
          </li>
        <?php endif; ?>

        <!-- Section: DEVELOPER TOOLS (Cicool Legacy parent 130 - Khusus Superadmin) -->
        <?php if ($isAdmin): ?>
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-3">DEVELOPER TOOLS</li>
          <li class="nav-item">
            <a href="<?= base_url('crud-generator/index.php') ?>" target="_blank" class="nav-link">
              <i class="nav-icon fas fa-magic text-info"></i>
              <p>CRUD Generator <span class="badge bg-info float-end">Adel</span></p>
            </a>
          </li>
        <?php endif; ?>

        <!-- ==================== LOGOUT (SEMUA ROLE) ==================== -->
        <li class="nav-header text-uppercase text-xs text-muted px-3 mt-3">AKUN</li>
        <li class="nav-item">
          <a href="<?= base_url('logout') ?>" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Keluar (Logout)</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>