<?php
// ========================================
// FILE: includes/footer.php - Reusable Footer
// ========================================
?>
<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Chris_Klosets offers the finest selection of women's clothing, shoes, accessories, and beauty products. We bring you the latest trends and timeless classics.</p>
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" title="Pinterest">
                        <i class="fab fa-pinterest"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Shop All</a></li>
                    <?php 
                    $footer_categories = getCategories($conn);
                    foreach(array_slice($footer_categories, 0, 4) as $cat): 
                    ?>
                        <li><a href="products.php?category=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="#">Shipping Information</a></li>
                    <li><a href="#">Returns & Exchanges</a></li>
                    <li><a href="#">Size Guide</a></li>
                    <li><a href="#">Track Your Order</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li>
                        <i class="fas fa-phone"></i> 
                        <?php echo getSetting('store_phone', $conn) ?: '+233 XX XXX XXXX'; ?>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i> 
                        <?php echo getSetting('store_email', $conn) ?: 'info@ladiesfashion.com'; ?>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt"></i> 
                        Accra, Ghana
                    </li>
                    <li>
                        <i class="fas fa-clock"></i> 
                        Mon - Sat: 9:00 AM - 8:00 PM
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo getSetting('store_name', $conn) ?: 'Ladies Fashion Store'; ?>. All rights reserved.</p>
            <p style="margin-top: 5px; font-size: 14px;">
                <a href="#" style="color: #999;">Terms & Conditions</a> | 
                <a href="#" style="color: #999;">Privacy Policy</a>
            </p>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="assets/js/main.js"></script>