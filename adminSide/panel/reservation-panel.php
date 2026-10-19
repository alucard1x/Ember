<?php
// reservation-panel.php - staff panel: Reservations. Upcoming bookings first.
// "New reservation" is one short form: pick date, time and people, and the free tables appear by themselves.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

$show = $_GET['show'] ?? 'upcoming';
$where = "reservation_date >= CURDATE()";
$order = "reservation_date, reservation_time";
if ($show == 'past') {
    $where = "reservation_date < CURDATE()";
    $order = "reservation_date DESC, reservation_time DESC";
} elseif ($show == 'all') {
    $where = "1 = 1";
    $order = "reservation_date DESC, reservation_time DESC";
} else {
    $show = 'upcoming';
}
$reservations = mysqli_fetch_all(mysqli_query($link, "SELECT * FROM Reservations WHERE $where ORDER BY $order"), MYSQLI_ASSOC);

$msg = $_GET['msg'] ?? '';
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Reservations</h2>
                <p class="pg-sub">Bookings from the website and from staff. A table can only be booked once per time slot.</p>
            </div>
            <button type="button" class="btn btn-dark" data-open="#add-booking">+ New reservation</button>
        </div>

<?php if ($msg == 'added') { ?>
        <div class="pg-msg">Reservation saved.</div>
<?php } elseif ($msg == 'taken') { ?>
        <div class="pg-msg is-error">That table was just booked for this time. Please pick another table.</div>
<?php } elseif ($msg == 'error') { ?>
        <div class="pg-msg is-error">Please fill in the name, date, time and a table.</div>
<?php } ?>

        <!-- NEW RESERVATION -->
        <form class="pg-add" id="add-booking" method="post" action="../reservationsCrud/addReservation.php" <?= ($msg == 'taken' || $msg == 'error') ? '' : 'hidden' ?>>
            <h3>New reservation</h3>
            <div class="pg-fields">
                <div><label>Guest name</label><input type="text" name="customer_name" class="form-control" required></div>
                <div><label>Date</label><input type="date" name="reservation_date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required data-slot></div>
                <div><label>Time</label>
                    <select name="reservation_time" class="form-control" required data-slot>
<?php for ($hour = 10; $hour <= 20; $hour++) { $t = sprintf('%02d:00:00', $hour); ?>
                        <option value="<?= $t ?>"<?= $hour == 19 ? ' selected' : '' ?>><?= sprintf('%02d:00', $hour) ?></option>
<?php } ?>
                    </select></div>
                <div><label>People</label><input type="number" name="head_count" class="form-control" min="1" max="20" value="2" required data-slot></div>
                <div><label>Free table</label>
                    <select name="table_id" class="form-control" required data-tables>
                        <option value="">Loading…</option>
                    </select></div>
                <div><label>Special request (optional)</label><input type="text" name="special_request" class="form-control"></div>
            </div>
            <button type="submit" class="btn btn-success">Save reservation</button>
        </form>

        <div class="pg-tools">
            <a class="pg-chip<?= $show == 'upcoming' ? ' is-on' : '' ?>" href="?show=upcoming">Upcoming</a>
            <a class="pg-chip<?= $show == 'past' ? ' is-on' : '' ?>" href="?show=past">Past</a>
            <a class="pg-chip<?= $show == 'all' ? ' is-on' : '' ?>" href="?show=all">All</a>
            <input type="search" class="form-control pg-search" placeholder="Search name or table…" data-filter="#bookings-list">
            <span class="pg-count" data-count><?= count($reservations) ?> reservations</span>
        </div>

        <div class="table-scroll"><table class="table" id="bookings-list">
            <thead><tr><th>Date</th><th>Time</th><th>Guest</th><th>People</th><th>Table</th><th>Request</th><th></th></tr></thead>
            <tbody>
<?php if (count($reservations) == 0) { ?>
                <tr><td colspan="7" class="text-muted">No reservations here.</td></tr>
<?php } ?>
<?php foreach ($reservations as $r) { ?>
                <tr>
                    <td><?= date('D j M Y', strtotime($r['reservation_date'])) ?></td>
                    <td><?= substr($r['reservation_time'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td><?= (int) $r['head_count'] ?></td>
                    <td><?= (int) $r['table_id'] ?></td>
                    <td><?= htmlspecialchars((string) $r['special_request']) ?></td>
                    <td class="pg-actions">
                        <a href="../reservationsCrud/reservationReceipt.php?reservation_id=<?= (int) $r['reservation_id'] ?>">Receipt</a>
                        <a href="../reservationsCrud/deleteReservationVerify.php?id=<?= (int) $r['reservation_id'] ?>">Cancel</a>
                    </td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>
    </div>
</div>

<script src="../js/staffPages.js"></script>
<script>
// when date, time or people change, ask freeTables.php which tables are still free
var bookingForm = document.getElementById('add-booking');
var tableSelect = bookingForm.querySelector('[data-tables]');

function loadFreeTables() {
    var date = bookingForm.reservation_date.value;
    var time = bookingForm.reservation_time.value;
    var people = bookingForm.head_count.value;
    fetch('../reservationsCrud/freeTables.php?date=' + date + '&time=' + time + '&people=' + people, { cache: 'no-store' })
        .then(function (response) { return response.json(); })
        .then(function (tables) {
            if (tables.length == 0) {
                tableSelect.innerHTML = '<option value="">No free table for this time</option>';
                return;
            }
            var html = '';
            for (var i = 0; i < tables.length; i++) {
                html += '<option value="' + tables[i].table_id + '">Table ' + tables[i].table_id + ' (' + tables[i].capacity + ' seats)</option>';
            }
            tableSelect.innerHTML = html;
        });
}

var slotInputs = bookingForm.querySelectorAll('[data-slot]');
for (var i = 0; i < slotInputs.length; i++) {
    slotInputs[i].addEventListener('change', loadFreeTables);
}
loadFreeTables();
</script>

<?php include '../includes/dashFooter.php'; ?>
