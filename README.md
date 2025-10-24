# WebPage Future is Robotics

This repository contains a PHP/MySQL web application inspired by the CrunchLabs landing page. It now includes customer accounts, saved items, a shopping cart with checkout capture, multilingual copy (English/Greek), and a lightweight admin area for maintaining catalog data.

## Features

- Responsive storefront grouped by learning stage (preschool, primary, high school, university) with product overlays that reveal detailed specs, per-kit IDs, and image captions.
- Customer accounts with login/registration, saved items, and a persistent cart so each user can resume where they left off.
- Card-style cart page that lets a customer update quantities and submit shipping + payment details; orders and line items are stored in MySQL for reference.
- English/Greek language toggle that updates navigation, hero copy, and section headings without leaving the page.
- Simple admin pages for adding/removing products (name, short/long description, category, price, quantity, image path) alongside basic catalog/order metrics.
- Sample catalog data, SVG placeholders, and helper functions that keep URLs working even if you rename the project folder.

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
2. From the `MariaDB [(none)]>` prompt, run the script with MySQL's `SOURCE` command:

   ```sql
   SOURCE C:/xampp/htdocs/crunchlabs-clone/database.sql;
   ```

   Adjust the path if your folder lives elsewhere. On macOS/Linux the command looks like `SOURCE /Applications/XAMPP/htdocs/crunchlabs-clone/database.sql;` (note the forward slashes).
3. After the script finishes you should see `Database changed` followed by a series of `Query OK` messages. You can confirm the data is ready with:

   ```sql
   USE crunchlabs;
   SELECT id, name, price FROM products;
   ```
4. The SQL script also seeds a demo admin user (`admin@example.com` / `AdminPass123!`) plus eight products—update or remove them as needed.

## Project Structure

```
crunchlabs-clone/
├── admin/
│   ├── dashboard.php
│   └── products.php
├── assets/
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   └── app.js
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
├── account.php
├── cart.php
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
2. Visit the site at `http://localhost/<folder-name>/` (for example, `http://localhost/crunchlabs-clone/`). The `<folder-name>` must match the directory you copied into `htdocs/`—if you renamed the folder to `crunchlab-copy`, browse to `http://localhost/crunchlab-copy/`. The application auto-detects its folder name so navigation links continue to work after renaming.
3. Visit `http://localhost/<folder-name>/register.php` to create a customer account or use the seeded admin credentials above on the login page. The account icon (top-right) also links to the login/registration flow.
4. After signing in you can:
   - Click any product tile to open its overlay, then add the kit to your cart or save it for later.
   - Use the **Account** page to review/remove saved items.
   - Open the **Cart** page to adjust quantities and fill out the shipping/payment form. Submitting the form stores an order and clears the cart so it is ready for the next session.
5. Administrators can manage products via `http://localhost/<folder-name>/admin/products.php` and review catalog/order stats at `http://localhost/<folder-name>/admin/dashboard.php`. These pages rely solely on your local server access—add authentication before exposing them publicly.

### Adding or Updating Product Photos

1. Place JPG/PNG/SVG assets under `crunchlabs-clone/assets/img/products/`. A `placeholder.svg` file ships with the repo so the catalog always has a fallback.
2. When creating a product in the admin dashboard, set **Image Path** to `assets/img/products/<file-name>` (omit the leading slash and folder name). You can also provide a full `http(s)` URL for externally hosted images.
3. Choose the appropriate **Category** (Preschool, Primary, High School, University). Each section of the home page reads from that column so products appear under the correct dropdown option.
4. The storefront automatically displays the `name` column as the caption and the auto-increment `id` as the unique product identifier beneath each image.
5. Add additional products any time—no layout updates are required. The responsive grid expands to fit as many cards as you need.

## Troubleshooting

- **Access forbidden / blank page** – confirm the project folder is inside the XAMPP `htdocs` directory (or `/var/www/html` on Linux) and that file permissions allow Apache to read the files.
- **Database connection failed** – confirm the MySQL service is running (green indicator inside XAMPP), the credentials in `config.php` match your MySQL setup, and that the `crunchlabs` database/tables were created from `database.sql`.
- **"MySQL server has gone away"** – this indicates the database service dropped the connection (often after it has been idle). Start MySQL before loading the site and refresh the page—the application will automatically re-establish the connection using the retry logic in `db.php` when the service comes back online.

## Security Notice

User login, saved items, and cart data rely on PHP sessions and server-side validation, but the admin tools remain unprotected. Keep the project on a trusted local machine or add authentication, CSRF protection, and TLS before deploying anywhere beyond a private lab environment.
