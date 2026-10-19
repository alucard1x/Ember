<?php
// kitchen-panel.php - staff panel: live kitchen display. Every dish that still has to be cooked
// is shown as a ticket per table, with a timer. The tickets reload by themselves every 5 seconds.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="kds-head">
            <div>
                <h2>Kitchen</h2>
                <p class="kds-sub">Live tickets &middot; new orders from QR tables and the POS appear here by themselves</p>
            </div>
            <a href="../posBackend/kitchenBackend/undo.php?UndoUnshow=true" class="btn btn-warning mb-2">Undo last Ready</a>
        </div>

        <!-- tickets.php fills this box (and refills it every 5 seconds) -->
        <div class="kds-board" data-kds-board>
            <p class="kds-empty">Loading the kitchen&hellip;</p>
        </div>
    </div>
</div>

<style>
    /* kitchen display: one card per table */
    .kds-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
    .kds-sub { color: var(--text-dim); margin: 0.4rem 0 0; font-size: 0.85rem; letter-spacing: 0.04em; }
    .kds-board { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1.25rem; }
    .kds-empty { color: var(--text-dim); font-style: italic; }
    .kds-ticket { background: var(--ink-700); border: 1px solid var(--line); padding: 1.1rem 1.2rem 1.2rem; position: relative; }
    .kds-ticket.is-new { animation: kds-glow 1.6s ease-in-out 3; border-color: var(--brand-accent); }
    .kds-ticket__top { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1px solid var(--line); padding-bottom: 0.7rem; margin-bottom: 0.6rem; }
    .kds-table { font-family: var(--brand-font); font-size: 1.15rem; letter-spacing: 0.08em; color: var(--text); }
    .kds-meta { font-size: 0.7rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--text-faint); }
    .kds-timer { font-variant-numeric: tabular-nums; font-size: 1.05rem; color: #6fbfa8; }
    .kds-timer.is-slow { color: #e2cd8a; }
    .kds-timer.is-late { color: #e0715c; animation: kds-blink 1s steps(2) infinite; }
    .kds-line { display: grid; grid-template-columns: 2.2rem 1fr auto; align-items: center; gap: 0.6rem; padding: 0.55rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .kds-qty { color: var(--brand-accent); font-weight: 500; }
    .kds-name { color: var(--text); }
    .kds-line.is-cooking .kds-name::after { content: ' · cooking'; color: #e0784f; font-size: 0.75rem; letter-spacing: 0.1em; }
    .kds-btn { font-size: 0.65rem; letter-spacing: 0.16em; text-transform: uppercase; padding: 0.45em 0.8em; border: 1px solid var(--line-strong); color: var(--text); background: transparent; text-decoration: none; white-space: nowrap; }
    .kds-btn:hover { border-color: var(--brand-accent); color: var(--text); background: rgba(177, 70, 47, 0.2); }
    .kds-btn--ready { border-color: #17594a; color: #6fbfa8; }
    .kds-btn--ready:hover { background: rgba(23, 89, 74, 0.35); border-color: #6fbfa8; color: #fff; }
    @keyframes kds-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(177, 70, 47, 0); } 50% { box-shadow: 0 0 26px 2px rgba(177, 70, 47, 0.55); } }
    @keyframes kds-blink { 50% { opacity: 0.45; } }
</style>

<script>
// live kitchen display
var kdsBoard = document.querySelector('[data-kds-board]');
var kdsLoadedAt = Date.now();

// load the tickets (HTML) from tickets.php
function loadTickets() {
    fetch('../posBackend/kitchenBackend/tickets.php', { cache: 'no-store' })
        .then(function (response) { return response.text(); })
        .then(function (html) {
            kdsBoard.innerHTML = html;
            kdsLoadedAt = Date.now();
            updateTimers();
        })
        .catch(function () {});
}

// every second: count the timers up from the age the server told us
function updateTimers() {
    var timers = document.querySelectorAll('[data-age]');
    var extra = Math.floor((Date.now() - kdsLoadedAt) / 1000);
    for (var i = 0; i < timers.length; i++) {
        var seconds = parseInt(timers[i].getAttribute('data-age'), 10) + extra;
        var minutes = Math.floor(seconds / 60);
        var rest = seconds % 60;
        timers[i].textContent = minutes + ':' + (rest < 10 ? '0' : '') + rest;
        timers[i].classList.toggle('is-slow', seconds >= 600 && seconds < 1200);   // 10 minutes
        timers[i].classList.toggle('is-late', seconds >= 1200);                    // 20 minutes
    }
}

// Start / Ready buttons: tell the server, then reload the tickets (no page reload)
kdsBoard.addEventListener('click', function (e) {
    var button = e.target.closest('[data-kds-action]');
    if (!button) {
        return;
    }
    e.preventDefault();
    fetch(button.getAttribute('href') + '&ajax=1', { cache: 'no-store' }).then(loadTickets);
});

loadTickets();
setInterval(loadTickets, 5000);
setInterval(updateTimers, 1000);
</script>

<?php include '../includes/dashFooter.php'; ?>
