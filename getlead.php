<?php
session_start();
ob_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');

// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Your database password
$dbname_lead = "leads"; // Your lead database name
$dbname_login = "login"; // Your login database name

// Initialize response array
$response = array();
$response["status"] = "error";
$response["message"] = "";

try {
    // Create connection to the lead database
    $conn_lead = new mysqli($servername, $username, $password, $dbname_lead);
    // Create connection to the login database
    $conn_login = new mysqli($servername, $username, $password, $dbname_login);

    // Check connection to the lead database
    if ($conn_lead->connect_error) {
        throw new Exception("Connection to lead database failed: " . $conn_lead->connect_error);
    }
    // Check connection to the login database
    if ($conn_login->connect_error) {
        throw new Exception("Connection to login database failed: " . $conn_login->connect_error);
    }

    // Fetch all leads
    $sql = "SELECT * FROM crm_lead";
    $result = mysqli_query($conn_lead, $sql);

    if (!$result) {
        throw new Exception("Error executing query: " . mysqli_error($conn_lead));
    }

    $leads = array(); // Array to hold all leads

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lead = array();
            $lead["enquiry_id"] = $row["enquiry_id"];
            $lead["name"] = $row["name"];
            $lead["email"] = $row["email"];
            $lead["mobile_no"] = $row["mobile_no"];
            $lead["enquiry_for"] = $row["enquiry_for"];
            $lead["enquiry_date"] = date("d-m-Y h:i A", strtotime($row["enquiry_date"]));
            $lead["last_connected"] = date("d-m-Y h:i A", strtotime($row["last_connected"]));
            $lead["start_date"] = $row["start_date"];
            $lead["duration"] = $row["duration"];
            $lead["enquiry_city"] = $row["enquiry_city"];
            $lead["lead_status"] = $row["lead_status"];
            $lead["comment"] = explode("|", $row["comment"]);
            $lead["created_by"] = $row["employee_code"];

            // Check if the employee_code exists in the employee_login table
            $sql_emp = "SELECT * FROM employee_login WHERE ecode = '{$row['employee_code']}'";
            $result_emp = mysqli_query($conn_login, $sql_emp);
            if (!$result_emp) {
                throw new Exception("Error executing employee query: " . mysqli_error($conn_login));
            }
            if (mysqli_num_rows($result_emp) > 0) {
                $row_emp = mysqli_fetch_assoc($result_emp);
                $lead["created_by"] .= " (" . $row_emp["ename"] . ")";
            }

            $leads[] = $lead; // Add lead to leads array
        }
        $response["status"] = "success";
        $response["leads"] = $leads; // Assign leads array to response
    } else {
        $response["message"] = "No leads found";
    }

    // Close database connections
    $conn_lead->close();
    $conn_login->close();
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Output JSON response
echo json_encode($response);
?>
