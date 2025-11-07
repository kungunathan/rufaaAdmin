<?php
if ($status_filter === 'active') {
    $query .= " AND ir.status IN ('open', 'in_progress')";
} elseif ($status_filter === 'resolved') {
    $query .= " AND ir.status IN ('resolved', 'closed')";
} elseif ($status_filter !== 'all') {
    $query .= " AND ir.status = ?";
    $params[] = $status_filter;
}

// Priority filter conditions
if ($priority_filter !== 'all') {
    $query .= " AND ir.priority_level = ?";
    $params[] = $priority_filter;
}

// Type filter conditions
if ($type_filter !== 'all') {
    $query .= " AND ir.issue_type = ?";
    $params[] = $type_filter;
}

// Module filter conditions
if ($module_filter !== 'all') {
    $query .= " AND ir.related_module = ?";
    $params[] = $module_filter;
}

// Order by priority and creation date
$query .= " ORDER BY 
    CASE 
        WHEN ir.priority_level = 'critical' THEN 1
        WHEN ir.priority_level = 'high' THEN 2
        WHEN ir.priority_level = 'medium' THEN 3
        WHEN ir.priority_level = 'low' THEN 4
        ELSE 5
    END, 
    ir.created_at DESC";

// Fetch issues based on filters
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $issues = $stmt->fetchAll();
} catch(PDOException $e) {
    $issues = [];
    error_log("Error fetching issues: " . $e->getMessage());
}

// Get counts for statistics dashboard
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