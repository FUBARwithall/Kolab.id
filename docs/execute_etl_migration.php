<?php
declare(strict_types=1);

/**
 * ETL Migration Script: CI3 (kol) -> CI4 (kolab_ci4)
 */

$pdo = new PDO('mysql:host=localhost', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== Memulai ETL Migration: kol -> kolab_ci4 ===\n";

// Disable foreign key checks during migration
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

// ID Maps
$campIdMap = [
    5  => 1,
    6  => 2,
    19 => 3,
    20 => 4,
    21 => 5,
    22 => 6,
    23 => 7,
    27 => 8,
];

$kolIdMap = [
    13 => 2,
    14 => 3,
    16 => 4,
    25 => 5,
    26 => 6,
    27 => 7,
    28 => 8,
    29 => 9,
    30 => 10,
];

$ckIdMap = [
    7  => 1,
    8  => 2,
    9  => 3,
    10 => 4,
    11 => 5,
    16 => 6,
    17 => 7,
    18 => 8,
    20 => 9,
    21 => 10,
    22 => 11,
    23 => 12,
    24 => 13,
    25 => 14,
    26 => 15,
    27 => 16,
    28 => 17,
    29 => 18,
    30 => 19,
    31 => 20,
    32 => 21,
];

$taskIdMap = [
    22 => 1,
    23 => 2,
    24 => 3,
    25 => 4,
    26 => 5,
    27 => 6,
    28 => 7,
    29 => 8,
    30 => 9,
    31 => 10,
    32 => 11,
    33 => 12,
    34 => 13,
    35 => 14,
    36 => 15,
    37 => 16,
    57 => 17,
    58 => 18,
    59 => 19,
    60 => 20,
    61 => 21,
    62 => 22,
    63 => 23,
    64 => 24,
    65 => 25,
    66 => 26,
    67 => 27,
    68 => 28,
    69 => 29,
];

$prodIdMap = [
    1  => 3,
    2  => 4,
    3  => 5,
    4  => 6,
    5  => 7,
    6  => 8,
    7  => 9,
    8  => 10,
    9  => 11,
    10 => 12,
    11 => 13,
    12 => 14,
    13 => 15,
    14 => 16,
    15 => 17,
];

// -------------------------------------------------------------
// 1. Sinkronisasi User & Staff
// -------------------------------------------------------------
echo "[1/15] Sinkronisasi User & Staff...\n";
$defaultHash = password_hash('admin123', PASSWORD_BCRYPT);

$usersToSync = [
    // Staff
    ['id' => 5, 'name' => 'Budi Pratama', 'email' => 'operational@gmail.com', 'role' => 3],
    ['id' => 6, 'name' => 'Siti Rahma', 'email' => 'siti.operational@example.com', 'role' => 3],
    ['id' => 7, 'name' => 'Rian Hidayat', 'email' => 'rian.operational@example.com', 'role' => 3],
    // KOL Talent
    ['id' => 8, 'name' => 'Jessica Jane', 'email' => 'inquiries.jessicajane99@gmail.com', 'role' => 4, 'kol_id' => 2],
    ['id' => 9, 'name' => 'Mamank Kuliner', 'email' => 'mamank.kuliner@example.com', 'role' => 4, 'kol_id' => 3],
    ['id' => 10, 'name' => 'Tanboy kun', 'email' => 'tanboykun@gmail.com', 'role' => 4, 'kol_id' => 4],
    ['id' => 11, 'name' => 'RJL 5', 'email' => 'partnership@rjl5.net', 'role' => 4, 'kol_id' => 5],
    ['id' => 12, 'name' => 'Devina Hermawan', 'email' => 'contact@devinahermawan.com', 'role' => 4, 'kol_id' => 6],
    ['id' => 13, 'name' => 'Deddy Corbuzier', 'email' => 'hello@closethedoor.com', 'role' => 4, 'kol_id' => 7],
    ['id' => 14, 'name' => 'Windah Basudara', 'email' => 'windahbasudara.business@gmail.com', 'role' => 4, 'kol_id' => 8],
    ['id' => 15, 'name' => 'Miawaug', 'email' => 'miawaug@gmail.com', 'role' => 4, 'kol_id' => 9],
    ['id' => 16, 'name' => 'Luthfi Halimawan', 'email' => 'luthfihalimawanteam@gmail.com', 'role' => 4, 'kol_id' => 10],
];

foreach ($usersToSync as $u) {
    $exists = $pdo->query("SELECT id FROM `kolab_ci4`.`users` WHERE id = {$u['id']} OR email = '{$u['email']}'")->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO `kolab_ci4`.`users` (id, name, email, password_hash, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$u['id'], $u['name'], $u['email'], $defaultHash]);
        $uid = $u['id'];
    } else {
        $uid = (int)$exists;
    }

    // Role
    $roleExists = $pdo->query("SELECT user_id FROM `kolab_ci4`.`user_roles` WHERE user_id = $uid AND role_id = {$u['role']}")->fetchColumn();
    if (!$roleExists) {
        $pdo->exec("INSERT INTO `kolab_ci4`.`user_roles` (user_id, role_id) VALUES ($uid, {$u['role']})");
    }

    // Update kols.user_id if talent
    if (!empty($u['kol_id'])) {
        $pdo->exec("UPDATE `kolab_ci4`.`kols` SET user_id = $uid WHERE id = {$u['kol_id']}");
    }
}

// -------------------------------------------------------------
// 2. Pemulihan Status tasks di kolab_ci4
// -------------------------------------------------------------
echo "[2/15] Pemulihan status tasks (12-state)...\n";
$c3Tasks = $pdo->query("SELECT id, status, estimated_posting_date, budget, title FROM `kol`.`tasks`")->fetchAll();
foreach ($c3Tasks as $t3) {
    $c3id = (int)$t3['id'];
    if (!isset($taskIdMap[$c3id])) continue;
    $c4id = $taskIdMap[$c3id];

    $mappedStatus = match ($t3['status']) {
        'DONE'           => 'COMPLETED',
        'WAITING_DRAFT'  => 'BRIEF_APPROVED',
        'PENDING_REVIEW' => 'DRAFT_SUBMITTED',
        'REVISION_DRAFT' => 'DRAFT_REVISION',
        'POSTED'         => 'POSTED',
        'DRAFT'          => 'DRAFT',
        default          => 'DRAFT',
    };

    $stmt = $pdo->prepare("UPDATE `kolab_ci4`.`tasks` SET status = ?, deadline = ?, rate_amount = ?, title = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([
        $mappedStatus,
        $t3['estimated_posting_date'] ?: null,
        $t3['budget'] ?: 0,
        $t3['title'] ?: "VT #$c4id",
        $c4id
    ]);
}

// -------------------------------------------------------------
// 3. campaign_products
// -------------------------------------------------------------
echo "[3/15] Migrasi campaign_products...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`campaign_products`");
$c3Cp = $pdo->query("SELECT campaign_id, product_id FROM `kol`.`campaign_products`")->fetchAll();
$stmtCp = $pdo->prepare("INSERT IGNORE INTO `kolab_ci4`.`campaign_products` (campaign_id, product_id) VALUES (?, ?)");
foreach ($c3Cp as $cp) {
    $cId = $campIdMap[(int)$cp['campaign_id']] ?? null;
    $pId = $prodIdMap[(int)$cp['product_id']] ?? null;
    if ($cId && $pId) {
        $stmtCp->execute([$cId, $pId]);
    }
}

// -------------------------------------------------------------
// 4. campaign_allowed_tiers, categories, platforms
// -------------------------------------------------------------
echo "[4/15] Migrasi kriteria target kampanye...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`campaign_allowed_tiers`");
$stmtCat = $pdo->prepare("INSERT IGNORE INTO `kolab_ci4`.`campaign_allowed_tiers` (campaign_id, tier_id) VALUES (?, ?)");
foreach ($pdo->query("SELECT campaign_id, tier_id FROM `kol`.`campaign_allowed_tiers`")->fetchAll() as $r) {
    $cId = $campIdMap[(int)$r['campaign_id']] ?? null;
    if ($cId) $stmtCat->execute([$cId, $r['tier_id']]);
}

$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`campaign_allowed_categories`");
$stmtCac = $pdo->prepare("INSERT IGNORE INTO `kolab_ci4`.`campaign_allowed_categories` (campaign_id, category_id) VALUES (?, ?)");
foreach ($pdo->query("SELECT campaign_id, category_id FROM `kol`.`campaign_allowed_categories`")->fetchAll() as $r) {
    $cId = $campIdMap[(int)$r['campaign_id']] ?? null;
    if ($cId) $stmtCac->execute([$cId, $r['category_id']]);
}

$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`campaign_required_platforms`");
$stmtCrp = $pdo->prepare("INSERT IGNORE INTO `kolab_ci4`.`campaign_required_platforms` (campaign_id, platform_id) VALUES (?, ?)");
foreach ($pdo->query("SELECT campaign_id, platform_id FROM `kol`.`campaign_required_platforms`")->fetchAll() as $r) {
    $cId = $campIdMap[(int)$r['campaign_id']] ?? null;
    if ($cId) $stmtCrp->execute([$cId, $r['platform_id']]);
}

// -------------------------------------------------------------
// 5. briefs & brief_approval_histories
// -------------------------------------------------------------
echo "[5/15] Migrasi briefs & approval histories...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`brief_approval_histories`");
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`briefs`");

$c3Briefs = $pdo->query("SELECT * FROM `kol`.`briefs` ORDER BY id ASC")->fetchAll();
$briefIdMap = [];
$stmtBrief = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`briefs` (id, task_id, title, guidelines, dos, donts, reference_url, approval_status, revision_notes, submitted_by, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($c3Briefs as $b) {
    $c4TaskId = $taskIdMap[(int)$b['task_id']] ?? null;
    if (!$c4TaskId) continue;

    $status = match ($b['status']) {
        'APPROVED' => 'APPROVED',
        'REVISION' => 'REVISION_NEEDED',
        'REJECTED' => 'REJECTED',
        default    => 'PENDING',
    };

    $newBriefId = (int)$b['id'];
    $briefIdMap[(int)$b['id']] = $newBriefId;

    $stmtBrief->execute([
        $newBriefId,
        $c4TaskId,
        $b['title'] ?: 'Brief Konten VT',
        $b['content'] ?: 'Ikuti panduan brand & guidelines yang tertera.',
        null,
        null,
        $b['file_path'] ?: null,
        $status,
        null,
        1, // submitted_by Admin
        $b['created_at'] ?: date('Y-m-d H:i:s'),
        $b['updated_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

$c3Bah = $pdo->query("SELECT * FROM `kol`.`brief_approval_histories` ORDER BY id ASC")->fetchAll();
$stmtBah = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`brief_approval_histories` (id, brief_id, reviewed_by, status, notes, created_at)
    VALUES (?, ?, ?, ?, ?, ?)
");
foreach ($c3Bah as $bah) {
    $c4BriefId = $briefIdMap[(int)$bah['brief_id']] ?? null;
    if (!$c4BriefId) continue;
    $status = match ($bah['status']) {
        'APPROVED' => 'APPROVED',
        'REVISION' => 'REVISION_NEEDED',
        'REJECTED' => 'REJECTED',
        default    => 'PENDING',
    };
    $stmtBah->execute([
        $bah['id'],
        $c4BriefId,
        1, // reviewed_by admin
        $status,
        $bah['note'] ?: ($status === 'APPROVED' ? 'Approved' : 'Revision needed'),
        $bah['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// -------------------------------------------------------------
// 6. drafts & draft_review_histories
// -------------------------------------------------------------
echo "[6/15] Migrasi drafts & review histories...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`draft_review_histories`");
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`drafts`");

$c3Drafts = $pdo->query("SELECT * FROM `kol`.`drafts` ORDER BY id ASC")->fetchAll();
$draftIdMap = [];
$stmtDraft = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`drafts` (id, task_id, version, video_url, status, review_notes, timestamp_notes, submitted_at, reviewed_at, reviewed_by, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($c3Drafts as $d) {
    $c4TaskId = $taskIdMap[(int)$d['task_id']] ?? null;
    if (!$c4TaskId) continue;

    $status = match ($d['status']) {
        'APPROVED' => 'APPROVED',
        'REVISION' => 'REVISION_NEEDED',
        default    => 'PENDING',
    };

    $newDraftId = (int)$d['id'];
    $draftIdMap[(int)$d['id']] = $newDraftId;

    $stmtDraft->execute([
        $newDraftId,
        $c4TaskId,
        (int)($d['version'] ?? 1),
        $d['draft_url'] ?: 'https://youtu.be/sample',
        $status,
        $d['notes'] ?: null,
        null,
        $d['created_at'] ?: date('Y-m-d H:i:s'),
        ($status === 'APPROVED') ? ($d['updated_at'] ?: $d['created_at']) : null,
        ($status === 'APPROVED') ? 1 : null,
        $d['created_at'] ?: date('Y-m-d H:i:s'),
        $d['updated_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

$c3Drh = $pdo->query("SELECT * FROM `kol`.`draft_review_histories` ORDER BY id ASC")->fetchAll();
$stmtDrh = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`draft_review_histories` (id, draft_id, reviewed_by, status, timestamp_notes, notes, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
foreach ($c3Drh as $drh) {
    $c4DraftId = $draftIdMap[(int)$drh['draft_id']] ?? null;
    if (!$c4DraftId) continue;
    $status = ($drh['action'] === 'APPROVED' || $drh['note'] === 'Approved') ? 'APPROVED' : 'REVISION_NEEDED';
    $stmtDrh->execute([
        $drh['id'],
        $c4DraftId,
        1,
        $status,
        null,
        $drh['note'] ?: $status,
        $drh['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// -------------------------------------------------------------
// 7. kol_shipments & kol_shipment_items
// -------------------------------------------------------------
echo "[7/15] Migrasi kol_shipments & shipment items...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`kol_shipment_items`");
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`kol_shipments`");

$c3Ship = $pdo->query("SELECT * FROM `kol`.`kol_shipments` ORDER BY id ASC")->fetchAll();
$shipIdMap = [];
$stmtShip = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`kol_shipments` (id, campaign_kol_id, courier_name, tracking_number, shipping_status, notes, shipped_at, delivered_at, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($c3Ship as $s) {
    $c4CkId = $ckIdMap[(int)$s['campaign_kol_id']] ?? null;
    if (!$c4CkId) continue;

    $status = match ($s['status']) {
        'DELIVERED' => 'DELIVERED',
        'SHIPPED'   => 'SHIPPED',
        'RETURNED', 'RETURN_PENDING' => 'RETURNED',
        default     => 'PENDING',
    };

    $newShipId = (int)$s['id'];
    $shipIdMap[(int)$s['id']] = $newShipId;

    $stmtShip->execute([
        $newShipId,
        $c4CkId,
        $s['courier'] ?: 'JNE',
        $s['tracking_no'] ?: 'RES-SAMPLE-' . $newShipId,
        $status,
        $s['notes'] ?: null,
        $s['shipped_at'] ?: ($status !== 'PENDING' ? $s['created_at'] : null),
        $s['delivered_at'] ?: ($status === 'DELIVERED' ? $s['created_at'] : null),
        $s['created_at'] ?: date('Y-m-d H:i:s'),
        $s['updated_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

$c3Si = $pdo->query("SELECT * FROM `kol`.`kol_shipment_items` ORDER BY id ASC")->fetchAll();
$stmtSi = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`kol_shipment_items` (id, shipment_id, product_id, variant_id, qty, created_at)
    VALUES (?, ?, ?, ?, ?, ?)
");
foreach ($c3Si as $si) {
    $c4ShipId = $shipIdMap[(int)$si['shipment_id']] ?? null;
    $c4ProdId = $prodIdMap[(int)$si['product_id']] ?? null;
    if (!$c4ShipId || !$c4ProdId) continue;

    $stmtSi->execute([
        $si['id'],
        $c4ShipId,
        $c4ProdId,
        null,
        (int)($si['qty'] ?? 1),
        $si['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// -------------------------------------------------------------
// 8. postings & posting_metrics
// -------------------------------------------------------------
echo "[8/15] Migrasi postings & posting_metrics...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`posting_metrics`");
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`postings`");

$c3Post = $pdo->query("SELECT * FROM `kol`.`postings` ORDER BY id ASC")->fetchAll();
$postIdMap = [];
$stmtPost = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`postings` (id, task_id, platform_id, live_url, post_identifier, posted_at, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($c3Post as $p) {
    $c4TaskId = $taskIdMap[(int)$p['task_id']] ?? null;
    if (!$c4TaskId) continue;

    $newPostId = (int)$p['id'];
    $postIdMap[(int)$p['id']] = $newPostId;

    $stmtPost->execute([
        $newPostId,
        $c4TaskId,
        (int)($p['platform_id'] ?? 1),
        $p['live_url'] ?: 'https://tiktok.com/@creator/video/sample',
        $p['post_identifier'] ?? null,
        $p['posted_at'] ?: ($p['actual_posted_at'] ?: ($p['created_at'] ?: date('Y-m-d H:i:s'))),
        $p['created_at'] ?: date('Y-m-d H:i:s'),
        $p['updated_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

$c3Pm = $pdo->query("SELECT * FROM `kol`.`posting_metrics` ORDER BY id ASC")->fetchAll();
$stmtPm = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`posting_metrics` (id, posting_id, views_count, likes_count, comments_count, shares_count, saves_count, actual_cpv, last_scraped_at, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Keep unique per posting_id in posting_metrics (since posting_id is UNIQUE in kolab_ci4)
$migratedPostings = [];
foreach ($c3Pm as $pm) {
    $c4PostId = $postIdMap[(int)$pm['posting_id']] ?? null;
    if (!$c4PostId) continue;

    if (isset($migratedPostings[$c4PostId])) {
        // Update to latest metric
        $stmtUp = $pdo->prepare("UPDATE `kolab_ci4`.`posting_metrics` SET views_count=?, likes_count=?, comments_count=?, shares_count=?, saves_count=?, actual_cpv=?, last_scraped_at=? WHERE posting_id=?");
        $stmtUp->execute([
            (int)($pm['views'] ?? 0),
            (int)($pm['likes'] ?? 0),
            (int)($pm['comments'] ?? 0),
            (int)($pm['shares'] ?? 0),
            (int)($pm['saves'] ?? 0),
            (float)($pm['cpv'] ?? 0),
            $pm['snapshot_at'] ?: date('Y-m-d H:i:s'),
            $c4PostId,
        ]);
        continue;
    }

    $stmtPm->execute([
        $pm['id'],
        $c4PostId,
        (int)($pm['views'] ?? 0),
        (int)($pm['likes'] ?? 0),
        (int)($pm['comments'] ?? 0),
        (int)($pm['shares'] ?? 0),
        (int)($pm['saves'] ?? 0),
        (float)($pm['cpv'] ?? 0),
        $pm['snapshot_at'] ?: date('Y-m-d H:i:s'),
        $pm['created_at'] ?: date('Y-m-d H:i:s'),
        $pm['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
    $migratedPostings[$c4PostId] = true;
}

// -------------------------------------------------------------
// 9. stock_history
// -------------------------------------------------------------
echo "[9/15] Migrasi stock_history...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`stock_history`");
$c3Sh = $pdo->query("SELECT * FROM `kol`.`stock_history` ORDER BY id ASC")->fetchAll();
$stmtSh = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`stock_history` (id, product_id, variant_id, type, qty, balance_after, reference_type, reference_id, notes, created_by, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
foreach ($c3Sh as $sh) {
    $c4ProdId = $prodIdMap[(int)$sh['product_id']] ?? null;
    if (!$c4ProdId) continue;

    $type = strtoupper((string)($sh['change_type'] ?? 'IN'));
    if (!in_array($type, ['IN', 'OUT', 'ADJUSTMENT'], true)) {
        $type = 'IN';
    }

    $stmtSh->execute([
        $sh['id'],
        $c4ProdId,
        null,
        $type,
        abs((int)($sh['qty_change'] ?? 0)),
        (int)($sh['stock_after'] ?? 0),
        $sh['shipment_id'] ? 'SHIPMENT' : 'MANUAL_ADJUSTMENT',
        $sh['shipment_id'] ? ($shipIdMap[(int)$sh['shipment_id']] ?? null) : null,
        $sh['notes'] ?: 'Riwayat stok legacy',
        1,
        $sh['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// -------------------------------------------------------------
// 10. campaign_kol_contracts
// -------------------------------------------------------------
echo "[10/15] Migrasi campaign_kol_contracts...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`campaign_kol_contracts`");
$c3Cont = $pdo->query("SELECT * FROM `kol`.`campaign_kol_contracts` ORDER BY id ASC")->fetchAll();
$stmtCont = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`campaign_kol_contracts` (id, campaign_kol_id, template_id, contract_number, clauses_rendered, signed_at, signed_ip, signature_image, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
foreach ($c3Cont as $ct) {
    $c4CkId = $ckIdMap[(int)$ct['campaign_kol_id']] ?? null;
    if (!$c4CkId) continue;

    $stmtCont->execute([
        $ct['id'],
        $c4CkId,
        1,
        $ct['contract_no'] ?: ('KTR/2026/' . str_pad((string)$ct['id'], 5, '0', STR_PAD_LEFT)),
        $ct['terms_and_conditions'] ?: $ct['contract_html'] ?: 'Klausul perjanjian kerjasama...',
        $ct['status'] === 'SIGNED' ? ($ct['updated_at'] ?: $ct['created_at']) : null,
        '127.0.0.1',
        $ct['kol_signature_image'] ?: null,
        $ct['created_at'] ?: date('Y-m-d H:i:s'),
        $ct['updated_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// -------------------------------------------------------------
// 11. invoices & payments
// -------------------------------------------------------------
echo "[11/15] Migrasi invoices & payments...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`payments`");
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`invoices`");

$c3Inv = $pdo->query("SELECT * FROM `kol`.`invoices` ORDER BY id ASC")->fetchAll();
$invIdMap = [];
$stmtInv = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`invoices` (id, campaign_kol_id, invoice_number, subtotal, tax_type, tax_rate, tax_amount, grand_total, invoice_file, bupot_file, status, verified_by, verified_at, paid_at, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($c3Inv as $iv) {
    $c4CkId = $ckIdMap[(int)$iv['campaign_kol_id']] ?? null;
    if (!$c4CkId) continue;

    $status = match ($iv['status']) {
        'PAID'      => 'PAID',
        'APPROVED'  => 'VERIFIED',
        'SUBMITTED' => 'SUBMITTED',
        'REJECTED'  => 'REJECTED',
        default     => 'DRAFT',
    };

    $newInvId = (int)$iv['id'];
    $invIdMap[(int)$iv['id']] = $newInvId;

    $taxRate = ((float)($iv['tax_rate_percent'] ?? 0)) / 100;
    $taxType = $iv['tax_type'] ?: 'PPh21';
    if (!in_array($taxType, ['PPh21', 'PPh23', 'NONE'], true)) {
        $taxType = 'PPh21';
    }

    $stmtInv->execute([
        $newInvId,
        $c4CkId,
        $iv['invoice_no'] ?: ('INV/2026/' . str_pad((string)$newInvId, 5, '0', STR_PAD_LEFT)),
        (float)($iv['subtotal'] ?? 0),
        $taxType,
        $taxRate,
        (float)($iv['tax_amount'] ?? 0),
        (float)($iv['grand_total'] ?? 0),
        $iv['invoice_file'] ?: null,
        $iv['bupot_file'] ?: null,
        $status,
        1,
        ($status === 'VERIFIED' || $status === 'PAID') ? ($iv['approved_at'] ?: $iv['created_at']) : null,
        ($status === 'PAID') ? ($iv['updated_at'] ?: $iv['created_at']) : null,
        $iv['created_at'] ?: date('Y-m-d H:i:s'),
        $iv['updated_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

$c3Pay = $pdo->query("SELECT * FROM `kol`.`payments` ORDER BY id ASC")->fetchAll();
$stmtPay = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`payments` (id, invoice_id, amount_paid, payment_method, bank_reference, transfer_receipt_file, paid_by, paid_at, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($c3Pay as $py) {
    $c4InvId = $invIdMap[(int)$py['invoice_id']] ?? null;
    if (!$c4InvId) continue;

    $stmtPay->execute([
        $py['id'],
        $c4InvId,
        (float)($py['amount'] ?? 0),
        $py['payment_method'] ?: 'BANK_TRANSFER',
        $py['notes'] ?: ('TRX-' . strtoupper(substr(md5((string)$py['id']), 0, 8))),
        $py['payment_proof_file'] ?: null,
        1,
        $py['payment_date'] ?: ($py['created_at'] ?: date('Y-m-d H:i:s')),
        $py['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// -------------------------------------------------------------
// 12. whatsapp_templates & whatsapp_messages
// -------------------------------------------------------------
echo "[12/15] Migrasi WhatsApp templates & messages...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`whatsapp_messages`");
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`whatsapp_templates`");

$c3Wt = $pdo->query("SELECT * FROM `kol`.`whatsapp_templates` ORDER BY id ASC")->fetchAll();
$stmtWt = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`whatsapp_templates` (id, template_code, title, body_text, variables, is_active, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
foreach ($c3Wt as $wt) {
    $vars = !empty($wt['available_tags']) 
        ? json_encode(array_values(array_filter(array_map('trim', explode(',', $wt['available_tags'])))))
        : json_encode([]);

    $stmtWt->execute([
        $wt['id'],
        $wt['template_code'] ?: ('TPL_' . $wt['id']),
        $wt['title'] ?: 'WhatsApp Template',
        $wt['content'] ?: 'Pesan notifikasi...',
        $vars,
        (int)($wt['is_active'] ?? 1),
        $wt['created_at'] ?: date('Y-m-d H:i:s'),
        $wt['updated_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

$c3Wm = $pdo->query("SELECT * FROM `kol`.`whatsapp_messages` ORDER BY id ASC")->fetchAll();
$stmtWm = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`whatsapp_messages` (id, recipient_phone, message_text, status, error_message, sent_at, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
foreach ($c3Wm as $wm) {
    $phone = preg_replace('/[^0-9]/', '', (string)($wm['receiver_jid'] ?? '08123456789'));
    $msgStatus = match (strtoupper((string)($wm['status'] ?? ''))) {
        'FAILED', 'ERROR' => 'FAILED',
        'PENDING', 'QUEUE' => 'PENDING',
        default => 'SENT',
    };
    $stmtWm->execute([
        $wm['id'],
        $phone ?: '628123456789',
        $wm['message_body'] ?: 'Halo talent KOL!',
        $msgStatus,
        null,
        $wm['sent_at'] ?: ($wm['created_at'] ?: date('Y-m-d H:i:s')),
        $wm['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// -------------------------------------------------------------
// 13. task_sentiments
// -------------------------------------------------------------
echo "[13/15] Migrasi task_sentiments...\n";
$pdo->exec("TRUNCATE TABLE `kolab_ci4`.`task_sentiments`");
$c3Ts = $pdo->query("SELECT * FROM `kol`.`task_sentiments` ORDER BY id ASC")->fetchAll();
$stmtTs = $pdo->prepare("
    INSERT INTO `kolab_ci4`.`task_sentiments` (id, task_id, positive_count, neutral_count, negative_count, sentiment_score, analyzed_at)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
foreach ($c3Ts as $ts) {
    $c4TaskId = $taskIdMap[(int)$ts['task_id']] ?? null;
    if (!$c4TaskId) continue;

    $pos = (int)($ts['positive_count'] ?? 0);
    $neu = (int)($ts['neutral_count'] ?? 0);
    $neg = (int)($ts['negative_count'] ?? 0);
    $total = $pos + $neu + $neg;
    $score = ($total > 0) ? round(($pos / $total) * 100, 2) : 75.00;

    $stmtTs->execute([
        $ts['id'],
        $c4TaskId,
        $pos,
        $neu,
        $neg,
        $score,
        $ts['created_at'] ?: date('Y-m-d H:i:s'),
    ]);
}

// Re-enable foreign key checks
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "=== ETL Migration Selesai dengan Sukses! ===\n";
