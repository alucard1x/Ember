<?php
// home.php - the homepage: one long scrolling story with big type, a 3D pasta plate,
// sticky "scenes", a sideways gallery and a dish picker (layout inspired by oryzo.ai).
// Uses Ember's own logo, colours, photos, video and menu data.
// Styles: ../css/oryzo.css   Scripts: ../js/oryzo.js, ../js/oryzo-3d.js

session_start();                              // to know if a member is logged in
$signedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

require_once '../config.php';                 // database connection ($link)
require_once '../components/media.php';       // ember_picture(), ember_dish_note(), ember_word()

// ---------- data from the database ----------

// one number from a query (first column of the first row)
function oz_number($link, $sql) {
    $row = mysqli_fetch_row(mysqli_query($link, $sql));
    return $row ? (float) $row[0] : 0;
}

// the 4 signature dishes (same ones as the current homepage)
$signatures = [];
foreach (['TK1', 'BP8', 'GS2', 'BP9'] as $id) {
    $stmt = mysqli_prepare($link, 'SELECT item_id, item_name, item_type, item_category, item_price, item_description FROM Menu WHERE item_id = ?');
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($row) {
        $signatures[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// numbers used in the copy
$courseCount  = (int) oz_number($link, 'SELECT COUNT(DISTINCT item_type) FROM Menu');
$dishCount    = (int) oz_number($link, 'SELECT COUNT(*) FROM Menu');
$biryaniCount = (int) oz_number($link, "SELECT COUNT(*) FROM Menu WHERE item_type = 'Biryani & Pulao'");
$tandoorCount = (int) oz_number($link, "SELECT COUNT(*) FROM Menu WHERE item_type = 'Tandoori & Kebabs'");
$seatCount    = (int) oz_number($link, 'SELECT SUM(capacity) FROM Restaurant_Tables');
$tableCount   = (int) oz_number($link, 'SELECT COUNT(*) FROM Restaurant_Tables');

// one house cocktail for the bar section
$cocktail = mysqli_fetch_assoc(mysqli_query($link, "SELECT item_name, item_price FROM Menu WHERE item_type = 'Ember Cocktails' ORDER BY item_price DESC LIMIT 1"));

// the three parts of the menu, for the comparison table
$categories = [];
$result = mysqli_query($link, 'SELECT item_category, COUNT(*) AS dishes, COUNT(DISTINCT item_type) AS courses, MIN(item_price) AS from_price
                               FROM Menu GROUP BY item_category');
while ($row = mysqli_fetch_assoc($result)) {
    $categories[$row['item_category']] = $row;
}
$categoryNotes = [
    'Main Dishes' => ['Mains', 'The tandoor, the dum pot and the coast.', 'Dinner, properly.'],
    'Side Snacks' => ['Sides & small plates', 'Breads, chaat, salads and mithai.', 'Sharing, and the end of the meal.'],
    'Drinks'      => ['The bar', 'Indian spirits, desi cocktails, fresh juice.', 'Before, during and long after.'],
];

// photo for each signature dish (files in image/editorial/)
$dishPhotos = ['TK1' => 'raan', 'BP8' => 'biryani', 'GS2' => 'nihari', 'BP9' => 'pulao'];

// address inside the QR code on the table card (the real ordering page for table 4)
$projectPath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));      // e.g. /RestaurantProject
$qrHost = $_SERVER['HTTP_HOST'];
if (preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $qrHost)) {
    // a phone can't open "localhost", so use the laptop's Wi-Fi address (same idea as the staff Table Setup page)
    $wifi = trim((string) shell_exec('ipconfig getifaddr en0 2>/dev/null'));
    if ($wifi == '') { $wifi = trim((string) shell_exec('ipconfig getifaddr en1 2>/dev/null')); }
    if ($wifi != '') { $qrHost = $wifi; }
}
$scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';   // https when the site is online
$tableQrUrl = $scheme . '://' . $qrHost . $projectPath . '/customerSide/order/table.php?t=4';

// the dish used in the "order from your table" demo
$demoDish = $signatures[1] ?? ['item_name' => 'Ember Special Biryani', 'item_price' => 570];

// a pill button whose label rolls up on hover (the text is printed twice by CSS)
function oz_pill($href, $label, $extraClass = '') {
    $label = htmlspecialchars($label);
    echo '<a class="oz-pill ' . $extraClass . '" href="' . htmlspecialchars($href) . '">'
       . '<span class="oz-roll"><span data-label="' . $label . '">' . $label . '</span></span></a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ember, Vellore: modern Indian cooked over coals. Tandoor, dum-cooked biryani, the coast, and a bar built on Indian spirits.">
    <meta name="theme-color" content="#14100f">
    <title>Ember &mdash; Dining &amp; Bar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300..800&family=DM+Mono:wght@400;500&family=EB+Garamond:ital@1&family=Alex+Brush&display=swap">
    <link rel="stylesheet" href="../css/oryzo.css?v=<?= @filemtime(__DIR__ . '/../css/oryzo.css') ?>">
    <script>document.documentElement.classList.add('js');</script>
</head>

<body class="oz">

<!-- one background for the whole page: dark with a faint ember glow; it fades to cream for the light section -->
<div class="oz-ambient" aria-hidden="true"></div>

<a class="oz-skip" href="#intro">Skip to content</a>

<!-- ============ HEADER: small logo (after the hero), section links, phone menu ============ -->
<header class="oz-header">
    <a class="oz-header__logo" href="../home/home.php" aria-label="Ember home">Ember</a>

    <!-- section index: the name of the part you're in, and one dot per part (click a dot to jump) -->
    <nav class="oz-nav" aria-label="Sections">
        <span class="oz-nav__now" data-nav-now aria-hidden="true">Intro</span>
        <span class="oz-nav__dots">
            <a href="#intro" data-nav="intro" aria-label="Intro"></a>
            <a href="#kitchen" data-nav="kitchen" aria-label="Kitchen"></a>
            <a href="#dishes" data-nav="dishes" aria-label="Dishes"></a>
            <a href="#visit" data-nav="visit" aria-label="Visit"></a>
        </span>
    </nav>

    <!-- Sign in (or Account when a member is logged in) with a small drop-down -->
    <div class="oz-signin">
        <button class="oz-signin__btn" type="button" aria-expanded="false" aria-controls="oz-signin-menu">
            <?= $signedIn ? 'Account' : 'Sign in' ?>
        </button>
        <div class="oz-signin__menu" id="oz-signin-menu" hidden>
<?php if ($signedIn) { ?>
            <a href="../customerLogin/profile.php">My profile</a>
            <a href="../customerLogin/logout.php">Sign out</a>
<?php } else { ?>
            <a href="../customerLogin/login.php">Member login</a>
            <a href="../customerLogin/register.php">Create account</a>
            <span class="oz-signin__sep"></span>
            <a href="../../adminSide/StaffLogin/login.php">Staff login</a>
<?php } ?>
        </div>
    </div>

    <button class="oz-menu-btn" type="button" aria-expanded="false" aria-controls="oz-overlay">
        <span class="oz-menu-btn__open">Menu</span><span class="oz-menu-btn__close">Close</span>
    </button>
</header>

<!-- full screen menu on phones -->
<div class="oz-overlay" id="oz-overlay" hidden>
    <nav aria-label="Menu">
        <a href="#intro">Intro</a>
        <a href="#kitchen">Kitchen</a>
        <a href="#dishes">Dishes</a>
        <a href="#visit">Visit</a>
    </nav>
    <div class="oz-overlay__foot">
        <a href="../menu/menu.php">Full menu</a>
        <a href="../CustomerReservation/reservePage.php">Reserve a table</a>
<?php if ($signedIn) { ?>
        <a href="../customerLogin/profile.php">My profile</a>
        <a href="../customerLogin/logout.php">Sign out</a>
<?php } else { ?>
        <a href="../customerLogin/login.php">Member login</a>
        <a href="../customerLogin/register.php">Create account</a>
        <a href="../../adminSide/StaffLogin/login.php">Staff login</a>
<?php } ?>
    </div>
</div>

<!-- the tab on the right edge of the screen -->
<a class="oz-sidetab" href="../CustomerReservation/reservePage.php"><span>Reserve a table</span></a>

<main>

<!-- ============ 1. HERO ============ -->
<section class="oz-hero" id="intro" data-section="intro">
    <!-- the restaurant video plays behind the hero, darkened and faded into the page -->
    <div class="oz-hero__bg" aria-hidden="true">
        <video src="../image/background.mp4" autoplay muted loop playsinline preload="auto" poster="../image/hero-background.jpg"></video>
    </div>

    <h1 class="oz-hero__word">Ember</h1>
    <p class="oz-hero__tag">Cooked over coals. Served after dark.</p>

    <!-- the plate in the middle: a 3D plate (Three.js, js/oryzo-3d.js) drawn on the canvas.
         The round photo underneath is shown until the 3D plate is ready, or if 3D is not available. -->
    <div class="oz-hero__plate">
        <canvas class="oz-3d" data-src="../image/editorial/hero-pasta.jpg" aria-hidden="true"></canvas>
        <div class="oz-plate oz-plate--photo oz-hero__fallback" data-tilt>
            <img src="../image/editorial/hero-pasta@640.jpg" alt="Shrimp pasta puttanesca" width="640" height="828">
        </div>
        <span class="oz-marks" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
    </div>

    <p class="oz-hero__lede">Modern Indian from the tandoor, the dum pot and the coast. In Vellore, seven nights a week.</p>

    <div class="oz-glass oz-hero__card">
        <p class="oz-hero__card-title"><span class="oz-dot" aria-hidden="true"></span>Modern Indian,<br>cooked over coals.</p>
        <span class="oz-dash" aria-hidden="true"></span>
        <p class="oz-hero__card-note">Area 51, Vellore<br>Open 11:00 to 22:00</p>
    </div>


    <!-- the old homepage's scroll line: a thin line with a small ember light sliding down it -->
    <a class="oz-scrollcue" href="#statement" aria-label="Scroll to continue"><span class="oz-scrollcue__track" aria-hidden="true"></span></a>
</section>

<!-- ============ 2. STATEMENT: big words left, turning plate, text right ============ -->
<section class="oz-statement" id="statement" data-section="intro">
    <h2 class="oz-statement__title" data-fade>Isn&rsquo;t just<br>dinner.</h2>
    <div class="oz-statement__plate" aria-hidden="true" data-fade style="--d:0.12s">
        <div class="oz-plate oz-plate--photo oz-plate--edge" data-spin>
            <?php ember_picture('biryani', '', '480px'); ?>
        </div>
    </div>
    <p class="oz-statement__text" data-fade style="--d:0.24s">Ember is a kitchen built around three fires and a <?= ember_word($courseCount) ?>-course card. Almost everything passes through one of them on its way to you.</p>
</section>

<!-- ============ 3. BIG LINE THAT SHARPENS WORD BY WORD ============ -->
<section class="oz-sharpen" data-section="intro" data-progress>
    <div class="oz-sticky">
        <p class="oz-sharpen__line" data-words>Powered by fire*</p>
        <p class="oz-sharpen__sub">The tandoor runs at around 480&deg;C. The dum pot, much lower and much longer.</p>
        <p class="oz-footnote">* Charcoal, mostly.</p>
    </div>
</section>

<!-- ============ 4. THE BAR: label left, framed photo with a live-looking chip, two small photos ============ -->
<section class="oz-frame" data-section="intro">
    <div class="oz-frame__label" data-fade>
        <p class="oz-kicker">The bar</p>
        <h2 class="oz-h3">Indian, top<br>to bottom</h2>
        <p class="oz-small">Single malts from Amrut, Rampur, Paul John and Indri, a desi cocktail list, and a short house list of our own.</p>
    </div>

    <figure class="oz-frame__main" data-fade style="--d:0.12s">
        <?php ember_picture('cocktail-pour', 'Whisky poured over a large ice cube', '(max-width: 767px) 100vw, 40vw'); ?>
<?php if ($cocktail) { ?>
        <figcaption class="oz-chip">
            <span class="oz-chip__head">House pour</span>
            <strong><?= htmlspecialchars($cocktail['item_name']) ?></strong>
            <span class="oz-chip__row"><span>Ember Cocktails</span><span>&#8377;<?= number_format((float) $cocktail['item_price']) ?></span></span>
        </figcaption>
<?php } ?>
    </figure>

    <div class="oz-frame__side" data-fade style="--d:0.24s">
        <figure><?php ember_picture('bulbs-ceiling', 'A ceiling hung with glowing bulbs', '(max-width: 767px) 50vw, 14vw'); ?></figure>
        <figure><?php ember_picture('dining-room', 'A dark, wood-panelled dining room', '(max-width: 767px) 50vw, 14vw'); ?></figure>
    </div>
</section>

<!-- ============ 5. THREE HEATS: sticky glass panel, photo changes as you scroll ============ -->
<section class="oz-scenes" id="kitchen" data-section="kitchen" data-progress data-steps="3">
    <div class="oz-sticky">
        <div class="oz-scenes__photos" aria-hidden="true">
            <div class="oz-scenes__photo is-on"><?php ember_picture('raan', '', '100vw'); ?></div>
            <div class="oz-scenes__photo"><?php ember_picture('biryani', '', '100vw'); ?></div>
            <div class="oz-scenes__photo"><?php ember_picture('coast-prawns', '', '100vw'); ?></div>
        </div>

        <div class="oz-glass oz-scenes__panel">
            <div class="oz-scenes__steps">
                <article class="oz-scene is-on">
                    <span class="oz-scene__icon" aria-hidden="true">1</span>
                    <h3 class="oz-scene__kicker">The first heat: tandoor &amp; coal</h3>
                    <p><?= ucfirst(ember_word($tandoorCount)) ?> kebabs, from a raan roasted slowly on the leg to barrah chops. The chicken, prawns and fish take the same heat.</p>
                    <p class="oz-scene__title">Smoke you can taste</p>
                </article>
                <article class="oz-scene">
                    <span class="oz-scene__icon" aria-hidden="true">2</span>
                    <h3 class="oz-scene__kicker">The second heat: dum</h3>
                    <p><?= ucfirst(ember_word($biryaniCount)) ?> biryanis and pulaos, sealed and finished on a low fire. Hyderabadi, Lucknowi, Awadhi, Kashmiri, Malabar, and our own.</p>
                    <p class="oz-scene__title">Sealed, then waited for</p>
                </article>
                <article class="oz-scene">
                    <span class="oz-scene__icon" aria-hidden="true">3</span>
                    <h3 class="oz-scene__kicker">The third heat: the coast</h3>
                    <p>Goa and Malabar arrive with the fish, Amritsar and Koliwada with the fry, and a long list of rolls and street plates.</p>
                    <p class="oz-scene__title">Curry with a sea breeze</p>
                </article>
            </div>
            <div class="oz-scenes__bar" aria-hidden="true"><span></span></div>
        </div>

        <p class="oz-scenes__formula" aria-hidden="true"><span>Three fires, one kitchen</span><b>T &asymp; 480&deg;</b></p>
    </div>
</section>

<!-- ============ 6. ORDER FROM YOUR TABLE: centred title, pill buttons switch the phone screen ============ -->
<section class="oz-order" data-section="kitchen">
    <p class="oz-kicker" data-fade>No waving for the waiter</p>
    <h2 class="oz-h2" data-fade style="--d:0.08s">Order from<br>your table</h2>
    <p class="oz-order__lede" data-fade style="--d:0.16s">Scan the code on your table, pick your dishes, and watch the kitchen cook them.</p>

    <div class="oz-phone" aria-live="polite" data-fade style="--d:0.24s">
        <div class="oz-phone__screen" data-phone="scan">
            <span class="oz-phone__qr" aria-hidden="true"></span>
            <p><strong>Table 4</strong>Welcome to Ember. Tap a dish to add it.</p>
        </div>
        <div class="oz-phone__screen" data-phone="order" hidden>
            <p class="oz-phone__line"><span><?= htmlspecialchars($demoDish['item_name']) ?> &times; 1</span><span>&#8377;<?= number_format((float) $demoDish['item_price']) ?></span></p>
            <span class="oz-phone__btn">Send to kitchen</span>
        </div>
        <div class="oz-phone__screen" data-phone="track" hidden>
            <ol class="oz-phone__track"><li class="is-done">Waiting</li><li class="is-now">Cooking</li><li>Ready</li></ol>
            <p>About 12 minutes. This page updates by itself.</p>
        </div>
    </div>

    <div class="oz-order__pills" role="group" aria-label="Show a step" data-fade style="--d:0.32s">
        <button type="button" class="oz-pill is-on" data-show="scan"><span class="oz-roll"><span data-label="Scan the code">Scan the code</span></span></button>
        <button type="button" class="oz-pill" data-show="order"><span class="oz-roll"><span data-label="Send the order">Send the order</span></span></button>
        <button type="button" class="oz-pill" data-show="track"><span class="oz-roll"><span data-label="Watch it cook">Watch it cook</span></span></button>
    </div>
</section>

<!-- ============ 7. ONE HUGE WORD, THEN THREE CARDS (light section) ============ -->
<section class="oz-light" data-section="kitchen">
    <div class="oz-huge">
        <p class="oz-huge__label" data-fade>Slow by choice</p>
        <h2 class="oz-huge__word" data-fade style="--d:0.08s">unhurried</h2>
        <p class="oz-huge__text" data-fade style="--d:0.16s">Nothing here is rushed: the raan roasts for hours, the nihari braises overnight, and the biryani waits under its seal until it&rsquo;s ready.</p>
    </div>

    <div class="oz-cards">
        <article class="oz-card oz-card--ember" data-fade>
            <h3>Courses on<br>the card</h3>
            <p><?= $dishCount ?> dishes, from kebabs and biryani to mithai and the bar.</p>
            <span class="oz-card__num" aria-hidden="true"><?= $courseCount ?></span>
        </article>
        <article class="oz-card" data-fade style="--d:0.1s">
            <h3>Braised<br>overnight</h3>
            <div class="oz-card__art oz-card__art--clock" aria-hidden="true"><span></span></div>
            <p>The mutton shank braises overnight, so it falls apart by the time you sit down.</p>
        </article>
        <article class="oz-card" data-fade style="--d:0.2s">
            <h3>Ways to<br>say thanks</h3>
            <div class="oz-card__art oz-card__art--words" aria-hidden="true">
                <span>Nandri</span><span>Dhanyavaad</span><span>Shukriya</span><span>Thank you</span><span>Dhonnobad</span>
            </div>
            <p>Whichever you use, the staff will say it back.</p>
        </article>
    </div>
</section>

<!-- ============ 8. REVIEWS ============ -->
<!-- SAMPLE REVIEWS for the project demo: replace them with real guest reviews before going live -->
<section class="oz-reviews" id="dishes" data-section="dishes">
    <div class="oz-reviews__head" data-fade>
        <h2 class="oz-h2">Vellore keeps<br>coming back</h2>
        <div class="oz-reviews__score">
            <span class="oz-mono">Guest reviews [ sample ]</span>
            <span class="oz-reviews__big">4.8<small>/5</small></span>
        </div>
    </div>

    <div class="oz-reviews__track" data-fade style="--d:0.12s" data-lenis-prevent tabindex="0" aria-label="Sample reviews, scroll sideways">
<?php
$reviews = [
    ['5/5', 'The raan came apart with a spoon. I am already planning the next birthday around it.', 'Meera S.', 'Booked for six'],
    ['5/5', 'Scanned the code, ordered, and watched it go from cooking to ready. No one had to wave.', 'Arjun P.', 'Table 4 regular'],
    ['4.5/5', 'Came for the biryani, stayed for the Amrut flight. The room is dark in the best way.', 'Kavya R.', 'First visit'],
    ['5/5', 'The nihari tastes like someone stayed up all night for it. Apparently someone did.', 'Imran K.', 'Sunday lunch'],
    ['5/5', 'Points from every bill add up fast. VIP after three dinners, not complaining.', 'Divya N.', 'Member'],
];
foreach ($reviews as $r) { ?>
        <figure class="oz-review">
            <span class="oz-mono">[ <?= $r[0] ?> ]</span>
            <blockquote>&ldquo;<?= htmlspecialchars($r[1]) ?>&rdquo;</blockquote>
            <figcaption>
                <span class="oz-review__face" aria-hidden="true"><?= htmlspecialchars(substr($r[2], 0, 1)) ?></span>
                <span><strong><?= htmlspecialchars($r[2]) ?></strong><?= htmlspecialchars($r[3]) ?></span>
            </figcaption>
        </figure>
<?php } ?>
    </div>
</section>

<!-- ============ 9. SIDEWAYS GALLERY (moves left as you scroll down) ============ -->
<section class="oz-strip" data-section="dishes" data-progress>
    <div class="oz-sticky">
        <div class="oz-strip__track">
            <figure class="oz-tile oz-tile--wide">
                <?php ember_picture('spit-flame', 'Flames rising around a spit', '(max-width: 767px) 85vw, 60vw'); ?>
                <figcaption><b>Real fire</b><span>No gas grills, no shortcuts.</span></figcaption>
            </figure>
            <!-- Ember's own table card with a real QR code (it opens table 4's order page) -->
            <figure class="oz-tile oz-tile--qr">
                <?php ember_picture('grill-wide', '', '(max-width: 767px) 85vw, 30vw'); ?>
                <div class="oz-qrcard" role="img" aria-label="Ember table card with a QR code for table 4">
                    <span class="oz-qrcard__logo">Ember</span>
                    <span class="oz-qrcard__code" data-qr="<?= htmlspecialchars($tableQrUrl) ?>"></span>
                    <span class="oz-qrcard__table">Table 4</span>
                    <span class="oz-qrcard__hint">Scan to order</span>
                </div>
                <figcaption><b>Scan</b><span>Every table has its own code.</span></figcaption>
            </figure>
            <figure class="oz-tile oz-tile--wide">
                <?php ember_picture('bulbs-ceiling', 'A ceiling hung with glowing bulbs', '(max-width: 767px) 85vw, 60vw'); ?>
                <figcaption><b>Open late</b><span>Seven days, 11:00 to 22:00.</span></figcaption>
            </figure>
            <figure class="oz-tile">
                <?php ember_picture('dining-room', 'A dark, wood-panelled dining room', '(max-width: 767px) 85vw, 30vw'); ?>
                <figcaption><b><?= $seatCount ?> seats</b><span>Across <?= ember_word($tableCount) ?> tables.</span></figcaption>
            </figure>
            <figure class="oz-tile oz-tile--wide">
                <?php ember_picture('biryani', 'Biryani in a copper handi', '(max-width: 767px) 85vw, 60vw'); ?>
                <figcaption><b><?= ucfirst(ember_word($biryaniCount)) ?> biryanis</b><span>Each one finished under dum.</span></figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- ============ 10. CHOOSE YOUR DISH: pill tabs, plate in the middle, details either side ============ -->
<section class="oz-picker" data-section="dishes">
    <p class="oz-kicker">Choose your</p>
    <h2 class="oz-picker__word" data-fade>Signature</h2>

    <div class="oz-picker__tabs" data-fade style="--d:0.08s" data-lenis-prevent role="tablist" aria-label="Signature dishes">
<?php foreach ($signatures as $i => $dish) { ?>
        <button type="button" class="oz-pill<?= $i == 0 ? ' is-on' : '' ?>" role="tab" id="oz-tab-<?= $i ?>"
                aria-selected="<?= $i == 0 ? 'true' : 'false' ?>" aria-controls="oz-panel-<?= $i ?>">
            <span class="oz-roll"><span data-label="<?= htmlspecialchars($dish['item_name']) ?>"><?= htmlspecialchars($dish['item_name']) ?></span></span>
        </button>
<?php } ?>
    </div>

<?php foreach ($signatures as $i => $dish) {
        $note = ember_dish_note($dish['item_description'], $dish['item_name'], $dish['item_type']); ?>
    <div class="oz-picker__panel" data-fade style="--d:0.16s" role="tabpanel" id="oz-panel-<?= $i ?>" aria-labelledby="oz-tab-<?= $i ?>"<?= $i == 0 ? '' : ' hidden' ?>>
        <div class="oz-picker__about">
            <h3><?= htmlspecialchars($dish['item_name']) ?></h3>
            <p><?= htmlspecialchars($note != '' ? $note : $dish['item_type']) ?>. One of the four dishes we would cook for you first.</p>
        </div>
        <div class="oz-plate oz-plate--photo">
            <?php ember_picture($dishPhotos[$dish['item_id']] ?? 'biryani', $dish['item_name'], '(max-width: 767px) 70vw, 360px'); ?>
        </div>
        <ul class="oz-picker__facts">
            <li><?= htmlspecialchars($dish['item_type']) ?></li>
            <li>&#8377;<?= number_format((float) $dish['item_price']) ?> before tax</li>
            <li>Counts toward member points</li>
        </ul>
    </div>
<?php } ?>

    <!-- comparison of the three parts of the menu -->
    <div class="oz-compare" data-fade data-lenis-prevent role="region" aria-label="The three parts of the menu" tabindex="0">
        <table>
            <thead>
                <tr>
<?php foreach ($categoryNotes as $key => $n) { ?>
                    <th scope="col"><?= $n[0] ?><span><?= $n[1] ?></span></th>
<?php } ?>
                </tr>
            </thead>
            <tbody>
                <tr>
<?php foreach ($categoryNotes as $key => $n) { ?>
                    <td><span class="oz-mono">Dishes</span><?= (int) ($categories[$key]['dishes'] ?? 0) ?></td>
<?php } ?>
                </tr>
                <tr>
<?php foreach ($categoryNotes as $key => $n) { ?>
                    <td><span class="oz-mono">Courses</span><?= (int) ($categories[$key]['courses'] ?? 0) ?></td>
<?php } ?>
                </tr>
                <tr>
<?php foreach ($categoryNotes as $key => $n) { ?>
                    <td><span class="oz-mono">From</span>&#8377;<?= number_format((float) ($categories[$key]['from_price'] ?? 0)) ?></td>
<?php } ?>
                </tr>
                <tr>
<?php foreach ($categoryNotes as $key => $n) { ?>
                    <td><span class="oz-mono">Best for</span><?= $n[2] ?></td>
<?php } ?>
                </tr>
            </tbody>
        </table>
    </div>
    <p class="oz-picker__more"><?php oz_pill('../menu/menu.php', 'See the full menu'); ?></p>
</section>

</main>

<!-- ============ 11. CONTACT / FOOTER ============ -->
<footer class="oz-contact" id="visit" data-section="visit">
    <!-- the restaurant video again, darker than in the hero (it starts playing when you reach the footer) -->
    <div class="oz-contact__bg" aria-hidden="true">
        <video src="../image/background.mp4" muted loop playsinline preload="none" poster="../image/hero-background.jpg" data-lazy-video></video>
    </div>

    <div class="oz-contact__top">
        <span class="oz-contact__mark" aria-hidden="true">E</span>
        <div class="oz-contact__statement" data-fade>
            <p>You scrolled all the way down.</p>
            <p>Dinner is the easy part.</p>
            <?php oz_pill('../CustomerReservation/reservePage.php', 'Reserve a table', 'oz-pill--solid'); ?>
        </div>
    </div>

    <div class="oz-contact__grid" data-fade style="--d:0.12s">
        <div class="oz-contact__box">
            <p>Share Ember<br>with friends</p>
            <button type="button" class="oz-pill oz-pill--small" data-copy-url><span class="oz-roll"><span data-label="Copy link">Copy link</span></span></button>
        </div>
        <div class="oz-contact__col">
            <p class="oz-mono">Visit</p>
            <p>Area 51, Vellore<br>Tamil Nadu</p>
            <p class="oz-mono">Call</p>
            <a href="tel:7777777777">7777777777</a>
        </div>
        <div class="oz-contact__col">
            <p class="oz-mono">Hours</p>
            <p>Open seven days<br>11:00 to 22:00</p>
            <p class="oz-mono">Email</p>
            <a href="mailto:noreplyaryan.ayush33@gmail.com">Write to us</a>
        </div>
        <nav class="oz-contact__col" aria-label="Pages">
            <a href="../menu/menu.php">Full menu</a>
            <a href="../about/about.php">Our story</a>
            <a href="../CustomerReservation/reservePage.php">Reservations</a>
            <a href="../customerLogin/login.php">Member login</a>
        </nav>
    </div>

    <div class="oz-contact__base" data-fade style="--d:0.2s">
        <!-- my signature (same as every other page) -->
        <p class="footer-signature">
            <span class="footer-signature-text">aryan made it</span>
            <span class="footer-signature-pixels" aria-hidden="true"></span>
        </p>
        <p class="oz-contact__legal">
            &copy; 26MCA0009<br>
            DBMS Project
            <span>Reviews on this page are samples. Some dish photos: Unsplash; pasta photo: jillhough.com.</span>
        </p>
    </div>
</footer>

<!-- Lenis: smooth "floaty" scrolling. Three.js r128: the 3D plate in the hero. Both free (MIT). -->
<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" defer></script>
<!-- qrcode.js: small free library (MIT) that draws QR codes in the browser (same file the staff Table Setup page uses) -->
<script src="../../adminSide/js/qrcode.js" defer></script>
<script src="../js/oryzo.js?v=<?= @filemtime(__DIR__ . '/../js/oryzo.js') ?>" defer></script>
<script src="../js/oryzo-3d.js?v=<?= @filemtime(__DIR__ . '/../js/oryzo-3d.js') ?>" defer></script>

  <script>
  // signature animation: small blocks cover the text and then disappear one by one
  var signature = document.querySelector('.footer-signature');
  var signaturePixels = signature.querySelector('.footer-signature-pixels');
  var signatureTiles = [];
  var signatureNoMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // cover the text with all the blocks
  function coverSignature() {
      signature.classList.add('is-revealing');
      for (var i = 0; i < signatureTiles.length; i++) {
          signatureTiles[i].style.transitionDelay = '0s';
          signatureTiles[i].style.opacity = '1';
          signatureTiles[i].style.transform = 'scale(1)';
      }
  }

  // remove the blocks, each one after a random small delay
  function revealSignature() {
      for (var i = 0; i < signatureTiles.length; i++) {
          signatureTiles[i].style.transitionDelay = (Math.random() * 0.55).toFixed(3) + 's';
          signatureTiles[i].style.opacity = '0';
          signatureTiles[i].style.transform = 'scale(0.4)';
      }
      setTimeout(function () {
          signature.classList.remove('is-revealing');
      }, 120);
  }

  if (!signatureNoMotion) {
      // make a grid of 22 x 5 blocks
      signaturePixels.style.gridTemplateColumns = 'repeat(22, 1fr)';
      signaturePixels.style.gridTemplateRows = 'repeat(5, 1fr)';
      for (var t = 0; t < 22 * 5; t++) {
          var tile = document.createElement('i');
          signaturePixels.appendChild(tile);
          signatureTiles.push(tile);
      }

      coverSignature();

      // play the animation every time the footer comes into view
      var signatureWatcher = new IntersectionObserver(function (entries) {
          if (entries[0].isIntersecting) {
              revealSignature();
          } else {
              coverSignature();
          }
      }, { threshold: 0.6 });
      signatureWatcher.observe(signature);
  }
  </script>

</body>
</html>
