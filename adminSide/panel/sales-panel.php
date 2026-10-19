<?php
// sales-panel.php - staff panel: Items Sales. Which dishes sell the most, for a period you choose.
// One simple ranked list with a bar for each dish (no extra chart library needed).
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

$periods = [
    '7'   => 'Last 7 days',
    '30'  => 'Last 30 days',
    '90'  => 'Last 90 days',
    '365' => 'Last year',
];
$days = $_GET['days'] ?? '30';
if (!isset($periods[$days])) {
    $days = '30';
}
$days = (int) $days;

$category = $_GET['category'] ?? 'all';
$categories = ['all' => 'Everything', 'Main Dishes' => 'Mains', 'Side Snacks' => 'Sides', 'Drinks' => 'Drinks'];
if (!isset($categories[$category])) {
    $category = 'all';
}

// units sold and money made per dish (before tax)
$sql = "SELECT m.item_name, m.item_type, SUM(bi.quantity) AS units, SUM(bi.quantity * m.item_price) AS revenue
        FROM Bill_Items AS bi
        JOIN Menu AS m ON m.item_id = bi.item_id
        JOIN Bills AS b ON b.bill_id = bi.bill_id
        WHERE b.bill_time >= CURDATE() - INTERVAL ? DAY
          AND (? = 'all' OR m.item_category = ?)
        GROUP BY m.item_id, m.item_name, m.item_type
        ORDER BY units DESC";
$stmt = $link->prepare($sql);
$stmt->bind_param('iss', $days, $category, $category);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$mostUnits = 1;
$totalUnits = 0;
$totalRevenue = 0;
foreach ($rows as $row) {
    $mostUnits = max($mostUnits, (int) $row['units']);
    $totalUnits = $totalUnits + $row['units'];
    $totalRevenue = $totalRevenue + $row['revenue'];
}
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Items Sales</h2>
                <p class="pg-sub">Best sellers first. <?= number_format($totalUnits) ?> portions sold &middot; &#8377;<?= number_format($totalRevenue) ?> before tax.</p>
            </div>
        </div>

        <div class="pg-tools">
<?php foreach ($periods as $key => $label) { ?>
            <a class="pg-chip<?= $key == $days ? ' is-on' : '' ?>" href="?days=<?= $key ?>&amp;category=<?= urlencode($category) ?>"><?= $label ?></a>
<?php } ?>
            <span style="width: 1rem"></span>
<?php foreach ($categories as $key => $label) { ?>
            <a class="pg-chip<?= $key == $category ? ' is-on' : '' ?>" href="?days=<?= $days ?>&amp;category=<?= urlencode($key) ?>"><?= $label ?></a>
<?php } ?>
            <input type="search" class="form-control pg-search" placeholder="Find a dish…" data-filter="#sales-list">
        </div>

        <div class="table-scroll"><table class="table" id="sales-list">
            <thead><tr><th>#</th><th>Dish</th><th>Course</th><th style="width: 32%"></th><th class="pg-num">Sold</th><th class="pg-num">Money</th></tr></thead>
            <tbody>
<?php if (count($rows) == 0) { ?>
                <tr><td colspan="6" class="text-muted">Nothing sold in this period.</td></tr>
<?php } ?>
<?php foreach ($rows as $i => $row) { ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($row['item_type']) ?></td>
                    <td><div class="rp-bar"><span style="width: <?= round($row['units'] / $mostUnits * 100) ?>%"></span></div></td>
                    <td class="pg-num"><?= number_format((int) $row['units']) ?></td>
                    <td class="pg-num">&#8377;<?= number_format((float) $row['revenue']) ?></td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>
    </div>
</div>

<style>
    .rp-bar { height: 8px; background: rgba(255, 255, 255, 0.06); }
    .rp-bar span { display: block; height: 100%; background: linear-gradient(90deg, #b1462f, #e0784f); }
</style>

<script src="../js/staffPages.js"></script>
<?php include '../includes/dashFooter.php'; ?>
