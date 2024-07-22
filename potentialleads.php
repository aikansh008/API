<?php
session_start();
ob_start();
date_default_timezone_set("Asia/Kolkata");

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust the origin as needed

// Include database connection
include "C:/xampp/common/connection2.php";

// Check connection
if (!$conn) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . mysqli_connect_error()
    ]);
    exit();
}

// Function to sanitize inputs
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, htmlspecialchars($input));
}

// Function to format date as required
function formatDateTime($datetime) {
    return date("d-m-Y h:i A", strtotime($datetime));
}

// Function to fetch leads data based on criteria
function fetchLeads($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    $data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Process each row of data
            $name = explode(" ", $row["name"])[0];
            $email = $row["email"];
            $mobile_no = $row["mobile_no"];
            $enquiry_for = $row["enquiry_for"];
            $enquiry_id = $row["enquiry_id"];
            $enquiry_date = formatDateTime($row["enquiry_date"]);
            $last_connected = formatDateTime($row["last_connected"]);
            $start_date = date("d/m/Y", strtotime($row["start_date"]));
            $duration = $row["duration"];
            $enquiry_city = $row["enquiry_city"];
            $lead_status = $row["lead_status"];
            $comment = "";
            $array = explode("|", $row["comment"]);
            if (count($array) > 0) {
                $comment = end($array);
            }
            $created_by = $row["employee_code"];

            // Prepare data array
            $data[] = [
                'name' => $name,
                'email' => $email,
                'mobile_no' => $mobile_no,
                'enquiry_for' => $enquiry_for,
                'enquiry_id' => $enquiry_id,
                'enquiry_date' => $enquiry_date,
                'last_connected' => $last_connected,
                'start_date' => $start_date,
                'duration' => $duration,
                'enquiry_city' => $enquiry_city,
                'lead_status' => $lead_status,
                'comment' => $comment,
                'created_by' => $created_by
            ];
        }
    } else {
        // Query execution failed
        echo json_encode([
            "status" => "error",
            "message" => "Query execution failed: " . mysqli_error($conn)
        ]);
        exit();
    }
    
    return $data;
}

// Fetching starting_data for the next 3 days
$date_1 = date("Y-m-d");
$date_2 = date("Y-m-d", strtotime("+3 days"));
$sql_starting = "SELECT * FROM crm_lead WHERE lead_status NOT IN ('Closed', 'Not Converted') AND start_date BETWEEN '$date_1' AND '$date_2' ORDER BY start_date DESC";
$starting_data = fetchLeads($conn, $sql_starting);

// Fetching not_contacted_data for the last 3 days
$date_1 = date("Y-m-d", strtotime("-3 days"));
$date_2 = date("Y-m-d");
$sql_not_contacted = "SELECT * FROM crm_lead WHERE lead_status NOT IN ('Closed', 'Not Converted') AND last_connected BETWEEN '$date_1' AND '$date_2' ORDER BY start_date DESC";
$not_contacted_data = fetchLeads($conn, $sql_not_contacted);

// Fetching monthly_data for the last 30 days
$date_1 = date("Y-m-d", strtotime("-30 days"));
$sql_monthly = "SELECT * FROM crm_lead WHERE lead_status <> 'Closed' AND duration = 'Monthly' AND DATE(enquiry_date) >= '$date_1'";
$monthly_data = fetchLeads($conn, $sql_monthly);

// Fetching daily leads for today
$today = date("Y-m-d");
$sql_daily = "SELECT * FROM crm_lead WHERE DATE(enquiry_date) = '$today'";
$daily_data = fetchLeads($conn, $sql_daily);

// Prepare response
$response = [
    'status' => 'success',
    'starting_data' => $starting_data,
    'not_contacted_data' => $not_contacted_data,
    'monthly_data' => $monthly_data,
    'daily_data' => $daily_data // Add the daily leads data here
];

// Output JSON response
echo json_encode($response);

// Close connection
mysqli_close($conn);
?>
