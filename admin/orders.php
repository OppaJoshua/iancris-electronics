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

// Get pending orders count for notification badge
$pending_count_query = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$pending_count = $pending_count_query->fetch_assoc()['count'];

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $installation_date = $_POST['installation_date'] ?: null;
    $installation_time = $_POST['installation_time'] ?: null;
    $admin_notes = trim($_POST['admin_notes']);
    
    // Server-side validation
    if (in_array($status, ['confirmed', 'scheduled']) && (empty($installation_date) || empty($installation_time))) {
        $error = "Installation date and time are required for confirmed/scheduled orders.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, installation_date = ?, installation_time = ?, admin_notes = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $status, $installation_date, $installation_time, $admin_notes, $order_id);
        
        if ($stmt->execute()) {
            $message = "Order #$order_id updated successfully!";
            $message_type = 'success';
            
            // Refresh pending count
            $pending_count_query = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
            $pending_count = $pending_count_query->fetch_assoc()['count'];
        } else {
            $error = "Error updating order. Please try again.";
        }
    }
}

// Get all orders
$sql = "SELECT o.* FROM orders o ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar-link.active {
            background-color: #EFF6FF;
            color: #2563EB;
        }
        .sidebar-link:hover {
            background-color: #F3F4F6;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-confirmed { background-color: #dbeafe; color: #1e40af; }
        .status-scheduled { background-color: #ede9fe; color: #5b21b6; }
        .status-completed { background-color: #dcfce7; color: #15803d; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        
        /* Notification badge styles */
        .notification-badge {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            background-color: #DC2626;
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            min-width: 1.5rem;
            text-align: center;
            animation: pulse-badge 2s infinite;
        }
        
        @keyframes pulse-badge {
            0%, 100% {
                transform: translateY(-50%) scale(1);
            }
            50% {
                transform: translateY(-50%) scale(1.1);
            }
        }
        
        .sidebar-link {
            position: relative;
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
        
        /* Form validation styles */
        .input-error {
            border-color: #EF4444 !important;
        }
        
        .error-message {
            color: #EF4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        /* Modal animations */
        #orderModal {
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
        
        #orderModal .bg-white {
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
        
        #orderModal.closing {
            animation: fadeOut 0.3s ease-out forwards;
        }
        
        @keyframes fadeOut {
            to {
                opacity: 0;
            }
        }
        
        #orderModal.closing .bg-white {
            animation: slideDown 0.3s ease-out forwards;
        }
        
        @keyframes slideDown {
            to {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
        }
        
        /* Input focus animations */
        input:focus, select:focus, textarea:focus {
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        /* Button hover animations */
        button {
            transition: all 0.2s ease;
        }
        
        button:active {
            transform: scale(0.98);
        }
        
        /* Status badge pulse for pending */
        .status-badge.status-pending {
            animation: glow 2s ease-in-out infinite;
        }
        
        @keyframes glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.4);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(251, 191, 36, 0);
            }
        }
        
        /* Smooth section transitions */
        .info-card {
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        /* Label animations */
        label {
            transition: color 0.2s ease;
        }
        
        input:focus + label,
        select:focus + label,
        textarea:focus + label {
            color: #2563EB;
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
                
                <a href="orders.php" class="sidebar-link active flex items-center px-4 py-3 text-gray-700 rounded-lg mb-1 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span class="font-medium">Orders</span>
                    <?php if ($pending_count > 0): ?>
                        <span class="notification-badge"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
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
                        <h2 class="text-2xl font-bold text-gray-900">Orders</h2>
                        <p class="text-gray-600 text-sm mt-1">Manage customer orders</p>
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
                    <div class="p-6 border-b border-gray-200 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Order ID / Customer</label>
                                <input type="text" id="searchInput" placeholder="Search..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                                <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                                <select id="sortFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="name">Customer Name</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button onclick="resetFilters()" class="w-full bg-gray-600 text-white py-2 rounded-lg hover:bg-gray-700 transition text-sm font-semibold">
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Total: <span id="orderCount"><?php echo $result->num_rows; ?></span> orders</p>
                    </div>
                    
                    <!-- Orders Table -->
                    <?php if ($result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php while ($order = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition" data-order-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['status']; ?>" data-customer="<?php echo strtolower($order['user_name']); ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['user_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($order['user_phone']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo $order['total_items']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($order)); ?>)" class="text-blue-600 hover:text-blue-800 font-semibold">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <p class="text-gray-600">No orders yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Order Edit Modal -->
    <div id="orderModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden shadow-2xl">
            <!-- Header with gradient -->
            <div class="p-6 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-gradient-to-r from-white to-gray-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Order</h3>
                        <p class="text-sm text-gray-500">Order #<span id="modalOrderId" class="font-semibold text-blue-600"></span></p>
                    </div>
                </div>
                <button onclick="closeOrderModal()" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="overflow-y-auto max-h-[calc(90vh-180px)] p-6">
                <form method="POST" class="space-y-6">
                    <input type="hidden" id="modalOrderIdInput" name="order_id">

                    <!-- Customer Info Card -->
                    <div class="info-card bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 border border-blue-100">
                        <div class="flex items-center space-x-2 mb-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <h4 class="font-semibold text-gray-900">Customer Information</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                                <p id="modalCustomerName" class="text-gray-900 font-semibold"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                <p id="modalCustomerPhone" class="text-gray-900 font-semibold"></p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                                <p id="modalCustomerAddress" class="text-gray-900 font-semibold"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Google Maps Location Card -->
                    <div id="mapContainer" class="info-card bg-white rounded-xl p-5 border border-gray-200" style="display: none;">
                        <div class="flex items-center space-x-2 mb-4">
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C7.802 0 4.403 3.403 4.403 7.602c0 6.243 6.377 14.298 7.055 15.177a.75.75 0 001.084 0c.678-.879 7.055-8.934 7.055-15.177C19.597 3.403 16.198 0 12 0zm0 11.25a3.75 3.75 0 110-7.5 3.75 3.75 0 010 7.5z"/>
                            </svg>
                            <h4 class="font-semibold text-gray-900">Installation Location</h4>
                        </div>
                        <div id="orderMap" class="w-full h-64 rounded-lg border border-gray-300"></div>
                        <p id="coordinatesDisplay" class="text-xs text-gray-600 mt-2"></p>
                    </div>

                    <!-- Order Items Card -->
                    <div class="info-card bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-5 border border-purple-100">
                        <div class="flex items-center space-x-2 mb-4">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <h4 class="font-semibold text-gray-900">Order Items</h4>
                        </div>
                        <div id="modalOrderItems" class="space-y-2 bg-white rounded-lg p-3 border border-purple-100"></div>
                    </div>

                    <!-- Status Update -->
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Order Status</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="modalStatus" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm hover:shadow-md" onchange="handleStatusChange()">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Installation Details -->
                    <div id="installationFields" class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Installation Date</span>
                                <span class="text-red-500 required-star hidden">*</span>
                            </label>
                            <input type="date" name="installation_date" id="modalInstallDate" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm hover:shadow-md">
                            <p class="error-message" id="dateError">Installation date is required</p>
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Installation Time</span>
                                <span class="text-red-500 required-star hidden">*</span>
                            </label>
                            <input type="time" name="installation_time" id="modalInstallTime" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm hover:shadow-md">
                            <p class="error-message" id="timeError">Installation time is required</p>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span>Admin Notes</span>
                        </label>
                        <textarea name="admin_notes" id="modalNotes" rows="3" placeholder="Add notes about this order..." class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none shadow-sm hover:shadow-md"></textarea>
                    </div>
                </form>
            </div>

            <!-- Footer with Action Buttons -->
            <div class="p-6 border-t border-gray-100 bg-gray-50 flex gap-3">
                <button type="button" onclick="closeOrderModal()" class="flex-1 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition font-semibold flex items-center justify-center space-x-2 shadow-sm hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>Cancel</span>
                </button>
                <button type="submit" name="update_order" form="orderForm" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-semibold flex items-center justify-center space-x-2 shadow-md hover:shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Update Order</span>
                </button>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8" async defer></script>

    <script>
        const orderModal = document.getElementById('orderModal');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const sortFilter = document.getElementById('sortFilter');
        let orderMap;
        let orderMarker;

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
            
            // Auto remove after 5 seconds
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

        // Handle status change to show/hide required fields
        function handleStatusChange() {
            const status = document.getElementById('modalStatus').value;
            const requiredStars = document.querySelectorAll('.required-star');
            const installDate = document.getElementById('modalInstallDate');
            const installTime = document.getElementById('modalInstallTime');
            
            if (status === 'confirmed' || status === 'scheduled') {
                requiredStars.forEach(star => star.classList.remove('hidden'));
                installDate.required = true;
                installTime.required = true;
            } else {
                requiredStars.forEach(star => star.classList.add('hidden'));
                installDate.required = false;
                installTime.required = false;
                // Clear errors
                document.getElementById('dateError').classList.remove('show');
                document.getElementById('timeError').classList.remove('show');
                installDate.classList.remove('input-error');
                installTime.classList.remove('input-error');
            }
        }

        // Form validation before submit
        document.querySelector('#orderModal form').addEventListener('submit', function(e) {
            const status = document.getElementById('modalStatus').value;
            const installDate = document.getElementById('modalInstallDate');
            const installTime = document.getElementById('modalInstallTime');
            const dateError = document.getElementById('dateError');
            const timeError = document.getElementById('timeError');
            
            // Clear previous errors
            dateError.classList.remove('show');
            timeError.classList.remove('show');
            installDate.classList.remove('input-error');
            installTime.classList.remove('input-error');
            
            let hasError = false;
            
            if (status === 'confirmed' || status === 'scheduled') {
                if (!installDate.value) {
                    dateError.classList.add('show');
                    installDate.classList.add('input-error');
                    hasError = true;
                }
                
                if (!installTime.value) {
                    timeError.classList.add('show');
                    installTime.classList.add('input-error');
                    hasError = true;
                }
                
                if (hasError) {
                    e.preventDefault();
                    showToast('Please fill in all required fields', 'error', 'Validation Error');
                    return false;
                }
            }
        });

        // Search and Filter
        [searchInput, statusFilter, sortFilter].forEach(el => {
            el.addEventListener('change', filterOrders);
            el.addEventListener('keyup', filterOrders);
        });

        function filterOrders() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const rows = document.querySelectorAll('tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const orderId = row.dataset.orderId;
                const status = row.dataset.status;
                const customer = row.dataset.customer;

                const matchesSearch = orderId.includes(searchTerm) || customer.includes(searchTerm);
                const matchesStatus = !statusValue || status === statusValue;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('orderCount').textContent = visibleCount;
        }

        function resetFilters() {
            searchInput.value = '';
            statusFilter.value = '';
            sortFilter.value = 'newest';
            filterOrders();
        }

        function openOrderModal(order) {
            document.getElementById('modalOrderId').textContent = order.id;
            document.getElementById('modalOrderIdInput').value = order.id;
            document.getElementById('modalCustomerName').textContent = order.user_name;
            document.getElementById('modalCustomerPhone').textContent = order.user_phone;
            document.getElementById('modalCustomerAddress').textContent = order.user_address;
            document.getElementById('modalStatus').value = order.status;
            document.getElementById('modalInstallDate').value = order.installation_date || '';
            document.getElementById('modalInstallTime').value = order.installation_time || '';
            document.getElementById('modalNotes').value = order.admin_notes || '';

            // Trigger status change handler to show/hide required fields
            handleStatusChange();

            // Fetch customer location data
            fetch(`get-customer-location.php?user_id=${order.user_id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.latitude && data.longitude) {
                        // Show map container
                        document.getElementById('mapContainer').style.display = 'block';
                        
                        // Initialize map
                        setTimeout(() => {
                            initOrderMap(parseFloat(data.latitude), parseFloat(data.longitude), order.user_name);
                        }, 100);
                        
                        // Display coordinates
                        document.getElementById('coordinatesDisplay').textContent = 
                            `Coordinates: ${data.latitude}, ${data.longitude}`;
                    } else {
                        // Hide map if no location data
                        document.getElementById('mapContainer').style.display = 'none';
                    }
                });

            // Fetch and display order items
            fetch(`get-order-items.php?order_id=${order.id}`)
                .then(res => res.json())
                .then(items => {
                    const itemsHtml = items.map(item => 
                        `<div class="flex justify-between items-center py-2 border-b border-purple-100 last:border-0">
                            <span class="text-gray-900 font-medium">${item.product_name}</span>
                            <span class="text-sm font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Qty: ${item.quantity}</span>
                        </div>`
                    ).join('');
                    document.getElementById('modalOrderItems').innerHTML = itemsHtml;
                });

            orderModal.classList.remove('hidden');
        }

        function initOrderMap(latitude, longitude, customerName) {
            const location = { lat: latitude, lng: longitude };
            
            orderMap = new google.maps.Map(document.getElementById('orderMap'), {
                center: location,
                zoom: 15,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
                styles: [
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [{ visibility: "off" }]
                    }
                ]
            });
            
            // Add marker
            orderMarker = new google.maps.Marker({
                position: location,
                map: orderMap,
                title: `Installation Location for ${customerName}`,
                animation: google.maps.Animation.DROP,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                }
            });
            
            // Add info window
            const infoWindow = new google.maps.InfoWindow({
                content: `<div style="padding: 8px;">
                    <h3 style="font-weight: bold; margin-bottom: 4px;">${customerName}</h3>
                    <p style="font-size: 12px; color: #666;">Installation Location</p>
                    <p style="font-size: 11px; margin-top: 4px;">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}" 
                           target="_blank" style="color: #2563EB;">Get Directions</a>
                    </p>
                </div>`
            });
            
            orderMarker.addListener('click', () => {
                infoWindow.open(orderMap, orderMarker);
            });
            
            // Auto-open info window
            infoWindow.open(orderMap, orderMarker);
        }

        function closeOrderModal() {
            // Add closing animation
            orderModal.classList.add('closing');
            
            setTimeout(() => {
                // Clear errors
                document.getElementById('dateError').classList.remove('show');
                document.getElementById('timeError').classList.remove('show');
                document.getElementById('modalInstallDate').classList.remove('input-error');
                document.getElementById('modalInstallTime').classList.remove('input-error');
                
                // Hide map
                document.getElementById('mapContainer').style.display = 'none';
                
                orderModal.classList.add('hidden');
                orderModal.classList.remove('closing');
            }, 300);
        }

        // Close modal on outside click
        orderModal.addEventListener('click', (e) => {
            if (e.target === orderModal) closeOrderModal();
        });
        
        // Add form id to the actual form
        document.querySelector('#orderModal form').id = 'orderForm';
    </script>
</body>
</html>
