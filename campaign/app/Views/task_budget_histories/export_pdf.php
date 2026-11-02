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
    .text-end { text-align: right; }
    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 3px;
      font-weight: bold;
      font-size: 9px;
    }
    .badge-increase { background: #e3fcef; color: #006644; }
    .badge-decrease { background: #ffebe6; color: #de350b; }
    .badge-same { background: #f4f5f7; color: #42526e; }
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
    Dicetak pada: <?= esc($generated) ?> &bull; Total Record: <?= count($items) ?> &bull; Kolab.id Task Budget Audit &amp; Negotiation Trail
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th style="width: 40px;" class="text-center">ID</th>
        <th>Task Konten</th>
        <th>Kampanye &amp; Talent KOL</th>
        <th style="width: 95px;" class="text-end">Tarif Semula</th>
        <th style="width: 95px;" class="text-end">Tarif Baru</th>
        <th style="width: 90px;" class="text-center">Selisih</th>
        <th>Diotorisasi Oleh</th>
        <th>Alasan Perubahan</th>
        <th style="width: 105px;">Waktu Perubahan</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): ?>
          <?php
            $curr = $row['currency_code'] ?? 'IDR';
            $oldRate = (float)$row['old_rate'];
            $newRate = (float)$row['new_rate'];
            $diff = $newRate - $oldRate;

            if ($diff > 0) {
              $deltaCls = 'badge-increase';
              $deltaText = '+' . number_format($diff, 0, ',', '.');
            } elseif ($diff < 0) {
              $deltaCls = 'badge-decrease';
              $deltaText = '-' . number_format(abs($diff), 0, ',', '.');
            } else {
              $deltaCls = 'badge-same';
              $deltaText = '0';
            }
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="text-center font-mono">#<?= $row['id'] ?></td>
            <td>
              <strong><?= esc($row['task_title'] ?: ('Task #' . ($row['task_number'] ?? $row['task_id']))) ?></strong>
              <div style="color: #6b778c; font-size: 9.5px;">Task ID #<?= $row['task_id'] ?></div>
            </td>
            <td>
              <strong><?= esc($row['campaign_title'] ?? '-') ?></strong><br>
              <small style="color: #0052cc;"><?= esc($row['kol_name'] ?? '-') ?></small>
            </td>
            <td class="text-end font-mono">
              <?= $curr ?> <?= number_format($oldRate, 0, ',', '.') ?>
            </td>
            <td class="text-end font-mono">
              <strong><?= $curr ?> <?= number_format($newRate, 0, ',', '.') ?></strong>
            </td>
            <td class="text-center">
              <span class="badge <?= $deltaCls ?>"><?= $deltaText ?></span>
            </td>
            <td>
              <strong><?= esc($row['authorized_name'] ?? 'System') ?></strong><br>
              <small style="color: #6b778c;"><?= esc($row['authorized_email'] ?? '-') ?></small>
            </td>
            <td style="font-size: 9.5px; color: #42526e;"><?= esc($row['reason'] ?? '-') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?> WIB</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="10" class="text-center" style="color: #999; padding: 20px;">
            Tidak ada riwayat penyesuaian tarif task yang tercatat.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
