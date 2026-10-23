<?php
// Assume this PHP script fetches the data you need and returns it as JSON
require 'conn.php';

$sz_id = $_GET['sz_id'];
$page = $_GET['page'];
$dd_id = isset($_GET['dd_id']) ? $_GET['dd_id'] : '';
// Encryption Key and Initialization Vector (IV)
$encryptionKey = "2e54d1151eaf55af8d889ec1425fdc85"; // Replace with your own key
$iv = "5fbc84cbabd58453"; // Replace with your own IV

// $s = "SELECT * FROM contract_location WHERE sz_id = '$sz_id'";
if ($page == 'detail') {
    $s = "SELECT * FROM contract_location AS a LEFT JOIN area_daily_records AS b ON b.area_id = a.area AND b.date = CURDATE() WHERE a.sz_id = '$sz_id' ";
} else {
   $s = "SELECT * FROM contract_location AS a LEFT JOIN area_daily_records AS b ON b.area_id = a.area WHERE a.sz_id = '$sz_id' AND b.dr_id='$dd_id'";
}
$r = $conn->query($s);

// Fetch data and store it in an array
$data = array();
if ($r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    //echo json_encode(["error" => "Search parameter is empty"]);
    $data = array();
    $data[] = ["error" => "No data found"];
}

// Convert the data to JSON format
$jsonData = json_encode($data);

// Encryption
//$encryptedData = openssl_encrypt($jsonData, 'aes-256-cbc', $encryptionKey, 0, $iv);

// Set the Content-Type header to indicate that the response contains JSON data
header('Content-Type: application/json');

// Output the JSON data
//print_r($jsonData);
//print_r($encryptedData);
echo json_encode($data);
//print_r($jsonData);

$conn->close();
