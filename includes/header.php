<?php
// ========================================
// FILE: includes/header.php - Reusable Header
// ========================================
?>
<?php
// Make sure config and functions are loaded
if(!isset($conn)) {
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
}

// Get categories for menu
$categories = getCategories($conn);
?>
<!-- Header -->
<header class="header">
    <!-- Top Bar -->
    <div class="header-top">
        <div class="container">
            <p>✨ Free Shipping on Orders Over GHS 200 | Shop Now & Save!</p>
        </div>
    </div>
    
    <!-- Main Header -->
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="index.php" class="logo">
                    Chris_<span>Luxury Wear</span>
                </a>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <form action="products.php" method="GET" class="search-form">
                        <input type="text" name="search" class="search-input" placeholder="Search for products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Header Icons -->
                <div class="header-icons">
                    <?php if(isLoggedIn()): ?>
                        <a href="my-account.php" class="icon-link" title="My Account">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="icon-link" title="Login">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php endif; ?>
                    
                    <a href="cart.php" class="icon-link" title="Shopping Cart">
                        <i class="fas fa-shopping-bag"></i>
                        <?php if(getCartCount() > 0): ?>
                            <span class="cart-count"><?php echo getCartCount(); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Shop All</a></li>
                <?php foreach($categories as $category): ?>
                    <li><a href="products.php?category=<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</header>