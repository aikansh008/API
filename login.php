<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Your database password
$dbname = "login"; // Your database name

// Initialize response array
$response = array("status" => "", "message" => "");

try {
    // Check if request method is POST
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    // Check if both username and password are provided
    if (!isset($_POST['username']) || !isset($_POST['password']) || empty($_POST['username']) || empty($_POST['password'])) {
        throw new Exception("Username and password are required");
    }

    // Create connection to database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Sanitize user input to prevent SQL Injection
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Query to check if user exists
    $query = "SELECT * FROM employee_login WHERE ename='$username'";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        // User found, verify password
        $user = $result->fetch_assoc();
        if ($password === $user['epassword']) {
            // Password is correct
            $response["status"] = "success";
            $response["message"] = "Login successful";
            $response["data"] = array(
                'emp_id' => $user['ecode'],
                'ename' => $user['ename'],
                'erole' => $user['erole'],
                'access' => explode(',', $user['access'])
            );
        } else {
            // Incorrect password
            $response["status"] = "error";
            $response["message"] = "Incorrect password";
        }
    } else {
        // User not found
        $response["status"] = "error";
        $response["message"] = "User not found";
    }

    // Free result set
    $result->free();

    // Close database connection
    $conn->close();

} catch (Exception $e) {
    $response["status"] = "error";
    $response["message"] = $e->getMessage();
}

// Output JSON response
echo json_encode($response);
?>
