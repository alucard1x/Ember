<?php
// cart.php - adds, removes or clears dishes in "Your Order", then goes back to the menu
// the cart is only kept in the session, nothing is saved in the database
session_start();
require_once '../config.php';

// make an empty cart the first time
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// returns true if this item_id is really on the menu
function menuItemExists($link, $item_id) {
    $stmt = mysqli_prepare($link, "SELECT item_id FROM Menu WHERE item_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $found = false;
    if ($result && mysqli_num_rows($result) > 0) {
        $found = true;
    }
    mysqli_stmt_close($stmt);
    return $found;
}

if (isset($_GET['add'])) {
    // cart.php?add=ID  -> one more of this dish
    $item_id = $_GET['add'];
    if (menuItemExists($link, $item_id)) {
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id] = $_SESSION['cart'][$item_id] + 1;
        } else {
            $_SESSION['cart'][$item_id] = 1;
        }
    }
} elseif (isset($_GET['remove'])) {
    // cart.php?remove=ID  -> one less of this dish
    $item_id = $_GET['remove'];
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] = $_SESSION['cart'][$item_id] - 1;
        if ($_SESSION['cart'][$item_id] < 1) {
            unset($_SESSION['cart'][$item_id]);
        }
    }
} elseif (isset($_GET['clear'])) {
    // cart.php?clear=1  -> empty the whole cart
    $_SESSION['cart'] = [];
}

mysqli_close($link);

// go back to the "Your Order" part of the menu page
header('Location: menu.php#bill');
exit;
