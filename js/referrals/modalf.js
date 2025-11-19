async function getReferralData(referralId) {
    try {
        const response = await fetch(`get_referral.php?id=${referralId}`);
        const data = await response.json();
        
        if (data.success) {
            return data.referral;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}

function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Show referral details
async function showReferralDetails(referralId) {
    const content = document.getElementById('referralDetailsContent');
    content.innerHTML = '<div style="text-align:center;padding:20px;">Loading...</div>';
    showModal('referralDetailsModal');
    
    const referral = await getReferralData(referralId);
    
    if (referral) {
        content.innerHTML = generateReferralDetailsHTML(referral);
    } else {
        content.innerHTML = '<div style="text-align:center;color:red;padding:20px;">Failed to load referral details</div>';
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