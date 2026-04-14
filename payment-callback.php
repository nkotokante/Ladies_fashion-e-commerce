<?php
// ========================================
// FILE: payment-callback.php - Verify Payment
// ========================================
?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isset($_GET['reference'])) {
    redirect('index.php');
}

$reference = sanitize($_GET['reference']);

// Verify payment with Paystack
$payment_data = verifyPaystackPayment($reference);

if($payment_data && $payment_data['status'] === 'success') {
    // Payment successful
    $order_number = $payment_data['reference'];
    $amount = $payment_data['amount'] / 100; // Convert from pesewas to cedis
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'processing' WHERE order_number = ?");
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    
    // Get order ID
    $stmt = $conn->prepare("SELECT id FROM orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if($order) {
        // Record payment
        $payment_method = 'Paystack';
        $status = 'success';
        
        $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_ref, amount, payment_method, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $order['id'], $reference, $amount, $payment_method, $status);
        $stmt->execute();
        
        // Clear cart
        clearCart();
        
        // Clear pending order session
        unset($_SESSION['pending_order']);
        
        // Redirect to success page
        redirect('order-success.php?order=' . $order_number . '&status=paid');
    }
} else {
    // Payment failed
    if(isset($_SESSION['pending_order'])) {
        $order_number = $_SESSION['pending_order']['order_number'];
        
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'failed' WHERE order_number = ?");
        $stmt->bind_param("s", $order_number);
        $stmt->execute();
        
        redirect('order-success.php?order=' . $order_number . '&status=failed');
    }
}

redirect('index.php');
?>