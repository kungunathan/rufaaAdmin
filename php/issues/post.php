<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_issue_status'])) {
        $issue_id = $_POST['issue_id'];
        $new_status = $_POST['status'];
        $resolution_notes = trim($_POST['resolution_notes'] ?? '');
        
        try {
            if ($new_status === 'resolved' || $new_status === 'closed') {
                // Mark as resolved/closed with resolution details
                $stmt = $pdo->prepare("UPDATE issue_reports SET status = ?, resolved_by = ?, resolved_at = NOW(), resolution_notes = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $user_id, $resolution_notes, $issue_id]);
                $success_message = 'Issue marked as ' . $new_status . '!';
            } else {
                // Other status changes without resolution details
                $stmt = $pdo->prepare("UPDATE issue_reports SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $issue_id]);
                $success_message = 'Issue status updated!';
            }
        } catch(PDOException $e) {
            $error_message = 'Error updating issue: ' . $e->getMessage();
        }
    }
    
    // Handle issue deletion
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
?>