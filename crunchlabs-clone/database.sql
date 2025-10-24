CREATE DATABASE IF NOT EXISTS crunchlabs;
USE crunchlabs;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, description, price, quantity, image_path) VALUES
('Robot Arm Lab', 'Build a fully articulated robotic arm that responds to joystick controls.', 149.00, 12, 'assets/img/products/kit-robot-arm.svg'),
('Explorer Rover', 'Design a six-wheel rover with obstacle detection for rough terrain missions.', 129.00, 15, 'assets/img/products/kit-explorer-rover.svg'),
('Aerial Drone Lab', 'Assemble a quad-copter and learn the basics of flight stabilization.', 179.00, 8, 'assets/img/products/kit-drone-lab.svg'),
('AI Mission Station', 'Train onboard models with real-time sensor data and deploy automation routines.', 199.00, 6, 'assets/img/products/kit-ai-station.svg'),
('Cyber Defense Kit', 'Simulate cybersecurity scenarios and learn how to harden IoT devices.', 139.00, 10, 'assets/img/products/kit-cyber-defense.svg'),
('Hydraulics Lab', 'Experiment with hydraulic power using syringes, pistons, and control valves.', 119.00, 18, 'assets/img/products/kit-hydraulics.svg'),
('Sensor Fusion Hub', 'Combine IMU, lidar, and environmental sensors into a single control interface.', 159.00, 9, 'assets/img/products/kit-sensor-hub.svg'),
('Coding Console', 'Learn embedded programming with a modular console and plug-in challenges.', 99.00, 20, 'assets/img/products/kit-coding-console.svg'),
('Space Bot Kit', 'Construct an extraterrestrial assistant with interchangeable tool heads.', 189.00, 7, 'assets/img/products/kit-space-bot.svg'),
('Bio-Tech Lab', 'Explore bio-inspired robotics with soft grippers and growth simulation tasks.', 129.00, 11, 'assets/img/products/kit-bio-tech.svg');
