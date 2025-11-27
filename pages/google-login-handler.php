<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_POST['google_token'])) {
    echo json_encode(['success' => false, 'message' => 'No token provided']);
    exit();
}

$token = $_POST['google_token'];

// Verify the token with Google
$client_id = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com'; // Replace with your Client ID

$ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$google_data = json_decode($response, true);

// Verify the token is valid
if (!isset($google_data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit();
}

$email = $google_data['email'];
$first_name = $google_data['given_name'] ?? '';
$last_name = $google_data['family_name'] ?? '';
$google_id = $google_data['sub'];

// Check if user exists
$stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists, log them in
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
} else {
    // Create new user
    $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Random password
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, google_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $google_id);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $first_name;
        $_SESSION['user_email'] = $email;
        $_SESSION['full_name'] = $first_name . ' ' . $last_name;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account']);
        exit();
    }
}

echo json_encode(['success' => true]);
?>
