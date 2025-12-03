<?php
session_start();

// Don't block cart access - let users view cart even when not logged in
// They'll be prompted to login when they try to place an order

$page_title = "Cart";
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/database.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-8 py-16">
    <div class="mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Shopping Cart</h1>
        <p class="text-gray-600">Review your selected products</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
            <div id="cart-items" class="space-y-4">
                <!-- Cart items will be loaded here -->
            </div>

            <!-- Empty Cart Message -->
            <div id="empty-cart" class="hidden text-center py-12 bg-white rounded-lg border border-gray-200">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-6">Add some products to get started</p>
                <a href="products.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">Browse Products</a>
            </div>
        </div>

        <!-- Summary -->
        <div class="lg:col-span-1">
            <div id="cart-summary" class="hidden bg-white border border-gray-200 rounded-lg p-6 sticky top-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600">
                        <span>Total Items:</span>
                        <span id="total-items" class="font-semibold text-gray-900">0</span>
                    </div>
                </div>

                <button onclick="startOrder()" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-md hover:shadow-lg">
                    Place Order
                </button>

                <p class="text-xs text-gray-500 text-center mt-4">
                    We'll call you to confirm installation details
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Installation Details Modal -->
<div id="installationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-5 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold mb-1">Installation Details</h3>
                    <p class="text-blue-100 text-xs">Provide your contact and location information</p>
                </div>
                <button type="button" onclick="closeInstallationModal()" class="text-white hover:bg-white/20 rounded-full p-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Scrollable Content -->
        <div class="overflow-y-auto flex-1 p-5">
            <form id="installationForm" class="space-y-5">
                <!-- Contact Information -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        Contact Information
                    </h4>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input type="tel" id="phoneInput" required 
                               pattern="[0-9]{11}" 
                               maxlength="11"
                               placeholder="09123456789"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">11 digits (e.g., 09123456789)</p>
                    </div>
                </div>

                <!-- Installation Address -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Installation Address
                    </h4>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Complete Address *</label>
                        <textarea id="addressInput" required rows="2"
                                  placeholder="Street, Barangay, City, Province"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>
                
                <!-- Google Maps -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-medium text-gray-700 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C7.802 0 4.403 3.403 4.403 7.602c0 6.243 6.377 14.298 7.055 15.177a.75.75 0 001.084 0c.678-.879 7.055-8.934 7.055-15.177C19.597 3.403 16.198 0 12 0zm0 11.25a3.75 3.75 0 110-7.5 3.75 3.75 0 010 7.5z"/>
                        </svg>
                        Pin Your Location (Optional)
                    </label>
                    <div id="map" class="w-full h-48 rounded-lg border border-gray-300 mb-2"></div>
                    <p class="text-xs text-gray-500">Click or drag marker to set exact location</p>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <p id="coordinates" class="text-xs text-blue-600 mt-1"></p>
                </div>

                <!-- Additional Notes -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-medium text-gray-700 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        Special Instructions (Optional)
                    </label>
                    <textarea id="notesInput" rows="3"
                              placeholder="Preferred installation time, camera placement requests, etc."
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>

                <!-- Info Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-xs text-blue-900 leading-relaxed">
                        <strong>Next Steps:</strong><br>
                        Our team will call you within 24 hours to confirm installation details and schedule a site visit if needed.
                    </p>
                </div>
            </form>
        </div>
        
        <!-- Fixed Footer -->
        <div class="border-t border-gray-200 p-4 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button type="button" onclick="closeInstallationModal()" class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 transition font-semibold text-sm">
                    Cancel
                </button>
                <button type="button" onclick="submitInstallationForm()" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-2.5 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold text-sm shadow-md">
                    Confirm Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-8 text-center transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
        <!-- Animated Checkmark -->
        <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-12 h-12 text-green-600 checkmark-animate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h3 class="text-2xl font-bold text-gray-900 mb-3">Order Placed Successfully!</h3>
        <p class="text-gray-600 mb-6 leading-relaxed">
            🎉 Thank you for your order!<br>
            📞 Our team will contact you shortly to confirm installation details.
        </p>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800">
                <strong>What's next?</strong><br>
                You'll receive a call within 24 hours to schedule your installation.
            </p>
        </div>
        
        <button onclick="closeSuccessModal()" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
            View My Orders
        </button>
        
        <p class="text-xs text-gray-500 mt-4">Redirecting in <span id="countdown">5</span> seconds...</p>
    </div>
</div>

<style>
    /* Modal animations */
    #successModal.show #successModalContent {
        animation: modalSlideIn 0.3s ease-out forwards;
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    /* Checkmark animation */
    .checkmark-animate {
        animation: checkmarkDraw 0.6s ease-out 0.2s forwards;
        stroke-dasharray: 50;
        stroke-dashoffset: 50;
    }
    
    @keyframes checkmarkDraw {
        to {
            stroke-dashoffset: 0;
        }
    }
    
    /* Pulse animation for checkmark circle */
    #successModal.show .bg-green-100 {
        animation: pulse 0.5s ease-out;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
    }
</style>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8" async defer></script>

<script>
let map;
let marker;
let isMapInitialized = false;

function initMap() {
    // Only initialize if modal is visible and not already initialized
    if (isMapInitialized) return;
    
    // Default center: Cebu City, Philippines
    const defaultLocation = { lat: 10.3157, lng: 123.8854 };
    
    const mapElement = document.getElementById('map');
    if (!mapElement) return;
    
    map = new google.maps.Map(mapElement, {
        center: defaultLocation,
        zoom: 13,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true
    });
    
    // Add marker
    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true,
        title: "Installation Location"
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
    
    isMapInitialized = true;
}

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('coordinates').textContent = 
        `Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
}

document.addEventListener('DOMContentLoaded', () => {
    loadCart();
});

function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        document.getElementById('cart-items').classList.add('hidden');
        document.getElementById('empty-cart').classList.remove('hidden');
        document.getElementById('cart-summary').classList.add('hidden');
    } else {
        displayCartItems(cart);
        updateSummary(cart);
        document.getElementById('cart-items').classList.remove('hidden');
        document.getElementById('empty-cart').classList.add('hidden');
        document.getElementById('cart-summary').classList.remove('hidden');
    }
}

function displayCartItems(cart) {
    const container = document.getElementById('cart-items');
    
    // Fetch product details from database
    const productIds = cart.map(item => item.id).join(',');
    
    fetch(`get-cart-products.php?ids=${productIds}`)
        .then(res => res.json())
        .then(products => {
            container.innerHTML = cart.map(item => {
                const product = products.find(p => p.id == item.id);
                if (!product) return '';
                
                return `
                    <div class="bg-white border border-gray-200 rounded-lg p-4 flex gap-4" data-product-id="${item.id}">
                        <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                            ${product.image ? 
                                `<img src="../${product.image}" alt="${product.name}" class="w-full h-full object-cover">` :
                                `<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>`
                            }
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">${product.name}</h3>
                            <p class="text-sm text-gray-600 mb-3">${product.category || ''}</p>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2 border border-gray-300 rounded">
                                    <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="w-8 h-8 flex items-center justify-center hover:bg-gray-100">-</button>
                                    <span class="w-12 text-center font-medium">${item.quantity}</span>
                                    <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="w-8 h-8 flex items-center justify-center hover:bg-gray-100">+</button>
                                </div>
                                <button onclick="removeItem(${item.id})" class="text-red-600 text-sm hover:underline ml-auto">Remove</button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        })
        .catch(error => {
            console.error('Error loading products:', error);
            container.innerHTML = '<p class="text-red-600 text-center py-4">Error loading cart items</p>';
        });
}

function updateQuantity(productId, newQuantity) {
    if (newQuantity <= 0) {
        removeItem(productId);
        return;
    }

    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const item = cart.find(i => i.id === productId);
    
    if (item) {
        item.quantity = newQuantity;
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
        updateCartCount();
    }
}

function removeItem(productId) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
    updateCartCount();
}

function updateSummary(cart) {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.getElementById('total-items').textContent = totalItems;
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    const cartBadge = document.getElementById('cart-count');
    if (cartBadge) {
        if (totalItems > 0) {
            cartBadge.textContent = totalItems;
            cartBadge.classList.remove('hidden');
        } else {
            cartBadge.classList.add('hidden');
        }
    }
}

function startOrder() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    <?php if (isset($_SESSION['user_id'])): ?>
        // User is logged in - proceed with order
        fetch('check-phone.php')
            .then(res => res.json())
            .then(data => {
                // Pre-fill existing data
                if (data.phone) {
                    document.getElementById('phoneInput').value = data.phone;
                }
                if (data.address) {
                    document.getElementById('addressInput').value = data.address;
                }
                
                // Show installation details modal
                document.getElementById('installationModal').classList.remove('hidden');
                
                // Initialize map after modal is shown - call it manually instead of callback
                setTimeout(() => {
                    if (typeof google !== 'undefined' && google.maps) {
                        initMap();
                    } else {
                        // Wait for Google Maps to load
                        const checkGoogleMaps = setInterval(() => {
                            if (typeof google !== 'undefined' && google.maps) {
                                clearInterval(checkGoogleMaps);
                                initMap();
                            }
                        }, 100);
                    }
                }, 200);
            });
    <?php else: ?>
        // User not logged in - redirect to login
        if (confirm('Please login to place an order. Go to login page?')) {
            window.location.href = 'login.php?redirect=cart.php';
        }
    <?php endif; ?>
}

function submitInstallationForm() {
    const form = document.getElementById('installationForm');
    
    // Validate required fields
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const phone = document.getElementById('phoneInput').value;
    const address = document.getElementById('addressInput').value;
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;
    const notes = document.getElementById('notesInput').value;
    
    // Save installation details
    fetch('save-contact.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
            phone, 
            address,
            latitude,
            longitude,
            notes
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeInstallationModal();
            placeOrder();
        } else {
            alert('Error saving installation details');
        }
    });
}

function closeInstallationModal() {
    document.getElementById('installationModal').classList.add('hidden');
    isMapInitialized = false; // Reset flag when modal closes
}

let countdownInterval;

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
    
    // Countdown and auto-redirect
    let seconds = 5;
    const countdownEl = document.getElementById('countdown');
    
    countdownInterval = setInterval(() => {
        seconds--;
        countdownEl.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(countdownInterval);
            window.location.href = 'dashboard.php';
        }
    }, 1000);
}

function closeSuccessModal() {
    clearInterval(countdownInterval);
    document.getElementById('successModal').classList.remove('show');
    setTimeout(() => {
        document.getElementById('successModal').classList.add('hidden');
        window.location.href = 'dashboard.php';
    }, 300);
}

function placeOrder() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    console.log('Placing order with cart:', cart);
    console.log('Fetching: place-order.php');
    
    fetch('place-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ cart })
    })
    .then(res => {
        console.log('Response status:', res.status);
        console.log('Response ok:', res.ok);
        console.log('Response headers:', res.headers);
        
        // Try to get response text first to see what's being returned
        return res.text().then(text => {
            console.log('Raw response:', text);
            
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}, body: ${text}`);
            }
            
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`Invalid JSON response: ${text}`);
            }
        });
    })
    .then(data => {
        console.log('Parsed server response:', data);
        
        if (data.success) {
            // Clear cart
            localStorage.removeItem('cart');
            updateCartCount();
            
            // Show success modal instead of alert
            showSuccessModal();
        } else {
            console.error('Order failed:', data);
            alert('Error placing order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error placing order: ' + error.message + '\n\nPlease check:\n1. Browser console for details\n2. Make sure place-order.php exists\n3. Check PHP error logs');
    });
}

// Add phone number validation
document.getElementById('phoneInput').addEventListener('input', function(e) {
    // Remove non-numeric characters
    let value = e.target.value.replace(/\D/g, '');
    
    // Limit to 11 digits
    if (value.length > 11) {
        value = value.slice(0, 11);
    }
    
    e.target.value = value;
    
    // Visual feedback
    if (value.length > 0 && value.length !== 11) {
        e.target.classList.add('border-yellow-400');
        e.target.classList.remove('border-gray-300', 'border-green-400');
    } else if (value.length === 11) {
        e.target.classList.add('border-green-400');
        e.target.classList.remove('border-gray-300', 'border-yellow-400');
    } else {
        e.target.classList.add('border-gray-300');
        e.target.classList.remove('border-yellow-400', 'border-green-400');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>