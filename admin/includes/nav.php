<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Admin Navigation -->
<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <h1 class="text-xl font-bold text-gray-900">ADMIN PANEL</h1>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="<?php echo $current_page == 'index' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?>">
                        Dashboard
                    </a>
                    <a href="products.php" class="<?php echo $current_page == 'products' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?>">
                        Products
                    </a>
                    <a href="requests.php" class="<?php echo $current_page == 'requests' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?>">
                        Requests
                    </a>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="text-gray-600 hidden md:inline"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden mt-4 flex space-x-4">
            <a href="index.php" class="<?php echo $current_page == 'index' ? 'text-blue-600 font-semibold' : 'text-gray-600'; ?>">
                Dashboard
            </a>
            <a href="products.php" class="<?php echo $current_page == 'products' ? 'text-blue-600 font-semibold' : 'text-gray-600'; ?>">
                Products
            </a>
            <a href="requests.php" class="<?php echo $current_page == 'requests' ? 'text-blue-600 font-semibold' : 'text-gray-600'; ?>">
                Requests
            </a>
        </div>
    </div>
</nav>