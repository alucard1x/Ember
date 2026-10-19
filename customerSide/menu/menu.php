<?php
// menu.php - the menu page (all dishes grouped by course) and "Your Order" (the cart)

$pageTitle = 'Menu';
$pageDesc  = 'Tandoor and coal, dum-cooked biryani, the coast and the street — the full Ember menu.';
include_once('../components/header.php');   // also gives us $mainDishes, $sides and $drinks


// all dishes in one list
$allDishes = array_merge($mainDishes, $sides, $drinks);


// ---------- YOUR ORDER (cart) ----------
// the cart is saved in the session like this: [item_id => quantity]
// cart.php adds and removes items, nothing is saved in the database

$cart = [];
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}

// find a dish by its id quickly
$dishById = [];
foreach ($allDishes as $dish) {
    $dishById[$dish['item_id']] = $dish;
}

// one line of the bill for each dish in the cart
$billLines = [];
$beforeTax = 0;
foreach ($cart as $itemId => $quantity) {
    if (isset($dishById[$itemId])) {
        $price = (float) $dishById[$itemId]['item_price'];
        $lineTotal = $price * (int) $quantity;

        $billLines[] = [
            'id'       => $itemId,
            'name'     => $dishById[$itemId]['item_name'],
            'price'    => $price,
            'quantity' => (int) $quantity,
            'total'    => $lineTotal,
        ];
        $beforeTax = $beforeTax + $lineTotal;
    }
}
$taxAmount = $beforeTax * 0.1;          // 10% tax
$grandTotal = $beforeTax + $taxAmount;


// ---------- group the dishes by course (item_type) ----------

$coursesByName = [];    // course name => ['category' => ..., 'items' => [...]]
foreach ($allDishes as $dish) {
    $type = $dish['item_type'];
    if (!isset($coursesByName[$type])) {
        $coursesByName[$type] = [
            'category' => $dish['item_category'],
            'items' => [],
        ];
    }
    $coursesByName[$type]['items'][] = $dish;
}

// the order the courses are shown on the page
$courseOrder = [
    'Chaat & Starters',
    'Tandoori & Kebabs',
    'Murg Specialities',
    'Gosht Specialities',
    'Machli & Jhinga',
    'Biryani & Pulao',
    'Rolls & Street Bites',
    'Indian Breads & Sides',
    'Kachumber & Salads',
    'Mithai & Desserts',
    'Little Maharaja',
    'Ember Cocktails',
    'Desi Cocktails',
    'Sharbat & Mocktails',
    'Fresh Juices',
    'Indian Spirits',
];

// first the courses in the list above...
$courses = [];
foreach ($courseOrder as $courseName) {
    if (isset($coursesByName[$courseName])) {
        $courses[$courseName] = $coursesByName[$courseName];
    }
}
// ...then any new course that is not in the list, in A-Z order
$otherCourses = [];
foreach ($coursesByName as $courseName => $course) {
    if (!in_array($courseName, $courseOrder, true)) {
        $otherCourses[$courseName] = $course;
    }
}
ksort($otherCourses, SORT_STRING);
foreach ($otherCourses as $courseName => $course) {
    $courses[$courseName] = $course;
}


// filter buttons: database category => label on the button
$filters = [
    'all'          => 'All',
    'Main Dishes'  => 'Mains',
    'Side Snacks'  => 'Sides',
    'Drinks'       => 'Bar',
];

// QR ordering: table.php remembers the guest's table in the session
$orderTable = 0;
if (isset($_SESSION['order_table'])) {
    $orderTable = (int) $_SESSION['order_table'];
}
$hasTrackedOrder = isset($_SESSION['track_bill']);
?>

<section class="e-section" aria-labelledby="menu-heading">
    <div class="e-shell">

<?php if ($orderTable > 0) { ?>
        <!-- shown only after scanning a table's QR code -->
        <p class="e-table-note">
            <span class="e-table-note__dot" aria-hidden="true"></span>
            Ordering for Table <?= $orderTable ?> &middot; your order goes straight to the kitchen
<?php   if ($hasTrackedOrder) { ?>
            &middot; <a href="../order/track.php">Track your order</a>
<?php   } ?>
        </p>
<?php } ?>
        <div class="e-section__head">
            <span class="e-eyebrow e-eyebrow--ember" data-reveal>The Menu</span>
            <!-- the number of courses is counted, not typed -->
            <h1 class="e-display" id="menu-heading" data-reveal><?= ucfirst(ember_word(count($courses))) ?> courses,<br>one fire.</h1>
        </div>

        <div data-menu>
            <!-- filter buttons and search box (app.js makes them work) -->
            <div class="e-menu__filter">
<?php foreach ($filters as $value => $label) {
        $pressed = 'false';
        if ($value == 'all') {
            $pressed = 'true';
        }
?>
                <button type="button" data-course="<?= htmlspecialchars($value, ENT_QUOTES) ?>"
                        aria-pressed="<?= $pressed ?>"><?= $label ?></button>
<?php } ?>
                <div class="e-menu__search">
                    <label class="e-visually-hidden" for="menu-search">Search dishes</label>
                    <input type="search" id="menu-search" data-menu-search
                           placeholder="Search" autocomplete="off">
                </div>
            </div>

<?php // one section for each course
foreach ($courses as $type => $course) {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($type));   // e.g. "biryani-pulao"
?>
            <section class="e-course" aria-labelledby="course-<?= $slug ?>">
                <div class="e-course__head">
                    <h2 class="e-eyebrow" id="course-<?= $slug ?>"><?= htmlspecialchars($type) ?></h2>
                    <span class="e-rule" aria-hidden="true"></span>
                </div>

                <ul class="e-dishes">
<?php   // one row for each dish
        foreach ($course['items'] as $item) {
            $note = ember_dish_note($item['item_description'], $item['item_name'], $type);
?>
                    <li class="e-dish"
                        data-category="<?= htmlspecialchars($course['category'], ENT_QUOTES) ?>"
                        data-name="<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>">
                        <span class="e-dish__name"><?= htmlspecialchars($item['item_name']) ?></span>
                        <span class="e-dish__price">&#8377;<?= number_format((float) $item['item_price']) ?></span>
                        <a class="e-dish__add" href="cart.php?add=<?= urlencode($item['item_id']) ?>">
                            Add<span class="e-visually-hidden"> <?= htmlspecialchars($item['item_name']) ?> to your order</span>
                        </a>
<?php       if ($note != '') { ?>
                        <span class="e-dish__desc"><?= htmlspecialchars($note) ?></span>
<?php       } ?>
                    </li>
<?php   } ?>
                </ul>
            </section>
<?php } ?>

            <!-- shown by app.js when the search finds nothing -->
            <p class="e-bill__empty" data-menu-empty hidden>Nothing on the card matches that.</p>
        </div>
    </div>
</section>

<!-- full width photo between the menu and the order -->
<section aria-label="The grill">
    <div class="e-plate e-plate--bleed" data-reveal="image">
        <?php ember_picture('spit-flame', 'Flames rising from a wood fire', '100vw'); ?>
    </div>
</section>

<!-- YOUR ORDER. id="bill" is needed because cart.php sends you back to menu.php#bill -->
<section class="e-section" id="bill" aria-labelledby="order-heading">
    <div class="e-shell">
        <div class="e-section__head">
            <span class="e-eyebrow e-eyebrow--ember" data-reveal>Your Order</span>
            <h2 class="e-display" id="order-heading" data-reveal>
<?php if (count($billLines) == 0) { ?>
                Nothing yet.
<?php } elseif (count($billLines) == 1) { ?>
                1 item.
<?php } else { ?>
                <?= count($billLines) ?> items.
<?php } ?>
            </h2>
        </div>

<?php if (count($billLines) == 0) { ?>
<?php   if ($orderTable > 0) { ?>
        <p class="e-bill__empty" data-reveal>
            Use Add beside any dish above, then press Send to kitchen.
        </p>
<?php   } else { ?>
        <p class="e-bill__empty" data-reveal>
            Use Add beside any dish above to start an order. It is kept to this
            browser only &mdash; nothing is sent to the kitchen.
        </p>
<?php   } ?>
<?php   if ($hasTrackedOrder) { ?>
        <p style="margin-top: 2rem;"><a class="e-link" href="../order/track.php">Track your order</a></p>
<?php   } ?>
<?php } else { ?>
        <div class="e-bill" data-reveal-group>
<?php   foreach ($billLines as $line) { ?>
            <div class="e-bill__row" data-reveal>
                <span class="e-bill__name"><?= htmlspecialchars($line['name']) ?></span>
                <span class="e-bill__qty"><?= $line['quantity'] ?> &times; &#8377;<?= number_format($line['price']) ?></span>
                <span class="e-bill__total">&#8377;<?= number_format($line['total'], 2) ?></span>
                <a class="e-bill__remove" href="cart.php?remove=<?= urlencode($line['id']) ?>"
                   aria-label="Remove one <?= htmlspecialchars($line['name'], ENT_QUOTES) ?>">&minus;</a>
            </div>
<?php   } ?>

            <div class="e-bill__sum" data-reveal>
                <span>Subtotal</span><span>&#8377;<?= number_format($beforeTax, 2) ?></span>
                <span>Tax (10%)</span><span>&#8377;<?= number_format($taxAmount, 2) ?></span>
                <span class="is-grand">Total</span><span class="is-grand">&#8377;<?= number_format($grandTotal, 2) ?></span>
            </div>

            <div class="e-bill__actions" data-reveal>
<?php   if ($orderTable > 0) { ?>
                <!-- QR ordering: place.php saves the order and sends it to the kitchen -->
                <form method="post" action="../order/place.php">
                    <button class="e-cta e-cta--ember" type="submit">
                        Send to kitchen
                        <span class="e-cta__arrow" aria-hidden="true"></span>
                    </button>
                </form>
<?php   } ?>
                <!-- data-no-transition: bill.php downloads a PDF, so no page animation here -->
                <a class="e-cta" href="bill.php" data-no-transition>
                    Download bill
                    <span class="e-cta__arrow" aria-hidden="true"></span>
                </a>
                <a class="e-link" href="cart.php?clear=1">Clear order</a>
            </div>
        </div>
<?php } ?>
    </div>
</section>

<?php include_once('../components/footer.php'); ?>
