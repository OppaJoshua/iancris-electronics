// Admin panel utilities

// Delete product
async function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return false;
    }

    try {
        const response = await fetch('/api/products/delete-product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('Product deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return true;
        } else {
            showAlert(data.message || 'Failed to delete product', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        showAlert('Error deleting product', 'error');
        return false;
    }
}

// Update request status
async function updateRequestStatus(requestId, status, notes = '') {
    try {
        const response = await fetch('/api/requests/update-request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: requestId,
                status: status,
                admin_notes: notes
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('Request status updated', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return true;
        } else {
            showAlert(data.message || 'Failed to update request', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error updating request:', error);
        showAlert('Error updating request', 'error');
        return false;
    }
}

// Show modal
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

// Hide modal
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Preview image before upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fixed top-4 right-4 z-50 max-w-md`;
    alertDiv.textContent = message;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Make functions globally available
window.deleteProduct = deleteProduct;
window.updateRequestStatus = updateRequestStatus;
window.showModal = showModal;
window.hideModal = hideModal;
window.previewImage = previewImage;
window.formatDate = formatDate;
window.showAlert = showAlert;

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
});