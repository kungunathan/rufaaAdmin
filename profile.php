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
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
            <div class="admin-badge"><?php echo strtoupper($role); ?></div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success" id="successAlert"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error" id="errorAlert"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Profile Information Section -->
    <div class="table-container">
        <h3 class="table-title">Profile Information</h3>
        <form method="POST">
            <input type="hidden" name="update_profile" value="1">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($admin['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($admin['last_name']); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-input" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly style="background-color: #f8f9fa;">
                <small style="color: #7f8c8d; font-size: 12px;">Username cannot be changed</small>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <input type="text" class="form-input" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $admin['role']))); ?>" readonly style="background-color: #f8f9fa;">
            </div>
            <div class="form-group">
                <label class="form-label">Last Login</label>
                <input type="text" class="form-input" value="<?php echo $admin['last_login'] ? date('Y-m-d H:i', strtotime($admin['last_login'])) : 'Never'; ?>" readonly style="background-color: #f8f9fa;">
            </div>
            <div class="form-group">
                <label class="form-label">Account Created</label>
                <input type="text" class="form-input" value="<?php echo date('Y-m-d H:i', strtotime($admin['created_at'])); ?>" readonly style="background-color: #f8f9fa;">
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>

    <!-- Change Password Section -->
    <div class="table-container">
        <h3 class="table-title">Change Password</h3>
        <form method="POST">
            <input type="hidden" name="change_password" value="1">
            <div style="display: grid; gap: 15px; max-width: 400px;">
                <div class="form-group">
                    <label class="form-label">Current Password *</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password *</label>
                    <input type="password" name="new_password" class="form-input" required minlength="6">
                    <small style="color: #7f8c8d; font-size: 12px;">Minimum 6 characters</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password *</label>
                    <input type="password" name="confirm_password" class="form-input" required minlength="6">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.display = 'none';
    });
}, 5000);
</script>

<style>
.alert {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-success {
    background-color: #e8f6f3;
    color: #27ae60;
    border: 1px solid #d1f2eb;
}

.alert-error {
    background-color: #fdedec;
    color: #e74c3c;
    border: 1px solid #fadbd8;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
    font-weight: 500;
    font-size: 14px;
}

.form-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.form-input:focus {
    outline: none;
    border-color: #3498db;
}

.table-container {
    margin-bottom: 30px;
}
</style>

<?php include 'includes/footer.php'; ?>