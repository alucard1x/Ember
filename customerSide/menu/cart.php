<?php
// Keeps the visitor's order in the session only. Nothing is written to the
// Bills / Bill_Items tables, so the staff POS and Kitchen queue are untouched.
session_start();
require_once '../config.php';

$_SESSION['cart'] = $_SESSION['cart'] ?? [];

// Only accept ids that actually exist on the menu.
function menuItemExists($link, $item_id) {
    $stmt = mysqli_prepare($link, "SELECT item_id FROM Menu WHERE item_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $found = $result && mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    return $found;
}

if (isset($_GET['add'])) {
    $item_id = $_GET['add'];
    if (menuItemExists($link, $item_id)) {
        $_SESSION['cart'][$item_id] = ($_SESSION['cart'][$item_id] ?? 0) + 1;
    }
} elseif (isset($_GET['remove'])) {
    $item_id = $_GET['remove'];
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]--;
        if ($_SESSION['cart'][$item_id] < 1) {
            unset($_SESSION['cart'][$item_id]);
        }
    }
} elseif (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
}

mysqli_close($link);

header('Location: menu.php#bill');
exit;
