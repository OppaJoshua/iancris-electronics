<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-errors.log');

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Log the start
    error_log("place-order.php: Started");
    
    if (!isset($_SESSION['user_id'])) {
        error_log("place-order.php: User not logged in");
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    error_log("place-order.php: Input received: " . json_encode($input));
    
    if (!$input) {
        error_log("place-order.php: Invalid JSON input");
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit();
    }
    
    $cart = $input['cart'] ?? [];

    if (empty($cart)) {
        error_log("place-order.php: Cart is empty");
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    error_log("place-order.php: User ID: $user_id");

    // Get user details
    $stmt = $conn->prepare("SELECT first_name, last_name, phone, address FROM users WHERE id = ?");
    if (!$stmt) {
        error_log("place-order.php: Prepare failed: " . $conn->error);
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("place-order.php: User not found");
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $user = $result->fetch_assoc();
    error_log("place-order.php: User data: " . json_encode($user));

    if (empty($user['phone'])) {
        error_log("place-order.php: Phone number missing");
        echo json_encode(['success' => false, 'message' => 'Phone number required']);
        exit();
    }

    $user_name = trim($user['first_name'] . ' ' . $user['last_name']);
    $user_phone = trim($user['phone']);
    $user_address = trim($user['address'] ?? '');
    $total_items = array_sum(array_column($cart, 'quantity'));
    
    error_log("place-order.php: Creating order - Name: $user_name, Phone: $user_phone, Items: $total_items");

    // Create order - excluding total_amount column
    $stmt = $conn->prepare("INSERT INTO orders (user_id, user_name, user_phone, user_address, total_items, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    if (!$stmt) {
        error_log("place-order.php: Prepare order insert failed: " . $conn->error);
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $stmt->bind_param("isssi", $user_id, $user_name, $user_phone, $user_address, $total_items);

    if (!$stmt->execute()) {
        error_log("place-order.php: Order insert failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $stmt->error]);
        exit();
    }

    // Get the last inserted ID
    $order_id = $conn->insert_id;

    error_log("place-order.php: Order created with ID: $order_id");

    // Insert order items
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity) VALUES (?, ?, ?, ?)");
    if (!$item_stmt) {
        error_log("place-order.php: Prepare order items failed: " . $conn->error);
        throw new Exception("Database prepare error: " . $conn->error);
    }

    foreach ($cart as $item) {
        $product_id = intval($item['id']);
        $product_name = trim($item['name']);
        $quantity = intval($item['quantity']);
        
        error_log("place-order.php: Adding item - ID: $product_id, Name: $product_name, Qty: $quantity");
        
        $item_stmt->bind_param("iisi", $order_id, $product_id, $product_name, $quantity);
        
        if (!$item_stmt->execute()) {
            error_log("place-order.php: Item insert failed: " . $item_stmt->error);
            echo json_encode(['success' => false, 'message' => 'Failed to add item: ' . $item_stmt->error]);
            exit();
        }
    }

    error_log("place-order.php: Order completed successfully");
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    error_log("place-order.php: Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
