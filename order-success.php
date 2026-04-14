<?php
// ========================================
// FILE: order-success.php - Order Confirmation
// ========================================
?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isset($_GET['order'])) {
    redirect('index.php');
}

$order_number = sanitize($_GET['order']);
$payment_status = isset($_GET['status']) ? sanitize($_GET['status']) : 'pending';

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->bind_param("s", $order_number);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if(!$order) {
    redirect('index.php');
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Order Confirmation - Ladies Fashion Store";
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
        <div class="container" style="max-width: 800px; text-align: center;">
            <?php if($payment_status == 'paid'): ?>
                <div class="alert alert-success" style="font-size: 18px; padding: 30px;">
                    <i class="fas fa-check-circle" style="font-size: 64px; display: block; margin-bottom: 20px; color: var(--success-color);"></i>
                    <h2>Order Placed Successfully!</h2>
                    <p>Thank you for your purchase. Your payment has been confirmed.</p>
                </div>
            <?php elseif($payment_status == 'failed'): ?>
                <div class="alert alert-danger" style="font-size: 18px; padding: 30px;">
                    <i class="fas fa-times-circle" style="font-size: 64px; display: block; margin-bottom: 20px;"></i>
                    <h2>Payment Failed</h2>
                    <p>Your order has been created but payment was not successful. Please try again.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="font-size: 18px; padding: 30px;">
                    <i class="fas fa-info-circle" style="font-size: 64px; display: block; margin-bottom: 20px;"></i>
                    <h2>Order Created</h2>
                    <p>Your order has been created. Please complete the payment.</p>
                </div>
            <?php endif; ?>
            
            <div class="cart-summary" style="text-align: left; margin: 30px auto; max-width: 600px;">
                <h3>Order Details</h3>
                <div class="summary-row">
                    <span>Order Number:</span>
                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Order Date:</span>
                    <strong><?php echo formatDate($order['created_at']); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Payment Status:</span>
                    <?php echo getPaymentStatusBadge($order['payment_status']); ?>
                </div>
                <div class="summary-row">
                    <span>Order Status:</span>
                    <?php echo getOrderStatusBadge($order['order_status']); ?>
                </div>
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                </div>
                
                <h4 style="margin-top: 30px;">Order Items</h4>
                <?php foreach($order_items as $item): ?>
                    <div class="summary-row" style="border-bottom: 1px solid var(--border-color); padding: 10px 0;">
                        <div>
                            <?php echo htmlspecialchars($item['product_name']); ?><br>
                            <small>Qty: <?php echo $item['quantity']; ?>
                            <?php if($item['size']): ?> | Size: <?php echo htmlspecialchars($item['size']); ?><?php endif; ?>
                            <?php if($item['color']): ?> | Color: <?php echo htmlspecialchars($item['color']); ?><?php endif; ?>
                            </small>
                        </div>
                        <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                <?php if(isLoggedIn()): ?>
                    <a href="my-account.php" class="btn btn-outline">View My Orders</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>