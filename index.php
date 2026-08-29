<?php
/**
 * Public entry point.
 *
 * Sends visitors straight to the customer home page. Staff reach the admin
 * side through the LOGIN dropdown in the site header, so no separate splash
 * page is needed.
 *
 * 302 rather than 301 on purpose: a permanent redirect gets cached hard by
 * browsers and is painful to undo if this entry point ever changes.
 */

header('Location: customerSide/home/home.php', true, 302);
exit;
