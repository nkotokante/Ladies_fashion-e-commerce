<?php
// ========================================
// FILE: cart.php - Shopping Cart
// ========================================
?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle cart updates
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_cart'])) {
        foreach($_POST['quantity'] as $cart_key => $qty) {
            updateCart($cart_key, (int)$qty);
        }
        $success_message = "Cart updated successfully!";
    }
    
    if(isset($_POST['remove_item'])) {
        $cart_key = sanitize($_POST['cart_key']);
        removeFromCart($cart_key);
        $success_message = "Item removed from cart!";
    }
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = getCartTotal($conn);
$delivery_fee = getSetting('delivery_fee', $conn) ?: 20.00;
$total = $subtotal + $delivery_fee;

$page_title = "Shopping Cart - Ladies Fashion Store";
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

    <section class="cart-section">
        <div class="container">
            <h1 class="section-title" style="margin-bottom: 30px;">Shopping Cart</h1>
            
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if(empty($cart_items)): ?>
                <div class="alert alert-info" style="text-align: center;">
                    <i class="fas fa-shopping-bag" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                    <h3>Your cart is empty</h3>
                    <p>Start shopping and add items to your cart!</p>
                    <a href="products.php" class="btn btn-primary" style="margin-top: 15px;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="cart-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart_items as $cart_key => $item): 
                                    $product = getProduct($conn, $item['product_id']);
                                    if(!$product) continue;
                                    
                                    $image = getPrimaryImage($conn, $product['id']);
                                    $price = $product['discount_price'] ?: $product['price'];
                                    $item_total = $price * $item['quantity'];
                                ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 15px;">
                                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="cart-item-image">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                                    <?php if($item['size']): ?>
                                                        <small>Size: <?php echo htmlspecialchars($item['size']); ?></small><br>
                                                    <?php endif; ?>
                                                    <?php if($item['color']): ?>
                                                        <small>Color: <?php echo htmlspecialchars($item['color']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong><?php echo formatPrice($price); ?></strong></td>
                                        <td>
                                            <input type="number" name="quantity[<?php echo $cart_key; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $product['stock']; ?>" style="width: 70px; padding: 5px; text-align: center;">
                                        </td>
                                        <td><strong><?php echo formatPrice($item_total); ?></strong></td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="cart_key" value="<?php echo $cart_key; ?>">
                                                <button type="submit" name="remove_item" class="remove-btn" onclick="return confirm('Remove this item?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <a href="products.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <button type="submit" name="update_cart" class="btn btn-primary">
                            <i class="fas fa-sync"></i> Update Cart
                        </button>
                    </div>
                </form>
                
                <div class="cart-summary">
                    <h3>Cart Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <strong><?php echo formatPrice($subtotal); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <strong><?php echo formatPrice($delivery_fee); ?></strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <strong><?php echo formatPrice($total); ?></strong>
                    </div>
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 20px; text-align: center;">
                        Proceed to Checkout <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>