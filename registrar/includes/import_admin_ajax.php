<?php
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Registrar']);

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    $conn = db();

    // Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
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
            throw new Exception("Only CSV files are allowed.");
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
                        $invalidEmails[] = "$email (Invalid format)";
                    }
                    // Validate department
                    elseif (!in_array($department, $validDepartments)) {
                        $invalidDepartments[] = "$department (Invalid department for $email)";
                    }
                    else {
                        // Check for duplicate email
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $stmt->bind_result($count);
                        $stmt->fetch();
                        $stmt->close();

                        // If a duplicate is found, store the email with line number
                        if ($count > 0) {
                            $duplicateRecords[] = "$email";
                        } else {
                            // Hash the password
                            $password = password_hash($rawPassword, PASSWORD_DEFAULT);
                            
                            // Insert the new admin record into the database
                            $stmt = $conn->prepare("INSERT INTO dept_admin (FirstName, LastName, Department, Email, Password) VALUES (?, ?, ?, ?, ?)");
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

            // Build the response message
            $successMessage = '';
            if ($imported > 0) {
                $successMessage = "Successfully imported $imported administrators";
            }
            
            $errorMessage = '';
            if (!empty($duplicateRecords) || !empty($invalidEmails) || !empty($invalidDepartments)) {
                $errorMessage = "Some records were not imported:";
                
                if (!empty($duplicateRecords)) {
                    $errorMessage .= "<br><strong>Duplicate Emails:</strong><br>" . implode("<br>", $duplicateRecords);
                }
                if (!empty($invalidEmails)) {
                    $errorMessage .= "<br><strong>Invalid Emails:</strong><br>" . implode("<br>", $invalidEmails);
                }
                if (!empty($invalidDepartments)) {
                    $errorMessage .= "<br><strong>Invalid Departments:</strong><br>" . implode("<br>", $invalidDepartments);
                }
            }

            if (!empty($errorMessage)) {
                if ($imported > 0) {
                    $response['success'] = true;
                    $response['message'] = $successMessage . '<br><br>' . $errorMessage;
                } else {
                    $response['success'] = false;
                    $response['message'] = $errorMessage;
                }
            } elseif ($imported > 0) {
                $response['success'] = true;
                $response['message'] = $successMessage;
            } else {
                throw new Exception('No new records were imported.');
            }

            // Fetch the updated admin list for the DataTable
            $sql = "SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin";
            $result = $conn->query($sql);
            $admins = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $admins[] = [
                        'FirstName' => htmlspecialchars($row['FirstName']),
                        'LastName' => htmlspecialchars($row['LastName']),
                        'Department' => htmlspecialchars($row['Department']),
                        'Email' => htmlspecialchars($row['Email']),
                        'Actions' => generateActionsHtml($row)
                    ];
                }
            }
            
            $response['data'] = $admins;
        } else {
            throw new Exception("The uploaded file is empty.");
        }
    } else {
        throw new Exception("No file uploaded or invalid request.");
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
    
    echo json_encode($response);
}

// Helper function to generate action buttons HTML
function generateActionsHtml($row) {
    $adminId = htmlspecialchars($row['AdminID']);
    $firstName = htmlspecialchars($row['FirstName']);
    $lastName = htmlspecialchars($row['LastName']);
    $department = htmlspecialchars($row['Department']);
    $email = htmlspecialchars($row['Email']);
    
    return '<button class="button is-info styled-button" onclick="openEditModal(\'' . $adminId . '\', \'' . $firstName . '\', \'' . $lastName . '\', \'' . $department . '\', \'' . $email . '\')">
              <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
              </span>
            </button>
            <button class="button is-danger styled-button is-reset" onclick="deleteAdmin(' . $adminId . ')">
              <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                  <path d="M4 7l16 0"></path>
                  <path d="M10 11l0 6"></path>
                  <path d="M14 11l0 6"></path>
                  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                </svg>
              </span>
            </button>';
}
