CREATE DATABASE IF NOT EXISTS crunchlabs;
USE crunchlabs;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS product_categories (
    slug VARCHAR(50) PRIMARY KEY,
    label VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    long_description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255),
    category_slug VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_slug)
        REFERENCES product_categories(slug)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS saved_items (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart_items (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(120) NOT NULL,
    postal_code VARCHAR(40) NOT NULL,
    country VARCHAR(120) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(60) NOT NULL,
    card_last4 CHAR(4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_each DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (order_id, product_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

INSERT INTO product_categories (slug, label) VALUES
    ('preschool', 'Preschool Builders'),
    ('primary', 'Primary Explorers'),
    ('highschool', 'High School Innovators'),
    ('university', 'University Research Lab')
ON DUPLICATE KEY UPDATE label = VALUES(label);

INSERT INTO users (name, email, password_hash) VALUES
    ('Demo Admin', 'admin@example.com', '$2y$12$/5UmW1KZ5BvO4gN/Gf5hKeCWw.WDTowLcN3jr20f5IvC7Ss68r0wy')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO products (name, short_description, long_description, price, quantity, image_path, category_slug) VALUES
('Robot Arm Lab', 'Assemble an articulated robotic arm with joystick control.', 'Master servo calibration, coordinate systems, and motion planning by building a four-axis robotic arm. The kit includes pre-cut aluminum pieces, a control board, and challenge cards to keep makers engaged.', 149.00, 12, 'assets/img/products/kit-robot-arm.svg', 'university'),
('Aerial Drone Lab', 'Learn quadcopter physics and stability loops.', 'Calibrate brushless motors, configure PID loops, and understand thrust-to-weight ratios while assembling a fully functional drone platform ready for indoor flight labs.', 179.00, 8, 'assets/img/products/kit-drone-lab.svg', 'university'),
('Explorer Rover', 'Construct a rover that handles uneven terrain.', 'The Explorer Rover introduces differential steering, suspension tuning, and obstacle avoidance algorithms. Great for intermediate builders transitioning into autonomous navigation.', 129.00, 15, 'assets/img/products/kit-explorer-rover.svg', 'highschool'),
('Bio-Tech Lab', 'Explore bio-inspired robotics concepts.', 'Hands-on labs teach soft robotics, biomimicry, and environmental sensing. Learners craft responsive grippers inspired by nature.', 129.00, 11, 'assets/img/products/kit-bio-tech.svg', 'highschool'),
('Cyber Defense Kit', 'Harden IoT devices against attacks.', 'Simulate cybersecurity incidents, run penetration exercises, and patch vulnerabilities on embedded boards. The kit includes guided labs for best practices in secure robotics.', 139.00, 10, 'assets/img/products/kit-cyber-defense.svg', 'primary'),
('Sensor Fusion Hub', 'Combine data from multiple sensors seamlessly.', 'Integrate IMU, lidar, and environmental data streams into one control interface. Students learn how to denoise signals and create adaptive responses in real time.', 159.00, 9, 'assets/img/products/kit-sensor-hub.svg', 'primary'),
('Hydraulics Lab', 'Experiment with power using hydraulic systems.', 'Young builders discover Pascal’s law while manipulating syringes and pistons. Each activity card blends storytelling with hands-on engineering to keep preschool minds engaged.', 119.00, 18, 'assets/img/products/kit-hydraulics.svg', 'preschool'),
('Coding Console', 'Start embedded programming challenges.', 'Modular plug-ins teach loops, conditionals, and event-driven logic in a playful environment. Perfect for early coders building toward robotics literacy.', 99.00, 20, 'assets/img/products/kit-coding-console.svg', 'preschool')
ON DUPLICATE KEY UPDATE short_description = VALUES(short_description), long_description = VALUES(long_description), price = VALUES(price), quantity = VALUES(quantity), image_path = VALUES(image_path), category_slug = VALUES(category_slug);
