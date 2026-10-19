<?php
// login.php - member login page (checks email and password in the Accounts table)
require_once '../config.php';   // gives us $link (the database connection)
session_start();

$email = "";
$password = "";
$email_err = "";
$password_err = "";

// this part runs only when the form is sent
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. check that both boxes are filled in
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 2. look for the account with this email
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT * FROM Accounts WHERE email = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_assoc($result);

                    // 3. check the password
                    if ($password === $row["password"]) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["email"] = $email;

                        // 4. find the membership of this account
                        $sql_member = "SELECT * FROM Memberships WHERE account_id = " . $row['account_id'];
                        $result_member = mysqli_query($link, $sql_member);

                        if ($result_member) {
                            $membership_row = mysqli_fetch_assoc($result_member);

                            if ($membership_row) {
                                // logged in! go to the home page
                                $_SESSION["account_id"] = $membership_row["account_id"];
                                header("location: ../home/home.php");
                                exit;
                            } else {
                                $password_err = "No membership details found for this account.";
                            }
                        } else {
                            $password_err = "Error fetching membership details: " . mysqli_error($link);
                        }
                    } else {
                        $password_err = "Invalid password. Please try again.";
                    }
                } else {
                    $email_err = "No account found with this email.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14100f">
    <title>Member Login &mdash; Ember</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- the design of the whole site -->
    <link rel="stylesheet" href="../css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>">
    <!-- same look as the new homepage -->
    <link rel="stylesheet" href="../css/ember-v2.css?v=<?= @filemtime(__DIR__ . '/../css/ember-v2.css') ?>">
    <!-- layout shared by the three sign-in pages -->
    <link rel="stylesheet" href="../css/entrance.css?v=<?= @filemtime(__DIR__ . '/../css/entrance.css') ?>">
</head>
<body class="sl-body">
<?php $bgAssets = '../image'; include '../components/background.php'; ?>
<?php $brandHome = '../home/home.php'; include '../components/brandLink.php'; ?>
<main class="sl">

    <!-- left: the Ember name with what membership gives you -->
    <section class="sl-intro">
        <p class="sl-kicker">Members</p>
        <a class="sl-mark" href="../home/home.php" aria-label="Ember homepage">Ember</a>
        <span class="sl-rule" aria-hidden="true"></span>
        <dl class="sl-meta">
            <div><dt>Points</dt><dd>1 for every &#8377;1</dd></div>
            <div><dt>VIP</dt><dd>1,000 points</dd></div>
            <div><dt>Service</dt><dd>11:00 &ndash; 22:00</dd></div>
        </dl>
    </section>

    <!-- right: the form, in the same glass panel as the homepage hero card -->
    <section class="sl-ticket" aria-labelledby="sl-ticket-title">
        <h1 class="sl-ticket__title" id="sl-ticket-title">Sign in</h1>

        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
                <span class="text-danger"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <span class="text-danger"><?php echo $password_err; ?></span>
            </div>

            <button class="btn sl-btn" type="submit" name="submit" value="Login">Sign in</button>
        </form>

        <p class="sl-ticket__foot">New here? <a href="register.php">Create an account</a></p>
    </section>

</main>

</body>
</html>
