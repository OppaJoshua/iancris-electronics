<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

$message = '';
$message_type = '';
$error = '';

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $stock = intval($_POST['stock']);
    
    // Handle multiple image uploads (up to 4 images)
    $image_paths = [];
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES["image$i"]) && $_FILES["image$i"]['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES["image$i"]['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $newname = 'product_' . time() . '_' . uniqid() . '.' . $filetype;
                $upload_path = '../uploads/products/';
                
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                if (move_uploaded_file($_FILES["image$i"]['tmp_name'], $upload_path . $newname)) {
                    $image_paths[] = 'uploads/products/' . $newname;
                }
            }
        }
    }
    
    // Store images as JSON array
    $images_json = json_encode($image_paths);
    $main_image = !empty($image_paths) ? $image_paths[0] : '';
    
    // Set price to 0 since it's non-monetary
    $price = 0;
    
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, images, category, stock, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssdsssi", $name, $description, $price, $main_image, $images_json, $category, $stock);
    
    if ($stmt->execute()) {
        $message = "Product added successfully!";
        $message_type = 'success';
    } else {
        $error = "Error adding product.";
    }
}

// Handle product edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $stock = intval($_POST['stock']);
    $status = $_POST['status'];
    
    // Get existing images from database first
    $existing_stmt = $conn->prepare("SELECT images FROM products WHERE id = ?");
    $existing_stmt->bind_param("i", $id);
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();
    $existing_product = $existing_result->fetch_assoc();
    $image_paths = json_decode($existing_product['images'], true) ?: [];
    
    // Only process new images if uploaded
    $has_new_images = false;
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES["image$i"]) && $_FILES["image$i"]['error'] == 0) {
            $has_new_images = true;
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES["image$i"]['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $newname = 'product_' . time() . '_' . uniqid() . '.' . $filetype;
                $upload_path = '../uploads/products/';
                
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                if (move_uploaded_file($_FILES["image$i"]['tmp_name'], $upload_path . $newname)) {
                    // Delete old image if exists
                    if (isset($image_paths[$i - 1]) && file_exists('../' . $image_paths[$i - 1])) {
                        unlink('../' . $image_paths[$i - 1]);
                    }
                    
                    if ($i - 1 < count($image_paths)) {
                        $image_paths[$i - 1] = 'uploads/products/' . $newname;
                    } else {
                        $image_paths[] = 'uploads/products/' . $newname;
                    }
                }
            }
        }
    }
    
    // Keep existing images if no new ones uploaded
    $images_json = json_encode($image_paths);
    $main_image = !empty($image_paths) ? $image_paths[0] : '';
    $price = 0;
    
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, images = ?, category = ?, stock = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssdsssssi", $name, $description, $price, $main_image, $images_json, $category, $stock, $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product updated successfully!";
        header("Location: products.php");
        exit();
    } else {
        $error = "Error updating product.";
    }
}

// Check for session messages
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $message_type = 'success';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Get all products
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);

// Store products in array for proper JSON encoding
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Get categories for filter
$categories_sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categories_result = $conn->query($categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar-link.active {
            background-color: #EFF6FF;
            color: #2563EB;
        }
        .sidebar-link:hover {
            background-color: #F3F4F6;
        }
        
        /* Toast notification styles */
        .toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 9999;
            max-width: 400px;
        }
        
        .toast {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideIn 0.3s ease-out;
            border-left: 4px solid;
        }
        
        .toast.success {
            border-left-color: #10B981;
        }
        
        .toast.error {
            border-left-color: #EF4444;
        }
        
        .toast.hiding {
            animation: slideOut 0.3s ease-out forwards;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .toast-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .toast.success .toast-icon {
            background-color: #D1FAE5;
            color: #10B981;
        }
        
        .toast.error .toast-icon {
            background-color: #FEE2E2;
            color: #EF4444;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }
        
        .toast-message {
            font-size: 0.875rem;
            color: #6B7280;
        }
        
        .toast-close {
            flex-shrink: 0;
            cursor: pointer;
            color: #9CA3AF;
            transition: color 0.2s;
        }
        
        .toast-close:hover {
            color: #4B5563;
        }
        
        /* Modal animations */
        .modal {
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .modal > div {
            animation: slideUp 0.4s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal.closing {
            animation: fadeOut 0.3s ease-out forwards;
        }
        
        @keyframes fadeOut {
            to {
                opacity: 0;
            }
        }
        
        .modal.closing > div {
            animation: slideDown 0.3s ease-out forwards;
        }
        
        @keyframes slideDown {
            to {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
        }
        
        /* Product card hover effects */
        .product-card {
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            z-index: 2;
        }
        
        /* Filter section needs higher z-index */
        .filter-section {
            position: relative;
            z-index: 10;
        }
        
        /* Ensure dropdowns appear above everything */
        select {
            position: relative;
            z-index: 50;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar (same as users.php) -->
        <aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">IanCris</h1>
                <p class="text-gray-500 text-sm mt-1">Admin Panel</p>
            </div>
            
            <nav class="mt-4 px-3">
                <a href="index.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="users.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium">Users</span>
                </a>
                
                <a href="products.php" class="sidebar-link active flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="font-medium">Products</span>
                </a>
                
                <a href="orders.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span class="font-medium">Orders</span>
                </a>
                
                <a href="gallery.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">Gallery</span>
                </a>
                
                <div class="border-t border-gray-200 mt-6 pt-6">
                    <a href="../index.php" target="_blank" rel="noopener noreferrer" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span class="font-medium">View Site</span>
                    </a>
                    
                    <a href="logout.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-lg transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="font-medium">Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-8 py-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Products</h2>
                        <p class="text-gray-600 text-sm mt-1">Manage product inventory</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                            <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <!-- Search & Filter Bar -->
                    <div class="p-6 border-b border-gray-200 bg-gray-50 filter-section">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
                                <input type="text" id="searchInput" placeholder="Search by name..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Category</label>
                                <select id="categoryFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">All Categories</option>
                                    <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($cat['category']); ?>"><?php echo htmlspecialchars($cat['category']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                                <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button onclick="resetFilters()" class="w-full bg-gray-600 text-white py-2 rounded-lg hover:bg-gray-700 transition text-sm font-semibold">
                                    Reset
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Showing <span id="productCount"><?php echo $result->num_rows; ?></span> products</p>
                            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                + Add Product
                            </button>
                        </div>
                    </div>
                    
                    <?php if (count($products) > 0): ?>
                        <div id="productsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                            <?php foreach ($products as $product): ?>
                            <div class="product-card border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition"
                                 data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>"
                                 data-category="<?php echo htmlspecialchars(strtolower($product['category'] ?? '')); ?>"
                                 data-status="<?php echo htmlspecialchars($product['status']); ?>"
                                 data-id="<?php echo htmlspecialchars($product['id']); ?>"
                                 data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                 data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
                                 data-product-category="<?php echo htmlspecialchars($product['category'] ?? ''); ?>"
                                 data-product-stock="<?php echo htmlspecialchars($product['stock']); ?>"
                                 data-product-status="<?php echo htmlspecialchars($product['status']); ?>"
                                 data-product-images="<?php echo htmlspecialchars($product['images'] ?? '[]'); ?>">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400">No Image</span>
                                    </div>
                                <?php endif; ?>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="text-xs text-gray-500 mb-2"><?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?></p>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-gray-700">Stock: <?php echo $product['stock']; ?></span>
                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $product['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </div>
                                    <!-- Only Edit button - Status changes handled in edit modal -->
                                    <button onclick='openEditModalFromCard(this)' class="w-full text-center bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 transition font-medium flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit Product
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- No Results -->
                        <div id="noResults" class="hidden p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-600">No products match your filters</p>
                        </div>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="text-gray-600 mb-4">No products yet. Add your first product!</p>
                            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                + Add Product
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 modal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-white to-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Add New Product</h3>
                    </div>
                    <button onclick="closeModal('addModal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
                <form method="POST" enctype="multipart/form-data" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <input type="text" name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition" placeholder="e.g., CCTV, IT Equipment">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stock *</label>
                            <input type="number" name="stock" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea name="description" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition" placeholder="Describe the product..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Images (Up to 4)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 1 *</label>
                                    <input type="file" name="image1" accept="image/*" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 2</label>
                                    <input type="file" name="image2" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 3</label>
                                    <input type="file" name="image3" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 4</label>
                                    <input type="file" name="image4" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Supported: JPG, JPEG, PNG, GIF</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                        <button type="submit" name="add_product" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-md hover:shadow-xl">
                            Add Product
                        </button>
                        <button type="button" onclick="closeModal('addModal')" class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition font-semibold shadow-sm hover:shadow-lg">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 modal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-white to-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Product</h3>
                    </div>
                    <button onclick="closeModal('editModal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
                <form method="POST" enctype="multipart/form-data" class="p-6">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <input type="hidden" name="existing_images" id="edit_existing_images">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <input type="text" name="category" id="edit_category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stock *</label>
                                <input type="number" name="stock" id="edit_stock" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select name="status" id="edit_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea name="description" id="edit_description" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Update Images (Optional)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 1</label>
                                    <input type="file" name="image1" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 2</label>
                                    <input type="file" name="image2" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 3</label>
                                    <input type="file" name="image3" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Image 4</label>
                                    <input type="file" name="image4" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm shadow-sm hover:shadow-md transition">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Leave empty to keep existing images</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                        <button type="submit" name="edit_product" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-md hover:shadow-xl">
                            Update Product
                        </button>
                        <button type="button" onclick="closeModal('editModal')" class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition font-semibold shadow-sm hover:shadow-lg">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const productCards = document.querySelectorAll('.product-card');

        // Toast notification system
        function showToast(message, type = 'success', title = null) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const defaultTitle = type === 'success' ? 'Success!' : 'Error';
            const toastTitle = title || defaultTitle;
            
            toast.innerHTML = `
                <div class="toast-icon">
                    ${type === 'success' ? 
                        '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' :
                        '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <div class="toast-content">
                    <div class="toast-title">${toastTitle}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="closeToast(this)">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                closeToast(toast.querySelector('.toast-close'));
            }, 5000);
        }

        function closeToast(button) {
            const toast = button.closest('.toast');
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }

        // Show toast on page load if there's a message
        <?php if ($message): ?>
            showToast('<?php echo addslashes($message); ?>', 'success');
        <?php endif; ?>
        
        <?php if ($error): ?>
            showToast('<?php echo addslashes($error); ?>', 'error');
        <?php endif; ?>

        // Filter functionality
        [searchInput, categoryFilter, statusFilter].forEach(el => {
            el.addEventListener('change', filterProducts);
            el.addEventListener('keyup', filterProducts);
        });

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            let visibleCount = 0;

            productCards.forEach(card => {
                const name = card.dataset.name;
                const category = card.dataset.category;
                const status = card.dataset.status;

                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = !selectedCategory || category === selectedCategory;
                const matchesStatus = !selectedStatus || status === selectedStatus;

                if (matchesSearch && matchesCategory && matchesStatus) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('productCount').textContent = visibleCount;
            document.getElementById('noResults').classList.toggle('hidden', visibleCount > 0);
            document.getElementById('productsGrid').classList.toggle('hidden', visibleCount === 0);
        }

        function resetFilters() {
            searchInput.value = '';
            categoryFilter.value = '';
            statusFilter.value = '';
            filterProducts();
        }

        function openEditModalFromCard(button) {
            const card = button.closest('.product-card');
            
            // Get product data from data attributes instead of JSON parsing
            const product = {
                id: card.dataset.id,
                name: card.dataset.productName,
                description: card.dataset.productDescription,
                category: card.dataset.productCategory,
                stock: card.dataset.productStock,
                status: card.dataset.productStatus,
                images: card.dataset.productImages
            };
            
            openEditModal(product);
        }

        function openEditModal(product) {
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_category').value = product.category || '';
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_status').value = product.status;
            document.getElementById('edit_description').value = product.description;
            // Preserve the existing images JSON
            document.getElementById('edit_existing_images').value = product.images || '[]';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('closing');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('closing');
            }, 300);
        }

        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal(modal.id);
                }
            });
        });
    </script>
</body>
</html>