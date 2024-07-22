<?php
// Set headers for JSON content type and CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *"); // Replace * with specific domains if needed
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT"); // Allow PUT method

// Initialize response array
$response = [
    "status" => "error",
    "message" => "Unknown error occurred"
];

try {
    // Include database connection (adjust path as necessary)
    include "C:/xampp/common/connection.php";

    // Set time zone (optional, adjust if needed)
    date_default_timezone_set("Asia/Kolkata");

    // Log request details for debugging
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    $raw_input = file_get_contents('php://input');
    error_log("Raw Input Data: " . $raw_input);

    // Check if the request method is PUT
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        throw new Exception("Invalid request method. Only PUT is allowed.");
    }

    // Decode JSON data
    $data = json_decode($raw_input, true);

    // Validate required fields
    $required_fields = ["enquiry_id", "duration", "start_date", "status"];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            error_log("Missing or empty required field: {$field}");
            throw new Exception("Missing or empty required field: {$field}");
        }
    }

    // Sanitize POST data to prevent SQL injection
    $enquiry_id = mysqli_real_escape_string($conn1, $data["enquiry_id"]);
    $duration = mysqli_real_escape_string($conn1, $data["duration"]);
    $start_date = mysqli_real_escape_string($conn1, $data["start_date"]);
    $status = mysqli_real_escape_string($conn1, $data["status"]);
    $comment = "";

    // Optional: Example logic for adding comment, enhanced for security
    if (!empty(trim($data["comment"]))) {
        $emp_id = sanitize_input($data["emp_id"]); // Sanitize employee ID (if needed)
        $date = date("d-m-Y h:i A");
        $comment = "|{$emp_id} ({$date}) :- " . mysqli_real_escape_string($conn1, $data["comment"]);
    }

    // Set last connected timestamp
    $last_connected = date("Y-m-d H:i:s");

    // Prepare SQL statement using prepared statements
    $sql_update = "UPDATE leads.crm_lead SET
        duration = ?,
        start_date = ?,
        lead_status = ?,
        comment = CONCAT(comment, ?),
        last_connected = ?
    WHERE enquiry_id = ?";

    $stmt_update = $conn1->prepare($sql_update);
    if (!$stmt_update) {
        throw new Exception("Prepare failed: " . $conn1->error);
    }

    // Bind parameters using type hints for improved readability and security
    $stmt_update->bind_param("ssssss", $duration, $start_date, $status, $comment, $last_connected, $enquiry_id);

    // Execute prepared statement
    if ($stmt_update->execute()) {
        $response = ["status" => "success", "message" => "Record updated successfully"];
    } else {
        throw new Exception("Error updating record: " . $stmt_update->error);
    }

    // Close statement and connection
    $stmt_update->close();
    $conn1->close();

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Send HTTP response code based on success or failure
http_response_code($response["status"] === "success" ? 200 : 400);

// Output JSON response
echo json_encode($response);

// Optional function for sanitizing input (improve security further)
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
