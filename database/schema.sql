-- IanCris Electronics Database Schema
-- PostgreSQL Database

-- Drop tables if exist
DROP TABLE IF EXISTS requests CASCADE;
DROP TABLE IF EXISTS cart CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS admins CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    firebase_uid VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    display_name VARCHAR(255),
    photo_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admins Table
CREATE TABLE admins (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    image_url TEXT,
    stock_status VARCHAR(50) DEFAULT 'In Stock',
    specifications TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    quantity INTEGER DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id)
);

-- Requests Table
CREATE TABLE requests (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    user_email VARCHAR(255) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_phone VARCHAR(50),
    products TEXT NOT NULL,
    total_items INTEGER NOT NULL,
    message TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    admin_notes TEXT,
    appointment_date TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admins (email, password_hash, full_name) 
VALUES ('admin@iancris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User');

-- Insert sample products
INSERT INTO products (name, description, category, image_url, stock_status, specifications) VALUES
('CCTV Camera Set - 4 Channel', 'Complete 4-channel CCTV system with DVR and cameras', 'CCTV Systems', 'uploads/products/cctv-4ch.jpg', 'In Stock', '1080p resolution, Night vision, Weatherproof'),
('IP Camera', 'Wireless IP camera with mobile app support', 'CCTV Systems', 'uploads/products/ip-camera.jpg', 'In Stock', '2MP, WiFi enabled, Motion detection'),
('Network Switch 8-Port', '8-port gigabit ethernet switch', 'Networking', 'uploads/products/switch-8port.jpg', 'In Stock', 'Gigabit speed, Plug and play'),
('Cat6 Cable - 305m', 'Cat6 ethernet cable roll', 'Cables', 'uploads/products/cat6-cable.jpg', 'In Stock', '305 meters, 23AWG, Blue color'),
('HDMI Cable 5m', 'High-speed HDMI cable', 'Cables', 'uploads/products/hdmi-cable.jpg', 'In Stock', '5 meters, 4K support, Gold plated'),
('Router Dual Band', 'Dual band wireless router', 'Networking', 'uploads/products/router.jpg', 'In Stock', 'AC1200, 4 LAN ports, 2.4GHz & 5GHz'),
('DVR 8 Channel', '8-channel digital video recorder', 'CCTV Systems', 'uploads/products/dvr-8ch.jpg', 'In Stock', '1080p recording, H.264, 1TB HDD'),
('Power Supply 12V', '12V DC power supply for cameras', 'Accessories', 'uploads/products/power-supply.jpg', 'In Stock', '12V 2A, Universal input'),
('BNC Connector Pack', 'Pack of 50 BNC connectors', 'Accessories', 'uploads/products/bnc-connector.jpg', 'In Stock', '50 pieces, Compression type'),
('Cable Tester', 'Network cable tester tool', 'Tools', 'uploads/products/cable-tester.jpg', 'In Stock', 'Tests RJ45 & RJ11, LED indicators');

-- Create indexes for better performance
CREATE INDEX idx_users_firebase_uid ON users(firebase_uid);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_cart_user_id ON cart(user_id);
CREATE INDEX idx_requests_user_id ON requests(user_id);
CREATE INDEX idx_requests_status ON requests(status);
CREATE INDEX idx_products_category ON products(category);