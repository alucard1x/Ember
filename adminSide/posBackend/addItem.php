<?php
// addItem.php - POS: adds ONE portion of a dish to a table's open bill (Bill_Items) and sends it to the kitchen.
// If the table has no open bill yet, a new bill is started automatically.
// Example: addItem.php?table_id=5&item_id=TK1
require_once __DIR__ . '/checkIfLoggedIn.php';   // staff only: if not logged in, go to the staff login page
require_once '../config.php';

$table_id = intval($_GET['table_id'] ?? 0);
$item_id = $_GET['item_id'] ?? '';
$now = date('Y-m-d H:i:s');

mysqli_begin_transaction($link);

// 1. the open bill of this table = its latest bill, if it is not paid yet
$stmt = $link->prepare('SELECT bill_id, payment_time FROM Bills WHERE table_id = ? ORDER BY bill_time DESC, bill_id DESC LIMIT 1 FOR UPDATE');
$stmt->bind_param('i', $table_id);
$stmt->execute();
$latest = $stmt->get_result()->fetch_assoc();

if ($latest && $latest['payment_time'] == null) {
    $bill_id = (int) $latest['bill_id'];
} else {
    // no open bill → start a new one for this table
    $stmt = $link->prepare('INSERT INTO Bills (table_id, bill_time) VALUES (?, ?)');
    $stmt->bind_param('is', $table_id, $now);
    $stmt->execute();
    $bill_id = $link->insert_id;
}

// 2. on the bill: one more of this dish (or a new line)
$stmt = $link->prepare('UPDATE Bill_Items SET quantity = quantity + 1 WHERE bill_id = ? AND item_id = ?');
$stmt->bind_param('is', $bill_id, $item_id);
$stmt->execute();
$ok = true;
if ($stmt->affected_rows == 0) {
    $stmt = $link->prepare('INSERT INTO Bill_Items (bill_id, item_id, quantity) VALUES (?, ?, 1)');
    $stmt->bind_param('is', $bill_id, $item_id);
    $ok = $stmt->execute();
}

// 3. in the kitchen: a new ticket line for this portion
if ($ok) {
    $stmt = $link->prepare("INSERT INTO Kitchen (table_id, item_id, quantity, time_submitted, status, bill_id) VALUES (?, ?, 1, ?, 'waiting', ?)");
    $stmt->bind_param('issi', $table_id, $item_id, $now, $bill_id);
    $ok = $stmt->execute();
}

if ($ok) {
    mysqli_commit($link);
} else {
    mysqli_rollback($link);
}

header('Location: orderItem.php?table_id=' . $table_id);
exit;
