<?php
// ========================================
// FILE: products.php - Product Listing Page
// ========================================
?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'latest';

// Get all categories for filter
$categories = getCategories($conn);

// Search products
$products = searchProducts($conn, $search, $category_id, $min_price, $max_price, $sort);

$page_title = "Shop Products - Ladies Fashion Store";
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
    <?php include 'includes/header.php'; ?>

    <section class="section">
        <div class="container">
            <h1 class="section-title" style="margin-bottom: 30px;">Shop Our Collection</h1>
            
            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" action="products.php" class="filters-row">
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Min Price</label>
                        <input type="number" name="min_price" placeholder="0" value="<?php echo $min_price; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Max Price</label>
                        <input type="number" name="max_price" placeholder="1000" value="<?php echo $max_price; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select name="sort">
                            <option value="latest" <?php echo ($sort == 'latest') ? 'selected' : ''; ?>>Latest</option>
                            <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo ($sort == 'name') ? 'selected' : ''; ?>>Name</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
                    </div>
                </form>
            </div>
            
            <!-- Products Grid -->
            <?php if(count($products) > 0): ?>
                <div class="products-grid">
                    <?php foreach($products as $product): 
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
                                <?php elseif($product['new_arrival']): ?>
                                    <span class="product-badge new">NEW</span>
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
            <?php else: ?>
                <div class="alert alert-info" style="text-align: center; margin-top: 40px;">
                    <i class="fas fa-info-circle"></i> No products found matching your criteria.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

