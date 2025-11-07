<div class="modal" id="issueDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Issue Details</h3>
            <button class="modal-close" onclick="hideModal('issueDetailsModal')">&times;</button>
        </div>
        <div class="modal-body" id="issueDetailsContent">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('issueDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- Resolve Issue Modal - For marking issues as resolved/closed -->
<div class="modal" id="resolveIssueModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Resolve Issue</h3>
            <button class="modal-close" onclick="hideModal('resolveIssueModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="update_issue_status" value="1">
            <input type="hidden" name="issue_id" id="resolveIssueId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Resolution Status</label>
                    <select name="status" class="form-input" required>
                        <option value="resolved">Mark as Resolved</option>
                        <option value="closed">Mark as Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Resolution Notes (Optional)</label>
                    <textarea name="resolution_notes" class="form-input" rows="4" placeholder="Add any notes about how this issue was resolved..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideModal('resolveIssueModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Resolution</button>
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
            <p>Are you sure you want to delete this issue? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="hideModal('deleteModal')">Cancel</button>
            <form method="POST" action="" id="deleteForm" style="display: inline;">
                <input type="hidden" name="issue_id" id="deleteIssueId">
                           <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>  <input type="hidden" name="delete_issue" value="1">