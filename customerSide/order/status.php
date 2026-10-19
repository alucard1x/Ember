<?php
// status.php - tells track.php (as JSON) how far the guest's order is.
// track.php asks every few seconds, so the page updates by itself.
session_start();
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['track_bill'])) {
    echo json_encode(['found' => false]);
    exit;
}
$bill_id = (int) $_SESSION['track_bill'];

$stmt = mysqli_prepare($link, 'SELECT m.item_name, k.quantity, k.status
                               FROM Kitchen AS k
                               JOIN Menu AS m ON m.item_id = k.item_id
                               WHERE k.bill_id = ?
                               ORDER BY k.kitchen_id');
mysqli_stmt_bind_param($stmt, 'i', $bill_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$dishes = [];
$ready = 0;
$started = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $dishes[] = $row;
    if ($row['status'] == 'ready') {
        $ready++;
    }
    if ($row['status'] != 'waiting') {
        $started++;
    }
}

// one step for the whole order: received → cooking → ready
$step = 'received';
if ($started > 0) {
    $step = 'cooking';
}
if (count($dishes) > 0 && $ready == count($dishes)) {
    $step = 'ready';
}

$table_id = null;
if (isset($_SESSION['order_table'])) {
    $table_id = (int) $_SESSION['order_table'];
}

echo json_encode([
    'found'  => true,
    'bill'   => $bill_id,
    'table'  => $table_id,
    'step'   => $step,
    'ready'  => $ready,
    'total'  => count($dishes),
    'dishes' => $dishes,
]);
