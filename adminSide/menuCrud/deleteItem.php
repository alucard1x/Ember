<?php
// deleteItem.php - deletes a dish from the Menu table
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only: if not logged in, go to the staff login page
// Include config file
require_once "../config.php";

// Check if the item_id parameter is set in the URL
if (isset($_GET['id'])) {
    // Item ids are short codes such as 'BP8', so keep them as strings
    $item_id = $_GET['id'];

    // Disable foreign key checks
    $disableForeignKeySQL = "SET FOREIGN_KEY_CHECKS=0;";
    mysqli_query($link, $disableForeignKeySQL);

    // Bind the id instead of pasting it into the SQL
    $deleteStmt = mysqli_prepare($link, "DELETE FROM Menu WHERE item_id = ?");
    mysqli_stmt_bind_param($deleteStmt, "s", $item_id);

    // Execute the DELETE query
    if (mysqli_stmt_execute($deleteStmt)) {
        // Item successfully deleted, redirect back to the main page
        header("location: ../panel/menu-panel.php");
        echo 'deleted';
        exit();
    } else {
        // Error occurred during execution, display an error message
        echo "Error: " . mysqli_stmt_error($deleteStmt);
    }

    mysqli_stmt_close($deleteStmt);

    // Enable foreign key checks
    $enableForeignKeySQL = "SET FOREIGN_KEY_CHECKS=1;";
    mysqli_query($link, $enableForeignKeySQL);

    // Close the connection
    mysqli_close($link);
}
?>
