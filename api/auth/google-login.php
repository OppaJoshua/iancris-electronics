<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['idToken']) || !isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$email = $data['email'];
$display_name = $data['displayName'] ?? '';
$photo_url = $data['photoURL'] ?? '';
$google_id = $data['uid'] ?? '';

// Check if user exists and get status
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists
    $user = $result->fetch_assoc();
    
    // Check if user is blocked
    if (isset($user['status']) && $user['status'] === 'blocked') {
        echo json_encode([
            'success' => false,
            'message' => 'Your account has been blocked. Please contact support for assistance.'
        ]);
        exit();
    }
    
    $user_id = $user['id'];
    
    // Update last login and photo
    $update_stmt = $conn->prepare("UPDATE users SET photo_url = ?, last_login = NOW() WHERE id = ?");
    $update_stmt->bind_param("si", $photo_url, $user_id);
    $update_stmt->execute();
} else {
    // Create new user
    $name_parts = explode(' ', $display_name, 2);
    $first_name = $name_parts[0] ?? '';
    $last_name = $name_parts[1] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, google_id, photo_url, role, status) VALUES (?, ?, ?, ?, ?, 'user', 'active')");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $google_id, $photo_url);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error creating user account']);
        exit;
    }
    
    $user_id = $conn->insert_id;
}

// Set session variables - MAKE SURE ROLE IS SET
$_SESSION['user_id'] = $user_id;
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = $display_name;
$_SESSION['user_photo'] = $photo_url;
$_SESSION['user_role'] = $user['role'] ?? 'user'; // CRITICAL: Set the role

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => '/iancris-electronics/index.php'
]);
?>
