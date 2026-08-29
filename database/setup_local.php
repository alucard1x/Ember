<?php
/**
 * One-click local database setup for XAMPP.
 *
 * This used to live in index.php and ran on every visit to the public
 * homepage, which meant the live site was re-checking the schema — and
 * reporting internal state — for anyone who loaded it. It now lives here and
 * runs only when you deliberately open it:
 *
 *     http://localhost/RestaurantProject/database/setup_local.php
 *
 * It refuses to run anywhere but localhost. For a hosted database, use
 * database/import_remote.php instead.
 */

$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$/', $host)
        || PHP_SAPI === 'cli';

if (!$isLocal) {
    http_response_code(403);
    exit('This setup page only runs on localhost.');
}

ob_start();

// Check if setup has already been completed
if (file_exists(__DIR__ . '/../setup_completed.flag')) {
    echo "Setup has already been completed. The SQL setup won't run again.";
} else {
    require_once __DIR__ . '/../db.php';

    // Connect without selecting a database first, so the database can be
    // created if it does not exist yet (local XAMPP).
    $link = db_connect(null);

    // Check Connection
    if ($link->connect_error) {
        error_log('Connection Failed: ' . $link->connect_error);
        die('Unable to connect to the database.');
    }

    // Managed MySQL providers create the database for you and usually deny
    // CREATE DATABASE, so a failure here is not fatal.
    $dbName = $link->real_escape_string(DB_NAME);
    if ($link->query("CREATE DATABASE IF NOT EXISTS `{$dbName}`") === TRUE) {
        echo "Database '" . htmlspecialchars(DB_NAME) . "' is ready.<br>";
    }

    if (!$link->select_db(DB_NAME)) {
        die('<p>Database <code>' . htmlspecialchars(DB_NAME)
          . '</code> does not exist and could not be created. Create it in your '
          . 'database provider first, then reload this page.</p>');
    }

    // Execute SQL statements from "restaurantdb.txt"
    function executeSQLFromFile($filename, $link) {
        if (!is_readable($filename)) {
            echo "Cannot read <code>" . htmlspecialchars($filename) . "</code>.";
            return;
        }
        $sql = file_get_contents($filename);

        try {
            // multi_query() only reports the FIRST statement, so walk the rest:
            // otherwise a failure half way through the dump looks like success
            if (!$link->multi_query($sql)) {
                throw new mysqli_sql_exception($link->error);
            }
            do {
                if ($result = $link->store_result()) {
                    $result->free();
                }
            } while ($link->more_results() && $link->next_result());

            if ($link->errno) {
                throw new mysqli_sql_exception($link->error);
            }
        } catch (mysqli_sql_exception $e) {
            echo "<p><strong>The import stopped part way through:</strong> "
               . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>The database is now in a half-loaded state. Drop "
               . "<code>restaurantdb</code> in <a href=\"/phpmyadmin\">phpMyAdmin</a> "
               . "and reload this page to start clean.</p>";
            return;
        }

        echo "SQL statements executed successfully.";

        // Set the flag to indicate setup is complete
        if (@file_put_contents(__DIR__ . '/../setup_completed.flag', 'Setup completed successfully.') === false) {
            echo "<p><em>Note: the data loaded, but this folder is not writable so "
               . "<code>setup_completed.flag</code> could not be created. This page will "
               . "try to import again on the next visit.</em></p>";
        }
    }

    // Importing on top of an existing database collides with its primary keys,
    // so only run the dump when restaurantdb is actually empty
    $existingTables = $link->query("SHOW TABLES");
    $tableCount = $existingTables ? $existingTables->num_rows : 0;

    if ($tableCount > 0) {
        echo "<p><strong>Import skipped:</strong> <code>restaurantdb</code> already "
           . "contains {$tableCount} tables.</p>";
        echo "<p>If this is the old data and you want the current "
           . "<code>database/restaurantDB.sql</code> loaded, drop the <code>restaurantdb</code> "
           . "database in <a href=\"/phpmyadmin\">phpMyAdmin</a> and reload this page. "
           . "Otherwise just carry on to the site below.</p>";
    } else {
        executeSQLFromFile(__DIR__ . '/restaurantDB.sql', $link);
    }

    // Close the database connection
    $link->close();
}

$setupOutput = trim(ob_get_clean());
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ember Dining &amp; Bar</title>
    <style>
        :root {
            --brand-accent: #b1462f;
            --brand-ink: #14100f;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            font-family: 'Montserrat', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #f3efec;
            background-color: var(--brand-ink);
            background-image: linear-gradient(120deg, rgba(20, 16, 15, 0.9) 0%, rgba(20, 16, 15, 0.6) 100%),
                              url('../customerSide/image/hero-background.jpg');
            background-size: cover;
            background-position: center;
        }
        .setup-card {
            width: 100%;
            max-width: 620px;
            text-align: center;
        }
        .setup-card .wordmark {
            font-family: Copperplate, Copperplate Gothic Light, Optima, Palatino Linotype, Georgia, serif;
            font-size: 4.4rem;
            letter-spacing: 0.5rem;
            font-weight: 400;
        }
        .setup-card .tagline {
            font-size: 0.85rem;
            letter-spacing: 0.55rem;
            font-weight: 300;
            text-transform: uppercase;
            color: rgba(243, 239, 236, 0.7);
            margin-top: 10px;
        }
        .setup-card .rule {
            width: 60px;
            height: 1px;
            background-color: var(--brand-accent);
            margin: 30px auto;
        }
        .setup-status {
            font-size: 0.9rem;
            line-height: 1.7;
            color: rgba(243, 239, 236, 0.72);
            background-color: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 24px;
            text-align: left;
            margin-bottom: 34px;
        }
        .setup-status p { margin-bottom: 10px; }
        .setup-status p:last-child { margin-bottom: 0; }
        .setup-status a, .setup-status code { color: #f3efec; }
        .entry-links {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .entry-links a {
            display: inline-block;
            padding: 12px 34px;
            font-size: 0.8rem;
            letter-spacing: 0.35rem;
            text-transform: uppercase;
            text-decoration: none;
            color: #f3efec;
            border: 1px solid var(--brand-accent);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .entry-links a:hover {
            background-color: var(--brand-accent);
            color: #fff;
        }
    </style>
</head>

<body>
    <main class="setup-card">
        <h1 class="wordmark">Ember</h1>
        <p class="tagline">Dining &amp; Bar</p>
        <div class="rule"></div>

<?php if ($setupOutput !== ''): ?>
        <div class="setup-status"><?= $setupOutput ?></div>
<?php endif; ?>

        <nav class="entry-links">
            <a href="../customerSide/home/home.php">Home</a>
            <a href="../adminSide/StaffLogin/login.php">Staff Login</a>
        </nav>
    </main>
</body>

</html>
