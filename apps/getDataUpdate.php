<?php
// Assume this PHP script fetches the data you need and returns it as JSON
require 'connect.php';

$uid = $_GET['uid'];
// Encryption Key and Initialization Vector (IV)
$encryptionKey = "2e54d1151eaf55af8d889ec1425fdc85"; // Replace with your own key
$iv = "5fbc84cbabd58453"; // Replace with your own IV

$s = "SELECT * FROM user_temp WHERE id = '$uid'";
$r = $conn -> query($s);
$row = $r -> fetch_assoc();

$sql = "SELECT * FROM user_temp WHERE email = '".$row['email']."' OR id = '".$uid."'";
$result = $conn -> query($sql);
$qr_row = $result -> fetch_assoc();

$sql1 = "SELECT * FROM brasecunit WHERE BSU_name like '%".$row['section']."%' ";
$result1 = $conn -> query($sql1);
$row1 = $result1 -> fetch_assoc();

$sql2 = "SELECT * FROM division WHERE DIV_NAME like '%".$row['division']."%' ";
$result2 = $conn -> query($sql2);
$row2 = $result2 -> fetch_assoc();

$today_date = date('Y-m-d');


    $data = array(
        "uid" => $row['id'],
        "name" => $row['fullname'],
        "email" => $row['email'],
        "access_level" => $row['access_level'],
        "BSU_name" => $row1['BSU_name'],
        "BSU" => $row1['BSU'],
        "bsu_id" => $row1['BSU_id'],
        "div" => $row2['DIV_ID'],
        "phone" => $row['phone'],
        "division" => $row2['DIV_NAME'],
        

    );
    
    // Convert the data to JSON format
    $jsonData = json_encode($data);

    // Encryption
    $encryptedData = openssl_encrypt($jsonData, 'aes-256-cbc', $encryptionKey, 0, $iv);

    // Set the Content-Type header to indicate that the response contains JSON data
    header('Content-Type: application/json');

    // Output the JSON data
    //print_r($jsonData);
    print_r($encryptedData);


?>