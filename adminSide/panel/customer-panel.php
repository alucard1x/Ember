<?php
// customer-panel.php - staff panel: Members. See every member with their contact details and points,
// search them as you type, and add a new member with one short form (Accounts + Memberships).
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

$sql = "SELECT m.member_id, m.member_name, m.points, a.email, a.phone_number, a.register_date
        FROM Memberships AS m
        LEFT JOIN Accounts AS a ON a.account_id = m.account_id
        ORDER BY m.member_name";
$members = mysqli_fetch_all(mysqli_query($link, $sql), MYSQLI_ASSOC);

$msg = $_GET['msg'] ?? '';
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Members</h2>
                <p class="pg-sub">Loyalty members earn 1 point for every rupee they spend. 1000 points = VIP.</p>
            </div>
            <button type="button" class="btn btn-dark" data-open="#add-member">+ Add member</button>
        </div>

<?php if ($msg == 'added') { ?>
        <div class="pg-msg">New member added.</div>
<?php } elseif ($msg == 'email') { ?>
        <div class="pg-msg is-error">That email is already used by another account.</div>
<?php } elseif ($msg == 'error') { ?>
        <div class="pg-msg is-error">Please fill in every field (password at least 6 characters).</div>
<?php } ?>

        <!-- ADD MEMBER: creates the login (Accounts) and the membership (Memberships) together -->
        <form class="pg-add" id="add-member" method="post" action="../customerCrud/addMember.php" <?= ($msg == 'email' || $msg == 'error') ? '' : 'hidden' ?>>
            <h3>New member</h3>
            <div class="pg-fields">
                <div><label>Name</label><input type="text" name="member_name" class="form-control" required></div>
                <div><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div><label>Phone</label><input type="text" name="phone_number" class="form-control" required></div>
                <div><label>Password (for the website)</label><input type="text" name="password" class="form-control" minlength="6" required></div>
            </div>
            <button type="submit" class="btn btn-success">Save member</button>
        </form>

        <div class="pg-tools">
            <input type="search" class="form-control pg-search" placeholder="Search name, email or phone…" data-filter="#members-list">
            <span class="pg-count" data-count><?= count($members) ?> members</span>
        </div>

        <div class="table-scroll"><table class="table" id="members-list">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th class="pg-num">Points</th><th>Level</th><th>Member since</th></tr></thead>
            <tbody>
<?php foreach ($members as $m) { ?>
                <tr>
                    <td><?= (int) $m['member_id'] ?></td>
                    <td><?= htmlspecialchars($m['member_name']) ?></td>
                    <td><?= htmlspecialchars((string) $m['email']) ?></td>
                    <td><?= htmlspecialchars((string) $m['phone_number']) ?></td>
                    <td class="pg-num"><?= number_format((int) $m['points']) ?></td>
                    <td><?php if ($m['points'] >= 1000) { ?><span class="pg-tag is-gold">VIP</span><?php } else { ?><span class="pg-tag">Member</span><?php } ?></td>
                    <td><?= $m['register_date'] ? date('j M Y', strtotime($m['register_date'])) : '' ?></td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>
    </div>
</div>

<script src="../js/staffPages.js"></script>
<?php include '../includes/dashFooter.php'; ?>
