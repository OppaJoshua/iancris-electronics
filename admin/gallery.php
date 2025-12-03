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

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_gallery'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
    
    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $newname = 'gallery_' . time() . '_' . uniqid() . '.' . $filetype;
            $upload_path = '../uploads/gallery/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $newname)) {
                $image_path = 'uploads/gallery/' . $newname;
                
                $stmt = $conn->prepare("INSERT INTO gallery (title, description, location, latitude, longitude, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssdds", $title, $description, $location, $latitude, $longitude, $image_path);
                
                if ($stmt->execute()) {
                    $message = "Gallery item added successfully!";
                    $message_type = 'success';
                } else {
                    $error = "Error adding gallery item.";
                }
            } else {
                $error = "Error uploading image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG & GIF allowed.";
        }
    } else {
        $error = "Please select an image.";
    }
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_gallery'])) {
    $id = intval($_POST['edit_id']);
    $title = trim($_POST['edit_title']);
    $description = trim($_POST['edit_description']);
    $location = trim($_POST['edit_location']);
    $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
    
    // Check if new image is uploaded
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['edit_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Delete old image
            $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $old_item = $result->fetch_assoc();
                $old_image_path = '../' . $old_item['image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            
            // Upload new image
            $newname = 'gallery_' . time() . '_' . uniqid() . '.' . $filetype;
            $upload_path = '../uploads/gallery/';
            
            if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $upload_path . $newname)) {
                $image_path = 'uploads/gallery/' . $newname;
                
                $stmt = $conn->prepare("UPDATE gallery SET title = ?, description = ?, location = ?, latitude = ?, longitude = ?, image = ? WHERE id = ?");
                $stmt->bind_param("sssddsi", $title, $description, $location, $latitude, $longitude, $image_path, $id);
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG & GIF allowed.";
        }
    } else {
        // Update without changing image
        $stmt = $conn->prepare("UPDATE gallery SET title = ?, description = ?, location = ?, latitude = ?, longitude = ? WHERE id = ?");
        $stmt->bind_param("sssddi", $title, $description, $location, $latitude, $longitude, $id);
    }
    
    if (isset($stmt) && $stmt->execute()) {
        $message = "Gallery item updated successfully!";
        $message_type = 'success';
    } else {
        $error = "Error updating gallery item.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get image path to delete file
    $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $image_path = '../' . $item['image'];
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            // Delete image file
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            $message = "Gallery item deleted successfully!";
            $message_type = 'success';
        }
    }
}

// Get all gallery items
$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8"></script>
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
        
        /* Gallery card hover effects */
        .gallery-card {
            transition: all 0.3s ease;
        }
        
        .gallery-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        
        .gallery-card img {
            transition: transform 0.3s ease;
        }
        
        .gallery-card:hover img {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
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
                
                <a href="products.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
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
                
                <a href="gallery.php" class="sidebar-link active flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
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
                        <h2 class="text-2xl font-bold text-gray-900">Gallery</h2>
                        <p class="text-gray-600 text-sm mt-1">Manage project showcase gallery</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                            + Add New
                        </button>
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                            <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8">
                <?php if ($result->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($item = $result->fetch_assoc()): ?>
                        <div class="gallery-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="overflow-hidden h-56">
                                <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1 998 1 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <?php echo htmlspecialchars($item['location']); ?>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                    <span><?php echo date('M d, Y', strtotime($item['created_at'])); ?></span>
                                    <span class="px-2 py-1 rounded-full <?php echo $item['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="flex-1 bg-blue-100 text-blue-700 py-2 rounded-lg hover:bg-blue-200 transition text-sm font-medium">
                                        Edit
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title']); ?>')" class="flex-1 bg-red-100 text-red-700 py-2 rounded-lg hover:bg-red-200 transition text-sm font-medium">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-600 mb-4">No gallery items yet</p>
                        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            Add First Item
                        </button>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Add Modal -->
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
                        <h3 class="text-xl font-bold text-gray-900">Add New Gallery Item</h3>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Title *</label>
                            <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" name="location" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition" placeholder="e.g., Manila, Philippines">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea name="description" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition" placeholder="Describe the installation project..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Image *</label>
                            <input type="file" name="image" accept="image/*" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                            <p class="text-xs text-gray-500 mt-1">Supported: JPG, JPEG, PNG, GIF</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pin Location on Map (Optional)</label>
                            <div id="addMap" class="w-full h-64 rounded-lg border border-gray-300 mb-2"></div>
                            <p class="text-xs text-gray-500">Click on map or drag marker to set location</p>
                            <input type="hidden" name="latitude" id="addLatitudeInput">
                            <input type="hidden" name="longitude" id="addLongitudeInput">
                            <p id="addCoordinates" class="text-xs text-blue-600 mt-1"></p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                        <button type="submit" name="add_gallery" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-md hover:shadow-xl">
                            Add to Gallery
                        </button>
                        <button type="button" onclick="closeModal('addModal')" class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition font-semibold shadow-sm hover:shadow-lg">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
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
                        <h3 class="text-xl font-bold text-gray-900">Edit Gallery Item</h3>
                    </div>
                    <button onclick="closeModal('editModal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
                <form method="POST" enctype="multipart/form-data" class="p-6" id="editForm">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Title *</label>
                            <input type="text" name="edit_title" id="edit_title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" name="edit_location" id="edit_location" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea name="edit_description" id="edit_description" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Change Image (optional)</label>
                            <input type="file" name="edit_image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pin Location on Map (Optional)</label>
                            <div id="editMap" class="w-full h-64 rounded-lg border border-gray-300 mb-2"></div>
                            <p class="text-xs text-gray-500">Click on map or drag marker to set location</p>
                            <input type="hidden" name="latitude" id="editLatitudeInput">
                            <input type="hidden" name="longitude" id="editLongitudeInput">
                            <p id="editCoordinates" class="text-xs text-blue-600 mt-1"></p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                        <button type="submit" name="edit_gallery" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-md hover:shadow-xl">
                            Update Gallery Item
                        </button>
                        <button type="button" onclick="closeModal('editModal')" class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition font-semibold shadow-sm hover:shadow-lg">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 modal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Delete Gallery Item</h3>
                <p class="text-gray-600 mb-1">Are you sure you want to delete</p>
                <p class="font-semibold text-gray-900 mb-6" id="deleteItemName"></p>
                <p class="text-sm text-red-600 mb-6">This action cannot be undone.</p>
                
                <div class="flex gap-3">
                    <button onclick="closeModal('deleteModal')" class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition font-semibold shadow-sm hover:shadow-lg">
                        Cancel
                    </button>
                    <button onclick="executeDelete()" class="flex-1 bg-gradient-to-r from-red-600 to-red-700 text-white py-3 rounded-xl hover:from-red-700 hover:to-red-800 transition font-semibold shadow-md hover:shadow-xl">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let deleteId = null;
        let map;
        let marker;
        let currentModal = null;

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

        function openEditModal(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_title').value = item.title;
            document.getElementById('edit_location').value = item.location;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('editModal').classList.remove('hidden');
            currentModal = 'edit';
            
            // Initialize map after modal is visible
            setTimeout(() => {
                initMap();
                
                // Set marker position if coordinates exist
                if (item.latitude && item.longitude) {
                    const position = {
                        lat: parseFloat(item.latitude),
                        lng: parseFloat(item.longitude)
                    };
                    map.setCenter(position);
                    marker.setPosition(position);
                    updateCoordinates(position.lat, position.lng);
                }
            }, 300);
        }

        function confirmDelete(id, name) {
            deleteId = id;
            document.getElementById('deleteItemName').textContent = `"${name}"?`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function executeDelete() {
            if (deleteId) {
                window.location.href = `?delete=${deleteId}`;
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('closing');
            
            // Reset map when closing
            if (modalId === 'addModal' || modalId === 'editModal') {
                map = null;
                marker = null;
                currentModal = null;
            }
            
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

        function initMap() {
            // Reset if already initialized
            if (map) {
                return;
            }
            
            // Default center: Cebu City, Philippines
            const defaultLocation = { lat: 10.3157, lng: 123.8854 };
            
            // Get the correct map div based on which modal is open
            const mapDiv = document.getElementById(currentModal === 'edit' ? 'editMap' : 'addMap');
            
            if (!mapDiv) {
                console.error('Map div not found for modal:', currentModal);
                return;
            }
            
            map = new google.maps.Map(mapDiv, {
                center: defaultLocation,
                zoom: 13,
                mapTypeControl: true,
                streetViewControl: false,
                fullscreenControl: true
            });
            
            marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true,
                title: "Gallery Location",
                animation: google.maps.Animation.DROP
            });
            
            // Try to get user's current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(userLocation);
                    marker.setPosition(userLocation);
                    updateCoordinates(userLocation.lat, userLocation.lng);
                }, (error) => {
                    console.log('Geolocation error:', error);
                });
            }
            
            // Update coordinates when marker is dragged
            marker.addListener('dragend', function() {
                const position = marker.getPosition();
                updateCoordinates(position.lat(), position.lng());
            });
            
            // Update coordinates when map is clicked
            map.addListener('click', function(event) {
                marker.setPosition(event.latLng);
                updateCoordinates(event.latLng.lat(), event.latLng.lng());
            });
        }

        function updateCoordinates(lat, lng) {
            // Update inputs in the correct modal
            const prefix = currentModal === 'edit' ? 'edit' : 'add';
            const latInput = document.getElementById(prefix + 'LatitudeInput');
            const lngInput = document.getElementById(prefix + 'LongitudeInput');
            const coordsText = document.getElementById(prefix + 'Coordinates');
            
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;
            if (coordsText) coordsText.textContent = `Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
        
        // Open add modal and initialize map
        document.querySelectorAll('[onclick*="addModal"]').forEach(btn => {
            btn.onclick = function() {
                document.getElementById('addModal').classList.remove('hidden');
                currentModal = 'add';
                
                // Reset form
                document.querySelector('#addModal form').reset();
                document.getElementById('addCoordinates').textContent = '';
                
                setTimeout(() => {
                    initMap();
                }, 300);
            };
        });
    </script>
</body>
</html>
