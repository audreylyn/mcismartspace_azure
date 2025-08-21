$(document).ready(function () {
  // Check if DataTable is already initialized on this table
  if ($.fn.dataTable.isDataTable('#buildingTable')) {
    // If already initialized, get the instance and redraw it
    $('#buildingTable').DataTable().draw();
  } else {
    // Initialize DataTable if it's not already initialized
    $('#buildingTable').DataTable({
      responsive: true,
      language: {
        search: '_INPUT_',
        searchPlaceholder: 'Search buildings...',
      },
      dom: '<"top"lf>rt<"bottom"ip><"clear">',
      lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, 'All'],
      ],
      pageLength: 10,
      ordering: true,
    });
  }

  // AJAX form submission for adding buildings
  $('#buildingForm').on('submit', function (e) {
    e.preventDefault();

    // Show loading state
    const submitButton = $(this).find('button[type="submit"]');
    const originalText = submitButton.text();
    submitButton.prop('disabled', true).text('Processing...');

    // Show the global loader for longer operations
    showLoader(true, 'Adding building...');

    $.ajax({
      url: 'includes/add_building_ajax.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (response) {
        // Reset the form on success
        if (response.status === 'success') {
          $('#buildingForm')[0].reset();

          // Add the new building to the table
          if (response.building) {
            const table = $('#buildingTable').DataTable();

            // If there was a "No buildings found" row, clear the table
            if ($('#buildingTable tbody tr td.has-text-centered').length) {
              table.clear();
            }

            // Add the new row and redraw
            table.row
              .add([
                response.building.name,
                response.building.department,
                response.building.floors,
                response.building.created_at,
              ])
              .draw();
          }
        }

        // Show the response message
        showModal(
          response.status === 'success' ? 'Success' : 'Error',
          response.message,
          response.status
        );
      },
      error: function (xhr, status, error) {
        // Show error message if AJAX call fails
        showModal(
          'Error',
          'There was a problem processing your request. Please try again.',
          'error'
        );
        console.error('AJAX Error:', status, error);
      },
      complete: function () {
        // Reset button state
        submitButton.prop('disabled', false).text(originalText);
        // Hide the global loader
        showLoader(false);
      },
    });
  });

  // Export functionality
  $('#exportBtn').on('click', function () {
    // Show loading state
    const exportBtn = $(this);
    const originalText = exportBtn.text().trim();
    exportBtn.html('<span class="spinner"></span> Exporting...');

    // Show the global loader
    showLoader(true, 'Generating export file...');

    // Start the export process
    $.ajax({
      url: 'includes/export_rooms.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.status === 'success') {
          // Create a temporary link to download the file
          const a = document.createElement('a');
          a.href = response.fileUrl;
          a.download = response.fileName || 'buildings.csv';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);

          showModal('Success', 'Buildings exported successfully!', 'success');
        } else {
          showModal('Error', response.message || 'Export failed', 'error');
        }
      },
      error: function () {
        showModal(
          'Error',
          'There was a problem with the export. Please try again.',
          'error'
        );
      },
      complete: function () {
        // Reset button state
        exportBtn.html(`<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
          <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
          <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
        </svg>Export`);

        // Hide the global loader
        showLoader(false);
      },
    });
  });
});

// Function to show the message modal
function showModal(title, message, type = 'success') {
  const modal = document.getElementById('messageModal');
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMessage').innerHTML = message;

  // Add the type class for specific styling
  modal.classList.add(type);

  // Display the modal and add show class for animation
  modal.style.display = 'block';

  // Trigger reflow for animation to work
  void modal.offsetWidth;

  modal.classList.add('show');
}

// Function to show/hide AJAX loader
function showLoader(show = true, message = 'Processing...') {
  const loader = document.getElementById('ajaxLoader');
  const loaderText = document.getElementById('ajaxLoaderText');

  if (show) {
    loaderText.textContent = message;
    loader.classList.add('show');
  } else {
    loader.classList.remove('show');
  }
}

// Close the modal when the user clicks on <span> (x)
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('closeModal').onclick = function () {
    closeModal();
  };

  // Close the modal when the user clicks anywhere outside of the modal
  window.onclick = function (event) {
    if (event.target == document.getElementById('messageModal')) {
      closeModal();
    }
  };
});

// Function to close message modal with animation
function closeModal() {
  const modal = document.getElementById('messageModal');
  modal.classList.remove('show');

  // Wait for animation to complete before hiding
  setTimeout(() => {
    modal.style.display = 'none';
    modal.classList.remove('success', 'error');
  }, 300);
}
