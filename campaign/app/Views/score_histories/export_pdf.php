<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 15px; }
    h2 { margin: 0 0 5px 0; font-size: 16px; color: #172b4d; }
    .subtitle { color: #6b778c; font-size: 10.5px; margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #dfe1e6; padding: 6px 8px; text-align: left; }
    th { background: #f4f5f7; font-weight: 600; font-size: 10px; text-transform: uppercase; color: #5e6c84; }
    .text-center { text-align: center; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 9.5px; }
    .badge-excellent { background: #e3fcef; color: #006644; }
    .badge-good { background: #deebff; color: #0747a6; }
    .badge-fair { background: #fffae6; color: #ff8b00; }
    .badge-poor { background: #ffebe6; color: #de350b; }
    .badge-collab { background: #e6fcff; color: #008da6; }
    .badge-lifetime { background: #eae6ff; color: #403294; }
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
  <div class="subtitle">Dicetak pada: <?= esc($generated) ?> &bull; Kolab.id Talent Performance &amp; Evaluation Engine</div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th>Talent KOL</th>
        <th>Kampanye Terkait</th>
        <th style="width: 110px;">Tipe Skor</th>
        <th style="width: 100px;">Nilai Skor</th>
        <th style="width: 80px;" class="text-center">Grade</th>
        <th style="width: 120px;">Waktu Perhitungan</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): ?>
          <?php
            $scoreVal = (float)$row['score'];
            if ($scoreVal >= 85) {
              $grade = 'Grade A';
              $badgeCls = 'badge-excellent';
            } elseif ($scoreVal >= 70) {
              $grade = 'Grade B';
              $badgeCls = 'badge-good';
            } elseif ($scoreVal >= 55) {
              $grade = 'Grade C';
              $badgeCls = 'badge-fair';
            } else {
              $grade = 'Grade D';
              $badgeCls = 'badge-poor';
            }

            $typeSt = strtoupper((string)$row['score_type']);
            $typeCls = $typeSt === 'COLLABORATION' ? 'badge-collab' : 'badge-lifetime';
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td>
              <strong><?= esc($row['kol_name'] ?? ('KOL #' . $row['kol_id'])) ?></strong><br>
              <small style="color: #6b778c;"><?= esc($row['kol_email'] ?? '-') ?></small>
            </td>
            <td><?= esc($row['campaign_title'] ?? 'Lifetime (Global)') ?></td>
            <td>
              <span class="badge <?= $typeCls ?>"><?= $typeSt ?></span>
            </td>
            <td>
              <strong><?= number_format($scoreVal, 2) ?></strong> / 100
            </td>
            <td class="text-center">
              <span class="badge <?= $badgeCls ?>"><?= $grade ?></span>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($row['calculated_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center" style="color: #999;">Tidak ada data riwayat evaluasi skor KOL.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
