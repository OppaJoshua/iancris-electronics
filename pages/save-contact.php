<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$phone = $data['phone'] ?? '';
$address = $data['address'] ?? '';
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;
$notes = $data['notes'] ?? '';

$user_id = $_SESSION['user_id'];

// Check if columns exist, if not add them
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8)");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8)");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS installation_notes TEXT");

$stmt = $conn->prepare("UPDATE users SET phone = ?, address = ?, latitude = ?, longitude = ?, installation_notes = ? WHERE id = ?");
$stmt->bind_param("ssddsi", $phone, $address, $latitude, $longitude, $notes, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
