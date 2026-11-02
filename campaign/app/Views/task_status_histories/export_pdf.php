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
    .badge-approved { background: #e3fcef; color: #006644; }
    .badge-revision { background: #fffae6; color: #ff8b00; }
    .badge-submitted { background: #deebff; color: #0747a6; }
    .badge-default { background: #f4f5f7; color: #42526e; }
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
  <div class="subtitle">Dicetak pada: <?= esc($generated) ?> &bull; Sistem Kolab.id Task Management &amp; Audit Engine</div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th>Tugas (Task)</th>
        <th style="width: 140px;">Transisi Status</th>
        <th>Kampanye</th>
        <th>Talent KOL</th>
        <th>Catatan Transisi</th>
        <th>Aktor / PIC</th>
        <th style="width: 100px;">Waktu Transisi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): ?>
          <?php
            $fromSt = !empty($row['from_status']) ? strtoupper(trim((string)$row['from_status'])) : null;
            $toSt   = strtoupper(trim((string)($row['to_status'] ?: ($row['status'] ?: 'DRAFT'))));

            $badgeCls = match ($toSt) {
              'BRIEF_APPROVED', 'DRAFT_APPROVED', 'APPROVED_DRAFT', 'COMPLETED', 'DONE', 'POSTED' => 'badge-approved',
              'BRIEF_REVISION', 'DRAFT_REVISION', 'REVISION', 'REVISION_DRAFT' => 'badge-revision',
              'BRIEF_SUBMITTED', 'DRAFT_SUBMITTED', 'PENDING_REVIEW', 'IN_REVIEW_DRAFT', 'WAITING_DRAFT' => 'badge-submitted',
              default => 'badge-default'
            };
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td>
              <strong><?= esc($row['task_title'] ?: ('Task #' . $row['task_id'])) ?></strong><br>
              <small style="color: #6b778c;">Task No: #<?= esc($row['task_number'] ?? '1') ?></small>
            </td>
            <td>
              <?php if ($fromSt && $fromSt !== $toSt): ?>
                <span style="color: #6b778c;"><?= esc($fromSt) ?></span> &rarr;
              <?php endif; ?>
              <span class="badge <?= $badgeCls ?>"><?= esc($toSt) ?></span>
            </td>
            <td><?= esc($row['campaign_title'] ?? '-') ?></td>
            <td><?= esc($row['kol_name'] ?? '-') ?></td>
            <td><?= esc($row['resolved_note'] ?: '-') ?></td>
            <td>
              <strong><?= esc($row['changed_by_name'] ?? 'System') ?></strong><br>
              <small style="color: #6b778c;"><?= esc($row['changed_by_email'] ?? '') ?></small>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center" style="color: #999;">Tidak ada data riwayat perubahan status task.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
