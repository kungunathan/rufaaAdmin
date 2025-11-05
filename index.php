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
    // User Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE is_active = 1");
    $active_users = $stmt->fetch()['active_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_admins FROM admin_users WHERE is_active = 1");
    $total_admins = $stmt->fetch()['total_admins'];
    
    // Issue Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_issues FROM issue_reports");
    $total_issues = $stmt->fetch()['total_issues'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending_issues FROM issue_reports WHERE status = 'open'");
    $pending_issues = $stmt->fetch()['pending_issues'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as resolved_issues FROM issue_reports WHERE status IN ('resolved', 'closed')");
    $resolved_issues = $stmt->fetch()['resolved_issues'];
    
    // Referral Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_referrals FROM referrals");
    $total_referrals = $stmt->fetch()['total_referrals'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending_referrals FROM referrals WHERE status = 'pending'");
    $pending_referrals = $stmt->fetch()['pending_referrals'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as accepted_referrals FROM referrals WHERE status = 'accepted'");
    $accepted_referrals = $stmt->fetch()['accepted_referrals'];
    
    // System Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as active_sessions FROM user_sessions WHERE expires_at > NOW()");
    $active_sessions = $stmt->fetch()['active_sessions'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as unread_alerts FROM alerts WHERE is_read = 0");
    $unread_alerts = $stmt->fetch()['unread_alerts'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as capacity_entries FROM capacity WHERE available_capacity > 0");
    $capacity_entries = $stmt->fetch()['capacity_entries'];
    
    // Recent activity data
    $stmt = $pdo->query("
        SELECT 'user' as type, first_name, last_name, email, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_users = $stmt->fetchAll();
    
    $stmt = $pdo->query("
        SELECT 'issue' as type, issue_title, status, created_at 
        FROM issue_reports 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_issues = $stmt->fetchAll();
    
    $stmt = $pdo->query("
        SELECT 'referral' as type, patient_name, status, created_at 
        FROM referrals 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_referrals = $stmt->fetchAll();
    
    // Combine and sort recent activity
    $recent_activities = array_merge($recent_users, $recent_issues, $recent_referrals);
    usort($recent_activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $recent_activities = array_slice($recent_activities, 0, 8); // Get top 8 most recent
    
} catch(PDOException $e) {
    error_log("Dashboard data fetch error: " . $e->getMessage());
    // Initialize all variables with default values
    $total_users = $active_users = $total_admins = $total_issues = $pending_issues = $resolved_issues = 0;
    $total_referrals = $pending_referrals = $accepted_referrals = $active_sessions = $unread_alerts = $capacity_entries = 0;
    $recent_activities = [];
}
?>

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
                    <?php echo $unread_alerts; ?> Alerts
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
            <div class="stat-change"><?php echo $active_users; ?> active • <?php echo $total_admins; ?> admins</div>
        </div>
        
        <div class="stat-card clickable" onclick="window.location.href='users.php'">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $active_users; ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-icon active">A</div>
            </div>
            <div class="stat-change"><?php echo round(($active_users / max($total_users, 1)) * 100); ?>% of total</div>
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
                    <div style="display: flex; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: <?php echo getActivityColor($activity['type']); ?>; display: flex; align-items: center; justify-content: center; margin-right: 12px; color: white; font-size: 14px; font-weight: bold;">
                            <?php echo strtoupper(substr($activity['type'], 0, 1)); ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 500; color: #2c3e50;">
                                <?php echo formatActivityTitle($activity); ?>
                            </div>
                            <div style="font-size: 13px; color: #7f8c8d;">
                                <?php echo formatActivityDescription($activity); ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #7f8c8d;">
                                <?php echo time_elapsed_string($activity['created_at']); ?>
                            </div>
                            <div style="font-size: 11px; color: #95a5a6;">
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

<script>
// Make stat cards clickable with hover effects
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card.clickable');
    statCards.forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.05)';
        });
    });
});

// Auto-refresh dashboard every 2 minutes
setTimeout(() => {
    window.location.reload();
}, 120000);
</script>

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

.action-buttons-container {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.action-buttons-container .btn {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.action-buttons-container .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.quick-action-card {
    display: block;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    text-align: center;
    border: 2px solid transparent;
}

.quick-action-card:hover {
    background: #e9ecef;
    border-color: #3498db;
    transform: translateY(-2px);
    text-decoration: none;
    color: inherit;
}

.stat-card {
    transition: all 0.3s ease;
}

.table-controls {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}
</style>

<?php include 'includes/footer.php'; ?>