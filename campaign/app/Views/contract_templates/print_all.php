<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            margin: 20px;
            font-size: 13px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn-print {
            background: #007bff;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak Dokumen / Simpan PDF</button>
    </div>

    <div class="header">
        <h2><?= esc($title) ?></h2>
        <p>Dicetak pada: <?= date('d F Y, H:i:s') ?> | Sistem Kolab.id</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th style="width: 60px; text-align: center;">ID</th>
                <th style="width: 250px;">Judul Template</th>
                <th style="width: 100px; text-align: center;">Status</th>
                <th>Cuplikan Format Naskah</th>
                <th style="width: 140px;">Tanggal Dibuat</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($templates)): ?>
                <?php $no = 1; foreach ($templates as $t): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td style="text-align: center; font-weight: bold;">#<?= $t['id'] ?></td>
                        <td><strong><?= esc($t['title']) ?></strong></td>
                        <td style="text-align: center;">
                            <?php if (!empty($t['is_active'])): ?>
                                <span class="badge badge-success">AKTIF</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">NON-AKTIF</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc(mb_substr(strip_tags($t['body_template'] ?: $t['content']), 0, 150)) ?>...</td>
                        <td><?= !empty($t['created_at']) ? date('d/m/Y H:i', strtotime($t['created_at'])) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Tidak ada template yang tersedia.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
