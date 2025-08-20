<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            background-color: #0a3320;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .right_col {
            background: transparent;
            width: 100%;
            max-width: 1200px;
            padding: 0 15px;
        }

        .qr-scan-container {
            max-width: 420px;
            margin: 0 auto;
            padding: 1.5rem;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .scan-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #0f4228;
        }

        .qr-preview {
            width: 100%;
            height: 220px;
            border: 2px dashed rgba(15, 66, 40, 0.3);
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            background-color: #f8f9fa;
        }

        .qr-instructions {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.4;
        }

        .scan-button {
            background-color: #0f4228;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 0.6rem;
            box-shadow: 0 4px 6px rgba(15, 66, 40, 0.1);
            box-sizing: border-box;
        }

        .scan-button:hover {
            background-color: #13503a;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(15, 66, 40, 0.15);
        }

        .back-button {
            background-color: #f1f5f9;
            color: #4b5563;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-decoration: none;
            display: block;
            box-sizing: border-box;
            text-align: center;
        }

        .back-button:hover {
            background-color: #e5e7eb;
            transform: translateY(-2px);
        }

        .scan-description {
            margin-top: 1rem;
            margin-bottom: 1rem;
            color: #6c757d;
            font-size: 13px;
        }

        #qr-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
            display: none;
        }

        .camera-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
            background-color: #f8f9fa;
            padding: 20px;
        }

        .scanning-effect {
            position: absolute;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, transparent, #0f4228, transparent);
            z-index: 10;
            animation: scan 2s linear infinite;
            display: none;
        }

        @keyframes scan {
            0% {
                top: 0;
            }

            50% {
                top: calc(100% - 5px);
            }

            100% {
                top: 0;
            }
        }

        .option-tabs {
            display: flex;
            margin-bottom: 1rem;
            border-radius: 8px;
            overflow: hidden;
            background-color: #f1f5f9;
            padding: 3px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .option-tab {
            flex: 1;
            padding: 0.6rem 0.5rem;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            color: #64748b;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .option-tab.active {
            background-color: #0f4228;
            color: white;
            box-shadow: 0 4px 8px rgba(15, 66, 40, 0.15);
        }

        .option-content {
            display: none;
        }

        .option-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading indicator styles */
        .loading .qr-preview:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .loading .qr-preview:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            margin-top: -20px;
            margin-left: -20px;
            border: 3px solid rgba(15, 66, 40, 0.1);
            border-top: 3px solid #0f4228;
            border-radius: 50%;
            z-index: 11;
            animation: spin 1s linear infinite;
        }

        .qr-success-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #0f4228;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
            font-weight: 500;
        }

        .qr-success-alert i {
            margin-right: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .upload-area {
            border: 2px dashed rgba(15, 66, 40, 0.3);
            border-radius: 10px;
            padding: 1.5rem 1rem;
            text-align: center;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .upload-area:hover {
            border-color: #0f4228;
            background-color: #f0f9f6;
            transform: translateY(-2px);
        }

        .upload-icon {
            font-size: 2rem;
            color: #0f4228;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        .upload-text {
            margin-bottom: 0.3rem;
            color: #334155;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .upload-subtext {
            color: #64748b;
            font-size: 0.8rem;
        }

        #file-input {
            display: none;
        }

        #preview-image {
            max-width: 100%;
            max-height: 240px;
            border-radius: 10px;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* Manual entry form styling */
        .manual-entry-form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 0.8rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 500;
            color: #334155;
            text-align: left;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.6rem 0.8rem;
            font-size: 0.9rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #0f4228;
            box-shadow: 0 0 0 3px rgba(15, 66, 40, 0.1);
        }

        /* Validation styles */
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

        #equipment-name:disabled,
        #equipment-room:disabled,
        #equipment-building:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <!-- Page content -->
    <div class="right_col" role="main">
        <div class="container">
            <div class="qr-scan-container">
                <h2 class="scan-title">Scan QR Code</h2>

                <!-- Option tabs -->
                <div class="option-tabs">
                    <div class="option-tab active" data-tab="scan">
                        <i class="fa fa-camera"></i> Camera Scan
                    </div>
                    <div class="option-tab" data-tab="upload">
                        <i class="fa fa-upload"></i> Upload Image
                    </div>
                    <div class="option-tab" data-tab="manual">
                        <i class="fa fa-keyboard-o"></i> Manual Entry
                    </div>
                </div>

                <!-- Camera scan content -->
                <div class="option-content active" id="scan-content">
                    <div class="qr-preview">
                        <div class="scanning-effect" id="scanning-effect"></div>
                        <video id="qr-video" playsinline></video>
                        <div class="camera-placeholder" id="camera-placeholder">
                            <p class="qr-instructions">Camera preview will appear here.</p>
                            <p class="qr-instructions">Point your camera at an equipment QR code.</p>
                        </div>
                    </div>

                    <p class="scan-description">Scan the QR code on any equipment to report a malfunction or issue.</p>

                    <button id="start-button" class="scan-button">
                        Start Scanning
                    </button>
                </div>

                <!-- Upload image content -->
                <div class="option-content" id="upload-content">
                    <div class="upload-area" id="upload-area">
                        <input type="file" id="file-input" accept="image/*">
                        <div class="upload-icon">
                            <i class="fa fa-cloud-upload"></i>
                        </div>
                        <div class="upload-text">Click to upload a QR code image</div>
                        <div class="upload-subtext">or drag and drop image here</div>
                    </div>

                    <div class="qr-preview" id="image-preview-container">
                        <img id="preview-image" src="" alt="Preview">
                    </div>

                    <p class="scan-description">Upload a photo of a QR code to report an equipment issue.</p>

                    <button id="process-image-button" class="scan-button" disabled>
                        Process QR Code
                    </button>
                </div>

                <!-- Manual entry content -->
                <div class="option-content" id="manual-content">
                    <div class="manual-entry-form">
                        <div class="form-group mb-3">
                            <label for="equipment-name-input" class="form-label">Equipment Name</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-control" id="equipment-name-input" placeholder="Enter equipment name">
                                <div class="validation-feedback" id="equipment-name-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group mb-3" id="location-select-container" style="display: none;">
                            <label for="location-select" class="form-label">Select Location</label>
                            <select class="form-control" id="location-select">
                                <option value="">-- Select location --</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="equipment-id" class="form-label">Equipment ID (Optional)</label>
                            <input type="text" class="form-control" id="equipment-id" placeholder="Enter equipment ID if known">
                        </div>

                        <div class="form-group mb-3">
                            <label for="equipment-room" class="form-label">Room</label>
                            <input type="text" class="form-control" id="equipment-room" placeholder="Will be auto-filled based on location">
                        </div>

                        <div class="form-group mb-3">
                            <label for="equipment-building" class="form-label">Building</label>
                            <input type="text" class="form-control" id="equipment-building" placeholder="Will be auto-filled based on location">
                        </div>
                    </div>

                    <p class="scan-description">Manually enter equipment details if you're unable to scan the QR code.</p>

                    <button id="manual-submit-button" class="scan-button" disabled>
                        Continue to Report
                    </button>
                </div>

                <a href="users_browse_room.php" class="back-button">
                    Go Back
                </a>
            </div>
        </div>
    </div>

    <!-- Hidden container for QR code reader -->
    <div id="qr-reader-container" style="display: none;"></div>

    <!-- Include QR code scanner library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));

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

            startButton.addEventListener('click', function() {
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
                            facingMode: "environment"
                        }
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
                    alert('Could not access camera. Please ensure you have granted camera permissions or try the upload option instead.');
                }
            }

            // Function to stop camera preview
            function stopCameraPreview() {
                if (!cameraActive) return;

                // Stop video stream
                if (videoStream) {
                    videoStream.getTracks().forEach(track => track.stop());
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
                        formats: ['qr_code']
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
                            successAlert.innerHTML = '<i class="fa fa-check-circle"></i> QR Code detected! Redirecting...';
                            document.querySelector('.qr-scan-container').appendChild(successAlert);

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
                html5QrCode = new Html5Qrcode("qr-reader-container");

                // Configuration for better performance
                const config = {
                    fps: 10, // Lower fps for better performance
                    qrbox: {
                        width: 250,
                        height: 250
                    },
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true // Use native API if available
                    },
                    rememberLastUsedCamera: true,
                    aspectRatio: 1.0 // Square aspect ratio for QR codes
                };

                // Start scanning
                html5QrCode.start({
                        facingMode: "environment"
                    }, // Use the back camera
                    config,
                    onScanSuccess,
                    onScanFailure
                ).catch(function(err) {
                    console.error('Error starting scanner:', err);
                    alert('Could not access camera. Please ensure you have granted camera permissions or try the upload option instead.');
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
                    html5QrCode.stop().then(() => {
                        // Update UI to show we're not scanning but still have preview
                        scanningEffect.style.display = 'none';
                        startButton.textContent = 'Start Scanning';
                        scanning = false;
                    }).catch(err => {
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
                successAlert.innerHTML = '<i class="fa fa-check-circle"></i> QR Code detected! Redirecting...';
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
            const imagePreviewContainer = document.getElementById('image-preview-container');

            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });

            // Drag and drop functionality
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.style.borderColor = '#10b981';
                uploadArea.style.backgroundColor = '#f8fafc';
            });

            uploadArea.addEventListener('dragleave', function() {
                uploadArea.style.borderColor = '#ccc';
                uploadArea.style.backgroundColor = '';
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.style.borderColor = '#ccc';
                uploadArea.style.backgroundColor = '';

                if (e.dataTransfer.files.length) {
                    handleFile(e.dataTransfer.files[0]);
                }
            });

            fileInput.addEventListener('change', function() {
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
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';

                    // Show loading state immediately
                    processImageButton.disabled = false;
                    processImageButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Compressing image...';

                    // Upload for server-side compression
                    uploadForCompression(file);
                };
                reader.readAsDataURL(file);
            }

            // Server-side compression
            function uploadForCompression(file) {
                console.time("Server Compression");
                const formData = new FormData();
                formData.append('qrImage', file);

                // Show compression is happening
                processImageButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Compressing image...';

                fetch('compress-image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log("Server response status:", response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.timeEnd("Server Compression");
                        if (data.success) {
                            console.log("Image compressed successfully:", data.width + "x" + data.height);
                            // Update preview with compressed image
                            previewImage.src = data.imageUrl + "?t=" + new Date().getTime(); // Add timestamp to avoid cache
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
                    .catch(error => {
                        console.timeEnd("Server Compression");
                        console.error('Upload error:', error);

                        // Show more detailed error in console for debugging
                        console.log("Error details:", {
                            message: error.message,
                            name: error.name,
                            stack: error.stack
                        });

                        alert('Error uploading image. Please check if the server is accessible and try again.');
                        processImageButton.innerHTML = 'Process QR Code';
                        processImageButton.disabled = false;
                    });
            }

            // Manual entry form validation
            const equipmentNameInput = document.getElementById('equipment-name-input');
            const equipmentNameFeedback = document.getElementById('equipment-name-feedback');
            const equipmentIdInput = document.getElementById('equipment-id');
            const equipmentRoomInput = document.getElementById('equipment-room');
            const equipmentBuildingInput = document.getElementById('equipment-building');
            const locationSelectContainer = document.getElementById('location-select-container');
            const locationSelect = document.getElementById('location-select');
            const manualSubmitButton = document.getElementById('manual-submit-button');

            // Disable room and building inputs initially
            equipmentRoomInput.disabled = true;
            equipmentBuildingInput.disabled = true;

            let validateTimeout;
            let validEquipment = false;
            let equipmentLocations = [];

            // Equipment name validation
            equipmentNameInput.addEventListener('input', function() {
                const equipmentName = this.value.trim();

                // Clear previous validation
                clearValidation();

                // Hide location selector
                locationSelectContainer.style.display = 'none';
                locationSelect.innerHTML = '<option value="">-- Select location --</option>';

                // Don't validate empty inputs
                if (!equipmentName) {
                    manualSubmitButton.disabled = true;
                    return;
                }

                // Add delay to prevent too many requests while typing
                clearTimeout(validateTimeout);
                validateTimeout = setTimeout(() => {
                    validateEquipmentName(equipmentName);
                }, 500);
            });

            // Location selection change handler
            locationSelect.addEventListener('change', function() {
                const selectedLocation = this.value;

                if (selectedLocation) {
                    // Parse the selected location (format: "room|building")
                    const [room, building] = selectedLocation.split('|');

                    // Fill in the room and building fields
                    equipmentRoomInput.value = room;
                    equipmentBuildingInput.value = building;

                    // Enable the submit button
                    manualSubmitButton.disabled = false;
                } else {
                    // Clear the room and building fields
                    equipmentRoomInput.value = '';
                    equipmentBuildingInput.value = '';

                    // Disable the submit button
                    manualSubmitButton.disabled = true;
                }
            });

            // Function to validate equipment name against database
            function validateEquipmentName(equipmentName) {
                // Show loading state
                equipmentNameFeedback.innerHTML = '<span class="validation-spinner"></span> Checking equipment...';

                // Send AJAX request to validate
                fetch(`validate-equipment.php?name=${encodeURIComponent(equipmentName)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            // Equipment exists in database
                            validEquipment = true;
                            showValidFeedback('Equipment found');

                            // Store equipment locations
                            equipmentLocations = data.locations;

                            // Populate location dropdown
                            if (equipmentLocations.length > 0) {
                                populateLocationDropdown(equipmentLocations);
                                locationSelectContainer.style.display = 'block';
                            } else {
                                showInvalidFeedback("Equipment found but no locations are assigned");
                                validEquipment = false;
                                manualSubmitButton.disabled = true;
                            }
                        } else {
                            // Equipment does not exist
                            validEquipment = false;
                            showInvalidFeedback("Equipment not found in database");

                            // Disable submit button
                            manualSubmitButton.disabled = true;

                            // Clear fields
                            equipmentRoomInput.value = '';
                            equipmentBuildingInput.value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Validation error:', error);
                        equipmentNameFeedback.textContent = 'Error checking equipment. Please try again.';
                        equipmentNameFeedback.className = 'validation-feedback validation-error';
                        equipmentNameInput.classList.add('is-invalid');
                        manualSubmitButton.disabled = true;
                    });
            }

            // Populate location dropdown with available locations
            function populateLocationDropdown(locations) {
                // Clear current options
                locationSelect.innerHTML = '<option value="">-- Select location --</option>';

                // Add locations as options
                locations.forEach(location => {
                    const option = document.createElement('option');
                    option.value = `${location.room}|${location.building}`;
                    option.textContent = `${location.room}, ${location.building}`;
                    locationSelect.appendChild(option);
                });
            }

            // Functions to show validation feedback
            function showValidFeedback(message) {
                equipmentNameFeedback.textContent = message;
                equipmentNameFeedback.className = 'validation-feedback validation-success';
                equipmentNameInput.classList.remove('is-invalid');
                equipmentNameInput.classList.add('is-valid');
            }

            function showInvalidFeedback(message) {
                equipmentNameFeedback.textContent = message;
                equipmentNameFeedback.className = 'validation-feedback validation-error';
                equipmentNameInput.classList.remove('is-valid');
                equipmentNameInput.classList.add('is-invalid');
            }

            function clearValidation() {
                equipmentNameFeedback.textContent = '';
                equipmentNameFeedback.className = 'validation-feedback';
                equipmentNameInput.classList.remove('is-valid', 'is-invalid');
                validEquipment = false;
                manualSubmitButton.disabled = true;
            }

            // Form submission
            manualSubmitButton.addEventListener('click', function() {
                // Additional validation before submission
                if (!validEquipment) {
                    showInvalidFeedback("Please enter a valid equipment name");
                    return;
                }

                if (!equipmentRoomInput.value || !equipmentBuildingInput.value) {
                    alert('Please select a location for the equipment');
                    return;
                }

                // Get selected equipment name
                const equipmentName = equipmentNameInput.value.trim();

                // Get room and building
                const room = equipmentRoomInput.value;
                const building = equipmentBuildingInput.value;

                // Get equipment ID if provided
                const equipmentId = equipmentIdInput.value.trim();

                // Construct URL to report form with parameters
                const baseUrl = window.location.origin + '/mcmod/student/report-equipment-issue.php';
                let reportUrl = `${baseUrl}?name=${encodeURIComponent(equipmentName)}&room=${encodeURIComponent(room)}&building=${encodeURIComponent(building)}`;

                // Add ID if provided
                if (equipmentId) {
                    reportUrl += `&id=${encodeURIComponent(equipmentId)}`;
                }

                // Navigate to report form
                window.location.href = reportUrl;
            });

            // Add this event listener for the Process QR Code button
            processImageButton.addEventListener('click', function() {
                // Show loading state
                processImageButton.disabled = true;
                processImageButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing QR code...';

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
                        formats: ['qr_code']
                    });

                    // Load the image
                    const img = new Image();
                    img.crossOrigin = 'Anonymous'; // Handle CORS if needed

                    img.onload = async function() {
                        try {
                            // Detect QR codes in the image
                            const barcodes = await barcodeDetector.detect(img);

                            if (barcodes.length > 0) {
                                // QR code detected
                                const qrContent = barcodes[0].rawValue;
                                handleSuccessfulScan(qrContent);
                            } else {
                                // No QR code found
                                showScanError("No QR code found in the image. Please try another image or ensure the QR code is clearly visible.");
                            }
                        } catch (error) {
                            console.error('QR detection error:', error);
                            showScanError("Error detecting QR code. Please try again.");
                        }
                    };

                    img.onerror = function() {
                        console.error('Image loading error');
                        showScanError("Failed to load the image. Please try again.");
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
                const html5QrCode = new Html5Qrcode("qr-reader-container");

                // Skip the initial attempt that's causing the error
                fetch(imageUrl)
                    .then(response => response.blob())
                    .then(blob => {
                        const file = new File([blob], "qr-image.jpg", {
                            type: "image/jpeg"
                        });
                        return html5QrCode.scanFile(file, true);
                    })
                    .then(decodedText => {
                        handleSuccessfulScan(decodedText);
                    })
                    .catch(err => {
                        console.error('QR scanning failed:', err);
                        showScanError("Could not detect a valid QR code. Please try another image or ensure the QR code is clearly visible.");
                    });
            }

            // Function to handle successful scan (reuse existing logic)
            function handleSuccessfulScan(decodedText) {
                // Show success indicator
                const successAlert = document.createElement('div');
                successAlert.className = 'qr-success-alert';
                successAlert.innerHTML = '<i class="fa fa-check-circle"></i> QR Code detected! Redirecting...';
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
    </script>
</body>

</html>