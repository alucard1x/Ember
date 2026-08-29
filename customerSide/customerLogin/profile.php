<?php
// Include your database connection code here
require_once '../config.php';
$conn = $link; // config.php exposes the connection as $link

// Check if the user is logged in
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to the login page if not logged in
    exit;
}

// Fetch the user's profile information
$user_id = $_SESSION['account_id'];

$query = "SELECT m.member_name, m.points, a.email, a.phone_number, a.register_date
          FROM Memberships AS m
          INNER JOIN Accounts AS a ON m.account_id = a.account_id
          WHERE m.account_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
} else {
    echo 'Error: ' . $stmt->error;
    // Handle the error gracefully
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile &mdash; Ember Dining &amp; Bar</title>
    <style>
        body {
            font-family: 'Montserrat', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .profile-card {
            max-width: 460px;
        }
        .profile-card h2 {
            font-weight: 400;
            letter-spacing: 0.25rem;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .profile-card p {
            margin: 10px 0;
            color: #ddd5d0;
        }
        .profile-card .label {
            display: inline-block;
            min-width: 130px;
            color: #b9aea8;
        }
        .profile-card a {
            display: inline-block;
            margin-top: 26px;
            margin-right: 14px;
            padding: 10px 26px;
            font-size: 13px;
            letter-spacing: 0.25rem;
            text-transform: uppercase;
            text-decoration: none;
            color: #f3efec;
            border: 1px solid #b1462f;
        }
        .profile-card a:hover {
            background-color: #b1462f;
            color: #ffffff;
        }
    </style>
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
