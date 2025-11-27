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

try {
    $product_id = intval($_POST['product_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $stock_status = sanitizeInput($_POST['stock_status'] ?? 'In Stock');
    $specifications = sanitizeInput($_POST['specifications'] ?? '');

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Product name is required']);
        exit;
    }

    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get current product info
    $current_query = "SELECT image_url FROM products WHERE id = $1";
    $current_result = pg_query_params($conn, $current_query, array($product_id));
    $current_product = pg_fetch_assoc($current_result);

    $image_url = $current_product['image_url'];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/products/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');

        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }

        $file_name = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            // Delete old image if exists
            if (!empty($current_product['image_url']) && file_exists('../../' . $current_product['image_url'])) {
                unlink('../../' . $current_product['image_url']);
            }
            $image_url = 'uploads/products/' . $file_name;
        }
    }

    $query = "UPDATE products SET name = $1, description = $2, category = $3, image_url = $4, 
              stock_status = $5, specifications = $6, updated_at = CURRENT_TIMESTAMP 
              WHERE id = $7";
    
    $result = pg_query_params($conn, $query, array(
        $name,
        $description,
        $category,
        $image_url,
        $stock_status,
        $specifications,
        $product_id
    ));

    closeDBConnection($conn);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update product');
    }

} catch (Exception $e) {
    error_log('Error in update-product: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>