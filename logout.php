<?php
// ========================================
// FILE: logout.php - Logout
// ========================================
?>
<?php
session_start();
session_destroy();
header("Location: index.php");
exit();
?>