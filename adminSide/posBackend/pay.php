<?php
// pay.php - POS: "Confirm payment" on the table screen. Settles the bill by cash or card,
// gives the member their points, and goes back to the table screen with a "Paid" message.
// The staff member who takes the payment is the one who is logged in (no ID to type).
require_once __DIR__ . '/checkIfLoggedIn.php';   // staff only: if not logged in, go to the staff login page
require_once '../config.php';

$bill_id = intval($_POST['bill_id'] ?? 0);
$table_id = intval($_POST['table_id'] ?? 0);
$method = 'cash';
if (($_POST['method'] ?? '') == 'card') {
    $method = 'card';
}
$member_id = null;
if (!empty($_POST['member_id'])) {
    $member_id = (int) $_POST['member_id'];
}
$back = 'orderItem.php?table_id=' . $table_id;

// 1. the bill must exist and still be open
$stmt = $link->prepare('SELECT payment_time FROM Bills WHERE bill_id = ?');
$stmt->bind_param('i', $bill_id);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();
if (!$bill || $bill['payment_time'] != null) {
    header('Location: ' . $back);
    exit;
}

// 2. the total, worked out again from the database (never trusted from the form)
$stmt = $link->prepare('SELECT SUM(m.item_price * bi.quantity) AS subtotal
                        FROM Bill_Items AS bi JOIN Menu AS m ON m.item_id = bi.item_id
                        WHERE bi.bill_id = ?');
$stmt->bind_param('i', $bill_id);
$stmt->execute();
$subtotal = (float) $stmt->get_result()->fetch_assoc()['subtotal'];
$grandTotal = round($subtotal * 1.1, 2);         // + 10% tax

// 3. the staff member who is logged in
$stmt = $link->prepare('SELECT staff_id FROM Staffs WHERE account_id = ?');
$stmt->bind_param('i', $_SESSION['logged_account_id']);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
$staff_id = null;
if ($staff) {
    $staff_id = (int) $staff['staff_id'];
}

// 4. check the payment details
$change = 0;
if ($method == 'cash') {
    $amount = (float) ($_POST['amount'] ?? 0);
    if ($amount < $grandTotal) {
        header('Location: ' . $back . '&error=amount');
        exit;
    }
    $change = $amount - $grandTotal;
} else {
    $cardName = trim($_POST['card_name'] ?? '');
    $cardNumber = preg_replace('/\D/', '', $_POST['card_number'] ?? '');     // digits only
    $cardExpiry = trim($_POST['card_expiry'] ?? '');
    $cardCvv = trim($_POST['card_cvv'] ?? '');
    $valid = $cardName != ''
        && strlen($cardNumber) >= 15 && strlen($cardNumber) <= 16      // card_payments.card_number holds 16 digits
        && preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $cardExpiry)
        && preg_match('/^[0-9]{3}$/', $cardCvv);
    if (!$valid) {
        header('Location: ' . $back . '&error=card');
        exit;
    }
}

// 5. save everything in one transaction
mysqli_begin_transaction($link);
$ok = true;
$card_id = null;

if ($method == 'card') {
    $stmt = $link->prepare('INSERT INTO card_payments (account_holder_name, card_number, expiry_date, security_code) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $cardName, $cardNumber, $cardExpiry, $cardCvv);
    $ok = $stmt->execute();
    $card_id = $link->insert_id;
}

if ($ok) {
    $now = date('Y-m-d H:i:s');
    $stmt = $link->prepare('UPDATE Bills SET payment_method = ?, payment_time = ?, staff_id = ?, member_id = ?, card_id = ? WHERE bill_id = ?');
    $stmt->bind_param('ssiiii', $method, $now, $staff_id, $member_id, $card_id, $bill_id);
    $ok = $stmt->execute();
}

// the member earns 1 point for every rupee of the total
if ($ok && $member_id != null) {
    $points = (int) $grandTotal;
    $stmt = $link->prepare('UPDATE Memberships SET points = points + ? WHERE member_id = ?');
    $stmt->bind_param('ii', $points, $member_id);
    $ok = $stmt->execute();
}

if ($ok) {
    mysqli_commit($link);
    header('Location: ' . $back . '&paid=' . $bill_id . '&change=' . round($change, 2));
} else {
    mysqli_rollback($link);
    header('Location: ' . $back . '&error=card');
}
exit;
