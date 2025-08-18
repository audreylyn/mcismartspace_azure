// Room details modal functionality
function showRoomDetails(roomId) {
    // Show modal with loading indicator
    $('#roomDetailsModal').modal('show');

    // Fetch room details via AJAX
    $.ajax({
        url: 'get_room_details.php',
        type: 'GET',
        data: {
            room_id: roomId
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // Load template
                let template = $('#roomDetailsTemplate').html();

                // Set status class and icon
                let statusClass = 'success';
                let statusIcon = 'check';
                let statusText = data.room.RoomStatus.charAt(0).toUpperCase() + data.room.RoomStatus.slice(1);
                let statusTooltip = '';

                if (data.room.RoomStatus === 'occupied') {
                    statusClass = 'warning';
                    statusIcon = 'warning';

                    // Add availability time if provided
                    if (data.availableTime) {
                        statusTooltip = ' data-toggle="tooltip" title="' + data.availableTime + '"';
                    }
                } else if (data.room.RoomStatus === 'maintenance') {
                    statusClass = 'danger';
                    statusIcon = 'wrench';
                }

                // Build equipment list
                let equipmentList = '';
                if (data.equipment && data.equipment.length > 0) {
                    data.equipment.forEach(function(item) {
                        let statusClass = 'status-' + item.status;
                        let conditionClass = 'condition-' + item.statusCondition;
                        equipmentList += `
                            <div class="equipment-item">
                                <span class="equipment-name">${item.name}</span>
                                <div>
                                    <span class="equipment-condition ${conditionClass}">Condition: ${item.statusCondition.replace('_', ' ')}</span>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    equipmentList = '<div class="no-equipment">No equipment found for this room.</div>';
                }

                // Create reserve button based on room status
                let reserveButton = '';
                if (data.room.RoomStatus === 'available') {
                    reserveButton = ''; // Remove the reserve button
                } else {
                    reserveButton = `
                        <button class="btn btn-secondary" disabled>
                            <i class="fa fa-ban"></i> Room Not Available
                        </button>
                    `;
                }

                // Replace placeholders in template
                template = template
                    .replace('{roomName}', data.room.room_name)
                    .replace(/{buildingName}/g, data.room.building_name)
                    .replace('{roomType}', data.room.room_type)
                    .replace('{capacity}', data.room.capacity)
                    .replace(/{statusText}/g, statusText)
                    .replace('{statusClass}', statusClass)
                    .replace('{statusIcon}', statusIcon)
                    .replace('{statusTooltip}', statusTooltip)
                    .replace('{equipmentList}', equipmentList)
                    .replace('{reserveButton}', reserveButton);

                // Set the modal title
                $('#roomDetailsModalLabel').text(data.room.room_name + ' Details');

                // Update modal content
                $('#roomDetailsContent').html(template);

                // Initialize tooltips in the modal
                $('[data-toggle="tooltip"]').tooltip();
            } else {
                $('#roomDetailsContent').html('<div class="alert alert-danger">Error loading room details.</div>');
            }
        },
        error: function() {
            $('#roomDetailsContent').html('<div class="alert alert-danger">Error connecting to server. Please try again later.</div>');
        }
    });
}

// Close modal when clicking the close button
$(document).on('click', '[data-dismiss="modal"]', function() {
    $('#roomDetailsModal').modal('hide');
});

// Initialize Bootstrap modal functionality
$.fn.modal = function(action) {
    if (action === 'show') {
        $(this).fadeIn('fast').addClass('show');
        $('body').addClass('modal-open').append('<div class="modal-backdrop fade show"></div>');
    } else if (action === 'hide') {
        $(this).fadeOut('fast').removeClass('show');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }
};

// Filter and search functionality
$(document).ready(function() {
    console.log('jQuery is loaded and document is ready');
    // Initialize the room filters
    initializeFilters();

    // Initialize the capacity slider fill
    const slider = document.getElementById('capacitySlider');
    const percent = (slider.value / slider.max) * 100;
    slider.style.setProperty('--value-percent', percent + '%');

    // Handle building checkboxes
    $('.building-checkbox').change(function() {
        updateBuildingCount();
    });

    // Handle room type checkboxes
    $('.roomtype-checkbox').change(function() {
        updateRoomTypeCount();
    });

    // Handle capacity slider
    $('#capacitySlider').on('input', function() {
        updateCapacityLabel();

        // Update the slider fill based on value
        const percent = ($(this).val() / $(this).attr('max')) * 100;
        this.style.setProperty('--value-percent', percent + '%');
    });

    // Handle equipment toggle
    $('#hasEquipment').change(function() {
        updateFilterTags();
    });

    // Handle availability toggle
    $('#onlyAvailable').change(function() {
        updateFilterTags();
    });

    // Apply filters button - Direct form submission
    $('#applyFilters').click(function() {
        // Collect all filter parameters
        const selectedBuildings = $('.building-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const selectedRoomTypes = $('.roomtype-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const minCapacity = parseInt($('#capacitySlider').val());
        const hasEquipment = $('#hasEquipment').is(':checked');
        const onlyAvailable = $('#onlyAvailable').is(':checked');
        const searchQuery = $('#searchRooms').val().trim();

        // Clear existing hidden inputs
        $('#hiddenInputsContainer').empty();

        // Add hidden inputs for all selected values
        if (selectedBuildings.length > 0) {
            selectedBuildings.forEach(id => {
                $('#hiddenInputsContainer').append(`<input type="hidden" name="building_ids[]" value="${id}">`);
            });
        }

        if (selectedRoomTypes.length > 0) {
            selectedRoomTypes.forEach(type => {
                $('#hiddenInputsContainer').append(`<input type="hidden" name="room_types[]" value="${type}">`);
            });
        }
        
        if (minCapacity > 0) {
            $('#hiddenInputsContainer').append(`<input type="hidden" name="min_capacity" value="${minCapacity}">`);
        }

        if (hasEquipment) {
            $('#hiddenInputsContainer').append(`<input type="hidden" name="has_equipment" value="true">`);
        }

        if (onlyAvailable) {
            $('#hiddenInputsContainer').append(`<input type="hidden" name="only_available" value="true">`);
        }

        if (searchQuery) {
            $('#hiddenInputsContainer').append(`<input type="hidden" name="search" value="${searchQuery}">`);
        }

        // Hide the dropdown when applying filters
        $('#filterDropdown').hide();
        $('#filterToggleBtn').removeClass('active');

        // Submit the form
        $('#filterForm').submit();
    });

    // Reset filters
    $('#resetFilters').click(function() {
        resetFilters();
    });

    // Initialize filter functions
    function initializeFilters() {
        updateBuildingCount();
        updateRoomTypeCount();
        updateCapacityLabel();
        updateFilterTags();
        updateFilterCountBubble();
    }

    // Update the building count label
    function updateBuildingCount() {
        const selectedCount = $('.building-checkbox:checked').length;
        $('#buildingCount').text(selectedCount > 0 ? selectedCount + ' selected' : '0 selected');
    }

    // Update the room type count label
    function updateRoomTypeCount() {
        const selectedCount = $('.roomtype-checkbox:checked').length;
        $('#roomTypeCount').text(selectedCount > 0 ? selectedCount + ' selected' : '0 selected');
    }

    // Update the capacity label
    function updateCapacityLabel() {
        const capacity = $('#capacitySlider').val();
        $('#capacityValue').text(capacity > 0 ? capacity + '+' : 'Any');
    }

    // Update just the filter count bubble
    function updateFilterCountBubble() {
        let filterCount = 0;

        // Count all active filters
        filterCount += $('.building-checkbox:checked').length;
        filterCount += $('.roomtype-checkbox:checked').length;
        if (parseInt($('#capacitySlider').val()) > 0) filterCount++;
        if ($('#hasEquipment').is(':checked')) filterCount++;

        // Update filter count bubble
        $('#filterCountBubble').text(filterCount > 0 ? filterCount : '');
    }

    // Update filter tags based on selected filters
    function updateFilterTags() {
        // Clear existing tags
        $('#appliedFilters').empty();

        let hasAnyFilter = false;
        let filterCount = 0;

        // Add building tags
        $('.building-checkbox:checked').each(function() {
            hasAnyFilter = true;
            filterCount++;
            const buildingId = $(this).val();
            const buildingName = $(this).siblings('.checkbox-label').text();
            addFilterTag('Buildings', buildingId, buildingName);
        });

        // Add room type tags
        $('.roomtype-checkbox:checked').each(function() {
            hasAnyFilter = true;
            filterCount++;
            const roomType = $(this).val();
            addFilterTag('Types', roomType, roomType);
        });

        // Add capacity tag if set
        const capacity = $('#capacitySlider').val();
        if (capacity > 0) {
            hasAnyFilter = true;
            filterCount++;
            addFilterTag('Capacity', capacity, capacity + '+');
        }

        // Add equipment tag if checked
        if ($('#hasEquipment').is(':checked')) {
            hasAnyFilter = true;
            filterCount++;
            addFilterTag('Equipment', 'true', 'Has Equipment');
        }

        // We don't add a tag for availability since it's on by default

        // Add clear all button if we have any filters
        if (hasAnyFilter) {
            $('#appliedFilters').append(
                '<button class="clear-all-filters" id="clearAllFilters">Clear all</button>'
            );

            // Add event handler for clear all button
            $('#clearAllFilters').click(function() {
                resetFilters();
            });
        }

        // Update the filter count bubble
        updateFilterCountBubble();
    }

    // Add a single filter tag
    function addFilterTag(type, value, label) {
        const tagHtml = `
            <div class="filter-tag" data-type="${type}" data-value="${value}">
                ${type === 'Types' ? '' : type + ': '}${label}
                <span class="close-icon">×</span>
            </div>
        `;
        $('#appliedFilters').append(tagHtml);

        // Add event handler for tag removal
        $('.filter-tag[data-type="' + type + '"][data-value="' + value + '"] .close-icon').click(function() {
            removeFilter(type, value);
        });
    }

    // Remove a specific filter when its tag is clicked
    function removeFilter(type, value) {
        switch (type) {
            case 'Buildings':
                $('.building-checkbox[value="' + value + '"]').prop('checked', false);
                updateBuildingCount();
                break;
            case 'Types':
                $('.roomtype-checkbox[value="' + value + '"]').prop('checked', false);
                updateRoomTypeCount();
                break;
            case 'Capacity':
                $('#capacitySlider').val(0);
                updateCapacityLabel();
                break;
            case 'Equipment':
                $('#hasEquipment').prop('checked', false);
                break;
        }

        // Update the filter tags and bubble count
        updateFilterTags();
    }

    // Reset all filters to default state
    function resetFilters() {
        // Uncheck all checkboxes
        $('.building-checkbox, .roomtype-checkbox').prop('checked', false);

        // Reset capacity slider
        $('#capacitySlider').val(0);

        // Reset equipment toggle
        $('#hasEquipment').prop('checked', false);

        // Set availability toggle to checked (default)
        $('#onlyAvailable').prop('checked', true);

        // Update UI
        updateBuildingCount();
        updateRoomTypeCount();
        updateCapacityLabel();
        updateFilterTags();

        // Clear the search field
        $('#searchRooms').val('');

        // Submit the form with no filters
        $('#hiddenInputsContainer').empty();
        $('#filterForm').submit();
    }

    // Handle search input - Only update when enter is pressed
    $('#searchRooms').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            $('#applyFilters').click();
        }
    });

    // Check if we already have URL parameters and apply them to the UI
    const urlParams = new URLSearchParams(window.location.search);

    // Set building checkboxes
    if (urlParams.has('building_ids[]')) {
        urlParams.getAll('building_ids[]').forEach(id => {
            $('.building-checkbox[value="' + id + '"]').prop('checked', true);
        });
        updateBuildingCount();
    }

    // Set room type checkboxes
    if (urlParams.has('room_types[]')) {
        urlParams.getAll('room_types[]').forEach(type => {
            $('.roomtype-checkbox[value="' + type + '"]').prop('checked', true);
        });
        updateRoomTypeCount();
    }

    // Set capacity slider
    if (urlParams.has('min_capacity')) {
        const slider = document.getElementById('capacitySlider');
        slider.value = urlParams.get('min_capacity');
        const percent = (slider.value / slider.max) * 100;
        slider.style.setProperty('--value-percent', percent + '%');
        updateCapacityLabel();
    }

    // Set equipment toggle
    if (urlParams.has('has_equipment')) {
        $('#hasEquipment').prop('checked', urlParams.get('has_equipment') === 'true');
    }

    // Set availability toggle
    if (urlParams.has('only_available')) {
        $('#onlyAvailable').prop('checked', urlParams.get('only_available') === 'true');
    }

    // Set search input
    if (urlParams.has('search')) {
        $('#searchRooms').val(urlParams.get('search'));
    }

    // Update filter tags
    updateFilterTags();

    // Show/hide clear icon based on search input content
    $('#searchRooms').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();

        if (searchTerm.length > 0) {
            $('#clearSearch').show();
            // Real-time filtering without page reload
            filterRoomsRealTime(searchTerm);
        } else {
            $('#clearSearch').hide();
            // If search is cleared, show all rooms
            $('.room-card').show();
            updateRoomCount();
        }
    });

    // Handle clear search icon click
    $('#clearSearch').on('click', function() {
        $('#searchRooms').val('');
        $(this).hide();

        // Show all rooms when search is cleared
        $('.room-card').show();
        updateRoomCount();

        // If we have a search parameter in the URL, resubmit to clear results
        const currentParams = new URLSearchParams(window.location.search);
        if (currentParams.has('search')) {
            $('#applyFilters').click();
        }
    });

    // Real-time room filtering function
    function filterRoomsRealTime(searchTerm) {
        let visibleCount = 0;

        // Loop through all room cards
        $('.room-card').each(function() {
            const roomName = $(this).find('.room-name').text().toLowerCase();
            const buildingName = $(this).find('.building-name').text().toLowerCase();
            const roomType = $(this).find('.room-info:contains("Type:")').text().toLowerCase();

            // If the search term matches any of the room data, show the card
            if (roomName.includes(searchTerm) ||
                buildingName.includes(searchTerm) ||
                roomType.includes(searchTerm)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        // Update the room count
        updateRoomCountDisplay(visibleCount);

        // Show or hide the "no results" message
        if (visibleCount === 0) {
            $('#noResultsMessage').css('display', 'flex');
        } else {
            $('#noResultsMessage').hide();
        }
    }

    // Update room count display
    function updateRoomCountDisplay(count) {
        $('#roomCount').text(count + ' ' + (count === 1 ? 'room' : 'rooms') + ' found');
    }

    // Update room count based on visible elements
    function updateRoomCount() {
        const visibleCount = $('.room-card:visible').length;
        updateRoomCountDisplay(visibleCount);

        if (visibleCount === 0) {
            $('#noResultsMessage').css('display', 'flex');
        } else {
            $('#noResultsMessage').hide();
        }
    }

    // View toggle functionality 
    $('#gridView').click(function() {
        $(this).addClass('active');
        $('#listView').removeClass('active');
        $('.room-card').removeClass('col-md-12').addClass('col-md-4');
    });

    $('#listView').click(function() {
        $(this).addClass('active');
        $('#gridView').removeClass('active');
        $('.room-card').removeClass('col-md-4').addClass('col-md-12');
    });

    // Close filter dropdown when clicking outside
    $(document).on('click', function(e) {
        // Keep this handler for extra safety but improve for compatibility
        if (!e.target.closest('#filterToggleBtn') && !e.target.closest('#filterDropdown')) {
            $('#filterDropdown').hide();
            $('#filterToggleBtn').removeClass('active');
        }
    });

    // Check if search has value on page load
    if ($('#searchRooms').val().length > 0) {
        $('#clearSearch').show();
    }

    // Add modal close handlers
    $('.modal .close, .modal .btn-secondary').on('click', function() {
        const modalId = $(this).closest('.modal').attr('id');
        $(`#${modalId}`).modal('hide');

        // Reset form if it's the reservation modal
        if (modalId === 'reservationModal') {
            resetReservationForm();
        }
    });

    // Close modal on backdrop click
    $('.modal').on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            $(this).modal('hide');

            // Reset form if it's the reservation modal
            if ($(this).attr('id') === 'reservationModal') {
                resetReservationForm();
            }
        }
    });

    // Function to reset reservation form
    function resetReservationForm() {
        $('#reservationForm')[0].reset();
        // Reset steps
        $('.step-content').removeClass('active');
        $('.step-item').removeClass('active completed');
        $('#step1').addClass('active');
        $('#step1Item').addClass('active');
        
        // Set the default date to today
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;
        $('#reservationDate').val(formattedDate);
    }

    // Move to step 2
    $("#toStep2").click(function(e) {
        e.preventDefault();

        // Validate step 1 fields
        const activityName = $("#activityName").val().trim();
        const purpose = $("#purpose").val().trim();
        const participants = $("#participants").val().trim();

        if (!activityName || activityName.split(' ').length < 2) {
            alert('Activity name must be at least 2 words');
            return;
        }

        if (!purpose || purpose.length < 10) {
            alert('Purpose must be at least 10 characters');
            return;
        }

        if (!participants || parseInt(participants) < 1) {
            alert('Please enter a valid number of participants');
            return;
        }

        // If validation passes, move to next step
        $("#step1").removeClass("active");
        $("#step2").addClass("active");
        $("#step1Item").removeClass("active").addClass("completed");
        $("#step2Item").addClass("active");
    });

    // Function to add validation icons to input wrappers
    function updateValidationIcons() {
        // Add validation icons to any input wrapper that doesn't have them yet
        $('.input-wrapper').each(function() {
            if ($(this).find('.valid-checkmark').length === 0) {
                $(this).append(`
                    <div class="valid-checkmark">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="invalid-mark">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </div>
                `);
            }
        });
    }

    // Add this code to validate fields on input and enable/disable the Next button
    function validateStep1() {
        const activityName = $("#activityName").val().trim();
        const purpose = $("#purpose").val().trim();
        const participants = $("#participants").val().trim();

        const isActivityValid = activityName && activityName.split(' ').length >= 2;
        const isPurposeValid = purpose && purpose.length >= 10;
        const isParticipantsValid = participants && parseInt(participants) >= 1;

        // Enable the button only if all fields are valid
        if (isActivityValid && isPurposeValid && isParticipantsValid) {
            $("#toStep2").prop('disabled', false);
        } else {
            $("#toStep2").prop('disabled', true);
        }

        // Show validation state
        if (activityName) {
            if (isActivityValid) {
                $("#activityName").removeClass("is-invalid").addClass("is-valid");
            } else {
                $("#activityName").removeClass("is-valid").addClass("is-invalid");
            }
        }

        if (purpose) {
            if (isPurposeValid) {
                $("#purpose").removeClass("is-invalid").addClass("is-valid");
            } else {
                $("#purpose").removeClass("is-valid").addClass("is-invalid");
            }
        }

        if (participants) {
            if (isParticipantsValid) {
                $("#participants").removeClass("is-invalid").addClass("is-valid");
            } else {
                $("#participants").removeClass("is-valid").addClass("is-invalid");
            }
        }
    }

    // Disable Next button initially
    $("#toStep2").prop('disabled', true);

    // Validate on input in any of the fields
    $("#activityName, #purpose, #participants").on('input', validateStep1);

    // Call updateValidationIcons when document is ready
    $(document).ready(function() {
        updateValidationIcons();
    });

    // Similar validation can be added for step 2 and 3
    function validateStep2() {
        const reservationDate = $("#reservationDate").val();
        const startTime = $("#startTime").val();
        const hours = parseInt($("#durationHours").val()) || 0;
        const minutes = parseInt($("#durationMinutes").val()) || 0;

        const isDateValid = reservationDate !== '';
        const isTimeValid = startTime !== '';
        const isDurationValid = (hours > 0 || minutes > 0);

        // Enable the button only if all fields are valid
        if (isDateValid && isTimeValid && isDurationValid) {
            $("#toStep3").prop('disabled', false);
        } else {
            $("#toStep3").prop('disabled', true);
        }
    }

    // Disable Next button for step 2 initially
    $("#toStep3").prop('disabled', true);

    // Validate on input/change in any of the step 2 fields
    $("#reservationDate, #startTime, #durationHours, #durationMinutes").on('input change', validateStep2);

    // Move to step 3
    $("#toStep3").click(function(e) {
        e.preventDefault();
        $("#step2").removeClass("active");
        $("#step3").addClass("active");
        $("#step2Item").removeClass("active").addClass("completed");
        $("#step3Item").addClass("active");
    });

    // Back to step 1
    $("#backToStep1").click(function(e) {
        e.preventDefault();
        $("#step2").removeClass("active");
        $("#step1").addClass("active");
        $("#step2Item").removeClass("active");
        $("#step1Item").removeClass("completed").addClass("active");
    });

    // Back to step 2
    $("#backToStep2").click(function(e) {
        e.preventDefault();
        $("#step3").removeClass("active");
        $("#step2").addClass("active");
        $("#step3Item").removeClass("active");
        $("#step2Item").removeClass("completed").addClass("active");
    });

    // Disable past dates in the reservation date picker
    function disablePastDates() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;
        
        // Set the min attribute to today
        $('#reservationDate').attr('min', formattedDate);
        
        // Also add validation for browsers that don't support the min attribute
        $('#reservationDate').on('input change', function() {
            const selectedDate = new Date($(this).val());
            if (selectedDate < today) {
                // If a past date is somehow selected, reset to today
                $(this).val(formattedDate);
               
            }
        });
    }
    
    // Call the function to initialize date restrictions
    disablePastDates();
    
    // If the reservation modal is opened dynamically, ensure we call this again
    $(document).on('click', '.reserve-btn', function() {
        // Allow a slight delay for the modal to open
        setTimeout(disablePastDates, 100);
    });
});

// Function to show the reservation modal and pre-select the room
function showReservationModal(roomId) {
    // Get room data from the card
    const roomCard = $(`.room-card[data-room-id="${roomId}"]`);
    const roomName = roomCard.find('.room-name').text();
    const buildingName = roomCard.find('.building-name').text();
    const capacity = roomCard.data('capacity');
    const roomType = roomCard.data('room-type');

    // Set the room ID in the hidden input
    $('#selectedRoom').val(roomId);

    // Create room info HTML
    const roomInfoHtml = `
        <div class="selected-room-card">
            <div class="room-header">
                <h4>${roomName}</h4>
                <p>${buildingName}</p>
            </div>
            <div class="room-details">
                <div class="room-detail-item">
                    <i class="fa fa-users"></i> Capacity: ${capacity}
                </div>
                <div class="room-detail-item">
                    <i class="fa fa-home"></i> Type: ${roomType}
                </div>
                <div class="room-detail-item">
                    <i class="fa fa-check"></i> Status: Available
                </div>
            </div>
        </div>
    `;

    // Set the selected room info
    $('#selectedRoomInfo').html(roomInfoHtml);

    // Show the modal
    $('#reservationModal').modal('show');
}

// Function to convert 24-hour format to 12-hour format
function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const formattedHour = hour % 12 || 12;
    return `${formattedHour}:${minutes} ${ampm}`;
}

// Function to calculate end time
function calculateEndTime() {
    const startTime = document.getElementById('startTime').value;
    const hours = parseInt(document.getElementById('durationHours').value) || 0;
    const minutes = parseInt(document.getElementById('durationMinutes').value) || 0;

    if (!startTime || (hours === 0 && minutes === 0)) {
        document.getElementById('endTime').value = '';
        return;
    }

    const [startHours, startMinutes] = startTime.split(':');
    const startDate = new Date();
    startDate.setHours(parseInt(startHours));
    startDate.setMinutes(parseInt(startMinutes));

    const endDate = new Date(startDate);
    endDate.setHours(endDate.getHours() + hours);
    endDate.setMinutes(endDate.getMinutes() + minutes);

    const endHours = endDate.getHours().toString().padStart(2, '0');
    const endMinutes = endDate.getMinutes().toString().padStart(2, '0');
    const endTimeValue = `${endHours}:${endMinutes}`;

    document.getElementById('endTime').value = formatTime(endTimeValue);
    document.getElementById('endTime').setAttribute('data-value', endTimeValue);
}

// Add event listeners for time and duration inputs
document.addEventListener('DOMContentLoaded', function() {
    // Only attach these if the elements exist on the page
    const startTimeEl = document.getElementById('startTime');
    const durationHoursEl = document.getElementById('durationHours');
    const durationMinutesEl = document.getElementById('durationMinutes');
    const reservationFormEl = document.getElementById('reservationForm');
    
    if (startTimeEl) {
        startTimeEl.addEventListener('change', calculateEndTime);
    }
    
    if (durationHoursEl) {
        durationHoursEl.addEventListener('input', calculateEndTime);
    }
    
    if (durationMinutesEl) {
        durationMinutesEl.addEventListener('input', calculateEndTime);
    }
    
    // Form submission handler
    if (reservationFormEl) {
        reservationFormEl.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate required fields
            const activityName = document.getElementById('activityName').value.trim();
            const purpose = document.getElementById('purpose').value.trim();
            const participants = document.getElementById('participants').value;
            const reservationDate = document.getElementById('reservationDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').getAttribute('data-value');
            const roomId = document.getElementById('selectedRoom').value;

            // Basic validation
            if (!activityName || activityName.split(' ').length < 2) {
                alert('Activity name must be at least 2 words');
                return;
            }

            if (!purpose || purpose.split('.').length < 1) {
                alert('Purpose must be at least 1 sentence');
                return;
            }

            if (!participants || participants < 1) {
                alert('Please enter a valid number of participants');
                return;
            }

            if (!reservationDate) {
                alert('Please select a reservation date');
                return;
            }

            if (!startTime) {
                alert('Please select a start time');
                return;
            }

            if (!endTime) {
                alert('Please set a valid duration');
                return;
            }

            if (!roomId) {
                alert('Please select a room');
                return;
            }

            // Create a hidden input for the end time value
            const endTimeInput = document.createElement('input');
            endTimeInput.type = 'hidden';
            endTimeInput.name = 'endTime';
            endTimeInput.value = endTime;
            this.appendChild(endTimeInput);

            // If all validations pass, submit the form
            this.submit();
        });
    }
});
