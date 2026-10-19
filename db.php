<?php
// db.php - database settings and the db_connect() function used by every page
// on XAMPP no environment variables are set, so the normal XAMPP values are used
// (localhost, user root, no password, database restaurantdb)

// returns the environment variable $name, or $default if it is not set
function db_setting($name, $default) {
    $value = getenv($name);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

if (!defined('DB_HOST')) {
    define('DB_HOST', db_setting('DB_HOST', 'localhost'));
    define('DB_USER', db_setting('DB_USER', 'root'));

    // the password may be empty on purpose, so only use the default when it is not set at all
    $dbPassword = getenv('DB_PASS');
    if ($dbPassword === false) {
        $dbPassword = '';
    }
    define('DB_PASS', $dbPassword);

    define('DB_NAME', db_setting('DB_NAME', 'restaurantdb'));
    define('DB_PORT', (int) db_setting('DB_PORT', 3306));

    // online MySQL servers need a secure (SSL) connection, XAMPP does not
    define('DB_SSL', filter_var(db_setting('DB_SSL', 'false'), FILTER_VALIDATE_BOOLEAN));
}

// opens a connection to MySQL and returns it
// $database = which database to use (null = connect without choosing one)
function db_connect($database = DB_NAME)
{
    mysqli_report(MYSQLI_REPORT_OFF);   // we check errors ourselves

    $link = mysqli_init();

    if (DB_SSL) {
        // secure connection (only used when the site is online)
        $ca = db_setting('DB_SSL_CA', null);
        $link->ssl_set(null, null, $ca, null, null);

        // without a certificate file we cannot check the server, so skip the check
        if ($ca === null) {
            $link->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        }

        $link->real_connect(DB_HOST, DB_USER, DB_PASS, $database, DB_PORT, null, MYSQLI_CLIENT_SSL);
    } else {
        // normal connection (XAMPP)
        $link->real_connect(DB_HOST, DB_USER, DB_PASS, $database, DB_PORT);
    }

    return $link;
}
