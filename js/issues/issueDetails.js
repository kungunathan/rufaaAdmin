// Modal control functions
function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    ocument.getElementById(modalId).classList.remove('active');
}
d
// Show issue details 

function showIssueDetails(issueId) {
    // Create a simple details display without needing external PHP file
    const issueDetailsContent = document.getElementById('issueDetailsContent');
    issueDetailsContent.innerHTML = '<p>Loading issue details...</p>';
    
    // In a real implementation, you would fetch from server
    // For now, we'll create a simple display using existing data
    // This is a simplified version - in production, you'd want to fetch fresh data
    
    // Find the issue in the current page data (from table)
    const issueRow = document.querySelector(`tr:has(button[onclick="showIssueDetails(${issueId})"])`);
    if (issueRow) {
        const cells = issueRow.cells;
        const issueData = {
            id: issueId,
            title: cells[1].querySelector('.issue-title').textContent,
            description: 'Full description would appear here. In a real implementation, this would be fetched from the database.',
            type: cells[2].querySelector('.issue-type-badge').textContent.trim(),
            module: cells[2].querySelector('.issue-module').textContent,
            priority: cells[3].querySelector('.priority-badge').textContent,
            status: cells[4].querySelector('.status-badge').textContent,
            reporter: cells[5].querySelector('.reporter-name').textContent + ' (' + cells[5].querySelector('.reporter-email').textContent + ')',
            created: cells[6].textContent,
            resolved: cells[7].querySelector('.resolver-name') ? 
                     cells[7].querySelector('.resolver-name').textContent + ' on ' + cells[7].querySelector('.resolver-date').textContent : 
                     'Not resolved'
        };
        
        // Build the details HTML
        issueDetailsContent.innerHTML = `
            <div class="issue-details-content">
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Issue ID:</div>
                    <div class="issue-detail-value">ISS-${String(issueId).padStart(4, '0')}</div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Title:</div>
                    <div class="issue-detail-value">${issueData.title}</div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Description:</div>
                    <div class="issue-detail-value">
                        <div class="issue-description-full">${issueData.description}</div>
                    </div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Type:</div>
                    <div class="issue-detail-value">${issueData.type}</div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Module:</div>
                    <div class="issue-detail-value">${issueData.module}</div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Priority:</div>
                    <div class="issue-detail-value">
                        <span class="priority-badge priority-${issueData.priority.toLowerCase()}">${issueData.priority}</span>
                    </div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Status:</div>
                    <div class="issue-detail-value">
                        <span class="status-badge status-${getStatusClass(issueData.status)}">${issueData.status}</span>
                    </div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Reporter:</div>
                    <div class="issue-detail-value">${issueData.reporter}</div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Created:</div>
                    <div class="issue-detail-value">${issueData.created}</div>
                </div>
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Resolution:</div>
                    <div class="issue-detail-value">${issueData.resolved}</div>
                </div>
                ${issueData.resolved !== 'Not resolved' ? `
                <div class="issue-detail-row">
                    <div class="issue-detail-label">Resolution Notes:</div>
                    <div class="issue-detail-value">
                        <div class="resolution-notes">Resolution notes would appear here. These are stored in the database when an issue is resolved.</div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    } else {
        issueDetailsContent.innerHTML = '<p>Could not load issue details. The issue may have been deleted or there might be a connection issue.</p>';
    }
    
    showModal('issueDetailsModal');
}

// Helper function for status class in details modal
function getStatusClass(status) {
    const statusMap = {
        'open': 'active',
        'in progress': 'pending', 
        'resolved': 'inactive',
        'closed': 'inactive'
    };
    return statusMap[status.toLowerCase()] || '';
}

// Show resolve modal
function showResolveModal(issueId) {
    document.getElementById('resolveIssueId').value = issueId;
    showModal('resolveIssueModal');
}

// Delete confirmation
function confirmDelete(issueId) {
    document.getElementById('deleteIssueId').value = issueId;
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