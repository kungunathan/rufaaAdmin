<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("UPDATE admin_users SET first_name = ?, last_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $email, $user_id]);
        
        // Update session
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        
        $success = "Profile updated successfully!";
    } catch(PDOException $e) {
        $error = "Failed to update profile: " . $e->getMessage();
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

    <?php if (isset($success)): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="table-container">
        <h3 class="table-title">Profile Information</h3>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($admin['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($admin['last_name']); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
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
                <input type="text" class="form-input" value="<?php echo $admin['last_login'] ? htmlspecialchars($admin['last_login']) : 'Never'; ?>" readonly style="background-color: #f8f9fa;">
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="change-password.php" class="btn btn-outline">Change Password</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>