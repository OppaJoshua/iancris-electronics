<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $code = $data['code'] ?? '';
    
    if (!isset($_SESSION['pending_firebase_registration'])) {
        echo json_encode(['success' => false, 'error' => 'Session expired']);
        exit;
    }
    
    $user_id = $_SESSION['pending_firebase_registration']['user_id'];
    
    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, code_expiry FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    // Check if code is expired
    if (strtotime($user['code_expiry']) < time()) {
        echo json_encode(['success' => false, 'error' => 'Verification code expired. Please request a new one.']);
        exit;
    }
    
    // Verify code
    if ($user['verification_code'] === $code) {
        // Update user as verified
        $update_stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_code = NULL, code_expiry = NULL WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        
        if ($update_stmt->execute()) {
            unset($_SESSION['pending_firebase_registration']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid verification code']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
