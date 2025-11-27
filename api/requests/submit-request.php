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
    $user_id = $_SESSION['user_id'];
    $user_name = sanitizeInput($input['user_name'] ?? '');
    $user_phone = sanitizeInput($input['user_phone'] ?? '');
    $message = sanitizeInput($input['message'] ?? '');
    $products = $input['products'] ?? [];

    if (empty($user_name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }

    if (empty($products)) {
        echo json_encode(['success' => false, 'message' => 'No products selected']);
        exit;
    }

    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get user email
    $user_query = "SELECT email FROM users WHERE id = $1";
    $user_result = pg_query_params($conn, $user_query, array($user_id));
    $user = pg_fetch_assoc($user_result);
    $user_email = $user['email'];

    // Prepare products JSON
    $products_json = json_encode($products);
    $total_items = count($products);

    // Insert request
    $query = "INSERT INTO requests (user_id, user_email, user_name, user_phone, products, total_items, message, status) 
              VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id";
    
    $result = pg_query_params($conn, $query, array(
        $user_id,
        $user_email,
        $user_name,
        $user_phone,
        $products_json,
        $total_items,
        $message,
        'pending'
    ));

    if ($result) {
        $request = pg_fetch_assoc($result);
        
        // Clear user's cart
        $clear_cart = "DELETE FROM cart WHERE user_id = $1";
        pg_query_params($conn, $clear_cart, array($user_id));

        closeDBConnection($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Request submitted successfully',
            'request_id' => $request['id']
        ]);
    } else {
        throw new Exception('Failed to submit request');
    }

} catch (Exception $e) {
    error_log('Error in submit-request: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>