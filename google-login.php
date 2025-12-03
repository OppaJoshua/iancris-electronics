<?php
ob_start();
session_start();
ob_end_clean();
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    exit;
}

$email = $data['email'] ?? '';
$name = $data['name'] ?? 'User';

$_SESSION['user_id'] = uniqid('user_');
$_SESSION['user_email'] = $email;
$_SESSION['display_name'] = $name;
$_SESSION['login_method'] = 'google';

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'email' => $email,
        'name' => $name
    ]
]);

exit;
?>
