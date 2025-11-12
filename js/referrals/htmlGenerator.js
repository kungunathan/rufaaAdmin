function generateReferralDetailsHTML(referral) {
    return `
        <div class="referral-details-grid">
            <div>
                <div class="detail-group">
                    <div class="detail-label">Referral ID</div>
                    <div class="detail-value">REF-${String(referral.id).padStart(4, '0')}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Patient Name</div>
                    <div class="detail-value">${referral.patient_name}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Patient ID</div>
                    <div class="detail-value">${referral.patient_id}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Age & Gender</div>
                    <div class="detail-value">${referral.patient_age} years, ${referral.patient_gender}</div>
                </div>
            </div>
            <div>
                <div class="detail-group">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="status-badge status-${getStatusBadgeClass(referral.status)}">
                            ${referral.status.charAt(0).toUpperCase() + referral.status.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Urgency Level</div>
                    <div class="detail-value">
                        <span class="priority-badge priority-${referral.urgency_level.toLowerCase()}">
                            ${referral.urgency_level.charAt(0).toUpperCase() + referral.urgency_level.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Referral Type</div>
                    <div class="detail-value">${referral.type.charAt(0).toUpperCase() + referral.type.slice(1)}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Created</div>
                    <div class="detail-value">${new Date(referral.created_at).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</div>
                </div>
            </div>
        </div>
        
        <div class="referral-details-grid">
            <div>
                <div class="detail-group">
                    <div class="detail-label">Referring Doctor</div>
                    <div class="detail-value">${referral.referring_doctor}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Referring Facility</div>
                    <div class="detail-value">${referral.referring_facility}</div>
                </div>
            </div>
            <div>
                <div class="detail-group">
                    <div class="detail-label">Receiving Facility</div>
                    <div class="detail-value">${referral.receiving_facility}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Specialty</div>
                    <div class="detail-value">${referral.specialty}</div>
                </div>
            </div>
        </div>
        
        <div class="medical-notes">
            <div class="detail-group">
                <div class="detail-label">Condition Description</div>
                <div class="detail-value">${referral.condition_description || 'Not specified'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Symptoms</div>
                <div class="detail-value">${referral.symptoms || 'Not specified'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Medical History</div>
                <div class="detail-value">${referral.medical_history || 'Not specified'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Current Medications</div>
                <div class="detail-value">${referral.current_medications || 'Not specified'}</div>
            </div>
        </div>
        
        ${referral.additional_notes ? `
        <div class="detail-group">
            <div class="detail-label">Additional Notes</div>
            <div class="detail-value">${referral.additional_notes}</div>
        </div>
        ` : ''}
        
        ${referral.feedback ? `
        <div class="detail-group">
            <div class="detail-label">Feedback</div>
            <div class="detail-value">${referral.feedback}</div>
        </div>
        ` : ''}
        
        ${referral.responded_at ? `
        <div class="detail-group">
            <div class="detail-label">Responded At</div>
            <div class="detail-value">${new Date(referral.responded_at).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })}</div>
        </div>
        ` : ''}
    `;
}
