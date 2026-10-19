<?php
// table.php - the QR code on every restaurant table opens this page.
// It remembers which table the guest is sitting at, then opens the menu.
// Example: table.php?t=5  → the guest is at table 5
session_start();
require_once '../config.php';

$table_id = 0;
if (isset($_GET['t'])) {
    $table_id = (int) $_GET['t'];
}

// make sure this table really exists
$stmt = mysqli_prepare($link, 'SELECT table_id FROM Restaurant_Tables WHERE table_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $table_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$found = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($found) {
    $_SESSION['order_table'] = $table_id;   // menu.php now shows "Send to kitchen"
}

header('Location: ../menu/menu.php');
exit;
