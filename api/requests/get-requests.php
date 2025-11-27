<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get filter parameters
    $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

    $query = "SELECT * FROM requests WHERE 1=1";
    $params = array();
    
    if (!empty($status)) {
        $query .= " AND status = $1";
        $params[] = $status;
    }

    $query .= " ORDER BY created_at DESC";

    // Execute query
    if (count($params) > 0) {
        $result = pg_query_params($conn, $query, $params);
    } else {
        $result = pg_query($conn, $query);
    }

    $requests = array();
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            // Decode products JSON
            $row['products'] = json_decode($row['products'], true);
            $requests[] = $row;
        }
    }

    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);

} catch (Exception $e) {
    error_log('Error in get-requests: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>