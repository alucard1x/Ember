<?php
// menu-panel.php - staff panel: Menu. Every dish grouped by course, search as you type,
// add a dish with one short form (the dish code is made automatically), edit or delete a dish.
session_start(); // Ensure session is started
require_once '../posBackend/checkIfLoggedIn.php';
include '../includes/dashHeader.php';
require_once '../config.php';

$dishes = mysqli_fetch_all(mysqli_query($link, 'SELECT * FROM Menu ORDER BY item_category, item_type, item_name'), MYSQLI_ASSOC);

// the list of courses, for the "Course" box
$courses = [];
foreach ($dishes as $dish) {
    $courses[$dish['item_type']] = $dish['item_category'];
}

$msg = $_GET['msg'] ?? '';
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="pg-head">
            <div>
                <h2>Menu</h2>
                <p class="pg-sub"><?= count($dishes) ?> dishes in <?= count($courses) ?> courses. Changes show on the website straight away (and the Price Time Machine remembers old prices).</p>
            </div>
            <button type="button" class="btn btn-dark" data-open="#add-dish">+ Add dish</button>
        </div>

<?php if ($msg == 'added') { ?>
        <div class="pg-msg">Dish added. It is on the website menu now.</div>
<?php } elseif ($msg == 'error') { ?>
        <div class="pg-msg is-error">Please fill in the name, course, category and a price above 0.</div>
<?php } ?>

        <form class="pg-add" id="add-dish" method="post" action="../menuCrud/addDish.php" <?= $msg == 'error' ? '' : 'hidden' ?>>
            <h3>New dish</h3>
            <div class="pg-fields">
                <div><label>Dish name</label><input type="text" name="item_name" class="form-control" required></div>
                <div><label>Course</label>
                    <input type="text" name="item_type" class="form-control" list="course-list" placeholder="e.g. Biryani &amp; Pulao" required>
                    <datalist id="course-list">
<?php foreach ($courses as $course => $category) { ?>
                        <option value="<?= htmlspecialchars($course) ?>">
<?php } ?>
                    </datalist></div>
                <div><label>Category</label>
                    <select name="item_category" class="form-control" required>
                        <option>Main Dishes</option><option>Side Snacks</option><option>Drinks</option>
                    </select></div>
                <div><label>Price (&#8377;)</label><input type="number" name="item_price" min="1" step="1" class="form-control" required></div>
                <div><label>Short description (optional)</label><input type="text" name="item_description" class="form-control"></div>
            </div>
            <button type="submit" class="btn btn-success">Save dish</button>
        </form>

        <div class="pg-tools">
            <input type="search" class="form-control pg-search" placeholder="Search dish or course…" data-filter="#menu-list">
            <span class="pg-count" data-count><?= count($dishes) ?> dishes</span>
        </div>

        <div class="table-scroll"><table class="table" id="menu-list">
            <thead><tr><th>Code</th><th>Dish</th><th>Course</th><th>Category</th><th class="pg-num">Price</th><th></th></tr></thead>
            <tbody>
<?php foreach ($dishes as $d) { ?>
                <tr>
                    <td><?= htmlspecialchars($d['item_id']) ?></td>
                    <td><?= htmlspecialchars($d['item_name']) ?>
<?php   if ($d['item_description'] != '') { ?>
                        <div class="text-muted small"><?= htmlspecialchars($d['item_description']) ?></div>
<?php   } ?>
                    </td>
                    <td><?= htmlspecialchars($d['item_type']) ?></td>
                    <td><?= htmlspecialchars($d['item_category']) ?></td>
                    <td class="pg-num">&#8377;<?= number_format((float) $d['item_price']) ?></td>
                    <td class="pg-actions">
                        <a href="../menuCrud/updateItemVerify.php?id=<?= urlencode($d['item_id']) ?>">Edit</a>
                        <a href="../menuCrud/deleteMenuVerify.php?id=<?= urlencode($d['item_id']) ?>">Delete</a>
                    </td>
                </tr>
<?php } ?>
            </tbody>
        </table></div>
    </div>
</div>

<script src="../js/staffPages.js"></script>
<?php include '../includes/dashFooter.php'; ?>
