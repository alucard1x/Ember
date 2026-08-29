-- Combined import for a hosted MySQL database (Aiven / Clever Cloud / Railway).
-- Generated from restaurantDB.sql + migrations/*.sql.
-- CREATE DATABASE / USE lines are stripped: the provider chooses the database name,
-- so import with:  mysql -h HOST -P PORT -u USER -p DBNAME < deploy_import.sql


CREATE TABLE IF NOT EXISTS Menu (
  item_id VARCHAR(6) PRIMARY KEY,
  item_name VARCHAR(255),
  item_type VARCHAR(255),
  item_category VARCHAR(255),
  item_price DECIMAL(10, 2),
  item_description VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS Accounts (
  account_id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255),
  register_date DATE,
  phone_number VARCHAR(255),
  password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS Staffs (
  staff_id INT PRIMARY KEY AUTO_INCREMENT,
  staff_name VARCHAR(255),
  role VARCHAR(255),
  account_id INT,
  FOREIGN KEY (account_id ) REFERENCES Accounts(account_id )
);

CREATE TABLE IF NOT EXISTS Memberships (
  member_id INT PRIMARY KEY AUTO_INCREMENT,
  member_name VARCHAR(255),
  points INT,
  account_id INT,
  FOREIGN KEY (account_id ) REFERENCES Accounts(account_id )
);

CREATE TABLE IF NOT EXISTS Restaurant_Tables (
  table_id INT PRIMARY KEY AUTO_INCREMENT,
  capacity INT,
  is_available BOOLEAN
);


CREATE TABLE IF NOT EXISTS Table_Availability (
  availability_id INT PRIMARY KEY AUTO_INCREMENT,
  table_id INT,
  reservation_date DATE,
  reservation_time TIME,
  status VARCHAR(20),
  FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id)
);

CREATE TABLE IF NOT EXISTS Reservations (
  reservation_id INT PRIMARY KEY AUTO_INCREMENT,
  customer_name VARCHAR(255),
  table_id INT,
  reservation_time TIME,
  reservation_date DATE,
  head_count INT,
  special_request VARCHAR(255),
  FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id)
);




CREATE TABLE IF NOT EXISTS card_payments (
    card_id INT AUTO_INCREMENT PRIMARY KEY,
    account_holder_name VARCHAR(255) NOT NULL,
    card_number VARCHAR(16) NOT NULL,
    expiry_date VARCHAR(7) NOT NULL,
    security_code VARCHAR(3) NOT NULL
);

CREATE TABLE IF NOT EXISTS Bills (
  bill_id INT PRIMARY KEY AUTO_INCREMENT,
  staff_id INT,
  member_id INT,
  reservation_id INT,
  table_id INT,
  card_id INT,
  payment_method VARCHAR(255),
  bill_time DATETIME,
  payment_time DATETIME,
  FOREIGN KEY (staff_id) REFERENCES Staffs(staff_id),
  FOREIGN KEY (member_id) REFERENCES Memberships(member_id),
  FOREIGN KEY (reservation_id) REFERENCES Reservations(reservation_id),
  FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id),
  FOREIGN KEY (card_id) REFERENCES card_payments(card_id)
);




CREATE TABLE IF NOT EXISTS Bill_Items (
  bill_item_id INT PRIMARY KEY AUTO_INCREMENT,
  bill_id INT,
  item_id VARCHAR(6),
  quantity INT,
  FOREIGN KEY (bill_id) REFERENCES Bills(bill_id),
  FOREIGN KEY (item_id) REFERENCES Menu(item_id)
);


CREATE TABLE IF NOT EXISTS Kitchen (
    kitchen_id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT,
    item_id VARCHAR(6),
    quantity INT,
    time_submitted DATETIME,
    time_ended DATETIME,
    FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id),
    FOREIGN KEY (item_id) REFERENCES Menu(item_id)
);




INSERT INTO Restaurant_Tables (table_id, capacity, is_available)
VALUES 
  ('1', '4', 1),
  ('2', '4', 1),
  ('3', '4', 1),
  ('4', '6', 1), 
  ('5', '6', 1),
  ('6', '6', 1),
('7', '6', 1),
  ('8', '8', 1), 
  ('9', '8', 1),
('10','8', 1);


INSERT INTO Menu (item_id, item_name, item_type, item_category, item_price, item_description)
VALUES
  ('TK1', 'Sikandari Raan', 'Tandoori & Kebabs', 'Main Dishes', 1824, '300g slow-roasted lamb leg'),
  ('TK2', 'Barrah Kebab', 'Tandoori & Kebabs', 'Main Dishes', 1501, '230g mutton chops'),
  ('TK3', 'Galouti Kebab Platter', 'Tandoori & Kebabs', 'Main Dishes', 1824, '230g'),
  ('TK4', 'Tandoori Lamb Ribs', 'Tandoori & Kebabs', 'Main Dishes', 1121, '400g'),
  ('MG1', 'Tandoori Chicken (Half)', 'Murg Specialities', 'Main Dishes', 627, 'Half Bird'),
  ('MG2', 'Amritsari Fried Chicken (Half)', 'Murg Specialities', 'Main Dishes', 627, 'Half Bird'),
  ('MG3', 'Butter Chicken', 'Murg Specialities', 'Main Dishes', 532, '300g'),
  ('MG4', 'Chicken Chettinad', 'Murg Specialities', 'Main Dishes', 532, '300g'),
  ('GS1', 'Rogan Josh', 'Gosht Specialities', 'Main Dishes', 912, 'slow-cooked Kashmiri mutton curry'),
  ('GS2', 'Nihari Gosht', 'Gosht Specialities', 'Main Dishes', 988, 'overnight-braised mutton shank'),
  ('MJ1', 'Catch of the Day Tawa Masala', 'Machli & Jhinga', 'Main Dishes', 874, 'fresh daily catch'),
  ('MJ2', 'Tandoori Jhinga', 'Machli & Jhinga', 'Main Dishes', 912, 'jumbo prawns'),
  ('MJ3', 'Goan Fish Curry', 'Machli & Jhinga', 'Main Dishes', 532, 'served with steamed rice'),
  ('MJ4', 'Amritsari Fish Fry', 'Machli & Jhinga', 'Main Dishes', 665, 'served with masala fries'),
  ('RS1', 'Paneer Tikka Kathi Roll', 'Rolls & Street Bites', 'Main Dishes', 570, 'delicious roll'),
  ('RS2', 'Mutton Kathi Roll', 'Rolls & Street Bites', 'Main Dishes', 570, 'delicious roll'),
  ('RS3', 'Chicken Frankie', 'Rolls & Street Bites', 'Main Dishes', 456, 'delicious roll'),
  ('RS4', 'Tandoori Chicken Roll', 'Rolls & Street Bites', 'Main Dishes', 456, 'delicious roll'),
  ('RS5', 'Pav Bhaji', 'Rolls & Street Bites', 'Main Dishes', 475, 'Mumbai street classic'),
  ('RS6', 'Keema Pav', 'Rolls & Street Bites', 'Main Dishes', 475, 'Mumbai street classic'),
  ('RS7', 'Mumbai Vada Pav', 'Rolls & Street Bites', 'Main Dishes', 456, 'Mumbai street classic'),
  ('RS8', 'Prawn Balchao Roll', 'Rolls & Street Bites', 'Main Dishes', 608, 'Goan style'),
  ('RS9', 'Chicken Seekh Roll', 'Rolls & Street Bites', 'Main Dishes', 532, 'delicious roll'),
  ('RS10', 'Masala Chicken Wrap', 'Rolls & Street Bites', 'Main Dishes', 456, 'delicious wrap'),
  ('BP1', 'Hyderabadi Chicken Biryani', 'Biryani & Pulao', 'Main Dishes', 494, 'Dum cooked'),
  ('BP2', 'Lucknowi Mutton Biryani', 'Biryani & Pulao', 'Main Dishes', 532, 'Dum cooked'),
  ('BP3', 'Awadhi Murg Pulao', 'Biryani & Pulao', 'Main Dishes', 532, 'Basmati'),
  ('BP4', 'Kashmiri Pulao', 'Biryani & Pulao', 'Main Dishes', 532, 'Basmati'),
  ('BP5', 'Chicken Tikka Biryani', 'Biryani & Pulao', 'Main Dishes', 532, 'Dum cooked'),
  ('BP6', 'Prawn Biryani', 'Biryani & Pulao', 'Main Dishes', 608, 'Dum cooked'),
  ('BP7', 'Malabar Fish Biryani', 'Biryani & Pulao', 'Main Dishes', 608, 'Basmati'),
  ('BP8', 'Ember Special Biryani', 'Biryani & Pulao', 'Main Dishes', 570, 'Chef''s Signature Biryani'),
  ('BP9', 'Gosht Yakhni Pulao', 'Biryani & Pulao', 'Main Dishes', 627, 'Chef''s Signature Pulao'),
  ('IB1', 'Masala Fries', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('IB2', 'Jeera Aloo', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('IB3', 'Garden Kachumber', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('IB4', 'Sabzi of the Day', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious vegetables'),
  ('IB5', 'Onion Pakora', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('IB6', 'Butter Masala Corn', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('IB7', 'Boondi Raita', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('IB8', 'Garlic Naan', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('IB9', 'Jeera Rice', 'Indian Breads & Sides', 'Side Snacks', 171, 'delicious'),
  ('CS1', 'Chicken 65', 'Chaat & Starters', 'Side Snacks', 456, 'delicious chicken 65'),
  ('CS2', 'Koliwada Calamari', 'Chaat & Starters', 'Side Snacks', 551, 'delicious fried calamari'),
  ('CS3', 'Masala Baked Mussels Half Dozen', 'Chaat & Starters', 'Side Snacks', 437, 'Half Dozen'),
  ('CS4', 'Masala Baked Mussels One Dozen', 'Chaat & Starters', 'Side Snacks', 722, 'One Dozen'),
  ('CS5', 'Mutton Seekh Kebab', 'Chaat & Starters', 'Side Snacks', 741, 'Delicious seekh kebab'),
  ('CS6', 'Papdi Chaat', 'Chaat & Starters', 'Side Snacks', 532, 'delicious papdi chaat'),
  ('CS7', 'Cheese Masala Fries', 'Chaat & Starters', 'Side Snacks', 266, 'delicious cheesy fries'),
  ('CS8', 'Keema Loaded Fries', 'Chaat & Starters', 'Side Snacks', 418, 'delicious keema fries'),
  ('KS1', 'Tandoori Chicken Salad', 'Kachumber & Salads', 'Side Snacks', 456, 'delicious salad'),
  ('KS2', 'Kachumber Salad', 'Kachumber & Salads', 'Side Snacks', 380, 'delicious salad'),
  ('IS1', 'Single Malt (Amrut Fusion)', 'Indian Spirits', 'Drinks', 5890, 'Amrut Fusion 700ml'),
  ('IS2', 'Single Malt (Rampur Double Cask)', 'Indian Spirits', 'Drinks', 7410, 'Rampur Double Cask 750ml'),
  ('IS3', 'Indian Whisky (Paul John Brilliance)', 'Indian Spirits', 'Drinks', 5510, '700ml'),
  ('IS4', 'Indian Whisky (Antiquity Blue)', 'Indian Spirits', 'Drinks', 5130, '750ml'),
  ('IS5', 'Single Malt (Indri Trini)', 'Indian Spirits', 'Drinks', 6840, 'Indri Trini 700ml'),
  ('IS6', 'Indian Brandy (Mansion House XO)', 'Indian Spirits', 'Drinks', 7410, 'Mansion House XO 700ml'),
  ('IS7', 'Vodka (Magic Moments)', 'Indian Spirits', 'Drinks', 3705, 'Magic Moments 700ml'),
  ('IS8', 'Dark Rum (Old Monk)', 'Indian Spirits', 'Drinks', 3135, 'Old Monk 750ml'),
  ('IS9', 'Gin (Greater Than)', 'Indian Spirits', 'Drinks', 3990, 'Greater Than 700ml'),
  ('DC1', 'Masala Mary', 'Desi Cocktails', 'Drinks', 532, 'cocktails'),
  ('DC2', 'Bombay Sling', 'Desi Cocktails', 'Drinks', 551, 'cocktails'),
  ('DC3', 'Delhi Iced Tea', 'Desi Cocktails', 'Drinks', 551, 'cocktails'),
  ('DC4', 'Goa Sunset', 'Desi Cocktails', 'Drinks', 551, 'cocktails'),
  ('DC5', 'Nimbu Margarita', 'Desi Cocktails', 'Drinks', 551, 'cocktails'),
  ('DC6', 'Nariyal Colada', 'Desi Cocktails', 'Drinks', 494, 'cocktails'),
  ('DC7', 'Pudina Mojito', 'Desi Cocktails', 'Drinks', 551, 'cocktails'),
  ('ZC1', 'Ember Iced Tea', 'Ember Cocktails', 'Drinks', 608, 'Gin, Old Monk'),
  ('ZC2', 'Neel Kamal', 'Ember Cocktails', 'Drinks', 570, 'Vodka'),
  ('ZC3', 'Kaala Jamun', 'Ember Cocktails', 'Drinks', 570, 'Vodka'),
  ('ZC4', 'Lychee Martini', 'Ember Cocktails', 'Drinks', 475, 'Vodka'),
  ('ZC5', 'Gulab Chichi', 'Ember Cocktails', 'Drinks', 475, 'Vodka'),
  ('MT1', 'Gulab Jamun', 'Mithai & Desserts', 'Side Snacks', 285, 'delicious gulab jamun'),
  ('MT2', 'Rasmalai Cheesecake', 'Mithai & Desserts', 'Side Snacks', 285, 'delicious cheese cake'),
  ('MT3', 'Mithai of the Day', 'Mithai & Desserts', 'Side Snacks', 247, 'delicious served with malai kulfi'),
  ('MT4', 'Malai Kulfi', 'Mithai & Desserts', 'Side Snacks', 228, 'delicious kulfi'),
  ('MT5', 'Gajar Halwa Sundae', 'Mithai & Desserts', 'Side Snacks', 266, 'delicious gajar halwa'),
  ('LM1', 'Mini Chicken Tikka', 'Little Maharaja', 'Side Snacks', 228, 'delicious chicken tikka'),
  ('LM2', 'Chicken Lollipop', 'Little Maharaja', 'Side Snacks', 228, 'delicious chicken lollipop'),
  ('LM3', 'Masala Fish Fingers', 'Little Maharaja', 'Side Snacks', 285, 'served with masala fries & corn'),
  ('LM4', 'Cheese Paneer Roll', 'Little Maharaja', 'Side Snacks', 228, 'delicious paneer roll'),
  ('LM5', 'Mini Keema Pav', 'Little Maharaja', 'Side Snacks', 228, 'delicious keema pav'),
  ('LM6', 'Bournvita', 'Little Maharaja', 'Side Snacks', 48, '200ml pack'),
  ('LM7', 'Frooti', 'Little Maharaja', 'Side Snacks', 66, '330ml pack'),
  ('LM8', 'Fruit Lassi', 'Little Maharaja', 'Side Snacks', 95, 'mango or strawberry'),
  ('LM9', 'Kulfi Milkshake', 'Little Maharaja', 'Side Snacks', 95, 'malai or chocolate'),
  ('FJ1', 'Amla & Apple', 'Fresh Juices', 'Drinks', 285, 'fresh pressed juice'),
  ('FJ2', 'Mosambi', 'Fresh Juices', 'Drinks', 285, 'fresh pressed juice'),
  ('FJ3', 'Gajar', 'Fresh Juices', 'Drinks', 285, 'fresh pressed juice'),
  ('FJ4', 'Tarbooz', 'Fresh Juices', 'Drinks', 247, 'fresh pressed juice'),
  ('FJ5', 'Ananas', 'Fresh Juices', 'Drinks', 247, 'fresh pressed juice'),
  ('FJ6', 'Nimbu', 'Fresh Juices', 'Drinks', 190, 'fresh pressed juice'),
  ('SM1', 'Jal Jeera Cooler', 'Sharbat & Mocktails', 'Drinks', 304, 'Cucumber, pudina, nimbu'),
  ('SM2', 'Virgin Pudina Mojito', 'Sharbat & Mocktails', 'Drinks', 304, 'Green apple, mint leaf'),
  ('SM3', 'Rose Sharbat', 'Sharbat & Mocktails', 'Drinks', 304, 'Lemonade, rooh afza'),
  ('SM4', 'Jamun Splash', 'Sharbat & Mocktails', 'Drinks', 304, 'Jamun, strawberry'),
  ('SM5', 'Gulab Green Tea', 'Sharbat & Mocktails', 'Drinks', 304, 'Green Tea, lychee, rose'),
  ('SM6', 'Aam Panna Fizz', 'Sharbat & Mocktails', 'Drinks', 304, 'Raw mango, nimbu, soda');



-- Accounts table
INSERT INTO Accounts (email, register_date, phone_number, password)
VALUES ('arjun.sharma@gmail.com', '2023-08-31', '+91 98450 11000', 'password123'),
       ('priya.nair@gmail.com', '2023-08-30', '+91 98450 11001', 'susanpassword'),
       ('rohan.verma@gmail.com', '2023-08-29', '+91 98450 11002', 'jamespass'),
       ('ananya.iyer@gmail.com', '2023-08-28', '+91 98450 11003', 'alicepassword'),
       ('karthik.reddy@gmail.com', '2023-08-27', '+91 98450 11004', 'mikepass'),
       ('meera.joshi@gmail.com', '2023-08-26', '+91 98450 11005', 'lisapassword'),
       ('vikram.malhotra@gmail.com', '2023-08-25', '+91 98450 11006', 'robertpass'),
       ('sneha.kulkarni@gmail.com', '2023-08-24', '+91 98450 11007', 'emilypassword'),
       ('rajesh.pillai@gmail.com', '2023-08-23', '+91 98450 11008', 'davidp321ass'),
       ('deepika.chatterjee@gmail.com', '2023-08-23', '+91 98450 11009', 'davidpa2ss'),
       ('aditya.bansal@gmail.com', '2023-08-23', '+91 98450 11010', 'david4pass'),
       ('kavya.menon@gmail.com', '2023-08-23', '+91 98450 11011', 'davidp13ass'),
       ('imran.sheikh@gmail.com', '2023-08-23', '+91 98450 11012', 'david2pass'),
       ('nikhil.kapoor@gmail.com', '2023-08-23', '+91 98450 11013', 'davidpa52ss'),
	('sanjay.gupta@gmail.com', '2023-08-23', '+91 98450 11014', 'da2332ss'),
	('divya.ramesh@gmail.com', '2023-08-23', '+91 98450 11015', 'dav43a52ss'),
	('harsh.patel@gmail.com', '2023-08-23', '+91 98450 11016', '32533pa52ss'),
	('neha.bhatia@gmail.com', '2023-08-23', '+91 98450 11017', '754dpa52ss'),
	('manish.agarwal@gmail.com', '2023-08-23', '+91 98450 11018', '41f2s'),
       ('pooja.deshmukh@gmail.com', '2023-08-22', '+91 98450 11019', 'oliviapass4215word'),
    ('siddharth.rao@gmail.com', '2023-09-01', '+91 98450 11020', 'password123'),
    ('ritu.saxena@gmail.com', '2023-09-02', '+91 98450 11021', 'password456'),
    ('varun.chauhan@gmail.com', '2023-09-03', '+91 98450 11022', 'password789'),
    ('ishita.ghosh@gmail.com', '2023-09-04', '+91 98450 11023', 'passwordabc'),
    ('aakash.sinha@gmail.com', '2023-09-05', '+91 98450 11024', 'passworddef'),
    ('tanvi.shah@gmail.com', '2023-09-06', '+91 98450 11025', 'passwordghi'),
    ('rahul.krishnan@gmail.com', '2023-09-07', '+91 98450 11026', 'passwordjkl'),
    ('megha.trivedi@gmail.com', '2023-09-08', '+91 98450 11027', 'passwordmno'),
    ('abhishek.dubey@gmail.com', '2023-09-09', '+91 98450 11028', 'passwordpqr'),
    ('simran.kaur@gmail.com', '2023-09-10', '+91 98450 11029', 'passwordstu');


-- Staffs table
INSERT INTO Staffs (staff_name, role, account_id)
VALUES ('Arjun Sharma', 'Waiter', 1),
       ('Priya Nair', 'Waiter', 2),
       ('Rohan Verma', 'Waiter', 3),
       ('Ananya Iyer', 'Waiter', 4),
       ('Karthik Reddy', 'Waiter', 5),
       ('Meera Joshi', 'Chef', 6),
       ('Vikram Malhotra', 'Manager', 7),
       ('Sneha Kulkarni', 'Manager', 8),
       ('Rajesh Pillai', 'Chef', 9),
       ('Deepika Chatterjee', 'Chef', 10);



-- Memberships table
INSERT INTO Memberships (member_name, points,account_id)
VALUES 
	('Aditya Bansal', 100, 11),
       	('Kavya Menon', 200, 12),
       	('Imran Sheikh', 300, 13),
       	('Nikhil Kapoor', 400, 14),
       	('Sanjay Gupta', 500, 15),
       	('Divya Ramesh', 600, 16),
       	('Harsh Patel', 700, 17),
       	('Neha Bhatia', 800, 18),
       	('Manish Agarwal', 900, 19),
       	('Pooja Deshmukh', 1000, 20),	
	('Siddharth Rao', 250, 21),
  	('Ritu Saxena', 300, 22),
  	('Varun Chauhan', 400, 23),
  	('Ishita Ghosh', 550, 24),
  	('Aakash Sinha', 650, 25),
  	('Tanvi Shah', 750, 26),
  	('Rahul Krishnan', 900, 27),
  	('Megha Trivedi', 1050, 28),
  	('Abhishek Dubey', 1200, 29),
  	('Simran Kaur', 1350, 30);



-- Reservations table
INSERT INTO Reservations (reservation_id, customer_name, table_id, reservation_time, reservation_date, head_count, special_request)
VALUES 
(2220231, 'Aditya Bansal', 1, '22:00:34', '2023-09-28', 1, 'Prepare Crocin for me'),
(2220232, 'Kavya Menon', 2, '22:00:34', '2023-09-29', 1, 'Default Special Request'),
(1920233, 'Imran Sheikh', 3, '19:30:00', '2023-09-30', 2, 'Pure veg / Jain options needed'),
(2020234, 'Nikhil Kapoor', 4, '20:00:00', '2023-09-30', 3, 'Birthday celebration'),
(1920235, 'Sanjay Gupta', 5, '19:45:00', '2023-10-01', 2, 'Quiet corner, please'),
(1820237, 'Ritu Saxena', 7, '18:30:00', '2023-10-03', 2, 'Allergies: peanuts (no groundnut oil)'),
(1920239, 'Rahul Krishnan', 9, '19:15:00', '2023-10-05', 2, 'Surprise dessert for anniversary'),
(1111111, 'Default', 9, '19:15:00', '2023-10-05', 2, 'Description'),
(14202310, 'Abhishek Dubey', 10, '14:45:00', '2023-10-06', 3, 'Window seat, if available');

INSERT INTO card_payments (card_id, account_holder_name, card_number, expiry_date, security_code)
VALUES
('1', 'Arjun Sharma', '1234567890123456', '10/15', '123'),
('2', 'Priya Nair', '2345678901234567', '10/24', '456'),
('3', 'Rohan Verma', '3456789012345678', '09/30', '789'),
('4', 'Ananya Iyer', '4567890123456789', '09/28', '321'),
('5', 'Karthik Reddy', '5678901234567890', '09/29', '654'),
('6', 'Vikram Malhotra', '7890123456789012', '10/19', '123'),
('7', 'Aditya Bansal', '1234123412341234', '10/25', '654'),
('8', 'Kavya Menon', '2345234523452345', '10/24', '987'),
('9', 'Imran Sheikh', '3456345634563456', '09/23', '123'),
('10', 'Nikhil Kapoor', '4567456745674567', '09/22', '456');

INSERT INTO Bills (staff_id, member_id, reservation_id, table_id, card_id, payment_method, bill_time, payment_time)
VALUES
(1, 1, 2220231, 1, 1, 'Card', '2023-09-28 22:45:00', '2023-09-28 22:50:00'),
(1, 5, NULL, 5, NULL, 'Cash', '2023-09-28 19:00:00', '2023-09-28 19:05:00'),
(1, 2, 2220232, 2, 2, 'Card', '2023-09-29 22:45:00', '2023-09-29 22:50:00'),
(2, 3, 1920233, 3, NULL, 'Cash', '2023-09-30 20:15:00', '2023-09-30 20:20:00'), 
(2, 4, 2020234, 4, 3, 'Card', '2023-09-30 20:30:00', '2023-09-30 20:35:00'),
(2, 8, NULL, 6, NULL, 'Cash', '2023-09-30 20:15:00', '2023-09-30 20:20:00'),
(3, 5, 1920235, 5, NULL, 'Cash', '2023-10-01 20:15:00', '2023-10-01 20:20:00'), 
(3, 6, NULL, 7, NULL, 'Cash', '2023-10-01 19:00:00', '2023-10-01 19:05:00'),
(3, 18, NULL, 2, NULL, 'Cash', '2023-10-01 18:30:00', '2023-10-01 18:35:00'),
(4, 7, NULL, 9, NULL, 'Cash', '2023-10-02 19:30:00', '2023-10-02 19:35:00'), 
(4, 17, NULL, 8, NULL, 'Cash', '2023-10-02 20:00:00', '2023-10-02 20:05:00'),
(4, 8, NULL, 10, 4, 'Card', '2023-10-02 19:00:00', '2023-10-02 19:05:00'),
(5, 9, 1820237, 6, 5, 'Card', '2023-10-03 18:45:00', '2023-10-03 18:50:00'),
(5, 16, NULL, 9, NULL, 'Cash', '2023-10-03 19:45:00', '2023-10-03 19:50:00'),
(5, 10, NULL, 5, NULL, 'Cash', '2023-10-03 20:00:00', '2023-10-03 20:05:00'), 
(6, 11, NULL, 4, 6, 'Card', '2023-10-03 20:15:00', '2023-10-03 20:20:00'),
(6, 8, NULL, 10, NULL, 'Cash', '2023-10-03 20:30:00', '2023-10-03 20:35:00'),
(6, 12, NULL, 3, 7, 'Card', '2023-10-04 19:30:00', '2023-10-04 19:35:00'),
(7, 13, NULL, 2, NULL, 'Cash', '2023-10-04 19:15:00', '2023-10-04 19:20:00'), 
(7, 14, 1920239, 1, NULL, 'Cash', '2023-10-05 20:30:00', '2023-10-05 20:35:00'),
(7, 1, NULL, 6, NULL, 'Cash', '2023-10-05 14:00:00', '2023-10-05 14:05:00'),
(8, 15, NULL, 8, 8, 'Card', '2023-10-05 20:45:00', '2023-10-05 20:50:00'),
(8, 16, NULL, 7, NULL, 'Cash', '2023-10-05 20:00:00', '2023-10-05 20:05:00'), 
(8, 2, NULL, 9, NULL, 'Cash', '2023-10-05 19:30:00', '2023-10-05 19:35:00'),
(8, 9, NULL, 4, NULL, 'Cash', '2023-10-05 20:15:00', '2023-10-05 20:20:00'),
(9, 17, NULL, 9, 9, 'Card', '2023-10-05 12:00:00', '2023-10-05 12:05:00'),
(9, 18, NULL, 10, 10, 'Card', '2023-10-06 13:15:00', '2023-10-06 13:20:00'),
(9, 19, 14202310, 8, NULL, 'Cash', '2023-10-06 14:30:00', '2023-10-06 14:35:00'),
(10, 7, NULL, 10, NULL, 'Cash', '2023-10-06 10:45:00', '2023-10-06 10:50:00'), 
(10, 20, NULL, 6, NULL, 'Cash', '2023-10-06 14:45:00', '2023-10-06 14:50:00');



INSERT INTO Bill_Items (bill_id, item_id, quantity)
VALUES 
(1, 'TK1', 2),
(1, 'RS1', 1),
(1, 'CS3', 2),
(1, 'IS1', 1),

(2, 'TK2', 1),
(2, 'MG1', 2),
(2, 'RS2', 1),
(2, 'CS5', 2),
(2, 'IS2', 1),
(2, 'ZC2', 2),

(3, 'RS5', 1),
(3, 'TK2', 1),
(3, 'TK4', 1),
(3, 'CS6', 2),
(3, 'IS3', 1),
(3, 'ZC3', 2),

(4, 'RS9', 1),
(4, 'GS1', 1),
(4, 'IS2', 2),
(4, 'DC3', 1),
(4, 'ZC4', 2),

(5, 'RS9', 1),
(5, 'CS1', 1),
(5, 'CS8', 2),
(5, 'IS5', 1),
(5, 'ZC5', 2),

(6, 'RS9', 1),
(6, 'RS7', 1),
(6, 'DC1', 1),
(6, 'DC2', 2),

(7, 'RS9', 1),
(7, 'CS1', 1),
(7, 'CS4', 1),
(7, 'DC3', 1),
(7, 'DC4', 2),

(8, 'RS9', 1),
(8, 'IS2', 1),
(8, 'DC3', 1),
(8, 'IS5', 1),
(8, 'DC4', 2),
(8, 'SM1', 1),
(8, 'SM2', 2),

(9, 'RS9', 1),
(9, 'SM1', 1),
(9, 'SM4', 1),
(9, 'SM2', 1),
(9, 'SM5', 2),
(9, 'SM6', 1),
(9, 'MT1', 2),

(10, 'LM3', 1),
(10, 'LM6', 1),
(10, 'FJ1', 1),
(10, 'FJ2', 1),
(10, 'FJ3', 2),
(10, 'FJ4', 1),
(10, 'FJ5', 2),

(11, 'TK1', 2),
(11, 'RS1', 1),
(11, 'CS3', 2),
(11, 'IS1', 1),

(12, 'TK2', 1),
(12, 'MG1', 2),
(12, 'RS2', 1),
(12, 'CS5', 2),
(12, 'IS2', 1),
(12, 'ZC2', 2),

(13, 'RS5', 1),
(13, 'TK2', 1),
(13, 'TK4', 1),
(13, 'CS6', 2),
(13, 'IS3', 1),
(13, 'ZC3', 2),

(14, 'RS9', 1),
(14, 'GS1', 1),
(14, 'IS2', 2),
(14, 'DC3', 1),
(14, 'ZC4', 2),

(15, 'RS9', 1),
(15, 'CS1', 1),
(15, 'CS8', 2),
(15, 'IS5', 1),
(15, 'ZC5', 2),

(16, 'RS9', 1),
(16, 'RS7', 1),
(16, 'DC1', 1),
(16, 'DC2', 2),

(17, 'RS9', 1),
(17, 'IB8', 1),
(17, 'CS4', 1),
(17, 'DC3', 1),
(17, 'DC4', 2),

(18, 'RS9', 1),
(18, 'BP8', 1),
(18, 'BP9', 1),
(18, 'IS5', 1),
(18, 'DC4', 2),
(18, 'SM1', 1),
(18, 'SM2', 2),

(19, 'RS9', 1),
(19, 'SM1', 1),
(19, 'SM4', 1),
(19, 'BP5', 1),
(19, 'SM5', 2),
(19, 'SM6', 1),
(19, 'MT1', 2),

(20, 'IB9', 1),
(20, 'LM6', 1),
(20, 'FJ1', 1),
(20, 'FJ2', 1),
(20, 'FJ3', 2),
(20, 'FJ4', 1),
(20, 'FJ5', 2),

(21, 'TK1', 2),
(21, 'RS1', 1),
(21, 'CS3', 2),
(21, 'CS1', 1),

(22, 'TK2', 1),
(22, 'MG1', 2),
(22, 'RS2', 1),
(22, 'CS5', 2),
(22, 'LM2', 1),
(22, 'ZC2', 2),

(23, 'GS1', 1),
(23, 'RS7', 1),
(23, 'SM6', 1),
(23, 'LM6', 2),
(23, 'IS9', 1),
(23, 'ZC5', 2),

(24, 'RS9', 1),
(24, 'MT2', 1),
(24, 'TK2', 2),
(24, 'SM3', 1),
(24, 'ZC1', 2),

(25, 'TK2', 1),
(25, 'RS7', 1),
(25, 'MG4', 2),
(25, 'IS5', 1),
(25, 'ZC5', 2),

(26, 'RS9', 1),
(26, 'RS7', 1),
(26, 'DC1', 1),
(26, 'DC2', 2),

(27, 'RS9', 1),
(27, 'MJ1', 1),
(27, 'TK4', 1),
(27, 'DC3', 1),
(27, 'DC4', 2),

(28, 'RS9', 1),
(28, 'RS8', 1),
(28, 'SM3', 1),
(28, 'FJ5', 1),
(28, 'LM4', 2),
(28, 'SM1', 1),
(28, 'TK2', 2),

(29, 'RS9', 1),
(29, 'SM1', 1),
(29, 'SM4', 1),
(29, 'TK2', 1),
(29, 'SM5', 2),
(29, 'FJ1', 1),
(29, 'MT1', 2),

(30, 'TK3', 1),
(30, 'MG2', 1),
(30, 'MJ1', 1),
(30, 'RS8', 1),
(30, 'FJ3', 2),
(30, 'FJ4', 1),
(30, 'FJ5', 2);

-- Kitchen
INSERT INTO Kitchen (table_id, item_id, quantity, time_submitted, time_ended)
VALUES	
	
	(6, 'LM3', 4, '2023-10-03 18:45:00', '2023-10-03 18:46:00'),
	(6, 'FJ2', 3, '2023-10-03 18:45:00', '2023-10-03 18:46:00'),
	(5, 'CS3', 5, '2023-10-03 20:00:00', '2023-10-03 20:46:00'), 
	(5, 'RS1', 2, '2023-10-03 14:45:00', '2023-10-03 14:46:00'),
	(1, 'TK1', 1, '2023-09-28 22:45:00', '2023-09-28 23:00:00'),
	(1, 'RS1', 2, '2023-09-28 22:45:00', '2023-09-28 23:00:00'),
	(1, 'CS3', 1, '2023-09-28 22:45:00', '2023-09-28 23:00:00'), 
	(1, 'IS1', 1, '2023-09-28 22:45:00', '2023-09-28 23:00:00'),
	(5, 'TK2', 1, '2023-09-28 19:00:00', '2023-09-28 19:15:00'),
	(5, 'MG1', 1, '2023-09-28 19:00:00', '2023-09-28 19:15:00'),
	(5, 'RS2', 1, '2023-09-28 19:00:00', '2023-09-28 19:15:00'), 
	(5, 'CS5', 1, '2023-09-28 19:00:00', '2023-09-28 19:15:00'),
	(5, 'IS2', 2, '2023-09-28 19:00:00', '2023-09-28 19:15:00'),
	(5, 'ZC2', 1, '2023-09-28 19:00:00', '2023-09-28 19:15:00'),
	(2, 'RS5', 2, '2023-09-29 22:45:00', '2023-09-29 23:00:00'), 
	(2, 'TK2', 1, '2023-09-29 22:45:00', '2023-09-29 23:00:00'),
	(2, 'TK4', 2, '2023-09-29 22:45:00', '2023-09-29 23:00:00'),
	(2, 'CS6', 2, '2023-09-29 22:45:00', '2023-09-29 23:00:00'),
	(2, 'IS3', 1, '2023-09-29 22:45:00', '2023-09-29 23:00:00'), 
	(2, 'ZC3', 1, '2023-09-29 22:45:00', '2023-09-29 23:00:00');

INSERT INTO Kitchen (table_id, item_id, quantity, time_submitted)
VALUES	
	(10, 'RS9', 1, '2023-10-06 10:45:00'),
	(10, 'TK2', 1, '2023-10-06 10:45:00'),
	(6, 'RS8', 1, '2023-10-06 14:45:00'), 
	(6, 'FJ5', 2, '2023-10-06 14:45:00');





-- ---------- migrations/001_rename_to_indian_names.sql ----------
-- Rename all staff + membership customers to Indian names.
-- Run against the existing restaurantDB; keyed on account_id so it is safe to re-run.
-- Generated for RestaurantProject.

START TRANSACTION;

-- 1. Account emails --------------------------------------------------
UPDATE Accounts SET email = 'arjun.sharma@gmail.com' WHERE account_id = 1;
UPDATE Accounts SET email = 'priya.nair@gmail.com' WHERE account_id = 2;
UPDATE Accounts SET email = 'rohan.verma@gmail.com' WHERE account_id = 3;
UPDATE Accounts SET email = 'ananya.iyer@gmail.com' WHERE account_id = 4;
UPDATE Accounts SET email = 'karthik.reddy@gmail.com' WHERE account_id = 5;
UPDATE Accounts SET email = 'meera.joshi@gmail.com' WHERE account_id = 6;
UPDATE Accounts SET email = 'vikram.malhotra@gmail.com' WHERE account_id = 7;
UPDATE Accounts SET email = 'sneha.kulkarni@gmail.com' WHERE account_id = 8;
UPDATE Accounts SET email = 'rajesh.pillai@gmail.com' WHERE account_id = 9;
UPDATE Accounts SET email = 'deepika.chatterjee@gmail.com' WHERE account_id = 10;
UPDATE Accounts SET email = 'aditya.bansal@gmail.com' WHERE account_id = 11;
UPDATE Accounts SET email = 'kavya.menon@gmail.com' WHERE account_id = 12;
UPDATE Accounts SET email = 'imran.sheikh@gmail.com' WHERE account_id = 13;
UPDATE Accounts SET email = 'nikhil.kapoor@gmail.com' WHERE account_id = 14;
UPDATE Accounts SET email = 'sanjay.gupta@gmail.com' WHERE account_id = 15;
UPDATE Accounts SET email = 'divya.ramesh@gmail.com' WHERE account_id = 16;
UPDATE Accounts SET email = 'harsh.patel@gmail.com' WHERE account_id = 17;
UPDATE Accounts SET email = 'neha.bhatia@gmail.com' WHERE account_id = 18;
UPDATE Accounts SET email = 'manish.agarwal@gmail.com' WHERE account_id = 19;
UPDATE Accounts SET email = 'pooja.deshmukh@gmail.com' WHERE account_id = 20;
UPDATE Accounts SET email = 'siddharth.rao@gmail.com' WHERE account_id = 21;
UPDATE Accounts SET email = 'ritu.saxena@gmail.com' WHERE account_id = 22;
UPDATE Accounts SET email = 'varun.chauhan@gmail.com' WHERE account_id = 23;
UPDATE Accounts SET email = 'ishita.ghosh@gmail.com' WHERE account_id = 24;
UPDATE Accounts SET email = 'aakash.sinha@gmail.com' WHERE account_id = 25;
UPDATE Accounts SET email = 'tanvi.shah@gmail.com' WHERE account_id = 26;
UPDATE Accounts SET email = 'rahul.krishnan@gmail.com' WHERE account_id = 27;
UPDATE Accounts SET email = 'megha.trivedi@gmail.com' WHERE account_id = 28;
UPDATE Accounts SET email = 'abhishek.dubey@gmail.com' WHERE account_id = 29;
UPDATE Accounts SET email = 'simran.kaur@gmail.com' WHERE account_id = 30;

-- 2. Staff names ----------------------------------------------------
UPDATE Staffs SET staff_name = 'Arjun Sharma' WHERE account_id = 1;
UPDATE Staffs SET staff_name = 'Priya Nair' WHERE account_id = 2;
UPDATE Staffs SET staff_name = 'Rohan Verma' WHERE account_id = 3;
UPDATE Staffs SET staff_name = 'Ananya Iyer' WHERE account_id = 4;
UPDATE Staffs SET staff_name = 'Karthik Reddy' WHERE account_id = 5;
UPDATE Staffs SET staff_name = 'Meera Joshi' WHERE account_id = 6;
UPDATE Staffs SET staff_name = 'Vikram Malhotra' WHERE account_id = 7;
UPDATE Staffs SET staff_name = 'Sneha Kulkarni' WHERE account_id = 8;
UPDATE Staffs SET staff_name = 'Rajesh Pillai' WHERE account_id = 9;
UPDATE Staffs SET staff_name = 'Deepika Chatterjee' WHERE account_id = 10;

-- 3. Membership names -----------------------------------------------
UPDATE Memberships SET member_name = 'Aditya Bansal' WHERE account_id = 11;
UPDATE Memberships SET member_name = 'Kavya Menon' WHERE account_id = 12;
UPDATE Memberships SET member_name = 'Imran Sheikh' WHERE account_id = 13;
UPDATE Memberships SET member_name = 'Nikhil Kapoor' WHERE account_id = 14;
UPDATE Memberships SET member_name = 'Sanjay Gupta' WHERE account_id = 15;
UPDATE Memberships SET member_name = 'Divya Ramesh' WHERE account_id = 16;
UPDATE Memberships SET member_name = 'Harsh Patel' WHERE account_id = 17;
UPDATE Memberships SET member_name = 'Neha Bhatia' WHERE account_id = 18;
UPDATE Memberships SET member_name = 'Manish Agarwal' WHERE account_id = 19;
UPDATE Memberships SET member_name = 'Pooja Deshmukh' WHERE account_id = 20;
UPDATE Memberships SET member_name = 'Siddharth Rao' WHERE account_id = 21;
UPDATE Memberships SET member_name = 'Ritu Saxena' WHERE account_id = 22;
UPDATE Memberships SET member_name = 'Varun Chauhan' WHERE account_id = 23;
UPDATE Memberships SET member_name = 'Ishita Ghosh' WHERE account_id = 24;
UPDATE Memberships SET member_name = 'Aakash Sinha' WHERE account_id = 25;
UPDATE Memberships SET member_name = 'Tanvi Shah' WHERE account_id = 26;
UPDATE Memberships SET member_name = 'Rahul Krishnan' WHERE account_id = 27;
UPDATE Memberships SET member_name = 'Megha Trivedi' WHERE account_id = 28;
UPDATE Memberships SET member_name = 'Abhishek Dubey' WHERE account_id = 29;
UPDATE Memberships SET member_name = 'Simran Kaur' WHERE account_id = 30;

-- 4. Same people stored as free text in Reservations ----------------
UPDATE Reservations SET customer_name = 'Aditya Bansal' WHERE customer_name = 'Abbel Tu Far Behind';
UPDATE Reservations SET customer_name = 'Kavya Menon' WHERE customer_name = 'Abignaile Lin Downney Jr';
UPDATE Reservations SET customer_name = 'Imran Sheikh' WHERE customer_name = 'Jamie Mustafa';
UPDATE Reservations SET customer_name = 'Nikhil Kapoor' WHERE customer_name = 'Luke Gun Slinger';
UPDATE Reservations SET customer_name = 'Sanjay Gupta' WHERE customer_name = 'Johny Rings';
UPDATE Reservations SET customer_name = 'Ritu Saxena' WHERE customer_name = 'Jean Ng';
UPDATE Reservations SET customer_name = 'Rahul Krishnan' WHERE customer_name = 'Taylor Swift';
UPDATE Reservations SET customer_name = 'Abhishek Dubey' WHERE customer_name = 'Bruce Lee';

-- 5. Same people stored as free text in card_payments ---------------
UPDATE card_payments SET account_holder_name = 'Arjun Sharma' WHERE account_holder_name = 'John Smith';
UPDATE card_payments SET account_holder_name = 'Priya Nair' WHERE account_holder_name = 'Susan Johnson';
UPDATE card_payments SET account_holder_name = 'Rohan Verma' WHERE account_holder_name = 'James Brown';
UPDATE card_payments SET account_holder_name = 'Ananya Iyer' WHERE account_holder_name = 'Alice Davis';
UPDATE card_payments SET account_holder_name = 'Karthik Reddy' WHERE account_holder_name = 'Mike Wilson';
UPDATE card_payments SET account_holder_name = 'Vikram Malhotra' WHERE account_holder_name = 'Robert Miller';
UPDATE card_payments SET account_holder_name = 'Aditya Bansal' WHERE account_holder_name = 'Abbel TuTuTu';
UPDATE card_payments SET account_holder_name = 'Kavya Menon' WHERE account_holder_name = 'Abignail Downey';
UPDATE card_payments SET account_holder_name = 'Imran Sheikh' WHERE account_holder_name = 'Jamie Mustafa';
UPDATE card_payments SET account_holder_name = 'Nikhil Kapoor' WHERE account_holder_name = 'Luke Gun Slinger';

COMMIT;

-- Verify:
-- SELECT s.staff_id, s.staff_name, s.role, a.email FROM Staffs s JOIN Accounts a USING(account_id) ORDER BY s.staff_id;
-- SELECT m.member_id, m.member_name, a.email FROM Memberships m JOIN Accounts a USING(account_id) ORDER BY m.member_id;


-- ---------- migrations/002_indianise_menu.sql ----------
-- Indianise the menu, prices, item codes and remaining Malaysian/US data.
-- Run against an existing restaurantdb that has already had
-- rename_to_indian_names.sql applied (or a fresh import of the old dump).
-- Prices converted from RM at 1 RM = 19 INR.

SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- 1. Menu: new codes, Indian dishes, Indian sections, INR prices -------
UPDATE Menu SET item_id='TK1', item_name='Sikandari Raan', item_type='Tandoori & Kebabs', item_category='Main Dishes', item_price=1824, item_description='300g slow-roasted lamb leg' WHERE item_id='MD1';
UPDATE Menu SET item_id='TK2', item_name='Barrah Kebab', item_type='Tandoori & Kebabs', item_category='Main Dishes', item_price=1501, item_description='230g mutton chops' WHERE item_id='MD2';
UPDATE Menu SET item_id='TK3', item_name='Galouti Kebab Platter', item_type='Tandoori & Kebabs', item_category='Main Dishes', item_price=1824, item_description='230g' WHERE item_id='MD3';
UPDATE Menu SET item_id='TK4', item_name='Tandoori Lamb Ribs', item_type='Tandoori & Kebabs', item_category='Main Dishes', item_price=1121, item_description='400g' WHERE item_id='MD4';
UPDATE Menu SET item_id='MG1', item_name='Tandoori Chicken (Half)', item_type='Murg Specialities', item_category='Main Dishes', item_price=627, item_description='Half Bird' WHERE item_id='MD5';
UPDATE Menu SET item_id='MG2', item_name='Amritsari Fried Chicken (Half)', item_type='Murg Specialities', item_category='Main Dishes', item_price=627, item_description='Half Bird' WHERE item_id='MD6';
UPDATE Menu SET item_id='MG3', item_name='Butter Chicken', item_type='Murg Specialities', item_category='Main Dishes', item_price=532, item_description='300g' WHERE item_id='MD7';
UPDATE Menu SET item_id='MG4', item_name='Chicken Chettinad', item_type='Murg Specialities', item_category='Main Dishes', item_price=532, item_description='300g' WHERE item_id='MD8';
UPDATE Menu SET item_id='GS1', item_name='Rogan Josh', item_type='Gosht Specialities', item_category='Main Dishes', item_price=912, item_description='slow-cooked Kashmiri mutton curry' WHERE item_id='MD9';
UPDATE Menu SET item_id='GS2', item_name='Nihari Gosht', item_type='Gosht Specialities', item_category='Main Dishes', item_price=988, item_description='overnight-braised mutton shank' WHERE item_id='MD10';
UPDATE Menu SET item_id='MJ1', item_name='Catch of the Day Tawa Masala', item_type='Machli & Jhinga', item_category='Main Dishes', item_price=874, item_description='fresh daily catch' WHERE item_id='MD11';
UPDATE Menu SET item_id='MJ2', item_name='Tandoori Jhinga', item_type='Machli & Jhinga', item_category='Main Dishes', item_price=912, item_description='jumbo prawns' WHERE item_id='MD12';
UPDATE Menu SET item_id='MJ3', item_name='Goan Fish Curry', item_type='Machli & Jhinga', item_category='Main Dishes', item_price=532, item_description='served with steamed rice' WHERE item_id='MD13';
UPDATE Menu SET item_id='MJ4', item_name='Amritsari Fish Fry', item_type='Machli & Jhinga', item_category='Main Dishes', item_price=665, item_description='served with masala fries' WHERE item_id='MD14';
UPDATE Menu SET item_id='RS1', item_name='Paneer Tikka Kathi Roll', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=570, item_description='delicious roll' WHERE item_id='MD15';
UPDATE Menu SET item_id='RS2', item_name='Mutton Kathi Roll', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=570, item_description='delicious roll' WHERE item_id='MD16';
UPDATE Menu SET item_id='RS3', item_name='Chicken Frankie', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=456, item_description='delicious roll' WHERE item_id='MD17';
UPDATE Menu SET item_id='RS4', item_name='Tandoori Chicken Roll', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=456, item_description='delicious roll' WHERE item_id='MD18';
UPDATE Menu SET item_id='RS5', item_name='Pav Bhaji', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=475, item_description='Mumbai street classic' WHERE item_id='MD19';
UPDATE Menu SET item_id='RS6', item_name='Keema Pav', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=475, item_description='Mumbai street classic' WHERE item_id='MD20';
UPDATE Menu SET item_id='RS7', item_name='Mumbai Vada Pav', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=456, item_description='Mumbai street classic' WHERE item_id='MD21';
UPDATE Menu SET item_id='RS8', item_name='Prawn Balchao Roll', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=608, item_description='Goan style' WHERE item_id='MD22';
UPDATE Menu SET item_id='RS9', item_name='Chicken Seekh Roll', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=532, item_description='delicious roll' WHERE item_id='MD23';
UPDATE Menu SET item_id='RS10', item_name='Masala Chicken Wrap', item_type='Rolls & Street Bites', item_category='Main Dishes', item_price=456, item_description='delicious wrap' WHERE item_id='MD24';
UPDATE Menu SET item_id='BP1', item_name='Hyderabadi Chicken Biryani', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=494, item_description='Dum cooked' WHERE item_id='MD25';
UPDATE Menu SET item_id='BP2', item_name='Lucknowi Mutton Biryani', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=532, item_description='Dum cooked' WHERE item_id='MD26';
UPDATE Menu SET item_id='BP3', item_name='Awadhi Murg Pulao', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=532, item_description='Basmati' WHERE item_id='MD27';
UPDATE Menu SET item_id='BP4', item_name='Kashmiri Pulao', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=532, item_description='Basmati' WHERE item_id='MD28';
UPDATE Menu SET item_id='BP5', item_name='Chicken Tikka Biryani', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=532, item_description='Dum cooked' WHERE item_id='MD29';
UPDATE Menu SET item_id='BP6', item_name='Prawn Biryani', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=608, item_description='Dum cooked' WHERE item_id='MD30';
UPDATE Menu SET item_id='BP7', item_name='Malabar Fish Biryani', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=608, item_description='Basmati' WHERE item_id='MD31';
UPDATE Menu SET item_id='BP8', item_name='Ember Special Biryani', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=570, item_description='Chef''s Signature Biryani' WHERE item_id='MD32';
UPDATE Menu SET item_id='BP9', item_name='Gosht Yakhni Pulao', item_type='Biryani & Pulao', item_category='Main Dishes', item_price=627, item_description='Chef''s Signature Pulao' WHERE item_id='MD33';
UPDATE Menu SET item_id='IB1', item_name='Masala Fries', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD34';
UPDATE Menu SET item_id='IB2', item_name='Jeera Aloo', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD35';
UPDATE Menu SET item_id='IB3', item_name='Garden Kachumber', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD36';
UPDATE Menu SET item_id='IB4', item_name='Sabzi of the Day', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious vegetables' WHERE item_id='MD37';
UPDATE Menu SET item_id='IB5', item_name='Onion Pakora', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD38';
UPDATE Menu SET item_id='IB6', item_name='Butter Masala Corn', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD39';
UPDATE Menu SET item_id='IB7', item_name='Boondi Raita', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD40';
UPDATE Menu SET item_id='IB8', item_name='Garlic Naan', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD41';
UPDATE Menu SET item_id='IB9', item_name='Jeera Rice', item_type='Indian Breads & Sides', item_category='Side Snacks', item_price=171, item_description='delicious' WHERE item_id='MD42';
UPDATE Menu SET item_id='CS1', item_name='Chicken 65', item_type='Chaat & Starters', item_category='Side Snacks', item_price=456, item_description='delicious chicken 65' WHERE item_id='S1';
UPDATE Menu SET item_id='CS2', item_name='Koliwada Calamari', item_type='Chaat & Starters', item_category='Side Snacks', item_price=551, item_description='delicious fried calamari' WHERE item_id='S2';
UPDATE Menu SET item_id='CS3', item_name='Masala Baked Mussels Half Dozen', item_type='Chaat & Starters', item_category='Side Snacks', item_price=437, item_description='Half Dozen' WHERE item_id='S3';
UPDATE Menu SET item_id='CS4', item_name='Masala Baked Mussels One Dozen', item_type='Chaat & Starters', item_category='Side Snacks', item_price=722, item_description='One Dozen' WHERE item_id='S4';
UPDATE Menu SET item_id='CS5', item_name='Mutton Seekh Kebab', item_type='Chaat & Starters', item_category='Side Snacks', item_price=741, item_description='Delicious seekh kebab' WHERE item_id='S5';
UPDATE Menu SET item_id='CS6', item_name='Papdi Chaat', item_type='Chaat & Starters', item_category='Side Snacks', item_price=532, item_description='delicious papdi chaat' WHERE item_id='S6';
UPDATE Menu SET item_id='CS7', item_name='Cheese Masala Fries', item_type='Chaat & Starters', item_category='Side Snacks', item_price=266, item_description='delicious cheesy fries' WHERE item_id='S7';
UPDATE Menu SET item_id='CS8', item_name='Keema Loaded Fries', item_type='Chaat & Starters', item_category='Side Snacks', item_price=418, item_description='delicious keema fries' WHERE item_id='S8';
UPDATE Menu SET item_id='KS1', item_name='Tandoori Chicken Salad', item_type='Kachumber & Salads', item_category='Side Snacks', item_price=456, item_description='delicious salad' WHERE item_id='S9';
UPDATE Menu SET item_id='KS2', item_name='Kachumber Salad', item_type='Kachumber & Salads', item_category='Side Snacks', item_price=380, item_description='delicious salad' WHERE item_id='S10';
UPDATE Menu SET item_id='IS1', item_name='Single Malt (Amrut Fusion)', item_type='Indian Spirits', item_category='Drinks', item_price=5890, item_description='Amrut Fusion 700ml' WHERE item_id='L1';
UPDATE Menu SET item_id='IS2', item_name='Single Malt (Rampur Double Cask)', item_type='Indian Spirits', item_category='Drinks', item_price=7410, item_description='Rampur Double Cask 750ml' WHERE item_id='L2';
UPDATE Menu SET item_id='IS3', item_name='Indian Whisky (Paul John Brilliance)', item_type='Indian Spirits', item_category='Drinks', item_price=5510, item_description='700ml' WHERE item_id='L3';
UPDATE Menu SET item_id='IS4', item_name='Indian Whisky (Antiquity Blue)', item_type='Indian Spirits', item_category='Drinks', item_price=5130, item_description='750ml' WHERE item_id='L4';
UPDATE Menu SET item_id='IS5', item_name='Single Malt (Indri Trini)', item_type='Indian Spirits', item_category='Drinks', item_price=6840, item_description='Indri Trini 700ml' WHERE item_id='L5';
UPDATE Menu SET item_id='IS6', item_name='Indian Brandy (Mansion House XO)', item_type='Indian Spirits', item_category='Drinks', item_price=7410, item_description='Mansion House XO 700ml' WHERE item_id='L6';
UPDATE Menu SET item_id='IS7', item_name='Vodka (Magic Moments)', item_type='Indian Spirits', item_category='Drinks', item_price=3705, item_description='Magic Moments 700ml' WHERE item_id='L7';
UPDATE Menu SET item_id='IS8', item_name='Dark Rum (Old Monk)', item_type='Indian Spirits', item_category='Drinks', item_price=3135, item_description='Old Monk 750ml' WHERE item_id='L8';
UPDATE Menu SET item_id='IS9', item_name='Gin (Greater Than)', item_type='Indian Spirits', item_category='Drinks', item_price=3990, item_description='Greater Than 700ml' WHERE item_id='L9';
UPDATE Menu SET item_id='DC1', item_name='Masala Mary', item_type='Desi Cocktails', item_category='Drinks', item_price=532, item_description='cocktails' WHERE item_id='C1';
UPDATE Menu SET item_id='DC2', item_name='Bombay Sling', item_type='Desi Cocktails', item_category='Drinks', item_price=551, item_description='cocktails' WHERE item_id='C2';
UPDATE Menu SET item_id='DC3', item_name='Delhi Iced Tea', item_type='Desi Cocktails', item_category='Drinks', item_price=551, item_description='cocktails' WHERE item_id='C3';
UPDATE Menu SET item_id='DC4', item_name='Goa Sunset', item_type='Desi Cocktails', item_category='Drinks', item_price=551, item_description='cocktails' WHERE item_id='C4';
UPDATE Menu SET item_id='DC5', item_name='Nimbu Margarita', item_type='Desi Cocktails', item_category='Drinks', item_price=551, item_description='cocktails' WHERE item_id='C5';
UPDATE Menu SET item_id='DC6', item_name='Nariyal Colada', item_type='Desi Cocktails', item_category='Drinks', item_price=494, item_description='cocktails' WHERE item_id='C6';
UPDATE Menu SET item_id='DC7', item_name='Pudina Mojito', item_type='Desi Cocktails', item_category='Drinks', item_price=551, item_description='cocktails' WHERE item_id='C7';
UPDATE Menu SET item_id='ZC1', item_name='Ember Iced Tea', item_type='Ember Cocktails', item_category='Drinks', item_price=608, item_description='Gin, Old Monk' WHERE item_id='HC1';
UPDATE Menu SET item_id='ZC2', item_name='Neel Kamal', item_type='Ember Cocktails', item_category='Drinks', item_price=570, item_description='Vodka' WHERE item_id='HC2';
UPDATE Menu SET item_id='ZC3', item_name='Kaala Jamun', item_type='Ember Cocktails', item_category='Drinks', item_price=570, item_description='Vodka' WHERE item_id='HC3';
UPDATE Menu SET item_id='ZC4', item_name='Lychee Martini', item_type='Ember Cocktails', item_category='Drinks', item_price=475, item_description='Vodka' WHERE item_id='HC4';
UPDATE Menu SET item_id='ZC5', item_name='Gulab Chichi', item_type='Ember Cocktails', item_category='Drinks', item_price=475, item_description='Vodka' WHERE item_id='HC5';
UPDATE Menu SET item_id='MT1', item_name='Gulab Jamun', item_type='Mithai & Desserts', item_category='Side Snacks', item_price=285, item_description='delicious gulab jamun' WHERE item_id='HD1';
UPDATE Menu SET item_id='MT2', item_name='Rasmalai Cheesecake', item_type='Mithai & Desserts', item_category='Side Snacks', item_price=285, item_description='delicious cheese cake' WHERE item_id='HD2';
UPDATE Menu SET item_id='MT3', item_name='Mithai of the Day', item_type='Mithai & Desserts', item_category='Side Snacks', item_price=247, item_description='delicious served with malai kulfi' WHERE item_id='HD3';
UPDATE Menu SET item_id='MT4', item_name='Malai Kulfi', item_type='Mithai & Desserts', item_category='Side Snacks', item_price=228, item_description='delicious kulfi' WHERE item_id='HD4';
UPDATE Menu SET item_id='MT5', item_name='Gajar Halwa Sundae', item_type='Mithai & Desserts', item_category='Side Snacks', item_price=266, item_description='delicious gajar halwa' WHERE item_id='HD5';
UPDATE Menu SET item_id='LM1', item_name='Mini Chicken Tikka', item_type='Little Maharaja', item_category='Side Snacks', item_price=228, item_description='delicious chicken tikka' WHERE item_id='SK1';
UPDATE Menu SET item_id='LM2', item_name='Chicken Lollipop', item_type='Little Maharaja', item_category='Side Snacks', item_price=228, item_description='delicious chicken lollipop' WHERE item_id='SK2';
UPDATE Menu SET item_id='LM3', item_name='Masala Fish Fingers', item_type='Little Maharaja', item_category='Side Snacks', item_price=285, item_description='served with masala fries & corn' WHERE item_id='SK3';
UPDATE Menu SET item_id='LM4', item_name='Cheese Paneer Roll', item_type='Little Maharaja', item_category='Side Snacks', item_price=228, item_description='delicious paneer roll' WHERE item_id='SK4';
UPDATE Menu SET item_id='LM5', item_name='Mini Keema Pav', item_type='Little Maharaja', item_category='Side Snacks', item_price=228, item_description='delicious keema pav' WHERE item_id='SK5';
UPDATE Menu SET item_id='LM6', item_name='Bournvita', item_type='Little Maharaja', item_category='Side Snacks', item_price=48, item_description='200ml pack' WHERE item_id='SK6';
UPDATE Menu SET item_id='LM7', item_name='Frooti', item_type='Little Maharaja', item_category='Side Snacks', item_price=66, item_description='330ml pack' WHERE item_id='SK7';
UPDATE Menu SET item_id='LM8', item_name='Fruit Lassi', item_type='Little Maharaja', item_category='Side Snacks', item_price=95, item_description='mango or strawberry' WHERE item_id='SK8';
UPDATE Menu SET item_id='LM9', item_name='Kulfi Milkshake', item_type='Little Maharaja', item_category='Side Snacks', item_price=95, item_description='malai or chocolate' WHERE item_id='SK9';
UPDATE Menu SET item_id='FJ1', item_name='Amla & Apple', item_type='Fresh Juices', item_category='Drinks', item_price=285, item_description='fresh pressed juice' WHERE item_id='CP1';
UPDATE Menu SET item_id='FJ2', item_name='Mosambi', item_type='Fresh Juices', item_category='Drinks', item_price=285, item_description='fresh pressed juice' WHERE item_id='CP2';
UPDATE Menu SET item_id='FJ3', item_name='Gajar', item_type='Fresh Juices', item_category='Drinks', item_price=285, item_description='fresh pressed juice' WHERE item_id='CP3';
UPDATE Menu SET item_id='FJ4', item_name='Tarbooz', item_type='Fresh Juices', item_category='Drinks', item_price=247, item_description='fresh pressed juice' WHERE item_id='CP4';
UPDATE Menu SET item_id='FJ5', item_name='Ananas', item_type='Fresh Juices', item_category='Drinks', item_price=247, item_description='fresh pressed juice' WHERE item_id='CP5';
UPDATE Menu SET item_id='FJ6', item_name='Nimbu', item_type='Fresh Juices', item_category='Drinks', item_price=190, item_description='fresh pressed juice' WHERE item_id='CP6';
UPDATE Menu SET item_id='SM1', item_name='Jal Jeera Cooler', item_type='Sharbat & Mocktails', item_category='Drinks', item_price=304, item_description='Cucumber, pudina, nimbu' WHERE item_id='M1';
UPDATE Menu SET item_id='SM2', item_name='Virgin Pudina Mojito', item_type='Sharbat & Mocktails', item_category='Drinks', item_price=304, item_description='Green apple, mint leaf' WHERE item_id='M2';
UPDATE Menu SET item_id='SM3', item_name='Rose Sharbat', item_type='Sharbat & Mocktails', item_category='Drinks', item_price=304, item_description='Lemonade, rooh afza' WHERE item_id='M3';
UPDATE Menu SET item_id='SM4', item_name='Jamun Splash', item_type='Sharbat & Mocktails', item_category='Drinks', item_price=304, item_description='Jamun, strawberry' WHERE item_id='M4';
UPDATE Menu SET item_id='SM5', item_name='Gulab Green Tea', item_type='Sharbat & Mocktails', item_category='Drinks', item_price=304, item_description='Green Tea, lychee, rose' WHERE item_id='M5';
UPDATE Menu SET item_id='SM6', item_name='Aam Panna Fizz', item_type='Sharbat & Mocktails', item_category='Drinks', item_price=304, item_description='Raw mango, nimbu, soda' WHERE item_id='M6';

-- 2. Cascade the new item codes into Bill_Items and Kitchen ----------
UPDATE Bill_Items SET item_id='TK1' WHERE item_id='MD1';
UPDATE Bill_Items SET item_id='TK2' WHERE item_id='MD2';
UPDATE Bill_Items SET item_id='TK3' WHERE item_id='MD3';
UPDATE Bill_Items SET item_id='TK4' WHERE item_id='MD4';
UPDATE Bill_Items SET item_id='MG1' WHERE item_id='MD5';
UPDATE Bill_Items SET item_id='MG2' WHERE item_id='MD6';
UPDATE Bill_Items SET item_id='MG3' WHERE item_id='MD7';
UPDATE Bill_Items SET item_id='MG4' WHERE item_id='MD8';
UPDATE Bill_Items SET item_id='GS1' WHERE item_id='MD9';
UPDATE Bill_Items SET item_id='GS2' WHERE item_id='MD10';
UPDATE Bill_Items SET item_id='MJ1' WHERE item_id='MD11';
UPDATE Bill_Items SET item_id='MJ2' WHERE item_id='MD12';
UPDATE Bill_Items SET item_id='MJ3' WHERE item_id='MD13';
UPDATE Bill_Items SET item_id='MJ4' WHERE item_id='MD14';
UPDATE Bill_Items SET item_id='RS1' WHERE item_id='MD15';
UPDATE Bill_Items SET item_id='RS2' WHERE item_id='MD16';
UPDATE Bill_Items SET item_id='RS3' WHERE item_id='MD17';
UPDATE Bill_Items SET item_id='RS4' WHERE item_id='MD18';
UPDATE Bill_Items SET item_id='RS5' WHERE item_id='MD19';
UPDATE Bill_Items SET item_id='RS6' WHERE item_id='MD20';
UPDATE Bill_Items SET item_id='RS7' WHERE item_id='MD21';
UPDATE Bill_Items SET item_id='RS8' WHERE item_id='MD22';
UPDATE Bill_Items SET item_id='RS9' WHERE item_id='MD23';
UPDATE Bill_Items SET item_id='RS10' WHERE item_id='MD24';
UPDATE Bill_Items SET item_id='BP1' WHERE item_id='MD25';
UPDATE Bill_Items SET item_id='BP2' WHERE item_id='MD26';
UPDATE Bill_Items SET item_id='BP3' WHERE item_id='MD27';
UPDATE Bill_Items SET item_id='BP4' WHERE item_id='MD28';
UPDATE Bill_Items SET item_id='BP5' WHERE item_id='MD29';
UPDATE Bill_Items SET item_id='BP6' WHERE item_id='MD30';
UPDATE Bill_Items SET item_id='BP7' WHERE item_id='MD31';
UPDATE Bill_Items SET item_id='BP8' WHERE item_id='MD32';
UPDATE Bill_Items SET item_id='BP9' WHERE item_id='MD33';
UPDATE Bill_Items SET item_id='IB1' WHERE item_id='MD34';
UPDATE Bill_Items SET item_id='IB2' WHERE item_id='MD35';
UPDATE Bill_Items SET item_id='IB3' WHERE item_id='MD36';
UPDATE Bill_Items SET item_id='IB4' WHERE item_id='MD37';
UPDATE Bill_Items SET item_id='IB5' WHERE item_id='MD38';
UPDATE Bill_Items SET item_id='IB6' WHERE item_id='MD39';
UPDATE Bill_Items SET item_id='IB7' WHERE item_id='MD40';
UPDATE Bill_Items SET item_id='IB8' WHERE item_id='MD41';
UPDATE Bill_Items SET item_id='IB9' WHERE item_id='MD42';
UPDATE Bill_Items SET item_id='CS1' WHERE item_id='S1';
UPDATE Bill_Items SET item_id='CS2' WHERE item_id='S2';
UPDATE Bill_Items SET item_id='CS3' WHERE item_id='S3';
UPDATE Bill_Items SET item_id='CS4' WHERE item_id='S4';
UPDATE Bill_Items SET item_id='CS5' WHERE item_id='S5';
UPDATE Bill_Items SET item_id='CS6' WHERE item_id='S6';
UPDATE Bill_Items SET item_id='CS7' WHERE item_id='S7';
UPDATE Bill_Items SET item_id='CS8' WHERE item_id='S8';
UPDATE Bill_Items SET item_id='KS1' WHERE item_id='S9';
UPDATE Bill_Items SET item_id='KS2' WHERE item_id='S10';
UPDATE Bill_Items SET item_id='IS1' WHERE item_id='L1';
UPDATE Bill_Items SET item_id='IS2' WHERE item_id='L2';
UPDATE Bill_Items SET item_id='IS3' WHERE item_id='L3';
UPDATE Bill_Items SET item_id='IS4' WHERE item_id='L4';
UPDATE Bill_Items SET item_id='IS5' WHERE item_id='L5';
UPDATE Bill_Items SET item_id='IS6' WHERE item_id='L6';
UPDATE Bill_Items SET item_id='IS7' WHERE item_id='L7';
UPDATE Bill_Items SET item_id='IS8' WHERE item_id='L8';
UPDATE Bill_Items SET item_id='IS9' WHERE item_id='L9';
UPDATE Bill_Items SET item_id='DC1' WHERE item_id='C1';
UPDATE Bill_Items SET item_id='DC2' WHERE item_id='C2';
UPDATE Bill_Items SET item_id='DC3' WHERE item_id='C3';
UPDATE Bill_Items SET item_id='DC4' WHERE item_id='C4';
UPDATE Bill_Items SET item_id='DC5' WHERE item_id='C5';
UPDATE Bill_Items SET item_id='DC6' WHERE item_id='C6';
UPDATE Bill_Items SET item_id='DC7' WHERE item_id='C7';
UPDATE Bill_Items SET item_id='ZC1' WHERE item_id='HC1';
UPDATE Bill_Items SET item_id='ZC2' WHERE item_id='HC2';
UPDATE Bill_Items SET item_id='ZC3' WHERE item_id='HC3';
UPDATE Bill_Items SET item_id='ZC4' WHERE item_id='HC4';
UPDATE Bill_Items SET item_id='ZC5' WHERE item_id='HC5';
UPDATE Bill_Items SET item_id='MT1' WHERE item_id='HD1';
UPDATE Bill_Items SET item_id='MT2' WHERE item_id='HD2';
UPDATE Bill_Items SET item_id='MT3' WHERE item_id='HD3';
UPDATE Bill_Items SET item_id='MT4' WHERE item_id='HD4';
UPDATE Bill_Items SET item_id='MT5' WHERE item_id='HD5';
UPDATE Bill_Items SET item_id='LM1' WHERE item_id='SK1';
UPDATE Bill_Items SET item_id='LM2' WHERE item_id='SK2';
UPDATE Bill_Items SET item_id='LM3' WHERE item_id='SK3';
UPDATE Bill_Items SET item_id='LM4' WHERE item_id='SK4';
UPDATE Bill_Items SET item_id='LM5' WHERE item_id='SK5';
UPDATE Bill_Items SET item_id='LM6' WHERE item_id='SK6';
UPDATE Bill_Items SET item_id='LM7' WHERE item_id='SK7';
UPDATE Bill_Items SET item_id='LM8' WHERE item_id='SK8';
UPDATE Bill_Items SET item_id='LM9' WHERE item_id='SK9';
UPDATE Bill_Items SET item_id='FJ1' WHERE item_id='CP1';
UPDATE Bill_Items SET item_id='FJ2' WHERE item_id='CP2';
UPDATE Bill_Items SET item_id='FJ3' WHERE item_id='CP3';
UPDATE Bill_Items SET item_id='FJ4' WHERE item_id='CP4';
UPDATE Bill_Items SET item_id='FJ5' WHERE item_id='CP5';
UPDATE Bill_Items SET item_id='FJ6' WHERE item_id='CP6';
UPDATE Bill_Items SET item_id='SM1' WHERE item_id='M1';
UPDATE Bill_Items SET item_id='SM2' WHERE item_id='M2';
UPDATE Bill_Items SET item_id='SM3' WHERE item_id='M3';
UPDATE Bill_Items SET item_id='SM4' WHERE item_id='M4';
UPDATE Bill_Items SET item_id='SM5' WHERE item_id='M5';
UPDATE Bill_Items SET item_id='SM6' WHERE item_id='M6';
UPDATE Kitchen SET item_id='TK1' WHERE item_id='MD1';
UPDATE Kitchen SET item_id='TK2' WHERE item_id='MD2';
UPDATE Kitchen SET item_id='TK3' WHERE item_id='MD3';
UPDATE Kitchen SET item_id='TK4' WHERE item_id='MD4';
UPDATE Kitchen SET item_id='MG1' WHERE item_id='MD5';
UPDATE Kitchen SET item_id='MG2' WHERE item_id='MD6';
UPDATE Kitchen SET item_id='MG3' WHERE item_id='MD7';
UPDATE Kitchen SET item_id='MG4' WHERE item_id='MD8';
UPDATE Kitchen SET item_id='GS1' WHERE item_id='MD9';
UPDATE Kitchen SET item_id='GS2' WHERE item_id='MD10';
UPDATE Kitchen SET item_id='MJ1' WHERE item_id='MD11';
UPDATE Kitchen SET item_id='MJ2' WHERE item_id='MD12';
UPDATE Kitchen SET item_id='MJ3' WHERE item_id='MD13';
UPDATE Kitchen SET item_id='MJ4' WHERE item_id='MD14';
UPDATE Kitchen SET item_id='RS1' WHERE item_id='MD15';
UPDATE Kitchen SET item_id='RS2' WHERE item_id='MD16';
UPDATE Kitchen SET item_id='RS3' WHERE item_id='MD17';
UPDATE Kitchen SET item_id='RS4' WHERE item_id='MD18';
UPDATE Kitchen SET item_id='RS5' WHERE item_id='MD19';
UPDATE Kitchen SET item_id='RS6' WHERE item_id='MD20';
UPDATE Kitchen SET item_id='RS7' WHERE item_id='MD21';
UPDATE Kitchen SET item_id='RS8' WHERE item_id='MD22';
UPDATE Kitchen SET item_id='RS9' WHERE item_id='MD23';
UPDATE Kitchen SET item_id='RS10' WHERE item_id='MD24';
UPDATE Kitchen SET item_id='BP1' WHERE item_id='MD25';
UPDATE Kitchen SET item_id='BP2' WHERE item_id='MD26';
UPDATE Kitchen SET item_id='BP3' WHERE item_id='MD27';
UPDATE Kitchen SET item_id='BP4' WHERE item_id='MD28';
UPDATE Kitchen SET item_id='BP5' WHERE item_id='MD29';
UPDATE Kitchen SET item_id='BP6' WHERE item_id='MD30';
UPDATE Kitchen SET item_id='BP7' WHERE item_id='MD31';
UPDATE Kitchen SET item_id='BP8' WHERE item_id='MD32';
UPDATE Kitchen SET item_id='BP9' WHERE item_id='MD33';
UPDATE Kitchen SET item_id='IB1' WHERE item_id='MD34';
UPDATE Kitchen SET item_id='IB2' WHERE item_id='MD35';
UPDATE Kitchen SET item_id='IB3' WHERE item_id='MD36';
UPDATE Kitchen SET item_id='IB4' WHERE item_id='MD37';
UPDATE Kitchen SET item_id='IB5' WHERE item_id='MD38';
UPDATE Kitchen SET item_id='IB6' WHERE item_id='MD39';
UPDATE Kitchen SET item_id='IB7' WHERE item_id='MD40';
UPDATE Kitchen SET item_id='IB8' WHERE item_id='MD41';
UPDATE Kitchen SET item_id='IB9' WHERE item_id='MD42';
UPDATE Kitchen SET item_id='CS1' WHERE item_id='S1';
UPDATE Kitchen SET item_id='CS2' WHERE item_id='S2';
UPDATE Kitchen SET item_id='CS3' WHERE item_id='S3';
UPDATE Kitchen SET item_id='CS4' WHERE item_id='S4';
UPDATE Kitchen SET item_id='CS5' WHERE item_id='S5';
UPDATE Kitchen SET item_id='CS6' WHERE item_id='S6';
UPDATE Kitchen SET item_id='CS7' WHERE item_id='S7';
UPDATE Kitchen SET item_id='CS8' WHERE item_id='S8';
UPDATE Kitchen SET item_id='KS1' WHERE item_id='S9';
UPDATE Kitchen SET item_id='KS2' WHERE item_id='S10';
UPDATE Kitchen SET item_id='IS1' WHERE item_id='L1';
UPDATE Kitchen SET item_id='IS2' WHERE item_id='L2';
UPDATE Kitchen SET item_id='IS3' WHERE item_id='L3';
UPDATE Kitchen SET item_id='IS4' WHERE item_id='L4';
UPDATE Kitchen SET item_id='IS5' WHERE item_id='L5';
UPDATE Kitchen SET item_id='IS6' WHERE item_id='L6';
UPDATE Kitchen SET item_id='IS7' WHERE item_id='L7';
UPDATE Kitchen SET item_id='IS8' WHERE item_id='L8';
UPDATE Kitchen SET item_id='IS9' WHERE item_id='L9';
UPDATE Kitchen SET item_id='DC1' WHERE item_id='C1';
UPDATE Kitchen SET item_id='DC2' WHERE item_id='C2';
UPDATE Kitchen SET item_id='DC3' WHERE item_id='C3';
UPDATE Kitchen SET item_id='DC4' WHERE item_id='C4';
UPDATE Kitchen SET item_id='DC5' WHERE item_id='C5';
UPDATE Kitchen SET item_id='DC6' WHERE item_id='C6';
UPDATE Kitchen SET item_id='DC7' WHERE item_id='C7';
UPDATE Kitchen SET item_id='ZC1' WHERE item_id='HC1';
UPDATE Kitchen SET item_id='ZC2' WHERE item_id='HC2';
UPDATE Kitchen SET item_id='ZC3' WHERE item_id='HC3';
UPDATE Kitchen SET item_id='ZC4' WHERE item_id='HC4';
UPDATE Kitchen SET item_id='ZC5' WHERE item_id='HC5';
UPDATE Kitchen SET item_id='MT1' WHERE item_id='HD1';
UPDATE Kitchen SET item_id='MT2' WHERE item_id='HD2';
UPDATE Kitchen SET item_id='MT3' WHERE item_id='HD3';
UPDATE Kitchen SET item_id='MT4' WHERE item_id='HD4';
UPDATE Kitchen SET item_id='MT5' WHERE item_id='HD5';
UPDATE Kitchen SET item_id='LM1' WHERE item_id='SK1';
UPDATE Kitchen SET item_id='LM2' WHERE item_id='SK2';
UPDATE Kitchen SET item_id='LM3' WHERE item_id='SK3';
UPDATE Kitchen SET item_id='LM4' WHERE item_id='SK4';
UPDATE Kitchen SET item_id='LM5' WHERE item_id='SK5';
UPDATE Kitchen SET item_id='LM6' WHERE item_id='SK6';
UPDATE Kitchen SET item_id='LM7' WHERE item_id='SK7';
UPDATE Kitchen SET item_id='LM8' WHERE item_id='SK8';
UPDATE Kitchen SET item_id='LM9' WHERE item_id='SK9';
UPDATE Kitchen SET item_id='FJ1' WHERE item_id='CP1';
UPDATE Kitchen SET item_id='FJ2' WHERE item_id='CP2';
UPDATE Kitchen SET item_id='FJ3' WHERE item_id='CP3';
UPDATE Kitchen SET item_id='FJ4' WHERE item_id='CP4';
UPDATE Kitchen SET item_id='FJ5' WHERE item_id='CP5';
UPDATE Kitchen SET item_id='FJ6' WHERE item_id='CP6';
UPDATE Kitchen SET item_id='SM1' WHERE item_id='M1';
UPDATE Kitchen SET item_id='SM2' WHERE item_id='M2';
UPDATE Kitchen SET item_id='SM3' WHERE item_id='M3';
UPDATE Kitchen SET item_id='SM4' WHERE item_id='M4';
UPDATE Kitchen SET item_id='SM5' WHERE item_id='M5';
UPDATE Kitchen SET item_id='SM6' WHERE item_id='M6';

-- 3. Indian mobile numbers ------------------------------------------
UPDATE Accounts SET phone_number='+91 98450 11000' WHERE account_id=1;
UPDATE Accounts SET phone_number='+91 98450 11001' WHERE account_id=2;
UPDATE Accounts SET phone_number='+91 98450 11002' WHERE account_id=3;
UPDATE Accounts SET phone_number='+91 98450 11003' WHERE account_id=4;
UPDATE Accounts SET phone_number='+91 98450 11004' WHERE account_id=5;
UPDATE Accounts SET phone_number='+91 98450 11005' WHERE account_id=6;
UPDATE Accounts SET phone_number='+91 98450 11006' WHERE account_id=7;
UPDATE Accounts SET phone_number='+91 98450 11007' WHERE account_id=8;
UPDATE Accounts SET phone_number='+91 98450 11008' WHERE account_id=9;
UPDATE Accounts SET phone_number='+91 98450 11009' WHERE account_id=10;
UPDATE Accounts SET phone_number='+91 98450 11010' WHERE account_id=11;
UPDATE Accounts SET phone_number='+91 98450 11011' WHERE account_id=12;
UPDATE Accounts SET phone_number='+91 98450 11012' WHERE account_id=13;
UPDATE Accounts SET phone_number='+91 98450 11013' WHERE account_id=14;
UPDATE Accounts SET phone_number='+91 98450 11014' WHERE account_id=15;
UPDATE Accounts SET phone_number='+91 98450 11015' WHERE account_id=16;
UPDATE Accounts SET phone_number='+91 98450 11016' WHERE account_id=17;
UPDATE Accounts SET phone_number='+91 98450 11017' WHERE account_id=18;
UPDATE Accounts SET phone_number='+91 98450 11018' WHERE account_id=19;
UPDATE Accounts SET phone_number='+91 98450 11019' WHERE account_id=20;
UPDATE Accounts SET phone_number='+91 98450 11020' WHERE account_id=21;
UPDATE Accounts SET phone_number='+91 98450 11021' WHERE account_id=22;
UPDATE Accounts SET phone_number='+91 98450 11022' WHERE account_id=23;
UPDATE Accounts SET phone_number='+91 98450 11023' WHERE account_id=24;
UPDATE Accounts SET phone_number='+91 98450 11024' WHERE account_id=25;
UPDATE Accounts SET phone_number='+91 98450 11025' WHERE account_id=26;
UPDATE Accounts SET phone_number='+91 98450 11026' WHERE account_id=27;
UPDATE Accounts SET phone_number='+91 98450 11027' WHERE account_id=28;
UPDATE Accounts SET phone_number='+91 98450 11028' WHERE account_id=29;
UPDATE Accounts SET phone_number='+91 98450 11029' WHERE account_id=30;

-- 4. Remaining non-Indian free text ---------------------------------
UPDATE Reservations SET special_request='Prepare Crocin for me' WHERE special_request='Prepare Panadol for me';
UPDATE Reservations SET special_request='Pure veg / Jain options needed' WHERE special_request='Vegan options needed';
UPDATE Reservations SET special_request='Allergies: peanuts (no groundnut oil)' WHERE special_request='Allergies: peanuts';

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- Verify:
-- SELECT item_type, COUNT(*), MIN(item_price), MAX(item_price) FROM Menu GROUP BY item_type;
-- SELECT COUNT(*) AS orphans FROM Bill_Items b LEFT JOIN Menu m USING(item_id) WHERE m.item_id IS NULL;


-- ---------- migrations/003_rename_zaika_to_ember.sql ----------
-- Renames the menu data that carried the old restaurant name.
-- Run this against an existing restaurantdb instead of re-importing the dump,
-- so bills, reservations, accounts and orders are kept.

UPDATE Menu SET item_name = 'Ember Special Biryani' WHERE item_name = 'Zaika Special Biryani';
UPDATE Menu SET item_name = 'Ember Iced Tea'        WHERE item_name = 'Zaika Iced Tea';
UPDATE Menu SET item_type = 'Ember Cocktails'       WHERE item_type = 'Zaika Cocktails';

-- Check: all three should return 0 rows.
-- SELECT item_id, item_name, item_type FROM Menu WHERE item_name LIKE '%Zaika%' OR item_type LIKE '%Zaika%';

