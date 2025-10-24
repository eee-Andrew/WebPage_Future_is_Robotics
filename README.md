# WebPage Future is Robotics

This repository contains a PHP/MySQL web application inspired by the CrunchLabs landing page. It focuses on a public-facing catalog and a lightweight admin area for maintaining product information.

## Features

- Responsive storefront that displays every robotics kit with its database ID, caption, price, and inventory quantity.
- Simple admin pages for adding or removing products—no site login is required, so only your MariaDB/MySQL password is needed.
- Sample catalog data and SVG placeholders so the grid is populated immediately after importing the schema.
- Automatic base-path detection so the site keeps working even if you rename the project folder.

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
2. Run the SQL statements in [`crunchlabs-clone/database.sql`](crunchlabs-clone/database.sql) to create the `crunchlabs` database, the `products` table, and sample catalog rows.
3. Your MariaDB/MySQL account password is the only credential required to manage the site.

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
├── database.sql
├── db.php
└── index.php
```

Copy the entire `crunchlabs-clone` directory into your local web root (`htdocs/` for XAMPP or `/var/www/html/` for Apache on Linux).

## Configuration

Edit `crunchlabs-clone/config.php` and set `DB_USER` / `DB_PASS` to match your MySQL credentials. The default assumes the MySQL `root` user with an empty password (XAMPP default).

## Running the Site

1. Start Apache and MySQL from the XAMPP control panel (or via `systemctl` if you installed the native packages).
2. Visit the site at `http://localhost/<folder-name>/` (for example, `http://localhost/crunchlabs-clone/`). The `<folder-name>` must match the directory you copied into `htdocs/`—if you renamed the folder to `crunchlab-copy`, browse to `http://localhost/crunchlab-copy/`. The application auto-detects its folder name so navigation links continue to work after renaming.
3. Open `http://localhost/<folder-name>/admin/products.php` to add, update, or delete products. Because there is no site login, protect access to this page by restricting who can reach your local machine.

### Adding or Updating Product Photos

1. Place JPG/PNG/SVG assets under `crunchlabs-clone/assets/img/products/`. A `placeholder.svg` file ships with the repo so the catalog always has a fallback.
2. When creating a product in the admin dashboard, set **Image Path** to `assets/img/products/<file-name>` (omit the leading slash and folder name). You can also provide a full `http(s)` URL for externally hosted images.
3. The storefront automatically displays the `name` column as the caption and the auto-increment `id` as the unique product identifier beneath each image.
4. Add additional products any time—no layout updates are required. The responsive grid expands to fit as many cards as you need.

## Troubleshooting

- **Access forbidden / blank page** – confirm the project folder is inside the XAMPP `htdocs` directory (or `/var/www/html` on Linux) and that file permissions allow Apache to read the files.
- **Database connection failed** – confirm the MySQL service is running (green indicator inside XAMPP), the credentials in `config.php` match your MySQL setup, and that the `crunchlabs` database/tables were created from `database.sql`.
- **"MySQL server has gone away"** – this indicates the database service dropped the connection (often after it has been idle). Start MySQL before loading the site and refresh the page—the application will automatically re-establish the connection using the retry logic in `db.php` when the service comes back online.

## Security Notice

With the password-only database approach requested for this setup, there are no authentication or CSRF protections on the PHP pages. Do not expose this application to the public internet without adding proper access controls.
