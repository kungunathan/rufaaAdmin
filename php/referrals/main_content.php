<html>
<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Referral Management</h1>
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

    <!-- Action Buttons -->
    <div class="action-buttons-container">
        <a href="index.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <!-- Referral Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $referral_stats['total']; ?></div>
                    <div class="stat-label">Total Referrals</div>
                </div>
                <div class="stat-icon" style="background-color: #3498db;">T</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $referral_stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-icon" style="background-color: #f39c12;">P</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $referral_stats['accepted']; ?></div>
                    <div class="stat-label">Accepted</div>
                </div>
                <div class="stat-icon" style="background-color: #27ae60;">A</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $referral_stats['emergency']; ?></div>
                    <div class="stat-label">Emergency</div>
                </div>
                <div class="stat-icon" style="background-color: #e74c3c;">E</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Referrals</h3>
            <div class="table-controls">
                <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="declined" <?php echo $status_filter === 'declined' ? 'selected' : ''; ?>>Declined</option>
                    </select>
                    
                    <select name="urgency" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $urgency_filter === 'all' ? 'selected' : ''; ?>>All Urgency Levels</option>
                        <option value="emergency" <?php echo $urgency_filter === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                        <option value="urgent" <?php echo $urgency_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        <option value="routine" <?php echo $urgency_filter === 'routine' ? 'selected' : ''; ?>>Routine</option>
                    </select>
                    
                    <select name="type" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="incoming" <?php echo $type_filter === 'incoming' ? 'selected' : ''; ?>>Incoming</option>
                        <option value="outgoing" <?php echo $type_filter === 'outgoing' ? 'selected' : ''; ?>>Outgoing</option>
                    </select>
                    
                    <button type="button" class="btn btn-outline" onclick="window.location.href='referrals.php'">Reset Filters</button>
                </form>
            </div>
        </div>

        <table class="data-table" id="referralsTable">
            <thead>
                <tr>
                    <th>Referral ID</th>
                    <th>Patient Information</th>
                    <th>Medical Details</th>
                    <th>Referral Details</th>
                    <th>Urgency</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($referrals as $referral): ?>
                <tr>
                    <td>REF-<?php echo str_pad($referral['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td>
                        <div style="max-width: 200px;">
                            <div style="font-weight: 500; color: #2c3e50; margin-bottom: 3px;">
                                <?php echo htmlspecialchars($referral['patient_name']); ?>
                            </div>
                            <div style="font-size: 13px; color: #7f8c8d;">
                                ID: <?php echo htmlspecialchars($referral['patient_id']); ?>
                            </div>
                            <div style="font-size: 13px; color: #7f8c8d;">
                                <?php echo $referral['patient_age']; ?> yrs • <?php echo ucfirst($referral['patient_gender']); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="max-width: 200px;">
                            <div style="font-size: 13px; margin-bottom: 3px;">
                                <strong>Symptoms:</strong> <?php echo htmlspecialchars(substr($referral['symptoms'] ?? 'Not specified', 0, 50) . (strlen($referral['symptoms'] ?? '') > 50 ? '...' : '')); ?>
                            </div>
                            <div style="font-size: 13px; color: #7f8c8d;">
                                <strong>Condition:</strong> <?php echo htmlspecialchars(substr($referral['condition_description'] ?? 'Not specified', 0, 30) . (strlen($referral['condition_description'] ?? '') > 30 ? '...' : '')); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="max-width: 200px;">
                            <div style="font-size: 13px; margin-bottom: 3px;">
                                <strong>From:</strong> <?php echo htmlspecialchars($referral['referring_doctor']); ?>
                            </div>
                            <div style="font-size: 13px; color: #7f8c8d; margin-bottom: 3px;">
                                <?php echo htmlspecialchars($referral['referring_facility']); ?>
                            </div>
                            <div style="font-size: 13px; color: #7f8c8d;">
                                <strong>To:</strong> <?php echo htmlspecialchars($referral['receiving_facility']); ?>
                            </div>
                            <div style="font-size: 13px; color: #7f8c8d;">
                                <strong>Specialty:</strong> <?php echo htmlspecialchars($referral['specialty']); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="priority-badge priority-<?php echo strtolower($referral['urgency_level']); ?>">
                            <?php echo ucfirst($referral['urgency_level']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo getStatusBadgeClass($referral['status']); ?>">
                            <?php echo ucfirst($referral['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($referral['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-sm" onclick="showReferralDetails(<?php echo $referral['id']; ?>)">View</button>
                            <?php if ($referral['status'] === 'pending'): ?>
                                <button class="btn btn-secondary btn-sm" onclick="showStatusModal(<?php echo $referral['id']; ?>)">Update</button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $referral['id']; ?>)">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($referrals)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #7f8c8d; padding: 40px;">
                        No referrals found matching the current filters.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</html>