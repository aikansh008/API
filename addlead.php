<?php
session_start();
ob_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Your database password
$dbname = "leads"; // Your database name

// Initialize response array
$response = array("status" => "", "message" => "");

try {
    // Check if request method is POST
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    // Check if both username and password are provided
    if (!isset($_POST['name']) || !isset($_POST['mobile_no']) || empty($_POST['name']) || empty($_POST['mobile_no'])) {
        throw new Exception("Name and mobile number are required");
    }

    // Create connection to database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Sanitize user input to prevent SQL Injection
    $name = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["name"]));
    $email = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["email"] ?? ''));
    $mobile_no = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["mobile_no"]));
    $enquiry_for = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["enquiry_for"] ?? ''));
    $start_date = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["start_date"]));
    $duration = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["duration"]));
    $enquiry_city = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["enquiry_city"]));
    $lead_status = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["lead_status"]));
    $comment = htmlspecialchars(mysqli_real_escape_string($conn, $_POST["comment"] ?? ''));
    $created_by = htmlspecialchars(mysqli_real_escape_string($conn, $_SESSION["emp_id"] ?? ''));

    $enquiry_date = date("Y-m-d H:i");
    $last_connected = date("Y-m-d H:i");
    $date = date("d-m-Y h:i A");
    $comment = "|" . $created_by . " ($date) :- Created " . $comment;

    // Generate enquiry_id
    $sql_check = "SELECT MAX(enquiry_id) AS id FROM crm_lead";
    $result_check = $conn->query($sql_check);
    $enquiry_id = 1;
    if ($result_check && $result_check->num_rows > 0) {
        $row_check = $result_check->fetch_assoc();
        $enquiry_id = intval($row_check["id"]) + 1;
    }

    // Insert lead into database
    $sql = "INSERT INTO crm_lead (
                enquiry_id, name, email, mobile_no, enquiry_for, 
                enquiry_date, start_date, duration, last_connected, 
                enquiry_city, lead_status, comment, employee_code
            ) VALUES (
                '$enquiry_id', '$name', '$email', '$mobile_no', '$enquiry_for', 
                '$enquiry_date', '$start_date', '$duration', '$last_connected', 
                '$enquiry_city', '$lead_status', '$comment', '$created_by'
            )";

    if ($conn->query($sql) === TRUE) {
        $response["status"] = "success";
        $response["message"] = "Lead created successfully!";
    } else {
        throw new Exception("Error: " . $sql . "<br>" . $conn->error);
    }

    // Close database connection
    $conn->close();
} catch (Exception $e) {
    $response["status"] = "error";
    $response["message"] = $e->getMessage();
}

// Output JSON response
echo json_encode($response);
?>
