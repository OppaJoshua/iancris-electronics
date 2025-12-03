<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

$order_id = intval($_GET['id'] ?? 0);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $status = $_POST['status'];
    $installation_date = $_POST['installation_date'] ?? null;
    $installation_time = $_POST['installation_time'] ?? null;
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, installation_date = ?, installation_time = ?, admin_notes = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $status, $installation_date, $installation_time, $admin_notes, $order_id);
    
    if ($stmt->execute()) {
        $message = "Order updated successfully!";
    }
}

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="orders.php" class="text-blue-600 hover:text-blue-800 font-medium">← Back to Orders</a>
        </div>

        <?php if (isset($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Order #<?php echo $order_id; ?></h1>
            
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Customer Information</h3>
                    <p class="text-sm text-gray-600">Name: <span class="font-medium"><?php echo htmlspecialchars($order['user_name']); ?></span></p>
                    <p class="text-sm text-gray-600">Email: <span class="font-medium"><?php echo htmlspecialchars($order['email']); ?></span></p>
                    <p class="text-sm text-gray-600">Phone: <span class="font-medium"><?php echo htmlspecialchars($order['user_phone']); ?></span></p>
                    <p class="text-sm text-gray-600">Address: <span class="font-medium"><?php echo htmlspecialchars($order['user_address']); ?></span></p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Order Information</h3>
                    <p class="text-sm text-gray-600">Order Date: <span class="font-medium"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span></p>
                    <p class="text-sm text-gray-600">Total Items: <span class="font-medium"><?php echo $order['total_items']; ?></span></p>
                    <p class="text-sm text-gray-600">Current Status: 
                        <span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </p>
                </div>
            </div>

            <h3 class="font-semibold text-gray-900 mb-3">Order Items</h3>
            <table class="w-full mb-6">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Product</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Quantity</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td class="px-4 py-2 text-sm"><?php echo $item['quantity']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <form method="POST" class="border-t pt-6">
                <h3 class="font-semibold text-gray-900 mb-4">Update Order Status</h3>
                
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="scheduled" <?php echo $order['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Installation Date</label>
                        <input type="date" name="installation_date" value="<?php echo $order['installation_date']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Installation Time</label>
                    <input type="time" name="installation_time" value="<?php echo $order['installation_time']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                    <textarea name="admin_notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($order['admin_notes']); ?></textarea>
                </div>
                
                <button type="submit" name="update_order" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Update Order
                </button>
            </form>
        </div>
    </div>
</body>
</html>
