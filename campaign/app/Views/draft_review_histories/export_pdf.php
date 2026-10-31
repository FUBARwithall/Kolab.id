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
    .badge-approved { background: #e3fcef; color: #006644; padding: 2px 6px; border-radius: 3px; font-weight: bold; }
    .badge-revision { background: #fffae6; color: #ff8b00; padding: 2px 6px; border-radius: 3px; font-weight: bold; }
    .badge-submitted { background: #deebff; color: #0747a6; padding: 2px 6px; border-radius: 3px; font-weight: bold; }
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
        <th>Draft ID / Versi</th>
        <th>Tugas (Task)</th>
        <th>Kampanye</th>
        <th>Talent KOL</th>
        <th style="width: 80px;" class="text-center">Keputusan</th>
        <th>Catatan Review (Note)</th>
        <th>Reviewer (Leader)</th>
        <th style="width: 110px;">Waktu Review</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($histories)): ?>
        <?php foreach ($histories as $idx => $h): ?>
          <?php
            $act = strtoupper(trim((string)$h['resolved_action']));
            $badgeCls = match ($act) {
              'APPROVED' => 'badge-approved',
              'REVISION', 'REVISION_NEEDED' => 'badge-revision',
              'SUBMITTED', 'PENDING' => 'badge-submitted',
              default => ''
            };
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td><strong>Draft #<?= $h['draft_id'] ?> (v<?= $h['draft_version'] ?? '1' ?>)</strong></td>
            <td><?= esc($h['task_title'] ?: ('VT #' . ($h['task_number'] ?? '1'))) ?></td>
            <td><?= esc($h['campaign_title'] ?? '-') ?></td>
            <td><?= esc($h['kol_name'] ?? '-') ?></td>
            <td class="text-center"><span class="<?= $badgeCls ?>"><?= esc($act) ?></span></td>
            <td><?= esc($h['resolved_note'] ?: '-') ?></td>
            <td><?= esc($h['reviewer_name'] ?? 'Reviewer') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="9" class="text-center" style="color: #999;">Tidak ada data riwayat review draft video.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
