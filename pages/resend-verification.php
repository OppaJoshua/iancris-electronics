<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['pending_firebase_registration'])) {
        echo json_encode(['success' => false, 'error' => 'Session expired']);
        exit;
    }
    
    $reg_data = $_SESSION['pending_firebase_registration'];
    $user_id = $reg_data['user_id'];
    
    // Generate new verification code
    $verification_code = sprintf("%06d", mt_rand(100000, 999999));
    $code_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    // Update database
    $stmt = $conn->prepare("UPDATE users SET verification_code = ?, code_expiry = ? WHERE id = ?");
    $stmt->bind_param("ssi", $verification_code, $code_expiry, $user_id);
    $stmt->execute();
    
    // Send email
    $to = $reg_data['email'];
    $subject = "New Verification Code - Iancris Electronics";
    $message = "Hello " . $reg_data['first_name'] . ",\n\n";
    $message .= "Your new verification code is: " . $verification_code . "\n\n";
    $message .= "This code will expire in 30 minutes.\n\n";
    $message .= "Thank you,\nIancris Electronics Team";
    $headers = "From: noreply@iancris-electronics.com\r\n";
    
    $mail_sent = @mail($to, $subject, $message, $headers);
    
    echo json_encode(['success' => true, 'mail_sent' => $mail_sent]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
