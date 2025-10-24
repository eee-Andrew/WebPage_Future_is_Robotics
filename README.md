# WebPage Future is Robotics

This repository contains a PHP/MySQL web application inspired by the CrunchLabs landing page. It includes:

- A public landing page with feature highlights and product listings.
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
2. Run the SQL statements in [`crunchlabs-clone/database.sql`](crunchlabs-clone/database.sql) to create the `crunchlabs` database, tables, and seed admin user.
3. Update the admin email/password in the script before running if desired (password uses MySQL `SHA2` for the seed user).

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
├── partials/
│   ├── footer.php
│   └── header.php
├── config.php
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
2. Visit `http://localhost/crunchlabs-clone/` to view the landing page.
3. Register a new account or log in using the seeded admin credentials (`admin@example.com` / `AdminPass123!`).
4. Admins can manage products at `http://localhost/crunchlabs-clone/admin/products.php`.

Add product images to `crunchlabs-clone/assets/img/` and reference them via the **Image Path** field on the admin product form.

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

## Security Notes

- This project is intended for local demos. For production use, add CSRF protection, stricter validation, HTTPS, and proper password policies.
- The seed admin password is stored using MySQL's `SHA2` function; all new users created via the site use PHP's `password_hash`/`password_verify`.
