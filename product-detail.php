<?php
// ========================================
// FILE: product-detail.php - Single Product Page
// ========================================
?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($product_id == 0) {
    redirect('products.php');
}

// Get product details
$product = getProduct($conn, $product_id);

if(!$product) {
    redirect('products.php');
}

// Get product images
$images = getProductImages($conn, $product_id);

// Get related products
$related_products = getRelatedProducts($conn, $product_id, $product['category_id'], 4);

// Parse sizes and colors
$sizes = !empty($product['sizes']) ? explode(',', $product['sizes']) : [];
$colors = !empty($product['colors']) ? explode(',', $product['colors']) : [];

// Handle Add to Cart
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? sanitize($_POST['size']) : '';
    $color = isset($_POST['color']) ? sanitize($_POST['color']) : '';
    
    if($quantity > 0 && $quantity <= $product['stock']) {
        addToCart($product_id, $quantity, $size, $color);
        $success_message = "Product added to cart successfully!";
    } else {
        $error_message = "Invalid quantity selected.";
    }
}

$page_title = htmlspecialchars($product['name']) . " - Ladies Fashion Store";
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
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    <a href="cart.php" style="float: right; color: inherit; font-weight: bold;">View Cart →</a>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="product-detail">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image" id="mainImage">
                        <?php 
                        $main_image = !empty($images) ? $images[0]['image_path'] : getPrimaryImage($conn, $product_id);
                        ?>
                        <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <?php if(count($images) > 1): ?>
                        <div class="thumbnail-images">
                            <?php foreach($images as $index => $img): ?>
                                <div class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" onclick="changeImage('<?php echo htmlspecialchars($img['image_path']); ?>', this)">
                                    <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Details -->
                <div class="product-details">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span>(4.5 / 5)</span>
                    </div>
                    
                    <div class="product-price-detail">
                        <?php if($product['discount_price']): ?>
                            <span class="price-detail-current"><?php echo formatPrice($product['discount_price']); ?></span>
                            <span class="price-detail-original"><?php echo formatPrice($product['price']); ?></span>
                            <?php 
                            $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                            ?>
                            <span class="product-badge" style="margin-left: 10px;"><?php echo $discount_percent; ?>% OFF</span>
                        <?php else: ?>
                            <span class="price-detail-current"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="product-options">
                            <!-- Size Selection -->
                            <?php if(!empty($sizes)): ?>
                                <div class="option-group">
                                    <label>Select Size:</label>
                                    <div class="option-buttons">
                                        <?php foreach($sizes as $size): ?>
                                            <label class="option-btn">
                                                <input type="radio" name="size" value="<?php echo trim($size); ?>" required style="display: none;">
                                                <?php echo trim($size); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Color Selection -->
                            <?php if(!empty($colors)): ?>
                                <div class="option-group">
                                    <label>Select Color:</label>
                                    <div class="option-buttons">
                                        <?php foreach($colors as $color): ?>
                                            <label class="option-btn">
                                                <input type="radio" name="color" value="<?php echo trim($color); ?>" required style="display: none;">
                                                <?php echo trim($color); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Quantity -->
                            <div class="quantity-selector">
                                <label>Quantity:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn" onclick="decreaseQty()">−</button>
                                    <input type="number" name="quantity" id="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                                </div>
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php if($product['stock'] > 0): ?>
                                    <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i> Out of Stock
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($product['stock'] > 0): ?>
                            <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px; margin-top: 20px;">
                                <i class="fas fa-shopping-bag"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline" disabled style="width: 100%; padding: 15px; font-size: 18px; margin-top: 20px;">
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if(!empty($related_products)): ?>
                <div style="margin-top: 60px;">
                    <div class="section-title">
                        <h2>Related Products</h2>
                        <p>You may also like</p>
                    </div>
                    
                    <div class="products-grid">
                        <?php foreach($related_products as $related): 
                            $rel_image = getPrimaryImage($conn, $related['id']);
                        ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>">
                                        <img src="<?php echo htmlspecialchars($rel_image); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                    </a>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name">
                                        <a href="product-detail.php?id=<?php echo $related['id']; ?>">
                                            <?php echo htmlspecialchars($related['name']); ?>
                                        </a>
                                    </h3>
                                    <div class="product-price">
                                        <span class="price-current"><?php echo formatPrice($related['discount_price'] ?: $related['price']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
    function changeImage(imageSrc, thumbnail) {
        document.querySelector('#mainImage img').src = imageSrc;
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        thumbnail.classList.add('active');
    }
    
    function increaseQty() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.max);
        const current = parseInt(input.value);
        if(current < max) {
            input.value = current + 1;
        }
    }
    
    function decreaseQty() {
        const input = document.getElementById('quantity');
        const current = parseInt(input.value);
        if(current > 1) {
            input.value = current - 1;
        }
    }
    
    // Option button selection
    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.querySelector('input[type="radio"]');
            this.parentElement.querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            input.checked = true;
        });
    });
    </script>
</body>
</html>