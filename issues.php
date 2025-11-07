<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Helper function for status badge classes
// Returns appropriate CSS class based on issue status for styling
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

// Handle issue status updates via POST requests
include 'php/issues/post.php';

// Get filter parameters from URL with default values
$status_filter = $_GET['status'] ?? 'active';
$priority_filter = $_GET['priority'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$module_filter = $_GET['module'] ?? 'all';

// Build query based on filters with parameter binding for security
$query = "SELECT ir.*, 
                 au.username as resolved_by_name,
                 au.first_name as resolver_first_name,
                 au.last_name as resolver_last_name
          FROM issue_reports ir 
          LEFT JOIN admin_users au ON ir.resolved_by = au.id 
          WHERE 1=1";

$params = [];

// Status filter conditions
include 'php/issues/conditions.php';
?>

<style>
<?php include 'css/issues.css';?>
</style>

<!-- Main Content Section -->
<?php include 'php/issues/main.php';?>

<!-- Issue Details Modal - Shows complete issue information -->
<?php include 'php/issues/modals.php';?>

   

<script>
<?php include 'js/issues/issueDetails.js';?>
</script>

<?php include 'includes/footer.php'; ?>