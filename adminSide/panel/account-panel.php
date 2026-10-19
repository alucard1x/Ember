<?php
// account-panel.php - staff panel: View All Accounts. Every login (Accounts table) with who it belongs to:
// a staff member, a member, or nobody yet. New accounts are added from the Members or Staff page.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

// LEFT JOIN to both tables: an account can belong to a staff member or to a member
$sql = "SELECT a.account_id, a.email, a.phone_number, a.register_date,
               s.staff_name, s.role, m.member_name
        FROM Accounts AS a
        LEFT JOIN Staffs AS s ON s.account_id = a.account_id
        LEFT JOIN Memberships AS m ON m.account_id = a.account_id
        ORDER BY a.account_id";
$accounts = mysqli_fetch_all(mysqli_query($link, $sql), MYSQLI_ASSOC);
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>All Accounts</h2>
                <p class="pg-sub">Every login in the database. To add one, use the <a href="customer-panel.php">Members</a> or <a href="staff-panel.php">Staff</a> page.</p>
            </div>
        </div>

        <div class="pg-tools">
            <input type="search" class="form-control pg-search" placeholder="Search name, email or type…" data-filter="#accounts-list">
            <span class="pg-count" data-count><?= count($accounts) ?> accounts</span>
        </div>

        <div class="table-scroll"><table class="table" id="accounts-list">
            <thead><tr><th>Login number</th><th>Belongs to</th><th>Type</th><th>Email</th><th>Phone</th><th>Created</th></tr></thead>
            <tbody>
<?php foreach ($accounts as $a) {
        if ($a['staff_name'] != null) {
            $who = $a['staff_name'];
            $type = '<span class="pg-tag is-gold">Staff &middot; ' . htmlspecialchars($a['role']) . '</span>';
        } elseif ($a['member_name'] != null) {
            $who = $a['member_name'];
            $type = '<span class="pg-tag is-green">Member</span>';
        } else {
            $who = '—';
            $type = '<span class="pg-tag">Not used</span>';
        } ?>
                <tr>
                    <td><?= (int) $a['account_id'] ?></td>
                    <td><?= htmlspecialchars($who) ?></td>
                    <td><?= $type ?></td>
                    <td><?= htmlspecialchars((string) $a['email']) ?></td>
                    <td><?= htmlspecialchars((string) $a['phone_number']) ?></td>
                    <td><?= $a['register_date'] ? date('j M Y', strtotime($a['register_date'])) : '' ?></td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>
    </div>
</div>

<script src="../js/staffPages.js"></script>
<?php include '../includes/dashFooter.php'; ?>
