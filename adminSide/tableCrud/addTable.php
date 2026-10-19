<?php
// addTable.php - saves a new restaurant table from the Tables page (only the number of seats is needed;
// MySQL gives the table its number with AUTO_INCREMENT).
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only
require_once '../config.php';

$capacity = (int) ($_POST['capacity'] ?? 0);
if ($capacity < 1 || $capacity > 20) {
    header('Location: ../panel/table-panel.php?msg=error');
    exit;
}

$stmt = $link->prepare('INSERT INTO Restaurant_Tables (capacity, is_available) VALUES (?, 1)');
$stmt->bind_param('i', $capacity);
$stmt->execute();

header('Location: ../panel/table-panel.php?msg=added');
exit;
