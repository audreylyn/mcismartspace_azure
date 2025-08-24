<?php
require_once '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Check if all required parameters are set
$requiredParams = [
    'requestId',
    'activityName',
    'buildingName',
    'roomName',
    'reservationDate',
    'startTime',
    'endTime',
    'participants',
    'purpose'
];

// Get parameters from either POST or GET
$requestData = $_GET ?: $_POST;

foreach ($requiredParams as $param) {
    if (!isset($requestData[$param])) {
        die("Error: Missing required parameter: $param");
    }
}

// Get data
$requestId = $requestData['requestId'];
$activityName = $requestData['activityName'];
$buildingName = $requestData['buildingName'];
$roomName = $requestData['roomName'];
$reservationDate = $requestData['reservationDate'];
$startTime = $requestData['startTime'];
$endTime = $requestData['endTime'];
$participants = $requestData['participants'];
$purpose = $requestData['purpose'];
$status = $requestData['status'] ?? 'approved';

// Get current date
$currentDate = date('F j, Y');

// Get user info from session
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
db();

// Get user information based on role
if ($userRole == 'Student') {
    $sql = "SELECT FirstName, LastName, Department, Program, YearSection FROM student WHERE StudentID = ?";
} else if ($userRole == 'Teacher') {
    $sql = "SELECT FirstName, LastName, Department, Position, Specialization FROM teacher WHERE TeacherID = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Close database connection
$conn->close();

// HTML content generation
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Room Requisition Form</title>
    <!-- Include html2pdf.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            font-size: 12pt;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            background-color: #f8f8f5;
        }

        #pdf-content {
            width: 200mm;
            min-height: 275mm;
            max-height: 275mm;
            margin: 0 auto;
            padding: 10mm 25mm 0 25mm;
            box-sizing: border-box;
            position: relative;
            background-color: #fff;
            box-shadow: none;
            border: none;
            color: #000;
            overflow: hidden;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(45deg);
            font-size: 80px;
            opacity: 0.15;
            z-index: -1;
            font-weight: bold;
            text-align: center;
            width: 100%;
            font-family: 'Times New Roman', Times, serif;
        }

        .approved {
            color: #006400;
        }

        .rejected {
            color: #8B0000;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 5px;
        }

        .logo {
            width: 60px;
            height: auto;
            margin: 0 auto 5px;
            display: block;
        }

        h1 {
            font-size: 18pt;
            margin: 5px 0 3px;
            font-weight: bold;
            color: #111;
        }

        .subtitle {
            font-size: 12pt;
            margin: 2px 0;
            font-style: italic;
        }

        .office-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0 2px;
            color: #333;
        }

        .form-title {
            font-size: 12pt;
            margin: 2px 0;
        }

        .horizontal-line {
            border-top: 1px solid #000;
            margin: 15px 0;
            opacity: 0.7;
        }

        .date-line {
            margin: 10px 0;
            text-align: left;
            font-weight: normal;
        }

        .addressee {
            margin: 15px 0;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .thru-line {
            margin: 10px 0;
            font-weight: normal;
        }

        .subject-line {
            margin: 15px 0;
            text-decoration: underline;
        }

        .salutation {
            margin: 15px 0;
        }

        .content {
            margin: 15px 0;
            text-indent: 40px;
            text-align: justify;
            line-height: 1.6;
        }

        .form-fields {
            margin: 15px 0;
            padding-left: 15px;
        }

        .form-field {
            margin-bottom: 8px;
            display: flex;
        }

        .form-label {
            width: 200px;
            font-weight: normal;
            color: #444;
        }

        .form-value {
            flex-grow: 1;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
            font-weight: 500;
        }

        .agreement {
            text-align: center;
            margin: 10px 0;
            font-style: italic;
            font-size: 10pt;
            color: #444;
        }

        .signatures {
            margin-top: 10px;
        }

        .signature-row {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
        }

        .signature {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 20px;
            margin-bottom: 2px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-label {
            font-size: 8pt;
            text-align: center;
            color: #444;
            font-weight: 500;
            margin-bottom: 1px;
        }

        .noted-signature {
            text-align: center;
            margin-top: 10px;
        }

        .button-container {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-button {
            margin: 0 10px;
            padding: 10px 20px;
            background-color: #1f71ae;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .action-button:hover {
            background-color: #165b8e;
        }

        #backButton {
            background-color: #6c757d;
        }

        #backButton:hover {
            background-color: #5a6268;
        }

        .no-print {
            display: none;
        }

        /* Responsive styles for mobile devices */
        @media only screen and (max-width: 768px) {
            body {
                font-size: 11pt;
                padding: 10px;
            }

            #pdf-content {
                width: 100%;
                padding: 10px 15px 0 15px;
                margin: 0;
                min-height: auto;
                max-height: none;
                overflow: visible;
            }

            .button-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 10px 5px;
            }

            .action-button {
                margin: 5px 0;
                width: 100%;
                max-width: 250px;
            }

            .logo {
                width: 50px;
            }

            h1 {
                font-size: 16pt;
            }

            .office-title {
                font-size: 13pt;
            }

            .form-field {
                flex-direction: column;
                margin-bottom: 15px;
            }

            .form-label {
                width: 100%;
                margin-bottom: 3px;
            }

            .signature-row {
                flex-direction: column;
                align-items: center;
            }

            .signature {
                width: 90%;
                margin-bottom: 20px;
            }
        }

        /* Small mobile devices */
        @media only screen and (max-width: 480px) {
            body {
                font-size: 10pt;
                padding: 5px;
            }

            #pdf-content {
                padding: 5px 10px 0 10px;
            }

            h1 {
                font-size: 14pt;
            }

            .date-line,
            .addressee,
            .thru-line,
            .subject-line,
            .salutation,
            .content {
                margin: 8px 0;
            }
        }
    </style>
</head>

<body>

    <div class="button-container">
        <button class="action-button" id="backButton" onclick="window.location.href='users_reservation_history.php';">
            <i class="fa fa-arrow-left"></i> Back to Reservation
        </button>
        <button class="action-button" id="downloadPdf">
            <i class="fa fa-download"></i> Download PDF
        </button>
        <div id="download-message" style="display: none; margin-top: 10px; color: #28a745;">
            <i class="fa fa-check-circle"></i> PDF is being prepared...
        </div>
    </div>

    <div id="pdf-content">
        <?php if ($status == 'approved'): ?>
            <div class="watermark approved">APPROVED</div>
        <?php elseif ($status == 'rejected'): ?>
            <div class="watermark rejected">REJECTED</div>
        <?php endif; ?>

        <div class="header">
            <img src="../public/assets/logo.webp" alt="Meycauayan College Logo" class="logo">
            <h1>Meycauayan College, Inc.</h1>
            <div class="subtitle">City of Meycauayan Bulacan</div>
            <div class="office-title">Deans' Office</div>
            <div class="form-title">Requisition Form</div>
            <div class="form-title">(Room for Student Activity)</div>
        </div>

        <div class="date-line">
            <span>Date: </span>
            <span><?php echo $currentDate; ?></span>
        </div>

        <div class="addressee">
            THE VICE PRESIDENT FOR ACADEMIC AFFAIR<br>
            Meycauayan College
        </div>

        <div class="thru-line">
            Thru: Deans' Office
        </div>

        <div class="subject-line">
            Subject: Confirmation of Room Request
        </div>

        <div class="salutation">
            Madam,
        </div>

        <div class="content">
            This is to confirm that <?php echo htmlspecialchars($userData['FirstName'] . ' ' . $userData['LastName']); ?> from
            <?php 
            if ($userRole == 'Student') {
                echo htmlspecialchars($userData['Department'] . ' - ' . $userData['Program'] . ' ' . $userData['YearSection']);
            } else {
                echo htmlspecialchars($userData['Department'] . ' - ' . $userData['Position']);
            }
            ?>
            has formally requested the use of a room for an upcoming activity.
        </div>

        <div class="form-fields">
            <div class="form-field">
                <div class="form-label">Requested Room:</div>
                <div class="form-value"><?php echo htmlspecialchars($roomName . ', ' . $buildingName); ?></div>
            </div>
            <div class="form-field">
                <div class="form-label">Activity:</div>
                <div class="form-value"><?php echo htmlspecialchars($activityName); ?></div>
            </div>
            <div class="form-field">
                <div class="form-label">Purpose:</div>
                <div class="form-value"><?php echo htmlspecialchars($purpose); ?></div>
            </div>
            <div class="form-field">
                <div class="form-label">Date/Time of Activity:</div>
                <div class="form-value"><?php echo htmlspecialchars($reservationDate . ', ' . $startTime . ' - ' . $endTime); ?></div>
            </div>
            <?php if ($userRole == 'Student'): ?>
            <div class="form-field">
                <div class="form-label">Program/Section:</div>
                <div class="form-value"><?php echo htmlspecialchars($userData['Program'] . ' ' . $userData['YearSection']); ?></div>
            </div>
            <?php else: ?>
            <div class="form-field">
                <div class="form-label">Position:</div>
                <div class="form-value"><?php echo htmlspecialchars($userData['Position']); ?></div>
            </div>
            <?php endif; ?>
            <div class="form-field">
                <div class="form-label">Department:</div>
                <div class="form-value"><?php echo htmlspecialchars($userData['Department']); ?></div>
            </div>
            <div class="form-field">
                <div class="form-label">No. of expected participants:</div>
                <div class="form-value"><?php echo htmlspecialchars($participants); ?></div>
            </div>
        </div>

        <div class="agreement">
            We agree to follow the terms and conditions for using the assigned room/s at Meycauayan College.
        </div>

        <div class="signatures">
            <div class="signature-row">
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-label">NAME & SIGNATURE</div>
                    <div>Requested by:</div>
                </div>
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-label">NAME & SIGNATURE</div>
                    <div>Assisted by:</div>
                </div>
            </div>

            <div class="noted-signature">
                <div class="signature-line"></div>
                <div class="signature-label">NAME & SIGNATURE</div>
                <div>Noted by:</div>
            </div>
        </div>
    </div>



    <script>
        document.getElementById('downloadPdf').addEventListener('click', function() {
            // Get the element to be converted to PDF
            const pdfContent = document.getElementById('pdf-content');

            // Show download message
            document.getElementById('download-message').style.display = 'block';

            // Set options for html2pdf
            const opt = {
                margin: 0,
                filename: 'room_requisition_form_<?php echo $requestId; ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            // Generate and download PDF
            html2pdf().set(opt).from(pdfContent).save().then(() => {
                // Change message after download starts
                document.getElementById('download-message').innerHTML = '<i class="fa fa-check-circle"></i> PDF downloaded successfully!';
            });
        });
    </script>
</body>

</html>