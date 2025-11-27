<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) {
        echo json_encode(['success' => true, 'items' => []]);
        exit;
    }

    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $user_id = $_SESSION['user_id'];

    $query = "SELECT c.*, p.name, p.description, p.image_url, p.category, p.stock_status 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $1 
              ORDER BY c.added_at DESC";
    
    $result = pg_query_params($conn, $query, array($user_id));

    $items = array();
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $items[] = $row;
        }
    }

    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);

} catch (Exception $e) {
    error_log('Error in get-cart: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>