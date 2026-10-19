<?php
// staff-panel.php - staff panel: Staff. Everyone who works here, their role and the number they log in with.
// Add a new staff member with one short form (Accounts + Staffs).
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

$sql = "SELECT s.staff_id, s.staff_name, s.role, s.account_id, a.email, a.phone_number
        FROM Staffs AS s
        LEFT JOIN Accounts AS a ON a.account_id = s.account_id
        ORDER BY s.role, s.staff_name";
$staff = mysqli_fetch_all(mysqli_query($link, $sql), MYSQLI_ASSOC);

$msg = $_GET['msg'] ?? '';
$newLogin = (int) ($_GET['login'] ?? 0);
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Staff</h2>
                <p class="pg-sub">Staff log in to this panel with their <b>login number</b> and password.</p>
            </div>
            <button type="button" class="btn btn-dark" data-open="#add-staff">+ Add staff member</button>
        </div>

<?php if ($msg == 'added') { ?>
        <div class="pg-msg">New staff member added. Their login number is <b><?= $newLogin ?></b>.</div>
<?php } elseif ($msg == 'email') { ?>
        <div class="pg-msg is-error">That email is already used by another account.</div>
<?php } elseif ($msg == 'error') { ?>
        <div class="pg-msg is-error">Please fill in every field (password at least 6 characters).</div>
<?php } ?>

        <!-- ADD STAFF: creates the login (Accounts) and the staff record (Staffs) together -->
        <form class="pg-add" id="add-staff" method="post" action="../staffCrud/addStaff.php" <?= ($msg == 'email' || $msg == 'error') ? '' : 'hidden' ?>>
            <h3>New staff member</h3>
            <div class="pg-fields">
                <div><label>Name</label><input type="text" name="staff_name" class="form-control" required></div>
                <div><label>Role</label>
                    <select name="role" class="form-control" required>
                        <option>Waiter</option><option>Chef</option><option>Manager</option>
                    </select></div>
                <div><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div><label>Phone</label><input type="text" name="phone_number" class="form-control" required></div>
                <div><label>Password</label><input type="text" name="password" class="form-control" minlength="6" required></div>
            </div>
            <button type="submit" class="btn btn-success">Save staff member</button>
        </form>

        <div class="pg-tools">
            <input type="search" class="form-control pg-search" placeholder="Search name, role or email…" data-filter="#staff-list">
            <span class="pg-count" data-count><?= count($staff) ?> people</span>
        </div>

        <div class="table-scroll"><table class="table" id="staff-list">
            <thead><tr><th>Name</th><th>Role</th><th>Login number</th><th>Email</th><th>Phone</th></tr></thead>
            <tbody>
<?php foreach ($staff as $s) { ?>
                <tr>
                    <td><?= htmlspecialchars($s['staff_name']) ?></td>
                    <td><span class="pg-tag"><?= htmlspecialchars($s['role']) ?></span></td>
                    <td><?= (int) $s['account_id'] ?></td>
                    <td><?= htmlspecialchars((string) $s['email']) ?></td>
                    <td><?= htmlspecialchars((string) $s['phone_number']) ?></td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>
    </div>
</div>

<script src="../js/staffPages.js"></script>
<?php include '../includes/dashFooter.php'; ?>
