<?php
// track.php - live order tracker for the guest: Received → Cooking → Ready.
// The numbers come from status.php, which this page asks every 4 seconds.
$pageTitle = 'Your Order';
$pageDesc  = 'Follow your order from the kitchen to your table.';
include_once('../components/header.php');

$table_text = '';
if (isset($_SESSION['order_table'])) {
    $table_text = 'Table ' . (int) $_SESSION['order_table'];
}
?>

<section class="e-section" aria-labelledby="track-heading">
    <div class="e-shell">
        <div class="e-section__head">
            <span class="e-eyebrow e-eyebrow--ember" data-track-label><?= htmlspecialchars($table_text) ?></span>
            <h1 class="e-display" id="track-heading" data-track-title>Finding your order…</h1>
        </div>

<?php if (!isset($_SESSION['track_bill'])) { ?>
        <p class="e-bill__empty">
            There is no order to follow yet. Scan the QR code on your table,
            choose your dishes and press <em>Send to kitchen</em>.
        </p>
        <p style="margin-top: 2rem;"><a class="e-link" href="../menu/menu.php">See the menu</a></p>
<?php } else { ?>
        <div class="e-track">
            <!-- the three steps with a line that fills up -->
            <div class="e-track__steps" data-track-steps>
                <span class="e-track__line"><span class="e-track__fill" data-track-fill></span></span>
                <div class="e-track__step" data-step="received"><span class="e-track__dot"></span><span class="e-track__name">Received</span></div>
                <div class="e-track__step" data-step="cooking"><span class="e-track__dot"></span><span class="e-track__name">Cooking</span></div>
                <div class="e-track__step" data-step="ready"><span class="e-track__dot"></span><span class="e-track__name">Ready</span></div>
            </div>

            <!-- one row per dish, filled in by the script below -->
            <div class="e-track__dishes" data-track-dishes></div>

            <div class="e-bill__actions">
                <a class="e-cta" href="../menu/menu.php">
                    Order more
                    <span class="e-cta__arrow" aria-hidden="true"></span>
                </a>
            </div>
        </div>
<?php } ?>
    </div>
</section>

<?php if (isset($_SESSION['track_bill'])) { ?>
<script>
// order tracker: ask status.php how far the order is, then update the page
var trackSteps = ['received', 'cooking', 'ready'];
var trackTitles = {
    received: 'Your order is in.',
    cooking: 'On the fire.',
    ready: 'Ready — on its way.'
};
var statusWords = { waiting: 'Waiting', cooking: 'Cooking', ready: 'Ready' };

function showOrder(order) {
    if (!order.found) {
        return;
    }
    var stepIndex = trackSteps.indexOf(order.step);

    document.querySelector('[data-track-title]').textContent = trackTitles[order.step];
    document.querySelector('[data-track-label]').textContent =
        (order.table ? 'Table ' + order.table + '  ·  ' : '') + 'Order ' + order.bill + '  ·  ' + order.ready + ' of ' + order.total + ' ready';

    // light up the steps that are done, and fill the line up to the current one
    var steps = document.querySelectorAll('.e-track__step');
    for (var i = 0; i < steps.length; i++) {
        steps[i].classList.toggle('is-done', i <= stepIndex);
        steps[i].classList.toggle('is-current', i == stepIndex && order.step != 'ready');
    }
    document.querySelector('[data-track-fill]').style.width = (stepIndex * 50) + '%';

    // the dish list
    var html = '';
    for (var j = 0; j < order.dishes.length; j++) {
        var dish = order.dishes[j];
        html += '<div class="e-track__dish">' +
                '<span class="e-bill__name"></span>' +
                '<span class="e-bill__qty">× ' + dish.quantity + '</span>' +
                '<span class="e-chip e-chip--' + dish.status + '">' + statusWords[dish.status] + '</span>' +
                '</div>';
    }
    var list = document.querySelector('[data-track-dishes]');
    list.innerHTML = html;
    // dish names are added as text (never as HTML) to stay safe
    var names = list.querySelectorAll('.e-bill__name');
    for (var k = 0; k < names.length; k++) {
        names[k].textContent = order.dishes[k].item_name;
    }
}

function checkOrder() {
    fetch('status.php', { cache: 'no-store' })
        .then(function (response) { return response.json(); })
        .then(showOrder)
        .catch(function () {});
}

checkOrder();
setInterval(checkOrder, 4000);
</script>
<?php } ?>

<?php include_once('../components/footer.php'); ?>
