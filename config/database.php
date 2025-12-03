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
    // mysqli real connect: host, user, pass, db (NULL initially), port
    $conn = @new mysqli($host, $username, $password, NULL, $port);
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
        $conn = @new mysqli($host, $username, $password, NULL, $port);
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
if (!$conn->select_db($database)) {
    log_debug("Error selecting database: " . $conn->error);
    die("Error selecting database: " . $conn->error);
}

// Verify database is selected
$result = $conn->query("SELECT DATABASE()");
$row = $result->fetch_row();
log_debug("Current database: " . ($row[0] ?? 'NULL'));

// Check InnoDB status
$innodb_check = $conn->query("SHOW VARIABLES LIKE 'innodb_force_recovery'");
if ($innodb_check) {
    $innodb_row = $innodb_check->fetch_assoc();
    log_debug("InnoDB force recovery: " . ($innodb_row['Value'] ?? 'not set'));
}

// Disable foreign key checks for safe operations
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Check if tables exist before creating
$tables_exist = $conn->query("SHOW TABLES LIKE 'users'");

if (!$tables_exist || $tables_exist->num_rows == 0) {
    log_debug("Tables don't exist, creating fresh database...");
    
    // Drop all tables in correct order (child tables first due to foreign keys)
    $tables_to_drop = ['order_items', 'orders', 'gallery', 'products', 'users'];
    foreach ($tables_to_drop as $table) {
        log_debug("Attempting to drop table: $table");
        $drop_result = @$conn->query("DROP TABLE IF EXISTS `$table`");
        if ($drop_result) {
            log_debug("Successfully dropped table: $table");
        } else {
            log_debug("Could not drop table $table: " . $conn->error);
        }
        
        // Try to remove physical files
        $data_dir = "C:\\xampp\\mysql\\data\\$database\\";
        $files_to_remove = [
            $data_dir . $table . ".frm",
            $data_dir . $table . ".ibd",
            $data_dir . $table . ".MYD",
            $data_dir . $table . ".MYI"
        ];
        
        foreach ($files_to_remove as $file) {
            if (file_exists($file)) {
                if (@unlink($file)) {
                    log_debug("Deleted file: $file");
                } else {
                    log_debug("Could not delete file: $file");
                }
            }
        }
    }
    
    log_debug("All tables dropped, creating fresh tables...");
    
    // Create users table fresh
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        password VARCHAR(255) NOT NULL,
        google_id VARCHAR(255),
        role ENUM('user', 'admin') DEFAULT 'user',
        status ENUM('active', 'blocked') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $create_result = $conn->query($sql);
    if ($create_result === FALSE) {
        log_debug("Error creating users table: " . $conn->error);
        die("<h2>Critical Database Error</h2>" .
            "<p>Cannot create users table: " . htmlspecialchars($conn->error) . "</p>" .
            "<p><strong>MANUAL FIX REQUIRED:</strong></p>" .
            "<ol>" .
            "<li>Close this browser tab</li>" .
            "<li>Open XAMPP Control Panel</li>" .
            "<li>Click 'Stop' on MySQL</li>" .
            "<li>Wait 5 seconds</li>" .
            "<li>Open File Explorer</li>" .
            "<li>Navigate to: <code>C:\\xampp\\mysql\\data\\</code></li>" .
            "<li>Delete the entire <code>iancris_electronics</code> folder</li>" .
            "<li>Go back to XAMPP Control Panel</li>" .
            "<li>Click 'Start' on MySQL</li>" .
            "<li>Wait for MySQL to fully start (green highlight)</li>" .
            "<li>Refresh this page in your browser</li>" .
            "</ol>");
    }
    
    log_debug("Users table created successfully");
    
    // Verify table is actually accessible
    $verify = @$conn->query("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Test', 'User', 'test_" . time() . "@test.com', 'test', 'user')");
    if (!$verify) {
        log_debug("Users table not accessible after creation: " . $conn->error);
        die("<h2>Database Error</h2><p>Users table created but not accessible. InnoDB corruption detected.</p>");
    }
    
    $conn->query("DELETE FROM users WHERE email LIKE 'test_%@test.com'");
    log_debug("Users table verified and working");
    
    // Create default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Admin', 'User', 'admin@iancris.com', '$admin_password', 'admin')");
    
    // Set joshuacalago649@gmail.com as admin
    $conn->query("UPDATE users SET role = 'admin' WHERE email = 'joshuacalago649@gmail.com'");
    
    // Create products table
    $sql = "CREATE TABLE products (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql);
    
    // Create orders table
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
    
    // Create order_items table
    $sql = "CREATE TABLE order_items (
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
    
    // Create gallery table
    $sql = "CREATE TABLE gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        location VARCHAR(255),
        latitude DECIMAL(10, 8) NULL,
        longitude DECIMAL(11, 8) NULL,
        image VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql);
    
    log_debug("All tables created successfully");
} else {
    log_debug("Tables already exist, skipping creation");
    
    // Just ensure status column exists in users table
    $check_status = @$conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    if (!$check_status || $check_status->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'blocked') DEFAULT 'active' AFTER role");
        log_debug("Added status column to users table");
    }
    
    // Add latitude and longitude columns to gallery table if they don't exist
    $check_lat = @$conn->query("SHOW COLUMNS FROM gallery LIKE 'latitude'");
    if (!$check_lat || $check_lat->num_rows == 0) {
        $conn->query("ALTER TABLE gallery ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER location");
        log_debug("Added latitude column to gallery table");
    }
    
    $check_lng = @$conn->query("SHOW COLUMNS FROM gallery LIKE 'longitude'");
    if (!$check_lng || $check_lng->num_rows == 0) {
        $conn->query("ALTER TABLE gallery ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude");
        log_debug("Added longitude column to gallery table");
    }
    
    // Make sure joshuacalago649@gmail.com is admin if exists
    $result = $conn->query("UPDATE users SET role = 'admin' WHERE email = 'joshuacalago649@gmail.com'");
    if ($result && $conn->affected_rows > 0) {
        log_debug("Updated joshuacalago649@gmail.com to admin role");
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
?>
