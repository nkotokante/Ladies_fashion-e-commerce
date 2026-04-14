<?php
// ========================================
// FILE: checkout.php - Checkout & Payment
// ========================================
?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if cart is empty
if(empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$cart_items = $_SESSION['cart'];
$subtotal = getCartTotal($conn);
$delivery_fee = getSetting('delivery_fee', $conn) ?: 20.00;
$total = $subtotal + $delivery_fee;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $customer_name = sanitize($_POST['customer_name']);
    $customer_email = sanitize($_POST['customer_email']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $delivery_address = sanitize($_POST['delivery_address']);
    
    // Validate inputs
    if(empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($delivery_address)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Create order
        $order_number = generateOrderNumber();
        $customer_id = isLoggedIn() ? $_SESSION['customer_id'] : null;
        
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_number, customer_name, customer_email, customer_phone, delivery_address, subtotal, delivery_fee, total_amount, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
        $stmt->bind_param("isssssddd", $customer_id, $order_number, $customer_name, $customer_email, $customer_phone, $delivery_address, $subtotal, $delivery_fee, $total);
        
        if($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Insert order items
            foreach($cart_items as $item) {
                $product = getProduct($conn, $item['product_id']);
                $price = $product['discount_price'] ?: $product['price'];
                
                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, size, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("iisidss", $order_id, $item['product_id'], $product['name'], $item['quantity'], $price, $item['size'], $item['color']);
                $stmt2->execute();
            }
            
            // Store order details in session for payment
            $_SESSION['pending_order'] = [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'amount' => $total,
                'email' => $customer_email
            ];
            
            // Redirect to payment
            $payment_redirect = true;
        } else {
            $error_message = "Failed to create order. Please try again.";
        }
    }
}

$page_title = "Checkout - Ladies Fashion Store";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="checkout-section">
        <div class="container" style="max-width: 1200px;">
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
                <!-- Checkout Form -->
                <div class="checkout-form">
                    <h2>Delivery Information</h2>
                    <form method="POST" action="" id="checkoutForm">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="customer_name" class="form-control" required value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['customer_name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="customer_email" class="form-control" required value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['customer_email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="customer_phone" class="form-control" required value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['customer_phone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Delivery Address *</label>
                            <textarea name="delivery_address" class="form-control" rows="4" required><?php echo isLoggedIn() ? htmlspecialchars($_SESSION['customer_address'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px;">
                            <i class="fas fa-credit-card"></i> Proceed to Payment
                        </button>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    
                    <div style="margin: 20px 0;">
                        <?php foreach($cart_items as $item): 
                            $product = getProduct($conn, $item['product_id']);
                            if(!$product) continue;
                            $price = $product['discount_price'] ?: $product['price'];
                        ?>
                            <div class="summary-row" style="border-bottom: 1px solid var(--border-color); padding: 10px 0;">
                                <div>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                    <small>Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <strong><?php echo formatPrice($price * $item['quantity']); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
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
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <?php if(isset($payment_redirect) && $payment_redirect): ?>
    <script>
    // Initialize Paystack payment
    const paymentData = {
        email: '<?php echo $_SESSION['pending_order']['email']; ?>',
        amount: <?php echo $_SESSION['pending_order']['amount'] * 100; ?>, // Paystack uses pesewas/cents
        ref: '<?php echo $_SESSION['pending_order']['order_number']; ?>',
        callback_url: '<?php echo SITE_URL; ?>/payment-callback.php'
    };
    
    const handler = PaystackPop.setup({
        key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
        email: paymentData.email,
        amount: paymentData.amount,
        currency: 'GHS', // ✅ REQUIRED
        ref: paymentData.ref,
        callback: function(response) {
            window.location = paymentData.callback_url + '?reference=' + response.reference;
        },
        onClose: function() {
            alert('Payment cancelled. Your order is saved and you can complete payment later.');
            window.location = 'order-success.php?order=<?php echo $_SESSION['pending_order']['order_number']; ?>';
        }
    });
    
    handler.openIframe();
    </script>
    <?php endif; ?>
</body>
</html>
