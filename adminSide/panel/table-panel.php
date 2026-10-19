<?php
// table-panel.php - staff panel: Tables. Everything about the restaurant's tables in one place:
// seats, free or occupied right now, the table's QR code (guests scan it to order), open its order,
// add a table (only the number of seats) or delete one. "Print QR codes" prints just the codes.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

// for every table: its latest bill (if that bill is not paid, the table is in use)
$sql = "SELECT t.table_id, t.capacity,
               (SELECT b.payment_time IS NULL FROM Bills AS b
                WHERE b.table_id = t.table_id ORDER BY b.bill_time DESC, b.bill_id DESC LIMIT 1) AS in_use
        FROM Restaurant_Tables AS t
        ORDER BY t.table_id";
$tables = mysqli_fetch_all(mysqli_query($link, $sql), MYSQLI_ASSOC);

// the QR codes need the laptop's Wi-Fi address (a phone can't open "localhost").
// ipconfig asks the Mac for it; staff can also type it in the box.
$host = '';
if (isset($_GET['host']) && $_GET['host'] != '') {
    $host = preg_replace('/[^A-Za-z0-9.:\-]/', '', $_GET['host']);
} else {
    $host = trim((string) shell_exec('ipconfig getifaddr en0 2>/dev/null'));
    if ($host == '') {
        $host = trim((string) shell_exec('ipconfig getifaddr en1 2>/dev/null'));
    }
    if ($host == '') {
        $host = $_SERVER['HTTP_HOST'];
    }
}
$projectPath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));      // e.g. /RestaurantProject
$scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';   // https when the site is online
$baseUrl = $scheme . '://' . $host . $projectPath . '/customerSide/order/table.php?t=';

$msg = $_GET['msg'] ?? '';
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Table Setup</h2>
                <p class="pg-sub">Add or remove tables and print each table's QR code. Guests scan the code to order from their phone.</p>
            </div>
            <div class="tb-buttons">
                <button type="button" class="btn btn-light" onclick="window.print()">Print QR codes</button>
                <button type="button" class="btn btn-dark" data-open="#add-table">+ Add table</button>
            </div>
        </div>

<?php if ($msg == 'added') { ?>
        <div class="pg-msg">New table added. Its QR code is ready below.</div>
<?php } elseif ($msg == 'error') { ?>
        <div class="pg-msg is-error">Seats must be a number from 1 to 20.</div>
<?php } ?>

        <form class="pg-add" id="add-table" method="post" action="../tableCrud/addTable.php" hidden>
            <h3>New table</h3>
            <div class="pg-fields">
                <div><label>Number of seats</label><input type="number" name="capacity" min="1" max="20" value="4" class="form-control" required></div>
            </div>
            <button type="submit" class="btn btn-success">Save table</button>
        </form>

        <!-- the address inside the QR codes -->
        <form class="pg-tools tb-host" method="get">
            <span class="pg-label">Laptop address in the QR codes</span>
            <input type="text" name="host" class="form-control pg-search" value="<?= htmlspecialchars($host) ?>">
            <button type="submit" class="btn btn-light">Update codes</button>
            <span class="pg-count">Phone and laptop must be on the same Wi-Fi.</span>
        </form>

        <div class="tb-grid">
<?php foreach ($tables as $t) {
        $url = $baseUrl . (int) $t['table_id']; ?>
            <div class="tb-card">
                <div class="tb-top">
                    <div>
                        <div class="tb-name">Table <?= (int) $t['table_id'] ?></div>
                        <div class="tb-seats"><?= (int) $t['capacity'] ?> seats</div>
                    </div>
<?php   if ($t['in_use']) { ?>
                    <span class="pg-tag is-red">Occupied</span>
<?php   } else { ?>
                    <span class="pg-tag is-green">Free</span>
<?php   } ?>
                </div>
                <div class="tb-qr" data-qr="<?= htmlspecialchars($url) ?>"></div>
                <div class="tb-scan">Scan to order</div>
                <div class="tb-actions">
                    <a href="../posBackend/orderItem.php?table_id=<?= (int) $t['table_id'] ?>">Open order</a>
                    <a href="<?= htmlspecialchars($url) ?>" target="_blank">Test QR link</a>
                    <a href="../tableCrud/deleteTableVerify.php?id=<?= (int) $t['table_id'] ?>">Delete</a>
                </div>
            </div>
<?php } ?>
        </div>
    </div>
</div>

<style>
    .tb-buttons { display: flex; gap: 0.6rem; }
    .tb-host .pg-label { margin: 0; }
    .tb-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.2rem; margin-bottom: 2rem; }
    .tb-card { background: var(--ink-700); border: 1px solid var(--line); padding: 1.1rem 1.2rem; text-align: center; }
    .tb-top { display: flex; justify-content: space-between; align-items: flex-start; text-align: left; margin-bottom: 0.9rem; }
    .tb-name { font-family: var(--brand-font); font-size: 1.25rem; letter-spacing: 0.06em; color: var(--text); }
    .tb-seats { font-size: 0.72rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--text-faint); }
    .tb-qr { background: #fff; padding: 10px; display: inline-block; line-height: 0; }
    .tb-qr img { width: 140px; height: 140px; image-rendering: pixelated; }
    .tb-scan { font-size: 0.7rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--text-dim); margin-top: 0.5rem; }
    .tb-actions { display: flex; justify-content: center; gap: 0.9rem; margin-top: 0.9rem; padding-top: 0.8rem; border-top: 1px solid var(--line); font-size: 0.78rem; }
    /* printing: white paper, only the QR cards */
    @media print {
        .sb-topnav, #layoutSidenav_nav, .pg-head, .pg-add, .pg-msg, .tb-host, .tb-actions, .pg-tag,
        .ember-footer, .site-bg, .ember-filament { display: none !important; }
        body, .tb-card { background: #fff !important; color: #000 !important; }
        #layoutSidenav_content { padding: 0 !important; margin: 0 !important; }
        .tb-card { border: 1px solid #999; break-inside: avoid; }
        .tb-name, .tb-seats, .tb-scan { color: #000 !important; }
    }
</style>

<!-- qrcode.js: small free library (MIT licence) that draws QR codes in the browser, works offline -->
<script src="../js/qrcode.js"></script>
<script>
// draw one QR code in every box that has a data-qr address
var qrBoxes = document.querySelectorAll('[data-qr]');
for (var i = 0; i < qrBoxes.length; i++) {
    var qr = qrcode(0, 'M');                 // 0 = choose the size automatically, M = medium error correction
    qr.addData(qrBoxes[i].getAttribute('data-qr'));
    qr.make();
    qrBoxes[i].innerHTML = qr.createImgTag(5, 0);
}
</script>
<script src="../js/staffPages.js"></script>

<?php include '../includes/dashFooter.php'; ?>
