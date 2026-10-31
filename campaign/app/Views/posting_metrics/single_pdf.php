<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #172b4d; margin: 30px; line-height: 1.5; }
    .header-box { border-bottom: 2px solid #0052cc; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
    .header-title { font-size: 20px; font-weight: bold; color: #0052cc; margin: 0; }
    .header-meta { font-size: 11px; color: #6b778c; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { border: 1px solid #dfe1e6; padding: 8px 12px; text-align: left; }
    th { background: #f4f5f7; width: 180px; font-weight: 600; color: #42526e; }
    .section-title { font-size: 14px; font-weight: bold; color: #172b4d; border-bottom: 1px solid #dfe1e6; padding-bottom: 5px; margin: 25px 0 10px 0; }
    .kpi-grid { display: flex; gap: 15px; margin-bottom: 20px; }
    .kpi-card { flex: 1; border: 1px solid #dfe1e6; background: #f9fafc; border-radius: 4px; padding: 12px; text-align: center; }
    .kpi-val { font-size: 18px; font-weight: bold; color: #0052cc; margin-top: 4px; }
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

  <div class="header-box">
    <div>
      <h1 class="header-title">LAPORAN POSTING METRIC SNAPSHOT</h1>
      <div style="font-size: 12px; color: #42526e; margin-top: 4px;">Kolab.id Influencer Marketing &amp; Video Intelligence Platform</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($printedAt) ?></div>
      <div class="header-meta">Snapshot ID: #<?= $metric['id'] ?></div>
    </div>
  </div>

  <div class="kpi-grid">
    <div class="kpi-card">
      <div style="font-size: 11px; text-transform: uppercase; color: #6b778c;">Total Views</div>
      <div class="kpi-val"><?= number_format((int)$metric['resolved_views'], 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card">
      <div style="font-size: 11px; text-transform: uppercase; color: #6b778c;">Engagement Rate</div>
      <div class="kpi-val" style="color: #006644;"><?= number_format((float)$metric['engagement_rate'], 2) ?>%</div>
    </div>
    <div class="kpi-card">
      <div style="font-size: 11px; text-transform: uppercase; color: #6b778c;">Cost Per View (CPV)</div>
      <div class="kpi-val">Rp <?= number_format((float)$metric['resolved_cpv'], 2, ',', '.') ?></div>
    </div>
    <div class="kpi-card">
      <div style="font-size: 11px; text-transform: uppercase; color: #6b778c;">Total Likes</div>
      <div class="kpi-val" style="color: #ff8b00;"><?= number_format((int)$metric['resolved_likes'], 0, ',', '.') ?></div>
    </div>
  </div>

  <div class="section-title">RINCIAN POSTING &amp; KAMPANYE</div>
  <table>
    <tr>
      <th>ID Snapshot</th>
      <td><strong>#<?= $metric['id'] ?></strong></td>
      <th>Waktu Snapshot</th>
      <td><strong><?= date('d F Y H:i:s', strtotime($metric['snapshot_at'] ?: $metric['created_at'])) ?> WIB</strong></td>
    </tr>
    <tr>
      <th>URL Video Live</th>
      <td colspan="3"><a href="<?= esc($metric['postings_live_url'] ?? '#') ?>"><?= esc($metric['postings_live_url'] ?? '-') ?></a></td>
    </tr>
    <tr>
      <th>Judul Kampanye</th>
      <td><strong><?= esc($metric['campaign_title'] ?? '-') ?></strong></td>
      <th>Nama Talent KOL</th>
      <td><strong><?= esc($metric['kol_name'] ?? '-') ?></strong></td>
    </tr>
    <tr>
      <th>Target Tugas / VT</th>
      <td><?= esc($metric['task_title'] ?: ('VT #' . ($metric['task_number'] ?? '1'))) ?></td>
      <th>Platform</th>
      <td><?= esc($metric['platform_name'] ?? 'TikTok') ?></td>
    </tr>
  </table>

  <div class="section-title">RINCIAN INTERAKSI &amp; EFISIENSI BIAYA</div>
  <table>
    <tr>
      <th>Views</th>
      <td><?= number_format((int)$metric['resolved_views'], 0, ',', '.') ?></td>
      <th>Likes</th>
      <td><?= number_format((int)$metric['resolved_likes'], 0, ',', '.') ?></td>
    </tr>
    <tr>
      <th>Comments</th>
      <td><?= number_format((int)$metric['resolved_comments'], 0, ',', '.') ?></td>
      <th>Shares</th>
      <td><?= number_format((int)$metric['resolved_shares'], 0, ',', '.') ?></td>
    </tr>
    <tr>
      <th>Saves / Bookmark</th>
      <td><?= number_format((int)$metric['resolved_saves'], 0, ',', '.') ?></td>
      <th>Total Likes Profil</th>
      <td><?= number_format((int)$metric['total_likes'], 0, ',', '.') ?></td>
    </tr>
    <tr>
      <th>Followers</th>
      <td><?= number_format((int)$metric['followers'], 0, ',', '.') ?></td>
      <th>Impressions</th>
      <td><?= number_format((int)$metric['impressions'], 0, ',', '.') ?></td>
    </tr>
    <tr>
      <th>Reach</th>
      <td><?= number_format((int)$metric['reach'], 0, ',', '.') ?></td>
      <th>View Rate</th>
      <td><?= number_format((float)$metric['view_rate'], 2) ?>%</td>
    </tr>
    <tr>
      <th>Actual CPV</th>
      <td><strong>Rp <?= number_format((float)$metric['resolved_cpv'], 2, ',', '.') ?></strong></td>
      <th>Actual CPA</th>
      <td><strong>Rp <?= number_format((float)$metric['cpa'], 2, ',', '.') ?></strong></td>
    </tr>
    <tr>
      <th>Average Views</th>
      <td><?= number_format((float)$metric['average_views'], 0, ',', '.') ?></td>
      <th>Median Views</th>
      <td><?= number_format((float)$metric['median_views'], 0, ',', '.') ?></td>
    </tr>
  </table>

  <div style="margin-top: 40px; text-align: center; font-size: 11px; color: #6b778c;">
    &mdash; Dokumen ini dihasilkan secara otomatis oleh sistem Kolab.id &mdash;
  </div>
</body>
</html>
