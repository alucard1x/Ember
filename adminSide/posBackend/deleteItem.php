<?php
require_once '../config.php';

$bill_id  = mysqli_real_escape_string($link, $_GET['bill_id']  ?? '');
$table_id = mysqli_real_escape_string($link, $_GET['table_id'] ?? '');

if (isset($_GET['bill_item_id'])) {
    $bill_item_id = mysqli_real_escape_string($link, $_GET['bill_item_id']);
    $item_id = mysqli_real_escape_string($link, $_GET['item_id'] ?? '');


    // Delete the item with the given bill_item_id
    $delete_query = "DELETE FROM Bill_Items WHERE bill_item_id = '$bill_item_id'";
    
    if (mysqli_query($link, $delete_query)) {
        // Redirect back to the orderItem.php page with a success message
        $update_kitchen_sql = "DELETE FROM Kitchen WHERE table_id = '$table_id' AND item_id = '$item_id'";
        mysqli_query($link, $update_kitchen_sql);

        
        header("Location: orderItem.php?bill_id={$bill_id}&delete_success=1&table_id={$table_id}");
        exit();
    } else {
        header("Location: orderItem.php?bill_id={$bill_id}&delete_error=1&table_id={$table_id}");
        exit();
    }
} else {
    header("Location: orderItem.php?bill_id={$bill_id}&table_id={$table_id}");
    exit();
}
?>
