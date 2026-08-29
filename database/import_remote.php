<?php
/**
 * Import deploy_import.sql into a remote MySQL server over TLS.
 *
 * XAMPP ships MariaDB's command-line client, which cannot authenticate against
 * MySQL 8's default caching_sha2_password plugin and reports a misleading
 * "Access denied" when it tries. PHP's mysqlnd driver speaks that plugin
 * correctly, so this script does the import instead of the mysql binary.
 *
 * Usage:
 *   /Applications/XAMPP/xamppfiles/bin/php database/import_remote.php \
 *       <host> <port> <user> <database> [sql-file]
 *
 * The password is prompted for, so it never reaches your shell history.
 */

if (PHP_SAPI !== 'cli') {
    exit("Run this from the command line.\n");
}

array_shift($argv);
$host = $argv[0] ?? null;
$port = isset($argv[1]) ? (int) $argv[1] : null;
$user = $argv[2] ?? null;
$name = $argv[3] ?? null;
$file = $argv[4] ?? __DIR__ . '/deploy_import.sql';

if (!$host || !$port || !$user || !$name) {
    exit("Usage: php database/import_remote.php <host> <port> <user> <database> [sql-file]\n");
}

if (!is_readable($file)) {
    exit("Cannot read SQL file: {$file}\n");
}

// Prompt without echoing the password back to the terminal.
fwrite(STDOUT, "Password for {$user}@{$host}: ");
shell_exec('stty -echo');
$pass = rtrim(fgets(STDIN), "\r\n");
shell_exec('stty echo');
fwrite(STDOUT, "\n");

mysqli_report(MYSQLI_REPORT_OFF);

$link = mysqli_init();
$link->ssl_set(null, null, null, null, null);
$link->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

fwrite(STDOUT, "Connecting to {$host}:{$port} over TLS...\n");

if (!@$link->real_connect($host, $user, $pass, $name, $port, null, MYSQLI_CLIENT_SSL)) {
    fwrite(STDERR, "\nConnection failed: " . mysqli_connect_error() . "\n");
    fwrite(STDERR, "If this says 'Access denied', the password is wrong — copy it\n");
    fwrite(STDERR, "again from the Aiven console using the copy icon.\n");
    exit(1);
}

fwrite(STDOUT, "Connected. Server version: " . $link->server_info . "\n");

$existing = $link->query('SHOW TABLES');
if ($existing && $existing->num_rows > 0) {
    fwrite(STDOUT, "\nDatabase '{$name}' already has {$existing->num_rows} tables.\n");
    fwrite(STDOUT, "Importing on top of them will collide on primary keys.\n");
    fwrite(STDOUT, "Type 'yes' to import anyway, anything else to stop: ");
    if (trim(fgets(STDIN)) !== 'yes') {
        exit("Stopped. Nothing was changed.\n");
    }
}

fwrite(STDOUT, "Importing " . basename($file) . " ("
    . number_format(filesize($file) / 1024, 1) . " KB)...\n");

$sql = file_get_contents($file);

if (!$link->multi_query($sql)) {
    exit("\nImport failed on the first statement: " . $link->error . "\n");
}

// multi_query only reports the first statement, so walk the whole result set
// or a failure half way through looks like success.
$statements = 0;
do {
    $statements++;
    if ($result = $link->store_result()) {
        $result->free();
    }
    if ($link->errno) {
        exit("\nImport stopped at statement {$statements}: " . $link->error . "\n");
    }
} while ($link->more_results() && $link->next_result());

fwrite(STDOUT, "Executed {$statements} statements.\n\n");

$tables = $link->query('SHOW TABLES');
fwrite(STDOUT, "Tables now in '{$name}' ({$tables->num_rows}):\n");
while ($row = $tables->fetch_array()) {
    $count = $link->query("SELECT COUNT(*) AS c FROM `{$row[0]}`");
    $rows  = $count ? $count->fetch_assoc()['c'] : '?';
    fwrite(STDOUT, sprintf("  %-22s %6s rows\n", $row[0], $rows));
}

$link->close();
fwrite(STDOUT, "\nDone.\n");
