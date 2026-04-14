<?php
/**
 * Admin Password Reset Tool
 * Use this file ONLY to reset your admin password
 * DELETE THIS FILE after resetting password for security!
 */

define('APP_INIT', true);
require_once 'includes/config.php';

$message = '';
$error = '';
$success = false;

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in both password fields';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update admin password (for username 'admin')
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
        $stmt->bind_param("s", $hashed_password);
        
        if ($stmt->execute()) {
            $success = true;
            $message = 'Password reset successful! You can now login with username: admin and your new password.';
        } else {
            $error = 'Failed to update password. Database error.';
        }
    }
}

// Check if admin exists
$check = $conn->query("SELECT id, username FROM admins WHERE username = 'admin'");
$admin_exists = $check->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #6366f1;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #6b7280;
            line-height: 1.6;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-color: #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-color: #ef4444;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-color: #f59e0b;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-color: #3b82f6;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #6366f1;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            margin-top: 1rem;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .info-box {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .info-box h3 {
            color: #374151;
            margin-bottom: 1rem;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box li {
            padding: 0.5rem 0;
            color: #6b7280;
            line-height: 1.6;
        }

        .info-box li:before {
            content: "✓ ";
            color: #10b981;
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .danger-zone {
            border: 2px solid #ef4444;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
            background: #fef2f2;
        }

        .danger-zone h3 {
            color: #991b1b;
            margin-bottom: 1rem;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="header">
            <h1>🔐 Reset Admin Password</h1>
            <p>Use this tool to reset your admin password if you're having login issues</p>
        </div>

        <?php if (!$admin_exists): ?>
            <div class="alert alert-error">
                <strong>❌ Admin account not found!</strong><br>
                The database may not be properly set up. Please ensure you've imported the install.sql file.
            </div>
        <?php else: ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✅ <?php echo $message; ?></strong>
                </div>
                <a href="admin/index.php" class="btn btn-primary">Go to Admin Login</a>
                
                <div class="danger-zone">
                    <h3>⚠️ Security Warning</h3>
                    <p style="color: #991b1b; line-height: 1.6;">
                        <strong>DELETE THIS FILE NOW!</strong><br>
                        This password reset tool is a security risk if left on your server. 
                        Delete <code>reset-password.php</code> immediately after resetting your password.
                    </p>
                </div>
            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        ❌ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>ℹ️ Current admin username:</strong> admin<br>
                    Set a new password below to regain access.
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" 
                               required minlength="6" 
                               placeholder="Enter new password (min 6 characters)">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               required minlength="6"
                               placeholder="Re-enter new password">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        🔄 Reset Password
                    </button>
                </form>

                <div class="info-box">
                    <h3>📋 Common Login Issues:</h3>
                    <ul>
                        <li>Make sure you're using username: <strong>admin</strong></li>
                        <li>Password is case-sensitive</li>
                        <li>Clear browser cache and cookies</li>
                        <li>Try a different browser</li>
                        <li>Ensure database connection is working</li>
                        <li>Check if sessions are enabled in PHP</li>
                    </ul>
                </div>

                <div class="danger-zone">
                    <h3>⚠️ Important Security Note</h3>
                    <p style="color: #991b1b; line-height: 1.6;">
                        <strong>DELETE this file after use!</strong><br>
                        This password reset tool should NOT remain on your server as it's a security vulnerability. 
                        After successfully resetting your password, immediately delete <code>reset-password.php</code> from your server.
                    </p>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <div class="back-link">
            <a href="index.php">← Back to Voting Page</a>
        </div>
    </div>
</body>
</html>