<?php
// insertReservation.php - "Make Reservation" button. Saves the booking in the
// Reservations and Table_Availability tables, then goes back to reservePage.php
//
// RACE-PROOF: two guests can press the button for the same table at the same second.
// We lock the table's row with SELECT ... FOR UPDATE inside a transaction, so the second
// guest waits, then sees the table is already booked. (Try it in Staff panel → Booking Race Lab.)
require_once '../config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // read the form values
    $customer_name    = $_POST["customer_name"];
    $table_id         = intval($_POST["table_id"]);
    $reservation_time = $_POST["reservation_time"];
    $reservation_date = $_POST["reservation_date"];
    $special_request  = $_POST["special_request"];

    // the reservation id is made by joining the time, the date and the table number
    $reservation_id = intval($reservation_time) . intval($reservation_date) . intval($table_id);

    mysqli_begin_transaction($link);

    // 1. lock this restaurant table: anyone else booking it now has to wait until we COMMIT
    $stmt = mysqli_prepare($link, 'SELECT capacity FROM Restaurant_Tables WHERE table_id = ? FOR UPDATE');
    mysqli_stmt_bind_param($stmt, 'i', $table_id);
    mysqli_stmt_execute($stmt);
    $table = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$table) {
        mysqli_rollback($link);
        echo "Error: this table does not exist.";
        exit;
    }
    $head_count = $table['capacity'];

    // 2. is the table still free at that date and time?
    $stmt = mysqli_prepare($link, 'SELECT COUNT(*) AS taken FROM Reservations
                                   WHERE table_id = ? AND reservation_date = ? AND reservation_time = ?');
    mysqli_stmt_bind_param($stmt, 'iss', $table_id, $reservation_date, $reservation_time);
    mysqli_stmt_execute($stmt);
    $check = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($check['taken'] > 0) {
        // someone was faster: undo and tell the guest
        mysqli_rollback($link);
        header("Location: reservePage.php?reservation=taken");
        exit;
    }

    // 3. save the booking and mark the table as not free at that time
    $stmt = mysqli_prepare($link, 'INSERT INTO Reservations (reservation_id, customer_name, table_id, reservation_time, reservation_date, head_count, special_request)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'isissis', $reservation_id, $customer_name, $table_id, $reservation_time, $reservation_date, $head_count, $special_request);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $stmt = mysqli_prepare($link, "INSERT INTO Table_Availability (availability_id, table_id, reservation_date, reservation_time, status)
                                       VALUES (?, ?, ?, ?, 'no')");
        mysqli_stmt_bind_param($stmt, 'iiss', $reservation_id, $table_id, $reservation_date, $reservation_time);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // 4. both saved → COMMIT (this also releases the lock). Otherwise undo everything.
    if ($ok) {
        mysqli_commit($link);
        $_SESSION['customer_name'] = $customer_name;
        header("Location: reservePage.php?reservation=success&reservation_id=$reservation_id");
    } else {
        mysqli_rollback($link);
        header("Location: reservePage.php?reservation=taken");
    }
    exit;
}
?>
