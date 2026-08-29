<?php
/**
 * Public entry point.
 *
 * Deliberately does no database work. The schema bootstrap that used to run
 * here now lives in database/setup_local.php, which only runs on localhost —
 * a public homepage should not be probing the database or reporting its
 * internal state to every visitor.
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ember Dining &amp; Bar</title>
    <style>
        :root {
            --brand-accent: #b1462f;
            --brand-ink: #14100f;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            font-family: 'Montserrat', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #f3efec;
            background-color: var(--brand-ink);
            background-image: linear-gradient(120deg, rgba(20, 16, 15, 0.9) 0%, rgba(20, 16, 15, 0.6) 100%),
                              url('customerSide/image/hero-background.jpg');
            background-size: cover;
            background-position: center;
        }
        .setup-card {
            width: 100%;
            max-width: 620px;
            text-align: center;
        }
        .setup-card .wordmark {
            font-family: Copperplate, Copperplate Gothic Light, Optima, Palatino Linotype, Georgia, serif;
            font-size: 4.4rem;
            letter-spacing: 0.5rem;
            font-weight: 400;
        }
        .setup-card .tagline {
            font-size: 0.85rem;
            letter-spacing: 0.55rem;
            font-weight: 300;
            text-transform: uppercase;
            color: rgba(243, 239, 236, 0.7);
            margin-top: 10px;
        }
        .setup-card .rule {
            width: 60px;
            height: 1px;
            background-color: var(--brand-accent);
            margin: 30px auto;
        }
        .entry-links {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .entry-links a {
            display: inline-block;
            padding: 12px 34px;
            font-size: 0.8rem;
            letter-spacing: 0.35rem;
            text-transform: uppercase;
            text-decoration: none;
            color: #f3efec;
            border: 1px solid var(--brand-accent);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .entry-links a:hover,
        .entry-links a:focus-visible {
            background-color: var(--brand-accent);
            color: #fff;
        }
    </style>
</head>

<body>
    <main class="setup-card">
        <h1 class="wordmark">Ember</h1>
        <p class="tagline">Dining &amp; Bar</p>
        <div class="rule"></div>

        <nav class="entry-links">
            <a href="customerSide/home/home.php">Home</a>
            <a href="adminSide/StaffLogin/login.php">Staff Login</a>
        </nav>
    </main>
</body>

</html>
