<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Helper functions
function getActivityColor($type) {
    switch($type) {
        case 'user': return '#3498db';
        case 'issue': return '#e74c3c';
        case 'referral': return '#8e44ad';
        default: return '#95a5a6';
    }
}

function formatActivityTitle($activity) {
    switch($activity['type']) {
        case 'user':
            return 'New User: ' . htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']);
        case 'issue':
            return 'Issue: ' . htmlspecialchars($activity['issue_title']);
        case 'referral':
            return 'Referral: ' . htmlspecialchars($activity['patient_name']);
        default:
            return 'New Activity';
    }
}

function formatActivityDescription($activity) {
    switch($activity['type']) {
        case 'user':
            return htmlspecialchars($activity['email']);
        case 'issue':
            return 'Status: ' . ucfirst($activity['status']);
        case 'referral':
            return 'Status: ' . ucfirst($activity['status']);
        default:
            return 'System activity';
    }
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Fetch comprehensive dashboard statistics
try {
    // Use session data for current user info
    $current_user_id = $_SESSION['user_id'];
    
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Active Users (users with activity in last 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE is_active = 1 AND last_activity >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $active_users = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
    
    // Total Admins - FIXED QUERY
    $stmt = $pdo->query("SELECT COUNT(*) as admins FROM users WHERE role IN ('admin', 'super_admin', 'moderator') AND is_active = 1");
    $total_admins = $stmt->fetch(PDO::FETCH_ASSOC)['admins'];
    
    // Total Issues
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM issue_reports");
    $total_issues = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Resolved Issues
    $stmt = $pdo->query("SELECT COUNT(*) as resolved FROM issue_reports WHERE status IN ('resolved', 'closed')");
    $resolved_issues = $stmt->fetch(PDO::FETCH_ASSOC)['resolved'];
    
    // Pending Issues
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM issue_reports WHERE status IN ('open', 'in_progress')");
    $pending_issues = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
    
    // Total Referrals
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM referrals");
    $total_referrals = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Accepted Referrals
    $stmt = $pdo->query("SELECT COUNT(*) as accepted FROM referrals WHERE status = 'accepted'");
    $accepted_referrals = $stmt->fetch(PDO::FETCH_ASSOC)['accepted'];
    
    // Pending Referrals
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM referrals WHERE status = 'pending'");
    $pending_referrals = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
    
    // Active Sessions (sessions active in last 30 minutes)
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM user_sessions WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $active_sessions = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
    
    // Unread Alerts for current user
    $stmt = $pdo->prepare("SELECT COUNT(*) as unread FROM alerts WHERE user_id = ? AND is_read = 0 AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$current_user_id]);
    $unread_alerts = $stmt->fetch(PDO::FETCH_ASSOC)['unread'];
    
    // Recent Activities (combining users, issues, and referrals)
    $recent_activities = [];
    
    // Recent Users (last 3)
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, created_at, 'user' as type 
        FROM users 
        WHERE is_active = 1 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent Issues (last 3)
    $stmt = $pdo->query("
        SELECT id, issue_title, status, created_at, 'issue' as type 
        FROM issue_reports 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $recent_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent Referrals (last 4)
    $stmt = $pdo->query("
        SELECT id, patient_name, status, created_at, 'referral' as type 
        FROM referrals 
        ORDER BY created_at DESC 
        LIMIT 4
    ");
    $recent_referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine and sort all activities by creation date
    $all_activities = array_merge($recent_users, $recent_issues, $recent_referrals);
    usort($all_activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    $recent_activities = array_slice($all_activities, 0, 10); // Get top 10 most recent
    
} catch (PDOException $e) {
    // Handle database errors gracefully
    error_log("Dashboard database error: " . $e->getMessage());
    
    // Set default values
    $total_users = $active_users = $total_admins = $total_issues = $resolved_issues = $pending_issues = 0;
    $total_referrals = $accepted_referrals = $pending_referrals = $active_sessions = $unread_alerts = 0;
    $recent_activities = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rufaa - Admin Dashboard</title>
    <link rel="stylesheet" href="css/index.css">
    
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Admin Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
                <div class="admin-badge"><?php echo strtoupper($role); ?></div>
                <?php if ($unread_alerts > 0): ?>
                    <div class="admin-badge" style="background-color: #e74c3c;">
                        <?php echo $unread_alerts; ?> Alert<?php echo $unread_alerts > 1 ? 's' : ''; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons-container">
            <a href="users.php" class="btn btn-primary">
                User Management
            </a>
            <a href="issues.php" class="btn btn-secondary">
                Reported Issues
            </a>
            <a href="referrals.php" class="btn btn-outline">
                Referral Management
            </a>

            <?php if (in_array($role, ['admin', 'super_admin'])): ?>
                <a href="users.php" class="btn btn-outline">
                    Admin Management
                </a>
            <?php endif; ?>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- User Statistics -->
            <div class="stat-card clickable" onclick="window.location.href='users.php'">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $total_users; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-icon users">U</div>
                </div>
                <div class="stat-change"><?php echo $active_users; ?> active</div>
            </div>
            
            <div class="stat-card clickable" onclick="window.location.href='users.php'">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $active_users; ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-icon active">A</div>
                </div>
                <div class="stat-change"><?php echo $total_users > 0 ? round(($active_users / $total_users) * 100) : 0; ?>% of total</div>
            </div>

            <!-- Issue Statistics -->
            <div class="stat-card clickable" onclick="window.location.href='issues.php'">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $total_issues; ?></div>
                        <div class="stat-label">Total Issues</div>
                    </div>
                    <div class="stat-icon issues">I</div>
                </div>
                <div class="stat-change"><?php echo $resolved_issues; ?> resolved • <?php echo $pending_issues; ?> pending</div>
            </div>
            
            <div class="stat-card clickable" onclick="window.location.href='issues.php?status=open'">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $pending_issues; ?></div>
                        <div class="stat-label">Pending Issues</div>
                    </div>
                    <div class="stat-icon pending">P</div>
                </div>
                <div class="stat-change">Require attention</div>
            </div>

            <!-- Referral Statistics -->
            <div class="stat-card clickable" onclick="window.location.href='referrals.php'">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $total_referrals; ?></div>
                        <div class="stat-label">Total Referrals</div>
                    </div>
                    <div class="stat-icon" style="background-color: #8e44ad;">R</div>
                </div>
                <div class="stat-change"><?php echo $accepted_referrals; ?> accepted • <?php echo $pending_referrals; ?> pending</div>
            </div>
            
            <div class="stat-card clickable" onclick="window.location.href='referrals.php?status=pending'">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $pending_referrals; ?></div>
                        <div class="stat-label">Pending Referrals</div>
                    </div>
                    <div class="stat-icon" style="background-color: #f39c12;">W</div>
                </div>
                <div class="stat-change">Awaiting response</div>
            </div>

            <!-- System Statistics -->
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $active_sessions; ?></div>
                        <div class="stat-label">Active Sessions</div>
                    </div>
                    <div class="stat-icon" style="background-color: #1abc9c;">S</div>
                </div>
                <div class="stat-change">Currently logged in</div>
            </div>        
        </div>

        <!-- Recent Activity Section -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Recent Activity</h3>
            </div>
            
            <?php if (!empty($recent_activities)): ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: <?php echo getActivityColor($activity['type']); ?>;">
                                <?php echo strtoupper(substr($activity['type'], 0, 1)); ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?php echo formatActivityTitle($activity); ?>
                                </div>
                                <div class="activity-description">
                                    <?php echo formatActivityDescription($activity); ?>
                                </div>
                            </div>
                            <div class="activity-time">
                                <div class="time-ago">
                                    <?php echo time_elapsed_string($activity['created_at']); ?>
                                </div>
                                <div class="exact-time">
                                    <?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: #7f8c8d;">
                    <p>No recent activity found.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Quick Actions</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <?php if (in_array($role, ['moderator', 'admin', 'super_admin'])): ?>
                    <a href="users.php" class="quick-action-card">
                        <div style="font-size: 24px; margin-bottom: 8px;">👥</div>
                        <div style="font-weight: 500;">Manage Users</div>
                        <div style="font-size: 12px; color: #7f8c8d;">View and manage system users</div>
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($role, ['admin', 'super_admin'])): ?>
                    <a href="users.php" class="quick-action-card">
                        <div style="font-size: 24px; margin-bottom: 8px;">⚙️</div>
                        <div style="font-weight: 500;">Admin Users</div>
                        <div style="font-size: 12px; color: #7f8c8d;">Manage admin accounts</div>
                    </a>
                <?php endif; ?>
                
                <a href="issues.php" class="quick-action-card">
                    <div style="font-size: 24px; margin-bottom: 8px;">🐛</div>
                    <div style="font-weight: 500;">Issue Reports</div>
                    <div style="font-size: 12px; color: #7f8c8d;">View and resolve issues</div>
                </a>
                
                <a href="referrals.php" class="quick-action-card">
                    <div style="font-size: 24px; margin-bottom: 8px;">📋</div>
                    <div style="font-weight: 500;">Referrals</div>
                    <div style="font-size: 12px; color: #7f8c8d;">Manage patient referrals</div>
                </a>
                
                <a href="profile.php" class="quick-action-card">
                    <div style="font-size: 24px; margin-bottom: 8px;">👤</div>
                    <div style="font-weight: 500;">My Profile</div>
                    <div style="font-size: 12px; color: #7f8c8d;">Update account settings</div>
                </a>
            </div>
        </div>
    </div>
    <script src="js/index.js">
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>