<?php
// insights-panel.php - staff panel: smart insights with SQL WINDOW FUNCTIONS.
// A window function looks at a group of rows ("the window") without squashing them into one row:
//   RANK() OVER (...)   → position inside each group
//   LAG() OVER (...)    → the value from the previous row (last week)
//   SUM() OVER (...)    → a running total
//   NTILE(4) OVER (...) → splits rows into 4 equal groups (quartiles)
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

// runs a query and returns all rows
function getRows($link, $sql) {
    $result = mysqli_query($link, $sql);
    if (!$result) {
        return [];
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// 1. the best-selling dish of every course (last 90 days) — RANK() OVER (PARTITION BY ...)
$sqlBest = "SELECT item_type, item_name, sold
FROM (
    SELECT m.item_type, m.item_name, SUM(bi.quantity) AS sold,
           RANK() OVER (PARTITION BY m.item_type ORDER BY SUM(bi.quantity) DESC) AS sales_rank
    FROM Bill_Items AS bi
    JOIN Menu  AS m ON m.item_id = bi.item_id
    JOIN Bills AS b ON b.bill_id = bi.bill_id
    WHERE b.bill_time >= NOW() - INTERVAL 90 DAY
    GROUP BY m.item_id, m.item_type, m.item_name
) AS ranked
WHERE sales_rank = 1
ORDER BY sold DESC";
$best = getRows($link, $sqlBest);

// 2. revenue per week and the change from last week — LAG() OVER (ORDER BY ...)
$sqlWeeks = "SELECT week_start, revenue,
       LAG(revenue) OVER (ORDER BY week_start) AS last_week
FROM (
    SELECT DATE(b.bill_time - INTERVAL WEEKDAY(b.bill_time) DAY) AS week_start,
           SUM(m.item_price * bi.quantity) AS revenue
    FROM Bills AS b
    JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
    JOIN Menu AS m ON m.item_id = bi.item_id
    WHERE b.bill_time >= CURDATE() - INTERVAL 13 WEEK
    GROUP BY week_start
) AS weeks
ORDER BY week_start";
$weeks = getRows($link, $sqlWeeks);

// 3. revenue per day for the last 30 days, with a running total — SUM() OVER (ORDER BY ...)
$sqlDays = "SELECT day, revenue,
       SUM(revenue) OVER (ORDER BY day) AS running_total
FROM (
    SELECT DATE(b.bill_time) AS day, SUM(m.item_price * bi.quantity) AS revenue
    FROM Bills AS b
    JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
    JOIN Menu AS m ON m.item_id = bi.item_id
    WHERE b.bill_time >= CURDATE() - INTERVAL 30 DAY
    GROUP BY day
) AS days
ORDER BY day";
$days = getRows($link, $sqlDays);

// 4. members split into 4 groups by how much they spent — NTILE(4) OVER (ORDER BY ...)
$sqlMembers = "SELECT member_name, spent,
       NTILE(4) OVER (ORDER BY spent DESC) AS quartile
FROM (
    SELECT ms.member_name, SUM(m.item_price * bi.quantity) AS spent
    FROM Memberships AS ms
    JOIN Bills AS b ON b.member_id = ms.member_id
    JOIN Bill_Items AS bi ON bi.bill_id = b.bill_id
    JOIN Menu AS m ON m.item_id = bi.item_id
    GROUP BY ms.member_id, ms.member_name
) AS spending
ORDER BY spent DESC";
$members = getRows($link, $sqlMembers);

// 5. busy hours: orders per weekday and hour (last 90 days) — GROUP BY two columns
$sqlBusy = "SELECT DAYOFWEEK(bill_time) AS dow, HOUR(bill_time) AS hr, COUNT(*) AS orders
FROM Bills
WHERE bill_time >= NOW() - INTERVAL 90 DAY
GROUP BY dow, hr";
$busyRows = getRows($link, $sqlBusy);
$busy = [];
$busiest = 1;
foreach ($busyRows as $row) {
    $busy[$row['dow']][$row['hr']] = (int) $row['orders'];
    $busiest = max($busiest, (int) $row['orders']);
}

// the biggest numbers, so the bars can be drawn as a % width
$maxDay = 1;
$maxRunning = 1;
foreach ($days as $d) {
    $maxDay = max($maxDay, (float) $d['revenue']);
    $maxRunning = max($maxRunning, (float) $d['running_total']);
}

// shows the SQL of a card in a small "Show SQL" box
function showSql($sql) {
    echo '<details class="in-sql"><summary>Show SQL</summary><pre>' . htmlspecialchars($sql) . '</pre></details>';
}
?>

<div class="wrapper">
    <div class="container-fluid">
        <h2>Insights</h2>
        <p class="in-sub">Answers calculated live by MySQL window functions &mdash; RANK, LAG, SUM OVER and NTILE.</p>

        <div class="in-grid">

            <!-- 1. RANK -->
            <section class="in-card">
                <span class="in-tag">RANK() OVER (PARTITION BY course)</span>
                <h3>Best seller in every course</h3>
                <p class="in-hint">Last 90 days</p>
                <table class="in-table">
<?php foreach ($best as $row) { ?>
                    <tr><td class="in-dim"><?= htmlspecialchars($row['item_type']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td class="in-num"><?= (int) $row['sold'] ?> sold</td></tr>
<?php } ?>
                </table>
                <?php showSql($sqlBest); ?>
            </section>

            <!-- 2. LAG -->
            <section class="in-card">
                <span class="in-tag">LAG() OVER (ORDER BY week)</span>
                <h3>This week vs last week</h3>
                <p class="in-hint">Revenue per week, last 13 weeks</p>
                <table class="in-table">
<?php foreach (array_reverse($weeks) as $row) {
        $growth = '';
        if ($row['last_week'] != null && $row['last_week'] > 0) {
            $percent = round(($row['revenue'] - $row['last_week']) / $row['last_week'] * 100, 1);
            if ($percent >= 0) {
                $growth = '<span class="in-up">▲ ' . $percent . '%</span>';
            } else {
                $growth = '<span class="in-down">▼ ' . abs($percent) . '%</span>';
            }
        } ?>
                    <tr><td class="in-dim">Week of <?= date('j M', strtotime($row['week_start'])) ?></td>
                        <td class="in-num">&#8377;<?= number_format((float) $row['revenue']) ?></td>
                        <td class="in-num"><?= $growth ?></td></tr>
<?php } ?>
                </table>
                <?php showSql($sqlWeeks); ?>
            </section>

            <!-- 3. SUM OVER -->
            <section class="in-card in-wide">
                <span class="in-tag">SUM() OVER (ORDER BY day)</span>
                <h3>Running total &mdash; last 30 days</h3>
                <p class="in-hint">Dark bar = that day's revenue &middot; ember bar = running total so far</p>
                <div class="in-bars">
<?php foreach ($days as $d) { ?>
                    <div class="in-bar" title="<?= date('j M', strtotime($d['day'])) ?>: &#8377;<?= number_format((float) $d['revenue']) ?> (total &#8377;<?= number_format((float) $d['running_total']) ?>)">
                        <span class="in-bar__run" style="height: <?= round((float) $d['running_total'] / $maxRunning * 100) ?>%"></span>
                        <span class="in-bar__day" style="height: <?= round((float) $d['revenue'] / $maxRunning * 100) ?>%"></span>
                    </div>
<?php } ?>
                </div>
<?php if (count($days) > 0) { $last = end($days); ?>
                <p class="in-total">&#8377;<?= number_format((float) $last['running_total']) ?> <span class="in-dim">in the last 30 days</span></p>
<?php } ?>
                <?php showSql($sqlDays); ?>
            </section>

            <!-- 4. NTILE -->
            <section class="in-card">
                <span class="in-tag">NTILE(4) OVER (ORDER BY spent)</span>
                <h3>Who are our best guests?</h3>
                <p class="in-hint">Members split into 4 equal groups by spending</p>
                <table class="in-table">
<?php foreach (array_slice($members, 0, 8) as $row) {
        $label = ['', 'Top 25%', 'Upper middle', 'Lower middle', 'Bottom 25%'][(int) $row['quartile']]; ?>
                    <tr><td><?= htmlspecialchars($row['member_name']) ?></td>
                        <td class="in-num">&#8377;<?= number_format((float) $row['spent']) ?></td>
                        <td class="in-num in-q<?= (int) $row['quartile'] ?>"><?= $label ?></td></tr>
<?php } ?>
                </table>
                <?php showSql($sqlMembers); ?>
            </section>

            <!-- 5. HEATMAP -->
            <section class="in-card">
                <span class="in-tag">GROUP BY DAYOFWEEK, HOUR</span>
                <h3>When are we busy?</h3>
                <p class="in-hint">Orders per weekday and hour, last 90 days</p>
                <div class="in-heat">
                    <span></span>
<?php $hours = [12, 13, 14, 19, 20, 21, 22];
      foreach ($hours as $h) { echo '<span class="in-heat__h">' . $h . '</span>'; }
      $dayNames = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat', 1 => 'Sun'];
      foreach ($dayNames as $dow => $name) {
          echo '<span class="in-heat__d">' . $name . '</span>';
          foreach ($hours as $h) {
              $n = 0;
              if (isset($busy[$dow][$h])) {
                  $n = $busy[$dow][$h];
              }
              $strength = round($n / $busiest, 2);
              echo '<span class="in-heat__c" style="background: rgba(177, 70, 47, ' . $strength . ')" title="' . $name . ' ' . $h . ':00 — ' . $n . ' orders"></span>';
          }
      } ?>
                </div>
                <?php showSql($sqlBusy); ?>
            </section>

        </div>
    </div>
</div>

<style>
    .in-sub { color: var(--text-dim); margin: 0.4rem 0 1.6rem; font-size: 0.85rem; }
    .in-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.3rem; margin-bottom: 2rem; }
    .in-card { background: var(--ink-700); border: 1px solid var(--line); padding: 1.3rem 1.4rem; min-width: 0; }
    .in-wide { grid-column: 1 / -1; }
    .in-card h3 { font-size: 1.15rem; margin: 0.6rem 0 0.1rem; }
    .in-tag { display: inline-block; font-family: monospace; font-size: 0.72rem; color: #e0784f; border: 1px solid rgba(177, 70, 47, 0.5); padding: 0.2em 0.6em; }
    .in-hint { color: var(--text-faint); font-size: 0.78rem; margin-bottom: 0.8rem; }
    .in-table { width: 100%; font-size: 0.85rem; }
    .in-table td { padding: 0.42rem 0.3rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .in-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .in-dim { color: var(--text-dim); }
    .in-up { color: #6fbfa8; }
    .in-down { color: #e0715c; }
    .in-q1 { color: #e0784f; }
    .in-q2 { color: var(--text); }
    .in-q3, .in-q4 { color: var(--text-faint); }
    .in-bars { display: flex; align-items: flex-end; gap: 4px; height: 180px; padding-top: 0.5rem; border-bottom: 1px solid var(--line); }
    .in-bar { flex: 1; height: 100%; position: relative; }
    .in-bar__run, .in-bar__day { position: absolute; left: 0; right: 0; bottom: 0; }
    .in-bar__run { background: rgba(177, 70, 47, 0.35); border-top: 2px solid #e0784f; }
    .in-bar__day { background: rgba(243, 239, 236, 0.55); left: 25%; right: 25%; }
    .in-total { font-family: var(--brand-font); font-size: 1.4rem; color: var(--text); margin-top: 0.8rem; }
    .in-total .in-dim { font-family: inherit; font-size: 0.8rem; letter-spacing: 0.1em; }
    .in-heat { display: grid; grid-template-columns: 2.6rem repeat(7, 1fr); gap: 4px; font-size: 0.7rem; color: var(--text-faint); }
    .in-heat__h { text-align: center; }
    .in-heat__c { height: 26px; border: 1px solid rgba(255, 255, 255, 0.04); }
    .in-sql { margin-top: 0.9rem; }
    .in-sql summary { cursor: pointer; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--text-faint); }
    .in-sql pre { background: rgba(0, 0, 0, 0.35); border: 1px solid var(--line); color: var(--text-dim); padding: 0.8rem; font-size: 0.72rem; margin-top: 0.6rem; white-space: pre-wrap; }
    @media (max-width: 1100px) { .in-grid { grid-template-columns: 1fr; } }
</style>

<?php include '../includes/dashFooter.php'; ?>
