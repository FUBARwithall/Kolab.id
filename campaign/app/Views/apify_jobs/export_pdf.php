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
    .badge-success { background: #e3fcef; color: #006644; }
    .badge-info { background: #deebff; color: #0747a6; }
    .badge-warning { background: #fffae6; color: #ff8b00; }
    .badge-danger { background: #ffebe6; color: #de350b; }
    .badge-purple { background: #ede7f6; color: #673ab7; }
    .badge-post { background: #e1f5fe; color: #0288d1; }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 10px;
    }
    .url-text {
      max-width: 260px;
      word-break: break-all;
      display: block;
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
    Dicetak pada: <?= esc($generated) ?> &bull; Filter Status: <?= esc($filters['status'] ?? 'Semua Status') ?> &bull; Total Record: <?= count($items) ?> &bull; Kolab.id Scraper &amp; Automation Audit
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 35px;" class="text-center">ID</th>
        <th style="width: 75px;">Tipe Actor</th>
        <th>Target URL &amp; Live Posting</th>
        <th>Talent &amp; Kampanye</th>
        <th style="width: 80px;" class="text-center">Status</th>
        <th style="width: 60px;" class="text-center">Percobaan</th>
        <th style="width: 110px;">Waktu Dibuat</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $row): ?>
          <?php
            $statusCls = 'badge-warning';
            switch ($row['status']) {
              case 'SUCCEEDED':
              case 'SUCCESS':
                $statusCls = 'badge-success';
                break;
              case 'RUNNING':
                $statusCls = 'badge-info';
                break;
              case 'FAILED':
                $statusCls = 'badge-danger';
                break;
              case 'QUEUED':
              default:
                $statusCls = 'badge-warning';
                break;
            }

            $actorCls = ($row['actor_type'] === 'post') ? 'badge-post' : 'badge-purple';
            $actorLabel = ($row['actor_type'] === 'post') ? 'Video Post' : 'Profile KOL';
          ?>
          <tr>
            <td class="text-center font-mono">#<?= $row['id'] ?></td>
            <td>
              <span class="badge <?= $actorCls ?>"><?= $actorLabel ?></span>
            </td>
            <td>
              <span class="font-mono url-text"><?= esc($row['target_url']) ?></span>
              <?php if (!empty($row['postings_live_url']) && $row['postings_live_url'] !== $row['target_url']): ?>
                <div style="color: #6b778c; font-size: 9.5px; margin-top: 3px;">
                  Live: <span class="font-mono"><?= esc($row['postings_live_url']) ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($row['external_run_id'])): ?>
                <div style="color: #6b778c; font-size: 9px; margin-top: 2px;">
                  Run ID: <span class="font-mono"><?= esc($row['external_run_id']) ?></span>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($row['kol_name']) || !empty($row['campaign_title'])): ?>
                <strong><?= esc($row['kol_name'] ?? 'KOL') ?></strong><br>
                <small style="color: #6b778c;"><?= esc($row['campaign_title'] ?? '-') ?></small>
                <?php if (!empty($row['posting_id'])): ?>
                  <span style="font-size: 9px; color: #0052cc;">(Post #<?= $row['posting_id'] ?>)</span>
                <?php endif; ?>
              <?php else: ?>
                <span style="color: #999; font-style: italic;">Standalone</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <span class="badge <?= $statusCls ?>"><?= esc($row['status']) ?></span>
              <?php if ($row['status'] === 'FAILED' && !empty($row['error_message'])): ?>
                <div style="color: #de350b; font-size: 8.5px; margin-top: 3px; max-width: 120px; word-break: break-word;">
                  <?= esc(substr($row['error_message'], 0, 70)) . (strlen($row['error_message']) > 70 ? '...' : '') ?>
                </div>
              <?php endif; ?>
            </td>
            <td class="text-center">
              Ke-<?= $row['attempt_number'] ?? 1 ?>
            </td>
            <td>
              <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>
              <?php if (!empty($row['finished_at'])): ?>
                <div style="font-size: 9px; color: #6b778c;">
                  Finish: <?= date('d/m/Y H:i', strtotime($row['finished_at'])) ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center" style="color: #999; padding: 20px;">
            Tidak ada riwayat scraping jobs yang sesuai dengan filter.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
