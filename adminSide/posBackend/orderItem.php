<?php
// orderItem.php - POS: one table's screen. Left: tap a dish to add it. Right: the bill and "Take payment".
// The table's open bill is found automatically (no bill numbers to type).
require_once __DIR__ . '/checkIfLoggedIn.php';   // staff only: if not logged in, go to the staff login page
require_once '../config.php';
include '../includes/dashHeader.php';

$table_id = intval($_GET['table_id'] ?? 0);

// 1. the table's latest bill: if it is not paid yet, it is the open bill
$bill_id = 0;
$stmt = $link->prepare('SELECT bill_id, payment_time FROM Bills WHERE table_id = ? ORDER BY bill_time DESC, bill_id DESC LIMIT 1');
$stmt->bind_param('i', $table_id);
$stmt->execute();
$latest = $stmt->get_result()->fetch_assoc();
if ($latest && $latest['payment_time'] == null) {
    $bill_id = (int) $latest['bill_id'];
}

// 2. the dishes on the open bill
$lines = [];
$subtotal = 0;
if ($bill_id > 0) {
    $stmt = $link->prepare('SELECT bi.bill_item_id, bi.item_id, bi.quantity, m.item_name, m.item_price
                            FROM Bill_Items AS bi JOIN Menu AS m ON m.item_id = bi.item_id
                            WHERE bi.bill_id = ? ORDER BY bi.bill_item_id');
    $stmt->bind_param('i', $bill_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['total'] = $row['item_price'] * $row['quantity'];
        $subtotal = $subtotal + $row['total'];
        $lines[] = $row;
    }
}
$tax = $subtotal * 0.1;            // 10% tax
$grandTotal = $subtotal + $tax;

// 3. the menu (grouped by course) and the members (for loyalty points)
$menu = mysqli_fetch_all(mysqli_query($link, 'SELECT item_id, item_name, item_type, item_price FROM Menu ORDER BY item_type, item_name'), MYSQLI_ASSOC);
$members = mysqli_fetch_all(mysqli_query($link, 'SELECT member_id, member_name, points FROM Memberships ORDER BY member_name'), MYSQLI_ASSOC);

$courses = [];
foreach ($menu as $dish) {
    $courses[$dish['item_type']][] = $dish;
}

// messages coming back from pay.php
$paidBill = intval($_GET['paid'] ?? 0);
$error = $_GET['error'] ?? '';
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pos-head">
            <div>
                <a href="posTable.php" class="pos-back">&larr; All tables</a>
                <h2>Table <?= $table_id ?></h2>
            </div>
<?php if ($bill_id > 0) { ?>
            <span class="pos-badge">Open bill #<?= $bill_id ?></span>
<?php } ?>
        </div>

<?php if ($paidBill > 0) { ?>
        <!-- shown right after a payment -->
        <div class="pos-paid">
            <div>
                <strong>Paid &#10003;</strong> Bill #<?= $paidBill ?> is settled and Table <?= $table_id ?> is free again.
<?php   if (isset($_GET['change']) && $_GET['change'] > 0) { ?>
                <span class="pos-change">Give back change: &#8377;<?= number_format((float) $_GET['change'], 2) ?></span>
<?php   } ?>
            </div>
            <div class="pos-paid__actions">
                <a class="btn btn-light" href="receipt.php?bill_id=<?= $paidBill ?>">Print receipt</a>
                <a class="btn btn-dark" href="posTable.php">Back to tables</a>
            </div>
        </div>
<?php } ?>

        <div class="pos-grid">

            <!-- LEFT: the menu. Tap + to add one portion. -->
            <section class="pos-menu">
                <input type="search" class="form-control pos-search" placeholder="Search dishes…" data-pos-search autofocus>
                <div class="pos-dishes">
<?php foreach ($courses as $course => $dishes) { ?>
                    <div class="pos-course" data-course>
                        <h4><?= htmlspecialchars($course) ?></h4>
<?php   foreach ($dishes as $dish) { ?>
                        <a class="pos-dish" data-dish="<?= htmlspecialchars(strtolower($dish['item_name'] . ' ' . $course)) ?>"
                           href="addItem.php?table_id=<?= $table_id ?>&amp;item_id=<?= urlencode($dish['item_id']) ?>">
                            <span><?= htmlspecialchars($dish['item_name']) ?></span>
                            <span class="pos-price">&#8377;<?= number_format((float) $dish['item_price']) ?></span>
                            <span class="pos-plus">+</span>
                        </a>
<?php   } ?>
                    </div>
<?php } ?>
                </div>
            </section>

            <!-- RIGHT: the bill and the payment -->
            <section class="pos-bill">
                <h3>Order</h3>
<?php if (count($lines) == 0) { ?>
                <p class="pos-empty">Nothing ordered yet. Tap a dish on the left to add it.</p>
<?php } else { ?>
                <table class="pos-lines">
<?php   foreach ($lines as $line) { ?>
                    <tr>
                        <td><?= htmlspecialchars($line['item_name']) ?></td>
                        <td class="pos-qty">
                            <a href="deleteItem.php?table_id=<?= $table_id ?>&amp;bill_item_id=<?= (int) $line['bill_item_id'] ?>&amp;one=1" title="One less">&minus;</a>
                            <span><?= (int) $line['quantity'] ?></span>
                            <a href="addItem.php?table_id=<?= $table_id ?>&amp;item_id=<?= urlencode($line['item_id']) ?>" title="One more">+</a>
                        </td>
                        <td class="pos-num">&#8377;<?= number_format($line['total'], 2) ?></td>
                        <td><a class="pos-remove" href="deleteItem.php?table_id=<?= $table_id ?>&amp;bill_item_id=<?= (int) $line['bill_item_id'] ?>" title="Remove">&times;</a></td>
                    </tr>
<?php   } ?>
                </table>

                <div class="pos-sum">
                    <span>Subtotal</span><span>&#8377;<?= number_format($subtotal, 2) ?></span>
                    <span>Tax (10%)</span><span>&#8377;<?= number_format($tax, 2) ?></span>
                    <span class="pos-grand">Total</span><span class="pos-grand">&#8377;<?= number_format($grandTotal, 2) ?></span>
                </div>

                <!-- TAKE PAYMENT: everything on one form -->
                <form class="pos-pay" method="post" action="pay.php">
                    <h3>Take payment</h3>
<?php   if ($error == 'amount') { ?>
                    <div class="alert alert-warning">The cash received is less than the total.</div>
<?php   } elseif ($error == 'card') { ?>
                    <div class="alert alert-warning">Please check the card details.</div>
<?php   } ?>
                    <input type="hidden" name="bill_id" value="<?= $bill_id ?>">
                    <input type="hidden" name="table_id" value="<?= $table_id ?>">

                    <label>Member (earns points)</label>
                    <select name="member_id" class="form-control">
                        <option value="">Walk-in guest (no member)</option>
<?php   foreach ($members as $member) { ?>
                        <option value="<?= (int) $member['member_id'] ?>"><?= htmlspecialchars($member['member_name']) ?> &middot; #<?= (int) $member['member_id'] ?></option>
<?php   } ?>
                    </select>

                    <label>Paid by</label>
                    <div class="pos-method">
                        <label><input type="radio" name="method" value="cash" checked> Cash</label>
                        <label><input type="radio" name="method" value="card"> Card</label>
                    </div>

                    <div data-pay="cash">
                        <label>Cash received (&#8377;)</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="<?= number_format($grandTotal, 2, '.', '') ?>" data-cash>
                        <p class="pos-change-live" data-change></p>
                    </div>

                    <div data-pay="card" hidden>
                        <label>Name on card</label>
                        <input type="text" name="card_name" class="form-control">
                        <label>Card number</label>
                        <input type="text" name="card_number" class="form-control" inputmode="numeric" placeholder="15 or 16 digits" maxlength="19">
                        <div class="pos-two">
                            <div><label>Expiry (MM/YYYY)</label><input type="text" name="card_expiry" class="form-control" placeholder="08/2028"></div>
                            <div><label>CVV</label><input type="text" name="card_cvv" class="form-control" maxlength="3" placeholder="123"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success pos-confirm">Confirm payment &middot; &#8377;<?= number_format($grandTotal, 2) ?></button>
                </form>
<?php } ?>
            </section>
        </div>
    </div>
</div>

<style>
    .pos-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.2rem; }
    .pos-back { font-size: 0.75rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--text-dim); }
    .pos-badge { border: 1px solid var(--line-strong); padding: 0.4em 0.9em; font-size: 0.75rem; letter-spacing: 0.12em; color: var(--text-dim); }
    .pos-paid { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; border: 1px solid #17594a; background: rgba(23, 89, 74, 0.2); padding: 1rem 1.2rem; margin-bottom: 1.4rem; }
    .pos-paid strong { color: #6fbfa8; margin-right: 0.4rem; }
    .pos-change { display: block; margin-top: 0.3rem; color: #e2cd8a; }
    .pos-paid__actions { display: flex; gap: 0.6rem; }
    .pos-grid { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .pos-menu, .pos-bill { background: var(--ink-700); border: 1px solid var(--line); padding: 1.2rem; }
    .pos-dishes { max-height: 66vh; overflow-y: auto; margin-top: 1rem; padding-right: 0.3rem; }
    .pos-course h4 { font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--text-faint); margin: 1.1rem 0 0.4rem; }
    .pos-dish { display: grid; grid-template-columns: 1fr auto 2rem; align-items: center; gap: 0.8rem; padding: 0.55rem 0.6rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); color: var(--text); text-decoration: none; }
    .pos-dish:hover { background: rgba(177, 70, 47, 0.16); color: var(--text); }
    .pos-price { color: var(--text-dim); font-variant-numeric: tabular-nums; }
    .pos-plus { width: 2rem; height: 2rem; display: grid; place-items: center; border: 1px solid var(--brand-accent); color: #e0784f; font-size: 1.1rem; }
    .pos-empty { color: var(--text-dim); font-style: italic; }
    .pos-lines { width: 100%; }
    .pos-lines td { padding: 0.5rem 0.2rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
    .pos-qty { white-space: nowrap; text-align: center; }
    .pos-qty a { display: inline-grid; place-items: center; width: 1.6rem; height: 1.6rem; border: 1px solid var(--line-strong); color: var(--text); text-decoration: none; }
    .pos-qty span { display: inline-block; min-width: 1.8rem; text-align: center; }
    .pos-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .pos-remove { color: var(--text-faint); font-size: 1.2rem; text-decoration: none; padding: 0 0.3rem; }
    .pos-remove:hover { color: #e0715c; }
    .pos-sum { display: grid; grid-template-columns: 1fr auto; gap: 0.35rem 1rem; margin: 1rem 0 1.4rem; color: var(--text-dim); font-variant-numeric: tabular-nums; }
    .pos-grand { color: var(--text); font-size: 1.15rem; border-top: 1px solid var(--line); padding-top: 0.5rem; }
    .pos-pay { border-top: 1px solid var(--line); padding-top: 1rem; }
    .pos-pay label { display: block; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--text-faint); margin: 0.9rem 0 0.3rem; }
    .pos-method { display: flex; gap: 0.6rem; }
    .pos-method label { flex: 1; margin: 0; border: 1px solid var(--line-strong); padding: 0.6rem; text-align: center; cursor: pointer; font-size: 0.8rem; color: var(--text); }
    .pos-method label:has(input:checked) { border-color: var(--brand-accent); background: rgba(177, 70, 47, 0.2); }
    .pos-method input { margin-right: 0.3rem; }
    .pos-two { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; }
    .pos-change-live { margin: 0.5rem 0 0; color: #e2cd8a; min-height: 1.2em; }
    .pos-confirm { width: 100%; margin-top: 1.3rem; padding: 0.8rem; }
    /* small screens: the order first (so it is always visible), the menu below it */
    @media (max-width: 1100px) { .pos-grid { grid-template-columns: 1fr; } .pos-bill { order: -1; } .pos-dishes { max-height: 50vh; } }
</style>

<script>
// search: hide dishes (and empty courses) that do not match what was typed
var posSearch = document.querySelector('[data-pos-search]');
posSearch.addEventListener('input', function () {
    var text = posSearch.value.trim().toLowerCase();
    var courses = document.querySelectorAll('[data-course]');
    for (var i = 0; i < courses.length; i++) {
        var dishes = courses[i].querySelectorAll('[data-dish]');
        var shown = 0;
        for (var j = 0; j < dishes.length; j++) {
            var match = dishes[j].getAttribute('data-dish').indexOf(text) !== -1;
            dishes[j].hidden = !match;
            if (match) {
                shown++;
            }
        }
        courses[i].hidden = (shown == 0);
    }
});

// payment form: show the cash fields or the card fields
var payForm = document.querySelector('.pos-pay');
if (payForm) {
    var grandTotal = <?= json_encode(round($grandTotal, 2)) ?>;

    function showMethod() {
        var method = payForm.querySelector('input[name=method]:checked').value;
        var boxes = payForm.querySelectorAll('[data-pay]');
        for (var i = 0; i < boxes.length; i++) {
            var isThis = boxes[i].getAttribute('data-pay') == method;
            boxes[i].hidden = !isThis;
            var inputs = boxes[i].querySelectorAll('input');
            for (var j = 0; j < inputs.length; j++) {
                inputs[j].required = isThis;      // only the visible fields must be filled
            }
        }
    }
    var radios = payForm.querySelectorAll('input[name=method]');
    for (var r = 0; r < radios.length; r++) {
        radios[r].addEventListener('change', showMethod);
    }
    showMethod();

    // cash: show the change while typing
    var cashInput = payForm.querySelector('[data-cash]');
    var changeText = payForm.querySelector('[data-change]');
    cashInput.addEventListener('input', function () {
        var given = parseFloat(cashInput.value || '0');
        if (given >= grandTotal) {
            changeText.textContent = 'Change to give back: ₹' + (given - grandTotal).toFixed(2);
        } else if (cashInput.value != '') {
            changeText.textContent = '₹' + (grandTotal - given).toFixed(2) + ' still missing';
        } else {
            changeText.textContent = '';
        }
    });
}
</script>

<?php include '../includes/dashFooter.php'; ?>
