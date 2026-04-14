<?php
// index.php - Home Page
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get categories
$categories = getCategories($conn);

// Get featured products
$featured_products = getFeaturedProducts($conn, 8);

// Get new arrivals
$new_arrivals = getNewArrivals($conn, 8);

// Get best sellers
$best_sellers = getBestSellers($conn, 8);

$page_title = "Home - Ladies Fashion Store";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
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
                            <input type="text" name="search" class="search-input" placeholder="Search for products...">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Header Icons -->
                    <div class="header-icons">
                        <?php if(isLoggedIn()): ?>
                            <a href="my-account.php" class="icon-link">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="icon-link">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="cart.php" class="icon-link">
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

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="container">
            <div class="hero-content">
                <h1>New Collection 2024</h1>
                <p>Discover the latest trends in women's fashion</p>
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Shop by Category</h2>
                <p>Explore our stunning collection</p>
            </div>
            
            <div class="categories-grid">
                <?php 
                $category_icons = [
                    'Dresses' => 'fa-dress',
                    'Tops' => 'fa-shirt',
                    'Shoes' => 'fa-shoe-prints',
                    'Handbags' => 'fa-bag-shopping',
                    'Accessories' => 'fa-gem',
                    'Beauty' => 'fa-wand-magic-sparkles'
                ];
                
                foreach($categories as $category): 
                    $icon = isset($category_icons[$category['name']]) ? $category_icons[$category['name']] : 'fa-star';
                ?>
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                        <div class="category-icon">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <?php if(!empty($featured_products)): ?>
    <section class="section" style="background: #fff;">
        <div class="container">
            <div class="section-title">
                <h2>Featured Products</h2>
                <p>Handpicked favorites just for you</p>
            </div>
            
            <div class="products-grid">
                <?php foreach($featured_products as $product): 
                    $image = getPrimaryImage($conn, $product['id']);
                    $discount = $product['discount_price'] ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <?php if($discount > 0): ?>
                                <span class="product-badge"><?php echo $discount; ?>% OFF</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?></p>
                            <h3 class="product-name">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <?php if($product['discount_price']): ?>
                                    <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                    <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm add-to-cart-btn">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- New Arrivals -->
    <?php if(!empty($new_arrivals)): ?>
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>New Arrivals</h2>
                <p>Fresh styles just landed</p>
            </div>
            
            <div class="products-grid">
                <?php foreach($new_arrivals as $product): 
                    $image = getPrimaryImage($conn, $product['id']);
                    $discount = $product['discount_price'] ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <span class="product-badge new">NEW</span>
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?></p>
                            <h3 class="product-name">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <?php if($product['discount_price']): ?>
                                    <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                    <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm add-to-cart-btn">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Best Sellers -->
    <?php if(!empty($best_sellers)): ?>
    <section class="section" style="background: #fff;">
        <div class="container">
            <div class="section-title">
                <h2>Best Sellers</h2>
                <p>Customer favorites</p>
            </div>
            
            <div class="products-grid">
                <?php foreach($best_sellers as $product): 
                    $image = getPrimaryImage($conn, $product['id']);
                    $discount = $product['discount_price'] ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <?php if($discount > 0): ?>
                                <span class="product-badge"><?php echo $discount; ?>% OFF</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?></p>
                            <h3 class="product-name">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <?php if($product['discount_price']): ?>
                                    <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                    <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm add-to-cart-btn">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>Ladies Fashion Store offers the finest selection of women's clothing, shoes, accessories, and beauty products.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="products.php">Shop All</a></li>
                        <li><a href="products.php?category=1">Dresses</a></li>
                        <li><a href="products.php?category=2">Tops</a></li>
                        <li><a href="products.php?category=3">Shoes</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="#">Shipping Information</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                        <li><a href="#">Size Guide</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> <?php echo getSetting('store_phone', $conn); ?></li>
                        <li><i class="fas fa-envelope"></i> <?php echo getSetting('store_email', $conn); ?></li>
                        <li><i class="fas fa-map-marker-alt"></i> Accra, Ghana</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Ladies Fashion Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>