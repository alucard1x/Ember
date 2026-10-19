<?php
// register.php - "Create Account" page (adds a row to Accounts and to Memberships)
require_once '../config.php';   // gives us $link
session_start();

$email = "";
$member_name = "";
$password = "";
$phone_number = "";

$email_err = "";
$member_name_err = "";
$password_err = "";
$phone_number_err = "";

// this part runs only when the form is sent
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. check the email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else if (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email. Ex: johndoe@email.com";
    } else {
        $email = trim($_POST["email"]);
    }

    // is this email already used by another account?
    $selectCreatedEmail = "SELECT email from Accounts WHERE email = ?";
    if ($stmt = $link->prepare($selectCreatedEmail)) {
        $stmt->bind_param("s", $_POST['email']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $email_err = "This email is already registered.";
        } else {
            $email = trim($_POST["email"]);
        }
        $stmt->close();
    }

    // 2. check the name
    if (empty(trim($_POST["member_name"]))) {
        $member_name_err = "Please enter your member name.";
    } else {
        $member_name = trim($_POST["member_name"]);
    }

    // 3. check the password (at least 6 characters)
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 4. check the phone number (only numbers)
    if (empty(trim($_POST["phone_number"]))) {
        $phone_number_err = "Please enter your phone number.";
    } else if (!is_numeric(trim($_POST['phone_number']))) {
        $phone_number_err = "Only enter numeric values!";
    } else {
        $phone_number = trim($_POST["phone_number"]);
    }

    // 5. no errors -> save the new member
    if (empty($email_err) && empty($member_name_err) && empty($password_err) && empty($phone_number_err)) {

        // transaction = both inserts work, or neither is saved
        mysqli_begin_transaction($link);

        // first the account (password is saved as plain text, like the rest of this project)
        $sql_accounts = "INSERT INTO Accounts (email, password, phone_number, register_date) VALUES (?, ?, ?, NOW())";
        if ($stmt_accounts = mysqli_prepare($link, $sql_accounts)) {
            mysqli_stmt_bind_param($stmt_accounts, "sss", $email, $password, $phone_number);

            if (mysqli_stmt_execute($stmt_accounts)) {
                // the id MySQL gave to the new account
                $last_account_id = mysqli_insert_id($link);

                // then the membership, starting with 0 points
                $sql_memberships = "INSERT INTO Memberships (member_name, points, account_id) VALUES (?, ?, ?)";
                if ($stmt_memberships = mysqli_prepare($link, $sql_memberships)) {
                    $points = 0;
                    mysqli_stmt_bind_param($stmt_memberships, "sii", $member_name, $points, $last_account_id);

                    if (mysqli_stmt_execute($stmt_memberships)) {
                        // both worked -> save and show the success page
                        mysqli_commit($link);
                        header("location: register_process.php");
                        exit;
                    } else {
                        mysqli_rollback($link);   // undo the account
                        echo "Oops! Something went wrong. Please try again later.";
                    }

                    mysqli_stmt_close($stmt_memberships);
                }
            } else {
                mysqli_rollback($link);
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt_accounts);
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
    <title>Create Account &mdash; Ember</title>
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
        <p class="sl-kicker">Membership</p>
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
        <h1 class="sl-ticket__title" id="sl-ticket-title">Create account</h1>

        <form action="register.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" class="form-control">
                <span class="text-danger"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group">
                <label for="member_name">Name</label>
                <input type="text" id="member_name" name="member_name" class="form-control">
                <span class="text-danger"><?php echo $member_name_err; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control">
                <span class="text-danger"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group">
                <label for="phone_number">Phone number</label>
                <input type="text" id="phone_number" name="phone_number" class="form-control">
                <span class="text-danger"><?php echo $phone_number_err; ?></span>
            </div>

            <button class="btn sl-btn" type="submit" name="register" value="Register">Create account</button>
        </form>

        <p class="sl-ticket__foot">Already a member? <a href="login.php">Sign in</a></p>
    </section>

</main>

</body>
</html>
