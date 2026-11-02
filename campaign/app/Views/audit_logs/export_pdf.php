<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 11px;
      color: #333;
      margin: 15px;
    }
    h2 {
      margin: 0 0 5px 0;
      font-size: 16px;
      color: #172b4d;
    }
    .subtitle {
      color: #6b778c;
      font-size: 10.5px;
      margin-bottom: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #dfe1e6;
      padding: 6px 8px;
      text-align: left;
      vertical-align: top;
    }
    th {
      background: #f4f5f7;
      font-weight: 600;
      font-size: 10px;
      text-transform: uppercase;
      color: #5e6c84;
    }
    .text-center { text-align: center; }
    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 3px;
      font-weight: bold;
      font-size: 9px;
    }
    .badge-create { background: #e3fcef; color: #006644; }
    .badge-update { background: #deebff; color: #0747a6; }
    .badge-delete { background: #ffebe6; color: #de350b; }
    .badge-approve { background: #e3fcef; color: #006644; }
    .badge-reject { background: #ffebe6; color: #de350b; }
    .badge-verify { background: #deebff; color: #0747a6; }
    .badge-login { background: #f4f5f7; color: #42526e; }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 10px;
    }
    @media print {
      body { margin: 0; }
      .no-print { display: none; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom: 15px;">
    <button onclick="window.print()" style="padding: 7px 15px; background: #0052cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      Cetak / Simpan PDF
    </button>
  </div>

  <h2><?= esc($title) ?></h2>
  <div class="subtitle">
    Dicetak pada: <?= esc($generated) ?> &bull; Filter: <?= esc($filters['action'] ?? 'Semua Aksi') ?> / <?= esc($filters['entity_type'] ?? 'Semua Entitas') ?> &bull; Total Record: <?= count($items) ?> &bull; Kolab.id System Security &amp; Audit Trail
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th style="width: 45px;" class="text-center">ID</th>
        <th style="width: 110px;">Waktu Kejadian</th>
        <th>Operator &amp; Role</th>
        <th style="width: 80px;" class="text-center">Aksi</th>
        <th style="width: 120px;">Entitas Target</th>
        <th style="width: 95px;">Alamat IP</th>
        <th>Ringkasan Perubahan</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): ?>
          <?php
            $actClass = 'badge-login';
            switch ($row['action']) {
              case 'CREATE': $actClass = 'badge-create'; break;
              case 'UPDATE': $actClass = 'badge-update'; break;
              case 'DELETE': $actClass = 'badge-delete'; break;
              case 'APPROVE': $actClass = 'badge-approve'; break;
              case 'REJECT': $actClass = 'badge-reject'; break;
              case 'VERIFY': $actClass = 'badge-verify'; break;
            }

            // Summary of changes
            $diffSummary = '-';
            if (!empty($row['new_values'])) {
              $diffSummary = (strlen($row['new_values']) > 90) ? substr($row['new_values'], 0, 90) . '...' : $row['new_values'];
            } elseif (!empty($row['old_values'])) {
              $diffSummary = 'Deleted: ' . ((strlen($row['old_values']) > 80) ? substr($row['old_values'], 0, 80) . '...' : $row['old_values']);
            }
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="text-center font-mono">#<?= $row['id'] ?></td>
            <td>
              <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?> WIB
            </td>
            <td>
              <strong><?= esc($row['user_name'] ?? 'System') ?></strong><br>
              <small style="color: #6b778c;"><?= esc($row['role_label'] ?? 'Operator') ?> &bull; <?= esc($row['user_email'] ?? '-') ?></small>
            </td>
            <td class="text-center">
              <span class="badge <?= $actClass ?>"><?= esc($row['action']) ?></span>
            </td>
            <td>
              <strong class="font-mono" style="text-transform: uppercase;"><?= esc($row['entity_type']) ?></strong>
              <span class="font-mono" style="color: #0052cc;">#<?= $row['entity_id'] ?></span>
            </td>
            <td class="font-mono"><?= esc($row['ip_address'] ?? '127.0.0.1') ?></td>
            <td class="font-mono" style="font-size: 9.5px; color: #42526e;"><?= esc($diffSummary) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-center" style="color: #999; padding: 20px;">
            Tidak ada data log audit yang sesuai dengan kriteria filter.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
