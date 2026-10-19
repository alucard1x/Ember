<?php
// statistics-panel.php - staff panel: Revenue Statistics. Money made today, this week, this month
// and in total, a bar for each of the last 14 days, and how guests paid (cash or card).
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

// money (before tax) of the bills since a date
function revenueSince($link, $where) {
    $sql = "SELECT COALESCE(SUM(bi.quantity * m.item_price), 0) AS money, COUNT(DISTINCT b.bill_id) AS bills
            FROM Bills AS b
            JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
            JOIN Menu AS m ON m.item_id = bi.item_id
            WHERE b.payment_time IS NOT NULL AND $where";
    return mysqli_fetch_assoc(mysqli_query($link, $sql));
}

$cards = [
    'Today'      => revenueSince($link, 'DATE(b.bill_time) = CURDATE()'),
    'This week'  => revenueSince($link, 'b.bill_time >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY'),
    'This month' => revenueSince($link, "b.bill_time >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"),
    'All time'   => revenueSince($link, '1 = 1'),
];

// the last 14 days, one bar each
$days = mysqli_fetch_all(mysqli_query($link, "
    SELECT DATE(b.bill_time) AS day, SUM(bi.quantity * m.item_price) AS money
    FROM Bills AS b
    JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
    JOIN Menu AS m ON m.item_id = bi.item_id
    WHERE b.payment_time IS NOT NULL AND b.bill_time >= CURDATE() - INTERVAL 13 DAY
    GROUP BY day ORDER BY day"), MYSQLI_ASSOC);
$bestDay = 1;
foreach ($days as $d) {
    $bestDay = max($bestDay, (float) $d['money']);
}

// cash or card, this month
$methods = mysqli_fetch_all(mysqli_query($link, "
    SELECT b.payment_method AS method, SUM(bi.quantity * m.item_price) AS money
    FROM Bills AS b
    JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
    JOIN Menu AS m ON m.item_id = bi.item_id
    WHERE b.payment_time IS NOT NULL AND b.bill_time >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    GROUP BY b.payment_method"), MYSQLI_ASSOC);
$methodTotal = 0;
foreach ($methods as $m) {
    $methodTotal = $methodTotal + $m['money'];
}
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Revenue Statistics</h2>
                <p class="pg-sub">Paid bills only, before the 10% tax.</p>
            </div>
            <a href="../report/generate_report.php" class="btn btn-dark">Download PDF report</a>
        </div>

        <div class="rv-cards">
<?php foreach ($cards as $label => $c) { ?>
            <div class="rv-card">
                <span class="pg-label"><?= $label ?></span>
                <span class="rv-money">&#8377;<?= number_format((float) $c['money']) ?></span>
                <span class="rv-small"><?= number_format((int) $c['bills']) ?> bills</span>
            </div>
<?php } ?>
        </div>

        <div class="rv-box">
            <h3>Last 14 days</h3>
            <div class="rv-bars">
<?php foreach ($days as $d) { ?>
                <div class="rv-bar" title="&#8377;<?= number_format((float) $d['money']) ?>">
                    <span class="rv-bar__fill" style="height: <?= round($d['money'] / $bestDay * 100) ?>%"></span>
                    <span class="rv-bar__day"><?= date('j M', strtotime($d['day'])) ?></span>
                </div>
<?php } ?>
            </div>
        </div>

        <div class="rv-box">
            <h3>How guests paid this month</h3>
<?php if ($methodTotal == 0) { ?>
            <p class="text-muted">No paid bills this month yet.</p>
<?php } ?>
<?php foreach ($methods as $m) { $share = round($m['money'] / max($methodTotal, 1) * 100); ?>
            <div class="rv-method">
                <span class="rv-method__name"><?= htmlspecialchars(ucfirst((string) $m['method'])) ?></span>
                <div class="rp-bar"><span style="width: <?= $share ?>%"></span></div>
                <span class="pg-num"><?= $share ?>% &middot; &#8377;<?= number_format((float) $m['money']) ?></span>
            </div>
<?php } ?>
        </div>
    </div>
</div>

<style>
    .rv-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; margin-bottom: 1.3rem; }
    .rv-card, .rv-box { background: var(--ink-700); border: 1px solid var(--line); padding: 1.1rem 1.2rem; }
    .rv-box { margin-bottom: 1.3rem; }
    .rv-box h3 { font-size: 1.05rem; margin: 0 0 1rem; }
    .rv-money { display: block; font-family: var(--brand-font); font-size: 1.7rem; color: var(--text); margin-top: 0.2rem; }
    .rv-small { font-size: 0.8rem; color: var(--text-faint); }
    .rv-bars { display: flex; align-items: flex-end; gap: 8px; height: 190px; }
    .rv-bar { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 6px; }
    .rv-bar__fill { width: 100%; background: linear-gradient(180deg, #e0784f, #b1462f); min-height: 2px; }
    .rv-bar__day { font-size: 0.65rem; color: var(--text-faint); white-space: nowrap; }
    .rv-method { display: grid; grid-template-columns: 5rem 1fr 11rem; align-items: center; gap: 1rem; margin-bottom: 0.7rem; }
    .rp-bar { height: 8px; background: rgba(255, 255, 255, 0.06); }
    .rp-bar span { display: block; height: 100%; background: linear-gradient(90deg, #b1462f, #e0784f); }
</style>

<?php include '../includes/dashFooter.php'; ?>
