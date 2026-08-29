<?php include_once('../components/header.php') ?>

<?php
// The cart is a plain [item_id => quantity] map kept in the session.
$cart = $_SESSION['cart'] ?? [];

// Build the bill lines from the menu rows header.php already fetched, so no
// extra queries are needed.
$allItems = [];
foreach (array_merge($mainDishes, $sides, $drinks) as $item) {
    $allItems[$item['item_id']] = $item;
}

$billLines = [];
foreach ($cart as $cartItemId => $cartQty) {
    if (isset($allItems[$cartItemId])) {
        $billLines[] = [
            'id'       => $cartItemId,
            'name'     => $allItems[$cartItemId]['item_name'],
            'price'    => (float) $allItems[$cartItemId]['item_price'],
            'quantity' => (int) $cartQty,
            'total'    => (float) $allItems[$cartItemId]['item_price'] * (int) $cartQty,
        ];
    }
}
$beforeTax = array_sum(array_column($billLines, 'total'));
$taxAmount = $beforeTax * 0.1;
$grandTotal = $beforeTax + $taxAmount;

// One menu row: name and price on top, type and the Add button beneath.
function renderMenuItem($item) {
?>
        <div class="menu-item">
          <div class="menu-item-head">
            <span class="item-name"><?php echo $item['item_name']; ?></span>
            <span class="item-price">&#8377;<?php echo $item['item_price']; ?></span>
          </div>
          <div class="menu-item-foot">
            <span class="item_type"><?php echo $item['item_type']; ?></span>
            <a class="add-btn" href="cart.php?add=<?php echo urlencode($item['item_id']); ?>">Add</a>
          </div>
        </div>
<?php
}

// One titled group of items.
function renderMenuGroup($title, $items) {
?>
      <div class="menu-group">
        <h2 class="menu-group-title"><?php echo $title; ?></h2>
        <div class="menu-items">
          <?php foreach ($items as $item) renderMenuItem($item); ?>
        </div>
      </div>
<?php
}
?>

<section id="projects" class="menu-page">
    <div class="projects container">
      <div class="projects-header">
        <h1 class="section-title">Menu</h1>
      </div>

      <select id="menu-category" class="menu-category">
        <option value="blue">All Items</option>
        <option value="yellow">Main Dishes</option>
        <option value="red">Side Dishes</option>
        <option value="green">Drinks</option>
      </select>

      <div class="yellow msg">
        <?php renderMenuGroup('Main Dishes', $mainDishes); ?>
      </div>

      <div class="red msg">
        <?php renderMenuGroup('Side Dishes', $sides); ?>
      </div>

      <div class="green msg">
        <?php renderMenuGroup('Drinks', $drinks); ?>
      </div>

      <div class="blue msg">
        <?php renderMenuGroup('Main Dishes', $mainDishes); ?>
        <?php renderMenuGroup('Side Dishes', $sides); ?>
        <?php renderMenuGroup('Drinks', $drinks); ?>
      </div>
    </div>
</section>

<!-- Bill Section -->
<section id="bill">
  <div class="bill container">
    <div class="bill-inner">
      <h1 class="section-title">Your <span>Order</span></h1>

<?php if (empty($billLines)) { ?>
      <p class="bill-empty">No items added yet. Use the Add button beside any dish above.</p>
<?php } else { ?>
      <table class="bill-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($billLines as $line) { ?>
          <tr>
            <td><?php echo $line['name']; ?></td>
            <td>&#8377;<?php echo number_format($line['price'], 2); ?></td>
            <td><?php echo $line['quantity']; ?></td>
            <td>&#8377;<?php echo number_format($line['total'], 2); ?></td>
            <td><a class="bill-remove" href="cart.php?remove=<?php echo urlencode($line['id']); ?>" title="Remove one">&minus;</a></td>
          </tr>
<?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3">Total</td>
            <td colspan="2">&#8377;<?php echo number_format($beforeTax, 2); ?></td>
          </tr>
          <tr>
            <td colspan="3">Tax (10%)</td>
            <td colspan="2">&#8377;<?php echo number_format($taxAmount, 2); ?></td>
          </tr>
          <tr class="bill-grand">
            <td colspan="3">Grand Total</td>
            <td colspan="2">&#8377;<?php echo number_format($grandTotal, 2); ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="bill-actions">
        <a class="cta" href="bill.php" data-no-transition>Download Bill</a>
        <a class="bill-clear" href="cart.php?clear=1">Clear order</a>
      </div>
<?php } ?>
    </div>
  </div>
</section>
<!-- End Bill Section -->

<?php include_once('../components/footer.php'); ?>
