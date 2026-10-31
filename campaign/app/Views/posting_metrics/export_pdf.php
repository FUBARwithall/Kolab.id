<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 11.5px; color: #333; margin: 20px; }
    h2 { margin: 0 0 5px 0; font-size: 17px; color: #172b4d; }
    .subtitle { color: #6b778c; font-size: 11px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #dfe1e6; padding: 6px 8px; text-align: left; }
    th { background: #f4f5f7; font-weight: 600; font-size: 10.5px; text-transform: uppercase; color: #5e6c84; }
    .text-end { text-align: right; }
    .text-center { text-align: center; }
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
  <div class="subtitle">Dicetak pada: <?= esc($printedAt) ?> &bull; Sistem Kolab.id Campaign Engine</div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;">#</th>
        <th>URL Postingan</th>
        <th>Kampanye</th>
        <th>Talent KOL</th>
        <th class="text-end">Views</th>
        <th class="text-end">Likes</th>
        <th class="text-end">Comments</th>
        <th class="text-end">Shares</th>
        <th class="text-center">ER (%)</th>
        <th class="text-end">CPV</th>
        <th style="width: 100px;">Snapshot</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($metrics)): ?>
        <?php foreach ($metrics as $idx => $m): ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td><?= esc(substr($m['postings_live_url'] ?? '-', 0, 35)) ?></td>
            <td><?= esc($m['campaign_title'] ?? '-') ?></td>
            <td><strong><?= esc($m['kol_name'] ?? '-') ?></strong></td>
            <td class="text-end"><strong><?= number_format((int)$m['resolved_views'], 0, ',', '.') ?></strong></td>
            <td class="text-end"><?= number_format((int)$m['resolved_likes'], 0, ',', '.') ?></td>
            <td class="text-end"><?= number_format((int)$m['resolved_comments'], 0, ',', '.') ?></td>
            <td class="text-end"><?= number_format((int)$m['resolved_shares'], 0, ',', '.') ?></td>
            <td class="text-center"><strong><?= number_format((float)$m['engagement_rate'], 2) ?>%</strong></td>
            <td class="text-end">Rp <?= number_format((float)$m['resolved_cpv'], 2, ',', '.') ?></td>
            <td><?= date('d/m/y H:i', strtotime($m['snapshot_at'] ?: $m['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="11" class="text-center" style="color: #999;">Tidak ada data metrics.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
