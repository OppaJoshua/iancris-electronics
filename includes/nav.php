<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine base path for links
$is_in_pages = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$is_in_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base_path = $is_in_pages ? '../' : ($is_in_admin ? '../' : '');

// Check if we're on the home page
$is_home_page = basename($_SERVER['PHP_SELF']) === 'index.php' && !$is_in_pages && !$is_in_admin;

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Check if user is admin
$is_admin = false;
if ($is_logged_in && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $is_admin = true;
}

// Get user's first name if logged in
$user_first_name = '';
$user_photo = '';
$user_email = '';
$pending_orders = 0;
if ($is_logged_in && isset($_SESSION['user_name'])) {
    $user_first_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'] ?? '';
    
    // Try to get Google profile picture first
    if (isset($_SESSION['google_picture'])) {
        $user_photo = $_SESSION['google_picture'];
    } else {
        $user_photo = $_SESSION['user_photo'] ?? '';
    }
    
    // Get pending orders count
    require_once $base_path . 'config/database.php';
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status IN ('pending', 'confirmed', 'scheduled')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pending_orders = $row['count'];
}

// If user is logged in, check if they're blocked
if ($is_logged_in && isset($_SESSION['user_id'])) {
    // Use the same base_path logic for consistency
    $db_path = __DIR__ . '/../config/database.php';
    if (file_exists($db_path)) {
        require_once $db_path;
        $check_stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
        $check_stmt->bind_param("i", $_SESSION['user_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $user_data = $check_result->fetch_assoc();
            if (isset($user_data['status']) && $user_data['status'] === 'blocked') {
                // User is blocked, destroy session and redirect
                session_destroy();
                header("Location: " . $base_path . "pages/login.php?blocked=1");
                exit();
            }
        }
    }
}

// Get initials for fallback
function getInitials($name) {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}
?>

<!-- Navigation -->
<nav class="fixed top-0 left-0 right-0 z-50 px-4 sm:px-8 py-6 <?php echo $is_home_page ? 'bg-transparent' : 'bg-white shadow-sm'; ?>" id="main-nav">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="text-lg sm:text-xl font-bold text-gray-900">
            IANKRIS ELECTRONICS
        </div>
        
        <!-- Desktop Navigation -->
        <div class="text-sm sm:text-base hidden md:flex items-center space-x-8">
            <a href="<?php echo $base_path; ?>index.php" class="text-gray-700 hover:text-blue-600 transition">Home</a>
            
            <!-- Products Dropdown -->
            <div class="relative group">
                <button class="text-gray-700 hover:text-blue-600 transition flex items-center gap-1">
                    Products
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="py-2">
                        <a href="<?php echo $base_path; ?>pages/products.php" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <div>
                                    <div class="font-medium">CCTV Products</div>
                                    <div class="text-xs text-gray-500">Browse catalog</div>
                                </div>
                            </div>
                        </a>
                        
                        <a href="<?php echo $base_path; ?>pages/3d-videos.php" class="block px-4 py-3 text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-blue-50 hover:text-purple-600 transition">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <div class="font-medium">3D Product Videos</div>
                                    <div class="text-xs text-gray-500">View demos</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <a href="<?php echo $base_path; ?>pages/gallery.php" class="text-gray-700 hover:text-blue-600 transition">Gallery</a>
            <a href="<?php echo $base_path; ?>pages/about.php" class="text-gray-700 hover:text-blue-600 transition">About</a>
            
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $base_path; ?>pages/dashboard.php" class="text-gray-700 hover:text-blue-600 transition">Dashboard</a>
            <?php endif; ?>
        </div>
        
        <!-- Right Side -->
        <div class="hidden md:flex items-center space-x-4">
            <!-- Admin Panel Button (only for admins) -->
            <?php if ($is_admin): ?>
                <a href="<?php echo $base_path; ?>admin/index.php" class="flex items-center space-x-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white px-4 py-2 rounded-lg hover:from-purple-700 hover:to-purple-800 transition font-medium text-sm shadow-md hover:shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Admin Panel</span>
                </a>
            <?php endif; ?>
            
            <!-- Cart Icon -->
            <a href="<?php echo $base_path; ?>pages/cart.php" class="relative">
                <svg class="cart-icon w-6 h-6 text-gray-900 cursor-pointer hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span id="cart-count" class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden font-bold">0</span>
            </a>

            <?php if ($is_logged_in): ?>
                <div class="relative" id="user-menu">
                    <button onclick="toggleUserMenu()" class="flex items-center space-x-2 hover:opacity-80 transition focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full">
                        <?php if (!empty($user_photo)): ?>
                            <img src="<?php echo htmlspecialchars($user_photo); ?>" 
                                 alt="<?php echo htmlspecialchars($user_first_name); ?>" 
                                 class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 hover:border-blue-500 transition"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm border-2 border-gray-200 hover:border-blue-500 transition" style="display: none;">
                                <?php echo htmlspecialchars(getInitials($user_first_name)); ?>
                            </div>
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm border-2 border-gray-200 hover:border-blue-500 transition">
                                <?php echo htmlspecialchars(getInitials($user_first_name)); ?>
                            </div>
                        <?php endif; ?>
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center space-x-3">
                                <?php if (!empty($user_photo)): ?>
                                    <img src="<?php echo htmlspecialchars($user_photo); ?>" 
                                         alt="<?php echo htmlspecialchars($user_first_name); ?>" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm" style="display: none;">
                                        <?php echo htmlspecialchars(getInitials($user_first_name)); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                        <?php echo htmlspecialchars(getInitials($user_first_name)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($user_first_name); ?></p>
                                    <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($user_email); ?></p>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $base_path; ?>logout.php" class="block px-4 py-2 text-gray-900 hover:bg-gray-100 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>pages/login.php" class="text-gray-900 font-semibold hover:text-blue-600 transition">LOGIN</a>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Button & Cart -->
        <div class="flex md:hidden items-center space-x-4">
            <a href="<?php echo $base_path; ?>pages/cart.php" class="relative">
                <svg class="cart-icon w-6 h-6 text-gray-900 cursor-pointer hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span id="cart-count-mobile" class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden font-bold">0</span>
            </a>
            <button class="hamburger" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu md:hidden mt-4 bg-white rounded-lg" id="mobileMenu">
        <a href="<?php echo $base_path; ?>index.php" class="block py-2 text-gray-700 hover:text-blue-600">HOME</a>
        <a href="<?php echo $base_path; ?>pages/products.php" class="block py-2 text-gray-700 hover:text-blue-600">PRODUCTS</a>
        <a href="<?php echo $base_path; ?>pages/gallery.php" class="block py-2 text-gray-700 hover:text-blue-600">GALLERY</a>
        <a href="<?php echo $base_path; ?>pages/about.php" class="block py-2 text-gray-700 hover:text-blue-600">ABOUT</a>
        
        <?php if ($is_logged_in): ?>
            <?php if ($is_admin): ?>
                <!-- Admin Panel Link for Mobile -->
                <a href="<?php echo $base_path; ?>admin/index.php" class="block py-3 px-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg mt-2 mb-2 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>ADMIN PANEL</span>
                </a>
            <?php endif; ?>
            <a href="<?php echo $base_path; ?>pages/dashboard.php" class="block py-2 text-gray-700 hover:text-blue-600">DASHBOARD</a>
            <a href="<?php echo $base_path; ?>logout.php" class="block py-2 text-red-600 hover:text-red-700">LOGOUT</a>
        <?php else: ?>
            <a href="<?php echo $base_path; ?>pages/login.php" class="block py-2 text-blue-600 font-medium">LOGIN</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Add padding to prevent content from going under fixed nav -->
<div class="h-20"></div>

<!-- Shared logout script -->
<script type="module">
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
    import { getAuth, signOut } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';
    
    const firebaseConfig = {
        apiKey: "AIzaSyCs39YGAPOkhVn7OughUIm-R1gfpINffBw",
        authDomain: "iancris-electronics.firebaseapp.com",
        projectId: "iancris-electronics",
        storageBucket: "iancris-electronics.firebasestorage.app",
        messagingSenderId: "1023774984228",
        appId: "1:1023774984228:web:73cda64515649dd2fb30b1",
        measurementId: "G-ZKCL13RTFD"
    };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    
    // Logout function
    window.logout = async function() {
        if (!confirm('Are you sure you want to logout?')) {
            return;
        }
        
        try {
            console.log('Logging out...');
            await signOut(auth);
            
            const response = await fetch('/iancris-electronics/api/auth/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            console.log('Logout response:', data);
            
            window.location.href = '/iancris-electronics/index.php';
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = '/iancris-electronics/index.php';
        }
    };
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        });
    }

    // User menu toggle
    window.toggleUserMenu = function() {
        const dropdown = document.getElementById('user-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.getElementById('user-menu');
        const dropdown = document.getElementById('user-dropdown');
        if (userMenu && dropdown && !userMenu.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Update cart count
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        
        const cartBadge = document.getElementById('cart-count');
        const cartBadgeMobile = document.getElementById('cart-count-mobile');
        
        if (totalItems > 0) {
            if (cartBadge) {
                cartBadge.textContent = totalItems;
                cartBadge.classList.remove('hidden');
            }
            if (cartBadgeMobile) {
                cartBadgeMobile.textContent = totalItems;
                cartBadgeMobile.classList.remove('hidden');
            }
        } else {
            if (cartBadge) cartBadge.classList.add('hidden');
            if (cartBadgeMobile) cartBadgeMobile.classList.add('hidden');
        }
    }
    
    document.addEventListener('DOMContentLoaded', updateCartCount);
    
    // Listen for cart updates from other tabs/windows
    window.addEventListener('storage', function(e) {
        if (e.key === 'cart') {
            updateCartCount();
        }
    });
</script>