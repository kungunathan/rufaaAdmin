<div class="modal" id="referralDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Referral Details</h3>
            <button class="modal-close" onclick="hideModal('referralDetailsModal')">&times;</button>
        </div>
        <div class="modal-body" id="referralDetailsContent">
            <!-- Content will be loaded via JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('referralDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal" id="statusModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Referral Status</h3>
            <button class="modal-close" onclick="hideModal('statusModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="referral_id" id="statusReferralId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-input" required>
                        <option value="accepted">Accept Referral</option>
                        <option value="declined">Decline Referral</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideModal('statusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Deletion</h3>
            <button class="modal-close" onclick="hideModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this referral? This action cannot be undone and all associated data will be permanently removed.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('deleteModal')">Cancel</button>
            <form method="POST" action="" id="deleteForm" style="display: inline;">
                <input type="hidden" name="referral_id" id="deleteReferralId">
                <input type="hidden" name="delete_referral" value="1">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>