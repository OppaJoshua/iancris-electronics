<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $request_id = intval($input['request_id'] ?? 0);
    $status = sanitizeInput($input['status'] ?? '');
    $admin_notes = sanitizeInput($input['admin_notes'] ?? '');

    if ($request_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
        exit;
    }

    if (empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Status is required']);
        exit;
    }

    $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $query = "UPDATE requests SET status = $1, admin_notes = $2, updated_at = CURRENT_TIMESTAMP 
              WHERE id = $3";
    
    $result = pg_query_params($conn, $query, array($status, $admin_notes, $request_id));

    closeDBConnection($conn);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Request updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update request');
    }

} catch (Exception $e) {
    error_log('Error in update-request: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>