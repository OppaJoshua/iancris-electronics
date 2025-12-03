-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS iancris_electronics;
USE iancris_electronics;

-- Drop tables in correct order (to handle foreign keys)
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS gallery;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;

-- Users Table (with status column)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firebase_uid VARCHAR(255) UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    password VARCHAR(255),
    google_id VARCHAR(255),
    display_name VARCHAR(255),
    photo_url TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admins Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    images TEXT,
    category VARCHAR(100),
    image_url TEXT,
    stock_status VARCHAR(50) DEFAULT 'In Stock',
    stock INT DEFAULT 0,
    specifications TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Requests Table
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_email VARCHAR(255) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_phone VARCHAR(50),
    products TEXT NOT NULL,
    total_items INT NOT NULL,
    message TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    admin_notes TEXT,
    appointment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_phone VARCHAR(20) NOT NULL,
    user_address TEXT,
    total_items INT NOT NULL DEFAULT 0,
    status ENUM('pending', 'confirmed', 'scheduled', 'completed', 'cancelled') DEFAULT 'pending',
    installation_date DATE NULL,
    installation_time TIME NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery Table
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    image VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin users
INSERT INTO users (first_name, last_name, email, password, role, status) VALUES 
('Admin', 'User', 'admin@iancris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('Joshua', 'Calago', 'joshuacalago649@gmail.com', NULL, 'admin', 'active');

-- Insert sample products
INSERT INTO products (name, description, price, image, image_url, category, stock_status, stock, status, specifications) VALUES
('CCTV Camera Set - 4 Channel', 'Complete 4-channel CCTV system with DVR and cameras', 2999.99, 'cctv-4ch.jpg', 'uploads/products/cctv-4ch.jpg', 'CCTV Systems', 'In Stock', 10, 'active', '1080p resolution, Night vision, Weatherproof'),
('IP Camera', 'Wireless IP camera with mobile app support', 1599.50, 'ip-camera.jpg', 'uploads/products/ip-camera.jpg', 'CCTV Systems', 'In Stock', 15, 'active', '2MP, WiFi enabled, Motion detection'),
('Network Switch 8-Port', '8-port gigabit ethernet switch', 899.99, 'switch-8port.jpg', 'uploads/products/switch-8port.jpg', 'Networking', 'In Stock', 8, 'active', 'Gigabit speed, Plug and play'),
('Cat6 Cable - 305m', 'Cat6 ethernet cable roll', 2499.00, 'cat6-cable.jpg', 'uploads/products/cat6-cable.jpg', 'Cables', 'In Stock', 5, 'active', '305 meters, 23AWG, Blue color'),
('HDMI Cable 5m', 'High-speed HDMI cable', 499.50, 'hdmi-cable.jpg', 'uploads/products/hdmi-cable.jpg', 'Cables', 'In Stock', 20, 'active', '5 meters, 4K support, Gold plated'),
('Router Dual Band', 'Dual band wireless router', 1299.99, 'router.jpg', 'uploads/products/router.jpg', 'Networking', 'In Stock', 12, 'active', 'AC1200, 4 LAN ports, 2.4GHz & 5GHz'),
('DVR 8 Channel', '8-channel digital video recorder', 4599.00, 'dvr-8ch.jpg', 'uploads/products/dvr-8ch.jpg', 'CCTV Systems', 'In Stock', 6, 'active', '1080p recording, H.264, 1TB HDD'),
('Power Supply 12V', '12V DC power supply for cameras', 299.00, 'power-supply.jpg', 'uploads/products/power-supply.jpg', 'Accessories', 'In Stock', 25, 'active', '12V 2A, Universal input'),
('BNC Connector Pack', 'Pack of 50 BNC connectors', 199.50, 'bnc-connector.jpg', 'uploads/products/bnc-connector.jpg', 'Accessories', 'In Stock', 30, 'active', '50 pieces, Compression type'),
('Cable Tester', 'Network cable tester tool', 599.00, 'cable-tester.jpg', 'uploads/products/cable-tester.jpg', 'Tools', 'In Stock', 8, 'active', 'Tests RJ45 & RJ11, LED indicators');

-- Create indexes for better performance
CREATE INDEX idx_users_firebase_uid ON users(firebase_uid);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_cart_user_id ON cart(user_id);
CREATE INDEX idx_requests_user_id ON requests(user_id);
CREATE INDEX idx_requests_status ON requests(status);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_status ON products(status);

-----------------------------------------------------

USE iancris_electronics;

UPDATE users 
SET password = '$2b$10$iT76Sjfa4tbM7cD0TkojIOBXr4ICg5377YDmK7ePlg0Fo5g81/NrS'
WHERE email = 'admin@iancris.com';

-----------------------------------------------------

USE iancris_electronics;

ALTER TABLE users 
ADD COLUMN last_login TIMESTAMP NULL AFTER role;