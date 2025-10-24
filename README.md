# WebPage Future is Robotics

This repository contains a PHP/MySQL web application inspired by the CrunchLabs landing page. It includes:

- A public storefront that focuses on the product catalog with per-item photos, captions, and prices.
- User registration and login backed by secure password hashing.
- An admin dashboard for viewing user/product statistics and managing inventory.
- A MySQL schema for users and product catalog data.

## Prerequisites

Install a local PHP + MySQL stack. Two common options are:

### Option A: XAMPP (Windows/macOS/Linux)
1. Download from [apachefriends.org](https://www.apachefriends.org/).
2. Launch the XAMPP Control Panel (on Windows, right-click and choose **Run as administrator** so services can bind to ports 80/443).
3. When the status log mentions "think about running this application with administrator rights," accept the Windows security/UAC prompt if it appears and continue.
4. Click **Start** beside **Apache** and **MySQL** until both entries turn green.
5. Use the **Shell** button to open a terminal for running MySQL commands when needed.

### Option B: Native packages on Ubuntu/Debian
```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql mariadb-server mariadb-client
sudo systemctl enable --now apache2 mariadb
sudo mysql_secure_installation
```

Make sure the `pdo_mysql` extension is enabled in your `php.ini` (uncomment `extension=pdo_mysql`). Restart Apache after changes.

## Database Setup

1. Launch the MySQL shell (`mysql -u root -p`).
2. Run the SQL statements in [`crunchlabs-clone/database.sql`](crunchlabs-clone/database.sql) to create the `crunchlabs` database, tables, seed data, and admin user.
3. Update the admin email/password in the script before running if desired. The seed password hash in the file was generated with PHP's `password_hash()` for better security.

## Project Structure

```
crunchlabs-clone/
├── admin/
│   ├── dashboard.php
│   └── products.php
├── assets/
│   ├── css/
│   │   └── styles.css
│   └── img/
│       └── products/
│           ├── README.md
│           └── placeholder.svg
├── partials/
│   ├── footer.php
│   └── header.php
├── config.php
├── security.php
├── database.sql
├── db.php
├── index.php
├── login.php
├── logout.php
└── register.php
```

Copy the entire `crunchlabs-clone` directory into your local web root (`htdocs/` for XAMPP or `/var/www/html/` for Apache on Linux).

## Configuration

Edit `crunchlabs-clone/config.php` and set `DB_USER` / `DB_PASS` to match your MySQL credentials. The default assumes the MySQL `root` user with an empty password (XAMPP default).

## Running the Site

1. Start Apache and MySQL from the XAMPP control panel (or via `systemctl` if you installed the native packages).
2. Visit `http://localhost/crunchlabs-clone/` to browse the product catalog presented to customers.
3. Register a new account or log in using the seeded admin credentials (`admin@example.com` / `AdminPass123!`).
4. Admins can manage products at `http://localhost/crunchlabs-clone/admin/products.php`.

### Adding or Updating Product Photos

1. Place JPG/PNG/SVG assets under `crunchlabs-clone/assets/img/products/`. A `placeholder.svg` file ships with the repo so the catalog always has a fallback.
2. When creating or editing a product in the admin dashboard, set **Image Path** to `/crunchlabs-clone/assets/img/products/<file-name>`.
3. The storefront automatically displays the `name` column as the caption and the auto-increment `id` as the unique product identifier beneath each image.
4. Add additional products any time—no layout updates are required. The responsive grid expands to fit as many cards as you need.

## Viewing Accounts in the Database

Once MySQL is running you can inspect the stored users (including their roles and password hashes) either through phpMyAdmin or
the MySQL shell:

### Option A: phpMyAdmin (bundled with XAMPP)
1. Open `http://localhost/phpmyadmin/` in your browser.
2. Sign in with your MySQL credentials (XAMPP default is user `root` with an empty password unless you changed it).
3. In the left sidebar choose the `crunchlabs` database, then click the `users` table.
4. phpMyAdmin will list every account along with the `role` column so you can confirm who is an admin.

### Option B: MySQL command line
1. Open the XAMPP **Shell** (or any terminal) and run `mysql -u root -p` (omit `-p` if your root account has no password).
2. Select the database: `USE crunchlabs;`
3. Query the table: `SELECT id, email, role, created_at FROM users;`
4. To exit the shell type `exit`.

Passwords are stored as secure hashes, so you will not see the original plain-text password—only the hashed value in the
`password_hash` column.

## Security Hardening

The original prototype left several openings that could be abused. The current version includes the following safeguards:

- **Session hardening:** Cookies are now sent with `HttpOnly`, `SameSite=Lax`, and optional `Secure` flags, and PHP strict mode reduces session fixation.
- **CSRF protection:** Login, registration, and all admin product forms include verified CSRF tokens. Destructive actions (product deletion) now require POST instead of a GET link.
- **Password hashing:** The seeded admin user uses the same `password_hash()` algorithm as runtime registrations, ensuring consistent bcrypt hashes.
- **Logout sanitization:** Session data and cookies are fully cleared before redirecting to the storefront.

For production deployments you should still enforce HTTPS, add rate limiting to login attempts, and consider stronger password and audit policies.
