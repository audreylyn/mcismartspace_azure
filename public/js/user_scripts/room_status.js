// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');

    // Auto fade-out alerts after 3 seconds
    const alerts = document.querySelectorAll('.fade-alert');
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(function(alert) {
                alert.classList.add('fade-out');
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500); // Wait for fade animation to complete
            });
        }, 3000); // 3 seconds
    }

    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded! Adding native click handlers instead.');
        // Fallback to native JavaScript if jQuery is not available
        document.querySelectorAll('.status-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.status-tab').forEach(function(t) {
                    t.classList.remove('active');
                });

                // Add active class to clicked tab
                this.classList.add('active');

                // Get status from data attribute
                var status = this.getAttribute('data-status');
                console.log('Tab clicked:', status);

                // Show/hide request cards based on status
                document.querySelectorAll('.request-card').forEach(function(card) {
                    if (status === 'all') {
                        card.style.display = '';
                    } else {
                        if (card.getAttribute('data-status') === status) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        });

        // Modal handling
        document.querySelectorAll('[data-dismiss="modal"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('requestDetailsModal').classList.remove('show');
            });
        });
    } else {
        console.log('jQuery is loaded, using jQuery event handlers');

        // jQuery handlers
        $('.status-tab').on('click', function() {
            console.log('Tab clicked with jQuery:', $(this).data('status'));

            // Update active tab
            $('.status-tab').removeClass('active');
            $(this).addClass('active');

            var status = $(this).data('status');

            // Show/hide request cards based on selected status
            if (status === 'all') {
                $('.request-card').show();
            } else {
                $('.request-card').hide();
                $('.request-card[data-status="' + status + '"]').show();
            }
        });

        // Modal handling
        $('[data-dismiss="modal"]').on('click', function() {
            $('#requestDetailsModal').removeClass('show');
        });
    }
});

// Show request details in modal
function showRequestDetails(requestId, activityName, buildingName, roomName, reservationDate, startTime, endTime, participants, purpose, statusLabel, rejectionReason, status) {
    // Don't show details modal if cancel alert is visible
    if (document.getElementById('confirmCancelModal').classList.contains('show')) {
        return;
    }

    // Prepare modal content
    var modalContent = `
        <div class="request-details-grid">
            <div class="detail-item">
                <div class="detail-label">Request ID</div>
                <div class="detail-value">${requestId}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Activity</div>
                <div class="detail-value">${activityName}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Room</div>
                <div class="detail-value">${roomName}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Building</div>
                <div class="detail-value">${buildingName}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Date</div>
                <div class="detail-value"><i class="fa fa-calendar"></i> ${reservationDate}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Time</div>
                <div class="detail-value"><i class="fa fa-clock-o"></i> ${startTime} - ${endTime}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Participants</div>
                <div class="detail-value"><i class="fa fa-users"></i> ${participants}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge badge-${status}">${statusLabel}</span>
                </div>
            </div>
        </div>

        <div class="request-section">
            <h5 class="request-section-title">
                <i class="fa fa-file-text-o"></i> Purpose
            </h5>
            <div class="purpose-content">${purpose}</div>
        </div>
    `;

    // Add rejection reason if rejected
    if (status === 'rejected' && rejectionReason) {
        modalContent += `
            <div class="rejection-reason">
                <div class="rejection-title">
                    <i class="fa fa-exclamation-circle"></i> Rejection Reason
                </div>
                <p class="rejection-text">${rejectionReason}</p>
            </div>
        `;
    }

    // Update modal content
    document.getElementById('requestDetailsContent').innerHTML = modalContent;

    // Show/hide print button based on status
    var printButton = document.getElementById('printButton');
    if (status === 'approved') {
        printButton.style.display = 'block';
        printButton.onclick = function() {
            printRequestDetails(requestId, activityName, buildingName, roomName, reservationDate, startTime, endTime, participants, purpose, statusLabel);
        };
    } else {
        printButton.style.display = 'none';
    }

    // Setup action buttons based on status
    var actionButtons = document.getElementById('actionButtons');
    actionButtons.innerHTML = '';

    if (status === 'pending') {
        var cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn-action btn-cancel';
        cancelBtn.innerHTML = '<i class="fa fa-times"></i> Cancel Request';
        cancelBtn.onclick = function() {
            // Hide the details modal first
            document.getElementById('requestDetailsModal').classList.remove('show');
            // Store the request ID in the modal for later use
            document.getElementById('confirmCancelButton').setAttribute('data-request-id', requestId);
            // Show the confirmation modal
            document.getElementById('confirmCancelModal').classList.add('show');
        };
        actionButtons.appendChild(cancelBtn);
    } else if (status === 'rejected') {
        var newRequestBtn = document.createElement('button');
        newRequestBtn.className = 'btn-action btn-new-request';
        newRequestBtn.innerHTML = '<i class="fa fa-refresh"></i> Submit New Request';
        newRequestBtn.onclick = function() {
            window.location.href = 'std_browse_room.php';
        };
        actionButtons.appendChild(newRequestBtn);
    }

    // Show the modal
    document.getElementById('requestDetailsModal').classList.add('show');
}

// Toggle request details
function toggleDetails(requestId) {
    console.log('Toggling details for request:', requestId);
    var content = document.getElementById('content-' + requestId);
    var indicator = document.getElementById('indicator-' + requestId);

    if (content.classList.contains('open')) {
        content.classList.remove('open');
        indicator.classList.remove('open');
    } else {
        content.classList.add('open');
        indicator.classList.add('open');
    }
}

// Print request details function
function printRequestDetails(requestId, activityName, buildingName, roomName, reservationDate, startTime, endTime, participants, purpose, status) {
    // Create form data to send to the PDF generation endpoint
    var formData = {
        requestId: requestId,
        activityName: activityName,
        buildingName: buildingName,
        roomName: roomName,
        reservationDate: reservationDate,
        startTime: startTime,
        endTime: endTime,
        participants: participants,
        purpose: purpose,
        status: status
    };

    // Close the modal before proceeding
    document.getElementById('requestDetailsModal').classList.remove('show');

    // Create query string for GET request
    var queryParams = new URLSearchParams();
    for (var key in formData) {
        if (formData.hasOwnProperty(key)) {
            queryParams.append(key, formData[key]);
        }
    }

    // Navigate to generate_pdf.php with query parameters
    window.location.href = 'generate_pdf.php?' + queryParams.toString();
}

// Add event listener for the confirm cancel button
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmCancelButton').addEventListener('click', function() {
        var requestId = this.getAttribute('data-request-id');
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'cancel_request.php';
        form.style.display = 'none';

        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'request_id';
        input.value = requestId;
        form.appendChild(input);

        var cancelInput = document.createElement('input');
        cancelInput.type = 'hidden';
        cancelInput.name = 'cancel_request';
        cancelInput.value = 'true';
        form.appendChild(cancelInput);

        document.body.appendChild(form);
        form.submit();
    });

    // Add event listeners for closing the confirmation modal
    document.querySelectorAll('#confirmCancelModal [data-dismiss="modal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            document.getElementById('confirmCancelModal').classList.remove('show');
            // Don't automatically show the details modal again
        });
    });

    // Close modal when clicking outside
    document.getElementById('confirmCancelModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
            // Don't automatically show the details modal again
        }
    });
});
