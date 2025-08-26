document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM fully loaded');

  // Auto fade-out alerts after 3 seconds
  const alerts = document.querySelectorAll('.fade-alert');
  if (alerts.length > 0) {
    setTimeout(function () {
      alerts.forEach(function (alert) {
        alert.classList.add('fade-out');
        setTimeout(function () {
          alert.style.display = 'none';
        }, 500); // Wait for fade animation to complete
      });
    }, 3000); // 3 seconds
  }

  // Handle tab clicks
  document.querySelectorAll('.history-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      // Remove active class from all tabs
      document.querySelectorAll('.history-tab').forEach(function (t) {
        t.classList.remove('active');
      });

      // Add active class to clicked tab
      this.classList.add('active');

      // Get filter type from data attribute
      var filterType = this.getAttribute('data-filter');
      console.log('Tab clicked:', filterType);

      // Show/hide reservation cards based on type
      document.querySelectorAll('.reservation-card').forEach(function (card) {
        if (filterType === 'all') {
          card.style.display = '';
        } else {
          if (card.getAttribute('data-type') === filterType) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        }
      });
    });
  });

  // Search functionality
  document.getElementById('searchInput').addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase().trim();

    document.querySelectorAll('.reservation-card').forEach(function (card) {
      const roomName = card.getAttribute('data-room');
      const buildingName = card.getAttribute('data-building');

      if (
        searchTerm === '' ||
        roomName.includes(searchTerm) ||
        buildingName.includes(searchTerm)
      ) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  });

  // Status filter functionality
  document
    .getElementById('statusFilter')
    .addEventListener('change', function () {
      const filterValue = this.value;

      document.querySelectorAll('.reservation-card').forEach(function (card) {
        if (filterValue === 'all') {
          card.style.display = '';
        } else {
          if (card.getAttribute('data-status') === filterValue) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        }
      });
    });

  // Modal close button handler
  document.querySelectorAll('[data-dismiss="modal"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document
        .getElementById('reservationDetailsModal')
        .classList.remove('show');
    });
  });

  // Add event listener for the confirm cancel button
  if (document.getElementById('confirmCancelButton')) {
    document
      .getElementById('confirmCancelButton')
      .addEventListener('click', function () {
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
    document
      .querySelectorAll('#confirmCancelModal [data-dismiss="modal"]')
      .forEach(function (element) {
        element.addEventListener('click', function () {
          document
            .getElementById('confirmCancelModal')
            .classList.remove('show');
          // Don't automatically show the details modal again
        });
      });

    // Close modal when clicking outside
    document
      .getElementById('confirmCancelModal')
      .addEventListener('click', function (e) {
        if (e.target === this) {
          this.classList.remove('show');
          // Don't automatically show the details modal again
        }
      });
  }
});

// Show reservation details in modal with modern two-column layout
function showReservationDetails(
  requestId,
  activityName,
  buildingName,
  roomName,
  reservationDate,
  startTime,
  endTime,
  participants,
  purpose,
  statusLabel,
  type,
  equipment,
  capacity,
  roomType,
  rejectionReason
) {
  // Remove rejectionReasonHtml variable, use only inline rendering in modalContent

  var modalContent = `
        <div class="modern-details-container">
            <div class="details-main-info">
                <div class="room-badge">
                    <div class="room-icon">
                        <i class="fa fa-building-o"></i>
                    </div>
                    <div class="room-text">
                        <div class="room-name">${roomName}</div>
                        <div class="building-name">${buildingName}</div>
                    </div>
                </div>
                <div class="status-container">
                    <span class="status-badge badge-${type}">${statusLabel}</span>
                </div>
            </div>
            
            <div class="details-columns">
                <div class="details-column">
                    <div class="detail-item">
                        <div class="detail-label">Request ID</div>
                        <div class="detail-value">${requestId}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Activity</div>
                        <div class="detail-value">${activityName}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Room Type</div>
                        <div class="detail-value">${roomType}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Capacity</div>
                        <div class="detail-value">${capacity} people</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Equipment</div>
                        <div class="detail-value">${equipment}</div>
                    </div>
                </div>
                
                <div class="details-column">
                    <div class="detail-item">
                        <div class="detail-label">Date</div>
                        <div class="detail-value">
                            <i class="fa fa-calendar"></i> ${reservationDate}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Time</div>
                        <div class="detail-value">
                            <i class="fa fa-clock-o"></i> ${startTime} - ${endTime}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Participants</div>
                        <div class="detail-value">
                            <i class="fa fa-users"></i> ${participants}
                        </div>
                    </div>
                    ${
                      (type === 'rejected' || type === 'cancelled') &&
                      rejectionReason &&
                      rejectionReason.trim() !== ''
                        ? `<div class="detail-item">
                              <div class="detail-label">Rejection Reason</div>
                              <div class="detail-value">
                                  <i class="fa fa-ban"></i> ${rejectionReason}
                              </div>
                           </div>`
                        : ''
                    }
                </div>
            </div>
            
            <div class="details-purpose">
                <h5 class="purpose-title">
                    <i class="fa fa-file-text-o"></i> Purpose
                </h5>
                <div class="purpose-content">${purpose}</div>
            </div>
        </div>
    `;

  // Update modal content
  document.getElementById('reservationDetailsContent').innerHTML = modalContent;

  // Show/hide print button based on status
  var printButton = document.getElementById('printButton');
  if (printButton) {
    if (type === 'completed' || type === 'upcoming') {
      printButton.style.display = 'inline-block';
      printButton.onclick = function () {
        printRequestDetails(
          requestId,
          activityName,
          buildingName,
          roomName,
          reservationDate,
          startTime,
          endTime,
          participants,
          purpose,
          statusLabel
        );
      };
    } else {
      printButton.style.display = 'none';
    }
  }

  // Setup action buttons based on status
  var actionButtons = document.getElementById('actionButtons');
  if (actionButtons) {
    actionButtons.innerHTML = '';

    if (type === 'pending') {
      var cancelBtn = document.createElement('button');
      cancelBtn.className = 'btn-action btn-cancel';
      cancelBtn.innerHTML = '<i class="fa fa-times"></i> Cancel Request';
      cancelBtn.onclick = function () {
        // Hide the details modal first
        document
          .getElementById('reservationDetailsModal')
          .classList.remove('show');
        // Store the request ID in the modal for later use
        document
          .getElementById('confirmCancelButton')
          .setAttribute('data-request-id', requestId);
        // Show the confirmation modal
        document.getElementById('confirmCancelModal').classList.add('show');
      };
      actionButtons.appendChild(cancelBtn);
    } else if (type === 'cancelled') {
      var newRequestBtn = document.createElement('button');
      newRequestBtn.className = 'btn-action btn-new-request';
      newRequestBtn.innerHTML =
        '<i class="fa fa-refresh"></i> Submit New Request';
      newRequestBtn.onclick = function () {
        window.location.href = 'users_browse_room.php';
      };
      actionButtons.appendChild(newRequestBtn);
    }
  }

  // Show the modal
  document.getElementById('reservationDetailsModal').classList.add('show');
}

// Print request details function
function printRequestDetails(
  requestId,
  activityName,
  buildingName,
  roomName,
  reservationDate,
  startTime,
  endTime,
  participants,
  purpose,
  status
) {
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
    status: status,
  };

  // Close the modal before proceeding
  document.getElementById('reservationDetailsModal').classList.remove('show');

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
