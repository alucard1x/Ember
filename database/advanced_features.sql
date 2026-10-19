-- advanced_features.sql - the "smart restaurant" upgrade. Run it ONCE, after restaurantDB.sql.
-- (phpMyAdmin → restaurantdb → Import, or:  mysql -u root restaurantdb < advanced_features.sql)
--
-- 1. Kitchen gets a status and a bill_id   → QR ordering, live kitchen tickets, order tracker
-- 2. Menu becomes a SYSTEM-VERSIONED table → the "price time machine"
-- 3. An index on Bills.bill_time           → fast reports and insights
-- 4. Stored procedures                     → generate_history() makes months of realistic orders


-- ---------------------------------------------------------------------
-- 1. KITCHEN: status of every dish + which bill it belongs to
-- ---------------------------------------------------------------------
-- status: 'waiting' (just ordered) → 'cooking' → 'ready'
ALTER TABLE Kitchen
    ADD COLUMN status VARCHAR(10) NOT NULL DEFAULT 'waiting',
    ADD COLUMN bill_id INT NULL,
    ADD CONSTRAINT fk_kitchen_bill FOREIGN KEY (bill_id) REFERENCES Bills(bill_id);

-- dishes that were already finished are 'ready'
UPDATE Kitchen SET status = 'ready' WHERE time_ended IS NOT NULL;


-- ---------------------------------------------------------------------
-- 2. MENU TIME MACHINE (system versioning)
-- MariaDB now keeps every old version of every Menu row by itself.
-- Ask for the past with:  SELECT * FROM Menu FOR SYSTEM_TIME AS OF '2026-06-01';
--
-- So the demo has some history, we pretend the menu started 200 days ago
-- with older (lower) prices, and prices went up twice since then.
-- @@timestamp changes the clock for this connection only.
-- At the end every price is exactly what it is today.
-- ---------------------------------------------------------------------
CREATE TEMPORARY TABLE today_prices AS SELECT item_id, item_price FROM Menu;
SET @real_now = NOW();     -- remember the real time (NOW() follows @@timestamp)

-- 200 days ago: older prices (about 15% lower), then switch on versioning
SET @@timestamp = UNIX_TIMESTAMP(@real_now - INTERVAL 200 DAY);
UPDATE Menu SET item_price = ROUND(item_price * 0.85);
ALTER TABLE Menu ADD SYSTEM VERSIONING;

-- 120 days ago: first price rise for the tandoor, biryani and cocktails
SET @@timestamp = UNIX_TIMESTAMP(@real_now - INTERVAL 120 DAY);
UPDATE Menu m JOIN today_prices t ON t.item_id = m.item_id
SET m.item_price = ROUND(t.item_price * 0.93)
WHERE m.item_type IN ('Tandoori & Kebabs', 'Biryani & Pulao', 'Ember Cocktails', 'Desi Cocktails');

-- 45 days ago: today's prices for everything
SET @@timestamp = UNIX_TIMESTAMP(@real_now - INTERVAL 45 DAY);
UPDATE Menu m JOIN today_prices t ON t.item_id = m.item_id
SET m.item_price = t.item_price;

SET @@timestamp = DEFAULT;
DROP TEMPORARY TABLE today_prices;


-- ---------------------------------------------------------------------
-- 3. INDEX for the reports (they all filter or group by bill_time)
-- ---------------------------------------------------------------------
CREATE INDEX idx_bills_bill_time ON Bills (bill_time);


-- ---------------------------------------------------------------------
-- 4. REALISTIC HISTORY GENERATOR
-- CALL generate_history(180);   → about 6 months of believable, paid bills
-- CALL remove_history();        → deletes everything generate_history() made
-- ---------------------------------------------------------------------

-- remembers which bills each run created, so they can be removed again
CREATE TABLE IF NOT EXISTS History_Runs (
    run_id     INT PRIMARY KEY AUTO_INCREMENT,
    first_bill INT,
    last_bill  INT,
    days       INT,
    created_at DATETIME
);

DROP PROCEDURE IF EXISTS generate_history;
DROP PROCEDURE IF EXISTS remove_history;

DELIMITER $$

CREATE PROCEDURE generate_history(IN p_days INT)
BEGIN
    DECLARE d INT DEFAULT p_days;          -- days ago (counts down to 1)
    DECLARE b INT;                         -- bill counter for one day
    DECLARE i INT;                         -- dish counter for one bill
    DECLARE bills_today INT;
    DECLARE dishes INT;
    DECLARE the_day DATE;
    DECLARE the_time DATETIME;
    DECLARE new_bill INT;
    DECLARE first_bill INT DEFAULT NULL;
    DECLARE pick VARCHAR(6);
    DECLARE recent DOUBLE;                 -- 0 = oldest day, 1 = yesterday

    WHILE d >= 1 DO
        SET the_day = CURDATE() - INTERVAL d DAY;
        SET recent = 1 - d / p_days;

        -- more guests on Friday, Saturday and Sunday
        SET bills_today = 8 + FLOOR(RAND() * 6);
        IF DAYOFWEEK(the_day) IN (1, 6, 7) THEN
            SET bills_today = bills_today + 7;
        END IF;

        SET b = 1;
        WHILE b <= bills_today DO
            -- 35% lunch (12:00-14:59), 65% dinner (19:00-22:59)
            IF RAND() < 0.35 THEN
                SET the_time = the_day + INTERVAL (12 + FLOOR(RAND() * 3)) HOUR + INTERVAL FLOOR(RAND() * 60) MINUTE;
            ELSE
                SET the_time = the_day + INTERVAL (19 + FLOOR(RAND() * 4)) HOUR + INTERVAL FLOOR(RAND() * 60) MINUTE;
            END IF;

            INSERT INTO Bills (staff_id, member_id, table_id, payment_method, bill_time, payment_time)
            VALUES (
                (SELECT staff_id FROM Staffs ORDER BY RAND() LIMIT 1),
                IF(RAND() < 0.4, (SELECT member_id FROM Memberships ORDER BY RAND() LIMIT 1), NULL),
                (SELECT table_id FROM Restaurant_Tables ORDER BY RAND() LIMIT 1),
                'cash',
                the_time,
                the_time + INTERVAL (40 + FLOOR(RAND() * 50)) MINUTE
            );
            SET new_bill = LAST_INSERT_ID();
            IF first_bill IS NULL THEN
                SET first_bill = new_bill;
            END IF;

            -- 2 to 5 dishes; signatures are popular, biryani gets more popular every month
            SET dishes = 2 + FLOOR(RAND() * 4);
            SET i = 1;
            WHILE i <= dishes DO
                SELECT item_id INTO pick FROM Menu
                ORDER BY -LOG(1 - RAND()) /
                    CASE
                        WHEN item_id IN ('TK1', 'BP8', 'GS2', 'BP9') THEN 5
                        WHEN item_type = 'Biryani & Pulao' THEN 1 + 4 * recent
                        WHEN item_category = 'Drinks' THEN 1.5
                        ELSE 1
                    END
                LIMIT 1;

                INSERT INTO Bill_Items (bill_id, item_id, quantity)
                VALUES (new_bill, pick, IF(RAND() < 0.25, 2, 1));
                SET i = i + 1;
            END WHILE;

            SET b = b + 1;
        END WHILE;

        SET d = d - 1;
    END WHILE;

    INSERT INTO History_Runs (first_bill, last_bill, days, created_at)
    VALUES (first_bill, new_bill, p_days, NOW());

    SELECT COUNT(*) AS bills_created FROM Bills WHERE bill_id BETWEEN first_bill AND new_bill;
END$$

CREATE PROCEDURE remove_history()
BEGIN
    DELETE bi FROM Bill_Items bi JOIN History_Runs r ON bi.bill_id BETWEEN r.first_bill AND r.last_bill;
    DELETE b  FROM Bills b       JOIN History_Runs r ON b.bill_id  BETWEEN r.first_bill AND r.last_bill;
    DELETE FROM History_Runs;
END$$

DELIMITER ;

-- fill the restaurant with 6 months of history
CALL generate_history(180);
