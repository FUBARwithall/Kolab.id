<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
    h2 { margin: 0 0 5px 0; font-size: 18px; color: #172b4d; }
    .subtitle { color: #6b778c; font-size: 12px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #dfe1e6; padding: 7px 10px; text-align: left; }
    th { background: #f4f5f7; font-weight: 600; font-size: 11px; text-transform: uppercase; color: #5e6c84; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }
    .badge-approved { background: #e3fcef; color: #006644; }
    .badge-revision { background: #fffae6; color: #ff8b00; }
    .badge-pending { background: #deebff; color: #0747a6; }
    .badge-rejected { background: #ffebe6; color: #bf2600; }
    @media print {
      body { margin: 0; }
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

  <h2><?= esc($title) ?></h2>
  <div class="subtitle">Dicetak pada: <?= esc($printedAt) ?> &bull; Sistem Kolab.id Campaign Engine</div>

  <table>
    <thead>
      <tr>
        <th style="width: 30px;">#</th>
        <th>Judul Brief</th>
        <th>Target Task</th>
        <th>Kampanye</th>
        <th>Talent KOL</th>
        <th style="width: 60px;">Revisi</th>
        <th style="width: 100px;">Status</th>
        <th style="width: 110px;">Dibuat</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($briefs)): ?>
        <?php foreach ($briefs as $idx => $b): ?>
          <?php 
            $st = strtoupper((string)($b['status'] ?: $b['approval_status']));
            $badgeClass = match($st) {
              'APPROVED' => 'badge-approved',
              'REVISION', 'REVISION_NEEDED' => 'badge-revision',
              'REJECTED' => 'badge-rejected',
              default => 'badge-pending',
            };
          ?>
          <tr>
            <td style="text-align: center;"><?= $idx + 1 ?></td>
            <td><strong><?= esc($b['title']) ?></strong></td>
            <td><?= esc($b['task_title'] ?: ('VT #' . $b['task_number'])) ?></td>
            <td><?= esc($b['campaign_title']) ?></td>
            <td><?= esc($b['kol_name']) ?></td>
            <td style="text-align: center;"><?= (int)($b['revision_count'] ?? 0) ?>x</td>
            <td><span class="badge <?= $badgeClass ?>"><?= esc($st) ?></span></td>
            <td><?= !empty($b['created_at']) ? date('d/m/Y H:i', strtotime($b['created_at'])) : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align: center; color: #999;">Tidak ada data brief.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
