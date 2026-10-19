<?php
// header.php - top part of every customer page (head, header, navigation, phone menu)
// a page can set $pageTitle and $pageDesc before including this file

session_start();

require_once '../config.php';            // database connection ($link)
require_once __DIR__ . '/media.php';     // helper functions


// ---------- get the menu from the database (menu.php uses these 3 lists) ----------

$sqlmainDishes = "SELECT * FROM Menu WHERE item_category = 'Main Dishes' ORDER BY item_type; ";
$resultmainDishes = mysqli_query($link, $sqlmainDishes);
$mainDishes = mysqli_fetch_all($resultmainDishes, MYSQLI_ASSOC);

$sqldrinks = "SELECT * FROM Menu WHERE item_category = 'Drinks' ORDER BY item_type; ";
$resultdrinks = mysqli_query($link, $sqldrinks);
$drinks = mysqli_fetch_all($resultdrinks, MYSQLI_ASSOC);

$sqlsides = "SELECT * FROM Menu WHERE item_category = 'Side Snacks' ORDER BY item_type; ";
$resultsides = mysqli_query($link, $sqlsides);
$sides = mysqli_fetch_all($resultsides, MYSQLI_ASSOC);


// ---------- which page are we on? ----------

$currentPage = basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH));
$isHome = false;
if ($currentPage == 'home.php') {
    $isHome = true;
}


// ---------- is a member logged in? ----------

$account_id = null;
if (isset($_SESSION['account_id'])) {
    $account_id = $_SESSION['account_id'];
}

$member_signed_in = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $account_id != null) {
    $member_signed_in = true;
}

// if logged in, get the member's name and points
$member_name = null;
$member_points = null;
if ($member_signed_in) {
    $stmt = mysqli_prepare($link, 'SELECT member_name, points FROM Memberships WHERE account_id = ?');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $account_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                $member_name = $row['member_name'];
                $member_points = (int) $row['points'];
            }
        }
        mysqli_stmt_close($stmt);
    }
}


// ---------- page title and description ----------

if (!isset($pageTitle)) {
    $pageTitle = null;
}
if (!isset($pageDesc)) {
    $pageDesc = 'Modern Indian cooked over coals. Tandoor, dum and the coast, in Vellore.';
}

if ($pageTitle) {
    $fullTitle = htmlspecialchars($pageTitle, ENT_QUOTES) . ' &mdash; Ember';
} else {
    $fullTitle = 'Ember &mdash; Dining &amp; Bar';
}

// homepage gets an extra class (used by the background dimmer)
$bodyClass = 'site-body';
if ($isHome) {
    $bodyClass = 'site-body has-hero';
}

// navigation links (shown in the header and in the phone menu)
$navLinks = [
    ['../menu/menu.php', 'Menu'],
    ['../about/about.php', 'Our Story'],
    ['../CustomerReservation/reservePage.php', 'Reservations'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES) ?>">
    <meta name="theme-color" content="#14100f">
    <title><?= $fullTitle ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- ?v= changes when the file changes, so the browser loads the new version -->
    <link rel="stylesheet" href="../css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>">
    <!-- same look as the new homepage -->
    <link rel="stylesheet" href="../css/ember-v2.css?v=<?= @filemtime(__DIR__ . '/../css/ember-v2.css') ?>">
</head>

<body class="<?= $bodyClass ?>">
<?php
// background video (and the loading screen / page transitions)
$bgAssets = '../image';
include '../components/background.php';
?>

<a class="e-skip" href="#main">Skip to content</a>

<header class="e-masthead">
    <!-- menu button, only shown on phones -->
    <button class="e-burger" type="button" aria-expanded="false" aria-controls="ember-menu">
        <span></span><span></span><span></span>
        <span class="e-visually-hidden">Menu</span>
    </button>

<?php
// the homepage already shows the big logo, so the small one is only on other pages
if (!$isHome) {
?>
    <a class="e-masthead__brand" href="../home/home.php">
        <span class="e-masthead__wordmark">Ember</span>
    </a>
<?php
}
?>

    <nav class="e-nav" aria-label="Primary">
<?php foreach ($navLinks as $navLink) { ?>
        <a href="<?= $navLink[0] ?>"><?= $navLink[1] ?></a>
<?php } ?>

        <!-- Sign In / Account dropdown -->
        <div class="e-account">
            <button class="e-account__button" type="button" aria-expanded="false">
                <?php if ($member_signed_in) { echo 'Account'; } else { echo 'Sign In'; } ?>
            </button>
            <div class="e-account__panel">
<?php if ($member_signed_in) { ?>
<?php   if ($member_name !== null) { ?>
                <span class="e-account__meta">
                    <strong><?= htmlspecialchars($member_name) ?></strong>
                    <?= number_format($member_points) ?> points &middot; <?php if ($member_points >= 1000) { echo 'VIP'; } else { echo 'Member'; } ?>
                </span>
<?php       if ($member_points < 1000) { ?>
                <span class="e-account__meta"><?= number_format(1000 - $member_points) ?> points to VIP</span>
<?php       } ?>
                <span class="e-account__sep"></span>
<?php   } ?>
                <a href="../customerLogin/profile.php">Profile</a>
                <a href="../customerLogin/logout.php">Sign out</a>
<?php } else { ?>
                <a href="../customerLogin/login.php">Member Login</a>
                <a href="../customerLogin/register.php">Create Account</a>
                <span class="e-account__sep"></span>
                <a href="../../adminSide/StaffLogin/login.php">Staff Login</a>
<?php } ?>
            </div>
        </div>
    </nav>

    <!-- empty space on the right so the logo stays in the middle on phones -->
    <span class="e-masthead__spacer" aria-hidden="true"></span>
</header>

<!-- full screen menu for phones (opened by the menu button) -->
<div class="e-overlay" id="ember-menu" aria-hidden="true">
    <nav aria-label="Menu">
        <ul class="e-overlay__list">
            <li><a href="../home/home.php">Home</a></li>
<?php foreach ($navLinks as $navLink) { ?>
            <li><a href="<?= $navLink[0] ?>"><?= $navLink[1] ?></a></li>
<?php } ?>
<?php if ($member_signed_in) { ?>
            <li><a href="../customerLogin/profile.php">Profile</a></li>
            <li><a href="../customerLogin/logout.php">Sign out</a></li>
<?php } else { ?>
            <li><a href="../customerLogin/login.php">Member Login</a></li>
<?php } ?>
        </ul>
    </nav>
    <div class="e-overlay__foot">
        <span class="e-eyebrow">Open seven days &middot; 11:00 &ndash; 22:00</span>
        <a class="e-eyebrow" href="tel:7777777777">7777777777</a>
<?php if (!$member_signed_in) { ?>
        <a class="e-eyebrow" href="../../adminSide/StaffLogin/login.php">Staff Login</a>
<?php } ?>
    </div>
</div>

<main id="main">
