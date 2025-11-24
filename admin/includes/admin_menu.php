<!-- Navigation Menu Tray -->
<div class="menu-tray">
    <button class="menu-toggle" onclick="toggleMenu()"><i class="fas fa-bars"></i> <span class="menu-text">Menu</span></button>
    <div class="menu-items" id="menuItems">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        ?>
        
        <a href="../index.php" class="btn btn-sm btn-outline-secondary">Home</a>
        <a href="../view/all_product.php" class="btn btn-sm btn-outline-secondary">All Products</a>
        
        <!-- Admin Section Separator -->
        <div style="border-left: 2px solid var(--primary); padding-left: 12px; margin-left: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
            <span class="badge" style="background: var(--primary); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; align-self: center;">ADMIN</span>
            <a href="category.php" class="btn btn-sm <?= $currentPage === 'category.php' ? 'btn-primary' : 'btn-outline-primary' ?>"><i class="fas fa-tags"></i> Categories</a>
            <a href="brand.php" class="btn btn-sm <?= $currentPage === 'brand.php' ? 'btn-primary' : 'btn-outline-primary' ?>"><i class="fas fa-copyright"></i> Brands</a>
            <a href="product.php" class="btn btn-sm <?= $currentPage === 'product.php' ? 'btn-primary' : 'btn-outline-primary' ?>"><i class="fas fa-box"></i> Products</a>
            <a href="orders.php" class="btn btn-sm <?= $currentPage === 'orders.php' ? 'btn-primary' : 'btn-outline-primary' ?>"><i class="fas fa-shopping-bag"></i> Orders</a>
        </div>
        
        <a href="../view/profile.php" class="btn btn-sm btn-outline-secondary">Profile</a>
        <a href="../login/logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
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
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const menuTray = document.querySelector('.menu-tray');
        const menuToggle = document.querySelector('.menu-toggle');
        if (menuTray && !menuTray.contains(event.target)) {
            const menuItems = document.getElementById('menuItems');
            if (menuItems && menuItems.classList.contains('active')) {
                menuItems.classList.remove('active');
                if (menuToggle) {
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i> <span class="menu-text">Menu</span>';
                }
            }
        }
    });
</script>
