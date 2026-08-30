<?php
require_once '../config.php';

// Start the session
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Desktop-parity viewport: phones render the full 1280px desktop layout, scaled to fit. -->
    <meta name="viewport" content="width=1280">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">


    <title>Reservations &mdash; Ember Dining &amp; Bar</title>
    <style>
        body {
            font-family: 'Montserrat', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #f3efec;
            display: block;
            min-height: 100vh;
            padding: 20px 20px 60px;
        }
        .reserve-container {
            max-width: 900px;
        }
        .reserve-title {
            font-size: 2.6rem;
            font-weight: 300;
            letter-spacing: 0.35rem;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 6px;
        }
        .reserve-title::after {
            content: '';
            display: block;
            width: 54px;
            height: 1px;
            margin: 22px auto 44px;
            background-color: #b1462f;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 48px;
        }
        .column {
            flex: 1 1 320px;
            min-width: 0;
            padding: 0;
            text-align: left;
            width: auto;
        }
        .column h2 {
            font-size: 1.3rem;
            font-weight: 400;
            letter-spacing: 0.3rem;
            text-transform: uppercase;
            color: #b1462f;
            padding-bottom: 12px;
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        }
        .reserve-container label,
        .reserve-container .form-label {
            display: block;
            font-size: 1.1rem;
            letter-spacing: 0.18rem;
            text-transform: uppercase;
            color: #b9aea8;
            margin-bottom: 8px;
        }
        .reserve-container .form-control {
            width: 100%;
            max-width: 100%;
            padding: 11px 14px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #f3efec;
            background-color: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 0;
            box-shadow: none;
        }
        .reserve-container .form-control:focus {
            outline: none;
            border-color: #b1462f;
            background-color: rgba(255, 255, 255, 0.09);
            color: #f3efec;
            box-shadow: none;
        }
        .reserve-container option {
            color: #14100f;
        }
        .reserve-container .btn {
            display: inline-block;
            padding: 10px 26px;
            font-size: 1.2rem;
            letter-spacing: 0.22rem;
            text-transform: uppercase;
            color: #f3efec;
            background-color: transparent;
            border: 1px solid #b1462f;
            border-radius: 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .reserve-container .btn:hover {
            background-color: #b1462f;
            color: #ffffff;
        }
        @media only screen and (max-width: 767px) {
            body { padding-top: 20px; }
            .row { gap: 28px; }
        }
    </style>
</head>
<body>
<?php $bgAssets = '../image'; include '../components/background.php'; ?>
<?php $brandHome = '../home/home.php'; include '../components/brandLink.php'; ?>
    <?php
        $reservationStatus = $_GET['reservation'] ?? null;
        $message = '';
        if ($reservationStatus === 'success') {
            $message = "Reservation successful";
            $reservation_id = $_GET['reservation_id'] ?? null;
            echo '<script>alert("Table Successfully Reserved. Click OK to view your reservation receipt."); window.location.href = "reservationReceipt.php?reservation_id=' . $reservation_id . '";</script>';

        }
        $head_count = (int)($_GET['head_count'] ?? 1);
    ?>
    <div class="member-info"></div>
    <div class="reserve-container">
        <h1 class="reserve-title">Reservations</h1>

        <div class="row">
            <div class="column">
                <div id="Search Table">
                    <h2>Search for Time</h2>
                 
                    <form id="reservation-form" method="GET" action="availability.php"><br>
                        <div class="form-group">
                        <label for="reservation_date" style="">Select Date</label><br>
                        <input class="form-control" type="date" id="reservation_date" name="reservation_date" required>
                        </div>
                        <div class="form-group">
                        <label for="reservation_time" style="">Available Reservation Times</label>
                        <div id="availability-table">
                            <?php
                            $availableTimes = array();
                            for ($hour = 10; $hour <= 20; $hour++) {
                                for ($minute = 0; $minute < 60; $minute += 60) {
                                    $time = sprintf('%02d:%02d:00', $hour, $minute);
                                    $availableTimes[] = $time;
                                }
                            }
                            echo '<select name="reservation_time" id="reservation_time" style="width:10em;" class="form-control" >';
                            echo '<option value="" selected disabled>Select a Time</option>';
                            foreach ($availableTimes as $time) {
                                echo "<option  value='$time'>$time</option>";
                            }
                            echo '</select>';
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
                    <form id="reservation-form" method="POST" action="insertReservation.php">
                        <br>
                        <div class="form-group">
                            <label for="customer_name" style="">Customer Name</label><br>
                            <input class="form-control" type="text" id="customer_name" name="customer_name" required>
                        </div>
                        <?php
                        $defaultReservationDate = $_GET['reservation_date'] ?? date("Y-m-d");
                        $defaultReservationTime = $_GET['reservation_time'] ?? "13:00:00";
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
                                $table_id_list = $_GET['reserved_table_id'] ?? '';
                                $head_count = (int)($_GET['head_count'] ?? 1);
                                // array_filter drops the empty string explode() returns for an empty list,
                                // otherwise the query below becomes "NOT IN ()" and is a syntax error
                                $reserved_table_ids = array_map('intval', array_filter(array_map('trim', explode(',', $table_id_list)), 'strlen'));
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
        const viewDateInput = document.getElementById("reservation_date");
        const makeDateInput = document.getElementById("reservation_date");

        viewDateInput.addEventListener("change", function () {
            makeDateInput.value = this.value;
        });
    </script>
</body>

</html>
