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

<?php 
include 'includes/sidebar.php'; 
//Main content
include 'php/referrals/main_content.php';
//Referral modals
include 'php/referrals/modals.php';
//Styling
include 'css/referrals.css';
// Modal functions
include 'js/referrals/modalf.js';
// Search functionality for referrals table
include 'js/referrals/searchF.js';
// Generate detailed HTML for referral modal
include 'js/referrals/htmlGenerator.js';
// Helper function for status badge classes
include 'includes/footer.php'; 
?>