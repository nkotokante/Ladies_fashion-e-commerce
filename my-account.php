<?php
// ========================================
// FILE: my-account.php - Customer Account Dashboard
// ========================================
?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$customer_id = $_SESSION['customer_id'];

// Get customer orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle profile update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $customer_id);
    
    if($stmt->execute()) {
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_phone'] = $phone;
        $_SESSION['customer_address'] = $address;
        $success = "Profile updated successfully!";
    }
}

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Ladies Fashion Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="section">
        <div class="container">
            <h1>My Account</h1>
            
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 30px;">
                <!-- Sidebar -->
                <div>
                    <div class="cart-summary">
                        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</h3>
                        <div style="margin-top: 20px;">
                            <a href="?section=orders" class="btn btn-outline" style="width: 100%; margin-bottom: 10px;">My Orders</a>
                            <a href="?section=profile" class="btn btn-outline" style="width: 100%; margin-bottom: 10px;">Edit Profile</a>
                            <a href="logout.php" class="btn btn-outline" style="width: 100%;">Logout</a>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div>
                    <?php 
                    $section = isset($_GET['section']) ? $_GET['section'] : 'orders';
                    
                    if($section == 'profile'): ?>
                        <!-- Profile Section -->
                        <div class="checkout-form">
                            <h2>Edit Profile</h2>
                            
                            <?php if(isset($success)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Email (cannot be changed)</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="4"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </form>
                        </div>
                    
                    <?php else: ?>
                        <!-- Orders Section -->
                        <h2>My Orders</h2>
                        
                        <?php if(empty($orders)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You have no orders yet.
                            </div>
                        <?php else: ?>
                            <div class="cart-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Order Number</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Payment Status</th>
                                            <th>Order Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($orders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                                <td><?php echo formatDate($order['created_at']); ?></td>
                                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                                <td><?php echo getPaymentStatusBadge($order['payment_status']); ?></td>
                                                <td><?php echo getOrderStatusBadge($order['order_status']); ?></td>
                                                <td>
                                                    <a href="order-success.php?order=<?php echo $order['order_number']; ?>" class="btn btn-sm btn-outline">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
