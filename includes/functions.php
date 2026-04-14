<?php
// includes/functions.php - Helper Functions

// Sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Format price with currency
function formatPrice($price) {
    global $currency;
    return $currency . ' ' . number_format($price, 2);
}

// Generate unique order number
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Get cart total count
function getCartCount() {
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
    return 0;
}

// Get cart total amount
function getCartTotal($conn) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $conn->prepare("SELECT price, discount_price FROM products WHERE id = ?");
        $stmt->bind_param("i", $item['product_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($product = $result->fetch_assoc()) {
            $price = $product['discount_price'] ? $product['discount_price'] : $product['price'];
            $total += $price * $item['quantity'];
        }
    }
    
    return $total;
}

// Add to cart
function addToCart($productId, $quantity = 1, $size = '', $color = '') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Create unique key for product with size and color
    $cartKey = $productId . '_' . $size . '_' . $color;
    
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = array(
            'product_id' => $productId,
            'quantity' => $quantity,
            'size' => $size,
            'color' => $color
        );
    }
    
    return true;
}

// Update cart quantity
function updateCart($cartKey, $quantity) {
    if (isset($_SESSION['cart'][$cartKey])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cartKey]);
        } else {
            $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
        }
        return true;
    }
    return false;
}

// Remove from cart
function removeFromCart($cartKey) {
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
        return true;
    }
    return false;
}

// Clear cart
function clearCart() {
    $_SESSION['cart'] = array();
}

// Get product by ID
function getProduct($conn, $id) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get product images
function getProductImages($conn, $productId) {
    $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get primary product image
function getPrimaryImage($conn, $productId) {
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['image_path'];
    }
    
    // If no primary, get first image
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['image_path'];
    }
    
    return 'assets/images/no-image.jpg';
}

// Get all categories
function getCategories($conn) {
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Upload image
function uploadImage($file, $targetDir = 'assets/images/products/') {
    // Check if file is uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    // Create directory if not exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    }
    
    return false;
}

// Delete image
function deleteImage($imagePath) {
    if (file_exists($imagePath)) {
        return unlink($imagePath);
    }
    return false;
}

// Create slug from string
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Verify Paystack payment
function verifyPaystackPayment($reference) {
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return false;
    }
    
    $result = json_decode($response, true);
    
    if ($result && $result['status'] && $result['data']['status'] === 'success') {
        return $result['data'];
    }
    
    return false;
}

// Send email (basic function - can be enhanced with PHPMailer)
function sendEmail($to, $subject, $message) {
    $headers = "From: " . getSetting('store_email', $GLOBALS['conn']) . "\r\n";
    $headers .= "Reply-To: " . getSetting('store_email', $GLOBALS['conn']) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Get order status badge
function getOrderStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'processing' => '<span class="badge badge-info">Processing</span>',
        'shipped' => '<span class="badge badge-primary">Shipped</span>',
        'delivered' => '<span class="badge badge-success">Delivered</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : $status;
}

// Get payment status badge
function getPaymentStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'paid' => '<span class="badge badge-success">Paid</span>',
        'failed' => '<span class="badge badge-danger">Failed</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : $status;
}

// Pagination function
function paginate($totalRecords, $perPage, $currentPage) {
    $totalPages = ceil($totalRecords / $perPage);
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset
    ];
}

// Search products
function searchProducts($conn, $keyword, $categoryId = null, $minPrice = null, $maxPrice = null, $sortBy = 'latest') {
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($keyword) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $keyword . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
        $types .= 'i';
    }
    
    if ($minPrice !== null) {
        $sql .= " AND p.price >= ?";
        $params[] = $minPrice;
        $types .= 'd';
    }
    
    if ($maxPrice !== null) {
        $sql .= " AND p.price <= ?";
        $params[] = $maxPrice;
        $types .= 'd';
    }
    
    // Sort
    switch ($sortBy) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'name':
            $sql .= " ORDER BY p.name ASC";
            break;
        default:
            $sql .= " ORDER BY p.created_at DESC";
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get featured products
function getFeaturedProducts($conn, $limit = 8) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get new arrivals
function getNewArrivals($conn, $limit = 8) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE new_arrival = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get best sellers
function getBestSellers($conn, $limit = 8) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE best_seller = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get related products
function getRelatedProducts($conn, $productId, $categoryId, $limit = 4) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT ?");
    $stmt->bind_param("iii", $categoryId, $productId, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>