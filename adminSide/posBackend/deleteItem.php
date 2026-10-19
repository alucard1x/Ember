<?php
// deleteItem.php - POS: removes a dish from a table's open bill (and from the kitchen, if not cooked yet).
//   deleteItem.php?table_id=5&bill_item_id=12&one=1   → one portion less
//   deleteItem.php?table_id=5&bill_item_id=12         → remove the whole line
require_once __DIR__ . '/checkIfLoggedIn.php';   // staff only: if not logged in, go to the staff login page
require_once '../config.php';

$table_id = intval($_GET['table_id'] ?? 0);
$bill_item_id = intval($_GET['bill_item_id'] ?? 0);
$onlyOne = isset($_GET['one']);

// the line, but only if its bill is still open (paid bills can't be changed)
$stmt = $link->prepare('SELECT bi.bill_id, bi.item_id, bi.quantity
                        FROM Bill_Items AS bi JOIN Bills AS b ON b.bill_id = bi.bill_id
                        WHERE bi.bill_item_id = ? AND b.payment_time IS NULL');
$stmt->bind_param('i', $bill_item_id);
$stmt->execute();
$line = $stmt->get_result()->fetch_assoc();

if ($line) {
    $bill_id = (int) $line['bill_id'];
    $item_id = $line['item_id'];

    if ($onlyOne && $line['quantity'] > 1) {
        // one less on the bill
        $stmt = $link->prepare('UPDATE Bill_Items SET quantity = quantity - 1 WHERE bill_item_id = ?');
        $stmt->bind_param('i', $bill_item_id);
        $stmt->execute();

        // and one less in the kitchen (the newest portion that is not ready yet)
        $stmt = $link->prepare('SELECT kitchen_id, quantity FROM Kitchen
                                WHERE bill_id = ? AND item_id = ? AND time_ended IS NULL
                                ORDER BY kitchen_id DESC LIMIT 1');
        $stmt->bind_param('is', $bill_id, $item_id);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        if ($ticket) {
            if ($ticket['quantity'] > 1) {
                $stmt = $link->prepare('UPDATE Kitchen SET quantity = quantity - 1 WHERE kitchen_id = ?');
            } else {
                $stmt = $link->prepare('DELETE FROM Kitchen WHERE kitchen_id = ?');
            }
            $stmt->bind_param('i', $ticket['kitchen_id']);
            $stmt->execute();
        }
    } else {
        // remove the whole line, and every portion of it the kitchen has not finished
        $stmt = $link->prepare('DELETE FROM Bill_Items WHERE bill_item_id = ?');
        $stmt->bind_param('i', $bill_item_id);
        $stmt->execute();

        $stmt = $link->prepare('DELETE FROM Kitchen WHERE bill_id = ? AND item_id = ? AND time_ended IS NULL');
        $stmt->bind_param('is', $bill_id, $item_id);
        $stmt->execute();
    }
}

header('Location: orderItem.php?table_id=' . $table_id);
exit;
