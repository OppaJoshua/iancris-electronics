<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['user_id']) && isset($data['firebase_uid'])) {
        $user_id = $data['user_id'];
        $firebase_uid = $data['firebase_uid'];
        
        $stmt = $conn->prepare("UPDATE users SET email_verified = 1, firebase_uid = ? WHERE id = ?");
        $stmt->bind_param("si", $firebase_uid, $user_id);
        
        if ($stmt->execute()) {
            // Clear pending registration
            unset($_SESSION['pending_firebase_registration']);
            
            echo json_encode(['success' => true, 'message' => 'Email verified']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
