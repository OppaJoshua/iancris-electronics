<?php
session_start();
$page_title = "Products";
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/database.php';

// Get all categories (only from active products)
$categories_sql = "SELECT DISTINCT category FROM products WHERE status = 'active' AND category IS NOT NULL AND category != '' ORDER BY category";
$categories_result = $conn->query($categories_sql);

// Get products from database (only active products)
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

        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php while ($product = $result->fetch_assoc()): ?>
                <!-- Product Card -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col">
                    <!-- Product Image -->
                    <div class="aspect-square bg-gray-50 overflow-hidden relative group">
                        <?php if ($product['image']): ?>
                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Stock Badge -->
                        <?php if ($product['stock'] <= 0): ?>
                            <div class="absolute top-3 right-3 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                                Out of Stock
                            </div>
                        <?php elseif ($product['stock'] < 10): ?>
                            <div class="absolute top-3 right-3 bg-orange-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                                Low Stock
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4 flex-1 flex flex-col">
                        <!-- Category -->
                        <?php if ($product['category']): ?>
                            <span class="text-xs text-blue-600 font-medium uppercase tracking-wide mb-2">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Product Name -->
                        <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2 min-h-[3rem]">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        
                        <!-- Description -->
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2 flex-1">
                            <?php echo htmlspecialchars($product['description'] ?? 'Professional CCTV solution'); ?>
                        </p>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                            <!-- Learn More Button -->
                            <button onclick="openProductModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                                    class="flex-1 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm font-medium flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Learn More
                            </button>
                            
                            <!-- Add to Cart Icon Button -->
                            <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')" 
                                    <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>
                                    class="w-12 h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center justify-center <?php echo $product['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </button>
                        </div>
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
    </div>
</div>

<!-- Product Detail Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-2xl font-bold text-gray-900" id="modalTitle"></h3>
            <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="overflow-y-auto max-h-[calc(90vh-200px)] p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Image Gallery -->
                <div>
                    <!-- Main Image -->
                    <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden mb-3">
                        <img id="modalMainImage" src="" alt="" class="w-full h-full object-cover">
                    </div>
                    
                    <!-- Thumbnail Gallery -->
                    <div id="thumbnailGallery" class="grid grid-cols-4 gap-2">
                        <!-- Thumbnails will be inserted here -->
                    </div>
                </div>
                
                <!-- Details -->
                <div>
                    <span id="modalCategory" class="inline-block text-xs text-blue-600 font-medium uppercase tracking-wide mb-2"></span>
                    <h4 class="text-xl font-bold text-gray-900 mb-4" id="modalName"></h4>
                    <p class="text-gray-600 mb-6 leading-relaxed" id="modalDescription"></p>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Professional Installation Available</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-gray-700">Warranty Included</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="text-gray-700">24/7 Technical Support</span>
                        </div>
                    </div>
                    
                    <div id="modalStock" class="text-sm mb-6"></div>
                    
                    <button id="modalAddToCart" onclick="addToCartFromModal()" 
                            class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Add to Cart
                    </button>
                </div>
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
let currentProduct = null;
let currentImages = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount(); // Initial cart count update
    initializeFilters(); // Initialize filter functionality
    updateResultCount(); // Update initial count
});

// Filter and Search Functionality
function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', applyFilters);
    }
    
    if (sortFilter) {
        sortFilter.addEventListener('change', applyFilters);
    }
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    const selectedCategory = document.getElementById('categoryFilter').value.toLowerCase().trim();
    const sortBy = document.getElementById('sortFilter').value;
    
    // Get all product cards - more specific selector
    const productGrid = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-4');
    if (!productGrid) return;
    
    const productCards = Array.from(productGrid.querySelectorAll(':scope > .bg-white.rounded-lg.border'));
    
    let visibleCount = 0;
    
    // Filter products
    productCards.forEach(card => {
        // Get product data from the card
        const nameElement = card.querySelector('h3');
        const categoryElement = card.querySelector('.text-blue-600');
        const descriptionElement = card.querySelector('.text-gray-600.line-clamp-2');
        
        const productName = nameElement ? nameElement.textContent.toLowerCase() : '';
        const productCategory = categoryElement ? categoryElement.textContent.toLowerCase() : '';
        const productDescription = descriptionElement ? descriptionElement.textContent.toLowerCase() : '';
        
        // Check if product matches filters
        const matchesSearch = !searchTerm || 
                             productName.includes(searchTerm) || 
                             productDescription.includes(searchTerm);
        const matchesCategory = !selectedCategory || productCategory === selectedCategory;
        
        // Show or hide card
        if (matchesSearch && matchesCategory) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Sort visible products
    sortProducts(productCards, sortBy);
    
    // Update result count
    updateResultCount(visibleCount);
    
    // Show/hide no results message
    toggleNoResults(visibleCount);
}

function sortProducts(productCards, sortBy) {
    const productGrid = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-4');
    if (!productGrid) return;
    
    // Create a copy of all cards to sort
    const allCards = Array.from(productCards);
    
    allCards.sort((a, b) => {
        const nameA = a.querySelector('h3')?.textContent || '';
        const nameB = b.querySelector('h3')?.textContent || '';
        
        switch(sortBy) {
            case 'name-asc':
                return nameA.localeCompare(nameB);
            case 'name-desc':
                return nameB.localeCompare(nameA);
            case 'oldest':
                // Reverse order - return 1 to move b before a
                return 1;
            case 'newest':
            default:
                // Keep original order - return -1 to keep a before b
                return -1;
        }
    });
    
    // Clear the grid
    productGrid.innerHTML = '';
    
    // Re-append all sorted cards
    allCards.forEach(card => {
        productGrid.appendChild(card);
    });
}

function updateResultCount(count) {
    const resultCount = document.getElementById('resultCount');
    if (resultCount) {
        if (count !== undefined) {
            resultCount.textContent = count;
        } else {
            // Count visible products with more specific selector
            const productGrid = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-4');
            if (productGrid) {
                const visibleCards = productGrid.querySelectorAll(':scope > .bg-white.rounded-lg.border:not([style*="display: none"])');
                resultCount.textContent = visibleCards.length;
            } else {
                resultCount.textContent = 0;
            }
        }
    }
}

function toggleNoResults(visibleCount) {
    const noResults = document.getElementById('noResults');
    const productGrid = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-4');
    
    if (noResults && productGrid) {
        if (visibleCount === 0) {
            productGrid.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            productGrid.classList.remove('hidden');
            noResults.classList.add('hidden');
        }
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('sortFilter').value = 'newest';
    applyFilters();
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    const cartBadge = document.getElementById('cart-count');
    const cartBadgeMobile = document.getElementById('cart-count-mobile');
    
    if (cartBadge) {
        if (totalItems > 0) {
            cartBadge.textContent = totalItems;
            cartBadge.classList.remove('hidden');
        } else {
            cartBadge.classList.add('hidden');
        }
    }
    
    if (cartBadgeMobile) {
        if (totalItems > 0) {
            cartBadgeMobile.textContent = totalItems;
            cartBadgeMobile.classList.remove('hidden');
        } else {
            cartBadgeMobile.classList.add('hidden');
        }
    }
}

function addToCart(productId, productName) {
    // Get the button element that was clicked
    const button = event.currentTarget;
    
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
    
    // Create flying animation
    createFlyingAnimation(button);
    
    // Show success popup after animation
    setTimeout(() => {
        showSuccessPopup(productName, cart);
    }, 800);
}

function createFlyingAnimation(buttonElement) {
    // Get cart icon position
    const cartIcon = document.querySelector('.cart-icon');
    if (!cartIcon) return;
    
    const cartRect = cartIcon.getBoundingClientRect();
    const buttonRect = buttonElement.getBoundingClientRect();
    
    // Calculate distance
    const dx = cartRect.left - buttonRect.left;
    const dy = cartRect.top - buttonRect.top;
    
    // Create flying element
    const flyingItem = document.createElement('div');
    flyingItem.className = 'flying-item';
    flyingItem.style.cssText = `
        left: ${buttonRect.left}px;
        top: ${buttonRect.top}px;
        width: 60px;
        height: 60px;
        --dx: ${dx}px;
        --dy: ${dy}px;
    `;
    
    flyingItem.innerHTML = `
        <div style="width: 100%; height: 100%; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            <svg style="width: 32px; height: 32px; color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
    `;
    
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

function openProductModal(product) {
    currentProduct = product;
    
    // Set modal content
    document.getElementById('modalTitle').textContent = product.name;
    document.getElementById('modalName').textContent = product.name;
    document.getElementById('modalCategory').textContent = product.category || 'CCTV';
    document.getElementById('modalDescription').textContent = product.description || 'Professional CCTV solution for your security needs.';
    
    // Parse and display images
    currentImages = [];
    
    // Parse additional images from the images field (stored as JSON array)
    if (product.images) {
        try {
            const additionalImages = JSON.parse(product.images);
            if (Array.isArray(additionalImages)) {
                additionalImages.forEach(img => {
                    if (img && img.trim()) {
                        currentImages.push('../' + img);
                    }
                });
            }
        } catch (e) {
            console.log('Error parsing images JSON:', e);
        }
    }
    
    // If no images from JSON, fallback to main image only
    if (currentImages.length === 0 && product.image) {
        currentImages.push('../' + product.image);
    }
    
    // Display images
    if (currentImages.length > 0) {
        // Set main image
        const modalMainImage = document.getElementById('modalMainImage');
        modalMainImage.src = currentImages[0];
        modalMainImage.alt = product.name;
        modalMainImage.style.display = 'block';
        
        // Create thumbnails only if there's more than 1 image
        const thumbnailGallery = document.getElementById('thumbnailGallery');
        if (currentImages.length > 1) {
            thumbnailGallery.innerHTML = currentImages.map((imgSrc, index) => `
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 ${index === 0 ? 'border-blue-600' : 'border-transparent'} hover:border-blue-400 transition" 
                     onclick="changeMainImage(${index})">
                    <img src="${imgSrc}" alt="${product.name} ${index + 1}" class="w-full h-full object-cover">
                </div>
            `).join('');
        } else {
            thumbnailGallery.innerHTML = '';
        }
    } else {
        // Show placeholder if no images
        const modalMainImage = document.getElementById('modalMainImage');
        modalMainImage.style.display = 'none';
        document.getElementById('thumbnailGallery').innerHTML = '';
    }
    
    // Stock status
    const stockEl = document.getElementById('modalStock');
    const addBtn = document.getElementById('modalAddToCart');
    
    if (product.stock <= 0) {
        stockEl.innerHTML = '<span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full font-medium">Out of Stock</span>';
        addBtn.disabled = true;
        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else if (product.stock < 10) {
        stockEl.innerHTML = '<span class="inline-block bg-orange-100 text-orange-800 px-3 py-1 rounded-full font-medium">Low Stock - Only ' + product.stock + ' left</span>';
        addBtn.disabled = false;
        addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        stockEl.innerHTML = '<span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full font-medium">In Stock</span>';
        addBtn.disabled = false;
        addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    // Show modal
    document.getElementById('productModal').classList.remove('hidden');
}

function changeMainImage(index) {
    if (index >= 0 && index < currentImages.length) {
        // Update main image
        document.getElementById('modalMainImage').src = currentImages[index];
        
        // Update active thumbnail border
        const thumbnails = document.querySelectorAll('#thumbnailGallery > div');
        thumbnails.forEach((thumb, i) => {
            if (i === index) {
                thumb.classList.remove('border-transparent');
                thumb.classList.add('border-blue-600');
            } else {
                thumb.classList.remove('border-blue-600');
                thumb.classList.add('border-transparent');
            }
        });
    }
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
    currentProduct = null;
    currentImages = [];
    
    // Clear images
    document.getElementById('modalMainImage').src = '';
    document.getElementById('thumbnailGallery').innerHTML = '';
}

function addToCartFromModal() {
    if (currentProduct) {
        addToCart(currentProduct.id, currentProduct.name);
        closeProductModal();
    }
}

// Close modal on outside click
document.getElementById('productModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeProductModal();
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