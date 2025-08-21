$(document).ready(function () {
  // Check if DataTable is already initialized
  if (!$.fn.dataTable.isDataTable('#adminTable')) {
    $('#adminTable').DataTable({
      responsive: true,
      language: {
        search: '_INPUT_',
        searchPlaceholder: 'Search admins...',
      },
      dom: '<"top"lf>rt<"bottom"ip><"clear">',
      lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, 'All'],
      ],
      pageLength: 10,
      ordering: true,
      columnDefs: [
        {
          targets: -1,
          orderable: false,
        },
      ],
      order: [[0, 'asc']], // Order by FirstName by default
    });
  }

  // Add CSS for better error message formatting
  $('<style>')
    .prop('type', 'text/css')
    .html(
      `
            #modalMessage {
                max-height: 300px;
                overflow-y: auto;
                line-height: 1.4;
            }
            #modalMessage strong {
                display: block;
                margin-top: 10px;
                color: #d32f2f;
            }
            .modal-content {
                max-width: 500px;
            }
        `
    )
    .appendTo('head');

  // AJAX Form Submission for adding admin
  $('#adminForm').on('submit', function (e) {
    e.preventDefault();
    submitAdminForm();
  });

  // Export button functionality
  $('#exportButton').on('click', function () {
    exportAdmins();
  });

  // Import form functionality
  $('#importButton').on('click', function () {
    importAdmins();
  });

  // Edit form submission
  $('#saveEditButton').on('click', function () {
    submitEditForm();
  });

  // Handle URL params for backward compatibility
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get('status');
  const message = urlParams.get('msg');

  if (status && message) {
    const title = status === 'success' ? 'Success' : 'Error';
    showModal(title, decodeURIComponent(message), status);

    // Clear the URL parameters
    if (window.history && window.history.pushState) {
      const newUrl =
        window.location.protocol +
        '//' +
        window.location.host +
        window.location.pathname;
      window.history.pushState({ path: newUrl }, '', newUrl);
    }
  }
});

// Function to submit the admin form via AJAX
function submitAdminForm() {
  // Show loader
  document.getElementById('ajaxLoader').style.display = 'flex';
  document.getElementById('ajaxLoaderText').textContent =
    'Adding administrator...';

  // Get form data
  const formData = new FormData(document.getElementById('adminForm'));

  // Send AJAX request
  $.ajax({
    url: 'includes/add_admin_ajax.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      if (response.success) {
        // Show success message
        showModal('Success', response.message, 'success');

        // Reset the form
        document.getElementById('adminForm').reset();

        // Reload the DataTable with new data
        updateDataTable(response.data);
      } else {
        // Show error message
        showModal('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      // Show error message
      showModal('Error', 'An error occurred: ' + error, 'error');
    },
  });
}

// Function to submit the edit form via AJAX
function submitEditForm() {
  // Show loader
  document.getElementById('ajaxLoader').style.display = 'flex';
  document.getElementById('ajaxLoaderText').textContent =
    'Updating administrator...';

  // Get form data
  const formData = new FormData(document.getElementById('editAdminForm'));

  // Send AJAX request
  $.ajax({
    url: 'includes/add_admin_ajax.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      // Close the edit modal
      closeEditModal();

      if (response.success) {
        // Show success message
        showModal('Success', response.message, 'success');

        // Reload the DataTable with new data
        updateDataTable(response.data);
      } else {
        // Show error message
        showModal('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      // Close the edit modal
      closeEditModal();

      // Show error message
      showModal('Error', 'An error occurred: ' + error, 'error');
    },
  });
}

// Function to delete an admin via AJAX
function deleteAdmin(adminId) {
  // Confirm deletion
  if (!confirm('Are you sure you want to delete this admin?')) {
    return;
  }

  // Show loader
  document.getElementById('ajaxLoader').style.display = 'flex';
  document.getElementById('ajaxLoaderText').textContent =
    'Deleting administrator...';

  // Create form data
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('admin_id', adminId);

  // Send AJAX request
  $.ajax({
    url: 'includes/add_admin_ajax.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      if (response.success) {
        // Show success message
        showModal('Success', response.message, 'success');

        // Reload the DataTable with new data
        updateDataTable(response.data);
      } else {
        // Show error message
        showModal('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      // Show error message
      showModal('Error', 'An error occurred: ' + error, 'error');
    },
  });
}

// Function to import admins via AJAX
function importAdmins() {
  // Check if file is selected
  const fileInput = document.querySelector('#importForm input[type="file"]');
  if (!fileInput.files.length) {
    showModal('Error', 'Please select a CSV file to import.', 'error');
    return;
  }

  // Show loader
  document.getElementById('ajaxLoader').style.display = 'flex';
  document.getElementById('ajaxLoaderText').textContent =
    'Importing administrators...';

  // Create form data
  const formData = new FormData(document.getElementById('importForm'));

  // Send AJAX request
  $.ajax({
    url: 'includes/import_admin_ajax.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      if (response.success) {
        // Show success message
        showModal('Success', response.message, 'success');

        // Reset the file input
        document.getElementById('importForm').reset();

        // Reload the DataTable with new data
        updateDataTable(response.data);
      } else {
        // Show error message
        showModal('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      // Show error message
      showModal('Error', 'An error occurred: ' + error, 'error');
    },
  });
}

// Function to export admins via AJAX
function exportAdmins() {
  // Show loader
  document.getElementById('ajaxLoader').style.display = 'flex';
  document.getElementById('ajaxLoaderText').textContent =
    'Generating export...';

  // Create form data
  const formData = new FormData();
  formData.append('action', 'export');

  // Send AJAX request
  $.ajax({
    url: 'includes/add_admin_ajax.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      if (response.success) {
        // Download the CSV file
        downloadCSV(response.data, response.filename);
      } else {
        // Show error message
        showModal('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      // Hide loader
      document.getElementById('ajaxLoader').style.display = 'none';

      // Show error message
      showModal('Error', 'An error occurred: ' + error, 'error');
    },
  });
}

// Function to download CSV file
function downloadCSV(base64Data, filename) {
  const blob = b64toBlob(base64Data, 'text/csv');
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.style.display = 'none';
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  window.URL.revokeObjectURL(url);
  document.body.removeChild(a);
}

// Helper function to convert base64 to Blob
function b64toBlob(b64Data, contentType = '', sliceSize = 512) {
  const byteCharacters = atob(b64Data);
  const byteArrays = [];

  for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
    const slice = byteCharacters.slice(offset, offset + sliceSize);
    const byteNumbers = new Array(slice.length);

    for (let i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }

    const byteArray = new Uint8Array(byteNumbers);
    byteArrays.push(byteArray);
  }

  return new Blob(byteArrays, { type: contentType });
}

// Function to update the DataTable with new data
function updateDataTable(data) {
  const table = $('#adminTable').DataTable();

  // Clear the table
  table.clear();

  // Add new data
  if (data && data.length > 0) {
    data.forEach(function (admin) {
      table.row.add([
        admin.FirstName,
        admin.LastName,
        admin.Department,
        admin.Email,
        admin.Actions,
      ]);
    });
  }

  // Draw the table
  table.draw();
}

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

// Close the modal when the user clicks on <span> (x)
document.getElementById('closeModal').onclick = function () {
  closeModal();
};

// Close the modal when the user clicks anywhere outside of the modal
window.onclick = function (event) {
  if (event.target == document.getElementById('messageModal')) {
    closeModal();
  } else if (event.target == document.getElementById('editModal')) {
    closeEditModal();
  }
};

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

// Function to open edit modal
function openEditModal(adminId, firstName, lastName, department, email) {
  // Populate form fields with admin data
  document.getElementById('edit_admin_id').value = adminId;
  document.getElementById('edit_first_name').value = firstName;
  document.getElementById('edit_last_name').value = lastName;
  document.getElementById('edit_department').value = department;
  document.getElementById('edit_email').value = email;

  // Show the modal
  const modal = document.getElementById('editModal');
  modal.style.display = 'block';

  // Trigger reflow for animation to work
  void modal.offsetWidth;
  modal.classList.add('show');
}

// Function to close edit modal
function closeEditModal() {
  const modal = document.getElementById('editModal');
  modal.classList.remove('show');

  // Wait for animation to complete before hiding
  setTimeout(() => {
    modal.style.display = 'none';
  }, 300);
}

// Close the edit modal when the user clicks on <span> (x)
document.getElementById('closeEditModal').onclick = function () {
  closeEditModal();
};
