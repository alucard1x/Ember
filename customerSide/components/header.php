<?php
session_start();

require_once '../config.php';


$sqlmainDishes = "SELECT * FROM Menu WHERE item_category = 'Main Dishes' ORDER BY item_type; ";
$resultmainDishes = mysqli_query($link, $sqlmainDishes);
$mainDishes = mysqli_fetch_all($resultmainDishes, MYSQLI_ASSOC);

$sqldrinks = "SELECT * FROM Menu WHERE item_category = 'Drinks' ORDER BY item_type; ";
$resultdrinks = mysqli_query($link, $sqldrinks);
$drinks = mysqli_fetch_all($resultdrinks, MYSQLI_ASSOC);

$sqlsides = "SELECT * FROM Menu WHERE item_category = 'Side Snacks' ORDER BY item_type; ";
$resultsides = mysqli_query($link, $sqlsides);
$sides = mysqli_fetch_all($resultsides, MYSQLI_ASSOC);


// The signed-in member's name, points and VIP status are rendered inside the
// ACCOUNT dropdown further down this file.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- Desktop-parity viewport: phones render the full 1280px desktop layout, scaled to fit. -->
    <meta name="viewport" content="width=1280">
    <!-- ?v= is the file's last-modified time, so a restyle is never served from cache -->
    <link rel="stylesheet" href="../css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.theme.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <title>Ember Dining &amp; Bar</title>
</head>

<body class="site-body">
<?php $bgAssets = '../image'; include '../components/background.php'; ?>
    <!-- Header -->

    <section id="header">
        <div class="header container">
            <div class="nav-bar">
<?php
// The homepage already carries the wordmark in the middle of the hero, so the
// top-left mark is only shown on the other pages.
if (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) !== 'home.php') {
?>
                <div class="brand">
                    <a class="nav-link" href="../home/home.php">
                        <h1>Ember</h1>
                    </a>
                </div>
<?php
}
?>
                <div class="nav-list">
                    <div class="hamburger">
                        <div class="bar"></div>
                    </div>
                    <div class="navbar-container">

                        <div class="navbar">
                            <ul>
                                <li><a href="../menu/menu.php" data-after="Menu">Menu</a></li>
                                <li><a href="../CustomerReservation/reservePage.php"
                                        data-after="Service">Reservation</a></li>
                                <li><a href="../about/about.php" data-after="About">About</a></li>

<?php
// Needed before the dropdown so the button can be labelled correctly.
$account_id = $_SESSION['account_id'] ?? null;
$member_signed_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $account_id != null;
?>
                                <div class="dropdown">
                                    <button class="dropbtn"><?= $member_signed_in ? 'ACCOUNT' : 'LOGIN' ?> <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-content">

<?php

// Create a query to retrieve the member's information
//$query = "SELECT member_name, points FROM Memberships WHERE account_id = $account_id";

// Execute the query
//$result = mysqli_query($link, $query);

// Check if the user is logged in
if ($member_signed_in) {
    $query = "SELECT member_name, points FROM Memberships WHERE account_id = " . (int)$account_id;

// Execute the query
$result = mysqli_query($link, $query);
    // If logged in, show "Logout" link
    // Check if the query was successful
    if ($result) {
        // Fetch the member's information
        $row = mysqli_fetch_assoc($result);
        
        if ($row) {
            $member_name = $row['member_name'];
            $points = $row['points'];
            
            // Calculate VIP status
            $vip_status = ($points >= 1000) ? 'VIP' : 'Regular';
            
            // Define the VIP tooltip text
            $vip_tooltip = ($vip_status === 'Regular') ? ($points < 1000 ? (1000 - $points) . ' points to VIP ' : 'You are eligible for VIP') : '';
            
            // Output the member's information
            echo "<p class='logout-link' style='font-size:1.3em; margin-left:15px; padding:5px; color:white; '>$member_name</p>";
            echo "<p class='logout-link' style='font-size:1.3em; margin-left:15px;padding:5px;color:white; '>$points Points </p>";
            echo "<p class='logout-link' style='font-size:1.3em; margin-left:15px;padding:5px; color:white; '>$vip_status";
            
            // Add the tooltip only for Regular status
            if ($vip_status === 'Regular') {
                echo " <span class='tooltip'>$vip_tooltip</span>";
            }
            
            echo "</p>";
        } else {
            echo "Member not found.";
        }
    } else {
        echo "Error: " . mysqli_error($link);
    }

    echo '<a class="logout-link" style="color: white; font-size:1.3em;" href="../customerLogin/profile.php">Profile</a>';
    echo '<a class="logout-link" style="color: white; font-size:1.3em;" href="../customerLogin/logout.php">Logout</a>';
} else {
    // Signed out: offer both ways in, plus sign-up for new members.
    echo '<a class="login-link" style="color: white; font-size:15px;" href="../customerLogin/login.php">Member Login</a>';
    echo '<a class="login-link" style="color: white; font-size:15px;" href="../../adminSide/StaffLogin/login.php">Staff Login</a>';
    echo '<a class="signin-link" style="color: white; font-size:15px;" href="../customerLogin/register.php">Sign Up</a>';
}

// Close the database connection
mysqli_close($link);
?>


                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Header -->