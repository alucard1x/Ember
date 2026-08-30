<?php
/*
 * Top-left Ember wordmark, linking home.
 *
 * For the standalone pages (login, register, profile, reservation, staff
 * login) that do not include components/header.php but still need one route
 * back to the homepage.
 *
 * Set $brandHome to the path of customerSide/home/home.php relative to the
 * page before including, e.g.
 *     $brandHome = '../home/home.php';                      // customerSide/<folder>/
 *     $brandHome = '../../customerSide/home/home.php';      // adminSide/<folder>/
 */
$brandHome = $brandHome ?? '../home/home.php';
?>
<a class="site-brand" href="<?= $brandHome ?>">Ember</a>

<style>
    .site-brand {
        position: fixed;
        top: 26px;
        left: 40px;
        z-index: 900;
        /* Cinzel sits after the installed faces as the Android fallback --
           see --brand-font in css/style.css for the full reasoning. */
        font-family: Copperplate, 'Copperplate Gothic Light', Optima, 'Palatino Linotype', 'Cinzel', Georgia, serif;
        font-size: 2rem;
        letter-spacing: 0.3rem;
        text-transform: uppercase;
        text-decoration: none;
        color: #f3efec;
        transition: color 0.3s ease;
    }
    .site-brand:hover {
        color: #b1462f;
    }
    @media only screen and (max-width: 767px) {
        .site-brand {
            top: 18px;
            left: 20px;
            font-size: 1.6rem;
        }
    }
</style>
