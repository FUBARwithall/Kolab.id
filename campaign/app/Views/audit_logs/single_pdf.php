<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 12px;
      color: #172b4d;
      margin: 30px;
      line-height: 1.5;
    }
    .header-box {
      border-bottom: 2px solid #0052cc;
      padding-bottom: 12px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    .header-title {
      font-size: 18px;
      font-weight: bold;
      color: #172b4d;
      margin: 0;
    }
    .header-meta {
      font-size: 11px;
      color: #6b778c;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #dfe1e6;
      padding: 8px 12px;
      text-align: left;
    }
    th {
      background: #f4f5f7;
      width: 180px;
      font-weight: 600;
      color: #42526e;
    }
    .section-title {
      font-size: 13px;
      font-weight: bold;
      color: #172b4d;
      border-bottom: 1px solid #dfe1e6;
      padding-bottom: 5px;
      margin: 20px 0 10px 0;
    }
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 11px;
    }
    .badge-create { background: #e3fcef; color: #006644; }
    .badge-update { background: #deebff; color: #0747a6; }
    .badge-delete { background: #ffebe6; color: #de350b; }
    .badge-approve { background: #e3fcef; color: #006644; }
    .badge-reject { background: #ffebe6; color: #de350b; }
    .badge-verify { background: #deebff; color: #0747a6; }
    .badge-login { background: #f4f5f7; color: #42526e; }
    .status-banner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fafbfc;
      border: 1px solid #dfe1e6;
      border-radius: 6px;
      padding: 15px 20px;
      margin-bottom: 20px;
    }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 11px;
    }
    pre {
      background: #f4f5f7;
      border: 1px solid #dfe1e6;
      padding: 10px;
      border-radius: 4px;
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 10.5px;
      white-space: pre-wrap;
      word-break: break-all;
      max-height: 300px;
      overflow: hidden;
      margin: 0;
    }
    @media print {
      body { margin: 15px; }
      .no-print { display: none; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom: 20px;">
    <button onclick="window.print()" style="padding: 8px 16px; background: #0052cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      Cetak / Simpan PDF
    </button>
  </div>

  <?php
    $actClass = 'badge-login';
    switch ($log['action']) {
      case 'CREATE': $actClass = 'badge-create'; break;
      case 'UPDATE': $actClass = 'badge-update'; break;
      case 'DELETE': $actClass = 'badge-delete'; break;
      case 'APPROVE': $actClass = 'badge-approve'; break;
      case 'REJECT': $actClass = 'badge-reject'; break;
      case 'VERIFY': $actClass = 'badge-verify'; break;
    }

    $formattedOld = null;
    if (!empty($log['old_values'])) {
      $dec = json_decode($log['old_values'], true);
      $formattedOld = $dec ? json_encode($dec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $log['old_values'];
    }

    $formattedNew = null;
    if (!empty($log['new_values'])) {
      $dec = json_decode($log['new_values'], true);
      $formattedNew = $dec ? json_encode($dec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $log['new_values'];
    }
  ?>

  <div class="header-box">
    <div>
      <h1 class="header-title">LEMBAR BUKTI REKAM AUDIT LOG SISTEM</h1>
      <div style="font-size: 11px; color: #42526e; margin-top: 3px;">Kolab.id Platform &bull; Security, Compliance &amp; Operation Audit Trail</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($generated) ?></div>
      <div class="header-meta">Audit Record ID: #<?= $log['id'] ?></div>
    </div>
  </div>

  <div class="status-banner">
    <div>
      <div style="font-size: 10px; text-transform: uppercase; color: #6b778c; font-weight: bold; margin-bottom: 2px;">TINDAKAN (ACTION)</div>
      <span class="badge <?= $actClass ?>" style="font-size: 14px; padding: 4px 12px;"><?= esc($log['action']) ?></span>
      <span style="margin-left: 10px; font-size: 12px; color: #42526e;">Target: <strong class="font-mono text-uppercase"><?= esc($log['entity_type']) ?> #<?= $log['entity_id'] ?></strong></span>
    </div>
    <div style="text-align: right;">
      <div style="font-size: 10px; text-transform: uppercase; color: #6b778c; font-weight: bold; margin-bottom: 2px;">WAKTU EKSEKUSI</div>
      <span class="font-mono" style="font-size: 12px; font-weight: bold; color: #172b4d;"><?= date('d F Y, H:i:s', strtotime($log['created_at'])) ?> WIB</span>
    </div>
  </div>

  <div class="section-title">METADATA OPERATOR &amp; JARINGAN</div>
  <table>
    <tr>
      <th>Nama Operator</th>
      <td><strong><?= esc($log['user_name'] ?? 'System / Automated') ?></strong></td>
      <th>User ID</th>
      <td><?= !empty($log['user_id']) ? '#' . $log['user_id'] : '-' ?></td>
    </tr>
    <tr>
      <th>Email Operator</th>
      <td><?= esc($log['user_email'] ?? '-') ?></td>
      <th>Role / Jabatan</th>
      <td><?= esc($log['role_label'] ?? ($log['role_name'] ?? 'Operator')) ?></td>
    </tr>
    <tr>
      <th>Alamat IP Client</th>
      <td><span class="font-mono"><?= esc($log['ip_address'] ?? '127.0.0.1') ?></span></td>
      <th>User Agent</th>
      <td><small class="font-mono" style="word-break: break-all;"><?= esc($log['user_agent'] ?? '-') ?></small></td>
    </tr>
  </table>

  <div class="section-title">PERUBAHAN DATA: NILAI SEBELUMNYA (OLD VALUES)</div>
  <?php if (!empty($formattedOld)): ?>
    <pre><?= esc($formattedOld) ?></pre>
  <?php else: ?>
    <div style="background: #fafbfc; border: 1px dashed #dfe1e6; padding: 10px; border-radius: 4px; color: #6b778c; font-style: italic;">
      Tidak ada data sebelumnya (Record baru dibuat / Null).
    </div>
  <?php endif; ?>

  <div class="section-title" style="margin-top: 20px;">PERUBAHAN DATA: NILAI BARU (NEW VALUES)</div>
  <?php if (!empty($formattedNew)): ?>
    <pre><?= esc($formattedNew) ?></pre>
  <?php else: ?>
    <div style="background: #fafbfc; border: 1px dashed #dfe1e6; padding: 10px; border-radius: 4px; color: #6b778c; font-style: italic;">
      Tidak ada nilai baru (Record telah dihapus / Null).
    </div>
  <?php endif; ?>

  <div style="margin-top: 35px; border-top: 1px dashed #ccc; padding-top: 8px; font-size: 10px; color: #6b778c; text-align: center;">
    Lembar ini adalah dokumen audit resmi internal yang dihasilkan oleh mesin audit logging Kolab.id.
  </div>
</body>
</html>
