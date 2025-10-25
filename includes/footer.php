    </div>

    <!-- Issue Details Modal -->
    <div class="modal" id="issueModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Issue Details</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="issue-details">
                    <div class="detail-group">
                        <div class="detail-label">Issue ID</div>
                        <div class="detail-value" id="modal-issue-id">ISS-2023-0042</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Title</div>
                        <div class="detail-value" id="modal-issue-title">Referral form not submitting data</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Priority</div>
                        <div class="detail-value">
                            <span class="priority-badge priority-critical" id="modal-issue-priority">Critical</span>
                        </div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge status-active" id="modal-issue-status">Open</span>
                        </div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Reporter</div>
                        <div class="detail-value" id="modal-issue-reporter">Dr. John Smith</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Reported Date</div>
                        <div class="detail-value" id="modal-issue-date">2023-10-15</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Issue Type</div>
                        <div class="detail-value" id="modal-issue-type">Technical Problem</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Related Module</div>
                        <div class="detail-value" id="modal-issue-module">Referrals</div>
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Description</div>
                    <div class="detail-value" id="modal-issue-description">
                        When submitting a referral form, the page hangs and eventually times out. 
                        No error message is displayed. This happens consistently for all referral types.
                        Users are unable to create new referrals until this is resolved.
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Impact</div>
                    <div class="detail-value" id="modal-issue-impact">
                        This is blocking all referral creation across the system. 
                        Patient transfers are being delayed, affecting patient care.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Assign to Me</button>
                <button class="btn btn-secondary">Mark as In Progress</button>
                <button class="btn btn-outline">Close Issue</button>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>