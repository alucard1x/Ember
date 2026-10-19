<?php
// place.php - "Send to kitchen" button. Turns the guest's cart into a real order:
// Bills (the table's open bill) + Bill_Items (the dishes) + Kitchen (what the cooks must make).
// Everything happens in ONE transaction, so an order is never half saved.
session_start();
require_once '../config.php';

// only with a table (from the QR code) and something in the cart
$cart = [];
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['order_table']) || count($cart) == 0) {
    header('Location: ../menu/menu.php#bill');
    exit;
}

$table_id = (int) $_SESSION['order_table'];
$now = date('Y-m-d H:i:s');
$twelve_hours_ago = date('Y-m-d H:i:s', time() - 12 * 3600);
$ok = true;

mysqli_begin_transaction($link);

// 1. find the table's open (not paid) bill from today, or start a new one.
//    FOR UPDATE locks it, so two phones at the same table can't create two bills at once.
$stmt = mysqli_prepare($link, 'SELECT bill_id FROM Bills
                               WHERE table_id = ? AND payment_time IS NULL AND bill_time >= ?
                               ORDER BY bill_time DESC LIMIT 1 FOR UPDATE');
mysqli_stmt_bind_param($stmt, 'is', $table_id, $twelve_hours_ago);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($row) {
    $bill_id = (int) $row['bill_id'];
} else {
    $stmt = mysqli_prepare($link, 'INSERT INTO Bills (table_id, bill_time) VALUES (?, ?)');
    mysqli_stmt_bind_param($stmt, 'is', $table_id, $now);
    $ok = mysqli_stmt_execute($stmt);
    $bill_id = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);
}

// 2. every dish goes on the bill and into the kitchen queue
foreach ($cart as $item_id => $quantity) {
    if (!$ok) {
        break;
    }
    $quantity = (int) $quantity;

    // on the bill: add to the same dish if it is already there, otherwise a new line
    $stmt = mysqli_prepare($link, 'UPDATE Bill_Items SET quantity = quantity + ? WHERE bill_id = ? AND item_id = ?');
    mysqli_stmt_bind_param($stmt, 'iis', $quantity, $bill_id, $item_id);
    $ok = mysqli_stmt_execute($stmt);
    $changed = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($ok && $changed == 0) {
        $stmt = mysqli_prepare($link, 'INSERT INTO Bill_Items (bill_id, item_id, quantity) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'isi', $bill_id, $item_id, $quantity);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // in the kitchen: a new ticket line that starts as 'waiting'
    if ($ok) {
        $stmt = mysqli_prepare($link, "INSERT INTO Kitchen (table_id, item_id, quantity, time_submitted, status, bill_id)
                                       VALUES (?, ?, ?, ?, 'waiting', ?)");
        mysqli_stmt_bind_param($stmt, 'isisi', $table_id, $item_id, $quantity, $now, $bill_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// 3. save everything, or nothing
if ($ok) {
    mysqli_commit($link);
    $_SESSION['cart'] = [];
    $_SESSION['track_bill'] = $bill_id;     // track.php follows this bill
    header('Location: track.php');
} else {
    mysqli_rollback($link);
    header('Location: ../menu/menu.php?order_error=1#bill');
}
exit;
