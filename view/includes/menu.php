<!-- Navigation Menu Tray -->
<div class="menu-tray">
    <button class="menu-toggle" onclick="toggleMenu()"><i class="fas fa-bars"></i> <span class="menu-text">Menu</span></button>
    <div class="menu-items" id="menuItems">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        $currentDir = basename(dirname($_SERVER['PHP_SELF']));
        
        // Detect the directory we're in and set appropriate prefixes
        if ($currentDir === 'htdocs' || $currentPage === 'index.php') {
            // Root level (index.php)
            $homePrefix = '';
            $viewPrefix = 'view/';
            $loginPrefix = 'login/';
            $adminPrefix = 'admin/';
        } elseif ($currentDir === 'view') {
            // In view folder
            $homePrefix = '../';
            $viewPrefix = '';
            $loginPrefix = '../login/';
            $adminPrefix = '../admin/';
        } elseif ($currentDir === 'actions') {
            // In actions folder
            $homePrefix = '../';
            $viewPrefix = '../view/';
            $loginPrefix = '../login/';
            $adminPrefix = '../admin/';
        } elseif ($currentDir === 'admin') {
            // In admin folder
            $homePrefix = '../';
            $viewPrefix = '../view/';
            $loginPrefix = '../login/';
            $adminPrefix = '';
        } elseif ($currentDir === 'login') {
            // In login folder
            $homePrefix = '../';
            $viewPrefix = '../view/';
            $loginPrefix = '';
            $adminPrefix = '../admin/';
        } else {
            // Default fallback
            $homePrefix = '../';
            $viewPrefix = '../view/';
            $loginPrefix = '../login/';
            $adminPrefix = '../admin/';
        }
        
        $userRole = $_SESSION['user_role'] ?? 1;
        ?>
        
        <a href="<?= $homePrefix ?>index.php" class="btn btn-sm <?= $currentPage === 'index.php' ? 'btn-primary' : 'btn-outline-secondary' ?>">Home</a>
        <a href="<?= $viewPrefix ?>all_product.php" class="btn btn-sm <?= $currentPage === 'all_product.php' ? 'btn-primary' : 'btn-outline-secondary' ?>">All Products</a>
        <?php if (isLoggedIn() && isSeller()): ?>
            <a href="<?= $viewPrefix ?>pricing.php" class="btn btn-sm <?= $currentPage === 'pricing.php' ? 'btn-primary' : 'btn-outline-secondary' ?>">Pricing</a>
        <?php endif; ?>
        <a href="<?= $viewPrefix ?>cart.php" class="btn btn-sm btn-outline-secondary">
            Cart <?php if (isset($cartCount) && $cartCount > 0): ?><span class="cart-badge" id="cart-count"><?= $cartCount ?></span><?php endif; ?>
        </a>
        
        <?php if (isLoggedIn()): ?>
            <?php if ($userRole == 2): ?>
                <!-- Admin Section with Dropdown -->
                <div class="dropdown-menu-container">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" onclick="toggleDropdown(event)">
                        <i class="fas fa-cog"></i> Admin
                    </button>
                    <div class="dropdown-content">
                        <a href="<?= $adminPrefix ?>category.php"><i class="fas fa-tags"></i> Categories</a>
                        <a href="<?= $adminPrefix ?>brand.php"><i class="fas fa-copyright"></i> Brands</a>
                        <a href="<?= $adminPrefix ?>product.php"><i class="fas fa-box"></i> Products</a>
                        <a href="<?= $adminPrefix ?>orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
                    </div>
                </div>
                <a href="<?= $viewPrefix ?>dashboard.php" class="btn btn-sm btn-outline-secondary">My Account</a>
            <?php elseif ($userRole == 3 || $userRole == 4): ?>
                <a href="<?= $viewPrefix ?>seller_dashboard.php" class="btn btn-sm btn-outline-secondary">Seller Dashboard</a>
            <?php else: ?>
                <a href="<?= $viewPrefix ?>dashboard.php" class="btn btn-sm btn-outline-secondary">Dashboard</a>
            <?php endif; ?>
            <a href="<?= $loginPrefix ?>logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
        <?php else: ?>
            <a href="<?= $loginPrefix ?>login.php" class="btn btn-sm btn-outline-secondary">Login</a>
            <a href="<?= $loginPrefix ?>register.php" class="btn btn-sm btn-outline-primary">Register</a>
        <?php endif; ?>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<script>
    function toggleMenu() {
        const menuItems = document.getElementById('menuItems');
        const menuToggle = document.querySelector('.menu-toggle');
        menuItems.classList.toggle('active');
        
        // Update button content
        if (menuItems.classList.contains('active')) {
            menuToggle.innerHTML = '<i class="fas fa-times"></i> <span class="menu-text">Close</span>';
        } else {
            menuToggle.innerHTML = '<i class="fas fa-bars"></i> <span class="menu-text">Menu</span>';
        }
    }
    
    function toggleDropdown(event) {
        event.stopPropagation();
        const dropdown = event.target.nextElementSibling;
        dropdown.classList.toggle('show');
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const menuTray = document.querySelector('.menu-tray');
        const menuToggle = document.querySelector('.menu-toggle');
        
        // Close main menu
        if (menuTray && !menuTray.contains(event.target)) {
            const menuItems = document.getElementById('menuItems');
            if (menuItems && menuItems.classList.contains('active')) {
                menuItems.classList.remove('active');
                if (menuToggle) {
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i> <span class="menu-text">Menu</span>';
                }
            }
        }
        
        // Close all dropdowns
        const dropdowns = document.querySelectorAll('.dropdown-content');
        dropdowns.forEach(dropdown => {
            if (!dropdown.previousElementSibling.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    });
</script>
