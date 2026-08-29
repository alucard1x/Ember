<?php
// availability.php
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $selectedDate = mysqli_real_escape_string($link, $_GET["reservation_date"] ?? '');   // Selected Date
    $head_count   = (int)($_GET["head_count"] ?? 0);    // Number of people
    $selectedTime = date("H:i:s", strtotime($_GET["reservation_time"] ?? 'now'));

    if ($selectedDate === '' || !$head_count) {
        echo "Missing reservation date, time or head count.";
        exit;
    }

    // Query to get all reservations for the selected date and time
    $reservedQuery = "SELECT * FROM Reservations WHERE reservation_date = '$selectedDate' AND reservation_time = '$selectedTime'";
    $reservedResult = mysqli_query($link, $reservedQuery);

    // Initialize an array to store reserved table IDs
    $reservedTableIDs = array();

    // Collect reserved table IDs
    if ($reservedResult) {
        while ($row = mysqli_fetch_assoc($reservedResult)) {
            $reservedTableIDs[] = $row["table_id"];
        }
    } else {
        echo "Query failed: " . mysqli_error($link);
    }

    // Check available tables
    if (!empty($reservedTableIDs)) {
        $reservedTableIDsString = implode(",", $reservedTableIDs);
        $availableTables = "SELECT table_id, capacity FROM Restaurant_Tables WHERE capacity >= '$head_count' AND table_id NOT IN ($reservedTableIDsString)";
        $availableResult = mysqli_query($link, $availableTables);

        if ($availableResult) {
            while ($row = mysqli_fetch_assoc($availableResult)) {
                
            }
            // Construct the reservation link with all table IDs
            $reservedTableIDsString = implode(",", $reservedTableIDs);
            $reservationLink = "reservePage.php?reservation_date=" . urlencode($selectedDate)
                . "&head_count=" . urlencode($head_count)
                . "&reservation_time=" . urlencode($selectedTime)
                . "&reserved_table_id=" . urlencode($reservedTableIDsString);

            // Add header link to reservationPage.php with parameters
            header("Location: $reservationLink");
            exit();
        } else {
            echo "Available tables query failed: " . mysqli_error($link);
        }
    } else {
        $reservationLink = "reservePage.php?reservation_date=" . urlencode($selectedDate)
            . "&head_count=" . urlencode($head_count)
            . "&reservation_time=" . urlencode($selectedTime)
            . "&reserved_table_id=";
        header("Location: $reservationLink");
        exit();
    }
    

}
?>
