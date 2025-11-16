CREATE DATABASE IF NOT EXISTS roboforge;
USE roboforge;

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
    status ENUM('PendingPayment','Paid','Failed','Cancelled') NOT NULL DEFAULT 'PendingPayment',
    total DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    full_name VARCHAR(150) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(120) NOT NULL,
    postal_code VARCHAR(40) NOT NULL,
    country VARCHAR(120) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(60) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    id CHAR(36) PRIMARY KEY,
    order_id INT NOT NULL,
    merchant_reference VARCHAR(255) NOT NULL,
    adyen_psp_reference VARCHAR(100),
    status ENUM('Pending','Authorised','Refused','Cancelled','Error','Chargeback','Refunded') NOT NULL DEFAULT 'Pending',
    amount_minor INT NOT NULL,
    currency CHAR(3) NOT NULL,
    payment_method_type VARCHAR(50),
    result_code VARCHAR(50),
    three_ds_result VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
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

CREATE TABLE IF NOT EXISTS adyen_shoppers (
    user_id INT PRIMARY KEY,
    shopper_reference VARCHAR(191) NOT NULL,
    email VARCHAR(255) NOT NULL,
    billing_country VARCHAR(2),
    ip_country VARCHAR(2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_shoppers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payment_tokens (
    id CHAR(36) PRIMARY KEY,
    shopper_reference VARCHAR(191) NOT NULL,
    recurring_detail_reference VARCHAR(100) NOT NULL,
    stored_payment_method_id VARCHAR(100),
    brand VARCHAR(50),
    card_last4 CHAR(4),
    expiry_month CHAR(2),
    expiry_year CHAR(4),
    recurring_processing_model ENUM('CardOnFile','Subscription','UnscheduledCardOnFile') DEFAULT 'CardOnFile',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_shopper_detail (shopper_reference, recurring_detail_reference)
);

CREATE TABLE IF NOT EXISTS webhook_events (
    id CHAR(36) PRIMARY KEY,
    event_date DATETIME NOT NULL,
    event_code VARCHAR(60) NOT NULL,
    success TINYINT(1) NOT NULL,
    psp_reference VARCHAR(100) NOT NULL,
    original_reference VARCHAR(100),
    payload JSON,
    processed TINYINT(1) NOT NULL DEFAULT 0,
    processed_at DATETIME NULL,
    KEY idx_webhook_reference (psp_reference),
    KEY idx_webhook_processed (processed)
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
