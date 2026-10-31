<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?= esc($portalTitle) ?> — KOLAB.ID</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Font: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url('asset/plugins/fontawesome-free/css/all.min.css') ?>">
  <!-- Bootstrap 5 & AdminLTE 4 (Adel UI) -->
  <link rel="stylesheet" href="<?= base_url('asset/css/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('asset/css/adminlte.min.css') ?>">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background: radial-gradient(circle at 50% 10%, #1e293b 0%, #0f172a 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 24px 16px;
    }

    .login-box {
      width: 100%;
      max-width: 440px;
    }

    .card {
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      overflow: hidden;
      background: #ffffff;
    }

    .card-header {
      background: #ffffff;
      border-bottom: 1px solid #f1f5f9;
      padding: 30px 24px 20px;
      text-align: center;
    }

    .portal-avatar {
      width: 58px;
      height: 58px;
      border-radius: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin-bottom: 14px;
    }

    /* Color styles based on portal */
    .portal-primary .portal-avatar { background: #eff6ff; color: #0d6efd; }
    .portal-primary .btn-portal { background: #0d6efd; border-color: #0d6efd; color: #fff; }
    .portal-primary .btn-portal:hover { background: #0b5ed7; border-color: #0a58ca; }

    .portal-success .portal-avatar { background: #ecfdf5; color: #198754; }
    .portal-success .btn-portal { background: #198754; border-color: #198754; color: #fff; }
    .portal-success .btn-portal:hover { background: #157347; border-color: #146c43; }

    .portal-warning .portal-avatar { background: #f5f3ff; color: #7c3aed; }
    .portal-warning .btn-portal { background: #7c3aed; border-color: #7c3aed; color: #fff; }
    .portal-warning .btn-portal:hover { background: #6d28d9; border-color: #5b21b6; }

    .form-control {
      border-radius: 10px 0 0 10px;
      padding: 11px 16px;
      font-size: 14px;
      border: 1px solid #cbd5e1;
    }

    .form-control:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }

    .input-group-text {
      border-radius: 0 10px 10px 0;
      border: 1px solid #cbd5e1;
      border-left: none;
      background: #f8fafc;
      color: #64748b;
    }

    .demo-helper {
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 12px;
      padding: 12px 14px;
      font-size: 12px;
      margin-top: 20px;
    }

    .demo-btn {
      cursor: pointer;
      border: 1px solid #e2e8f0;
      background: #ffffff;
      border-radius: 8px;
      padding: 6px 10px;
      font-size: 11.5px;
      font-weight: 600;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .demo-btn:hover {
      background: #f1f5f9;
      color: #0f172a;
    }
  </style>
</head>
<body class="hold-transition portal-<?= esc($themeColor) ?>">

<div class="login-box">

  <!-- Alert / Flash Messages -->
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm text-sm rounded-3 py-2.5 px-3 mb-3 d-flex align-items-center gap-2" role="alert">
      <i class="fas fa-exclamation-circle text-danger fs-5"></i>
      <div class="flex-grow-1"><?= esc(session()->getFlashdata('error')) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm text-sm rounded-3 py-2.5 px-3 mb-3 d-flex align-items-center gap-2" role="alert">
      <i class="fas fa-check-circle text-success fs-5"></i>
      <div class="flex-grow-1"><?= esc(session()->getFlashdata('success')) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Active User Alert -->
  <?php if (!empty($currentUser)): ?>
    <div class="alert alert-info shadow-sm text-xs rounded-3 py-2.5 px-3 mb-3 d-flex align-items-center justify-content-between" role="alert">
      <div>
        <i class="fas fa-user-check text-info me-1"></i> Terhubung: <strong><?= esc($currentUser['name']) ?></strong> (<?= esc($currentUser['role']) ?>)
      </div>
      <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-success py-0 px-2 fw-semibold" style="font-size: 11px;">Ke Dashboard &rarr;</a>
    </div>
  <?php endif; ?>

  <!-- Card Login Adel UI -->
  <div class="card">
    <div class="card-header">
      <div class="portal-avatar">
        <i class="<?= esc($icon) ?>"></i>
      </div>
      <span class="badge bg-<?= esc($themeColor) ?> mb-2 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.08em;">
        <?= esc($badgeText) ?>
      </span>
      <h2 class="fw-bold fs-4 text-dark mb-1"><?= esc($portalTitle) ?></h2>
      <p class="text-muted text-xs mb-0"><?= esc($portalSubtitle) ?></p>
    </div>

    <div class="card-body p-4">
      <form action="<?= esc($formAction) ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="portal" value="<?= esc($portal) ?>">

        <div class="mb-3">
          <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Email Pengguna</label>
          <div class="input-group">
            <input type="email" name="email" id="login_email" class="form-control" placeholder="nama@kolab.id" value="<?= old('email') ?>" required autofocus>
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">Kata Sandi</label>
          <div class="input-group">
            <input type="password" name="password" id="login_password" class="form-control" placeholder="••••••••" required>
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
          </div>
        </div>

        <button type="submit" class="btn btn-portal w-100 fw-bold py-2.5 rounded-3 shadow-sm">
          <span>Masuk ke <?= esc($portalTitle) ?></span>
          <i class="fas fa-arrow-right ms-1"></i>
        </button>
      </form>

      <!-- Quick Demo Account Fill (2 RBAC: Superadmin & KOL) -->
      <div class="demo-helper">
        <div class="fw-bold text-dark text-xs mb-2"><i class="fas fa-users-cog text-success me-1"></i> Pilih Akun Role Demo:</div>
        <?php if (!empty($demoAccounts)): ?>
          <?php foreach ($demoAccounts as $acc): ?>
            <div class="demo-btn w-100 justify-content-between mb-2 p-2 rounded border" style="cursor: pointer;" onclick="fillCredential('<?= esc($acc['email']) ?>', '<?= esc($acc['pass']) ?>')" title="<?= esc($acc['desc'] ?? '') ?>">
              <div class="d-flex align-items-center">
                <i class="<?= esc($acc['icon'] ?? 'fas fa-user-circle') ?> me-2 fs-5"></i>
                <div class="text-start">
                  <div class="fw-bold text-dark" style="font-size: 12px;"><?= esc($acc['role']) ?></div>
                  <div class="text-muted" style="font-size: 11px;"><?= esc($acc['email']) ?></div>
                </div>
              </div>
              <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 10px;">Klik untuk Isi</span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="demo-btn w-100 justify-content-between" onclick="fillCredential('<?= esc($demoEmail) ?>', '<?= esc($demoPass) ?>')">
            <span><i class="fas fa-user-circle text-muted me-1"></i> <?= esc($demoRole) ?></span>
            <span class="badge bg-light text-muted border"><?= esc($demoPass) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Links to Hub and Landing -->
      <div class="d-flex align-items-center justify-content-between mt-4 pt-3 border-top text-xs">
        <a href="<?= root_url('login') ?>" class="text-decoration-none text-muted hover-dark">
          <i class="fas fa-th-large me-1"></i> Pilih Portal Lain
        </a>
        <a href="<?= root_url('') ?>" class="text-decoration-none text-muted hover-dark">
          <i class="fas fa-globe me-1"></i> Ke Landing Page
        </a>
      </div>
    </div>
  </div>

</div>

<!-- Scripts -->
<script src="<?= base_url('asset/js/jquery-3.6.0.min.js') ?>"></script>
<script src="<?= base_url('asset/js/bootstrap.bundle.min.js') ?>"></script>
<script>
function fillCredential(email, pass) {
  document.getElementById('login_email').value = email;
  document.getElementById('login_password').value = pass;
  document.getElementById('login_email').focus();
}
</script>

</body>
</html>
