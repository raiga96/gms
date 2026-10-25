<?php require_once __DIR__ . '/_q.php';
// Assume this PHP script fetches the data you need and returns it as JSON
require 'conn.php';

$num_si = $_GET['num_si'];
$page = $_GET['page'];
// Encryption Key and Initialization Vector (IV)
$encryptionKey = "2e54d1151eaf55af8d889ec1425fdc85"; // Replace with your own key
$iv = "5fbc84cbabd58453"; // Replace with your own IV

// $s = "SELECT * FROM contract_location WHERE sz_id = '$sz_id'";
if($page == 'DailyRecord'){
    $s = "SELECT b.* FROM daily_records AS a JOIN site_instruction AS b ON b.ddId = a.id AND a.date = CURDATE() WHERE b.num_si = ?";
} else if ($page == 'DetailHistory'){
    $s = "SELECT b.* FROM daily_records AS a JOIN site_instruction AS b ON b.ddId = a.id WHERE b.num_si = ?";
} else if ($page == 'Overall'){
    $s = "SELECT b.* FROM daily_records AS a JOIN site_instruction AS b ON b.ddId = a.id WHERE b.num_si = ?";
}

$r = gms_q($conn, $s, "s", [$num_si]);

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
