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