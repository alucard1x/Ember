<?php
// login_process.php - checks the staff login (Accounts + Staffs tables) and shows success or error
// default values, so the page also works when you open it directly
$message = $message ?? '';
$iconClass = $iconClass ?? '';
$cardClass = $cardClass ?? '';
$bgColor = $bgColor ?? '';
$direction = $direction ?? '';

session_start(); // Ensure session is started
?>
<?php
require_once "../config.php";


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User-provided input
    $provided_account_id = $_POST['account_id'];
    $provided_password = $_POST['password'];

    // Query to fetch staff record based on provided account_id
    $stmt = $link->prepare("SELECT * FROM Accounts WHERE account_id = ?");
    $stmt->bind_param("s", $provided_account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        if ($provided_password === $stored_password) {
        // Password matches, login successful

        // Check if the account_id exists in the Staffs table
        $staff_stmt = $link->prepare("SELECT * FROM Staffs WHERE account_id = ?");
        $staff_stmt->bind_param("s", $provided_account_id);
        $staff_stmt->execute();
        $staff_result = $staff_stmt->get_result();

        if ($staff_result->num_rows === 1) {
            $staff_row = $staff_result->fetch_assoc();
            $logged_staff_name = $staff_row['staff_name']; // Get staff_name

            // After successful login, store staff_name in session
            $_SESSION['logged_account_id'] = $provided_account_id;
            $_SESSION['logged_staff_name'] = $logged_staff_name;

            //Directly go to the pos panel upon successful login
            header("Location: ../panel/pos-panel.php");
            exit;

        } else {
            // Staff ID not found in Staffs table
            $message = "Staff ID not found.<br>Please try again to choose a correct Staff ID.";
            $iconClass = "fa-times-circle";
            $cardClass = "alert-danger";
            $bgColor = "#FFA7A7"; // Custom background color for error
            $direction = "login.php"; // Fail, go back to login
            }

        } else {
            $message = "Incorrect password.<br>Please try again to type your password.";
            $iconClass = "fa-times-circle";
            $cardClass = "alert-danger";
            $bgColor = "#FFA7A7"; // Custom background color for error
            $direction = "login.php"; //Fail back to login
        }
    } else {
        $message = "Staff ID not found.<br>Please try again to choose a correct Staff ID.";
        $iconClass = "fa-times-circle";
        $cardClass = "alert-danger";
        $bgColor = "#FFA7A7";
        $direction = "login.php"; //Fail back to login
    }
}

// Close the database connection
$link->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>
        /* the message card */
        body {
            text-align: center;
            padding: 40px 0;
            background: #EBF0F5;
        }
        h1 {
            color: #88B04B;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-weight: 900;
            font-size: 40px;
            margin-bottom: 10px;
        }
        p {
            color: #404F5E;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-size: 20px;
            margin: 0;
        }
        i.checkmark {
            color: #9ABC66;
            font-size: 100px;
            line-height: 200px;
            margin-left: -15px;
        }
        .card {
            background: white;
            padding: 60px;
            border-radius: 4px;
            box-shadow: 0 2px 3px #C8D0D8;
            display: inline-block;
            margin: 0 auto;
        }
        /* green card for success, red card for error */
        .alert-success {

            background-color: <?php echo $bgColor; ?>;
        }
        .alert-success i {
            color: #5DBE6F;
        }
        .alert-danger {

            background-color: #FFA7A7;
        }
        .alert-danger i {
            color: #F25454;
        }
        .custom-x {
            color: #F25454;
            font-size: 100px;
            line-height: 200px;
        }
            .alert-box {
            max-width: 300px;
            margin: 0 auto;
        }

        .alert-icon {
            padding-bottom: 20px;
        }

    </style>
</head>
<body>
    <div class="card <?php echo $cardClass; ?>" style="display: none;">
        <div style="border-radius: 200px; height: 200px; width: 200px; background: #F8FAF5; margin: 0 auto;">
            <?php if ($iconClass === 'fa-check-circle'): ?>
                <i class="checkmark">✓</i>
            <?php else: ?>
                <i class="custom-x" style="font-size: 100px; line-height: 200px;">✘</i>
            <?php endif; ?>
        </div>
        <h1><?php echo ($cardClass === 'alert-success') ? 'Success' : 'Error'; ?></h1>
        <p><?php echo $message; ?></p>
    </div>

    <div style="text-align: center; margin-top: 20px;">Redirecting back in <span id="countdown">3</span></div>

    <script>
        // where to go after the message
        var direction = "<?php echo $direction; ?>";

        // show the message card and count down 3, 2, 1
        function showPopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "block";

            var i = 3;
            var countdownElement = document.getElementById("countdown");
            var countdownInterval = setInterval(function() {
                i--;
                countdownElement.textContent = i;
                if (i <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = direction;
                }
            }, 1000); // 1000 milliseconds = 1 second
        }

        // start when the page has loaded
        window.onload = showPopup;

        // hide the card after a few seconds
        function hidePopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "none";
            // then go to the next page
            setTimeout(function () {
                window.location.href = direction;
            }, 3000); // 3000 milliseconds = 3 seconds
        }

        // hide the card after 3 seconds
        setTimeout(hidePopup, 3000);
    </script>
</body>
</html>