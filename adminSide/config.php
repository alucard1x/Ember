<?php
// config.php - opens the database connection ($link) for every staff page
require_once __DIR__ . '/../db.php';

$link = db_connect();

// stop if MySQL is not running
if ($link->connect_error) {
    error_log('Connection Failed: ' . $link->connect_error);
    die('Unable to connect to the database.');
}
