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
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $stock_status = sanitizeInput($_POST['stock_status'] ?? 'In Stock');
    $specifications = sanitizeInput($_POST['specifications'] ?? '');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Product name is required']);
        exit;
    }

    $image_url = '';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/products/';
        
        // Create directory if it doesn't exist
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
            $image_url = 'uploads/products/' . $file_name;
        }
    }

    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $query = "INSERT INTO products (name, description, category, image_url, stock_status, specifications) 
              VALUES ($1, $2, $3, $4, $5, $6) RETURNING id";
    
    $result = pg_query_params($conn, $query, array(
        $name,
        $description,
        $category,
        $image_url,
        $stock_status,
        $specifications
    ));

    if ($result) {
        $product = pg_fetch_assoc($result);
        closeDBConnection($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $product['id']
        ]);
    } else {
        throw new Exception('Failed to create product');
    }

} catch (Exception $e) {
    error_log('Error in create-product: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>