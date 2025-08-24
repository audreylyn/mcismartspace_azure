
<!-- Rejection Modal -->
        <div id="rejectModal" class="modal">
            <div class="modal-content">
                <h3 class="modal-title">Rejection Reason
                    <span class="close" onclick="closeRejectModal()">&times;</span>
                </h3>
                <div class="modal-body">
                    <form id="rejectForm" method="POST">
                        <input type="hidden" id="rejectRequestId" name="request_id">
                        <div class="predefined-reasons">
                            <label>Select a reason or enter your own:</label>
                            <div class="reason-options">
                                <button type="button" class="reason-option" onclick="selectReason('Room unavailable due to maintenance')">Room unavailable</button>
                                <button type="button" class="reason-option" onclick="selectReason('Scheduling conflict with another event')">Scheduling conflict</button>
                                <button type="button" class="reason-option" onclick="selectReason('Insufficient information provided')">Insufficient info</button>
                                <button type="button" class="reason-option" onclick="selectReason('Exceeds room capacity')">Exceeds capacity</button>
                                <button type="button" class="reason-option" onclick="selectReason('Request does not meet department policy')">Policy violation</button>
                            </div>
                        </div>
                        <label for="rejection_reason">Reason for rejection:</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                        <div class="modal-footer">
                            <button type="button" onclick="closeRejectModal()" class="modal-btn modal-btn-cancel">Cancel</button>
                            <button type="submit" name="reject_request" class="modal-btn modal-btn-reject">Confirm Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Request Details Modal -->
        <div id="detailsModal" class="modal">
            <div class="modal-content">
                <h3 class="modal-title">Request Details
                    <span class="close" onclick="closeDetailsModal()">&times;</span>
                </h3>
                <div class="modal-body" id="detailsModalContent">
                    <!-- Details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeDetailsModal()" class="btn-approve">Close</button>
                </div>
            </div>
        </div>


