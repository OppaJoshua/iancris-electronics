// Cart management functions

// Add product to cart
async function addToCart(productId, quantity = 1) {
    try {
        const response = await fetch('/api/cart/add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        });

        const data = await response.json();

        if (data.success) {
            updateCartCount();
            showAlert('Product added to cart!', 'success');
            return true;
        } else {
            if (data.message === 'Please login to add items to cart') {
                if (confirm('Please login to add items to cart. Go to login page?')) {
                    window.location.href = '/pages/login.php';
                }
            } else {
                showAlert(data.message || 'Failed to add to cart', 'error');
            }
            return false;
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showAlert('Error adding to cart', 'error');
        return false;
    }
}

// Update cart item quantity
async function updateCartQuantity(productId, quantity) {
    try {
        const response = await fetch('/api/cart/update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        });

        const data = await response.json();

        if (data.success) {
            updateCartCount();
            return true;
        } else {
            showAlert(data.message || 'Failed to update cart', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error updating cart:', error);
        showAlert('Error updating cart', 'error');
        return false;
    }
}

// Remove item from cart
async function removeFromCart(productId) {
    if (!confirm('Remove this item from cart?')) {
        return false;
    }

    try {
        const response = await fetch('/api/cart/remove-from-cart.php', {
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
            updateCartCount();
            return true;
        } else {
            showAlert(data.message || 'Failed to remove from cart', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
        showAlert('Error removing from cart', 'error');
        return false;
    }
}

// Get cart items
async function getCart() {
    try {
        const response = await fetch('/api/cart/get-cart.php');
        const data = await response.json();

        if (data.success) {
            return data.items || [];
        }
        return [];
    } catch (error) {
        console.error('Error fetching cart:', error);
        return [];
    }
}

// Update cart count in navigation
async function updateCartCount() {
    try {
        const items = await getCart();
        const count = items.length;
        
        const cartCount = document.getElementById('cart-count');
        const cartCountMobile = document.getElementById('cart-count-mobile');
        
        if (count > 0) {
            if (cartCount) {
                cartCount.textContent = count;
                cartCount.classList.remove('hidden');
            }
            if (cartCountMobile) {
                cartCountMobile.textContent = count;
                cartCountMobile.classList.remove('hidden');
            }
        } else {
            if (cartCount) cartCount.classList.add('hidden');
            if (cartCountMobile) cartCountMobile.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
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
window.addToCart = addToCart;
window.updateCartQuantity = updateCartQuantity;
window.removeFromCart = removeFromCart;
window.getCart = getCart;
window.updateCartCount = updateCartCount;
window.showAlert = showAlert;

// Update cart count on page load
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});