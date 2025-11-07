<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Helper functions

include 'php/index/helper.php'

// Fetch comprehensive dashboard statistics

?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<?php include 'php/index/main_content.php'; ?>

<script>
// Make stat cards clickable with hover effects
<?php include 'js/index/hover.js'; ?>

// Auto-refresh dashboard every 2 minutes
<?php include 'js/index/timeout.js' ?>
</script>

<style>
<?php include 'css/index.css'; ?>
</style>

<?php include 'includes/footer.php'; ?>

