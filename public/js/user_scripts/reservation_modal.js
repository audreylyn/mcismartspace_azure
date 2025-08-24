// Room capacity validation
let selectedRoomCapacity = 0;
let isCapacityValid = true;

// Store room capacity when opening the reservation modal
function showReservationModal(roomId) {
  // Reset form fields
  document.getElementById('reservationForm').reset();

  // Find the room card with the matching roomId
  const roomCard = document.querySelector(
    `.room-card[data-room-id="${roomId}"]`
  );
  if (roomCard) {
    // Get the capacity from the data attribute
    selectedRoomCapacity = parseInt(roomCard.getAttribute('data-capacity'));

    // Set the hidden room ID input
    document.getElementById('selectedRoom').value = roomId;

    // Populate the room info in step 3
    const roomName = roomCard.querySelector('.room-name').textContent;
    const buildingName = roomCard.querySelector('.building-name').textContent;

    const roomInfoHTML = `
                            <div class="selected-room-details">
                                <h4>${roomName}</h4>
                                <p>${buildingName}</p>
                                <p><i class="fa fa-users"></i> Capacity: ${selectedRoomCapacity} persons</p>
                  <div class="rejected-reasons-box" style="background:#ffe6e6;border:1px solid #ffb3b3;border-radius:6px;padding:12px;margin-top:10px;">
                    <div style="font-weight:bold;color:#cc0000;margin-bottom:6px;"><i class="fa fa-exclamation-circle"></i> Possible Reasons for Rejection:</div>
                    <ul style="padding-left:18px;margin-bottom:0;color:#cc0000;font-size:14px;">
                      <li>Room is already reserved for your selected date/time</li>
                      <li>Number of participants exceeds room capacity</li>
                      <li>Activity type not allowed in this room</li>
                      <li>Incomplete or invalid reservation details</li>
                      <li>Room is under maintenance or unavailable</li>
                    </ul>
                  </div>
                            </div>
                        `;

    document.getElementById('selectedRoomInfo').innerHTML = roomInfoHTML;
  }

  // Reset steps to first step
  showStep(1);

  // Reset validation state
  isCapacityValid = true;
  updateNextButtonState();

  // Show the modal
  $('#reservationModal').modal('show');
}

// Function to update Next button state
function updateNextButtonState() {
  const nextButton = document.getElementById('toStep2');
  nextButton.disabled = !isCapacityValid;
  nextButton.style.opacity = isCapacityValid ? '1' : '0.5';
  nextButton.style.cursor = isCapacityValid ? 'pointer' : 'not-allowed';
}

// Add validation to check participants against room capacity
document.getElementById('toStep2').addEventListener('click', function (e) {
  const participantsInput = document.getElementById('participants');
  const capacityError = document.getElementById('capacityError');
  const participantsCount = parseInt(participantsInput.value);

  // Basic form validation
  if (
    !participantsInput.checkValidity() ||
    isNaN(participantsCount) ||
    participantsCount <= 0
  ) {
    participantsInput.classList.add('is-invalid');
    participantsInput.style.borderColor = '#e74c3c';
    capacityError.style.display = 'none';
    e.preventDefault();
    return false;
  }

  // Check if participants exceed room capacity
  if (participantsCount > selectedRoomCapacity) {
    participantsInput.classList.add('is-invalid');
    participantsInput.style.borderColor = '#e74c3c';
    capacityError.textContent = `Error: The number of participants (${participantsCount}) exceeds the room capacity (${selectedRoomCapacity}).`;
    capacityError.style.display = 'block';
    isCapacityValid = false;
    updateNextButtonState();
    e.preventDefault();
    return false;
  }

  // Clear any previous error
  participantsInput.classList.remove('is-invalid');
  participantsInput.style.borderColor = '';
  capacityError.style.display = 'none';

  // Proceed to step 2
  showStep(2);
});

// Add real-time validation as user types in participant count
document.addEventListener('DOMContentLoaded', function () {
  const participantsInput = document.getElementById('participants');
  const capacityError = document.getElementById('capacityError');
  const dateInput = document.getElementById('reservationDate');

  // Set minimum date to tomorrow
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const tomorrowFormatted = tomorrow.toISOString().split('T')[0];

  // Set min attribute and default value to tomorrow
  dateInput.setAttribute('min', tomorrowFormatted);
  dateInput.value = tomorrowFormatted;

  // Prevent selecting dates before tomorrow
  dateInput.addEventListener('change', function () {
    const selectedDate = new Date(this.value);
    if (selectedDate < tomorrow) {
      this.value = tomorrowFormatted;
    }
  });

  participantsInput.addEventListener('input', function () {
    const participantsCount = parseInt(this.value);

    // Skip validation if room not selected yet or input empty
    if (!selectedRoomCapacity || !this.value) {
      this.style.borderColor = '';
      capacityError.style.display = 'none';
      isCapacityValid = true;
      updateNextButtonState();
      return;
    }

    // Basic validation
    if (isNaN(participantsCount) || participantsCount <= 0) {
      this.style.borderColor = '#e74c3c';
      capacityError.style.display = 'none';
      isCapacityValid = false;
      updateNextButtonState();
      return;
    }

    // Check against room capacity
    if (participantsCount > selectedRoomCapacity) {
      this.style.borderColor = '#e74c3c';
      capacityError.textContent = `Error: The number of participants (${participantsCount}) exceeds the room capacity (${selectedRoomCapacity}).`;
      capacityError.style.display = 'block';
      isCapacityValid = false;
    } else {
      this.style.borderColor = '#2ecc71';
      capacityError.style.display = 'none';
      isCapacityValid = true;
    }
    updateNextButtonState();
  });
});

// Function to show a specific step
function showStep(stepNumber) {
  // Hide all steps
  document.querySelectorAll('.step-content').forEach((step) => {
    step.classList.remove('active');
  });

  // Remove active class from all step items
  document.querySelectorAll('.step-item').forEach((item) => {
    item.classList.remove('active');
  });

  // Show the specified step
  document.getElementById(`step${stepNumber}`).classList.add('active');
  document.getElementById(`step${stepNumber}Item`).classList.add('active');
}

// Hook up the back buttons
document.getElementById('backToStep1').addEventListener('click', function () {
  showStep(1);
});

document.getElementById('backToStep2').addEventListener('click', function () {
  showStep(2);
});

// Handle date and time selection
document
  .getElementById('reservationDate')
  .addEventListener('change', function () {
    updateAvailableTimes();
  });

function updateAvailableTimes() {
  const dateInput = document.getElementById('reservationDate');
  const selectedDate = new Date(dateInput.value);
  const startTimeSelect = document.getElementById('startTime');
  const today = new Date();

  // Reset the time options
  startTimeSelect.selectedIndex = 0;

  // Enable all time slots by default
  Array.from(startTimeSelect.options).forEach((option) => {
    option.disabled = false;
  });

  // If the selected date is tomorrow, disable past times
  if (
    selectedDate.toDateString() ===
    new Date(today.getTime() + 24 * 60 * 60 * 1000).toDateString()
  ) {
    const currentHour = today.getHours();

    // Disable time slots that have passed for tomorrow
    Array.from(startTimeSelect.options).forEach((option) => {
      if (option.value) {
        const timeValue = option.value.split(':');
        const hour = parseInt(timeValue[0]);

        if (hour <= currentHour) {
          option.disabled = true;
        }
      }
    });
  }
}

// Hook up the next button for step 2 to step 3
document.getElementById('toStep3').addEventListener('click', function () {
  // Add validation for step 2 if needed
  showStep(3);
});
