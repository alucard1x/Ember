<?php
// Credentials come from environment variables in production and fall back to
// the local XAMPP defaults otherwise. See db.php at the project root.
require_once __DIR__ . '/../db.php';

$link = db_connect();

if ($link->connect_error) {
    error_log('Connection Failed: ' . $link->connect_error);
    die('Unable to connect to the database.');
}
