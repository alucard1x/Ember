<?php
// posTable.php - POS: the board of all tables (free / busy)
session_start(); // Ensure session is started
require_once 'checkIfLoggedIn.php';
?>
<?php
include '../includes/dashHeader.php';
require_once '../config.php'; // Include your database configuration
?>

<link href="../css/pos.css" rel="stylesheet" />

<div class="wrapper">
    <div class="container-fluid pt-5">
        <h2>Point of Sale</h2>
        <p class="pg-sub" style="margin-bottom: 1.2rem;">Tap a table to add dishes and take payment.</p>

        <div class="table-board">
            <?php
            // Fetch all tables from the database
            $query = "SELECT * FROM Restaurant_Tables ORDER BY table_id;";
            $result = mysqli_query($link, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $table_id = $row['table_id'];
                    $capacity = $row['capacity'];

                    $sqlBill = "SELECT bill_id FROM Bills WHERE table_id = $table_id ORDER BY bill_time DESC LIMIT 1";
                    $result1 = $link->query($sqlBill);
                    $latestBillData = $result1->fetch_assoc();

                    // Check if the table is reserved for the selected time
                    date_default_timezone_set('Asia/Singapore'); // Set the time zone to Singapore

                    $selectedDate = date("Y-m-d"); // Get the current date, you can change this to your selected date
                    $endTime = date("H:i:s"); // Get the current time, you can change this to your selected time

                    // Calculate the end time of the 20-minute range
                    $startTime = date("H:i:s", strtotime($endTime) - (20 * 60));
                    // Check if there's a reservation within the 20-minute range
                    $reservationQuery = "SELECT * FROM Reservations WHERE table_id = $table_id AND reservation_date = '$selectedDate' AND reservation_time BETWEEN '$startTime' AND '$endTime'";
                    $reservationResult = mysqli_query($link, $reservationQuery);

                    // Work out the table's state. The colours now live in
                    // css/ember-admin.css as .is-free / .is-busy / .is-held /
                    // .is-idle, so this file only decides which one applies.
                    if ($latestBillData) {
                        $latestBillID = $latestBillData['bill_id'];

                        $sqlBillItems = "SELECT * FROM Bill_Items WHERE bill_id = $latestBillID";
                        $result2 = $link->query($sqlBillItems);
                        // Bill has associated bill items => the table is in use.
                        $billItemState = ($result2 && mysqli_num_rows($result2) > 0) ? 'busy' : 'free';

                        $paymentTimeQuery = "SELECT payment_time FROM Bills WHERE bill_id = $latestBillID";
                        $paymentTimeResult = $link->query($paymentTimeQuery);
                        $hasPaymentTime = false;

                        if ($paymentTimeResult && $paymentTimeResult->num_rows > 0) {
                            $paymentTimeRow = $paymentTimeResult->fetch_assoc();
                            if (!empty($paymentTimeRow['payment_time'])) {
                                $hasPaymentTime = true;
                            }
                        }

                        // Settled bill => the table is free again.
                        $state = $hasPaymentTime ? 'free' : $billItemState;
                    } else {
                        $latestBillID = null;
                        $state = 'idle'; // No bill has ever been opened on this table
                    }

                    // A reservation in the current window outranks the bill state.
                    if ($reservationResult && mysqli_num_rows($reservationResult) > 0) {
                        $state = 'held';
                    }

                    $stateLabel = array(
                        'free' => 'Available',
                        'busy' => 'Occupied',
                        'held' => 'Reserved',
                        'idle' => 'Available',
                    );
                    ?>
                    <a class="table-tile is-<?= $state ?>"
                       href="orderItem.php?table_id=<?= $table_id ?>">
                        <span class="tile-label">Table</span>
                        <span class="tile-id"><?= $table_id ?></span>
                        <span class="tile-capacity">Seats <?= $capacity ?></span>
                        <span class="tile-status"><?= $stateLabel[$state] ?></span>
                    </a>
                    <?php
                }
            } else {
                echo '<div class="alert alert-danger">Error fetching tables: ' . mysqli_error($link) . '</div>';
            }
            ?>
        </div>

        <div class="board-legend">
            <span class="legend-item" title="Tables that are free">
                <span class="legend-swatch is-free"></span> Available
            </span>
            <span class="legend-item" title="Tables that are in use">
                <span class="legend-swatch is-busy"></span> Occupied
            </span>
            <span class="legend-item" title="Tables that are reserved">
                <span class="legend-swatch is-held"></span> Reserved
            </span>
        </div>
    </div>
</div>

<?php include '../includes/dashFooter.php' ?>
