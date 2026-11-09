<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 12px;
      color: #172b4d;
      margin: 30px;
      line-height: 1.5;
    }
    .header-box {
      border-bottom: 2px solid #de350b;
      padding-bottom: 12px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    .header-title {
      font-size: 18px;
      font-weight: bold;
      color: #172b4d;
      margin: 0;
    }
    .header-meta {
      font-size: 11px;
      color: #6b778c;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #dfe1e6;
      padding: 8px 12px;
      text-align: left;
    }
    th {
      background: #f4f5f7;
      width: 180px;
      font-weight: 600;
      color: #42526e;
    }
    .section-title {
      font-size: 13px;
      font-weight: bold;
      color: #172b4d;
      border-bottom: 1px solid #dfe1e6;
      padding-bottom: 5px;
      margin: 20px 0 10px 0;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 11px;
    }
    .badge-success { background: #e3fcef; color: #006644; }
    .badge-info { background: #deebff; color: #0747a6; }
    .badge-warning { background: #fffae6; color: #ff8b00; }
    .badge-danger { background: #ffebe6; color: #de350b; }
    .badge-purple { background: #ede7f6; color: #673ab7; }
    .status-banner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fafbfc;
      border: 1px solid #dfe1e6;
      border-radius: 6px;
      padding: 15px 20px;
      margin-bottom: 20px;
    }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 11px;
    }
    pre {
      background: #f4f5f7;
      border: 1px solid #dfe1e6;
      padding: 10px;
      border-radius: 4px;
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 10.5px;
      white-space: pre-wrap;
      word-break: break-all;
      max-height: 350px;
      overflow: hidden;
    }
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

  <?php
    $statusCls = 'badge-warning';
    switch ($job['status']) {
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

    $durationText = '-';
    if (!empty($job['started_at']) && !empty($job['finished_at'])) {
      $diff = max(0, strtotime($job['finished_at']) - strtotime($job['started_at']));
      $durationText = ($diff < 60) ? $diff . ' detik' : floor($diff / 60) . ' m ' . ($diff % 60) . ' s';
    }

    // Try parsing metrics
    $parsed = !empty($job['response_data']) ? json_decode($job['response_data'], true) : null;
    $metrics = [];
    if (is_array($parsed)) {
      $item = isset($parsed[0]) && is_array($parsed[0]) ? $parsed[0] : $parsed;
      if (isset($item['playCount']) || isset($item['viewsCount']) || isset($item['videoViewCount'])) {
        $metrics['views'] = $item['playCount'] ?? ($item['viewsCount'] ?? $item['videoViewCount']);
      }
      if (isset($item['diggCount']) || isset($item['likesCount'])) {
        $metrics['likes'] = $item['diggCount'] ?? $item['likesCount'];
      }
      if (isset($item['commentCount']) || isset($item['commentsCount'])) {
        $metrics['comments'] = $item['commentCount'] ?? $item['commentsCount'];
      }
      if (isset($item['shareCount']) || isset($item['sharesCount'])) {
        $metrics['shares'] = $item['shareCount'] ?? $item['sharesCount'];
      }
    }
  ?>

  <div class="header-box">
    <div>
      <h1 class="header-title">LEMBAR AUDIT EKSEKUSI APIFY SCRAPING JOB</h1>
      <div style="font-size: 11px; color: #42526e; margin-top: 3px;">Kolab.id Influencer Marketing Platform &bull; Data Aggregator &amp; Bot Monitor</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($generated) ?></div>
      <div class="header-meta">Job ID: #<?= $job['id'] ?></div>
    </div>
  </div>

  <div class="status-banner">
    <div>
      <div style="font-size: 10px; text-transform: uppercase; color: #6b778c; font-weight: bold; margin-bottom: 2px;">STATUS EKSEKUSI</div>
      <span class="badge <?= $statusCls ?>" style="font-size: 14px; padding: 4px 12px;"><?= esc($job['status']) ?></span>
      <span style="margin-left: 10px; font-size: 12px; color: #42526e;">Percobaan ke-<strong><?= $job['attempt_number'] ?? 1 ?></strong></span>
    </div>
    <div style="text-align: right;">
      <div style="font-size: 10px; text-transform: uppercase; color: #6b778c; font-weight: bold; margin-bottom: 2px;">RUN IDENTIFIER</div>
      <span class="font-mono" style="font-size: 12px; font-weight: bold; color: #172b4d;"><?= esc($job['external_run_id'] ?? '-') ?></span>
    </div>
  </div>

  <div class="section-title">PARAMETER EKSEKUSI &amp; TARGET SCRAPING</div>
  <table>
    <tr>
      <th>Target URL</th>
      <td colspan="3"><span class="font-mono" style="word-break: break-all; color: #0052cc;"><?= esc($job['target_url']) ?></span></td>
    </tr>
    <tr>
      <th>Tipe Aktor (Actor Type)</th>
      <td>
        <strong><?= $job['actor_type'] === 'post' ? 'Video Post Scraping' : 'Profile Talent Scraping' ?></strong>
        <span class="font-mono" style="color: #6b778c;">(<?= esc($job['actor_type']) ?>)</span>
      </td>
      <th>Percobaan Ke-</th>
      <td><?= $job['attempt_number'] ?? 1 ?></td>
    </tr>
    <tr>
      <th>Waktu Dibuat (Queued)</th>
      <td><?= date('d F Y, H:i:s', strtotime($job['created_at'])) ?> WIB</td>
      <th>Durasi Eksekusi</th>
      <td><strong><?= $durationText ?></strong></td>
    </tr>
    <tr>
      <th>Waktu Mulai (Started)</th>
      <td><?= !empty($job['started_at']) ? date('d F Y, H:i:s', strtotime($job['started_at'])) . ' WIB' : '-' ?></td>
      <th>Waktu Selesai (Finished)</th>
      <td><?= !empty($job['finished_at']) ? date('d F Y, H:i:s', strtotime($job['finished_at'])) . ' WIB' : '-' ?></td>
    </tr>
  </table>

  <div class="section-title">RELASI KONTEN, TALENT &amp; KAMPANYE</div>
  <table>
    <tr>
      <th>Nama Talent (KOL)</th>
      <td><strong><?= esc($job['kol_name'] ?? ('KOL #' . ($job['kol_id'] ?? '-'))) ?></strong></td>
      <th>ID Talent</th>
      <td><?= !empty($job['kol_id']) ? '#' . $job['kol_id'] : '-' ?></td>
    </tr>
    <tr>
      <th>Kampanye Terkait</th>
      <td><?= esc($job['campaign_title'] ?? '-') ?></td>
      <th>Brand</th>
      <td><?= esc($job['campaign_brand'] ?? '-') ?></td>
    </tr>
    <tr>
      <th>Posting ID</th>
      <td><?= !empty($job['posting_id']) ? '#' . $job['posting_id'] : '-' ?></td>
      <th>Live URL Posting</th>
      <td>
        <?php if (!empty($job['postings_live_url'])): ?>
          <span class="font-mono" style="word-break: break-all;"><?= esc($job['postings_live_url']) ?></span>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <?php if (!empty($metrics)): ?>
    <div class="section-title">HASIL RINGKASAN METRIK KONTEN TERSCRAPE</div>
    <table>
      <tr>
        <th>Video Views / Plays</th>
        <td><strong style="color: #0052cc; font-size: 13px;"><?= number_format($metrics['views'] ?? 0) ?></strong></td>
        <th>Likes / Diggs</th>
        <td><strong style="color: #de350b; font-size: 13px;"><?= number_format($metrics['likes'] ?? 0) ?></strong></td>
      </tr>
      <tr>
        <th>Komentar (Comments)</th>
        <td><strong style="color: #00875a; font-size: 13px;"><?= number_format($metrics['comments'] ?? 0) ?></strong></td>
        <th>Shares / Reposts</th>
        <td><strong style="color: #ff8b00; font-size: 13px;"><?= number_format($metrics['shares'] ?? 0) ?></strong></td>
      </tr>
    </table>
  <?php endif; ?>

  <?php if ($job['status'] === 'FAILED' || !empty($job['error_message'])): ?>
    <div class="section-title" style="color: #de350b;">PESAN KESALAHAN (ERROR LOG)</div>
    <div style="background: #ffebe6; border: 1px solid #ffbdad; padding: 10px; border-radius: 4px; color: #de350b; font-family: monospace; font-size: 11px;">
      <?= esc($job['error_message'] ?? 'Tidak ada deskripsi error spesifik.') ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($job['response_data'])): ?>
    <div class="section-title">PAYLOAD RESPONS JSON DARI APIFY ACTOR</div>
    <pre><?= esc(json_encode(json_decode($job['response_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $job['response_data']) ?></pre>
  <?php endif; ?>

  <div style="margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 8px; font-size: 10px; color: #6b778c; text-align: center;">
    Dokumen ini digenerate secara otomatis oleh sistem Kolab.id sebagai bukti rekam jejak eksekusi data bot scraping.
  </div>
</body>
</html>
