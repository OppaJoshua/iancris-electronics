<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT latitude, longitude, address FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode([
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'address' => $data['address']
        ]);
    } else {
        echo json_encode([
            'latitude' => null,
            'longitude' => null,
            'address' => null
        ]);
    }
} else {
    echo json_encode([
        'error' => 'Invalid user ID',
        'latitude' => null,
        'longitude' => null
    ]);
}
?>
