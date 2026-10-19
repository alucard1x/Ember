<?php
// checkIfLoggedIn.php - sends you to the staff login page if you are not logged in
// every staff page includes this file at the top

// start the session if the page has not started it yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// login_process.php saves logged_account_id in the session when a staff member logs in
if (!isset($_SESSION['logged_account_id'])) {
    // find the address of the staff login page, e.g. /RestaurantProject/adminSide/StaffLogin/login.php
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    $adminPosition = strpos($scriptPath, '/adminSide/');
    $loginPage = substr($scriptPath, 0, $adminPosition) . '/adminSide/StaffLogin/login.php';

    header("Location: " . $loginPage);
    exit;   // stop here, so the rest of the page does not run
}
?>
