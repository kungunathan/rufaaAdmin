<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Helper function for status badge classes
function getStatusBadgeClass($status) {
    switch($status) {
        case 'open':
            return 'active';
        case 'in_progress':
            return 'pending';
        case 'resolved':
        case 'closed':
            return 'inactive';
        default:
            return '';
    }
}

$success_message = '';
$error_message = '';

// Handle issue status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_issue_status'])) {
        $issue_id = $_POST['issue_id'];
        $new_status = $_POST['status'];
        $resolution_notes = trim($_POST['resolution_notes'] ?? '');
        
        try {
            if ($new_status === 'resolved' || $new_status === 'closed') {
                // Mark as resolved/closed
                $stmt = $pdo->prepare("UPDATE issue_reports SET status = ?, resolved_by = ?, resolved_at = NOW(), resolution_notes = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $user_id, $resolution_notes, $issue_id]);
                $success_message = 'Issue marked as ' . $new_status . '!';
            } else {
                // Other status changes
                $stmt = $pdo->prepare("UPDATE issue_reports SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $issue_id]);
                $success_message = 'Issue status updated!';
            }
        } catch(PDOException $e) {
            $error_message = 'Error updating issue: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_issue'])) {
        $issue_id = $_POST['issue_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM issue_reports WHERE id = ?");
            $stmt->execute([$issue_id]);
            $success_message = 'Issue deleted successfully!';
        } catch(PDOException $e) {
            $error_message = 'Error deleting issue: ' . $e->getMessage();
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'active';
$priority_filter = $_GET['priority'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$module_filter = $_GET['module'] ?? 'all';

// Build query based on filters
$query = "SELECT ir.*, 
                 au.username as resolved_by_name,
                 au.first_name as resolver_first_name,
                 au.last_name as resolver_last_name
          FROM issue_reports ir 
          LEFT JOIN admin_users au ON ir.resolved_by = au.id 
          WHERE 1=1";

$params = [];

// Status filter
if ($status_filter === 'active') {
    $query .= " AND ir.status IN ('open', 'in_progress')";
} elseif ($status_filter === 'resolved') {
    $query .= " AND ir.status IN ('resolved', 'closed')";
} elseif ($status_filter !== 'all') {
    $query .= " AND ir.status = ?";
    $params[] = $status_filter;
}

// Priority filter
if ($priority_filter !== 'all') {
    $query .= " AND ir.priority_level = ?";
    $params[] = $priority_filter;
}

// Type filter
if ($type_filter !== 'all') {
    $query .= " AND ir.issue_type = ?";
    $params[] = $type_filter;
}

// Module filter
if ($module_filter !== 'all') {
    $query .= " AND ir.related_module = ?";
    $params[] = $module_filter;
}

$query .= " ORDER BY 
    CASE 
        WHEN ir.priority_level = 'critical' THEN 1
        WHEN ir.priority_level = 'high' THEN 2
        WHEN ir.priority_level = 'medium' THEN 3
        WHEN ir.priority_level = 'low' THEN 4
        ELSE 5
    END, 
    ir.created_at DESC";

// Fetch issues
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $issues = $stmt->fetchAll();
} catch(PDOException $e) {
    $issues = [];
    error_log("Error fetching issues: " . $e->getMessage());
}

// Get counts for stats
try {
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status IN ('open', 'in_progress') THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved
    FROM issue_reports");
    $issue_stats = $stmt->fetch();
} catch(PDOException $e) {
    $issue_stats = ['total' => 0, 'active' => 0, 'open' => 0, 'in_progress' => 0, 'resolved' => 0];
}

include 'includes/sidebar.php';
?>

<style>
.issues-filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.issue-title-container {
    max-width: 250px;
}

.issue-title {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 5px;
}

.issue-description {
    color: #7f8c8d;
    font-size: 13px;
    line-height: 1.3;
}

.issue-type-container {
    font-size: 13px;
}

.issue-type-badge {
    margin-bottom: 3px;
}

.issue-module {
    color: #7f8c8d;
}

.reporter-info {
    font-size: 13px;
}

.reporter-name {
    font-weight: 500;
}

.reporter-email {
    color: #7f8c8d;
}

.resolver-info {
    font-size: 13px;
}

.resolver-name {
    font-weight: 500;
}

.resolver-date {
    color: #7f8c8d;
}

.not-resolved {
    color: #7f8c8d;
    font-style: italic;
}

.no-issues-message {
    text-align: center;
    color: #7f8c8d;
    padding: 40px;
}

.priority-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}

.priority-critical { background-color: #fdedec; color: #e74c3c; }
.priority-high { background-color: #fef9e7; color: #f39c12; }
.priority-medium { background-color: #e8f4fd; color: #3498db; }
.priority-low { background-color: #e8f6f3; color: #27ae60; }
</style>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Reported Issues</h1>
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

    <!-- Issue Statistics -->
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

    <!-- Filters -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Issues Management</h3>
            <div class="table-controls">
                <form method="GET" action="" class="issues-filter-form">
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active Issues</option>
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Issues</option>
                        <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Open Only</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved/Closed</option>
                    </select>
                    
                    <select name="priority" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $priority_filter === 'all' ? 'selected' : ''; ?>>All Priorities</option>
                        <option value="critical" <?php echo $priority_filter === 'critical' ? 'selected' : ''; ?>>Critical</option>
                        <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                    
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

<!-- Issue Details Modal -->
<div class="modal" id="issueDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Issue Details</h3>
            <button class="modal-close" onclick="hideModal('issueDetailsModal')">&times;</button>
        </div>
        <div class="modal-body" id="issueDetailsContent">
            <!-- Content will be loaded via AJAX -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('issueDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- Resolve Issue Modal -->
<div class="modal" id="resolveIssueModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Resolve Issue</h3>
            <button class="modal-close" onclick="hideModal('resolveIssueModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="update_issue_status" value="1">
            <input type="hidden" name="issue_id" id="resolveIssueId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Resolution Status</label>
                    <select name="status" class="form-input" required>
                        <option value="resolved">Mark as Resolved</option>
                        <option value="closed">Mark as Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Resolution Notes (Optional)</label>
                    <textarea name="resolution_notes" class="form-input" rows="4" placeholder="Add any notes about how this issue was resolved..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideModal('resolveIssueModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Resolution</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Deletion</h3>
            <button class="modal-close" onclick="hideModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this issue? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('deleteModal')">Cancel</button>
            <form method="POST" action="" id="deleteForm" style="display: inline;">
                <input type="hidden" name="issue_id" id="deleteIssueId">
                <input type="hidden" name="delete_issue" value="1">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functions
function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Show issue details
function showIssueDetails(issueId) {
    fetch('get_issue_details.php?id=' + issueId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('issueDetailsContent').innerHTML = data;
            showModal('issueDetailsModal');
        })
        .catch(error => {
            document.getElementById('issueDetailsContent').innerHTML = '<p>Error loading issue details.</p>';
            showModal('issueDetailsModal');
        });
}

// Show resolve modal
function showResolveModal(issueId) {
    document.getElementById('resolveIssueId').value = issueId;
    showModal('resolveIssueModal');
}

// Delete confirmation
function confirmDelete(issueId) {
    document.getElementById('deleteIssueId').value = issueId;
    showModal('deleteModal');
}

// Auto-hide alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.display = 'none';
    });
}, 5000);

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
