<?php
// about.php - "Our Story" page (the numbers in the text come from the database)

$pageTitle = 'Our Story';
$pageDesc  = 'Three fires and a long card. How Ember cooks: tandoor, coal and dum, the coast, the street, and a bar built on Indian spirits.';
include_once('../components/header.php');


// counts how many dishes are in one course (item_type) of the Menu table
function count_dishes_of_type($link, $type) {
    $stmt = mysqli_prepare($link, "SELECT COUNT(*) AS n FROM Menu WHERE item_type = ?");
    mysqli_stmt_bind_param($stmt, 's', $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['n'];
}

$biryaniCount = count_dishes_of_type($link, 'Biryani & Pulao');
$tandoorCount = count_dishes_of_type($link, 'Tandoori & Kebabs');
$streetCount  = count_dishes_of_type($link, 'Rolls & Street Bites');

// how many different courses there are
$result = mysqli_query($link, 'SELECT COUNT(DISTINCT item_type) AS n FROM Menu');
$row = mysqli_fetch_assoc($result);
$courseCount = (int) $row['n'];
?>

<section class="e-section" aria-labelledby="story-heading">
    <div class="e-shell">
        <div class="e-section__head">
            <span class="e-eyebrow e-eyebrow--ember" data-reveal>Our Story</span>
            <h1 class="e-display" id="story-heading" data-reveal>Three fires,<br>and a long card.</h1>
            <p class="e-body" data-reveal>
                Ember is a modern Indian kitchen in Vellore. The card runs to
                <?= ember_word($courseCount) ?> courses, and almost all of it
                passes through one of three heats on its way to the table.
            </p>
        </div>
    </div>
</section>

<!-- 1. The fire -->
<section class="e-section" aria-labelledby="fire-heading">
    <div class="e-shell">
        <div class="e-spread">
            <div class="e-spread__body">
                <span class="e-eyebrow e-eyebrow--ember" data-reveal>Tandoor &amp; Coal</span>
                <h2 class="e-display" id="fire-heading" data-reveal>The first heat.</h2>
                <p class="e-lede" data-reveal>
                    The kebab list runs to <?= ember_word($tandoorCount) ?> &mdash; a raan roasted
                    slowly on the leg, barrah chops, galouti, and lamb ribs.
                    The chicken, the prawns and the fish take the same heat.
                </p>
            </div>
            <figure class="e-figure e-figure--wide e-spread__figure" data-reveal="image">
                <?php ember_picture('grill-coals', 'A seekh kebab on black slate with mint chutney', '(max-width: 1100px) 100vw, 620px'); ?>
                <figcaption>The grill</figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- 2. The dum pot -->
<section class="e-section" aria-labelledby="dum-heading">
    <div class="e-shell">
        <div class="e-spread e-spread--flip">
            <div class="e-spread__body">
                <span class="e-eyebrow e-eyebrow--ember" data-reveal>Dum</span>
                <h2 class="e-display" id="dum-heading" data-reveal>The second heat.</h2>
                <p class="e-lede" data-reveal>
                    <?= ucfirst(ember_word($biryaniCount)) ?> biryanis and pulaos, sealed and
                    finished on a low fire &mdash; Hyderabadi, Lucknowi, Awadhi,
                    Kashmiri, Malabar, and the kitchen&rsquo;s own.
                </p>
            </div>
            <figure class="e-figure e-figure--wide e-spread__figure" data-reveal="image">
                <?php ember_picture('biryani', 'Biryani in a copper handi with raita and salad', '(max-width: 1100px) 100vw, 620px'); ?>
                <figcaption>Biryani</figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- 3. The coast and the street -->
<section class="e-section" aria-labelledby="coast-heading">
    <div class="e-shell">
        <div class="e-spread">
            <div class="e-spread__body">
                <span class="e-eyebrow e-eyebrow--ember" data-reveal>The Coast &amp; The Street</span>
                <h2 class="e-display" id="coast-heading" data-reveal>Everything else.</h2>
                <p class="e-lede" data-reveal>
                    Goa and Malabar arrive with the fish; Amritsar and Koliwada with
                    the fry. Then <?= ember_word($streetCount) ?> rolls and street
                    plates, because not every good thing needs a tablecloth.
                </p>
            </div>
            <figure class="e-figure e-figure--wide e-spread__figure" data-reveal="image">
                <?php ember_picture('fish-curry', 'A curry served in a copper handi', '(max-width: 1100px) 100vw, 620px'); ?>
                <figcaption>The curry</figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- 4. The bar -->
<section class="e-section" aria-labelledby="bar-heading">
    <div class="e-shell">
        <div class="e-spread e-spread--flip">
            <div class="e-spread__body">
                <span class="e-eyebrow e-eyebrow--ember" data-reveal>The Bar</span>
                <h2 class="e-display" id="bar-heading" data-reveal>Indian, top to bottom.</h2>
                <p class="e-lede" data-reveal>
                    Single malts from Amrut, Rampur, Paul John and Indri; a desi
                    cocktail list; and a short house list of our own, poured under
                    the same lights.
                </p>
            </div>
            <figure class="e-figure e-figure--wide e-spread__figure" data-reveal="image">
                <?php ember_picture('cocktail-pour', 'Whisky poured over a large ice cube', '(max-width: 1100px) 100vw, 620px'); ?>
                <figcaption>The bar</figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- full width photo -->
<section aria-label="The room">
    <div class="e-plate e-plate--bleed" data-reveal="image">
        <?php ember_picture('bulbs-ceiling', 'A ceiling hung with glowing bulbs', '100vw'); ?>
    </div>
</section>

<!-- reserve a table -->
<section class="e-section" aria-labelledby="story-reserve">
    <div class="e-shell">
        <div class="e-reserve">
            <span class="e-eyebrow e-eyebrow--ember" data-reveal>Reservations</span>
            <h2 class="e-display" id="story-reserve" data-reveal>Come and see.</h2>
            <p data-reveal>
                <a class="e-cta" href="../CustomerReservation/reservePage.php">
                    Reserve a table
                    <span class="e-cta__arrow" aria-hidden="true"></span>
                </a>
            </p>
            <p class="e-reserve__hours" data-reveal>Open seven days &middot; 11:00 &ndash; 22:00 &middot; Area 51, Vellore</p>
        </div>
    </div>
</section>

<?php include_once('../components/footer.php'); ?>
