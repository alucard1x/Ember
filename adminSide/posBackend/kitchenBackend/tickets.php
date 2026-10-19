<?php
// tickets.php - the kitchen tickets (HTML only). kitchen-panel.php loads this every 5 seconds.
// One card per table (or per bill), with every dish that is not ready yet.
require_once __DIR__ . '/../checkIfLoggedIn.php';   // staff only: if not logged in, go to the staff login page
require_once '../../config.php';

$query = "SELECT k.kitchen_id, k.table_id, k.bill_id, k.quantity, k.status, k.time_submitted, m.item_name
          FROM Kitchen AS k
          LEFT JOIN Menu AS m ON m.item_id = k.item_id
          WHERE k.time_ended IS NULL
          ORDER BY k.time_submitted, k.kitchen_id";
$result = mysqli_query($link, $query);

// group the dishes into tickets: same bill (or same table for old POS rows) = one ticket
$tickets = [];
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['bill_id'] != null) {
        $key = 'bill' . $row['bill_id'];
    } else {
        $key = 'table' . $row['table_id'];
    }
    if (!isset($tickets[$key])) {
        $tickets[$key] = [
            'table'  => $row['table_id'],
            'bill'   => $row['bill_id'],
            'oldest' => $row['time_submitted'],
            'lines'  => [],
        ];
    }
    $tickets[$key]['lines'][] = $row;
}

if (count($tickets) == 0) {
    echo '<p class="kds-empty">All quiet. New orders will appear here by themselves.</p>';
    exit;
}

foreach ($tickets as $ticket) {
    // age in seconds (PHP wrote time_submitted, so PHP also measures it)
    $age = max(0, time() - strtotime($ticket['oldest']));
    $class = 'kds-ticket';
    if ($age < 30) {
        $class .= ' is-new';      // glows for a moment
    }

    echo '<article class="' . $class . '">';
    echo '<div class="kds-ticket__top">';
    echo '<div><div class="kds-table">Table ' . (int) $ticket['table'] . '</div>';
    if ($ticket['bill'] != null) {
        echo '<div class="kds-meta">Order ' . (int) $ticket['bill'] . '</div>';
    } else {
        echo '<div class="kds-meta">POS</div>';
    }
    echo '</div>';
    echo '<div class="kds-timer" data-age="' . $age . '">0:00</div>';
    echo '</div>';

    foreach ($ticket['lines'] as $line) {
        $name = $line['item_name'];
        if ($name == null) {
            $name = 'Deleted dish';
        }
        $id = (int) $line['kitchen_id'];

        echo '<div class="kds-line is-' . htmlspecialchars($line['status']) . '">';
        echo '<span class="kds-qty">' . (int) $line['quantity'] . '×</span>';
        echo '<span class="kds-name">' . htmlspecialchars($name) . '</span>';
        if ($line['status'] == 'waiting') {
            echo '<a class="kds-btn" data-kds-action href="../posBackend/kitchenBackend/kitchen-actions.php?action=start&kitchen_id=' . $id . '">Start</a>';
        } else {
            echo '<a class="kds-btn kds-btn--ready" data-kds-action href="../posBackend/kitchenBackend/kitchen-actions.php?action=set_time_ended&kitchen_id=' . $id . '">Ready</a>';
        }
        echo '</div>';
    }
    echo '</article>';
}
