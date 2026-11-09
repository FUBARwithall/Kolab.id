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
    .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; }
    .badge-approved { background: #e3fcef; color: #006644; }
    .badge-revision { background: #fffae6; color: #ff8b00; }
    .badge-submitted { background: #deebff; color: #0747a6; }
    .badge-default { background: #f4f5f7; color: #42526e; }
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
    $fromSt = !empty($history['from_status']) ? strtoupper(trim((string)$history['from_status'])) : null;
    $toSt   = strtoupper(trim((string)($history['to_status'] ?: ($history['status'] ?: 'DRAFT'))));

    $badgeCls = match ($toSt) {
      'BRIEF_APPROVED', 'DRAFT_APPROVED', 'APPROVED_DRAFT', 'COMPLETED', 'DONE', 'POSTED' => 'badge-approved',
      'BRIEF_REVISION', 'DRAFT_REVISION', 'REVISION', 'REVISION_DRAFT' => 'badge-revision',
      'BRIEF_SUBMITTED', 'DRAFT_SUBMITTED', 'PENDING_REVIEW', 'IN_REVIEW_DRAFT', 'WAITING_DRAFT' => 'badge-submitted',
      default => 'badge-default'
    };
  ?>

  <div class="header-box">
    <div>
      <h1 class="header-title">LEMBAR AUDIT STATUS TRANSISI TASK</h1>
      <div style="font-size: 12px; color: #42526e; margin-top: 4px;">Kolab.id Influencer Marketing Platform &bull; Workflow Engine</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($generated) ?></div>
      <div class="header-meta">ID Riwayat: #<?= $history['id'] ?></div>
    </div>
  </div>

  <div class="section-title">INFORMASI TRANSISI STATUS TUGAS</div>
  <table>
    <tr>
      <th>ID Riwayat</th>
      <td><strong>#<?= $history['id'] ?></strong></td>
      <th>Status Hasil Transisi</th>
      <td>
        <?php if ($fromSt && $fromSt !== $toSt): ?>
          <span style="color: #6b778c;"><?= esc($fromSt) ?></span> &rarr;
        <?php endif; ?>
        <span class="badge <?= $badgeCls ?>"><?= esc($toSt) ?></span>
      </td>
    </tr>
    <tr>
      <th>Target Tugas (Task)</th>
      <td><strong><?= esc($history['task_title'] ?: ('Task #' . $history['task_id'])) ?></strong></td>
      <th>Nomor Tugas (Task No)</th>
      <td>#<?= esc($history['task_number'] ?? '1') ?></td>
    </tr>
    <tr>
      <th>Kampanye</th>
      <td><?= esc($history['campaign_title'] ?? '-') ?></td>
      <th>Talent KOL</th>
      <td><strong><?= esc($history['kol_name'] ?? '-') ?></strong></td>
    </tr>
    <tr>
      <th>PIC / Diubah Oleh</th>
      <td><strong><?= esc($history['changed_by_name'] ?? 'System') ?></strong> <?= !empty($history['changed_by_email']) ? '(' . esc($history['changed_by_email']) . ')' : '' ?></td>
      <th>Waktu Perubahan</th>
      <td><?= date('d F Y, H:i:s', strtotime($history['created_at'])) ?> WIB</td>
    </tr>
    <tr>
      <th>Tipe Konten</th>
      <td><?= esc($history['content_type'] ?? 'Video Feed') ?></td>
      <th>Status Task Sekarang</th>
      <td><strong><?= esc($history['task_current_status'] ?? '-') ?></strong></td>
    </tr>
  </table>

  <div class="section-title">CATATAN PERUBAHAN STATUS (NOTES)</div>
  <div style="padding: 12px 15px; background: #f4f5f7; border: 1px solid #dfe1e6; border-radius: 4px; color: #172b4d; font-size: 13px; white-space: pre-line; margin-bottom: 20px;">
    <?= !empty($history['resolved_note']) ? esc($history['resolved_note']) : '<em>Tidak ada catatan transisi yang dilampirkan.</em>' ?>
  </div>

  <div style="margin-top: 40px; border-top: 1px solid #dfe1e6; padding-top: 15px; font-size: 11px; color: #6b778c; display: flex; justify-content: space-between;">
    <div>Dokumen ini diterbitkan otomatis oleh Sistem Kolab.id sebagai bukti autentik rekam jejak status tugas operasional.</div>
    <div>Halaman 1 dari 1</div>
  </div>
</body>
</html>
