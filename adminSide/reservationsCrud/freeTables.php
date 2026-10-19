<?php
// freeTables.php - answers (as JSON) which tables are free for a date, time and number of people.
// Used by the "New reservation" form on the Reservations page.
// Example: freeTables.php?date=2026-10-20&time=19:00:00&people=4
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only
require_once '../config.php';
header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$people = (int) ($_GET['people'] ?? 1);

// tables big enough, and NOT booked at that date and time (a subquery with NOT IN)
$stmt = $link->prepare('SELECT table_id, capacity FROM Restaurant_Tables
                        WHERE capacity >= ?
                          AND table_id NOT IN (SELECT table_id FROM Reservations
                                               WHERE reservation_date = ? AND reservation_time = ?
                                                 AND table_id IS NOT NULL)
                        ORDER BY capacity, table_id');
$stmt->bind_param('iss', $people, $date, $time);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
