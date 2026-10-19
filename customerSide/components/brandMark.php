<?php
// brandMark.php - the big EMBER logo (used on the homepage and on the loading screen)
// before including this file set $markVariant to 'hero' (homepage) or 'loader' (loading screen)

if (!isset($markVariant)) {
    $markVariant = 'hero';
}

// on the homepage the logo is the main heading (h1), on the loading screen it is not
if ($markVariant == 'hero') {
    $markTag = 'h1';
} else {
    $markTag = 'span';
}
?>
<div class="brand-mark brand-mark--<?= $markVariant ?>">
    <<?= $markTag ?> class="brand-word">Ember</<?= $markTag ?>>
    <span class="brand-rule"></span>
    <span class="brand-tagline">Dining &amp; Bar</span>
</div>
