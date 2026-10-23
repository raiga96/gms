<?php

require 'conn.php';

$sql = "SELECT * FROM staff_checkin";

$result = $conn->query($sql);

// Fetch data and store it in an array
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    //echo json_encode(["error" => "Search parameter is empty"]);
    $data = array();
    $data[] = ["error" => "No data found"];
}

// Close the database connection
$conn->close();

// Encode the data in JSON format and send it as the response
header('Content-Type: application/json');
echo json_encode($data);
