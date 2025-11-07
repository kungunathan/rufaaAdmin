<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Reported Issues</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
            <div class="admin-badge"><?php echo strtoupper($role); ?></div>
        </div>
    </div>

    <!-- Success/Error Messages Display -->
    <?php if ($success_message): ?>
        <div class="alert alert-success" id="successAlert"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error" id="errorAlert"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="action-buttons-container">
        <a href="index.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <!-- Issue Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $issue_stats['total']; ?></div>
                    <div class="stat-label">Total Issues</div>
                </div>
                <div class="stat-icon" style="background-color: #3498db;">T</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $issue_stats['active']; ?></div>
                    <div class="stat-label">Active Issues</div>
                </div>
                <div class="stat-icon" style="background-color: #e74c3c;">A</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $issue_stats['open']; ?></div>
                    <div class="stat-label">Open Issues</div>
                </div>
                <div class="stat-icon" style="background-color: #f39c12;">O</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $issue_stats['resolved']; ?></div>
                    <div class="stat-label">Resolved Issues</div>
                </div>
                <div class="stat-icon" style="background-color: #27ae60;">R</div>
            </div>
        </div>
    </div>

    <!-- Filters and Table Section -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Issues Management</h3>
            <div class="table-controls">
                <form method="GET" action="" class="issues-filter-form">
                    <!-- Status Filter -->
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active Issues</option>
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Issues</option>
                        <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Open Only</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved/Closed</option>
                    </select>
                    
                    <!-- Priority Filter -->
                    <select name="priority" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $priority_filter === 'all' ? 'selected' : ''; ?>>All Priorities</option>
                        <option value="critical" <?php echo $priority_filter === 'critical' ? 'selected' : ''; ?>>Critical</option>
                        <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                    
                    <!-- Type Filter -->
                    <select name="type" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="technical" <?php echo $type_filter === 'technical' ? 'selected' : ''; ?>>Technical</option>
                        <option value="bug" <?php echo $type_filter === 'bug' ? 'selected' : ''; ?>>Bug</option>
                        <option value="feature" <?php echo $type_filter === 'feature' ? 'selected' : ''; ?>>Feature</option>
                        <option value="ui" <?php echo $type_filter === 'ui' ? 'selected' : ''; ?>>UI/UX</option>
                        <option value="performance" <?php echo $type_filter === 'performance' ? 'selected' : ''; ?>>Performance</option>
                        <option value="data" <?php echo $type_filter === 'data' ? 'selected' : ''; ?>>Data</option>
                        <option value="other" <?php echo $type_filter === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    
                    <!-- Module Filter -->
                    <select name="module" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $module_filter === 'all' ? 'selected' : ''; ?>>All Modules</option>
                        <option value="referrals" <?php echo $module_filter === 'referrals' ? 'selected' : ''; ?>>Referrals</option>
                        <option value="patients" <?php echo $module_filter === 'patients' ? 'selected' : ''; ?>>Patients</option>
                        <option value="reports" <?php echo $module_filter === 'reports' ? 'selected' : ''; ?>>Reports</option>
                        <option value="user" <?php echo $module_filter === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="system" <?php echo $module_filter === 'system' ? 'selected' : ''; ?>>System</option>
                    </select>
                    
                    <button type="button" class="btn btn-outline" onclick="window.location.href='issues.php'">Reset Filters</button>
                </form>
            </div>
        </div>

        <!-- Issues Table -->
        <table class="data-table" id="issuesTable">
            <thead>
                <tr>
                    <th>Issue ID</th>
                    <th>Title & Description</th>
                    <th>Type & Module</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Reporter</th>
                    <th>Created</th>
                    <th>Resolved By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($issues as $issue): ?>
                <tr>
                    <td>ISS-<?php echo str_pad($issue['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td>
                        <div class="issue-title-container">
                            <div class="issue-title">
                                <?php echo htmlspecialchars($issue['issue_title']); ?>
                            </div>
                            <div class="issue-description">
                                <?php echo htmlspecialchars(substr($issue['issue_description'], 0, 100) . (strlen($issue['issue_description']) > 100 ? '...' : '')); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="issue-type-container">
                            <div class="issue-type-badge">
                                <span class="status-badge status-inactive">
                                    <?php echo ucfirst($issue['issue_type']); ?>
                                </span>
                            </div>
                            <div class="issue-module">
                                <?php echo $issue['related_module'] ? ucfirst($issue['related_module']) : 'N/A'; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="priority-badge priority-<?php echo strtolower($issue['priority_level']); ?>">
                            <?php echo ucfirst($issue['priority_level']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo getStatusBadgeClass($issue['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $issue['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <div class="reporter-info">
                            <div class="reporter-name"><?php echo htmlspecialchars($issue['reporter_name']); ?></div>
                            <div class="reporter-email"><?php echo htmlspecialchars($issue['reporter_email']); ?></div>
                        </div>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($issue['created_at'])); ?></td>
                    <td>
                        <?php if ($issue['resolved_by_name']): ?>
                            <div class="resolver-info">
                                <div class="resolver-name"><?php echo htmlspecialchars($issue['resolver_first_name'] . ' ' . $issue['resolver_last_name']); ?></div>
                                <div class="resolver-date"><?php echo date('M j, Y', strtotime($issue['resolved_at'])); ?></div>
                            </div>
                        <?php else: ?>
                            <span class="not-resolved">Not resolved</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-sm" onclick="showIssueDetails(<?php echo $issue['id']; ?>)">View</button>
                            <?php if ($issue['status'] !== 'resolved' && $issue['status'] !== 'closed'): ?>
                                <button class="btn btn-secondary btn-sm" onclick="showResolveModal(<?php echo $issue['id']; ?>)">Resolve</button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $issue['id']; ?>)">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($issues)): ?>
                <tr>
                    <td colspan="9" class="no-issues-message">
                        No issues found matching the current filters.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>