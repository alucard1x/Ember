<?php
// lab-panel.php - staff panel: Booking Race Lab.
// Two guests try to book table 4 at exactly the same moment. Without a lock both "win" (double booking);
// with SELECT ... FOR UPDATE the second guest waits for the first and is then refused.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
?>

<div class="wrapper">
    <div class="container-fluid">
        <h2>Booking Race Lab</h2>
        <p class="lab-sub">Two guests press "Make Reservation" for <b>table 4, 31 Dec 2099, 8 pm</b> at the same second.
            Each one takes 2 seconds to finish. What does the database do?</p>

        <div class="lab-buttons">
            <button type="button" class="btn btn-light" data-race="nolock">Race without a lock</button>
            <button type="button" class="btn btn-dark" data-race="lock">Race with FOR UPDATE</button>
        </div>

        <div class="lab-lanes">
            <div class="lab-lane" data-lane="A"><span class="lab-who">Guest A</span><span class="lab-state">ready</span><span class="lab-time"></span></div>
            <div class="lab-lane" data-lane="B"><span class="lab-who">Guest B</span><span class="lab-state">ready</span><span class="lab-time"></span></div>
        </div>
        <p class="lab-verdict" data-verdict></p>

        <h3 class="lab-h3">What the code does</h3>
        <pre class="lab-sql">START TRANSACTION;
SELECT capacity FROM Restaurant_Tables WHERE table_id = 4 <b>FOR UPDATE</b>;   -- lock: the other guest waits here
SELECT COUNT(*) FROM Reservations WHERE table_id = 4 AND reservation_date = ... AND reservation_time = ...;
INSERT INTO Reservations (...) VALUES (...);                                -- only if the count was 0
COMMIT;                                                                       -- saves and releases the lock</pre>
        <p class="lab-note">The real "Make Reservation" button on the website (insertReservation.php) uses exactly these steps.</p>
    </div>
</div>

<style>
    .lab-sub { color: var(--text-dim); margin: 0.4rem 0 1.4rem; font-size: 0.9rem; max-width: 52rem; }
    .lab-sub b { color: var(--text); font-weight: 400; }
    .lab-buttons { display: flex; gap: 1rem; margin-bottom: 1.6rem; }
    .lab-lanes { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; max-width: 52rem; }
    .lab-lane { background: var(--ink-700); border: 1px solid var(--line); padding: 1.3rem 1.4rem; display: flex; flex-direction: column; gap: 0.4rem; transition: border-color 0.4s ease, box-shadow 0.4s ease; }
    .lab-who { font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--text-faint); }
    .lab-state { font-family: var(--brand-font); font-size: 1.5rem; letter-spacing: 0.06em; color: var(--text); }
    .lab-time { font-size: 0.8rem; color: var(--text-dim); min-height: 1.2em; }
    .lab-lane.is-waiting { border-color: #c9a227; }
    .lab-lane.is-waiting .lab-state { color: #e2cd8a; }
    .lab-lane.is-booked { border-color: #17594a; box-shadow: 0 0 22px rgba(23, 89, 74, 0.45); }
    .lab-lane.is-booked .lab-state { color: #6fbfa8; }
    .lab-lane.is-refused { border-color: #a8102f; }
    .lab-lane.is-refused .lab-state { color: #e0715c; }
    .lab-verdict { font-family: var(--brand-font); font-size: 1.15rem; letter-spacing: 0.04em; margin-top: 1.4rem; min-height: 1.6em; }
    .lab-verdict.is-bad { color: #e0715c; }
    .lab-verdict.is-good { color: #6fbfa8; }
    .lab-h3 { margin-top: 2.2rem; }
    .lab-sql { background: rgba(0,0,0,0.35); border: 1px solid var(--line); color: var(--text-dim); padding: 1rem 1.2rem; font-size: 0.8rem; white-space: pre-wrap; max-width: 60rem; }
    .lab-sql b { color: #e0784f; font-weight: 600; }
    .lab-note { color: var(--text-faint); font-size: 0.85rem; }
</style>

<script>
// Booking Race Lab: fire two booking requests at the same moment and show what happened
var labBusy = false;

function setLane(who, state, text) {
    var lane = document.querySelector('[data-lane="' + who + '"]');
    lane.className = 'lab-lane is-' + state;
    lane.querySelector('.lab-state').textContent = state;
    lane.querySelector('.lab-time').textContent = text || '';
}

function race(withLock) {
    if (labBusy) {
        return;
    }
    labBusy = true;
    var verdict = document.querySelector('[data-verdict]');
    verdict.className = 'lab-verdict';
    verdict.textContent = '';
    var extra = withLock ? '' : '&nolock=1';

    // 1. remove old test bookings, 2. start both guests together
    fetch('../lab/race.php?cleanup=1').then(function () {
        setLane('A', 'waiting', 'trying to book…');
        setLane('B', 'waiting', 'trying to book…');

        var guestA = fetch('../lab/race.php?who=A' + extra, { cache: 'no-store' }).then(function (r) { return r.json(); });
        var guestB = fetch('../lab/race.php?who=B' + extra, { cache: 'no-store' }).then(function (r) { return r.json(); });

        [guestA, guestB].forEach(function (guest) {
            guest.then(function (answer) {
                setLane(answer.who, answer.result, 'waited ' + answer.waited + ' s for the lock · finished after ' + answer.total + ' s');
            });
        });

        return Promise.all([guestA, guestB]);
    }).then(function () {
        return fetch('../lab/race.php?count=1').then(function (r) { return r.json(); });
    }).then(function (count) {
        if (count.bookings > 1) {
            verdict.className = 'lab-verdict is-bad';
            verdict.textContent = count.bookings + ' bookings for the same table and time — a double booking!';
        } else {
            verdict.className = 'lab-verdict is-good';
            verdict.textContent = 'Exactly 1 booking. The lock made the second guest wait, then refused them.';
        }
        return fetch('../lab/race.php?cleanup=1');
    }).finally(function () {
        labBusy = false;
    });
}

document.querySelector('[data-race="nolock"]').addEventListener('click', function () { race(false); });
document.querySelector('[data-race="lock"]').addEventListener('click', function () { race(true); });
</script>

<?php include '../includes/dashFooter.php'; ?>
