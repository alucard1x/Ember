<?php
// profile.php - shows the signed-in member's details (Memberships + Accounts tables)
require_once '../config.php';   // gives us $link
session_start();

// only for signed-in members
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['account_id'];

// get the name and points (Memberships) and email, phone, date (Accounts)
$query = "SELECT m.member_name, m.points, a.email, a.phone_number, a.register_date
          FROM Memberships AS m
          INNER JOIN Accounts AS a ON m.account_id = a.account_id
          WHERE m.account_id = ?";

$stmt = $link->prepare($query);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
} else {
    echo 'Error: ' . $stmt->error;
}

$stmt->close();
$link->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14100f">
    <title>My Profile &mdash; Ember</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- the design of the whole site -->
    <link rel="stylesheet" href="../css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>">
    <!-- same look as the new homepage -->
    <link rel="stylesheet" href="../css/ember-v2.css?v=<?= @filemtime(__DIR__ . '/../css/ember-v2.css') ?>">
</head>
<body class="has-site-panel">
<?php $bgAssets = '../image'; include '../components/background.php'; ?>
<?php $brandHome = '../home/home.php'; include '../components/brandLink.php'; ?>
    <div class="site-panel profile-card">
        <h2>My Profile</h2>
        <p><span class="label">Name</span><?php echo $row['member_name']; ?></p>
        <p><span class="label">Email</span><?php echo $row['email']; ?></p>
        <p><span class="label">Phone Number</span><?php echo $row['phone_number']; ?></p>
        <p><span class="label">Points</span><?php echo $row['points']; ?></p>
        <p><span class="label">Member Since</span><?php echo $row['register_date']; ?></p>
        <a href="../home/home.php">Back to Site</a>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
