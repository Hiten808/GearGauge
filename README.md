# Gear Gauge — Tech Spec & Gadget Comparison Portal

A full-stack web application built as a Web Technology course project. Gear Gauge lets visitors compare two gadgets (laptops and phones) side by side, browse a searchable device catalog, save their favourite comparisons to their account, and lets an administrator manage the device catalog with full CRUD and image uploads.

Built entirely with **vanilla HTML5, CSS3, JavaScript (ES6) and procedural PHP 8 with MySQLi** — no frameworks, no libraries.

## Features

| Area | Details |
| --- | --- |
| Side-by-side comparison | Two dropdowns on the home page build a full spec table. JavaScript highlights the winning value in green for RAM, storage and battery (higher wins) and price (lower wins). |
| Live search | `browse.php` shows every device in a responsive grid; the search box filters cards instantly on the client side (no page reloads). |
| Authentication | Register and login with email + password. Passwords are stored with `password_hash()` and verified with `password_verify()`. Session fixation is mitigated with `session_regenerate_id()`. |
| Saved comparisons | Logged-in users can save any comparison pair and reopen or delete it from their profile page. |
| Admin dashboard | Only users with the `admin` role can open `admin.php` and `edit_device.php`. Admins add, edit and delete devices and upload device images (JPG/PNG/WEBP, max 2 MB, validated by extension and real image content). |
| Security basics | Every database query uses a MySQLi prepared statement (`?` placeholders) and every piece of output is escaped with `htmlspecialchars()`. |

## Tech Stack

- **Frontend:** HTML5, CSS3 (CSS Grid + Flexbox, responsive dark-slate theme), vanilla ES6 JavaScript
- **Backend:** Procedural PHP 8 with the `mysqli` extension
- **Database:** MySQL / MariaDB (schema and seed data in `database.sql`)

## Folder Structure

```
GearGauge/
├── index.php              Home page: device selectors + comparison table + save comparison
├── browse.php             Grid view of all devices with live JS search filter
├── login.php              Login form and session creation
├── register.php           Registration form with client + server validation
├── logout.php             Destroys the session and redirects home
├── profile.php            Logged-in user's saved comparisons (open / delete)
├── admin.php              Admin dashboard: add device (with image upload), list, delete
├── edit_device.php        Admin form to update an existing device
├── database.sql           Complete schema + 6 seeded devices + default admin account
├── includes/
│   ├── db.php             MySQLi connection (env vars with XAMPP fallback) + session start
│   ├── header.php         Shared <head>, navigation bar and flash messages
│   └── footer.php         Shared footer + script include
├── css/
│   └── style.css          Full responsive stylesheet
├── js/
│   └── script.js          Form validation, live search filter, spec winner highlighting
└── uploads/               Device images (kept out of git, only .gitkeep tracked)
```

## Running Locally with XAMPP

1. **Install XAMPP** and start **Apache** and **MySQL** from the control panel.
2. **Copy the project** into `C:\xampp\htdocs\GearGauge` (Windows) or `/opt/lampp/htdocs/GearGauge` (Linux).
3. **Create the database:** open phpMyAdmin at `http://localhost/phpmyadmin`, go to the *Import* tab, and import `database.sql`. This creates the `specmatrix_db` database, all three tables, 6 sample devices and the default admin account.
4. **Open the site:** visit `http://localhost/GearGauge/`.
5. **Log in as admin:** `admin@specmatrix.com` / `admin123` — then open the Admin Panel to add devices.

The connection settings in `includes/db.php` read environment variables first and fall back to the standard XAMPP defaults (`localhost` / `root` / empty password / `specmatrix_db`), so no code changes are needed for local development.

### Optional `.env`-style configuration

On hosts where you cannot edit the web server config, `getenv()` values can be set in the host's dashboard (Environment Variables on Railway, for example). Locally you can also hardcode-test by temporarily editing the four fallback values in `includes/db.php`.

## GitHub Workflow

```bash
git init
git add .
git commit -m "Gear Gauge: initial course submission"
git branch -M main
git remote add origin https://github.com/<your-username>/gear-gauge.git
git push -u origin main
```

The included `.gitignore` keeps uploaded images, `.env` files and OS junk (`.DS_Store`) out of version control while preserving `uploads/.gitkeep` so the folder structure still deploys.

Everyday workflow:

```bash
git add .
git commit -m "Describe your change"
git push
```

## Deployment

### Option A — Free shared PHP hosting (InfinityFree, byet.host, etc.)

1. Create a free account and a new website; note the assigned FTP host, username and password.
2. In the hosting control panel create a MySQL database and note the database name, user and password (InfinityFree database hosts look like `sqlXXX.infinityfree.com`).
3. Upload all project files to `htdocs/` using File Manager or an FTP client such as FileZilla.
4. Open phpMyAdmin on the host and import `database.sql`. If the host forces a prefixed database name (e.g. `epiz_123_specmatrix_db`), update the database name — either in the host's environment settings or the fallback value in `includes/db.php`.
5. Make sure the `uploads/` directory has write permissions (usually `755` or `777` on these hosts) so image uploads work.
6. Visit your site URL and log in with the admin credentials above.

### Option B — Railway (PHP + MySQL container)

1. Push the repository to GitHub.
2. Create a new deployment on Railway from the repo, using a PHP/Apache base image (e.g. `php:8.2-apache`) with a start command that enables `mysqli` and serves the app.
3. Add a MySQL instance and a public proxy in the project; Railway exposes the connection as environment variables.
4. Map the variables in `includes/db.php` in the service variables panel: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
5. Import `database.sql` against the Railway MySQL instance (via a temporary phpMyAdmin container or the Railway query console).

## Academic Notes (Viva Preparation)

- **Why prepared statements?** They separate SQL logic from data, which prevents SQL injection even with untrusted input.
- **Why `htmlspecialchars()` on output?** It neutralises `<`, `>`, `&` and quotes in user data so stored XSS payloads render as harmless text.
- **How does the winner highlighting work?** `index.php` renders numeric cells with `data-field` and `data-value` attributes; `js/script.js` compares the two cells for each numeric field and adds a `spec-winner` class to the better one (higher for RAM/storage/battery, lower for price).
- **How does the live search work?** Each device card carries a lowercase `data-search` attribute; typing filters cards with `String.includes()` and toggles a `hidden` class — no server round trip.
- **Session flow:** `includes/db.php` starts the session on every page. Login stores `user_id`, `username` and `role` in `$_SESSION`; `admin.php` and `edit_device.php` check `role === 'admin'` before anything renders, otherwise redirecting to `login.php`.
