<?php
session_start();
require_once __DIR__ . '/../../auth/middleware.php';

// Create a new database connection
$db = db();

// Check if the form was submitted
if (isset($_POST['importSubmit'])) {
    $file = $_FILES['file']['tmp_name'];
    $duplicateRecords = [];
    $invalidEmails = [];
    $invalidDepartments = [];
    $successRecords = [];
    $imported = 0;
    $lineNumber = 1; // Start at line 1 (header row)

    // Validate file type
    $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if ($fileType != 'csv') {
        $_SESSION['error_message'] = "Only CSV files are allowed.";
        header("Location: ../reg_add_admin.php");
        exit();
    }

    // Check if the uploaded file is not empty
    if ($_FILES['file']['size'] > 0) {
        // Open the CSV file for reading
        $fileHandle = fopen($file, 'r');
        
        // Skip the header row
        fgetcsv($fileHandle);
        $lineNumber++;

        // List of valid departments
        $validDepartments = ['Accountancy', 'Business Administration', 'Hospitality Management', 'Education and Arts', 'Criminal Justice'];

        // Process each row in the CSV file
        while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
            // Ensure the row has enough columns
            if (count($row) >= 5) {
                $firstName = trim($row[0]);
                $lastName = trim($row[1]);
                $department = trim($row[2]);
                $email = trim($row[3]);
                $rawPassword = trim($row[4]);
                
                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = "Line $lineNumber: $email (Invalid format)";
                }
                // Validate department
                elseif (!in_array($department, $validDepartments)) {
                    $invalidDepartments[] = "Line $lineNumber: $department (Invalid department for $email)";
                }
                else {
                    // Check for duplicate email
                    $stmt = $db->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();

                    // If a duplicate is found, store the email with line number
                    if ($count > 0) {
                        $duplicateRecords[] = "Line $lineNumber: $email";
                    } else {
                        // Hash the password
                        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
                        
                        // Insert the new admin record into the database
                        $stmt = $db->prepare("INSERT INTO dept_admin (FirstName, LastName, Department, Email, Password) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $firstName, $lastName, $department, $email, $password);
                        
                        if ($stmt->execute()) {
                            $imported++;
                            $successRecords[] = "$firstName $lastName ($email)";
                        }
                        $stmt->close(); // Close the statement
                    }
                }
            }
            $lineNumber++;
        }

        // Close the file handle
        fclose($fileHandle);

        // Set session messages based on the import results
        $messages = [];
        
        if ($imported > 0) {
            $_SESSION['success_message'] = "Successfully imported $imported administrator(s)!";
        }
        
        if (!empty($duplicateRecords) || !empty($invalidEmails) || !empty($invalidDepartments)) {
            $errorMessage = "Some records were not imported:";
            
            if (!empty($duplicateRecords)) {
                $errorMessage .= "<br><strong>Duplicate Emails:</strong><br>" . implode("<br>", $duplicateRecords);
            }
            
            if (!empty($invalidEmails)) {
                $errorMessage .= "<br><strong>Invalid Email Formats:</strong><br>" . implode("<br>", $invalidEmails);
            }
            
            if (!empty($invalidDepartments)) {
                $errorMessage .= "<br><strong>Invalid Departments:</strong><br>" . implode("<br>", $invalidDepartments);
            }
            
            $_SESSION['error_message'] = $errorMessage;
        } else if ($imported == 0) {
            $_SESSION['error_message'] = "No records were imported. Please check your CSV file format.";
        }

        // Redirect back to the import page
        header("Location: ../reg_add_admin.php");
        exit();
    } else {
        // Handle the case where the uploaded file is empty
        $_SESSION['error_message'] = "Uploaded file is empty.";
        header("Location: ../reg_add_admin.php");
        exit();
    }
}

// No need to close the database connection, it's handled by the db() function.

