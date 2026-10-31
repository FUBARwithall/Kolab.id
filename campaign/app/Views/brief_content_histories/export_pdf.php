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
        <th>Judul Brief</th>
        <th style="width: 50px;" class="text-center">Versi</th>
        <th>Judul Snapshot</th>
        <th>Kampanye</th>
        <th>Talent KOL</th>
        <th>Editor</th>
        <th style="width: 120px;">Waktu Snapshot</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($histories)): ?>
        <?php foreach ($histories as $idx => $h): ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td><strong><?= esc($h['briefs_title'] ?? ('Brief #' . $h['brief_id'])) ?></strong></td>
            <td class="text-center"><strong>v<?= (int)($h['version'] ?? 1) ?></strong></td>
            <td><?= esc($h['title'] ?: '-') ?></td>
            <td><?= esc($h['campaign_title'] ?? '-') ?></td>
            <td><?= esc($h['kol_name'] ?? '-') ?></td>
            <td><?= esc($h['author_name'] ?? 'Editor') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($h['snapshot_at'] ?: $h['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center" style="color: #999;">Tidak ada data snapshot.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
