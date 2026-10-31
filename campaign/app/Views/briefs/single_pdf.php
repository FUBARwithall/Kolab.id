<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #172b4d; margin: 30px; line-height: 1.5; }
    .header-box { border-bottom: 2px solid #0052cc; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
    .header-title { font-size: 22px; font-weight: bold; color: #0052cc; margin: 0; }
    .header-meta { font-size: 11px; color: #6b778c; }
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
    .meta-table th, .meta-table td { border: 1px solid #dfe1e6; padding: 8px 12px; text-align: left; }
    .meta-table th { background: #f4f5f7; width: 160px; font-weight: 600; color: #42526e; }
    .section-title { font-size: 15px; font-weight: bold; color: #172b4d; border-bottom: 1px solid #dfe1e6; padding-bottom: 5px; margin: 25px 0 10px 0; }
    .content-box { background: #f9fafc; border: 1px solid #dfe1e6; border-radius: 4px; padding: 15px; white-space: pre-line; margin-bottom: 20px; font-size: 13.5px; }
    .dos-donts { display: flex; gap: 15px; margin-bottom: 20px; }
    .dos-box { flex: 1; border: 1px solid #b3f5d1; background: #e3fcef; border-radius: 4px; padding: 12px; color: #006644; }
    .donts-box { flex: 1; border: 1px solid #ffbdad; background: #ffebe6; border-radius: 4px; padding: 12px; color: #bf2600; }
    .dos-title { font-weight: bold; margin-bottom: 6px; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .badge-approved { background: #e3fcef; color: #006644; border: 1px solid #b3f5d1; }
    .badge-revision { background: #fffae6; color: #ff8b00; border: 1px solid #ffe380; }
    .badge-pending { background: #deebff; color: #0747a6; border: 1px solid #b3d4ff; }
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
      <h1 class="header-title">BRIEF KONTEN KOL &amp; GUIDELINE</h1>
      <div style="font-size: 12px; color: #42526e; margin-top: 4px;">Kolab.id Influencer Marketing &amp; Campaign Management Platform</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($printedAt) ?></div>
      <div class="header-meta">Dokumen ID: BRF-<?= str_pad((string)$brief['id'], 5, '0', STR_PAD_LEFT) ?></div>
    </div>
  </div>

  <table class="meta-table">
    <tr>
      <th>ID Brief</th>
      <td><strong>#<?= $brief['id'] ?></strong></td>
      <th>Status Approval</th>
      <td>
        <?php 
          $st = strtoupper((string)($brief['status'] ?: $brief['approval_status']));
          $cls = match($st) {
            'APPROVED' => 'badge-approved',
            'REVISION', 'REVISION_NEEDED' => 'badge-revision',
            default => 'badge-pending'
          };
        ?>
        <span class="badge <?= $cls ?>"><?= esc($st) ?></span>
      </td>
    </tr>
    <tr>
      <th>Judul Kampanye</th>
      <td><strong><?= esc($brief['campaign_title']) ?></strong></td>
      <th>Nama Talent KOL</th>
      <td><strong><?= esc($brief['kol_name']) ?></strong></td>
    </tr>
    <tr>
      <th>Target Tugas / VT</th>
      <td>#<?= $brief['task_id'] ?> - <?= esc($brief['task_title'] ?: ('VT #' . $brief['task_number'])) ?></td>
      <th>Deadline Produksi</th>
      <td><?= !empty($brief['task_deadline']) ? date('d F Y', strtotime($brief['task_deadline'])) : '-' ?></td>
    </tr>
    <tr>
      <th>Dibuat Oleh</th>
      <td><?= esc($brief['submitter_name'] ?? 'Admin') ?> (<?= date('d/m/Y H:i', strtotime($brief['created_at'])) ?>)</td>
      <th>Revisi Berjalan</th>
      <td><?= (int)($brief['revision_count'] ?? 0) ?>x Revisi</td>
    </tr>
  </table>

  <div class="section-title">PETUNJUK KONTEN &amp; STORYLINE</div>
  <div class="content-box"><?= esc($brief['content'] ?: $brief['guidelines']) ?></div>

  <div class="section-title">DO'S &amp; DON'TS</div>
  <div class="dos-donts">
    <div class="dos-box">
      <div class="dos-title">&#10004; Do's (Hal yang Harus Dilakukan):</div>
      <div style="white-space: pre-line;"><?= esc($brief['dos'] ?: 'Tidak ada instruksi khusus.') ?></div>
    </div>
    <div class="donts-box">
      <div class="dos-title">&#10008; Don'ts (Hal yang Dilarang):</div>
      <div style="white-space: pre-line;"><?= esc($brief['donts'] ?: 'Tidak ada larangan khusus.') ?></div>
    </div>
  </div>

  <?php if (!empty($brief['reference_url'])): ?>
    <div class="section-title">MOODBOARD &amp; REFERENSI</div>
    <div style="padding: 10px; background: #f4f5f7; border: 1px solid #dfe1e6; border-radius: 4px; font-size: 12px;">
      <strong>Link Referensi:</strong> <a href="<?= esc($brief['reference_url']) ?>"><?= esc($brief['reference_url']) ?></a>
    </div>
  <?php endif; ?>

  <?php if (!empty($brief['history'])): ?>
    <div class="section-title">RIWAYAT PERSETUJUAN &amp; CATATAN REVIEWER</div>
    <table class="meta-table">
      <thead>
        <tr>
          <th style="width: 130px;">Tanggal</th>
          <th style="width: 140px;">Reviewer</th>
          <th style="width: 90px;">Keputusan</th>
          <th>Catatan / Instruksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($brief['history'] as $h): ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
            <td><strong><?= esc($h['reviewer_name'] ?? 'Reviewer') ?></strong></td>
            <td><span class="badge <?= $h['status'] === 'APPROVED' ? 'badge-approved' : 'badge-revision' ?>"><?= esc($h['status']) ?></span></td>
            <td><?= esc($h['full_note'] ?: ($h['notes'] ?: ($h['note'] ?: '-'))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div style="margin-top: 40px; text-align: center; font-size: 11px; color: #6b778c;">
    &mdash; Dokumen ini dihasilkan secara otomatis oleh sistem Kolab.id &mdash;
  </div>
</body>
</html>
