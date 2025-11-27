<?php
session_start();
$page_title = "Products";
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/database.php';

// Get all categories
$categories_sql = "SELECT DISTINCT category FROM products WHERE status = 'active' AND category IS NOT NULL AND category != '' ORDER BY category";
$categories_result = $conn->query($categories_sql);

// Get products from database
$sql = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC";
$result = $conn->query($sql);

// Check if query was successful
if (!$result) {
    die("Error fetching products: " . $conn->error);
}
?>

<style>
.product-card {
    cursor: pointer;
    transition: all 0.2s;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.product-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.thumbnail {
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.2s;
}

.thumbnail.active {
    border-color: #2563EB;
}

.thumbnail:hover {
    border-color: #93C5FD;
}

.color-swatch {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    transition: border-color 0.2s;
}

.color-swatch.active {
    border-color: #2563EB;
}

.color-swatch:hover {
    border-color: #93C5FD;
}

/* Flying cart animation */
@keyframes flyToCart {
    0% {
        transform: translate(0, 0) scale(1);
        opacity: 1;
    }
    50% {
        transform: translate(var(--dx), var(--dy)) scale(0.5);
        opacity: 0.8;
    }
    100% {
        transform: translate(var(--dx), var(--dy)) scale(0.1);
        opacity: 0;
    }
}

.flying-item {
    position: fixed;
    z-index: 9999;
    pointer-events: none;
    animation: flyToCart 1s cubic-bezier(0.4, 0.0, 0.2, 1) forwards;
}

/* Cart icon pulse animation */
@keyframes cartPulse {
    0%, 100% {
        transform: scale(1);
    }
    25% {
        transform: scale(1.2);
    }
    50% {
        transform: scale(1);
    }
    75% {
        transform: scale(1.1);
    }
}

.cart-pulse-animation {
    animation: cartPulse 0.6s ease-in-out;
}

/* Success popup modal */
.success-popup {
    animation: successPopIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes successPopIn {
    0% {
        transform: scale(0) translateY(100px);
        opacity: 0;
    }
    100% {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
}

.success-popup-hide {
    animation: successPopOut 0.3s ease-out forwards;
}

@keyframes successPopOut {
    0% {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    100% {
        transform: scale(0.8) translateY(50px);
        opacity: 0;
    }
}
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Our Products</h1>
            <p class="text-gray-600">Find the perfect CCTV and IT solutions</p>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Search products..." 
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                
                <!-- Category Filter -->
                <select id="categoryFilter" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">All Categories</option>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($category['category']); ?>">
                            <?php echo htmlspecialchars($category['category']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <!-- Sort By -->
                <select id="sortFilter" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                </select>
                
                <!-- Reset Button -->
                <button onclick="resetFilters()" class="bg-gray-600 text-white py-2.5 rounded-lg hover:bg-gray-700 transition text-sm font-semibold">
                    Reset Filters
                </button>
            </div>
            <p class="text-sm text-gray-600 mt-3">Showing <span id="resultCount">0</span> products</p>
        </div>

        <!-- Toast Notification -->
        <div id="toast" class="hidden fixed top-20 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span id="toast-message">Item added to cart!</span>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div id="productsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <?php while ($product = $result->fetch_assoc()): ?>
                <div class="product-card" 
                     data-product-id="<?php echo $product['id']; ?>"
                     data-product-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>"
                     data-product-category="<?php echo htmlspecialchars(strtolower($product['category'] ?? '')); ?>"
                     data-created-at="<?php echo $product['created_at']; ?>">
                    
                    <!-- Product Image -->
                    <div class="aspect-square bg-gray-100 p-4 cursor-pointer" onclick="openProductModal(<?php echo $product['id']; ?>)">
                        <?php if (!empty($product['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-full object-contain">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Color Swatches -->
                    <div class="px-3 py-2 flex gap-1">
                        <div class="color-swatch active" style="background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%)"></div>
                        <div class="color-swatch" style="background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%)"></div>
                        <div class="color-swatch" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)"></div>
                        <div class="color-swatch" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%)"></div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-3 cursor-pointer" onclick="openProductModal(<?php echo $product['id']; ?>)">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1 line-clamp-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-xs text-gray-500 mb-3"><?php echo htmlspecialchars($product['category'] ?? 'ELECTRONICS'); ?></p>
                        
                        <!-- Add to Cart Button -->
                        <button onclick="event.stopPropagation(); quickAddToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')" 
                                class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-800 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            ADD TO CART
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <!-- No Results Message -->
            <div id="noResults" class="hidden text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
                <p class="text-gray-600">Try adjusting your search or filter to find what you're looking for.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded p-12 text-center border border-gray-200">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Products Available</h3>
                <p class="text-gray-600">Products will be added soon. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Detail Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-5xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex flex-col md:flex-row h-full">
            <!-- Left: Image Gallery -->
            <div class="md:w-1/2 p-6 bg-white">
                <button onclick="closeModal()" class="mb-4 text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                
                <!-- Main Image -->
                <div class="mb-4 bg-gray-100 rounded overflow-hidden aspect-square flex items-center justify-center relative group">
                    <img id="mainImage" src="" alt="" class="w-full h-full object-contain">
                    
                    <!-- Navigation Arrows -->
                    <button onclick="previousImage()" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition">
                        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button onclick="nextImage()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition">
                        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    
                    <!-- Image Counter -->
                    <div class="absolute bottom-3 right-3 bg-black/60 text-white px-3 py-1 rounded-full text-sm">
                        <span id="currentImageIndex">1</span> / <span id="totalImages">1</span>
                    </div>
                </div>
                
                <!-- Thumbnail Gallery -->
                <div id="thumbnailGallery" class="grid grid-cols-4 gap-2">
                    <!-- Thumbnails will be loaded here -->
                </div>
            </div>
            
            <!-- Right: Product Details -->
            <div class="md:w-1/2 p-6 overflow-y-auto bg-gray-50">
                <h2 id="modalProductName" class="text-2xl font-bold text-gray-900 mb-2"></h2>
                <p id="modalProductCategory" class="text-sm text-gray-500 mb-4"></p>
                
                <div class="border-t border-gray-200 pt-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
                    <p id="modalProductDescription" class="text-gray-600 text-sm leading-relaxed"></p>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Quantity</h3>
                    <div class="flex items-center gap-3">
                        <button onclick="modalUpdateQuantity(-1)" class="w-10 h-10 border-2 border-gray-300 rounded-lg flex items-center justify-center hover:border-blue-500 hover:text-blue-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span id="modalQuantity" class="text-2xl font-bold text-gray-900 w-16 text-center">1</span>
                        <button onclick="modalUpdateQuantity(1)" class="w-10 h-10 border-2 border-gray-300 rounded-lg flex items-center justify-center hover:border-blue-500 hover:text-blue-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button onclick="addToCartFromModal()" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold shadow-md">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Popup Modal -->
<div id="addToCartSuccessPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9998] p-4">
    <div class="success-popup bg-white rounded-2xl max-w-sm w-full p-8 text-center">
        <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Added to Cart!</h3>
        <p class="text-gray-600 mb-1" id="successPopupProductName"></p>
        <p class="text-sm text-gray-500 mb-6">
            <span id="successPopupCartCount">1</span> item(s) in cart
        </p>
        
        <div class="flex gap-3">
            <button onclick="closeSuccessPopup()" class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition font-semibold">
                Continue
            </button>
            <button onclick="goToCart()" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold">
                View Cart
            </button>
        </div>
    </div>
</div>

<script>
let currentProductId = null;
let currentProductName = '';
let currentQuantity = 1;
let productImages = [];
let currentImageIndex = 0;
let allProducts = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const productCards = document.querySelectorAll('.product-card');
    allProducts = Array.from(productCards);
    updateResultCount();
    updateCartCount(); // Initial cart count update
    
    // Search and filter listeners
    document.getElementById('searchInput')?.addEventListener('input', filterProducts);
    document.getElementById('categoryFilter')?.addEventListener('change', filterProducts);
    document.getElementById('sortFilter')?.addEventListener('change', filterProducts);
});

function filterProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const selectedCategory = document.getElementById('categoryFilter').value.toLowerCase();
    const sortBy = document.getElementById('sortFilter').value;
    
    let visibleProducts = allProducts.filter(card => {
        const productName = card.dataset.productName;
        const productCategory = card.dataset.productCategory;
        
        const matchesSearch = productName.includes(searchTerm);
        const matchesCategory = !selectedCategory || productCategory === selectedCategory;
        
        return matchesSearch && matchesCategory;
    });
    
    // Sort products
    visibleProducts.sort((a, b) => {
        switch(sortBy) {
            case 'newest':
                return new Date(b.dataset.createdAt) - new Date(a.dataset.createdAt);
            case 'oldest':
                return new Date(a.dataset.createdAt) - new Date(b.dataset.createdAt);
            case 'name-asc':
                return a.dataset.productName.localeCompare(b.dataset.productName);
            case 'name-desc':
                return b.dataset.productName.localeCompare(a.dataset.productName);
            default:
                return 0;
        }
    });
    
    // Hide all products first
    allProducts.forEach(card => card.classList.add('hidden'));
    
    // Show filtered and sorted products
    const grid = document.getElementById('productsGrid');
    visibleProducts.forEach(card => {
        card.classList.remove('hidden');
        grid.appendChild(card);
    });
    
    updateResultCount();
    
    // Show/hide no results message
    const noResults = document.getElementById('noResults');
    if (visibleProducts.length === 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
}

function updateResultCount() {
    const visibleCount = allProducts.filter(card => !card.classList.contains('hidden')).length;
    document.getElementById('resultCount').textContent = visibleCount;
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('sortFilter').value = 'newest';
    filterProducts();
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

function quickAddToCart(productId, productName) {
    // Get the button element that was clicked
    const button = event.target.closest('button');
    const productCard = button.closest('.product-card');
    const productImage = productCard.querySelector('img');
    
    // Create flying element
    createFlyingAnimation(productImage, button);
    
    // Add to cart
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update cart count
    updateCartCount();
    
    // Show success popup after animation
    setTimeout(() => {
        showSuccessPopup(productName, cart);
    }, 800);
}

function createFlyingAnimation(sourceElement, buttonElement) {
    // Get cart icon position
    const cartIcon = document.querySelector('.cart-icon') || document.querySelector('[href*="cart.php"]');
    if (!cartIcon) return;
    
    const cartRect = cartIcon.getBoundingClientRect();
    const sourceRect = sourceElement ? sourceElement.getBoundingClientRect() : buttonElement.getBoundingClientRect();
    
    // Calculate distance
    const dx = cartRect.left - sourceRect.left;
    const dy = cartRect.top - sourceRect.top;
    
    // Create flying element
    const flyingItem = document.createElement('div');
    flyingItem.className = 'flying-item';
    flyingItem.style.cssText = `
        left: ${sourceRect.left}px;
        top: ${sourceRect.top}px;
        width: ${sourceRect.width}px;
        height: ${sourceRect.height}px;
        --dx: ${dx}px;
        --dy: ${dy}px;
    `;
    
    // Clone the image or create cart icon
    if (sourceElement && sourceElement.tagName === 'IMG') {
        const clonedImg = sourceElement.cloneNode(true);
        clonedImg.style.width = '100%';
        clonedImg.style.height = '100%';
        clonedImg.style.objectFit = 'contain';
        clonedImg.style.borderRadius = '8px';
        clonedImg.style.backgroundColor = 'white';
        clonedImg.style.boxShadow = '0 4px 20px rgba(0,0,0,0.2)';
        flyingItem.appendChild(clonedImg);
    } else {
        flyingItem.innerHTML = `
            <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <svg style="width: 32px; height: 32px; color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        `;
    }
    
    document.body.appendChild(flyingItem);
    
    // Pulse cart icon
    if (cartIcon) {
        cartIcon.classList.add('cart-pulse-animation');
        setTimeout(() => {
            cartIcon.classList.remove('cart-pulse-animation');
        }, 600);
    }
    
    // Remove flying element after animation
    setTimeout(() => {
        flyingItem.remove();
    }, 1000);
}

function showSuccessPopup(productName, cart) {
    const popup = document.getElementById('addToCartSuccessPopup');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    document.getElementById('successPopupProductName').textContent = productName;
    document.getElementById('successPopupCartCount').textContent = totalItems;
    
    popup.classList.remove('hidden');
    
    // Auto close after 3 seconds
    setTimeout(() => {
        closeSuccessPopup();
    }, 3000);
}

function closeSuccessPopup() {
    const popup = document.getElementById('addToCartSuccessPopup');
    const content = popup.querySelector('.success-popup');
    
    content.classList.add('success-popup-hide');
    
    setTimeout(() => {
        popup.classList.add('hidden');
        content.classList.remove('success-popup-hide');
    }, 300);
}

function goToCart() {
    window.location.href = 'cart.php';
}

function openProductModal(productId) {
    currentProductId = productId;
    currentQuantity = 1;
    currentImageIndex = 0;
    
    fetch(`get-cart-products.php?ids=${productId}`)
        .then(res => res.json())
        .then(products => {
            const product = products[0];
            if (!product) return;
            
            currentProductName = product.name;
            productImages = product.images_array || [];
            
            if (productImages.length === 0 && product.image) {
                productImages = [product.image];
            }
            
            // Set main image
            updateMainImage();
            
            // Create thumbnails
            const thumbnailGallery = document.getElementById('thumbnailGallery');
            thumbnailGallery.innerHTML = productImages.map((img, index) => `
                <div class="thumbnail ${index === 0 ? 'active' : ''} rounded overflow-hidden aspect-square bg-gray-100 cursor-pointer" 
                     onclick="changeMainImage(${index})">
                    <img src="../${img}" class="w-full h-full object-cover">
                </div>
            `).join('');
            
            // Update image counter
            document.getElementById('totalImages').textContent = productImages.length;
            
            // Set product details
            document.getElementById('modalProductName').textContent = product.name;
            document.getElementById('modalProductCategory').textContent = product.category || 'Uncategorized';
            document.getElementById('modalProductDescription').textContent = product.description || 'No description available.';
            document.getElementById('modalQuantity').textContent = '1';
            
            // Show modal
            document.getElementById('productModal').classList.remove('hidden');
        });
}

function updateMainImage() {
    if (productImages.length > 0) {
        document.getElementById('mainImage').src = `../${productImages[currentImageIndex]}`;
        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;
        
        // Update thumbnail active state
        document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
            if (index === currentImageIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }
}

function changeMainImage(index) {
    currentImageIndex = index;
    updateMainImage();
}

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % productImages.length;
    updateMainImage();
}

function previousImage() {
    currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
    updateMainImage();
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
    currentProductId = null;
}

function modalUpdateQuantity(change) {
    currentQuantity = Math.max(1, currentQuantity + change);
    document.getElementById('modalQuantity').textContent = currentQuantity;
}

function addToCartFromModal() {
    if (currentProductId && currentProductName) {
        // Get modal image
        const modalImage = document.getElementById('mainImage');
        const addButton = event.target;
        
        // Create flying animation
        createFlyingAnimation(modalImage, addButton);
        
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingItem = cart.find(item => item.id === currentProductId);
        
        if (existingItem) {
            existingItem.quantity += currentQuantity;
        } else {
            cart.push({
                id: currentProductId,
                name: currentProductName,
                quantity: currentQuantity
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        closeModal();
        
        // Show success popup
        setTimeout(() => {
            showSuccessPopup(currentProductName, cart);
        }, 800);
    }
}

// Close modal on outside click
document.getElementById('productModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close popup when clicking outside
document.getElementById('addToCartSuccessPopup')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeSuccessPopup();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>