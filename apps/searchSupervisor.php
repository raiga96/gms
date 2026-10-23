<?php
// Assume this PHP script fetches the data you need and returns it as JSON
require 'connect.php';

$fullname = $_GET['newValue'];
$division = $_GET['div'];
$uid = $_GET['uid'];

$s = "SELECT * FROM users as A JOIN userinfo as B ON A.uid = B.uid WHERE A.access_level >='2' AND B.division ='$division' AND B.uid != '$uid'";
$r = $conn -> query($s);
$row = $r -> fetch_assoc();


    $data = array(
        "SUID" => $row['uid']
 
    );
    
    // Convert the data to JSON format
    $jsonData = json_encode($data);

    // Set the Content-Type header to indicate that the response contains JSON data
    header('Content-Type: application/json');

    // Output the JSON data
    print_r($jsonData);


?>