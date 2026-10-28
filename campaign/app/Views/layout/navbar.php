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
      <li class="nav-item d-none d-md-inline-block">
        <a href="<?= base_url('/') ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
          <i class="fas fa-external-link-alt me-1"></i> Web Publik
        </a>
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
            <a href="<?= base_url('logout') ?>" class="dropdown-item rounded text-danger fw-semibold d-flex align-items-center gap-2 py-2">
              <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
          </div>
        </div>
      </li>
    </ul>
  </div>
</nav>
