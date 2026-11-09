<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?= esc($portalTitle) ?> — KOLAB.ID</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Font: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url('asset/plugins/fontawesome-free/css/all.min.css') ?>">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="<?= base_url('asset/css/bootstrap.min.css') ?>">

  <style>
    *, *::before, *::after { box-sizing: border-box; }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #f5f6fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 24px 16px;
      color: #0f172a;
    }

    /* ── Wordmark / brand row ── */
    .brand-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 28px;
    }
    .brand-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      flex-shrink: 0;
    }
    .brand-name {
      font-size: 15px;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.01em;
    }
    .brand-sep { color: #cbd5e1; font-size: 18px; font-weight: 300; }
    .brand-portal {
      font-size: 13px;
      font-weight: 500;
      color: #64748b;
    }

    /* ── Card ── */
    .login-card {
      width: 100%;
      max-width: 400px;
      background: #ffffff;
      border: 1px solid #e8edf2;
      border-radius: 16px;
      padding: 36px 32px 28px;
      box-shadow: 0 4px 24px rgba(15, 23, 42, 0.07);
    }

    /* ── Header inside card ── */
    .card-brand-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 16px;
    }
    .card-title {
      font-size: 20px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 4px;
      letter-spacing: -0.02em;
      line-height: 1.25;
    }
    .card-subtitle {
      font-size: 13px;
      color: #64748b;
      margin: 0 0 28px;
      line-height: 1.5;
    }

    /* ── Form ── */
    .field-label {
      display: block;
      font-size: 11.5px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 6px;
    }
    .field-wrap {
      position: relative;
      margin-bottom: 16px;
    }
    .field-input {
      width: 100%;
      padding: 10px 40px 10px 14px;
      font-size: 14px;
      font-family: inherit;
      color: #0f172a;
      background: #f8fafc;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      outline: none;
      transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .field-input::placeholder { color: #94a3b8; }
    .field-input:focus {
      background: #fff;
      border-color: var(--accent, #10b981);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent, #10b981) 15%, transparent);
    }
    .field-icon {
      position: absolute;
      right: 13px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 13px;
      pointer-events: none;
    }

    /* ── Submit Button ── */
    .btn-submit {
      width: 100%;
      padding: 11px 16px;
      font-size: 14px;
      font-weight: 600;
      font-family: inherit;
      color: #fff;
      background: var(--accent, #10b981);
      border: none;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 8px;
      transition: opacity .15s, transform .1s;
    }
    .btn-submit:hover { opacity: .92; transform: translateY(-1px); }
    .btn-submit:active { opacity: 1; transform: translateY(0); }

    /* ── Demo accounts ── */
    .demo-box {
      background: #f8fafc;
      border: 1px dashed #e2e8f0;
      border-radius: 10px;
      padding: 12px 14px;
      margin-top: 20px;
    }
    .demo-label {
      font-size: 11px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .demo-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid #e8edf2;
      background: #fff;
      cursor: pointer;
      transition: border-color .15s, background .15s;
      margin-bottom: 6px;
    }
    .demo-row:last-child { margin-bottom: 0; }
    .demo-row:hover {
      border-color: var(--accent, #10b981);
      background: color-mix(in srgb, var(--accent, #10b981) 5%, #fff);
    }
    .demo-row-info { display: flex; align-items: center; gap: 10px; }
    .demo-row-icon { font-size: 18px; }
    .demo-role { font-size: 12.5px; font-weight: 600; color: #0f172a; }
    .demo-email { font-size: 11px; color: #64748b; }
    .demo-cta {
      font-size: 10.5px;
      font-weight: 600;
      color: #94a3b8;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 2px 8px;
      flex-shrink: 0;
    }

    /* ── Footer links ── */
    .login-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 20px;
      padding-top: 16px;
      border-top: 1px solid #f1f5f9;
    }
    .footer-link {
      font-size: 12px;
      color: #94a3b8;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: color .15s;
    }
    .footer-link:hover { color: #0f172a; }

    /* ── Alert ── */
    .alert-min {
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
    }
    .alert-min.danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .alert-min.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-min.info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

    /* ── Theme Colors ── */
    /* Datahub: emerald/green */
    .theme-success { --accent: #10b981; }
    /* Campaign: blue */
    .theme-primary { --accent: #2563eb; }
    /* KOL: purple */
    .theme-warning { --accent: #7c3aed; }
  </style>
</head>
<body class="theme-<?= esc($themeColor) ?>">

<!-- Brand Row -->
<div class="brand-row">
  <div class="brand-icon" style="background: color-mix(in srgb, var(--accent) 12%, #fff);">
    <i class="<?= esc($icon) ?>" style="color: var(--accent);"></i>
  </div>
  <span class="brand-name">KOLAB<span style="color: var(--accent);">.</span>ID</span>
  <span class="brand-sep">|</span>
  <span class="brand-portal"><?= esc($badgeText) ?></span>
</div>

<!-- Alerts -->
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert-min danger" style="width:100%;max-width:400px;">
    <i class="fas fa-exclamation-circle"></i>
    <?= esc(session()->getFlashdata('error')) ?>
  </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert-min success" style="width:100%;max-width:400px;">
    <i class="fas fa-check-circle"></i>
    <?= esc(session()->getFlashdata('success')) ?>
  </div>
<?php endif; ?>

<?php if (!empty($currentUser)): ?>
  <div class="alert-min info" style="width:100%;max-width:400px;justify-content:space-between;">
    <div><i class="fas fa-user-check me-1"></i> Terhubung: <strong><?= esc($currentUser['name']) ?></strong> (<?= esc($currentUser['role'] ?? 'User') ?>)</div>
    <a href="<?= base_url('dashboard') ?>" style="font-size:12px;font-weight:600;color:var(--accent);text-decoration:none;">
      Dashboard &rarr;
    </a>
  </div>
<?php endif; ?>

<!-- Login Card -->
<div class="login-card">

  <!-- Header -->
  <div style="margin-bottom: 24px;">
    <h1 class="card-title"><?= esc($portalTitle) ?></h1>
    <p class="card-subtitle"><?= esc($portalSubtitle) ?></p>
  </div>

  <!-- Form -->
  <form action="<?= esc($formAction) ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="portal" value="<?= esc($portal) ?>">

    <div class="field-wrap">
      <label class="field-label" for="login_email">Email Pengguna</label>
      <input type="email" name="email" id="login_email" class="field-input"
             placeholder="nama@kolab.id" value="<?= old('email') ?>" required autofocus>
      <i class="fas fa-envelope field-icon"></i>
    </div>

    <div class="field-wrap">
      <label class="field-label" for="login_password">Kata Sandi</label>
      <input type="password" name="password" id="login_password" class="field-input"
             placeholder="••••••••" required>
      <i class="fas fa-lock field-icon"></i>
    </div>

    <button type="submit" class="btn-submit">
      Masuk <i class="fas fa-arrow-right" style="font-size:12px;"></i>
    </button>
  </form>

  <!-- Demo Accounts -->
  <?php if (!empty($demoAccounts) || !empty($demoEmail)): ?>
  <div class="demo-box">
    <div class="demo-label">
      <i class="fas fa-user-friends" style="color: var(--accent);"></i>
      Akun Demo
    </div>

    <?php if (!empty($demoAccounts)): ?>
      <?php foreach ($demoAccounts as $acc): ?>
        <div class="demo-row" onclick="fillCredential('<?= esc($acc['email']) ?>', '<?= esc($acc['pass']) ?>')"
             title="<?= esc($acc['desc'] ?? '') ?>">
          <div class="demo-row-info">
            <span class="demo-row-icon"><i class="<?= esc($acc['icon'] ?? 'fas fa-user-circle') ?>" style="color: var(--accent);"></i></span>
            <div>
              <div class="demo-role"><?= esc($acc['role']) ?></div>
              <div class="demo-email"><?= esc($acc['email']) ?></div>
            </div>
          </div>
          <span class="demo-cta">Isi Otomatis</span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="demo-row" onclick="fillCredential('<?= esc($demoEmail) ?>', '<?= esc($demoPass) ?>')">
        <div class="demo-row-info">
          <span class="demo-row-icon"><i class="fas fa-user-circle" style="color: var(--accent);"></i></span>
          <div>
            <div class="demo-role"><?= esc($demoRole) ?></div>
            <div class="demo-email"><?= esc($demoEmail) ?></div>
          </div>
        </div>
        <span class="demo-cta">Isi Otomatis</span>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Footer Links -->
  <div class="login-footer">
    <a href="<?= root_url('login') ?>" class="footer-link">
      <i class="fas fa-th-large"></i> Portal Lain
    </a>
    <a href="<?= root_url('') ?>" class="footer-link">
      <i class="fas fa-globe"></i> Landing Page
    </a>
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
