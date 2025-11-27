<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get query parameters
    $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
    $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

    // Build query
    $query = "SELECT * FROM products WHERE 1=1";
    $params = array();
    $param_count = 1;

    if (!empty($category)) {
        $query .= " AND category = $" . $param_count;
        $params[] = $category;
        $param_count++;
    }

    if (!empty($search)) {
        $query .= " AND (name ILIKE $" . $param_count . " OR description ILIKE $" . $param_count . ")";
        $params[] = "%$search%";
        $param_count++;
    }

    $query .= " ORDER BY created_at DESC";

    // Execute query
    if (count($params) > 0) {
        $result = pg_query_params($conn, $query, $params);
    } else {
        $result = pg_query($conn, $query);
    }

    $products = array();
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (Exception $e) {
    error_log('Error in get-products: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>