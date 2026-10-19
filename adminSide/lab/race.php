<?php
// race.php - one booking attempt for the Booking Race Lab (answers in JSON).
// The lab page calls this twice AT THE SAME TIME (guest A and guest B) for the same table.
// It uses the same steps as customerSide/CustomerReservation/insertReservation.php.
//   race.php?who=A            → with the lock   (SELECT ... FOR UPDATE)
//   race.php?who=A&nolock=1   → without the lock (to show what goes wrong)
//   race.php?cleanup=1        → delete the test bookings
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only
session_write_close();   // let two requests from the same browser run at the same time
require_once '../config.php';
header('Content-Type: application/json');

// a test booking far in the future, so real reservations are never touched
$table_id = 4;
$date = '2099-12-31';
$time = '20:00:00';

if (isset($_GET['cleanup'])) {
    mysqli_query($link, "DELETE FROM Reservations WHERE reservation_date = '2099-12-31'");
    echo json_encode(['cleaned' => true]);
    exit;
}

if (isset($_GET['count'])) {
    $row = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) AS n FROM Reservations WHERE reservation_date = '2099-12-31'"));
    echo json_encode(['bookings' => (int) $row['n']]);
    exit;
}

$who = 'A';
if (isset($_GET['who']) && $_GET['who'] == 'B') {
    $who = 'B';
}
$useLock = !isset($_GET['nolock']);
$start = microtime(true);

mysqli_begin_transaction($link);

// 1. lock the table's row (only in the safe version)
if ($useLock) {
    $stmt = mysqli_prepare($link, 'SELECT capacity FROM Restaurant_Tables WHERE table_id = ? FOR UPDATE');
    mysqli_stmt_bind_param($stmt, 'i', $table_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}
$gotLockAfter = microtime(true) - $start;

// 2. is it still free?
$stmt = mysqli_prepare($link, 'SELECT COUNT(*) AS taken FROM Reservations WHERE table_id = ? AND reservation_date = ? AND reservation_time = ?');
mysqli_stmt_bind_param($stmt, 'iss', $table_id, $date, $time);
mysqli_stmt_execute($stmt);
$check = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($check['taken'] > 0) {
    mysqli_rollback($link);
    $result = 'refused';
} else {
    // pretend the guest takes 2 seconds to finish (slow phone, slow network)
    mysqli_query($link, 'SELECT SLEEP(2)');

    $name = 'Race test guest ' . $who;
    $stmt = mysqli_prepare($link, "INSERT INTO Reservations (customer_name, table_id, reservation_time, reservation_date, head_count, special_request)
                                   VALUES (?, ?, ?, ?, 2, 'Booking Race Lab')");
    mysqli_stmt_bind_param($stmt, 'siss', $name, $table_id, $time, $date);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_commit($link);
    $result = 'booked';
}

echo json_encode([
    'who'    => $who,
    'lock'   => $useLock,
    'waited' => round($gotLockAfter, 2),            // seconds spent waiting for the lock
    'total'  => round(microtime(true) - $start, 2),
    'result' => $result,
]);
