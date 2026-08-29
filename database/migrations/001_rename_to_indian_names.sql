-- Rename all staff + membership customers to Indian names.
-- Run against the existing restaurantDB; keyed on account_id so it is safe to re-run.
-- Generated for RestaurantProject.

USE restaurantdb;
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
