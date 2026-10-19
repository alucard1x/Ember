<?php
// register_process.php - "Success" card shown after a new account is made, then goes to login.php after 3 seconds
session_start();
require_once '../config.php';
$link->close();   // this page does not need the database
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>
        /* the green success card */
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
        .alert-success {
            background-color: #D4F4DD;
        }
        .alert-success i {
            color: #5DBE6F;
        }
    </style>
</head>
<body>
    <div class="card alert-success" style="display: none;">
        <div style="border-radius: 200px; height: 200px; width: 200px; background: #F8FAF5; margin: 0 auto;">
            <i class="checkmark">✓</i>
        </div>
        <h1>Success</h1>
        <p>Register successful.<br>Welcome to Ember Dining & Bar.<br>Please Login with your Account.</p>
    </div>

    <div style="text-align: center; margin-top: 20px;">Redirecting back in <span id="countdown">3</span></div>

    <script>
        // where to go after the countdown
        var direction = "login.php";

        // show the card and count down 3, 2, 1, then go to the login page
        function showPopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "block";

            var i = 3;
            var countdownElement = document.getElementById("countdown");
            var countdownInterval = setInterval(function () {
                i--;
                countdownElement.textContent = i;
                if (i <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = direction;
                }
            }, 1000);
        }
        window.onload = showPopup;

        // after 3 seconds hide the card (and go to the login page if we are still here)
        function hidePopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "none";
            setTimeout(function () {
                window.location.href = direction;
            }, 3000);
        }
        setTimeout(hidePopup, 3000);
    </script>
</body>
</html>
