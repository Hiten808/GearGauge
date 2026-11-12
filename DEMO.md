# Gear Gauge — Viva Demo Script

A step-by-step walkthrough for demonstrating the project. Follow it in order; each step takes under a minute.

## Before You Start

1. Open the XAMPP Control Panel and make sure **Apache** and **MySQL** are both running.
2. Open the site in the browser: `http://localhost/GearGauge/`
3. Keep this repo open in the editor so you can show source code when asked.

## Demo Walkthrough

### 1. Compare Two Devices (home page)

- Open `http://localhost/GearGauge/`
- Pick **Device 1** = MacBook Air 13 M3 and **Device 2** = Galaxy S24 Ultra.
- The table auto-loads when you change a dropdown (JavaScript `change` event submits the form).
- Point out the **green highlighted cells** — JavaScript marks the winner for RAM, storage and battery (higher wins) and price (lower wins).
- Click a device name in the table — it opens that product's detail page.

### 2. Product Detail Template

- On `device.php?id=1`, explain: **one single PHP template** serves all 30 products.
- The page reads `?id=`, validates it, and runs **one prepared statement** (`SELECT ... WHERE id = ?`).
- Show a different product (`device.php?id=13`) and the related-products row at the bottom.
- Show `device.php?id=9999` → clean "Device Not Found" page instead of an error.

### 3. Browse and Filter

- Open **Browse Devices**.
- Type `samsung` in the search box — the grid filters live, no page reload.
- Click the **Android TVs (5)** chip — category filtering combines with the search box.
- Show that every card links to its product page (View button / card image).

### 4. Accounts

- Log out (if logged in), then go to **Register** and create an account.
- Explain `password_hash()` and `password_verify()` are used — passwords are never stored in plain text.
- Compare two devices again and click **Save Comparison**.
- Open **My Comparisons** — reopen it via **Open**, then delete one (a confirmation dialog appears).

### 5. Admin Panel

- Log in as `admin@specmatrix.com` / `admin123`.
- The **Admin Panel** link only appears for admin-role users; visiting `admin.php` directly when logged out redirects to the login page.
- Add a device with the form (try uploading an image — validation only allows JPG/PNG/WEBP up to 2 MB).
- Edit that device, change something, save.
- Delete the test device and confirm it disappears from Browse.

### 6. Security Points (when asked)

- **SQL injection:** every query uses prepared statements — show one in `admin.php` or `device.php`.
- **XSS:** every piece of output passes through `htmlspecialchars()` — search for it in `browse.php`.
- **Password storage:** `password_hash()` / `password_verify()` in `register.php` / `login.php`.
- **Access control:** admin pages check the session role and redirect non-admins.
- **Database config:** `includes/db.php` reads environment variables with XAMPP fallbacks — no hardcoded secrets.

## Where Things Live

| Feature | File |
| --- | --- |
| Comparison page + save | `index.php` |
| Catalog with live filters | `browse.php` |
| Single product template | `device.php` |
| Registration / login | `register.php`, `login.php` |
| Saved comparisons | `profile.php` |
| Admin dashboard | `admin.php`, `edit_device.php` |
| Database schema + seed data | `database.sql` |
| JavaScript behaviour | `js/script.js` |
| Styling | `css/style.css` |

## Troubleshooting

- **Site shows a database error** → MySQL is not running in XAMPP.
- **Blank page** → check Apache is running and the project is in `C:\xampp\htdocs\GearGauge`.
- **Fresh database setup** → import `database.sql` in phpMyAdmin (creates the DB, tables, admin account and 30 devices).
