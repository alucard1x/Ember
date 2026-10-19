<?php
// profiles-panel.php - staff panel: Member Profiles. Pick a member from the list to see
// how often they come, how much they spend, and their favourite dishes.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

$members = mysqli_fetch_all(mysqli_query($link, 'SELECT member_id, member_name FROM Memberships ORDER BY member_name'), MYSQLI_ASSOC);

$memberId = (int) ($_GET['member_id'] ?? 0);
if ($memberId == 0 && count($members) > 0) {
    $memberId = (int) $members[0]['member_id'];
}

// the member and their account
$stmt = $link->prepare('SELECT m.member_name, m.points, a.email, a.phone_number, a.register_date
                        FROM Memberships AS m LEFT JOIN Accounts AS a ON a.account_id = m.account_id
                        WHERE m.member_id = ?');
$stmt->bind_param('i', $memberId);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

// visits, money spent and last visit (paid bills)
$stmt = $link->prepare('SELECT COUNT(DISTINCT b.bill_id) AS visits,
                               COALESCE(SUM(bi.quantity * m.item_price), 0) * 1.1 AS spent,
                               MAX(b.bill_time) AS last_visit
                        FROM Bills AS b
                        LEFT JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
                        LEFT JOIN Menu AS m ON m.item_id = bi.item_id
                        WHERE b.member_id = ? AND b.payment_time IS NOT NULL');
$stmt->bind_param('i', $memberId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// favourite dishes
$stmt = $link->prepare('SELECT m.item_name, SUM(bi.quantity) AS times
                        FROM Bill_Items AS bi
                        JOIN Bills AS b ON b.bill_id = bi.bill_id
                        JOIN Menu AS m ON m.item_id = bi.item_id
                        WHERE b.member_id = ?
                        GROUP BY m.item_id, m.item_name
                        ORDER BY times DESC
                        LIMIT 8');
$stmt->bind_param('i', $memberId);
$stmt->execute();
$favourites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$most = 1;
foreach ($favourites as $f) {
    $most = max($most, (int) $f['times']);
}
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Member Profiles</h2>
                <p class="pg-sub">Choose a member to see their visits and favourite dishes.</p>
            </div>
        </div>

        <form method="get" class="pg-tools">
            <select name="member_id" class="form-control pg-search" onchange="this.form.submit()">
<?php foreach ($members as $m) { ?>
                <option value="<?= (int) $m['member_id'] ?>"<?= $m['member_id'] == $memberId ? ' selected' : '' ?>><?= htmlspecialchars($m['member_name']) ?> &middot; #<?= (int) $m['member_id'] ?></option>
<?php } ?>
            </select>
        </form>

<?php if (!$member) { ?>
        <p class="text-muted">No member selected.</p>
<?php } else { ?>
        <div class="rv-cards">
            <div class="rv-card"><span class="pg-label">Member</span><span class="rv-money"><?= htmlspecialchars($member['member_name']) ?></span>
                <span class="rv-small"><?= htmlspecialchars((string) $member['email']) ?> &middot; <?= htmlspecialchars((string) $member['phone_number']) ?></span></div>
            <div class="rv-card"><span class="pg-label">Points</span><span class="rv-money"><?= number_format((int) $member['points']) ?></span>
                <span class="rv-small"><?= $member['points'] >= 1000 ? 'VIP member' : (1000 - $member['points']) . ' points to VIP' ?></span></div>
            <div class="rv-card"><span class="pg-label">Visits</span><span class="rv-money"><?= (int) $stats['visits'] ?></span>
                <span class="rv-small">Last visit: <?= $stats['last_visit'] ? date('j M Y', strtotime($stats['last_visit'])) : '—' ?></span></div>
            <div class="rv-card"><span class="pg-label">Total spent</span><span class="rv-money">&#8377;<?= number_format((float) $stats['spent']) ?></span>
                <span class="rv-small">including tax</span></div>
        </div>

        <div class="rv-box">
            <h3>Favourite dishes</h3>
<?php   if (count($favourites) == 0) { ?>
            <p class="text-muted">This member has not ordered yet.</p>
<?php   } ?>
<?php   foreach ($favourites as $f) { ?>
            <div class="rv-method">
                <span class="rv-method__name"><?= htmlspecialchars($f['item_name']) ?></span>
                <div class="rp-bar"><span style="width: <?= round($f['times'] / $most * 100) ?>%"></span></div>
                <span class="pg-num"><?= (int) $f['times'] ?> times</span>
            </div>
<?php   } ?>
        </div>
<?php } ?>
    </div>
</div>

<style>
    .rv-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.3rem; }
    .rv-card, .rv-box { background: var(--ink-700); border: 1px solid var(--line); padding: 1.1rem 1.2rem; }
    .rv-box h3 { font-size: 1.05rem; margin: 0 0 1rem; }
    .rv-money { display: block; font-family: var(--brand-font); font-size: 1.5rem; color: var(--text); margin-top: 0.2rem; }
    .rv-small { font-size: 0.8rem; color: var(--text-faint); }
    .rv-method { display: grid; grid-template-columns: minmax(10rem, 16rem) 1fr 6rem; align-items: center; gap: 1rem; margin-bottom: 0.7rem; }
    .rp-bar { height: 8px; background: rgba(255, 255, 255, 0.06); }
    .rp-bar span { display: block; height: 100%; background: linear-gradient(90deg, #b1462f, #e0784f); }
</style>

<?php include '../includes/dashFooter.php'; ?>
