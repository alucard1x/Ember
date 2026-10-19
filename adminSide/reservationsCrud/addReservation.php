<?php
// addReservation.php - saves a reservation made by staff (Reservations page).
// Race-proof, like the website: the table row is locked with FOR UPDATE inside a transaction,
// so two people can never book the same table for the same time.
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only
require_once '../config.php';

$name = trim($_POST['customer_name'] ?? '');
$date = $_POST['reservation_date'] ?? '';
$time = $_POST['reservation_time'] ?? '';
$people = (int) ($_POST['head_count'] ?? 0);
$table_id = (int) ($_POST['table_id'] ?? 0);
$request = trim($_POST['special_request'] ?? '');
$back = '../panel/reservation-panel.php';

if ($name == '' || $date == '' || $time == '' || $people < 1 || $table_id < 1) {
    header('Location: ' . $back . '?msg=error');
    exit;
}

// the same reservation id the website makes: time + date + table number
$reservation_id = intval($time) . intval($date) . $table_id;

mysqli_begin_transaction($link);

// 1. lock the table
$stmt = $link->prepare('SELECT capacity FROM Restaurant_Tables WHERE table_id = ? FOR UPDATE');
$stmt->bind_param('i', $table_id);
$stmt->execute();
$table = $stmt->get_result()->fetch_assoc();

// 2. still free at that time?
$stmt = $link->prepare('SELECT COUNT(*) AS taken FROM Reservations WHERE table_id = ? AND reservation_date = ? AND reservation_time = ?');
$stmt->bind_param('iss', $table_id, $date, $time);
$stmt->execute();
$taken = $stmt->get_result()->fetch_assoc()['taken'];

if (!$table || $taken > 0) {
    mysqli_rollback($link);
    header('Location: ' . $back . '?msg=taken');
    exit;
}

// 3. save the booking and mark the table as taken at that time
$stmt = $link->prepare('INSERT INTO Reservations (reservation_id, customer_name, table_id, reservation_time, reservation_date, head_count, special_request)
                        VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('isissis', $reservation_id, $name, $table_id, $time, $date, $people, $request);
$ok = $stmt->execute();

if ($ok) {
    $stmt = $link->prepare("INSERT INTO Table_Availability (availability_id, table_id, reservation_date, reservation_time, status) VALUES (?, ?, ?, ?, 'no')");
    $stmt->bind_param('iiss', $reservation_id, $table_id, $date, $time);
    $ok = $stmt->execute();
}

if ($ok) {
    mysqli_commit($link);
    header('Location: ' . $back . '?msg=added');
} else {
    mysqli_rollback($link);
    header('Location: ' . $back . '?msg=taken');
}
exit;
