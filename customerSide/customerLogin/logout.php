<?php
// logout.php - signs the member out and goes back to the home page
require_once '../config.php';
session_start();

// not signed in? just go home
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../home/home.php');
    exit;
}

// delete the cookie and everything in the session
setcookie('cookie_name', '', time() - 3600, '/');
$_SESSION = array();
session_destroy();

// tell the browser not to keep this page in its cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

header('Location: ../home/home.php');
exit;
