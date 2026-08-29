<?php
/* defaults so the page also renders on a direct visit */
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
    <title>Staff Log In &mdash; Ember Dining &amp; Bar</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
         body {
            font-family: 'Montserrat', sans-serif;
            color: white;
            background-color: #1C1427;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .wrapper {
            width: 360px;
            padding: 20px;
        }
        h2{
            text-align: center;
        }
        
        
    </style>
</head>
<body>
<?php $bgAssets = '../../customerSide/image'; include '../../customerSide/components/background.php'; ?>
<?php $brandHome = '../../customerSide/home/home.php'; include '../../customerSide/components/brandLink.php'; ?>
    <section id="signup">
    <div class="container my-6">
    <div class="wrapper site-panel">
        <h1 class="panel-title">Staff Login</h1>
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

    <form action="login_process.php" method="post" >
        <div class="form-group">
            <label for="account_id">Staff Account ID</label>
            <input type="number" id="account_id" name="account_id" placeholder="Enter Account ID" required class="form-control <?php echo (!empty($account_id)) ? 'is-invalid' : ''; ?>" value="<?php echo $account_id; ?>">
            <span class="invalid-feedback"><?php echo $account_id; ?></span>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
        </div>
            
            <div class="form-group">
                <button class="btn" type="submit" name="submit" value="Login">Login</button>
            </div>
    </form>
    </div>
    </div>
    </section>
</body>
</html>
