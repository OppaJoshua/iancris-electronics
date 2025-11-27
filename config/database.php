<?php
// Attempt MySQL connection with retries and alternate ports
$host = '127.0.0.1';
$ports_to_try = [3306, 3307, 33060]; // common ports to try
$username = 'root';
$password = '';
$database = 'iancris_electronics';
$connect_success = false;
$last_error = '';

$log_dir = __DIR__ . '/../logs';
if (!file_exists($log_dir)) {
    @mkdir($log_dir, 0777, true);
}
$log_file = $log_dir . '/db-debug.log';

function log_debug($msg) {
    global $log_file;
    @file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

// try connect helper
foreach ($ports_to_try as $port) {
    // mysqli real connect: host, user, pass, db, port
    $conn = @new mysqli($host, $username, $password, $database, $port);
    if ($conn && !$conn->connect_error) {
        log_debug("Connected to MySQL on port $port");
        $connect_success = true;
        break;
    } else {
        $err = $conn ? $conn->connect_error : mysqli_connect_error();
        log_debug("Connection attempt to port $port failed: " . $err);
        $last_error = "Port $port: " . $err;
    }
}

// If connection not successful, and running on Windows, attempt to start mysql service and retry a couple times
if (!$connect_success && PHP_OS_FAMILY === 'Windows') {
    log_debug("Attempting to start MySQL Windows service (sc start mysql / net start mysql).");
    // Try sc start
    @exec('sc start mysql 2>&1', $output1, $rc1);
    log_debug("sc start output: " . implode("\n", $output1) . " rc=$rc1");
    // Try net start as fallback
    @exec('net start mysql 2>&1', $output2, $rc2);
    log_debug("net start output: " . implode("\n", $output2) . " rc=$rc2");
    // wait a moment and retry ports
    sleep(3);
    foreach ($ports_to_try as $port) {
        $conn = @new mysqli($host, $username, $password, $database, $port);
        if ($conn && !$conn->connect_error) {
            log_debug("Connected to MySQL on port $port after attempting to start service.");
            $connect_success = true;
            break;
        } else {
            $err = $conn ? $conn->connect_error : mysqli_connect_error();
            log_debug("Retry connection to port $port failed: " . $err);
            $last_error = "Port $port: " . $err;
        }
    }
}

// If still not connected, write friendly message and stop execution (so other code doesn't error)
if (!$connect_success) {
    $msg = "Database connection failed after trying ports " . implode(',', $ports_to_try) . ". Last error: $last_error. Check XAMPP MySQL service.";
    log_debug($msg);
    // Show minimal friendly HTML (avoid breaking AJAX requests)
    if (php_sapi_name() !== 'cli') {
        echo "<h2>Database unavailable</h2>";
        echo "<p>$msg</p>";
        echo "<p>Check XAMPP control panel, ensure MySQL is running or try starting the 'mysql' Windows service as Administrator.</p>";
    }
    exit; // stop further execution to prevent downstream errors
}

// At this point $conn is a valid mysqli connection. Continue with your existing table creation logic below.

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($conn->query($sql) === FALSE) {
    log_debug("Error creating database: " . $conn->error);
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    password VARCHAR(255) NOT NULL,
    google_id VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Add google_id column if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'google_id'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) AFTER password");
}

// Add phone column if it doesn't exist
$check_phone = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
if ($check_phone->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email");
}

// Add address column if it doesn't exist
$check_address = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
if ($check_address->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN address TEXT AFTER phone");
}

// Add role column if it doesn't exist
$check_role = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check_role->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER google_id");
}

// Create default admin user if not exists
$admin_check = $conn->query("SELECT * FROM users WHERE email = 'admin@iancris.com'");
if ($admin_check->num_rows == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Admin', 'User', 'admin@iancris.com', '$admin_password', 'admin')");
}

// Make joshuacalago649@gmail.com an admin
$josh_check = $conn->query("SELECT * FROM users WHERE email = 'joshuacalago649@gmail.com'");
if ($josh_check->num_rows > 0) {
    $conn->query("UPDATE users SET role = 'admin' WHERE email = 'joshuacalago649@gmail.com'");
}

// Create products table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    images TEXT,
    category VARCHAR(100),
    stock INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Add images column if it doesn't exist
$check_images = $conn->query("SHOW COLUMNS FROM products LIKE 'images'");
if ($check_images->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN images TEXT AFTER image");
}

// Check if orders table exists
$table_check = $conn->query("SHOW TABLES LIKE 'orders'");

if ($table_check->num_rows == 0) {
    // Create new orders table
    $sql = "CREATE TABLE orders (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
} else {
    // Add missing columns to existing orders table
    $columns_to_add = [
        'user_name' => "ALTER TABLE orders ADD COLUMN user_name VARCHAR(255) NOT NULL AFTER user_id",
        'user_phone' => "ALTER TABLE orders ADD COLUMN user_phone VARCHAR(20) NOT NULL AFTER user_name",
        'user_address' => "ALTER TABLE orders ADD COLUMN user_address TEXT AFTER user_phone",
        'total_items' => "ALTER TABLE orders ADD COLUMN total_items INT NOT NULL DEFAULT 0 AFTER user_address",
        'installation_date' => "ALTER TABLE orders ADD COLUMN installation_date DATE NULL AFTER status",
        'installation_time' => "ALTER TABLE orders ADD COLUMN installation_time TIME NULL AFTER installation_date",
        'admin_notes' => "ALTER TABLE orders ADD COLUMN admin_notes TEXT NULL AFTER installation_time"
    ];
    
    foreach ($columns_to_add as $column => $sql) {
        $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
        if ($check->num_rows == 0) {
            $conn->query($sql);
        }
    }
    
    // Update status column if needed
    $conn->query("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'scheduled', 'completed', 'cancelled') DEFAULT 'pending'");
}

// Create order_items table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($sql);

// Create gallery table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    image VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
?>
