<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="qr/qr.css">
    <link rel="stylesheet" href="qr/modal.css">

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
                            <label for="building-select" class="form-label">Building</label>
                            <div class="input-wrapper">
                                <select class="form-control" id="building-select">
                                    <option value="">-- Select Building --</option>
                                </select>
                                <div class="validation-feedback" id="building-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="room-select" class="form-label">Room</label>
                            <div class="input-wrapper">
                                <select class="form-control" id="room-select" disabled>
                                    <option value="">-- Select Building First --</option>
                                </select>
                                <div class="validation-feedback" id="room-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="equipment-unit-select" class="form-label">Select Equipment Unit</label>
                            <div class="input-wrapper">
                                <select class="form-control" id="equipment-unit-select" disabled>
                                    <option value="">-- Select Room First --</option>
                                </select>
                                <div class="validation-feedback" id="equipment-unit-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="equipment-id" class="form-label">Equipment ID (Optional)</label>
                            <input type="text" class="form-control" id="equipment-id" placeholder="Enter equipment ID if known">
                        </div>

                        <!-- Hidden fields to store selected data -->
                        <input type="hidden" id="selected-unit-id">
                        <input type="hidden" id="selected-room-name">
                        <input type="hidden" id="selected-building-name">
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

    <!-- Equipment Validation Modal -->
    <div id="equipment-validation-modal" class="validation-modal" style="display: none;">
        <div class="validation-modal-content">
            <div class="validation-modal-header">
                <h3 class="validation-modal-title">
                    <i class="fa fa-exclamation-triangle"></i>
                    Equipment Not Available
                </h3>
                <button type="button" class="validation-modal-close" onclick="closeValidationModal()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="validation-modal-body">
                <div class="validation-error-message">
                    <p id="validation-error-text"></p>
                </div>
                <div id="validation-alternatives" class="validation-alternatives" style="display: none;">
                    <h4>Available Locations:</h4>
                    <div id="alternatives-list" class="alternatives-list"></div>
                    <p class="validation-suggestion">Please select one of these rooms or choose a different equipment.</p>
                </div>
                <div id="validation-no-alternatives" class="validation-no-alternatives" style="display: none;">
                    <p class="validation-suggestion">This equipment is not available in any room. Please choose a different equipment.</p>
                </div>
            </div>
            <div class="validation-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeValidationModal()">
                    <i class="fa fa-check"></i> Got It
                </button>
            </div>
        </div>
    </div>


    <!-- Include QR code scanner library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="qr/qr.js"></script>

</body>

</html>