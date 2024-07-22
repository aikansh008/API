<?php
session_start();
ob_start();
header("Content-Type: application/json");

// Include database connection file
include "C:/xampp/common/connection2.php"; // Make sure this path is correct

// Check if the database connection was successful
if (!isset($conn)) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

// Ensure the user is logged in
if (!isset($_SESSION['emp_id'])) {
    echo json_encode(["error" => "Unauthorized access. Please log in."]);
    exit();
}

// Check if the search term is provided
$search = $_POST["search-item"] ?? '';

if (empty($search)) {
    echo json_encode(["error" => "Search term is required."]);
    exit();
}

// Sanitize and escape the search term
$search = htmlspecialchars(mysqli_real_escape_string($conn, $search));

// SQL query to search for leads by name, mobile number, or enquiry city
$sql = "SELECT * FROM crm_lead 
        WHERE name LIKE '%$search%' 
        OR mobile_no LIKE '%$search%' 
        OR enquiry_city LIKE '%$search%' 
        ORDER BY start_date DESC";

$result = mysqli_query($conn, $sql);

$leads = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $name = explode(" ", $row["name"])[0];
        $email = $row["email"];
        $mobile_no = $row["mobile_no"];
        $enquiry_for = $row["enquiry_for"];
        $enquiry_id = $row["enquiry_id"];
        $enquiry_date = date("d-m-Y h:i A", strtotime($row["enquiry_date"]));
        $last_connected = date("d-m-Y h:i A", strtotime($row["last_connected"]));
        $start_date = date("d/m/Y", strtotime($row["start_date"]));
        $duration = $row["duration"];
        $class_duration = "";
        $text_class = "text-danger";
        $class_link = "text-success";

        if (strcasecmp($duration, "Monthly") == 0 || strcasecmp($duration, "1 Week") == 0 || strcasecmp($duration, "2 Week") == 0) {
            $class_duration = "bg-success text-white";
            $text_class = "text-white";
            $class_link = "text-white";
        }

        $enquiry_city = $row["enquiry_city"];
        $lead_status = $row["lead_status"];
        $comment = "";
        $array = explode("|", $row["comment"]);

        if (count($array) > 0) {
            $comment = end($array);
        }

        $created_by = $row["employee_code"];

        $leads[] = [
            "name" => $name,
            "email" => $email,
            "mobile_no" => $mobile_no,
            "enquiry_for" => $enquiry_for,
            "enquiry_id" => $enquiry_id,
            "enquiry_date" => $enquiry_date,
            "last_connected" => $last_connected,
            "start_date" => $start_date,
            "duration" => $duration,
            "class_duration" => $class_duration,
            "text_class" => $text_class,
            "class_link" => $class_link,
            "enquiry_city" => $enquiry_city,
            "lead_status" => $lead_status,
            "comment" => $comment,
            "created_by" => $created_by,
        ];
    }
}

// Output the results as JSON
echo json_encode(["leads" => $leads]);
?>
