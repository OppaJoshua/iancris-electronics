<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $firebase_uid = $data['firebase_uid'] ?? '';
    $email = $data['email'] ?? '';
    $first_name = $data['first_name'] ?? '';
    $last_name = $data['last_name'] ?? '';
    $photo_url = $data['photo_url'] ?? '';
    $email_verified = $data['email_verified'] ?? false;
    $phone = $data['phone'] ?? '';
    
    if (empty($firebase_uid) || empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
        exit;
    }
    
    // Validate phone if provided
    if (!empty($phone) && !preg_match('/^[0-9]{12}$/', $phone)) {
        echo json_encode(['success' => false, 'error' => 'Phone number must be exactly 12 digits']);
        exit;
    }
    
    // Check if user exists by Firebase UID or email
    $stmt = $conn->prepare("SELECT id, email_verified FROM users WHERE firebase_uid = ? OR email = ?");
    $stmt->bind_param("ss", $firebase_uid, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists - login
        $user = $result->fetch_assoc();
        
        // Update Firebase UID and email verified status if needed
        $update_stmt = $conn->prepare("UPDATE users SET firebase_uid = ?, email_verified = 1, last_login = NOW() WHERE id = ?");
        $update_stmt->bind_param("si", $firebase_uid, $user['id']);
        $update_stmt->execute();
        
        // Get updated user data
        $user_stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, phone FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user['id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        
        // Set session
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['user_name'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
        $_SESSION['user_email'] = $user_data['email'];
        $_SESSION['user_role'] = $user_data['role'];
        
        echo json_encode(['success' => true, 'message' => 'Login successful']);
        
    } else {
        // New user - register
        $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Random password for Google users
        
        $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, firebase_uid, email_verified, role) VALUES (?, ?, ?, ?, ?, 1, 'user')");
        $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $firebase_uid);
        
        if ($insert_stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Set session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'user';
            
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Registration failed']);
        }
    }
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
