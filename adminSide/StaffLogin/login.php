<?php
// login.php - staff login page (the form)
// Left: the Ember name with today's date and service hours. Right: the form in a glass panel like the homepage. Styles: customerSide/css/entrance.css (shared with the member login and create account pages)
// default values, so the page also works when you open it directly
$account_id = $account_id ?? '';

session_start();
if(isset($_SESSION['logged_account_id'])) {
    header("Location: ../panel/pos-panel.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14100f">
    <title>Staff Login &mdash; Ember</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- the design of the whole site -->
    <link rel="stylesheet" href="../../customerSide/css/style.css?v=<?= @filemtime(__DIR__ . '/../../customerSide/css/style.css') ?>">
    <!-- same look as the new homepage -->
    <link rel="stylesheet" href="../../customerSide/css/ember-v2.css?v=<?= @filemtime(__DIR__ . '/../../customerSide/css/ember-v2.css') ?>">
    <!-- layout shared by the three sign-in pages -->
    <link rel="stylesheet" href="../../customerSide/css/entrance.css?v=<?= @filemtime(__DIR__ . '/../../customerSide/css/entrance.css') ?>">
</head>
<body class="sl-body">
<?php $bgAssets = '../../customerSide/image'; include '../../customerSide/components/background.php'; ?>
<?php $brandHome = '../../customerSide/home/home.php'; include '../../customerSide/components/brandLink.php'; ?>

<main class="sl">

    <!-- left: the restaurant's name, quietly, with today's date and service hours -->
    <section class="sl-intro">
        <p class="sl-kicker">Staff entrance</p>
        <a class="sl-mark" href="../../customerSide/home/home.php" aria-label="Ember homepage">Ember</a>
        <span class="sl-rule" aria-hidden="true"></span>
        <dl class="sl-meta">
            <div><dt>Today</dt><dd data-today>&nbsp;</dd></div>
            <div><dt>Time</dt><dd data-clock>--:--</dd></div>
            <div><dt>Service</dt><dd>11:00 &ndash; 22:00</dd></div>
        </dl>
    </section>

    <!-- right: the form, in the same glass panel as the homepage hero card -->
    <section class="sl-ticket" aria-labelledby="sl-ticket-title">
        <h2 class="sl-ticket__title" id="sl-ticket-title">Sign in</h2>

        <?php
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form action="login_process.php" method="post">
            <div class="form-group">
                <label for="account_id">Staff account ID</label>
                <input type="number" id="account_id" name="account_id" placeholder="" required class="form-control <?php echo (!empty($account_id)) ? 'is-invalid' : ''; ?>" value="<?php echo $account_id; ?>">
                <span class="invalid-feedback"><?php echo $account_id; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="" required class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            </div>

            <div class="form-group">
                <button class="btn sl-btn" type="submit" name="submit" value="Login">Sign in</button>
            </div>
        </form>

    </section>

</main>

<script>
// today's date and the time
(function () {
    var clock = document.querySelector('[data-clock]');
    var today = document.querySelector('[data-today]');
    function two(n) { return (n < 10 ? '0' : '') + n; }
    function tick() {
        var now = new Date();
        clock.textContent = two(now.getHours()) + ':' + two(now.getMinutes());
        today.textContent = now.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
    }
    tick();
    setInterval(tick, 15000);
})();
</script>
</body>
</html>
