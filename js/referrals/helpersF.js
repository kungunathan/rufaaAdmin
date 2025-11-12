function getStatusBadgeClass(status) {
    switch(status) {
        case 'pending': return 'pending';
        case 'accepted': return 'accepted';
        case 'declined': return 'declined';
        default: return '';
    }
}

// Helper function for status badge classes
function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending': return 'pending';
        case 'accepted': return 'accepted';
        case 'declined': return 'declined';
        default: return '';
    }
}
