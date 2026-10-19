<?php
// brandLink.php - the "Ember" logo link at the top of the form pages (login, register, reservation...)
// It looks and sits exactly like the logo in the header bar (.site-brand in style.css).
// before including this file set $brandHome to the path of the homepage

if (!isset($brandHome)) {
    $brandHome = '../home/home.php';
}
?>
<a class="site-brand" href="<?= $brandHome ?>">Ember</a>
