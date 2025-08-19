# MCiSmartSpace - Room & Equipment Management System

This is a web-based application for managing room reservations and equipment for an educational institution.

## Deployment Instructions

When deploying this application to a live server, you will need to update the database credentials in the following files. The current credentials are set for a local development environment (`root` with no password).

### 1. Main Database Connection

The primary database connection for the entire application is configured in this file.

- **File:** `auth/db.inc.php`
- **Action:** Update the `$host`, `$user`, `$pass`, and `$name` variables with your production database server details.

```php
// Example from auth/db.inc.php
function db(): mysqli
{
    // ...
    $host = 'localhost'; // <-- Replace with your production host
    $user = 'root';      // <-- Replace with your production username
    $pass = '';          // <-- Replace with your production password
    $name = 'my_db';     // <-- Replace with your production database name
    // ...
}
```

### 2. Room Status Handler Connection

This script runs as a background task to update room statuses and uses its own isolated database connection. You must update the credentials here as well.

- **File:** `auth/room_status_handler.php`
- **Action:** Update the `mysqli` connection details inside the `connectToDatabase()` function.

```php
// Example from auth/room_status_handler.php
function connectToDatabase()
{
    // ...
    $conn = new mysqli("localhost", "root", "", "my_db"); // <-- Replace these credentials
    // ...
}
```

By updating these two locations, your application should be able to connect to the production database successfully.
