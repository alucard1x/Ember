<?php
// addDish.php - saves a new dish from the Menu page.
// The dish code (item_id, e.g. "BP12") is made automatically from the course:
// the letters other dishes in that course use, plus the next free number.
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';   // staff only
require_once '../config.php';

$name = trim($_POST['item_name'] ?? '');
$course = trim($_POST['item_type'] ?? '');
$category = trim($_POST['item_category'] ?? '');
$price = (float) ($_POST['item_price'] ?? 0);
$description = trim($_POST['item_description'] ?? '');
$back = '../panel/menu-panel.php';

if ($name == '' || $course == '' || $category == '' || $price <= 0) {
    header('Location: ' . $back . '?msg=error');
    exit;
}

// 1. the letters: copied from a dish of the same course, or made from the course name
$stmt = $link->prepare('SELECT item_id FROM Menu WHERE item_type = ? LIMIT 1');
$stmt->bind_param('s', $course);
$stmt->execute();
$example = $stmt->get_result()->fetch_assoc();
if ($example) {
    $letters = preg_replace('/[0-9]+$/', '', $example['item_id']);      // "BP8" → "BP"
} else {
    $words = preg_split('/[^A-Za-z]+/', $course, -1, PREG_SPLIT_NO_EMPTY);
    $letters = strtoupper(substr($words[0] ?? 'X', 0, 1) . substr($words[1] ?? $words[0] ?? 'X', 0, 1));
}

$letters = substr($letters, 0, 3);     // item_id holds at most 6 characters

// 2. the next number for those letters
$number = 1;
$like = $letters . '%';
$stmt = $link->prepare('SELECT item_id FROM Menu WHERE item_id LIKE ?');
$stmt->bind_param('s', $like);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $digits = substr($row['item_id'], strlen($letters));
    if (ctype_digit($digits) && (int) $digits >= $number) {
        $number = (int) $digits + 1;
    }
}
$item_id = $letters . $number;

// 3. save the dish
$stmt = $link->prepare('INSERT INTO Menu (item_id, item_name, item_type, item_category, item_price, item_description) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssds', $item_id, $name, $course, $category, $price, $description);
if ($stmt->execute()) {
    header('Location: ' . $back . '?msg=added');
} else {
    header('Location: ' . $back . '?msg=error');
}
exit;
