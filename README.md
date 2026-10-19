# Ember — Dining & Bar

A full restaurant system for **Ember**, a modern Indian restaurant in Vellore: a customer website, table-side QR ordering, a live kitchen screen and a staff panel for running service, all on one MariaDB database.

Built as the DBMS course project (PAMCA503) by **Aryan Ayush · 26MCA0009**, under Dr. M. Rajkumar.

---

## What it does

**For guests**
- An editorial homepage with a 3D dish, scroll-driven scenes and the restaurant's own video
- The full menu with live search and filters, an order basket and a PDF bill
- Table reservations that only offer tables that are actually free
- Member accounts that earn loyalty points (1 point per ₹1, VIP at 1,000)
- **QR table ordering**: scan the code on the table, order from the phone, and follow the dish from *waiting* → *cooking* → *ready*

**For staff**
- **Point of Sale**: tap a table, add dishes, take cash or card, print the receipt
- **Kitchen screen**: new orders from the POS and from QR tables appear by themselves
- **Table Setup** with a printable QR code for every table
- Menu, reservations, members, staff and accounts in one place
- **Reports**: revenue, best-selling dishes and insights built with window functions
- **Price history**: see the menu exactly as it was on any past date

---

## Database highlights

| Feature | Where it is used |
| --- | --- |
| Transactions with `SELECT … FOR UPDATE` | Adding dishes to a bill, taking payment, booking a table — two guests can never book the same table |
| System-versioned table (`Menu … WITH SYSTEM VERSIONING`) | The price "time machine" (`FOR SYSTEM_TIME AS OF`) |
| Window functions (`RANK`, `LAG`, `SUM OVER`, `NTILE`) | Insights: dish rankings, day-on-day change, running totals, member tiers |
| Stored procedures | `generate_history(days)` and `remove_history()` create and clear sample trading history |
| Foreign keys and indexes | 12 related tables, with an index on bill time for the reports |

**Tables:** `Accounts`, `Memberships`, `Staffs`, `Menu`, `Restaurant_Tables`, `Table_Availability`, `Reservations`, `Bills`, `Bill_Items`, `card_payments`, `Kitchen`, `History_Runs`

---

## Built with

PHP 8 · MariaDB 10.4 · HTML, CSS and plain JavaScript · Three.js (3D dish) · Lenis (smooth scrolling) · FPDF (bills and reports) · qrcode-generator · Docker

---

## Project structure

```
Ember/
├── customerSide/          the guest website
│   ├── home/              homepage
│   ├── menu/              menu, basket, PDF bill
│   ├── order/             QR table ordering and order tracker
│   ├── CustomerReservation/
│   ├── customerLogin/     member login, sign-up, profile
│   ├── components/        shared header, footer, page transitions
│   ├── css/  js/  image/
├── adminSide/             the staff panel
│   ├── StaffLogin/
│   ├── panel/             every staff page (POS, kitchen, tables, reports …)
│   ├── posBackend/        orders, payments, receipts, kitchen tickets
│   ├── menuCrud/  staffCrud/  customerCrud/  tableCrud/  reservationsCrud/
│   ├── lab/  report/  includes/  css/  js/
├── database/
│   ├── restaurantDB.sql   the complete database (tables, data, procedures)
│   ├── advanced_features.sql
│   └── deploy_import.sql
├── db.php                 database connection
├── index.php
└── Dockerfile · render.yaml
```

---

## Running it

Put the folder in XAMPP's `htdocs`, import `database/restaurantDB.sql` in phpMyAdmin, and open `http://localhost/RestaurantProject/`.

---

## License

Reviews on the homepage are samples. Licensed under the Apache License 2.0 — see [LICENSE](LICENSE).
