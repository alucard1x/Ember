<?php
// bill-panel.php - staff panel: Bills. Today's bills first, with the total, how it was paid and a receipt link.
// Quick filters: Today / Last 7 days / Last 30 days / Unpaid. Type to search inside the list.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

// which bills to show
$show = $_GET['show'] ?? 'today';
$filters = [
    'today'  => 'Today',
    'week'   => 'Last 7 days',
    'month'  => 'Last 30 days',
    'unpaid' => 'Not paid',
];
if (!isset($filters[$show])) {
    $show = 'today';
}
$where = "DATE(b.bill_time) = CURDATE()";
if ($show == 'week') {
    $where = "b.bill_time >= CURDATE() - INTERVAL 7 DAY";
} elseif ($show == 'month') {
    $where = "b.bill_time >= CURDATE() - INTERVAL 30 DAY";
} elseif ($show == 'unpaid') {
    $where = "b.payment_time IS NULL";
}

// one row per bill: total from Bill_Items × Menu, plus the member's and staff member's names
$sql = "SELECT b.bill_id, b.table_id, b.bill_time, b.payment_time, b.payment_method,
               ms.member_name, s.staff_name,
               COUNT(bi.bill_item_id) AS dish_lines,
               COALESCE(SUM(m.item_price * bi.quantity), 0) * 1.1 AS total
        FROM Bills AS b
        LEFT JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
        LEFT JOIN Menu AS m ON m.item_id = bi.item_id
        LEFT JOIN Memberships AS ms ON ms.member_id = b.member_id
        LEFT JOIN Staffs AS s ON s.staff_id = b.staff_id
        WHERE $where
        GROUP BY b.bill_id
        ORDER BY b.bill_time DESC
        LIMIT 500";
$bills = mysqli_fetch_all(mysqli_query($link, $sql), MYSQLI_ASSOC);

$sum = 0;
foreach ($bills as $bill) {
    if ($bill['payment_time'] != null) {
        $sum = $sum + $bill['total'];
    }
}
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Bills</h2>
                <p class="pg-sub">Totals include 10% tax. Click Receipt to print a paid bill.</p>
            </div>
        </div>

        <div class="pg-tools">
<?php foreach ($filters as $key => $label) { ?>
            <a class="pg-chip<?= $key == $show ? ' is-on' : '' ?>" href="?show=<?= $key ?>"><?= $label ?></a>
<?php } ?>
            <input type="search" class="form-control pg-search" placeholder="Search bill, table, member…" data-filter="#bills-list">
            <span class="pg-count" data-count><?= count($bills) ?> bills &middot; &#8377;<?= number_format($sum) ?> paid</span>
        </div>

        <div class="table-scroll"><table class="table" id="bills-list">
            <thead><tr><th>Bill</th><th>Table</th><th>Opened</th><th>Dishes</th><th class="pg-num">Total</th><th>Status</th><th>Member</th><th>Staff</th><th></th></tr></thead>
            <tbody>
<?php if (count($bills) == 0) { ?>
                <tr><td colspan="9" class="text-muted">No bills here yet.</td></tr>
<?php } ?>
<?php foreach ($bills as $b) { ?>
                <tr>
                    <td>#<?= (int) $b['bill_id'] ?></td>
                    <td><?= (int) $b['table_id'] ?></td>
                    <td><?= date('j M, H:i', strtotime($b['bill_time'])) ?></td>
                    <td><?= (int) $b['dish_lines'] ?></td>
                    <td class="pg-num">&#8377;<?= number_format((float) $b['total'], 2) ?></td>
                    <td>
<?php   if ($b['payment_time'] != null) { ?>
                        <span class="pg-tag is-green">Paid &middot; <?= htmlspecialchars((string) $b['payment_method']) ?></span>
<?php   } else { ?>
                        <span class="pg-tag is-red">Not paid</span>
<?php   } ?>
                    </td>
                    <td><?= htmlspecialchars((string) $b['member_name']) ?></td>
                    <td><?= htmlspecialchars((string) $b['staff_name']) ?></td>
                    <td class="pg-actions">
<?php   if ($b['payment_time'] != null) { ?>
                        <a href="../posBackend/receipt.php?bill_id=<?= (int) $b['bill_id'] ?>">Receipt</a>
<?php   } else { ?>
                        <a href="../posBackend/orderItem.php?table_id=<?= (int) $b['table_id'] ?>">Open</a>
<?php   } ?>
                    </td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>
    </div>
</div>

<script src="../js/staffPages.js"></script>
<?php include '../includes/dashFooter.php'; ?>
