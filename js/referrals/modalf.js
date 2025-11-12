function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Show referral details
function showReferralDetails(referralId) {
    // Create detailed HTML content for the referral
    const referral = getReferralData(referralId);
    if (referral) {
        const content = generateReferralDetailsHTML(referral);
        document.getElementById('referralDetailsContent').innerHTML = content;
        showModal('referralDetailsModal');
    }
}

// Show status update modal
function showStatusModal(referralId) {
    document.getElementById('statusReferralId').value = referralId;
    showModal('statusModal');
}

// Delete confirmation
function confirmDelete(referralId) {
    document.getElementById('deleteReferralId').value = referralId;
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