<?php
$uri = service('uri');
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

$session = session();
$userName = $session->get('name') ?? 'Pengguna';
$userEmail = $session->get('email') ?? '';
$userRoles = (array) ($session->get('roles') ?? []);
$isAdmin = in_array('admin', $userRoles, true);
$isKol = in_array('kol', $userRoles, true) || (!$isAdmin);

// Portal identity
$portalHome = base_url('dashboard');
$portalLabel = 'DATAHUB';
$portalBadgeClass = 'bg-success';
$portalAccent = '#198754';

// Helper function to check active state
$isActive = function(string $route) use ($currentPath): string {
    $cleanRoute = trim($route, '/');
    if ($cleanRoute === '' || $cleanRoute === 'dashboard') {
        return ($currentPath === '' || $currentPath === 'datahub' || $currentPath === 'datahub/dashboard' || $currentPath === 'dashboard') ? 'active' : '';
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
          <?php else: ?>
            <span class="badge bg-warning text-dark text-uppercase" style="font-size: 8.5px;"><i class="fas fa-user-astronaut me-1"></i> Talent / KOL</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Sidebar Menu (RBAC KOL Data Hub) -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column gap-1" data-lte-toggle="treeview" role="menu" data-accordion="false">

        <?php if ($isAdmin): ?>
          <!-- ==================== RBAC 1: SUPERADMIN ACCESS ==================== -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">
            <span class="badge bg-danger text-white"><i class="fas fa-shield-alt me-1"></i> INTELIJEN &amp; MASTER DATA</span>
          </li>
          
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $isActive('dashboard') ?>">
              <i class="nav-icon fas fa-chart-pie text-success"></i>
              <p>Overview Datahub</p>
            </a>
          </li>

          <!-- Group: KOL Management (Cicool Legacy: parent 76) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">KOL MANAGEMENT</li>
          <li class="nav-item">
            <a href="<?= base_url('kols') ?>" class="nav-link <?= $isActive('kols') ?>">
              <i class="nav-icon fas fa-users text-primary"></i>
              <p>Master Data KOL</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('platforms') ?>" class="nav-link <?= $isActive('platforms') ?>">
              <i class="nav-icon fas fa-hashtag text-info"></i>
              <p>Akun Media Sosial</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('rate-cards') ?>" class="nav-link <?= $isActive('rate-cards') ?>">
              <i class="nav-icon fas fa-tag text-warning"></i>
              <p>Rate Cards</p>
            </a>
          </li>

          <!-- Group: Master Data (Cicool Legacy: parent 82) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">MASTER DATA</li>
          <li class="nav-item">
            <a href="<?= base_url('tiers') ?>" class="nav-link <?= $isActive('tiers') ?>">
              <i class="nav-icon fas fa-layer-group text-primary"></i>
              <p>Tiers Influencer</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('categories') ?>" class="nav-link <?= $isActive('categories') ?>">
              <i class="nav-icon fas fa-list text-info"></i>
              <p>Kategori &amp; Niches</p>
            </a>
          </li>

          <!-- Group: Scraping Hub (Cicool Legacy: id 86) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">SCRAPER ENGINE</li>
          <li class="nav-item">
            <a href="<?= base_url('scraper') ?>" class="nav-link <?= $isActive('scraper') ?>">
              <i class="nav-icon fas fa-robot text-warning"></i>
              <p>Apify Scraper Engine</p>
            </a>
          </li>

          <!-- Group: Developer Tools (Cicool Legacy: id 131 Konfigurasi) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-3">DEVELOPER TOOLS</li>
          <li class="nav-item">
            <a href="/campaign/crud-generator/index.php" target="_blank" class="nav-link">
              <i class="nav-icon fas fa-magic text-info"></i>
              <p>CRUD Generator <span class="badge bg-info float-end">Adel</span></p>
            </a>
          </li>

        <?php else: ?>
          <!-- ==================== RBAC 2: TALENT / KOL ACCESS ==================== -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">
            <span class="badge bg-warning text-dark"><i class="fas fa-user-astronaut me-1"></i> TALENT KOL SPACE</span>
          </li>
          
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $isActive('dashboard') ?>">
              <i class="nav-icon fas fa-tachometer-alt text-warning"></i>
              <p>Ringkasan Pasar</p>
            </a>
          </li>

          <!-- Profil & Performance Card Saya (Cicool Legacy: id 149 & 160) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">PROFIL &amp; PERFORMA SAYA</li>
          <li class="nav-item">
            <a href="<?= base_url('my-profile') ?>" class="nav-link <?= $isActive('my-profile') ?>">
              <i class="nav-icon fas fa-id-card text-info"></i>
              <p>Profil &amp; Performa Saya</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('my-rates') ?>" class="nav-link <?= $isActive('my-rates') ?>">
              <i class="nav-icon fas fa-money-bill-wave text-success"></i>
              <p>Rate Card Saya</p>
            </a>
          </li>

          <!-- Peluang & Pekerjaan Saya (Cicool Legacy: id 146, 147, 159, 145) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">PELUANG &amp; PEKERJAAN</li>
          <li class="nav-item">
            <a href="<?= base_url('open-campaigns') ?>" class="nav-link <?= $isActive('open-campaigns') ?>">
              <i class="nav-icon fas fa-bullhorn text-warning"></i>
              <p>Kampanye Terbuka</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('my-applications') ?>" class="nav-link <?= $isActive('my-applications') ?>">
              <i class="nav-icon fas fa-clipboard-list text-primary"></i>
              <p>Lamaran Saya</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('my-tasks') ?>" class="nav-link <?= $isActive('my-tasks') ?>">
              <i class="nav-icon fas fa-tasks text-info"></i>
              <p>Tugas Saya</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('my-contracts') ?>" class="nav-link <?= $isActive('my-contracts') ?>">
              <i class="nav-icon fas fa-file-contract text-danger"></i>
              <p>Kontrak Saya</p>
            </a>
          </li>

          <!-- Keuangan Talent (Cicool Legacy: id 150) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">KEUANGAN TALENT</li>
          <li class="nav-item">
            <a href="<?= base_url('my-invoices') ?>" class="nav-link <?= $isActive('my-invoices') ?>">
              <i class="nav-icon fas fa-receipt text-warning"></i>
              <p>Tagihan &amp; Invoices</p>
            </a>
          </li>

          <!-- Market Benchmark (Cicool Legacy: kols, rate_cards, tiers, categories) -->
          <li class="nav-header text-uppercase text-xs text-muted px-3 mt-2">BENCHMARK INDUSTRI</li>
          <li class="nav-item">
            <a href="<?= base_url('kols') ?>" class="nav-link <?= $isActive('kols') ?>">
              <i class="nav-icon fas fa-search text-primary"></i>
              <p>Direktori KOL (Benchmark)</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('rate-cards') ?>" class="nav-link <?= $isActive('rate-cards') ?>">
              <i class="nav-icon fas fa-tags text-success"></i>
              <p>Benchmark Rate Card</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('tiers') ?>" class="nav-link <?= $isActive('tiers') ?>">
              <i class="nav-icon fas fa-layer-group text-info"></i>
              <p>Klasifikasi Tier</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('categories') ?>" class="nav-link <?= $isActive('categories') ?>">
              <i class="nav-icon fas fa-shapes text-warning"></i>
              <p>Daftar Kategori Niche</p>
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