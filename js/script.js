document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding content
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === `${tabId}-tab`) {
                    content.classList.add('active');
                }
            });
        });
    });
    
    // Search functionality
    const searchBoxes = document.querySelectorAll('.search-box');
    searchBoxes.forEach(box => {
        box.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.table-container').querySelector('tbody');
            if (!table) return;
            
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Filter functionality
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            console.log(`Filter changed to: ${this.value}`);
            // In a real app, this would filter the data
        });
    });
    
    // Modal functionality
    const modal = document.getElementById('issueModal');
    const viewIssueButtons = document.querySelectorAll('.view-issue');
    const closeModal = document.querySelector('.modal-close');
    
    if (viewIssueButtons && modal) {
        viewIssueButtons.forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const cells = row.querySelectorAll('td');
                
                // Populate modal with data from the row
                document.getElementById('modal-issue-id').textContent = cells[0].textContent;
                document.getElementById('modal-issue-title').textContent = cells[1].textContent;
                document.getElementById('modal-issue-reporter').textContent = cells[2].textContent;
                document.getElementById('modal-issue-date').textContent = cells[5].textContent;
                
                // Show modal
                modal.classList.add('active');
            });
        });
        
        closeModal.addEventListener('click', function() {
            modal.classList.remove('active');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }
    
    // User action buttons
    const actionButtons = document.querySelectorAll('.action-buttons .btn');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            const row = this.closest('tr');
            const userName = row.querySelector('.user-name')?.textContent;
            
            if (action === 'Deactivate' || action === 'Activate' || action === 'Approve') {
                alert(`${action} action performed for ${userName}`);
            }
        });
    });
});