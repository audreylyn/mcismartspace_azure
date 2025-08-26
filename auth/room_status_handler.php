<?php

/**
 * Room Status Handler
 * 
 * This file contains functions to automatically update room statuses based on
 * reservation times. It marks rooms as 'occupied' when approved reservations start
 * and back to 'available' when they end.
 */

// Connect to database
function connectToDatabase()
{
    try {
        // Define the SSL certificate path
        $ssl_cert = __DIR__ . '/../DigiCertGlobalRootCA.crt.pem';

        // Initialize connection
        $conn = mysqli_init();

        // Set SSL certificate
        $conn->ssl_set(NULL, NULL, $ssl_cert, NULL, NULL);

        // Establish connection with SSL
        $conn->real_connect(
            "mcismartdb.mysql.database.azure.com", 
            "adminuser", 
            "SmartDb2025!", 
            "mcismartdb",
            3306,
            NULL,
            MYSQLI_CLIENT_SSL
        );

        // Check for connection errors
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }

        // Set charset
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection exception: " . $e->getMessage());
        return null;
    }
}

/**
 * Update room statuses based on current time
 * 
 * This function:
 * 1. Sets rooms to 'occupied' if there's an active, approved reservation now
 * 2. Sets rooms back to 'available' when all approved reservations are finished
 * 3. Preserves rooms with 'maintenance' status
 * 
 * @return bool Success or failure
 */
function updateRoomStatuses()
{
    // Make sure we're using Philippines timezone
    date_default_timezone_set('Asia/Manila');

    // Get current date and time
    $currentDateTime = date('Y-m-d H:i:s');

    // Only output debug information when script is run directly
    $directRun = (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']));

    if ($directRun) echo "Current server time: $currentDateTime\n";

    // Connect to database
    $conn = connectToDatabase();
    if (!$conn) return false;

    // Always perform the update to ensure room statuses are current
    // Removing the 5-minute check to ensure immediate updates

    try {
        // Start transaction to ensure all updates are atomic
        $conn->begin_transaction();

        // Debug logging
        error_log("Running room status update at: " . $currentDateTime);

        // Check what bookings are active right now
        $activeBookingsQuery = "
            SELECT r.id, r.room_name, rr.StartTime, rr.EndTime
            FROM room_requests rr
            JOIN rooms r ON rr.RoomID = r.id
            WHERE rr.Status = 'approved'
            AND '$currentDateTime' >= rr.StartTime 
            AND '$currentDateTime' <= rr.EndTime";

        $bookingsResult = $conn->query($activeBookingsQuery);

        if ($directRun) {
            echo "Current active bookings:\n";
            while ($row = $bookingsResult->fetch_assoc()) {
                echo "Room {$row['room_name']} (ID: {$row['id']}) is booked from {$row['StartTime']} to {$row['EndTime']}\n";
            }
            // Reset result pointer
            $bookingsResult->data_seek(0);
        }

        // 1. Find rooms that should be marked as occupied
        // (approved reservations where current time is between start and end time)
        $occupiedRoomsSql = "
            UPDATE rooms r
            SET r.RoomStatus = 'occupied'
            WHERE r.id IN (
                SELECT DISTINCT rr.RoomID
                FROM room_requests rr
                WHERE rr.Status = 'approved'
                AND '$currentDateTime' >= rr.StartTime 
                AND '$currentDateTime' <= rr.EndTime
            )
            AND r.RoomStatus != 'maintenance'";

        $conn->query($occupiedRoomsSql);

        // Log how many rows were affected
        $occupiedCount = $conn->affected_rows;
        if ($directRun) echo "Rooms marked as occupied: " . $occupiedCount . "\n";

        // 2. Find rooms that should be marked as available again
        // (no current approved reservations)
        $availableRoomsSql = "
            UPDATE rooms r
            SET r.RoomStatus = 'available'
            WHERE r.RoomStatus = 'occupied'
            AND r.id NOT IN (
                SELECT DISTINCT rr.RoomID
                FROM room_requests rr
                WHERE rr.Status = 'approved'
                AND '$currentDateTime' >= rr.StartTime 
                AND '$currentDateTime' <= rr.EndTime
            )";

        $conn->query($availableRoomsSql);

        // Log how many rows were affected
        $availableCount = $conn->affected_rows;
        if ($directRun) echo "Rooms marked as available: " . $availableCount . "\n";

        // Get all room statuses after update
        if ($directRun) {
            $roomStatusQuery = "SELECT id, room_name, RoomStatus FROM rooms";
            $roomStatusResult = $conn->query($roomStatusQuery);

            echo "Updated room statuses:\n";
            while ($row = $roomStatusResult->fetch_assoc()) {
                echo "Room {$row['room_name']} (ID: {$row['id']}) status: {$row['RoomStatus']}\n";
            }
        }

        // Update the last check time
        updateLastCheckTime($currentDateTime);

        // Commit the transaction
        $conn->commit();

        return true;
    } catch (Exception $e) {
        // If an error occurs, roll back the changes
        $conn->rollback();
        error_log("Error updating room statuses: " . $e->getMessage());
        if ($directRun) echo "Error updating room statuses: " . $e->getMessage() . "\n";
        return false;
    } finally {
        // Do not close the connection here, as it's managed globally
    }
}

/**
 * Get the last time the status update was run
 */
function getLastUpdateTime()
{
    $conn = connectToDatabase();
    if (!$conn) return null;

    // Check if the settings table exists and has our last_check value
    $checkTableSql = "SHOW TABLES LIKE 'system_settings'";
    $tableExists = $conn->query($checkTableSql)->num_rows > 0;

    if (!$tableExists) {
        // Create the settings table if it doesn't exist
        $createTableSql = "CREATE TABLE system_settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($createTableSql);
        $conn->close();
        return null;
    }

    // Get the last check time
    $lastCheckSql = "SELECT setting_value FROM system_settings WHERE setting_key = 'room_status_last_check'";
    $result = $conn->query($lastCheckSql);

    if ($result && $result->num_rows > 0) {
        $value = $result->fetch_assoc()['setting_value'];
        $conn->close();
        return $value;
    }

    $conn->close();
    return null;
}

/**
 * Update the timestamp for the last status check
 */
function updateLastCheckTime($timestamp)
{
    $conn = connectToDatabase();
    if (!$conn) return false;

    $sql = "INSERT INTO system_settings (setting_key, setting_value) 
            VALUES ('room_status_last_check', ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $timestamp, $timestamp);
    $result = $stmt->execute();

    $conn->close();
    return $result;
}

// Automatically run the update when this file is included
updateRoomStatuses();

// Main function that runs when the file is executed directly
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    // If the file is called directly (not included), run the update
    echo "Running room status update manually...\n";
    $result = updateRoomStatuses();
    echo "Update " . ($result ? "completed successfully" : "failed") . "\n";
}
