<?php
$page_title = "Request Management";
require_once 'includes/header.php';
require_once '../config/database.php';
require_once 'includes/nav.php';

// Get filter status
$filter_status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Get all requests
$conn = getDBConnection();
$requests = array();

if ($conn) {
    $query = "SELECT * FROM requests WHERE 1=1";
    $params = array();
    
    if (!empty($filter_status)) {
        $query .= " AND status = $1";
        $params[] = $filter_status;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    if (count($params) > 0) {
        $result = pg_query_params($conn, $query, $params);
    } else {
        $result = pg_query($conn, $query);
    }
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $row['products'] = json_decode($row['products'], true);
            $requests[] = $row;
        }
    }
    
    closeDBConnection($conn);
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Request Management</h1>
        <p class="text-gray-600">View and manage customer product requests</p>
    </div>

    <!-- Filter Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="?status=" class="px-4 py-2 border border-gray-200 <?php echo empty($filter_status) ? 'bg-gray-900 text-white' : 'bg-white text-gray-900 hover:bg-gray-100'; ?>">
            All Requests
        </a>
        <a href="?status=pending" class="px-4 py-2 border border-gray-200 <?php echo $filter_status === 'pending' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900 hover:bg-gray-100'; ?>">
            Pending
        </a>
        <a href="?status=confirmed" class="px-4 py-2 border border-gray-200 <?php echo $filter_status === 'confirmed' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900 hover:bg-gray-100'; ?>">
            Confirmed
        </a>
        <a href="?status=completed" class="px-4 py-2 border border-gray-200 <?php echo $filter_status === 'completed' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900 hover:bg-gray-100'; ?>">
            Completed
        </a>
        <a href="?status=cancelled" class="px-4 py-2 border border-gray-200 <?php echo $filter_status === 'cancelled' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900 hover:bg-gray-100'; ?>">
            Cancelled
        </a>
    </div>

    <!-- Requests List -->
    <div class="space-y-4">
        <?php if (empty($requests)): ?>
            <div class="border border-gray-200 p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Requests Found</h3>
                <p class="text-gray-600">There are no requests matching your filter</p>
            </div>
        <?php else: ?>
            <?php foreach ($requests as $request): ?>
                <div class="border border-gray-200 p-6">
                    <div class="flex flex-col md:flex-row justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Request #<?php echo $request['id']; ?></h3>
                            <p class="text-sm text-gray-600">Submitted on <?php echo date('F d, Y h:i A', strtotime($request['created_at'])); ?></p>
                        </div>
                        <div>
                            <?php
                            $status_colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $color = $status_colors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 text-sm font-medium <?php echo $color; ?>"><?php echo ucfirst($request['status']); ?></span>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Customer Information</h4>
                            <div class="space-y-1 text-sm">
                                <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($request['user_name']); ?></p>
                                <p><span class="font-medium">Email:</span> <?php echo htmlspecialchars($request['user_email']); ?></p>
                                <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($request['user_phone'] ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Request Details</h4>
                            <div class="space-y-1 text-sm">
                                <p><span class="font-medium">Total Items:</span> <?php echo $request['total_items']; ?></p>
                                <?php if ($request['message']): ?>
                                    <p><span class="font-medium">Message:</span> <?php echo htmlspecialchars($request['message']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Products List -->
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Products</h4>
                        <div class="space-y-2">
                            <?php foreach ($request['products'] as $product): ?>
                                <div class="flex justify-between items-center border border-gray-200 p-3">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['category'] ?? ''); ?></p>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Qty: <?php echo $product['quantity']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($request['admin_notes']): ?>
                        <div class="mb-4 bg-gray-50 p-3">
                            <h4 class="font-semibold text-gray-900 mb-1">Admin Notes</h4>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($request['admin_notes']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200">
                        <?php if ($request['status'] === 'pending'): ?>
                            <button onclick="updateStatus(<?php echo $request['id']; ?>, 'confirmed', 'Request confirmed. Will contact customer for appointment.')" class="btn btn-blue text-sm">Confirm Request</button>
                            <button onclick="updateStatus(<?php echo $request['id']; ?>, 'cancelled', 'Request cancelled by admin.')" class="btn btn-primary text-sm">Cancel Request</button>
                        <?php elseif ($request['status'] === 'confirmed'): ?>
                            <button onclick="updateStatus(<?php echo $request['id']; ?>, 'completed', 'Request completed and payment received.')" class="btn btn-blue text-sm">Mark as Completed</button>
                            <button onclick="updateStatus(<?php echo $request['id']; ?>, 'cancelled', 'Request cancelled by admin.')" class="btn btn-primary text-sm">Cancel Request</button>
                        <?php endif; ?>
                        <button onclick="addNotes(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['admin_notes'] ?? ''); ?>')" class="btn btn-primary text-sm">Add/Edit Notes</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Notes Modal -->
<div id="notes-modal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Admin Notes</h2>
            <button onclick="hideModal('notes-modal')" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form onsubmit="saveNotes(event)">
            <input type="hidden" id="notes-request-id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-900 mb-2">Notes</label>
                <textarea id="notes-text" rows="4" class="w-full" placeholder="Enter notes about this request..."></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="hideModal('notes-modal')" class="btn btn-primary">Cancel</button>
                <button type="submit" class="btn btn-blue">Save Notes</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/admin.js"></script>
<script>
    async function updateStatus(requestId, status, notes) {
        if (!confirm('Are you sure you want to update this request status?')) {
            return;
        }

        const success = await updateRequestStatus(requestId, status, notes);
        if (success) {
            window.location.reload();
        }
    }

    function addNotes(requestId, currentNotes) {
        document.getElementById('notes-request-id').value = requestId;
        document.getElementById('notes-text').value = currentNotes;
        showModal('notes-modal');
    }

    async function saveNotes(event) {
        event.preventDefault();
        
        const requestId = document.getElementById('notes-request-id').value;
        const notes = document.getElementById('notes-text').value;

        try {
            const response = await fetch('/api/requests/update-request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    request_id: requestId,
                    status: 'pending',
                    admin_notes: notes
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('Notes saved successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert(data.message || 'Failed to save notes', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error saving notes', 'error');
        }
    }
</script>

</body>
</html>