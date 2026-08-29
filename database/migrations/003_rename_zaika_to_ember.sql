-- Renames the menu data that carried the old restaurant name.
-- Run this against an existing restaurantdb instead of re-importing the dump,
-- so bills, reservations, accounts and orders are kept.

UPDATE Menu SET item_name = 'Ember Special Biryani' WHERE item_name = 'Zaika Special Biryani';
UPDATE Menu SET item_name = 'Ember Iced Tea'        WHERE item_name = 'Zaika Iced Tea';
UPDATE Menu SET item_type = 'Ember Cocktails'       WHERE item_type = 'Zaika Cocktails';

-- Check: all three should return 0 rows.
-- SELECT item_id, item_name, item_type FROM Menu WHERE item_name LIKE '%Zaika%' OR item_type LIKE '%Zaika%';
