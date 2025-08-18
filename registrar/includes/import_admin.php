<?php
session_start();
require_once __DIR__ . '/../../auth/middleware.php';

// Create a new database connection
$db = db();

// Check if the form was submitted
if (isset($_POST['importSubmit'])) {
    $file = $_FILES['file']['tmp_name'];
    $duplicateRecords = [];
    $imported = 0;

    // Check if the uploaded file is not empty
    if ($_FILES['file']['size'] > 0) {
        // Open the CSV file for reading
        $fileHandle = fopen($file, 'r');

        // Skip the header row
        fgetcsv($fileHandle);

        // Process each row in the CSV file
        while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
            // Ensure the row has enough columns
            if (count($row) >= 5) {
                $firstName = trim($row[0]);
                $lastName = trim($row[1]);
                $department = trim($row[2]);
                $email = trim($row[3]);
                $password = password_hash(trim($row[4]), PASSWORD_DEFAULT); // Hash the password

                // Check for duplicate email
                $stmt = $db->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                // If a duplicate is found, store the email
                if ($count > 0) {
                    $duplicateRecords[] = $email; // Store duplicate email
                } else {
                    // Insert the new admin record into the database
                    $stmt = $db->prepare("INSERT INTO dept_admin (FirstName, LastName, Department, Email, Password) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $firstName, $lastName, $department, $email, $password);
                    $stmt->execute();
                    $imported++;
                    $stmt->close(); // Close the statement
                }
            }
        }

        // Close the file handle
        fclose($fileHandle);

        // Set session messages based on the import results
        if (!empty($duplicateRecords)) {
            $_SESSION['error_message'] = "The following duplicate records were found and not imported: " . implode(", ", $duplicateRecords);
        } else if ($imported > 0) {
            $_SESSION['success_message'] = "Successfully imported $imported administrators!";
        } else {
            $_SESSION['error_message'] = "No records were imported. Please check your CSV file format.";
        }

        // Redirect back to the import page
        header("Location: ../test.php");
        exit();
    } else {
        // Handle the case where the uploaded file is empty
        $_SESSION['error_message'] = "Uploaded file is empty.";
        header("Location: ../test.php");
        exit();
    }
}

// No need to close the database connection, it's handled by the db() function.

