// Make stat cards clickable with hover effects
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card.clickable');
    
    statCards.forEach(card => {
        card.style.cursor = 'pointer';
        
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.05)';
        });
    });
});

// Auto-refresh dashboard every 2 minutes
setTimeout(() => {
    window.location.reload();
}, 120000);