<?php
/**
 * Shared database bootstrap.
 *
 * Locally (XAMPP) no environment variables are set, so the defaults below
 * apply and nothing changes. On Render the DB_* variables are supplied as
 * environment variables in the service settings and take over.
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
    define('DB_NAME', getenv('DB_NAME') ?: 'restaurantdb');
    define('DB_PORT', (int) (getenv('DB_PORT') ?: 3306));
    // Hosted MySQL providers (Aiven, Clever Cloud, PlanetScale) require TLS.
    define('DB_SSL', filter_var(getenv('DB_SSL') ?: 'false', FILTER_VALIDATE_BOOLEAN));
}

/**
 * Open a mysqli connection using the constants above.
 *
 * @param string|null $database Database to select, or null to connect without one.
 */
function db_connect($database = DB_NAME)
{
    mysqli_report(MYSQLI_REPORT_OFF);

    $link = mysqli_init();

    if (DB_SSL) {
        $ca = getenv('DB_SSL_CA') ?: null;
        $link->ssl_set(null, null, $ca, null, null);

        // Without a CA bundle the certificate cannot be verified, so skip
        // verification rather than failing the handshake outright.
        if ($ca === null) {
            $link->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        }

        $link->real_connect(
            DB_HOST,
            DB_USER,
            DB_PASS,
            $database,
            DB_PORT,
            null,
            MYSQLI_CLIENT_SSL
        );
    } else {
        $link->real_connect(DB_HOST, DB_USER, DB_PASS, $database, DB_PORT);
    }

    return $link;
}
