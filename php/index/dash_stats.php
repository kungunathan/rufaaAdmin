<?php
try {
    // User Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE is_active = 1");
    $active_users = $stmt->fetch()['active_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_admins FROM admin_users WHERE is_active = 1");
    $total_admins = $stmt->fetch()['total_admins'];
    
    // Issue Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_issues FROM issue_reports");
    $total_issues = $stmt->fetch()['total_issues'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending_issues FROM issue_reports WHERE status = 'open'");
    $pending_issues = $stmt->fetch()['pending_issues'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as resolved_issues FROM issue_reports WHERE status IN ('resolved', 'closed')");
    $resolved_issues = $stmt->fetch()['resolved_issues'];
    
    // Referral Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_referrals FROM referrals");
    $total_referrals = $stmt->fetch()['total_referrals'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending_referrals FROM referrals WHERE status = 'pending'");
    $pending_referrals = $stmt->fetch()['pending_referrals'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as accepted_referrals FROM referrals WHERE status = 'accepted'");
    $accepted_referrals = $stmt->fetch()['accepted_referrals'];
    
    // System Statistics
    $stmt = $pdo->query("SELECT COUNT(*) as active_sessions FROM user_sessions WHERE expires_at > NOW()");
    $active_sessions = $stmt->fetch()['active_sessions'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as unread_alerts FROM alerts WHERE is_read = 0");
    $unread_alerts = $stmt->fetch()['unread_alerts'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as capacity_entries FROM capacity WHERE available_capacity > 0");
    $capacity_entries = $stmt->fetch()['capacity_entries'];
    
    // Recent activity data
    $stmt = $pdo->query("
        SELECT 'user' as type, first_name, last_name, email, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_users = $stmt->fetchAll();
    
    $stmt = $pdo->query("
        SELECT 'issue' as type, issue_title, status, created_at 
        FROM issue_reports 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_issues = $stmt->fetchAll();
    
    $stmt = $pdo->query("
        SELECT 'referral' as type, patient_name, status, created_at 
        FROM referrals 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_referrals = $stmt->fetchAll();
    
    // Combine and sort recent activity
    $recent_activities = array_merge($recent_users, $recent_issues, $recent_referrals);
    usort($recent_activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $recent_activities = array_slice($recent_activities, 0, 8); // Get top 8 most recent
    
} catch(PDOException $e) {
    error_log("Dashboard data fetch error: " . $e->getMessage());
    // Initialize all variables with default values
    $total_users = $active_users = $total_admins = $total_issues = $pending_issues = $resolved_issues = 0;
    $total_referrals = $pending_referrals = $accepted_referrals = $active_sessions = $unread_alerts = $capacity_entries = 0;
    $recent_activities = [];
}
?>