<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Handle form submissions
$success_message = '';
$error_message = '';

// Check user permissions
$current_user_role = $_SESSION['role'];
$can_manage_users = in_array($current_user_role, ['moderator', 'admin', 'super_admin']);
$can_manage_admins = in_array($current_user_role, ['admin', 'super_admin']);
$can_manage_superadmins = ($current_user_role === 'super_admin');

// Add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!$can_manage_users) {
        $error_message = 'You do not have permission to add users.';
    } else {
        try {
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'] ?? 'user';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate input
            if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
                $error_message = 'All fields are required.';
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $error_message = 'Email already exists.';
                } else {
                    // Insert new user
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$first_name, $last_name, $email, $password_hash, $role, $is_active]);
                    
                    $success_message = 'User added successfully!';
                }
            }
        } catch(PDOException $e) {
            $error_message = 'Error adding user: ' . $e->getMessage();
        }
    }
}

// Add new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    if (!$can_manage_admins) {
        $error_message = 'You do not have permission to add admin users.';
    } else {
        try {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            // Validate input
            if (empty($username) || empty($email) || empty($first_name) || empty($last_name) || empty($password)) {
                $error_message = 'All fields are required.';
            } else {
                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetch()) {
                    $error_message = 'Username or email already exists.';
                } else {
                    // Insert new admin
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, first_name, last_name, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $first_name, $last_name, $password_hash, $role]);
                    
                    $success_message = 'Admin user added successfully!';
                }
            }
        } catch(PDOException $e) {
            $error_message = 'Error adding admin: ' . $e->getMessage();
        }
    }
}

// Update user status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!$can_manage_users) {
        $error_message = 'You do not have permission to update user status.';
    } else {
        try {
            $user_id = $_POST['user_id'];
            $is_active = $_POST['is_active'];
            
            $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$is_active, $user_id]);
            
            $success_message = 'User status updated successfully!';
        } catch(PDOException $e) {
            $error_message = 'Error updating user: ' . $e->getMessage();
        }
    }
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!$can_manage_users) {
        $error_message = 'You do not have permission to delete users.';
    } else {
        try {
            $user_id = $_POST['user_id'];
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $success_message = 'User deleted successfully!';
        } catch(PDOException $e) {
            $error_message = 'Error deleting user: ' . $e->getMessage();
        }
    }
}

// Delete admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin'])) {
    $admin_id = $_POST['admin_id'];
    
    try {
        // Get the target admin's role
        $stmt = $pdo->prepare("SELECT role FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $target_admin = $stmt->fetch();
        
        if (!$target_admin) {
            $error_message = 'Admin user not found.';
        } else {
            $target_role = $target_admin['role'];
            
            // Check permissions based on roles
            if ($admin_id == $_SESSION['user_id']) {
                $error_message = 'You cannot delete your own account.';
            } elseif ($target_role === 'super_admin' && !$can_manage_superadmins) {
                $error_message = 'You do not have permission to delete super admin users.';
            } elseif ($target_role === 'admin' && !$can_manage_admins) {
                $error_message = 'You do not have permission to delete admin users.';
            } elseif ($target_role === 'moderator' && !$can_manage_admins) {
                $error_message = 'You do not have permission to delete moderator users.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                $stmt->execute([$admin_id]);
                
                $success_message = 'Admin user deleted successfully!';
            }
        }
    } catch(PDOException $e) {
        $error_message = 'Error deleting admin: ' . $e->getMessage();
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $users = [];
    error_log("Error fetching users: " . $e->getMessage());
}

// Fetch all admin users
try {
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, role, is_active, last_login, created_at FROM admin_users ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
} catch(PDOException $e) {
    $admins = [];
    error_log("Error fetching admins: " . $e->getMessage());
}
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">User Management</h1>
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
        <?php if ($can_manage_users): ?>
            <button class="btn btn-primary" onclick="showModal('addUserModal')">Add New User</button>
        <?php endif; ?>
        <?php if ($can_manage_admins): ?>
            <button class="btn btn-secondary" onclick="showModal('addAdminModal')">Add New Admin</button>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <!-- Regular Users Section -->
    <?php if ($can_manage_users): ?>
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">System Users</h3>
            <div class="table-controls">
                <input type="text" class="search-box" placeholder="Search users..." onkeyup="searchTable('usersTable', this.value)">
                <select class="filter-select" onchange="filterTable('usersTable', this.value, 1)">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <select class="filter-select" onchange="filterTable('usersTable', this.value, 4)">
                    <option value="">All Roles</option>
                    <option value="user">User</option>
                    <option value="moderator">Moderator</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
        </div>
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td>
                        <div class="user-info-cell">
                            <div class="user-avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?></div>
                            <div class="user-details">
                                <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo getRoleBadgeClass($user['role']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-sm" onclick="editUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active']; ?>)">
                                <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $user['id']; ?>, 'user')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 40px;">
                        No users found.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Admin Users Section -->
    <?php if ($can_manage_admins): ?>
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Admin Users</h3>
            <div class="table-controls">
                <input type="text" class="search-box" placeholder="Search admins..." onkeyup="searchTable('adminsTable', this.value)">
                <select class="filter-select" onchange="filterTable('adminsTable', this.value, 1)">
                    <option value="">All Roles</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="moderator">Moderator</option>
                </select>
            </div>
        </div>
        <table class="data-table" id="adminsTable">
            <thead>
                <tr>
                    <th>Admin User</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($admins as $admin): ?>
                <tr>
                    <td>
                        <div class="user-info-cell">
                            <div class="user-avatar"><?php echo strtoupper(substr($admin['first_name'], 0, 1) . substr($admin['last_name'], 0, 1)); ?></div>
                            <div class="user-details">
                                <div class="user-name"><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($admin['username']); ?> (<?php echo htmlspecialchars($admin['email']); ?>)</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge <?php echo getRoleBadgeClass($admin['role']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                        </span>
                    </td>
                    <td><?php echo $admin['last_login'] ? date('Y-m-d H:i', strtotime($admin['last_login'])) : 'Never'; ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if (canDeleteAdmin($current_user_role, $admin['role'], $admin['id'] == $_SESSION['user_id'])): ?>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $admin['id']; ?>, 'admin')">Delete</button>
                            <?php else: ?>
                                <button class="btn btn-danger btn-sm" disabled title="You cannot delete this user">Delete</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($admins)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #7f8c8d; padding: 40px;">
                        No admin users found.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Add User Modal -->
<?php if ($can_manage_users): ?>
<div class="modal" id="addUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New User</h3>
            <button class="modal-close" onclick="hideModal('addUserModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <div style="display: grid; gap: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-input" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" required minlength="6">
                    </div>
                    <?php if ($can_manage_admins): ?>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-input" required>
                            <option value="user">User</option>
                            <option value="moderator">Moderator</option>
                            <?php if ($can_manage_superadmins): ?>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="is_active" checked>
                            <span>Active User</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideModal('addUserModal')">Cancel</button>
                <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Add Admin Modal -->
<?php if ($can_manage_admins): ?>
<div class="modal" id="addAdminModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Admin</h3>
            <button class="modal-close" onclick="hideModal('addAdminModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <div style="display: grid; gap: 15px;">
                    <div>
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-input" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" required minlength="6">
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-input" required>
                            <option value="moderator">Moderator</option>
                            <option value="admin">Admin</option>
                            <?php if ($can_manage_superadmins): ?>
                                <option value="super_admin">Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideModal('addAdminModal')">Cancel</button>
                <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Deletion</h3>
            <button class="modal-close" onclick="hideModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p id="deleteMessage">Are you sure you want to delete this user? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('deleteModal')">Cancel</button>
            <form method="POST" action="" id="deleteForm" style="display: inline;">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form method="POST" action="" id="statusForm" style="display: none;">
    <input type="hidden" name="user_id" id="statusUserId">
    <input type="hidden" name="is_active" id="statusIsActive">
    <input type="hidden" name="update_status">
</form>

<script>
// Modal functions
function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Search functionality
function searchTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

// Filter functionality
function filterTable(tableId, filterValue, columnIndex) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cell = row.cells[columnIndex];
        const cellText = cell.textContent.toLowerCase();
        const filterText = filterValue.toLowerCase();
        
        if (!filterValue || cellText.includes(filterText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// User status management
function editUserStatus(userId, currentStatus) {
    document.getElementById('statusUserId').value = userId;
    document.getElementById('statusIsActive').value = currentStatus ? 0 : 1;
    document.getElementById('statusForm').submit();
}

// Delete confirmation
function confirmDelete(id, type) {
    const message = type === 'user' 
        ? 'Are you sure you want to delete this user? This action cannot be undone.'
        : 'Are you sure you want to delete this admin user? This action cannot be undone.';
    
    document.getElementById('deleteMessage').textContent = message;
    
    const form = document.getElementById('deleteForm');
    form.innerHTML = `
        <input type="hidden" name="${type}_id" value="${id}">
        <input type="hidden" name="delete_${type}">
        <button type="submit" class="btn btn-danger">Delete</button>
    `;
    
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
</style>

<?php 
// Helper functions
function getRoleBadgeClass($role) {
    switch($role) {
        case 'super_admin':
            return 'status-active';
        case 'admin':
            return 'status-pending';
        case 'moderator':
            return 'status-inactive';
        default:
            return '';
    }
}

function canDeleteAdmin($current_role, $target_role, $is_self) {
    if ($is_self) return false;
    
    switch($current_role) {
        case 'super_admin':
            return true; // Super admins can delete anyone
        case 'admin':
            return in_array($target_role, ['admin', 'moderator']); // Admins can delete admins and moderators
        case 'moderator':
            return false; // Moderators cannot delete any admin users
        default:
            return false;
    }
}

include 'includes/footer.php'; 
?>