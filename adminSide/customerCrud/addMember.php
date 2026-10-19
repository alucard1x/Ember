<?php
// addMember.php - saves a new member from the Members page.
// Two inserts in ONE transaction: Accounts (login details) then Memberships (name, 0 points).
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only
require_once '../config.php';

$name = trim($_POST['member_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone_number'] ?? '');
$password = trim($_POST['password'] ?? '');
$back = '../panel/customer-panel.php';

if ($name == '' || $phone == '' || strlen($password) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $back . '?msg=error');
    exit;
}

// the email must not be used yet
$stmt = $link->prepare('SELECT account_id FROM Accounts WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    header('Location: ' . $back . '?msg=email');
    exit;
}

mysqli_begin_transaction($link);
$today = date('Y-m-d');

$stmt = $link->prepare('INSERT INTO Accounts (email, register_date, phone_number, password) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $email, $today, $phone, $password);
$ok = $stmt->execute();
$account_id = $link->insert_id;

if ($ok) {
    $stmt = $link->prepare('INSERT INTO Memberships (member_name, points, account_id) VALUES (?, 0, ?)');
    $stmt->bind_param('si', $name, $account_id);
    $ok = $stmt->execute();
}

if ($ok) {
    mysqli_commit($link);
    header('Location: ' . $back . '?msg=added');
} else {
    mysqli_rollback($link);
    header('Location: ' . $back . '?msg=error');
}
exit;
