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
    .content-box { background: #f9fafc; border: 1px solid #dfe1e6; border-radius: 4px; padding: 15px; white-space: pre-line; margin-bottom: 20px; font-size: 13px; }
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
      <h1 class="header-title">SNAPSHOT HISTORI KONTEN BRIEF</h1>
      <div style="font-size: 12px; color: #42526e; margin-top: 4px;">Kolab.id Influencer Marketing &amp; Content Production Management</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($printedAt) ?></div>
      <div class="header-meta">Versi Snapshot: v<?= $hist['version'] ?> &bull; ID: #<?= $hist['id'] ?></div>
    </div>
  </div>

  <div class="section-title">INFORMASI BRIEF &amp; TARGET</div>
  <table>
    <tr>
      <th>ID Snapshot</th>
      <td><strong>#<?= $hist['id'] ?></strong></td>
      <th>Nomor Versi</th>
      <td><strong>Versi <?= $hist['version'] ?></strong></td>
    </tr>
    <tr>
      <th>Target Brief</th>
      <td><strong><?= esc($hist['briefs_title'] ?? ('Brief #' . $hist['brief_id'])) ?></strong></td>
      <th>Target Tugas (Task)</th>
      <td><?= esc($hist['task_title'] ?: ('VT #' . ($hist['task_number'] ?? '1'))) ?></td>
    </tr>
    <tr>
      <th>Judul Snapshot</th>
      <td><?= esc($hist['title'] ?: '-') ?></td>
      <th>Kampanye</th>
      <td><?= esc($hist['campaign_title'] ?? '-') ?></td>
    </tr>
    <tr>
      <th>Talent KOL</th>
      <td><strong><?= esc($hist['kol_name'] ?? '-') ?></strong></td>
      <th>Editor / Pembuat</th>
      <td><?= esc($hist['author_name'] ?? 'Editor') ?></td>
    </tr>
    <tr>
      <th>Waktu Snapshot</th>
      <td colspan="3"><?= date('d F Y H:i:s', strtotime($hist['snapshot_at'] ?: $hist['created_at'])) ?> WIB</td>
    </tr>
  </table>

  <?php if (!empty($hist['content_diff'])): ?>
    <div class="section-title">CATATAN ALASAN REVISI</div>
    <div style="padding: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; color: #92400e; font-size: 13px; margin-bottom: 20px;">
      <?= esc($hist['content_diff']) ?>
    </div>
  <?php endif; ?>

  <div class="section-title">ISI KONTEN &amp; STORYLINE PADA VERSI INI</div>
  <div class="content-box"><?= esc($hist['content'] ?: 'Tidak ada teks isi konten.') ?></div>

  <div style="margin-top: 40px; text-align: center; font-size: 11px; color: #6b778c;">
    &mdash; Dokumen snapshot ini dihasilkan secara otomatis oleh sistem Kolab.id &mdash;
  </div>
</body>
</html>
