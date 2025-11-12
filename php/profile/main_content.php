<html>
<link rel="stylesheet" href="css/login.css"> 
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
</html>