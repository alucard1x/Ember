<?php
// history-panel.php - staff panel: the "Price Time Machine".
// The Menu table is SYSTEM-VERSIONED: MariaDB keeps every old version of every row by itself,
// so we can ask "what did the menu look like on that day?" with FOR SYSTEM_TIME AS OF.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

// the oldest version MariaDB has kept = the start of the slider
$first = mysqli_fetch_assoc(mysqli_query($link, 'SELECT DATE(MIN(ROW_START)) AS first_day FROM Menu FOR SYSTEM_TIME ALL'));
$firstDay = $first['first_day'];
$totalDays = (int) floor((strtotime(date('Y-m-d')) - strtotime($firstDay)) / 86400);

// the slider goes from 0 (the first day) to $totalDays (today)
$position = (int) round($totalDays / 4);
if (isset($_GET['pos'])) {
    $position = max(0, min($totalDays, (int) $_GET['pos']));
}
$daysAgo = $totalDays - $position;
$theDay = date('Y-m-d', strtotime($firstDay . ' +' . $position . ' days'));
$moment = $theDay . ' 23:59:59';

// 1. the whole menu on that day, next to today's price
$sql = "SELECT then_menu.item_id, then_menu.item_name, then_menu.item_type,
               then_menu.item_price AS then_price, now_menu.item_price AS now_price
        FROM Menu FOR SYSTEM_TIME AS OF TIMESTAMP ? AS then_menu
        JOIN Menu AS now_menu ON now_menu.item_id = then_menu.item_id
        ORDER BY then_menu.item_type, then_menu.item_name";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 's', $moment);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$changed = 0;
foreach ($rows as $row) {
    if ($row['then_price'] != $row['now_price']) {
        $changed++;
    }
}

// 2. the full price history of one dish (every version MariaDB kept)
$dish = 'TK1';
if (isset($_GET['dish'])) {
    $dish = $_GET['dish'];
}
$stmt = mysqli_prepare($link, "SELECT item_name, item_price, ROW_START AS valid_from, ROW_END AS valid_to
                               FROM Menu FOR SYSTEM_TIME ALL
                               WHERE item_id = ?
                               ORDER BY ROW_START");
mysqli_stmt_bind_param($stmt, 's', $dish);
mysqli_stmt_execute($stmt);
$versions = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$allDishes = mysqli_fetch_all(mysqli_query($link, 'SELECT item_id, item_name FROM Menu ORDER BY item_name'), MYSQLI_ASSOC);
?>

<div class="wrapper">
    <div class="container-fluid">
        <h2>Price Time Machine</h2>
        <p class="tm-sub">MariaDB keeps every old version of the Menu table (system versioning). Move the slider to travel back in time.</p>

        <!-- the slider: changing it reloads the page with ?days= -->
        <form class="tm-slider" method="get">
            <input type="hidden" name="dish" value="<?= htmlspecialchars($dish) ?>">
            <span class="tm-edge"><?= $totalDays ?> days ago</span>
            <input type="range" name="pos" min="0" max="<?= $totalDays ?>" value="<?= $position ?>"
                   oninput="document.getElementById('tm-day').textContent = (<?= $totalDays ?> - this.value) + ' days ago'"
                   onchange="this.form.submit()">
            <span class="tm-edge">today</span>
        </form>

        <div class="tm-now">
            <div><span class="tm-label">Menu as it was on</span><span class="tm-date"><?= date('j M Y', strtotime($theDay)) ?></span>
                 <span class="tm-label" id="tm-day"><?= $daysAgo ?> days ago</span></div>
            <div><span class="tm-big"><?= $changed ?></span><span class="tm-label">of <?= count($rows) ?> prices are different from today</span></div>
        </div>

        <pre class="tm-sql">SELECT * FROM Menu FOR SYSTEM_TIME AS OF TIMESTAMP '<?= htmlspecialchars($moment) ?>';</pre>

        <div class="table-scroll"><table class="table table-bordered">
            <thead><tr><th>Dish</th><th>Course</th><th>Price then</th><th>Price today</th><th>Change</th></tr></thead>
            <tbody>
<?php foreach ($rows as $row) {
        $then = (float) $row['then_price'];
        $now = (float) $row['now_price'];
        $change = '';
        if ($then != $now && $then > 0) {
            $percent = round(($now - $then) / $then * 100);
            $change = '<span class="tm-up">+' . $percent . '%</span>';
        } ?>
                <tr>
                    <td><a href="?pos=<?= $position ?>&dish=<?= urlencode($row['item_id']) ?>#versions"><?= htmlspecialchars($row['item_name']) ?></a></td>
                    <td><?= htmlspecialchars($row['item_type']) ?></td>
                    <td>&#8377;<?= number_format($then) ?></td>
                    <td>&#8377;<?= number_format($now) ?></td>
                    <td><?= $change ?></td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>

        <h3 id="versions" class="tm-h3">Every version of one dish</h3>
        <form method="get" class="tm-dish">
            <input type="hidden" name="pos" value="<?= $position ?>">
            <select name="dish" class="form-control" onchange="this.form.submit()">
<?php foreach ($allDishes as $d) { ?>
                <option value="<?= htmlspecialchars($d['item_id']) ?>"<?= $d['item_id'] == $dish ? ' selected' : '' ?>><?= htmlspecialchars($d['item_name']) ?></option>
<?php } ?>
            </select>
        </form>
        <pre class="tm-sql">SELECT item_price, ROW_START, ROW_END FROM Menu FOR SYSTEM_TIME ALL WHERE item_id = '<?= htmlspecialchars($dish) ?>';</pre>
        <div class="tm-timeline">
<?php foreach ($versions as $v) {
        $isCurrent = substr($v['valid_to'], 0, 4) == '2038'; ?>
            <div class="tm-version<?= $isCurrent ? ' is-current' : '' ?>">
                <span class="tm-price">&#8377;<?= number_format((float) $v['item_price']) ?></span>
                <span class="tm-range"><?= date('j M Y', strtotime($v['valid_from'])) ?> &rarr; <?= $isCurrent ? 'now' : date('j M Y', strtotime($v['valid_to'])) ?></span>
            </div>
<?php } ?>
        </div>
    </div>
</div>

<style>
    .tm-sub { color: var(--text-dim); margin: 0.4rem 0 1.4rem; font-size: 0.85rem; }
    .tm-slider { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.4rem; }
    .tm-slider input[type=range] { flex: 1; accent-color: #b1462f; }
    .tm-edge { font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--text-faint); white-space: nowrap; }
    .tm-now { display: flex; flex-wrap: wrap; gap: 3rem; margin-bottom: 1rem; }
    .tm-now > div { display: flex; flex-direction: column; }
    .tm-label { font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--text-faint); }
    .tm-date { font-family: var(--brand-font); font-size: 1.6rem; letter-spacing: 0.06em; color: var(--text); }
    .tm-big { font-family: var(--brand-font); font-size: 1.6rem; color: var(--brand-accent); }
    .tm-sql { background: rgba(0,0,0,0.35); border: 1px solid var(--line); color: #e0784f; padding: 0.7rem 1rem; font-size: 0.8rem; white-space: pre-wrap; }
    .tm-up { color: #e0784f; }
    .tm-h3 { margin-top: 2.2rem; }
    .tm-dish { max-width: 22rem; margin-bottom: 1rem; }
    .tm-timeline { display: flex; flex-wrap: wrap; gap: 0.8rem; margin-bottom: 2rem; }
    .tm-version { border: 1px solid var(--line); padding: 0.8rem 1.1rem; background: var(--ink-700); }
    .tm-version.is-current { border-color: var(--brand-accent); }
    .tm-price { display: block; font-family: var(--brand-font); font-size: 1.3rem; color: var(--text); }
    .tm-range { font-size: 0.75rem; color: var(--text-dim); }
</style>

<?php include '../includes/dashFooter.php'; ?>
