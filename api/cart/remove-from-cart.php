<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $product_id = intval($input['product_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $query = "DELETE FROM cart WHERE user_id = $1 AND product_id = $2";
    $result = pg_query_params($conn, $query, array($user_id, $product_id));

    closeDBConnection($conn);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
    } else {
        throw new Exception('Failed to remove item from cart');
    }

} catch (Exception $e) {
    error_log('Error in remove-from-cart: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>