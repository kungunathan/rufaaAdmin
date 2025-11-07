<?php
function getActivityColor($type) {
    switch($type) {
        case 'user': return '#3498db';
        case 'issue': return '#e74c3c';
        case 'referral': return '#8e44ad';
        default: return '#95a5a6';
    }
}

function formatActivityTitle($activity) {
    switch($activity['type']) {
        case 'user':
            return 'New User: ' . htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']);
        case 'issue':
            return 'Issue: ' . htmlspecialchars($activity['issue_title']);
        case 'referral':
            return 'Referral: ' . htmlspecialchars($activity['patient_name']);
        default:
            return 'New Activity';
    }
}

function formatActivityDescription($activity) {
    switch($activity['type']) {
        case 'user':
            return htmlspecialchars($activity['email']);
        case 'issue':
            return 'Status: ' . ucfirst($activity['status']);
        case 'referral':
            return 'Status: ' . ucfirst($activity['status']);
        default:
            return 'System activity';
    }
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>