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
      border-bottom: 2px solid #28a745;
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
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 11px;
    }
    .badge-increase { background: #e3fcef; color: #006644; }
    .badge-decrease { background: #ffebe6; color: #de350b; }
    .badge-same { background: #f4f5f7; color: #42526e; }
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
    $curr = esc($history['currency_code'] ?? 'IDR');
    $oldRate = (float)$history['old_rate'];
    $newRate = (float)$history['new_rate'];
    $diff = $newRate - $oldRate;

    if ($diff > 0) {
      $badgeCls = 'badge-increase';
      $badgeTxt = 'Kenaikan Tarif (+' . $curr . ' ' . number_format($diff, 0, ',', '.') . ')';
    } elseif ($diff < 0) {
      $badgeCls = 'badge-decrease';
      $badgeTxt = 'Penurunan Tarif (-' . $curr . ' ' . number_format(abs($diff), 0, ',', '.') . ')';
    } else {
      $badgeCls = 'badge-same';
      $badgeTxt = 'Tarif Tetap (0%)';
    }
  ?>

  <div class="header-box">
    <div>
      <h1 class="header-title">LEMBAR AUDIT PENYESUAIAN TARIF &amp; ANGGARAN TASK</h1>
      <div style="font-size: 11px; color: #42526e; margin-top: 3px;">Kolab.id Influencer Platform &bull; Budget, Fee &amp; Compensation Audit Trail</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($generated) ?></div>
      <div class="header-meta">ID Riwayat: #<?= $history['id'] ?></div>
    </div>
  </div>

  <div class="status-banner">
    <div>
      <div style="font-size: 10px; text-transform: uppercase; color: #6b778c; font-weight: bold; margin-bottom: 2px;">STATUS PENYESUAIAN</div>
      <span class="badge <?= $badgeCls ?>" style="font-size: 13px; padding: 4px 12px;"><?= $badgeTxt ?></span>
      <span style="margin-left: 10px; font-size: 12px; color: #42526e;">Task: <strong><?= esc($history['task_title'] ?: ('Task #' . ($history['task_number'] ?? $history['task_id']))) ?></strong></span>
    </div>
    <div style="text-align: right;">
      <div style="font-size: 10px; text-transform: uppercase; color: #6b778c; font-weight: bold; margin-bottom: 2px;">WAKTU OTORISASI</div>
      <span class="font-mono" style="font-size: 12px; font-weight: bold; color: #172b4d;"><?= date('d F Y, H:i:s', strtotime($history['created_at'])) ?> WIB</span>
    </div>
  </div>

  <div class="section-title">KOMPARASI TARIF &amp; ANGGARAN KONTEN</div>
  <table>
    <tr>
      <th>Tarif Semula (Old Rate)</th>
      <td class="font-mono" style="font-size: 13px; color: #6b778c; text-decoration: line-through;">
        <?= $curr ?> <?= number_format($oldRate, 0, ',', '.') ?>
      </td>
      <th>Tarif Baru (New Rate)</th>
      <td class="font-mono" style="font-size: 15px; font-weight: bold; color: #006644;">
        <?= $curr ?> <?= number_format($newRate, 0, ',', '.') ?>
      </td>
    </tr>
    <tr>
      <th>Selisih Anggaran (Delta)</th>
      <td colspan="3" class="font-mono" style="font-size: 13px; font-weight: bold; color: <?= $diff >= 0 ? '#006644' : '#de350b' ?>;">
        <?= ($diff > 0 ? '+' : '') . $curr . ' ' . number_format($diff, 0, ',', '.') ?>
      </td>
    </tr>
  </table>

  <div class="section-title">INFORMASI TASK &amp; KAMPANYE TERKAIT</div>
  <table>
    <tr>
      <th>ID Task Konten</th>
      <td>#<?= $history['task_id'] ?></td>
      <th>Judul Task</th>
      <td><strong><?= esc($history['task_title'] ?: ('Task #' . ($history['task_number'] ?? $history['task_id']))) ?></strong></td>
    </tr>
    <tr>
      <th>Format Konten</th>
      <td><?= esc($history['content_type'] ?? 'Short Form Video') ?></td>
      <th>Status Task Terkini</th>
      <td><?= esc($history['task_status'] ?? 'DRAFT') ?></td>
    </tr>
    <tr>
      <th>Nama Kampanye</th>
      <td colspan="3"><strong><?= esc($history['campaign_title'] ?? '-') ?></strong></td>
    </tr>
    <tr>
      <th>Talent (KOL)</th>
      <td><strong><?= esc($history['kol_name'] ?? '-') ?></strong></td>
      <th>Kontak KOL</th>
      <td><?= esc($history['kol_email'] ?? '-') ?> &bull; <?= esc($history['kol_phone'] ?? '-') ?></td>
    </tr>
  </table>

  <div class="section-title">OTORISASI &amp; ALASAN PENYESUAIAN</div>
  <table>
    <tr>
      <th>Diotorisasi Oleh</th>
      <td><strong><?= esc($history['authorized_name'] ?? 'System') ?></strong> (<?= esc($history['authorized_email'] ?? '-') ?>)</td>
    </tr>
    <tr>
      <th>Alasan / Justifikasi</th>
      <td style="font-family: monospace; font-size: 11px; white-space: pre-wrap; background: #fafbfc;"><?= esc($history['reason'] ?? 'Tidak ada alasan tertulis yang dicantumkan.') ?></td>
    </tr>
  </table>

  <div style="margin-top: 40px; border-top: 1px dashed #ccc; padding-top: 8px; font-size: 10px; color: #6b778c; text-align: center;">
    Dokumen ini digenerate secara otomatis oleh sistem Kolab.id sebagai bukti sah rekam jejak persetujuan tarif task kampanye.
  </div>
</body>
</html>
