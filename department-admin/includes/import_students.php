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
    // Check for file upload errors
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $message = "File upload failed. Please try again.";
        header("Location: ../manage_students.php?status=$status&msg=" . urlencode($message));
        exit();
    }
    
    $file = $_FILES['file']['tmp_name'];
    $duplicateRecords = [];
    $invalidRecords = [];
    $successRecords = [];
    $imported = 0;
    $lineNumber = 1; // Start at line 1 (header row)

    // Validate file type
    $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($fileType), ['csv', 'xlsx', 'xls'])) {
        $message = "Only CSV and Excel files are allowed.";
        header("Location: ../manage_students.php?status=$status&msg=" . urlencode($message));
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
                // Skip completely empty rows
                if (empty(array_filter($row))) {
                    $lineNumber++;
                    continue;
                }
                
                // Ensure the row has enough columns (FirstName, LastName, Email, Program, YearSection, Password)
                if (count($row) >= 6) {
                    $firstName = trim($row[0]);
                    $lastName = trim($row[1]);
                    $email = trim($row[2]);
                    $program = trim($row[3]);
                    $yearSection = trim($row[4]);
                    $rawPassword = trim($row[5]);
                    
                    // Validate required fields
                    if (empty($firstName) || empty($lastName) || empty($email) || empty($program) || empty($yearSection) || empty($rawPassword)) {
                        $invalidRecords[] = "$email (Missing required fields at line $lineNumber)";
                        $lineNumber++;
                        continue;
                    }
                    
                    // Validate password length
                    if (strlen($rawPassword) < 8) {
                        $invalidRecords[] = "$email (Password too short - minimum 8 characters at line $lineNumber)";
                        $lineNumber++;
                        continue;
                    }
                    
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $invalidRecords[] = "$email (Invalid email format at line $lineNumber)";
                    } else {
                        // Check for duplicate email in student table
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE Email = ?");
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
                            
                            // Insert the new student record into the database
                            $stmt = $conn->prepare("INSERT INTO student (FirstName, LastName, Department, Program, YearSection, Email, Password, AdminID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            
                            if ($stmt === false) {
                                $invalidRecords[] = "$email (Database error at line $lineNumber)";
                            } else {
                                $stmt->bind_param("sssssssi", $firstName, $lastName, $adminDepartment, $program, $yearSection, $email, $password, $adminId);
                                
                                if ($stmt->execute()) {
                                    $imported++;
                                    $successRecords[] = "$firstName $lastName ($email)";
                                } else {
                                    $invalidRecords[] = "$email (Failed to insert at line $lineNumber)";
                                }
                                $stmt->close();
                            }
                        }
                    }
                } else {
                    // Invalid row format
                    $invalidRecords[] = "Invalid row format at line $lineNumber (Expected 6 columns: FirstName, LastName, Email, Program, YearSection, Password)";
                }
                $lineNumber++;
            }
            
            // Close the file handle
            fclose($fileHandle);
            
        } else {
            // Handle Excel files (requires PHPSpreadsheet)
            $message = "Excel file support is not yet implemented. Please use CSV format.";
            header("Location: ../manage_students.php?status=$status&msg=" . urlencode($message));
            exit();
        }

        // Build the response message
        $successMessage = '';
        if ($imported > 0) {
            $successMessage = "Successfully imported $imported students to $adminDepartment department";
        }
        
        $errorMessage = '';
        if (!empty($duplicateRecords) || !empty($invalidRecords)) {
            $errorMessage = "Some records were not imported:";
            
            if (!empty($duplicateRecords)) {
                $errorMessage .= "<br><strong>Duplicate Emails:</strong><br>" . implode("<br>", $duplicateRecords);
            }
            if (!empty($invalidRecords)) {
                $errorMessage .= "<br><strong>Invalid Records:</strong><br>" . implode("<br>", $invalidRecords);
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

// Redirect back to the manage students page
header("Location: ../manage_students.php?status=$status&msg=" . urlencode($message));
exit();
?>
