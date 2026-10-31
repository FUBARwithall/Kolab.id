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
    .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; }
    .badge-approved { background: #e3fcef; color: #006644; }
    .badge-revision { background: #fffae6; color: #ff8b00; }
    .badge-rejected { background: #ffebe6; color: #de350b; }
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
    $st = strtoupper(trim((string)$hist['status']));
    $badgeCls = match ($st) {
      'APPROVED' => 'badge-approved',
      'REVISION', 'REVISION_NEEDED' => 'badge-revision',
      'REJECTED' => 'badge-rejected',
      default => ''
    };
  ?>

  <div class="header-box">
    <div>
      <h1 class="header-title">LEMBAR KEPUTUSAN APPROVAL BRIEF</h1>
      <div style="font-size: 12px; color: #42526e; margin-top: 4px;">Kolab.id Influencer Marketing &amp; Content Production Management</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($printedAt) ?></div>
      <div class="header-meta">ID Riwayat: #<?= $hist['id'] ?></div>
    </div>
  </div>

  <div class="section-title">INFORMASI BRIEF &amp; KEPUTUSAN APPROVAL</div>
  <table>
    <tr>
      <th>ID Riwayat</th>
      <td><strong>#<?= $hist['id'] ?></strong></td>
      <th>Status Keputusan</th>
      <td><span class="badge <?= $badgeCls ?>"><?= esc($st) ?></span></td>
    </tr>
    <tr>
      <th>Target Brief</th>
      <td><strong><?= esc($hist['briefs_title'] ?? ('Brief #' . $hist['brief_id'])) ?></strong> (ID: #<?= $hist['brief_id'] ?>)</td>
      <th>Target Tugas (Task)</th>
      <td><?= esc($hist['task_title'] ?: ('VT #' . ($hist['task_number'] ?? '1'))) ?></td>
    </tr>
    <tr>
      <th>Kampanye</th>
      <td><?= esc($hist['campaign_title'] ?? '-') ?></td>
      <th>Talent KOL</th>
      <td><strong><?= esc($hist['kol_name'] ?? '-') ?></strong></td>
    </tr>
    <tr>
      <th>Reviewer / Leader</th>
      <td><strong><?= esc($hist['reviewer_name'] ?? 'Reviewer') ?></strong> <?= !empty($hist['reviewer_email']) ? '(' . esc($hist['reviewer_email']) . ')' : '' ?></td>
      <th>Waktu Review</th>
      <td><?= date('d F Y H:i:s', strtotime($hist['created_at'])) ?> WIB</td>
    </tr>
  </table>

  <div class="section-title">CATATAN REVIEWER / LEADER</div>
  <div style="padding: 12px 15px; background: #f4f5f7; border: 1px solid #dfe1e6; border-radius: 4px; color: #172b4d; font-size: 13px; white-space: pre-line; margin-bottom: 20px;">
    <?= !empty($hist['resolved_note']) ? esc($hist['resolved_note']) : '<em>Tidak ada catatan review yang dilampirkan.</em>' ?>
  </div>

  <div class="section-title">PEDOMAN KONTEN BRIEF TERKAIT</div>
  <div class="content-box"><?= esc($hist['brief_guidelines'] ?: ($hist['brief_content'] ?: 'Tidak ada teks isi konten tersimpan.')) ?></div>

  <table style="margin-top: 40px; border: none;">
    <tr style="border: none;">
      <td style="border: none; width: 60%;"></td>
      <td style="border: none; width: 40%; text-align: center;">
        <div style="font-size: 12px; color: #6b778c; margin-bottom: 50px;">Disetujui / Direview Oleh,</div>
        <div style="font-weight: bold; border-bottom: 1px solid #333; display: inline-block; padding-bottom: 3px; min-width: 180px;">
          <?= esc($hist['reviewer_name'] ?? 'Reviewer') ?>
        </div>
        <div style="font-size: 11px; color: #6b778c; margin-top: 4px;">Leader / Reviewer Kolab.id</div>
      </td>
    </tr>
  </table>

  <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #6b778c;">
    &mdash; Lembar riwayat approval ini dihasilkan secara otomatis oleh sistem Kolab.id &mdash;
  </div>
</body>
</html>
