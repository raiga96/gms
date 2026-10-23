<?php

require 'conn.php';

$uuid = $_GET['uid'];

$sql1 = "SELECT * FROM users WHERE user_email = '" . $uuid . "' OR user_id = '" . $uuid . "' OR uid = '" . $uuid . "'";
$result1 = $conn->query($sql1);
$row1 = $result1->fetch_assoc();
$uid = $row1['uid'];
$type = $row1['user_type'];

if ($type == 'CON') {
    $sql = "SELECT * FROM contract AS a JOIN contractor AS b ON b.cid = a.cid JOIN subzone AS c ON c.sz_id = a.sz_id JOIN zone AS d ON d.zone_id = c.zone_id WHERE b.uid = '$uid'";
} else {
    $sql = "SELECT * FROM contract AS a JOIN contractor AS b ON b.cid = a.cid JOIN subzone AS c ON c.sz_id = a.sz_id JOIN zone AS d ON d.zone_id = c.zone_id JOIN users AS e ON (e.uid = a.spv_id OR e.uid = a.pgs_id) JOIN userinfo AS f ON f.uid = e.uid WHERE e.uid = '$uid' ORDER BY contract_number";
}

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
