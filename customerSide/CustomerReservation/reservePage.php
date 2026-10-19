<?php
// reservePage.php - the reservation page: "Search for Time" (left) and "Make Reservation" (right)
require_once '../config.php';   // gives us $link
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14100f">
    <title>Reservations &mdash; Ember</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- the design of the whole site -->
    <link rel="stylesheet" href="../css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>">
    <!-- same look as the new homepage -->
    <link rel="stylesheet" href="../css/ember-v2.css?v=<?= @filemtime(__DIR__ . '/../css/ember-v2.css') ?>">
</head>
<body>
<?php $bgAssets = '../image'; include '../components/background.php'; ?>
<?php $brandHome = '../home/home.php'; include '../components/brandLink.php'; ?>
    <?php
        // insertReservation.php sends us back here with ?reservation=success
        // -> show a message and open the receipt
        $message = '';
        if (isset($_GET['reservation']) && $_GET['reservation'] === 'success') {
            $message = "Reservation successful";
            $reservation_id = null;
            if (isset($_GET['reservation_id'])) {
                $reservation_id = $_GET['reservation_id'];
            }
            echo '<script>alert("Table Successfully Reserved. Click OK to view your reservation receipt."); window.location.href = "reservationReceipt.php?reservation_id=' . $reservation_id . '";</script>';
        }
        // insertReservation.php found the table already booked (someone else was faster)
        if (isset($_GET['reservation']) && $_GET['reservation'] === 'taken') {
            echo '<script>alert("Sorry, that table was just booked by someone else. Please choose another table or time.");</script>';
        }
    ?>
    <div class="member-info"></div>
    <div class="reserve-container">
        <h1 class="reserve-title">Reservations</h1>

        <div class="row">
            <div class="column">
                <div id="Search Table">
                    <h2>Search for Time</h2>
                    <!-- LEFT FORM: goes to availability.php, which finds the booked tables -->
                    <form id="reservation-form" method="GET" action="availability.php"><br>
                        <div class="form-group">
                        <label for="reservation_date" style="">Select Date</label><br>
                        <input class="form-control" type="date" id="reservation_date" name="reservation_date" required>
                        </div>
                        <div class="form-group">
                        <label for="reservation_time" style="">Available Reservation Times</label>
                        <div id="availability-table">
                            <?php
                            // one time every hour from 10:00:00 to 20:00:00
                            $availableTimes = array();
                            for ($hour = 10; $hour <= 20; $hour++) {
                                $time = sprintf('%02d:00:00', $hour);
                                $availableTimes[] = $time;
                            }
                            echo '<select name="reservation_time" id="reservation_time" style="width:10em;" class="form-control" >';
                            echo '<option value="" selected disabled>Select a Time</option>';
                            foreach ($availableTimes as $time) {
                                echo "<option  value='$time'>$time</option>";
                            }
                            echo '</select>';
                            // optional message in the url
                            if (isset($_GET['message'])) {
                                $message = $_GET['message'];
                                echo "<p>$message</p>";
                            }
                            ?>
                        </div>
                        </div>
              
                        <input type="number" id="head_count" name="head_count" value=1 hidden required>
                        <button type="submit" class="btn" name="submit">Search</button>
                    </form>
                </div>
            </div>

            <div class="column right-column">
                <div id="insert-reservation-into-table">
                    <h2>Make Reservation</h2>
                    <!-- RIGHT FORM: goes to insertReservation.php, which saves the booking -->
                    <form id="reservation-form" method="POST" action="insertReservation.php">
                        <br>
                        <div class="form-group">
                            <label for="customer_name" style="">Customer Name</label><br>
                            <input class="form-control" type="text" id="customer_name" name="customer_name" required>
                        </div>
                        <?php
                        // the date and time chosen in the left form (or today, 13:00)
                        $defaultReservationDate = date("Y-m-d");
                        if (isset($_GET['reservation_date'])) {
                            $defaultReservationDate = $_GET['reservation_date'];
                        }
                        $defaultReservationTime = "13:00:00";
                        if (isset($_GET['reservation_time'])) {
                            $defaultReservationTime = $_GET['reservation_time'];
                        }
                        ?>
                   
                        <div class="form-group " >
                            <label for="reservation_date" style="">Reservation Date</label><br>
                            <input type="date" id="reservation_date" name="reservation_date"
                                   value="<?= $defaultReservationDate ?>" readonly required>
                            <input type="time" id="reservation_time" name="reservation_time"
                                   value="<?= $defaultReservationTime ?>" readonly required>
                        </div>
                 
                        <div class="form-group">
                            <label for="table_id_reserve" style="">Available Tables</label>
                            <select class="form-control" name="table_id" id="table_id_reserve" style="width:10em;" required>
                                <option value="" selected disabled>Select a Table</option>
                                <?php
                                // booked tables from availability.php, like "3,7,12"
                                $table_id_list = '';
                                if (isset($_GET['reserved_table_id'])) {
                                    $table_id_list = $_GET['reserved_table_id'];
                                }
                                $head_count = 1;
                                if (isset($_GET['head_count'])) {
                                    $head_count = (int) $_GET['head_count'];
                                }

                                // turn "3,7,12" into a list of numbers [3, 7, 12]
                                // (empty parts are skipped, otherwise the SQL would be "NOT IN ()" which is an error)
                                $reserved_table_ids = array();
                                foreach (explode(',', $table_id_list) as $part) {
                                    $part = trim($part);
                                    if ($part !== '') {
                                        $reserved_table_ids[] = intval($part);
                                    }
                                }

                                // all tables big enough, except the booked ones
                                $select_query_tables = "SELECT * FROM Restaurant_Tables WHERE capacity >= '$head_count'";
                                if (!empty($reserved_table_ids)) {
                                    $reserved_table_ids_string = implode(',', $reserved_table_ids);
                                    $select_query_tables .= " AND table_id NOT IN ($reserved_table_ids_string)";
                                }
                                $result_tables = mysqli_query($link, $select_query_tables);
                                $resultCheckTables = mysqli_num_rows($result_tables);
                                if ($resultCheckTables > 0) {
                                    while ($row = mysqli_fetch_assoc($result_tables)) {
                                        echo '<option value="' . $row['table_id'] . '">For ' . $row['capacity'] . ' people. (Table Id: ' . $row['table_id'] . ')</option>';
                                    }
                                }  else {
                                    echo '<option disabled>No tables available, please choose another time.</option>';
                                    echo '<script>alert("No reservation tables found for the selected time. Please choose another time.");</script>';
                                }
                                ?>
                            </select>
                            <input type="number" id="head_count" name="head_count" value="<?= $head_count ?>" required hidden>
                        </div>
                 
                        <div class="form-group mb-3">
                            <label for="special_request">Special request</label><br>
                            <textarea class="form-control"  id="special_request" name="special_request"> </textarea><br>
                            <button type="submit" class="btn" name="submit">Make Reservation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // copy the chosen date into the date box (both boxes have the same id, so this finds the first one)
        const viewDateInput = document.getElementById("reservation_date");
        const makeDateInput = document.getElementById("reservation_date");

        viewDateInput.addEventListener("change", function () {
            makeDateInput.value = this.value;
        });
    </script>
</body>

</html>
