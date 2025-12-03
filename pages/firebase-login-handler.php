<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$email = $input['email'];
$uid = $input['uid'];
$name = $input['name'] ?? '';
$photoURL = $input['photoURL'] ?? ''; // Get Google profile picture

// Split name into first and last
$name_parts = explode(' ', trim($name), 2);
$first_name = $name_parts[0] ?? 'User';
$last_name = $name_parts[1] ?? '';

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists - log them in
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['google_picture'] = $photoURL; // Store Google profile picture
        
        // Check if admin
        $redirect = 'dashboard.php';
        if ($user['role'] === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['first_name'];
            $redirect = '../admin/index.php';
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'redirect' => $redirect,
            'user' => [
                'id' => $user['id'],
                'name' => $user['first_name']
            ]
        ]);
    } else {
        // Create new user (always as regular user, not admin)
        $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        
        // Check if google_id column exists
        $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'google_id'");
        
        if ($column_check->num_rows > 0) {
            // Insert with google_id
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, google_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $uid);
        } else {
            // Insert without google_id
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $password_hash);
        }
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $first_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['full_name'] = $name;
            $_SESSION['google_picture'] = $photoURL; // Store Google profile picture
            
            echo json_encode([
                'success' => true, 
                'message' => 'Account created successfully',
                'redirect' => 'dashboard.php',
                'user' => [
                    'id' => $user_id,
                    'name' => $first_name
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create account: ' . $conn->error]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
