CREATE DATABASE IF NOT EXISTS restaurantdb; -- Change to your database name
USE restaurantdb;

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



