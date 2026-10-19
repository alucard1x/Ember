<?php
// availability.php - "Search" button of the reservation page.
// finds the tables already booked at that date and time (Reservations table)
// and sends their ids back to reservePage.php, so those tables are not shown
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    // read the form values
    $selectedDate = '';
    if (isset($_GET["reservation_date"])) {
        $selectedDate = mysqli_real_escape_string($link, $_GET["reservation_date"]);
    }

    $head_count = 0;    // number of people
    if (isset($_GET["head_count"])) {
        $head_count = (int) $_GET["head_count"];
    }

    $timeText = 'now';
    if (isset($_GET["reservation_time"])) {
        $timeText = $_GET["reservation_time"];
    }
    $selectedTime = date("H:i:s", strtotime($timeText));   // e.g. "13:00:00"

    if ($selectedDate === '' || !$head_count) {
        echo "Missing reservation date, time or head count.";
        exit;
    }

    // 1. which tables are already booked at this date and time?
    $reservedQuery = "SELECT * FROM Reservations WHERE reservation_date = '$selectedDate' AND reservation_time = '$selectedTime'";
    $reservedResult = mysqli_query($link, $reservedQuery);

    $reservedTableIDs = array();
    if ($reservedResult) {
        while ($row = mysqli_fetch_assoc($reservedResult)) {
            $reservedTableIDs[] = $row["table_id"];
        }
    } else {
        echo "Query failed: " . mysqli_error($link);
    }

    // list of ids like "3,7,12" (empty if nothing is booked)
    $reservedTableIDsString = implode(",", $reservedTableIDs);

    // 2. if some tables are booked, check that the free-tables query works
    //    (reservePage.php runs the same kind of query to fill the "Available Tables" list)
    if (!empty($reservedTableIDs)) {
        $availableTables = "SELECT table_id, capacity FROM Restaurant_Tables WHERE capacity >= '$head_count' AND table_id NOT IN ($reservedTableIDsString)";
        $availableResult = mysqli_query($link, $availableTables);

        if (!$availableResult) {
            echo "Available tables query failed: " . mysqli_error($link);
            exit;
        }
    }

    // 3. go back to the reservation page with the date, time, people and booked tables
    $reservationLink = "reservePage.php?reservation_date=" . urlencode($selectedDate)
        . "&head_count=" . urlencode($head_count)
        . "&reservation_time=" . urlencode($selectedTime)
        . "&reserved_table_id=" . urlencode($reservedTableIDsString);

    header("Location: $reservationLink");
    exit();
}
?>
