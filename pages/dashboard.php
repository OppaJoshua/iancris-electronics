<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

// Get user information and verify user exists BEFORE any output
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists - do this BEFORE including nav/header
if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php?error=user_not_found");
    exit();
}

$user = $result->fetch_assoc();
$_SESSION['user_name'] = $user['first_name'];

// Get recent orders
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$recent_orders = $orders_stmt->get_result();

// NOW include files that produce output
$page_title = "My Dashboard";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Orders</h1>
            <p class="text-gray-600">Track your installation orders</p>
        </div>

        <?php if ($recent_orders->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <?php
                    // Get order items
                    $order_id = $order['id'];
                    $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $items_stmt->bind_param("i", $order_id);
                    $items_stmt->execute();
                    $items_result = $items_stmt->get_result();
                    
                    // Determine status info
                    $status_info = [
                        'pending' => ['icon' => '🕒', 'text' => 'Pending', 'color' => 'yellow', 'step' => 1],
                        'confirmed' => ['icon' => '📞', 'text' => 'Confirmed', 'color' => 'blue', 'step' => 2],
                        'scheduled' => ['icon' => '📅', 'text' => 'Scheduled', 'color' => 'purple', 'step' => 3],
                        'completed' => ['icon' => '✅', 'text' => 'Completed', 'color' => 'green', 'step' => 4],
                        'cancelled' => ['icon' => '❌', 'text' => 'Cancelled', 'color' => 'red', 'step' => 0]
                    ];
                    $current_status = $status_info[$order['status']] ?? $status_info['pending'];
                    ?>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Order Header -->
                        <div class="p-6 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Order #<?php echo $order['id']; ?></h3>
                                    <p class="text-sm text-gray-600">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-<?php echo $current_status['color']; ?>-100 text-<?php echo $current_status['color']; ?>-800">
                                    <?php echo $current_status['icon']; ?> <?php echo $current_status['text']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Order Timeline -->
                        <div class="p-6 bg-white">
                            <div class="relative">
                                <!-- Timeline Line -->
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                                
                                <!-- Steps -->
                                <div class="space-y-6">
                                    <!-- Step 1: Order Placed -->
                                    <div class="relative flex items-start">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center bg-green-600 text-white font-bold text-sm z-10">✓</div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="font-semibold text-gray-900">Order Placed</h4>
                                            <p class="text-sm text-gray-600"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Step 2: Waiting for Admin Call -->
                                    <div class="relative flex items-start">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $current_status['step'] >= 2 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-500'; ?> font-bold text-sm z-10">
                                            <?php echo $current_status['step'] >= 2 ? '✓' : '2'; ?>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="font-semibold text-gray-900">Waiting for Admin Call</h4>
                                            <p class="text-sm text-gray-600">
                                                <?php if ($current_status['step'] >= 2): ?>
                                                    Admin confirmed your order
                                                <?php else: ?>
                                                    You will receive a call soon
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Step 3: Installation Scheduled -->
                                    <div class="relative flex items-start">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $current_status['step'] >= 3 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-500'; ?> font-bold text-sm z-10">
                                            <?php echo $current_status['step'] >= 3 ? '✓' : '3'; ?>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="font-semibold text-gray-900">Installation Scheduled</h4>
                                            <?php if (!empty($order['installation_date'])): ?>
                                                <p class="text-sm text-gray-600">
                                                    📅 <?php echo date('M d, Y', strtotime($order['installation_date'])); ?>
                                                    <?php if (!empty($order['installation_time'])): ?>
                                                        at <?php echo date('h:i A', strtotime($order['installation_time'])); ?>
                                                    <?php endif; ?>
                                                </p>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-600">Date will be confirmed by admin</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Step 4: Completed -->
                                    <div class="relative flex items-start">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $current_status['step'] >= 4 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-500'; ?> font-bold text-sm z-10">
                                            <?php echo $current_status['step'] >= 4 ? '✓' : '4'; ?>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="font-semibold text-gray-900">Installation Completed</h4>
                                            <p class="text-sm text-gray-600">
                                                <?php if ($current_status['step'] >= 4): ?>
                                                    Thank you for your order!
                                                <?php else: ?>
                                                    Your CCTV will be installed soon
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="p-6 border-t border-gray-200 bg-gray-50">
                            <h4 class="font-semibold text-gray-900 mb-3">Order Items</h4>
                            <div class="space-y-2">
                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        <span class="text-sm font-medium text-gray-900">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <!-- Contact Info -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm text-gray-600">
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($order['user_phone']); ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <strong>Address:</strong> <?php echo htmlspecialchars($order['user_address']); ?>
                                </p>
                                <?php if (!empty($order['admin_notes'])): ?>
                                    <p class="text-sm text-gray-600 mt-2">
                                        <strong>Admin Notes:</strong> <?php echo htmlspecialchars($order['admin_notes']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                <p class="text-gray-600 mb-6">Start shopping to place your first order</p>
                <a href="products.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Browse Products
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
