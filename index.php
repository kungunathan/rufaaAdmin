<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Fetch dashboard statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];
    
    // Active users
    $stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE is_active = 1");
    $active_users = $stmt->fetch()['active_users'];
    
    // Total issues
    $stmt = $pdo->query("SELECT COUNT(*) as total_issues FROM issue_reports");
    $total_issues = $stmt->fetch()['total_issues'];
    
    // Pending issues
    $stmt = $pdo->query("SELECT COUNT(*) as pending_issues FROM issue_reports WHERE status = 'open'");
    $pending_issues = $stmt->fetch()['pending_issues'];
    
    // Total referrals
    $stmt = $pdo->query("SELECT COUNT(*) as total_referrals FROM referrals");
    $total_referrals = $stmt->fetch()['total_referrals'];
    
    // Pending referrals
    $stmt = $pdo->query("SELECT COUNT(*) as pending_referrals FROM referrals WHERE status = 'pending'");
    $pending_referrals = $stmt->fetch()['pending_referrals'];
    
} catch(PDOException $e) {
    error_log("Dashboard data fetch error: " . $e->getMessage());
    $total_users = $active_users = $total_issues = $pending_issues = $total_referrals = $pending_referrals = 0;
}
?>
<!-- Side bar -->
<?php include 'includes/sidebar.php'?>
<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Admin Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
            <div class="admin-badge"><?php echo strtoupper($role); ?></div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons-container" style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
        <a href="users.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">
            User Management
        </a>
        <a href="issues.php" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">
            Reported Issues
        </a>
        <a href="referrals.php" class="btn btn-outline" style="text-decoration: none; display: inline-block;">
            Referral Management
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-icon users">U</div>
            </div>
            <div class="stat-change">Registered in system</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $active_users; ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-icon active">A</div>
            </div>
            <div class="stat-change">Currently active</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $total_issues; ?></div>
                    <div class="stat-label">Total Issues</div>
                </div>
                <div class="stat-icon issues">I</div>
            </div>
            <div class="stat-change">All reported issues</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $pending_issues; ?></div>
                    <div class="stat-label">Pending Issues</div>
                </div>
                <div class="stat-icon pending">P</div>
            </div>
            <div class="stat-change">Awaiting resolution</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $total_referrals; ?></div>
                    <div class="stat-label">Total Referrals</div>
                </div>
                <div class="stat-icon" style="background-color: #8e44ad;">R</div>
            </div>
            <div class="stat-change">All patient referrals</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $pending_referrals; ?></div>
                    <div class="stat-label">Pending Referrals</div>
                </div>
                <div class="stat-icon" style="background-color: #f39c12;">W</div>
            </div>
            <div class="stat-change">Awaiting response</div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Recent Activity</h3>
        </div>
        <div style="padding: 20px; text-align: center; color: #7f8c8d;">
            <p>Dashboard overview showing key system metrics. Use the action buttons above to manage specific areas.</p>
        </div>
    </div>
</div>

<style>
.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    color: white;
}

.stat-icon.users { background-color: #3498db; }
.stat-icon.issues { background-color: #e74c3c; }
.stat-icon.active { background-color: #27ae60; }
.stat-icon.pending { background-color: #f39c12; }

.stat-change {
    font-size: 12px;
    margin-top: 5px;
    color: #7f8c8d;
}

.action-buttons-container .btn {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.action-buttons-container .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}
</style>

<?php include 'includes/footer.php'; ?>