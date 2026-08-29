<?php
/*
 * The Ember mark: wordmark, hairline, tagline.
 *
 * Shared by the loading curtain and the homepage hero on purpose. Both render
 * the same element in the same place, so when the curtain lifts the wordmark
 * does not move by a single pixel -- the rule and the tagline simply settle in
 * around it. That is what makes the intro read as one continuous moment
 * instead of a loader followed by a separate animation.
 *
 * The mark is deliberately not a link. It used to point at this very page,
 * which raised the transition curtain for a navigation that never happened.
 *
 * Set $markVariant before including:
 *     'loader'  the curtain -- word only, the rest holds its space
 *     'hero'    the homepage -- the full mark, animated in on reveal
 */
$markVariant = $markVariant ?? 'hero';
?>
<div class="brand-mark brand-mark--<?= $markVariant ?>">
    <span class="brand-word">Ember</span>
    <span class="brand-rule"></span>
    <span class="brand-tagline">Dining &amp; Bar</span>
</div>
