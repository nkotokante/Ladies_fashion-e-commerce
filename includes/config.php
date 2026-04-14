<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ladies_fashion_store');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Site settings
define('SITE_URL', 'http://localhost/ladies-fashion-store');
define('ADMIN_URL', SITE_URL . '/admin');

// Payment settings (Get these from Paystack dashboard)
define('PAYSTACK_PUBLIC_KEY', 'pk_test_0dc54d1982a2997902b16b805e2d6d8c51d40f4b');
define('PAYSTACK_SECRET_KEY', 'sk_test_38ac669f150432f6018dd906e02ece96c6599c3e');
define('PAYSTACK_CALLBACK_URL', SITE_URL . '/payment-callback.php');
define('PAYSTACK_CURRENCY', 'GHS');

// Helper functions
function getSetting($key, $conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return '';
}

// Get currency
$currency = getSetting('currency', $conn) ?: 'GHS';
?>