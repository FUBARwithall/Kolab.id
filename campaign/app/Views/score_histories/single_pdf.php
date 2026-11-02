<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #172b4d; margin: 30px; line-height: 1.5; }
    .header-box { border-bottom: 2px solid #ffab00; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
    .header-title { font-size: 20px; font-weight: bold; color: #172b4d; margin: 0; }
    .header-meta { font-size: 11px; color: #6b778c; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { border: 1px solid #dfe1e6; padding: 8px 12px; text-align: left; }
    th { background: #f4f5f7; width: 180px; font-weight: 600; color: #42526e; }
    .section-title { font-size: 14px; font-weight: bold; color: #172b4d; border-bottom: 1px solid #dfe1e6; padding-bottom: 5px; margin: 25px 0 10px 0; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; }
    .badge-excellent { background: #e3fcef; color: #006644; }
    .badge-good { background: #deebff; color: #0747a6; }
    .badge-fair { background: #fffae6; color: #ff8b00; }
    .badge-poor { background: #ffebe6; color: #de350b; }
    .score-banner { display: flex; align-items: center; justify-content: space-between; background: #fafbfc; border: 1px solid #dfe1e6; border-radius: 6px; padding: 18px 24px; margin-bottom: 20px; }
    .score-number { font-size: 38px; font-weight: bold; color: #0052cc; line-height: 1; }
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
    $scoreVal = (float)$history['score'];
    if ($scoreVal >= 85) {
      $grade = 'Grade A (Excellent)';
      $badgeCls = 'badge-excellent';
      $interpretation = 'Performa sangat memuaskan, konsisten memenuhi brief dan target views kampanye.';
    } elseif ($scoreVal >= 70) {
      $grade = 'Grade B (Good)';
      $badgeCls = 'badge-good';
      $interpretation = 'Performa baik dengan pemenuhan kriteria brief yang solid dan komunikasi lancar.';
    } elseif ($scoreVal >= 55) {
      $grade = 'Grade C (Fair)';
      $badgeCls = 'badge-fair';
      $interpretation = 'Performa standar, perlu perbaikan dalam ketepatan revisi atau interaksi engagement.';
    } else {
      $grade = 'Grade D (Needs Improvement)';
      $badgeCls = 'badge-poor';
      $interpretation = 'Performa di bawah standar ekspektasi. Evaluasi mendalam diperlukan.';
    }
    $scoreType = strtoupper((string)$history['score_type']);
  ?>

  <div class="header-box">
    <div>
      <h1 class="header-title">LEMBAR AUDIT EVALUASI SKOR PERFORMA KOL</h1>
      <div style="font-size: 12px; color: #42526e; margin-top: 4px;">Kolab.id Influencer Marketing Platform &bull; Scoring Engine</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($generated) ?></div>
      <div class="header-meta">ID Riwayat: #<?= $history['id'] ?></div>
    </div>
  </div>

  <div class="score-banner">
    <div>
      <div style="font-size: 11px; text-transform: uppercase; color: #6b778c; font-weight: bold; margin-bottom: 4px;">NILAI EVALUASI SKOR</div>
      <div class="score-number"><?= number_format($scoreVal, 2) ?> <span style="font-size: 18px; color: #6b778c; font-weight: normal;">/ 100</span></div>
      <div style="font-size: 12px; color: #42526e; margin-top: 6px;"><?= $interpretation ?></div>
    </div>
    <div style="text-align: right;">
      <span class="badge <?= $badgeCls ?>" style="font-size: 14px; padding: 6px 14px;"><?= $grade ?></span>
      <div style="font-size: 11px; color: #6b778c; margin-top: 6px;">Tipe: <strong><?= $scoreType ?></strong></div>
    </div>
  </div>

  <div class="section-title">PROFIL TALENT (KOL)</div>
  <table>
    <tr>
      <th>Nama Lengkap KOL</th>
      <td><strong><?= esc($history['kol_name'] ?? ('KOL #' . $history['kol_id'])) ?></strong></td>
      <th>ID Talent</th>
      <td>#<?= $history['kol_id'] ?></td>
    </tr>
    <tr>
      <th>Email</th>
      <td><?= esc($history['kol_email'] ?? '-') ?></td>
      <th>Kontak / WhatsApp</th>
      <td><?= esc($history['kol_whatsapp'] ?: ($history['kol_phone'] ?: '-')) ?></td>
    </tr>
    <tr>
      <th>Status Akun</th>
      <td><?= esc($history['kol_status'] ?? 'Active') ?></td>
      <th>Tanggal Kalkulasi</th>
      <td><?= date('d F Y, H:i:s', strtotime($history['calculated_at'])) ?> WIB</td>
    </tr>
  </table>

  <div class="section-title">KONTEKS KAMPANYE</div>
  <table>
    <tr>
      <th>Judul Kampanye</th>
      <td colspan="3"><strong><?= esc($history['campaign_title'] ?? 'Tidak Terikat Kampanye Tertentu (Evaluasi Lifetime Global)') ?></strong></td>
    </tr>
    <tr>
      <th>ID Kampanye</th>
      <td><?= $history['campaign_id'] ? '#' . $history['campaign_id'] : '-' ?></td>
      <th>ID Partisipasi Kampanye</th>
      <td><?= $history['campaign_kol_id'] ? '#' . $history['campaign_kol_id'] : '-' ?></td>
    </tr>
    <tr>
      <th>Dealing Status</th>
      <td><?= esc($history['dealing_status'] ?? 'N/A') ?></td>
      <th>Metode Evaluasi</th>
      <td><?= $scoreType === 'COLLABORATION' ? 'Agregasi Kampanye Berjalan' : 'Akumulasi Historis Menyeluruh' ?></td>
    </tr>
  </table>

  <div style="margin-top: 50px; border-top: 1px solid #dfe1e6; padding-top: 15px; font-size: 11px; color: #6b778c; display: flex; justify-content: space-between;">
    <div>Dokumen ini dicetak otomatis oleh sistem audit Kolab.id dan sah sebagai bukti rekapitulasi penilaian performa KOL.</div>
    <div>Halaman 1 / 1</div>
  </div>
</body>
</html>
