<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

$success_message = '';
$error_message = '';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        
        try {
            // Check if email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $error_message = 'Email already exists.';
            } else {
                $stmt = $pdo->prepare("UPDATE admin_users SET first_name = ?, last_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $user_id]);
                
                // Update session
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;
                
                $success_message = "Profile updated successfully!";
            }
        } catch(PDOException $e) {
            $error_message = "Failed to update profile: " . $e->getMessage();
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        try {
            // Get current password hash
            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$user_id]);
            $admin = $stmt->fetch();
            
            if (!$admin || !password_verify($current_password, $admin['password_hash'])) {
                $error_message = 'Current password is incorrect.';
            } elseif ($new_password !== $confirm_password) {
                $error_message = 'New passwords do not match.';
            } elseif (strlen($new_password) < 6) {
                $error_message = 'New password must be at least 6 characters long.';
            } else {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_password_hash, $user_id]);
                
                $success_message = "Password changed successfully!";
            }
        } catch(PDOException $e) {
            $error_message = "Failed to change password: " . $e->getMessage();
        }
    }
}

// Get current admin data
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch();
} catch(PDOException $e) {
    die("Error fetching admin data: " . $e->getMessage());
}



 include 'includes/sidebar.php';
 // Main content
 include 'php/profile/main_content.php';
 include 'includes/footer.php';
 ?>






