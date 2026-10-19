<?php
// addStaff.php - saves a new staff member from the Staff page.
// Two inserts in ONE transaction: Accounts (login details) then Staffs (name and role).
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only
require_once '../config.php';

$name = trim($_POST['staff_name'] ?? '');
$role = trim($_POST['role'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone_number'] ?? '');
$password = trim($_POST['password'] ?? '');
$back = '../panel/staff-panel.php';

if ($name == '' || $role == '' || $phone == '' || strlen($password) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
$account_id = $link->insert_id;     // this is the number they log in with

if ($ok) {
    $stmt = $link->prepare('INSERT INTO Staffs (staff_name, role, account_id) VALUES (?, ?, ?)');
    $stmt->bind_param('ssi', $name, $role, $account_id);
    $ok = $stmt->execute();
}

if ($ok) {
    mysqli_commit($link);
    header('Location: ' . $back . '?msg=added&login=' . $account_id);
} else {
    mysqli_rollback($link);
    header('Location: ' . $back . '?msg=error');
}
exit;
