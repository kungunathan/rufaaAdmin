<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

$success_message = '';
$error_message = '';

// Handle delete referral
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_referral'])) {
    try {
        $referral_id = $_POST['referral_id'];
        
        $stmt = $pdo->prepare("DELETE FROM referrals WHERE id = ?");
        $stmt->execute([$referral_id]);
        
        $success_message = 'Referral deleted successfully!';
    } catch(PDOException $e) {
        $error_message = 'Error deleting referral: ' . $e->getMessage();
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $referral_id = $_POST['referral_id'];
        $new_status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE referrals SET status = ?, responded_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $referral_id]);
        
        $success_message = 'Referral status updated successfully!';
    } catch(PDOException $e) {
        $error_message = 'Error updating referral status: ' . $e->getMessage();
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$urgency_filter = $_GET['urgency'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';

// Build query based on filters
$query = "SELECT * FROM referrals WHERE 1=1";
$params = [];

// Status filter
if ($status_filter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

// Urgency filter
if ($urgency_filter !== 'all') {
    $query .= " AND urgency_level = ?";
    $params[] = $urgency_filter;
}

// Type filter
if ($type_filter !== 'all') {
    $query .= " AND type = ?";
    $params[] = $type_filter;
}

$query .= " ORDER BY 
    CASE 
        WHEN urgency_level = 'emergency' THEN 1
        WHEN urgency_level = 'urgent' THEN 2
        WHEN urgency_level = 'routine' THEN 3
        ELSE 4
    END, 
    created_at DESC";

// Fetch referrals
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $referrals = $stmt->fetchAll();
} catch(PDOException $e) {
    $referrals = [];
    error_log("Error fetching referrals: " . $e->getMessage());
}

// Get counts for stats
try {
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined,
        SUM(CASE WHEN urgency_level = 'emergency' THEN 1 ELSE 0 END) as emergency,
        SUM(CASE WHEN urgency_level = 'urgent' THEN 1 ELSE 0 END) as urgent
    FROM referrals");
    $referral_stats = $stmt->fetch();
} catch(PDOException $e) {
    $referral_stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'declined' => 0, 'emergency' => 0, 'urgent' => 0];
}
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
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

<!-- Referral Details Modal -->
<div class="modal" id="referralDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Referral Details</h3>
            <button class="modal-close" onclick="hideModal('referralDetailsModal')">&times;</button>
        </div>
        <div class="modal-body" id="referralDetailsContent">
            <!-- Content will be loaded via JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('referralDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal" id="statusModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Referral Status</h3>
            <button class="modal-close" onclick="hideModal('statusModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="referral_id" id="statusReferralId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-input" required>
                        <option value="accepted">Accept Referral</option>
                        <option value="declined">Decline Referral</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideModal('statusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
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
            <p>Are you sure you want to delete this referral? This action cannot be undone and all associated data will be permanently removed.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('deleteModal')">Cancel</button>
            <form method="POST" action="" id="deleteForm" style="display: inline;">
                <input type="hidden" name="referral_id" id="deleteReferralId">
                <input type="hidden" name="delete_referral" value="1">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

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
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-label {
    color: #7f8c8d;
    font-size: 14px;
}

.priority-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.priority-emergency {
    background-color: #fdedec;
    color: #e74c3c;
}

.priority-urgent {
    background-color: #fef9e7;
    color: #f39c12;
}

.priority-routine {
    background-color: #e8f6f3;
    color: #27ae60;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-pending {
    background-color: #fef9e7;
    color: #f39c12;
}

.status-accepted {
    background-color: #e8f6f3;
    color: #27ae60;
}

.status-declined {
    background-color: #fdedec;
    color: #e74c3c;
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

.referral-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.detail-group {
    margin-bottom: 15px;
}

.detail-label {
    font-weight: 500;
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 5px;
}

.detail-value {
    color: #2c3e50;
    line-height: 1.4;
}

.medical-notes {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
}

@media (max-width: 768px) {
    .referral-details-grid {
        grid-template-columns: 1fr;
    }
    
    .table-controls {
        flex-direction: column;
        gap: 10px;
    }
    
    .action-buttons-container {
        flex-direction: column;
    }
    
    .action-buttons-container .btn {
        text-align: center;
    }
}
</style>

<script>
// Modal functions
function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Show referral details
function showReferralDetails(referralId) {
    // Create detailed HTML content for the referral
    const referral = getReferralData(referralId);
    if (referral) {
        const content = generateReferralDetailsHTML(referral);
        document.getElementById('referralDetailsContent').innerHTML = content;
        showModal('referralDetailsModal');
    }
}

// Show status update modal
function showStatusModal(referralId) {
    document.getElementById('statusReferralId').value = referralId;
    showModal('statusModal');
}

// Delete confirmation
function confirmDelete(referralId) {
    document.getElementById('deleteReferralId').value = referralId;
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

// Search functionality for referrals table
function searchReferrals() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('referralsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// Helper function to get referral data (in a real app, this would be an API call)
function getReferralData(referralId) {
    // This is a simplified version. In a real application, you would fetch this data from the server
    const referrals = <?php echo json_encode($referrals); ?>;
    return referrals.find(ref => ref.id == referralId);
}

// Generate detailed HTML for referral modal
function generateReferralDetailsHTML(referral) {
    return `
        <div class="referral-details-grid">
            <div>
                <div class="detail-group">
                    <div class="detail-label">Referral ID</div>
                    <div class="detail-value">REF-${String(referral.id).padStart(4, '0')}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Patient Name</div>
                    <div class="detail-value">${referral.patient_name}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Patient ID</div>
                    <div class="detail-value">${referral.patient_id}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Age & Gender</div>
                    <div class="detail-value">${referral.patient_age} years, ${referral.patient_gender}</div>
                </div>
            </div>
            <div>
                <div class="detail-group">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="status-badge status-${getStatusBadgeClass(referral.status)}">
                            ${referral.status.charAt(0).toUpperCase() + referral.status.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Urgency Level</div>
                    <div class="detail-value">
                        <span class="priority-badge priority-${referral.urgency_level.toLowerCase()}">
                            ${referral.urgency_level.charAt(0).toUpperCase() + referral.urgency_level.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Referral Type</div>
                    <div class="detail-value">${referral.type.charAt(0).toUpperCase() + referral.type.slice(1)}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Created</div>
                    <div class="detail-value">${new Date(referral.created_at).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</div>
                </div>
            </div>
        </div>
        
        <div class="referral-details-grid">
            <div>
                <div class="detail-group">
                    <div class="detail-label">Referring Doctor</div>
                    <div class="detail-value">${referral.referring_doctor}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Referring Facility</div>
                    <div class="detail-value">${referral.referring_facility}</div>
                </div>
            </div>
            <div>
                <div class="detail-group">
                    <div class="detail-label">Receiving Facility</div>
                    <div class="detail-value">${referral.receiving_facility}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Specialty</div>
                    <div class="detail-value">${referral.specialty}</div>
                </div>
            </div>
        </div>
        
        <div class="medical-notes">
            <div class="detail-group">
                <div class="detail-label">Condition Description</div>
                <div class="detail-value">${referral.condition_description || 'Not specified'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Symptoms</div>
                <div class="detail-value">${referral.symptoms || 'Not specified'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Medical History</div>
                <div class="detail-value">${referral.medical_history || 'Not specified'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Current Medications</div>
                <div class="detail-value">${referral.current_medications || 'Not specified'}</div>
            </div>
        </div>
        
        ${referral.additional_notes ? `
        <div class="detail-group">
            <div class="detail-label">Additional Notes</div>
            <div class="detail-value">${referral.additional_notes}</div>
        </div>
        ` : ''}
        
        ${referral.feedback ? `
        <div class="detail-group">
            <div class="detail-label">Feedback</div>
            <div class="detail-value">${referral.feedback}</div>
        </div>
        ` : ''}
        
        ${referral.responded_at ? `
        <div class="detail-group">
            <div class="detail-label">Responded At</div>
            <div class="detail-value">${new Date(referral.responded_at).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })}</div>
        </div>
        ` : ''}
    `;
}

// Helper function for status badge classes
function getStatusBadgeClass(status) {
    switch(status) {
        case 'pending': return 'pending';
        case 'accepted': return 'accepted';
        case 'declined': return 'declined';
        default: return '';
    }
}
</script>

<?php 
// Helper function for status badge classes
function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending': return 'pending';
        case 'accepted': return 'accepted';
        case 'declined': return 'declined';
        default: return '';
    }
}

include 'includes/footer.php'; 
?>