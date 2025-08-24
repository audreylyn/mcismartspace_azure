document.addEventListener('DOMContentLoaded', function () {
  // Tab switching
  const tabs = document.querySelectorAll('.option-tab');
  const contents = document.querySelectorAll('.option-content');

  // Add validation styles
  const styleElement = document.createElement('style');
  styleElement.textContent = `
                .input-wrapper {
                    position: relative;
                }
                .validation-feedback {
                    font-size: 0.85rem;
                    margin-top: 0.3rem;
                    transition: all 0.3s ease;
                    height: 20px;
                }
                .validation-error {
                    color: #e11d48;
                }
                .validation-success {
                    color: #0f9d58;
                }
                .form-control.is-invalid {
                    border-color: #e11d48;
                }
                .form-control.is-valid {
                    border-color: #0f9d58;
                }
                .validation-spinner {
                    display: inline-block;
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(15, 66, 40, 0.1);
                    border-top: 2px solid #0f4228;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-right: 8px;
                    vertical-align: middle;
                }
                #equipment-room:disabled, #equipment-building:disabled {
                    background-color: #f8f9fa;
                    cursor: not-allowed;
                }
            `;
  document.head.appendChild(styleElement);

  tabs.forEach((tab) => {
    tab.addEventListener('click', function () {
      // Remove active class from all tabs and contents
      tabs.forEach((t) => t.classList.remove('active'));
      contents.forEach((c) => c.classList.remove('active'));

      // Add active class to current tab and content
      this.classList.add('active');
      const tabName = this.getAttribute('data-tab');
      document.getElementById(`${tabName}-content`).classList.add('active');

      // If we're switching to camera tab, show camera preview
      if (tabName === 'scan' && !scanning) {
        showCameraPreview();
      }

      // If we're switching away from camera tab, stop scanning
      if (tabName !== 'scan' && scanning) {
        stopScanning();
      } else if (tabName !== 'scan' && cameraActive) {
        stopCameraPreview();
      }
    });
  });

  // Camera scanning functionality
  const startButton = document.getElementById('start-button');
  const qrVideo = document.getElementById('qr-video');
  const cameraPlaceholder = document.getElementById('camera-placeholder');
  const scanningEffect = document.getElementById('scanning-effect');

  let html5QrCode;
  let scanning = false;
  let cameraActive = false;
  let videoStream;

  // Initialize camera preview when page loads
  showCameraPreview();

  startButton.addEventListener('click', function () {
    if (!scanning) {
      startScanning();
    } else {
      stopScanning();
    }
  });

  // Function to show camera preview without scanning
  async function showCameraPreview() {
    if (cameraActive) return;

    try {
      // Hide placeholder and show video
      cameraPlaceholder.style.display = 'none';
      qrVideo.style.display = 'block';

      // Show loading indicator until camera starts
      document.getElementById('scan-content').classList.add('loading');

      // Get camera stream
      videoStream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: 'environment',
        },
      });

      // Attach stream to video element
      qrVideo.srcObject = videoStream;
      qrVideo.play();

      // Remove loading indicator
      document.getElementById('scan-content').classList.remove('loading');

      cameraActive = true;
      startButton.textContent = 'Start Scanning';
    } catch (error) {
      console.error('Error starting camera preview:', error);
      cameraPlaceholder.style.display = 'flex';
      qrVideo.style.display = 'none';
      document.getElementById('scan-content').classList.remove('loading');
      alert(
        'Could not access camera. Please ensure you have granted camera permissions or try the upload option instead.'
      );
    }
  }

  // Function to stop camera preview
  function stopCameraPreview() {
    if (!cameraActive) return;

    // Stop video stream
    if (videoStream) {
      videoStream.getTracks().forEach((track) => track.stop());
      videoStream = null;
    }

    // Reset UI
    cameraPlaceholder.style.display = 'flex';
    qrVideo.style.display = 'none';
    qrVideo.srcObject = null;
    cameraActive = false;
  }

  function startScanning() {
    // If camera is already active, start scanning. Otherwise, initialize camera first
    if (cameraActive) {
      // Update button text and start scanning effect
      startButton.innerHTML = '<i class="fa fa-stop"></i> Stop Scanning';
      scanningEffect.style.display = 'block';
      scanning = true;

      // Check if BarcodeDetector API is available
      if ('BarcodeDetector' in window) {
        startNativeScanning();
      } else {
        startHTML5QrScanning();
      }
    } else {
      showCameraPreview().then(() => {
        startScanning();
      });
    }
  }

  // Native BarcodeDetector API implementation (much faster)
  let nativeScanning = false;

  async function startNativeScanning() {
    try {
      // Create a BarcodeDetector instance
      const barcodeDetector = new BarcodeDetector({
        formats: ['qr_code'],
      });

      nativeScanning = true;

      // Start detection loop
      nativeDetectionLoop(barcodeDetector);
    } catch (error) {
      console.error('Error starting native scanner:', error);
      // Fallback to HTML5QrCode if native scanner fails
      nativeScanning = false;
      startHTML5QrScanning();
    }
  }

  async function nativeDetectionLoop(barcodeDetector) {
    if (!nativeScanning) return;

    try {
      // Only detect if video is playing
      if (qrVideo.readyState === qrVideo.HAVE_ENOUGH_DATA) {
        const barcodes = await barcodeDetector.detect(qrVideo);

        if (barcodes.length > 0) {
          // QR code detected
          const qrContent = barcodes[0].rawValue;
          stopScanning();

          // Show success indicator
          const successAlert = document.createElement('div');
          successAlert.className = 'qr-success-alert';
          successAlert.innerHTML =
            '<i class="fa fa-check-circle"></i> QR Code detected! Redirecting...';
          document
            .querySelector('.qr-scan-container')
            .appendChild(successAlert);

          // Navigate to the equipment issue report page after a brief delay
          setTimeout(() => {
            window.location.href = qrContent;
          }, 500);

          return;
        }
      }

      // Continue detection loop
      requestAnimationFrame(() => nativeDetectionLoop(barcodeDetector));
    } catch (error) {
      console.error('Detection error:', error);
      requestAnimationFrame(() => nativeDetectionLoop(barcodeDetector));
    }
  }

  function stopNativeScanning() {
    nativeScanning = false;
    scanningEffect.style.display = 'none';
    startButton.textContent = 'Start Scanning';
    scanning = false;
  }

  // HTML5QrCode implementation (fallback)
  function startHTML5QrScanning() {
    // Initialize the scanner
    html5QrCode = new Html5Qrcode('qr-reader-container');

    // Configuration for better performance
    const config = {
      fps: 10, // Lower fps for better performance
      qrbox: {
        width: 250,
        height: 250,
      },
      experimentalFeatures: {
        useBarCodeDetectorIfSupported: true, // Use native API if available
      },
      rememberLastUsedCamera: true,
      aspectRatio: 1.0, // Square aspect ratio for QR codes
    };

    // Start scanning
    html5QrCode
      .start(
        {
          facingMode: 'environment',
        }, // Use the back camera
        config,
        onScanSuccess,
        onScanFailure
      )
      .catch(function (err) {
        console.error('Error starting scanner:', err);
        alert(
          'Could not access camera. Please ensure you have granted camera permissions or try the upload option instead.'
        );
        stopScanning();

        // Switch to upload tab if camera fails
        document.querySelector('.option-tab[data-tab="upload"]').click();
      });
  }

  function stopScanning() {
    if (nativeScanning) {
      stopNativeScanning();
      return;
    }

    if (html5QrCode && html5QrCode.isScanning) {
      html5QrCode
        .stop()
        .then(() => {
          // Update UI to show we're not scanning but still have preview
          scanningEffect.style.display = 'none';
          startButton.textContent = 'Start Scanning';
          scanning = false;
        })
        .catch((err) => {
          console.error('Error stopping scanner:', err);
        });
    }
  }

  function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning when a QR code is detected
    stopScanning();

    // Show success indicator
    const successAlert = document.createElement('div');
    successAlert.className = 'qr-success-alert';
    successAlert.innerHTML =
      '<i class="fa fa-check-circle"></i> QR Code detected! Redirecting...';
    document.querySelector('.qr-scan-container').appendChild(successAlert);

    // Navigate to the equipment issue report page after a brief delay
    setTimeout(() => {
      window.location.href = decodedText;
    }, 500);
  }

  function onScanFailure(error) {
    // This function will be called frequently, do not use console.log here
  }

  // Image upload functionality
  const fileInput = document.getElementById('file-input');
  const uploadArea = document.getElementById('upload-area');
  const previewImage = document.getElementById('preview-image');
  const processImageButton = document.getElementById('process-image-button');
  const imagePreviewContainer = document.getElementById(
    'image-preview-container'
  );

  uploadArea.addEventListener('click', function () {
    fileInput.click();
  });

  // Drag and drop functionality
  uploadArea.addEventListener('dragover', function (e) {
    e.preventDefault();
    uploadArea.style.borderColor = '#10b981';
    uploadArea.style.backgroundColor = '#f8fafc';
  });

  uploadArea.addEventListener('dragleave', function () {
    uploadArea.style.borderColor = '#ccc';
    uploadArea.style.backgroundColor = '';
  });

  uploadArea.addEventListener('drop', function (e) {
    e.preventDefault();
    uploadArea.style.borderColor = '#ccc';
    uploadArea.style.backgroundColor = '';

    if (e.dataTransfer.files.length) {
      handleFile(e.dataTransfer.files[0]);
    }
  });

  fileInput.addEventListener('change', function () {
    if (this.files.length) {
      handleFile(this.files[0]);
    }
  });

  function handleFile(file) {
    if (!file.type.match('image.*')) {
      alert('Please select an image file');
      return;
    }

    // Display preview while uploading
    const reader = new FileReader();
    reader.onload = function (e) {
      previewImage.src = e.target.result;
      previewImage.style.display = 'block';

      // Show loading state immediately
      processImageButton.disabled = false;
      processImageButton.innerHTML =
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Compressing image...';

      // Upload for server-side compression
      uploadForCompression(file);
    };
    reader.readAsDataURL(file);
  }

  // Server-side compression
  function uploadForCompression(file) {
    console.time('Server Compression');
    const formData = new FormData();
    formData.append('qrImage', file);

    // Show compression is happening
    processImageButton.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Compressing image...';

    fetch('compress-image.php', {
      method: 'POST',
      body: formData,
    })
      .then((response) => {
        console.log('Server response status:', response.status);
        return response.json();
      })
      .then((data) => {
        console.timeEnd('Server Compression');
        if (data.success) {
          console.log(
            'Image compressed successfully:',
            data.width + 'x' + data.height
          );
          // Update preview with compressed image
          previewImage.src = data.imageUrl + '?t=' + new Date().getTime(); // Add timestamp to avoid cache
          processImageButton.innerHTML = 'Process QR Code';

          // Store compressed image URL for processing
          previewImage.dataset.compressedUrl = data.imageUrl;
        } else {
          console.error('Compression error:', data.error);
          // Create a user-friendly error message
          let errorMessage = data.error || 'Unknown server error';
          alert('Error compressing image: ' + errorMessage);
          processImageButton.innerHTML = 'Process QR Code';
          processImageButton.disabled = false;
        }
      })
      .catch((error) => {
        console.timeEnd('Server Compression');
        console.error('Upload error:', error);

        // Show more detailed error in console for debugging
        console.log('Error details:', {
          message: error.message,
          name: error.name,
          stack: error.stack,
        });

        alert(
          'Error uploading image. Please check if the server is accessible and try again.'
        );
        processImageButton.innerHTML = 'Process QR Code';
        processImageButton.disabled = false;
      });
  }

  // Manual entry form elements - Updated for dropdown functionality
  const equipmentSelect = document.getElementById('equipment-select');
  const buildingSelect = document.getElementById('building-select');
  const roomSelect = document.getElementById('room-select');
  const equipmentIdInput = document.getElementById('equipment-id');
  const manualSubmitButton = document.getElementById('manual-submit-button');

  // Hidden fields
  const selectedEquipmentId = document.getElementById('selected-equipment-id');
  const selectedEquipmentName = document.getElementById(
    'selected-equipment-name'
  );
  const selectedRoomName = document.getElementById('selected-room-name');
  const selectedBuildingName = document.getElementById(
    'selected-building-name'
  );

  // Load initial data
  loadEquipmentOptions();
  loadBuildingOptions();

  // Event listeners for cascading dropdowns
  equipmentSelect.addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value) {
      selectedEquipmentId.value = this.value;
      selectedEquipmentName.value = selectedOption.textContent;
      checkFormValidity();
    } else {
      selectedEquipmentId.value = '';
      selectedEquipmentName.value = '';
      manualSubmitButton.disabled = true;
    }
  });

  buildingSelect.addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value) {
      selectedBuildingName.value = selectedOption.textContent;
      loadRoomOptions(this.value);
      roomSelect.disabled = false;
    } else {
      selectedBuildingName.value = '';
      selectedRoomName.value = '';
      roomSelect.disabled = true;
      roomSelect.innerHTML =
        '<option value="">-- Select Building First --</option>';
      manualSubmitButton.disabled = true;
    }
  });

  roomSelect.addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value) {
      selectedRoomName.value = selectedOption.textContent;
      checkFormValidity();
    } else {
      selectedRoomName.value = '';
      manualSubmitButton.disabled = true;
    }
  });

  // Load equipment options
  function loadEquipmentOptions() {
    equipmentSelect.classList.add('loading');

    fetch('api/get_equipment_data.php?action=equipment')
      .then((response) => response.json())
      .then((data) => {
        equipmentSelect.classList.remove('loading');

        if (data.success) {
          equipmentSelect.innerHTML =
            '<option value="">-- Select Equipment --</option>';

          // Group equipment by category
          const groupedEquipment = {};
          data.data.forEach((equipment) => {
            const category = equipment.category || 'Other';
            if (!groupedEquipment[category]) {
              groupedEquipment[category] = [];
            }
            groupedEquipment[category].push(equipment);
          });

          // Add grouped options
          Object.keys(groupedEquipment)
            .sort()
            .forEach((category) => {
              const optgroup = document.createElement('optgroup');
              optgroup.label = category;

              groupedEquipment[category].forEach((equipment) => {
                const option = document.createElement('option');
                option.value = equipment.id;
                option.textContent = equipment.name;
                optgroup.appendChild(option);
              });

              equipmentSelect.appendChild(optgroup);
            });
        } else {
          showError('Failed to load equipment options');
        }
      })
      .catch((error) => {
        equipmentSelect.classList.remove('loading');
        console.error('Error loading equipment:', error);
        showError('Error loading equipment options');
      });
  }

  // Load building options
  function loadBuildingOptions() {
    buildingSelect.classList.add('loading');

    fetch('api/get_equipment_data.php?action=buildings')
      .then((response) => response.json())
      .then((data) => {
        buildingSelect.classList.remove('loading');

        if (data.success) {
          buildingSelect.innerHTML =
            '<option value="">-- Select Building --</option>';

          data.data.forEach((building) => {
            const option = document.createElement('option');
            option.value = building.id;
            option.textContent = building.building_name;
            option.setAttribute('data-department', building.department);
            buildingSelect.appendChild(option);
          });
        } else {
          showError('Failed to load building options');
        }
      })
      .catch((error) => {
        buildingSelect.classList.remove('loading');
        console.error('Error loading buildings:', error);
        showError('Error loading building options');
      });
  }

  // Load room options based on selected building
  function loadRoomOptions(buildingId) {
    roomSelect.innerHTML = '<option value="">Loading rooms...</option>';

    fetch(`api/get_equipment_data.php?action=rooms&building_id=${buildingId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          roomSelect.innerHTML = '<option value="">-- Select Room --</option>';

          data.data.forEach((room) => {
            const option = document.createElement('option');
            option.value = room.id;
            option.textContent = `${room.room_name} (${room.room_type}, Capacity: ${room.capacity})`;
            roomSelect.appendChild(option);
          });
        } else {
          roomSelect.innerHTML = '<option value="">No rooms available</option>';
          showError('Failed to load room options');
        }
      })
      .catch((error) => {
        roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
        console.error('Error loading rooms:', error);
        showError('Error loading room options');
      });
  }

  // Check if form is valid and enable/disable submit button
  function checkFormValidity() {
    const equipmentSelected =
      equipmentSelect.value && selectedEquipmentName.value;
    const buildingSelected = buildingSelect.value && selectedBuildingName.value;
    const roomSelected = roomSelect.value && selectedRoomName.value;

    if (equipmentSelected && buildingSelected && roomSelected) {
      // Verify that the selected equipment exists in the selected room
      verifyEquipmentInRoom();
    } else {
      manualSubmitButton.disabled = true;
    }
  }

  // Verify that the equipment exists in the selected room
  function verifyEquipmentInRoom() {
    const equipmentId = selectedEquipmentId.value;
    const roomId = roomSelect.value;

    if (!equipmentId || !roomId) {
      manualSubmitButton.disabled = true;
      return;
    }

    fetch(
      `api/get_equipment_data.php?action=equipment_in_room&equipment_id=${equipmentId}&room_id=${roomId}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.exists) {
          manualSubmitButton.disabled = false;
          showSuccess('Equipment verified in selected room');
        } else if (data.success && !data.exists) {
          manualSubmitButton.disabled = true;
          showEquipmentNotFoundAlert(data);
        } else {
          manualSubmitButton.disabled = true;
          showError('Error verifying equipment location');
        }
      })
      .catch((error) => {
        console.error('Error verifying equipment:', error);
        manualSubmitButton.disabled = true;
        showError('Error verifying equipment location');
      });
  }

  // Show equipment not found modal with suggestions
  function showEquipmentNotFoundAlert(data) {
    const modal = document.getElementById('equipment-validation-modal');
    const errorText = document.getElementById('validation-error-text');
    const alternativesDiv = document.getElementById('validation-alternatives');
    const noAlternativesDiv = document.getElementById(
      'validation-no-alternatives'
    );
    const alternativesList = document.getElementById('alternatives-list');

    // Set error message
    errorText.textContent = `Equipment "${data.equipment_name}" is not available in ${data.room_name}, ${data.building_name}.`;

    // Clear previous alternatives
    alternativesList.innerHTML = '';

    if (data.alternatives && data.alternatives.length > 0) {
      alternativesDiv.style.display = 'block';
      noAlternativesDiv.style.display = 'none';

      data.alternatives.forEach((alt) => {
        const altItem = document.createElement('div');
        altItem.className = 'alternative-item';

        const location = document.createElement('div');
        location.className = 'alternative-location';
        location.textContent = `${alt.room_name}, ${alt.building_name}`;

        const status = document.createElement('span');
        status.className = `alternative-status status-${alt.status.replace(
          '_',
          '-'
        )}`;
        status.textContent =
          alt.status === 'working'
            ? '✓ Working'
            : `⚠ ${alt.status.replace('_', ' ')}`;

        altItem.appendChild(location);
        altItem.appendChild(status);
        alternativesList.appendChild(altItem);
      });
    } else {
      alternativesDiv.style.display = 'none';
      noAlternativesDiv.style.display = 'block';
    }

    // Show modal
    modal.style.display = 'flex';
  }

  // Close modal when clicking outside
  document
    .getElementById('equipment-validation-modal')
    .addEventListener('click', function (e) {
      if (e.target === this) {
        closeValidationModal();
      }
    });

  // Show success message
  function showSuccess(message) {
    console.log('Success:', message);
  }

  // Show error message
  function showError(message) {
    console.error('Error:', message);
  }

  // Form submission
  manualSubmitButton.addEventListener('click', function () {
    if (
      !selectedEquipmentId.value ||
      !selectedRoomName.value ||
      !selectedBuildingName.value
    ) {
      alert('Please select equipment, building, and room');
      return;
    }

    // Final validation before submission
    if (manualSubmitButton.disabled) {
      alert(
        'Please verify that the selected equipment exists in the chosen room before proceeding.'
      );
      return;
    }

    // Store the manually entered equipment data
    const equipmentData = {
      name: selectedEquipmentName.value,
      room: selectedRoomName.value,
      building: selectedBuildingName.value,
      equipment_id: selectedEquipmentId.value,
      user_equipment_id: equipmentIdInput.value || null,
      source: 'manual_entry',
    };

    // Save to sessionStorage and redirect
    sessionStorage.setItem('scannedEquipment', JSON.stringify(equipmentData));
    window.location.href = 'report-equipment-issue.php';
  });

  // Add this event listener for the Process QR Code button
  processImageButton.addEventListener('click', function () {
    // Show loading state
    processImageButton.disabled = true;
    processImageButton.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing QR code...';

    // Get the compressed image URL
    const imageUrl = previewImage.dataset.compressedUrl;

    if (!imageUrl) {
      alert('No image uploaded or image processing failed. Please try again.');
      processImageButton.innerHTML = 'Process QR Code';
      processImageButton.disabled = false;
      return;
    }

    // Check if BarcodeDetector API is available
    if ('BarcodeDetector' in window) {
      processWithNativeBarcodeDetector(imageUrl);
    } else {
      processWithHtml5QrCode(imageUrl);
    }
  });

  // Function to process with native BarcodeDetector API
  async function processWithNativeBarcodeDetector(imageUrl) {
    try {
      // Create a BarcodeDetector instance
      const barcodeDetector = new BarcodeDetector({
        formats: ['qr_code'],
      });

      // Load the image
      const img = new Image();
      img.crossOrigin = 'Anonymous'; // Handle CORS if needed

      img.onload = async function () {
        try {
          // Detect QR codes in the image
          const barcodes = await barcodeDetector.detect(img);

          if (barcodes.length > 0) {
            // QR code detected
            const qrContent = barcodes[0].rawValue;
            handleSuccessfulScan(qrContent);
          } else {
            // No QR code found
            showScanError(
              'No QR code found in the image. Please try another image or ensure the QR code is clearly visible.'
            );
          }
        } catch (error) {
          console.error('QR detection error:', error);
          showScanError('Error detecting QR code. Please try again.');
        }
      };

      img.onerror = function () {
        console.error('Image loading error');
        showScanError('Failed to load the image. Please try again.');
      };

      // Set the source to load the image
      img.src = imageUrl;
    } catch (error) {
      console.error('Error initializing BarcodeDetector:', error);
      // Fallback to Html5QrCode if native detection fails
      processWithHtml5QrCode(imageUrl);
    }
  }

  // Function to process with Html5QrCode library
  function processWithHtml5QrCode(imageUrl) {
    // Initialize the scanner
    const html5QrCode = new Html5Qrcode('qr-reader-container');

    // Skip the initial attempt that's causing the error
    fetch(imageUrl)
      .then((response) => response.blob())
      .then((blob) => {
        const file = new File([blob], 'qr-image.jpg', {
          type: 'image/jpeg',
        });
        return html5QrCode.scanFile(file, true);
      })
      .then((decodedText) => {
        handleSuccessfulScan(decodedText);
      })
      .catch((err) => {
        console.error('QR scanning failed:', err);
        showScanError(
          'Could not detect a valid QR code. Please try another image or ensure the QR code is clearly visible.'
        );
      });
  }

  // Function to handle successful scan (reuse existing logic)
  function handleSuccessfulScan(decodedText) {
    // Show success indicator
    const successAlert = document.createElement('div');
    successAlert.className = 'qr-success-alert';
    successAlert.innerHTML =
      '<i class="fa fa-check-circle"></i> QR Code detected! Redirecting...';
    document.querySelector('.qr-scan-container').appendChild(successAlert);

    // Navigate to the equipment issue report page after a brief delay
    setTimeout(() => {
      window.location.href = decodedText;
    }, 500);
  }

  // Function to show scan error
  function showScanError(message) {
    alert(message);
    processImageButton.innerHTML = 'Process QR Code';
    processImageButton.disabled = false;
  }
});

// Global functions for modal (outside DOMContentLoaded)
function closeValidationModal() {
  const modal = document.getElementById('equipment-validation-modal');
  modal.style.display = 'none';
}
