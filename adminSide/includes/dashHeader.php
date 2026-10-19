<?php
// dashHeader.php - top bar and left menu of every staff panel page (dashFooter.php closes it)

// find which menu link should be lit up (class "active")
// we use the folder name, so for example menuCrud/createItem.php still lights up "Menu"
$emberPath = '';
if (isset($_SERVER['PHP_SELF'])) {
    $emberPath = (string) parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH);
}
$emberDir = basename(dirname($emberPath));    // e.g. "menuCrud"

// folder name => panel page of that folder
$folderToPanel = [
    'posBackend'       => 'pos-panel.php',
    'kitchenBackend'   => 'kitchen-panel.php',
    'tableCrud'        => 'table-panel.php',
    'menuCrud'         => 'menu-panel.php',
    'reservationsCrud' => 'reservation-panel.php',
    'customerCrud'     => 'customer-panel.php',
    'staffCrud'        => 'staff-panel.php',
    'report'           => 'sales-panel.php',
    'StaffLogin'       => '',
];

if (isset($folderToPanel[$emberDir])) {
    $emberSection = $folderToPanel[$emberDir];
} else {
    $emberSection = basename($emberPath);      // a page inside panel/ itself
}

// the left menu: one link per group. Pages that belong together share one link
// and show tabs at the top of the page to switch between them.
// each tab: [tab name, panel page, other files that count as this tab]
$emberGroups = [
    ['Point of Sale',     'fa-cash-register', [['Tables', 'pos-panel.php', ['posTable.php']], ['Bills', 'bill-panel.php']]],
    ['Table Setup',       'fa-chair',         [['Tables', 'table-panel.php']]],
    ['Menu',              'fa-utensils',      [['Dishes', 'menu-panel.php'], ['Price history', 'history-panel.php']]],
    ['Reservations',      'fa-book',          [['Bookings', 'reservation-panel.php'], ['Double-booking test', 'lab-panel.php']]],
    ['People',            'fa-people-group',  [['Members', 'customer-panel.php'], ['Member profiles', 'profiles-panel.php'],
                                               ['Staff', 'staff-panel.php'], ['All accounts', 'account-panel.php']]],
    ['Kitchen',           'fa-kitchen-set',   [['Kitchen', 'kitchen-panel.php']]],
    ['Reports',           'fa-chart-line',    [['Revenue', 'statistics-panel.php'], ['Dish sales', 'sales-panel.php'], ['Insights', 'insights-panel.php']]],
];

// find the group we are in, and the tab (only when we are on the tab's own page)
$emberFile = basename($emberPath);
$emberGroupNow = -1;
$emberTabNow = -1;
foreach ($emberGroups as $g => $group) {
    foreach ($group[2] as $t => $tab) {
        $files = array_merge([$tab[1]], $tab[2] ?? []);
        if ($tab[1] === $emberSection) {
            $emberGroupNow = $g;
        }
        if (in_array($emberFile, $files)) {
            $emberGroupNow = $g;
            $emberTabNow = $t;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard &mdash; Ember Staff Panel</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <!-- our dark Ember design on top of the template (?v= loads the newest version) -->
        <link href="../css/ember-admin.css?v=<?= @filemtime(__DIR__ . '/../css/ember-admin.css') ?>" rel="stylesheet" />
        <!-- same look as the customer homepage (fonts, pill buttons, rounded panels) -->
        <link href="../css/ember-staff.css?v=<?= @filemtime(__DIR__ . '/../css/ember-staff.css') ?>" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> 

    </head>
    <body class="sb-nav-fixed">
        <!-- loading line and page change animation -->
        <?php include __DIR__ . '/panelTransition.php'; ?>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- button that opens and closes the left menu -->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- logo. "Ember" is in its own span so it can fly in from the login page -->
            <a class="navbar-brand ps-3" href="../../customerSide/home/home.php" title="Ember homepage"><span class="ember-brand-word">Ember</span> <span class="ember-brand-suffix">Staff Panel</span></a>

            <!-- link back to the customer website (same tab, so the animation works) -->
            <a class="ember-site-link" href="../../customerSide/home/home.php"
               title="Back to the Ember website">
                <span class="ember-site-mark">Ember</span>
                <i class="fas fa-arrow-up-right-from-square"></i>
            </a>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <!-- left menu links -->
                    <div class="sb-sidenav-menu">
                        <div class="nav">
<?php foreach ($emberGroups as $g => $group) { ?>
                            <a class="nav-link<?= $g === $emberGroupNow ? ' active' : '' ?>" href="../panel/<?= $group[2][0][1] ?>">
                                <div class="sb-nav-link-icon"><i class="fas <?= $group[1] ?>"></i></div>
                                <?= htmlspecialchars($group[0]) ?>
                            </a>
<?php } ?>
                            <a class="nav-link" href="../StaffLogin/logout.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                                Log out
                            </a>
                            
                            
                            
                        </div>
                    </div>
                        <div class="sb-sidenav-footer">
                            <div class="small">Logged in as:</div>
                                <?php
                                // show who is logged in (saved in the session by login_process.php)
                                if (isset($_SESSION['logged_account_id']) && isset($_SESSION['logged_staff_name'])) {
                                    echo "Staff ID: " . $_SESSION['logged_account_id'] . "<br>";
                                    echo "Staff Name: " . $_SESSION['logged_staff_name'];
                                    
                                } else {
                                    echo "Not logged in";
                                }
                                ?>
                        </div>
                </nav>
            </div>
            <!-- the page content goes here, dashFooter.php closes <main> -->
            <div id="layoutSidenav_content">
                <main>
<?php
// tabs for groups with more than one page (only on the tab pages themselves)
if ($emberTabNow >= 0 && count($emberGroups[$emberGroupNow][2]) > 1) { ?>
                    <nav class="ember-tabs" aria-label="<?= htmlspecialchars($emberGroups[$emberGroupNow][0]) ?>">
<?php   foreach ($emberGroups[$emberGroupNow][2] as $t => $tab) { ?>
                        <a class="ember-tab<?= $t === $emberTabNow ? ' is-on' : '' ?>" href="../panel/<?= $tab[1] ?>"><?= htmlspecialchars($tab[0]) ?></a>
<?php   } ?>
                    </nav>
<?php } ?>

        <script src="../js/scripts.js" type="text/javascript"></script>
