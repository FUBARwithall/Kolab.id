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
            font-size: 12px;
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
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
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
            .no-print { display: none; }
            body { margin: 0; }
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
                <th style="width: 30px; text-align: center;">No</th>
                <th style="width: 40px; text-align: center;">ID</th>
                <th>Judul Artikel</th>
                <th style="width: 140px;">Slug URL</th>
                <th style="width: 100px;">Kategori</th>
                <th style="width: 90px;">Penulis</th>
                <th style="width: 80px; text-align: center;">Status</th>
                <th style="width: 100px;">Tanggal Terbit</th>
                <th style="width: 50px; text-align: center;">Views</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($articles)): ?>
                <?php $no = 1; foreach ($articles as $a): ?>
                    <?php
                        $st = strtoupper((string) $a->status);
                        $badgeClass = 'badge-secondary';
                        if ($st === 'PUBLISHED') $badgeClass = 'badge-success';
                        elseif ($st === 'ARCHIVED') $badgeClass = 'badge-warning';
                    ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td style="text-align: center;"><?= (int) $a->id ?></td>
                        <td>
                            <strong><?= esc($a->title) ?></strong>
                            <?php if (!empty($a->is_featured)): ?>
                                <span style="color: #007bff; font-size: 10px;">[Featured]</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($a->slug) ?></td>
                        <td><?= esc($a->category ?: '-') ?></td>
                        <td><?= esc($a->author_name ?: 'Admin') ?></td>
                        <td style="text-align: center;"><span class="badge <?= $badgeClass ?>"><?= esc($st) ?></span></td>
                        <td><?= !empty($a->published_at) ? date('d/m/Y H:i', strtotime($a->published_at)) : '-' ?></td>
                        <td style="text-align: center;"><?= (int) $a->views ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada data artikel.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
