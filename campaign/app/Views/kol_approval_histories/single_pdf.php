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
    .badge-submitted { background: #deebff; color: #0747a6; }
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
    $act = strtoupper(trim((string)$hist['resolved_action']));
    $badgeCls = match ($act) {
      'APPROVED' => 'badge-approved',
      'SUBMITTED', 'PENDING' => 'badge-submitted',
      'REJECTED' => 'badge-rejected',
      default => ''
    };
  ?>

  <div class="header-box">
    <div>
      <h1 class="header-title">LEMBAR VERIFIKASI &amp; APPROVAL KOL</h1>
      <div style="font-size: 12px; color: #42526e; margin-top: 4px;">Kolab.id Influencer Marketing &amp; Talent Management</div>
    </div>
    <div style="text-align: right;">
      <div class="header-meta">Dicetak: <?= esc($printedAt) ?></div>
      <div class="header-meta">ID Riwayat: #<?= $hist['id'] ?></div>
    </div>
  </div>

  <div class="section-title">INFORMASI TALENT KOL &amp; KEPUTUSAN VERIFIKASI</div>
  <table>
    <tr>
      <th>ID Riwayat</th>
      <td><strong>#<?= $hist['id'] ?></strong></td>
      <th>Aksi / Keputusan</th>
      <td><span class="badge <?= $badgeCls ?>"><?= esc($act) ?></span></td>
    </tr>
    <tr>
      <th>Nama Talent KOL</th>
      <td><strong><?= esc($hist['kol_name'] ?? ('KOL #' . $hist['kol_id'])) ?></strong> (ID: #<?= $hist['kol_id'] ?>)</td>
      <th>Kota Domisili</th>
      <td><?= esc($hist['kol_city'] ?: '-') ?></td>
    </tr>
    <tr>
      <th>WhatsApp</th>
      <td><?= esc($hist['kol_whatsapp'] ?: '-') ?></td>
      <th>Email</th>
      <td><?= esc($hist['kol_email'] ?: '-') ?></td>
    </tr>
    <tr>
      <th>NIK</th>
      <td><?= esc($hist['kol_nik'] ?: '-') ?></td>
      <th>Status Akun</th>
      <td><?= esc($hist['kol_current_status'] ?? 'PENDING') ?></td>
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

  <div class="section-title">INFORMASI REKENING &amp; PAJAK</div>
  <table>
    <tr>
      <th>Bank Penerima</th>
      <td><?= esc($hist['bank_name'] ?: '-') ?></td>
      <th>Nomor Rekening</th>
      <td><?= esc($hist['bank_account'] ?: '-') ?></td>
    </tr>
    <tr>
      <th>Atas Nama Rekening</th>
      <td><?= esc($hist['bank_holder'] ?: '-') ?></td>
      <th>NPWP</th>
      <td><?= esc($hist['npwp'] ?: '-') ?></td>
    </tr>
  </table>

  <table style="margin-top: 40px; border: none;">
    <tr style="border: none;">
      <td style="border: none; width: 60%;"></td>
      <td style="border: none; width: 40%; text-align: center;">
        <div style="font-size: 12px; color: #6b778c; margin-bottom: 50px;">Disetujui / Diverifikasi Oleh,</div>
        <div style="font-weight: bold; border-bottom: 1px solid #333; display: inline-block; padding-bottom: 3px; min-width: 180px;">
          <?= esc($hist['reviewer_name'] ?? 'Reviewer') ?>
        </div>
        <div style="font-size: 11px; color: #6b778c; margin-top: 4px;">Leader / Reviewer Kolab.id</div>
      </td>
    </tr>
  </table>

  <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #6b778c;">
    &mdash; Lembar riwayat approval KOL ini dihasilkan secara otomatis oleh sistem Kolab.id &mdash;
  </div>
</body>
</html>
