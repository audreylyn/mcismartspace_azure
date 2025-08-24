<?php
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Department Admin']);

// Initialize variables
$conn = db();
$adminId = $_SESSION['user_id'];
$adminDepartment = $_SESSION['department'] ?? '';

$status = 'error';
$message = 'An unknown error occurred.';

// Check if the form was submitted
if (isset($_POST['importSubmit'])) {
    $file = $_FILES['file']['tmp_name'];
    $duplicateRecords = [];
    $invalidEmails = [];
    $successRecords = [];
    $imported = 0;
    $lineNumber = 1; // Start at line 1 (header row)

    // Validate file type
    $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($fileType), ['csv', 'xlsx', 'xls'])) {
        $message = "Only CSV and Excel files are allowed.";
        header("Location: ../manage_teachers.php?status=$status&msg=" . urlencode($message));
        exit();
    }

    // Check if the uploaded file is not empty
    if ($_FILES['file']['size'] > 0) {
        
        // Handle different file types
        if (strtolower($fileType) === 'csv') {
            // Handle CSV files
            $fileHandle = fopen($file, 'r');
            
            // Skip the header row
            fgetcsv($fileHandle);
            $lineNumber++;

            // Process each row in the CSV file
            while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                // Ensure the row has enough columns (FirstName, LastName, Email, Password)
                if (count($row) >= 4) {
                    $firstName = trim($row[0]);
                    $lastName = trim($row[1]);
                    $email = trim($row[2]);
                    $rawPassword = trim($row[3]);
                    
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $invalidEmails[] = "$email (Invalid format at line $lineNumber)";
                    } else {
                        // Check for duplicate email in teacher table
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM teacher WHERE Email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $stmt->bind_result($count);
                        $stmt->fetch();
                        $stmt->close();

                        // If a duplicate is found, store the email
                        if ($count > 0) {
                            $duplicateRecords[] = "$email (Line $lineNumber)";
                        } else {
                            // Hash the password
                            $password = password_hash($rawPassword, PASSWORD_DEFAULT);
                            
                            // Insert the new teacher record into the database
                            $stmt = $conn->prepare("INSERT INTO teacher (FirstName, LastName, Department, Email, Password, AdminID) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssssi", $firstName, $lastName, $adminDepartment, $email, $password, $adminId);
                            
                            if ($stmt->execute()) {
                                $imported++;
                                $successRecords[] = "$firstName $lastName ($email)";
                            }
                            $stmt->close();
                        }
                    }
                }
                $lineNumber++;
            }
            
            // Close the file handle
            fclose($fileHandle);
            
        } else {
            // Handle Excel files (requires PHPSpreadsheet)
            $message = "Excel file support is not yet implemented. Please use CSV format.";
            header("Location: ../manage_teachers.php?status=$status&msg=" . urlencode($message));
            exit();
        }

        // Build the response message
        $successMessage = '';
        if ($imported > 0) {
            $successMessage = "Successfully imported $imported teachers to $adminDepartment department";
        }
        
        $errorMessage = '';
        if (!empty($duplicateRecords) || !empty($invalidEmails)) {
            $errorMessage = "Some records were not imported:";
            
            if (!empty($duplicateRecords)) {
                $errorMessage .= "<br><strong>Duplicate Emails:</strong><br>" . implode("<br>", $duplicateRecords);
            }
            if (!empty($invalidEmails)) {
                $errorMessage .= "<br><strong>Invalid Emails:</strong><br>" . implode("<br>", $invalidEmails);
            }
        }

        if (!empty($errorMessage)) {
            $status = 'error';
            $message = $successMessage . ($successMessage ? '<br><br>' : '') . $errorMessage;
        } elseif ($imported > 0) {
            $status = 'success';
            $message = $successMessage;
        } else {
            $status = 'error';
            $message = 'No new records were imported.';
        }

    } else {
        $message = "The uploaded file is empty.";
    }
} else {
    $message = "No file was uploaded.";
}

// Close the database connection
$conn->close();

// Redirect back to the manage teachers page
header("Location: ../manage_teachers.php?status=$status&msg=" . urlencode($message));
exit();
?>
