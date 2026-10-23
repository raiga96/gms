<?php
// Assume this PHP script fetches the data you need and returns it as JSON
require 'connect.php';
require 'conn.php';

$uid = $_GET['uid'];
// Encryption Key and Initialization Vector (IV)
$encryptionKey = "2e54d1151eaf55af8d889ec1425fdc85"; // Replace with your own key
$iv = "5fbc84cbabd58453"; // Replace with your own IV

$s = "SELECT * FROM users AS a JOIN userinfo AS b ON b.uid = a.uid JOIN brasecunit AS c ON c.BSU_id = b.section JOIN division AS d ON d.DIV_ID = b.division WHERE a.uid = '".$uid."'";
$r = $conn1 -> query($s);
$row = $r -> fetch_assoc();

$sql = "SELECT * FROM users WHERE user_email = '".$row['user_email']."' OR uid = '".$uid."' -- API GETDATA LAMA";
$result = $conn1 -> query($sql);
$qr_row = $result -> fetch_assoc();

$today_date = date('Y-m-d');

$sql1 = "SELECT * FROM qrscan WHERE uid = '".$qr_row['uid']."' AND date = '$today_date' -- API APPS LAMA";
$result1 = $conn1 -> query($sql1);
$qr_row1 = $result1 -> fetch_assoc();
if(empty($qr_row1['record_id'])){
    $timeIn = $today_date.' 00:00:00';
    $timeOut = $today_date.' 00:00:00';
}else{
$timeIn = $qr_row1['date'].' '.$qr_row1['timeIN'];
$timeOut = $qr_row1['date'].' '.$qr_row1['timeOUT'];
}

$r = "SELECT * FROM profile_image WHERE UID = '".$uid."' ";
$f = $conn1 -> query($r);
$row1 = $f -> fetch_assoc();

$r1 = "SELECT * FROM holiday WHERE holiday_date = '$today_date' ";
$f1 = $conn1 -> query($r1);
$row2 = $f1 -> fetch_assoc();

$r2 = "SELECT * FROM user_log WHERE uid = '".$row['uid']."' ORDER BY log_id desc";
$f2 = $conn1 -> query($r2);
$row3 = $f2 -> fetch_assoc();

$r3 = "SELECT * FROM app_info WHERE app_status = 'Y'";
$f3 = $conn1 -> query($r3);
$row4 = $f3 -> fetch_assoc();

$r4 = "SELECT * FROM access_level WHERE acc_lvl = '".$row['access_level']."'";
$f4 = $conn1 -> query($r4);
$row5 = $f4 -> fetch_assoc();



    $data = array(
        "uid" => $row['uid'],
        "name" => $row['fullname'],
        "email" => $row['user_email'],
        "access_level" => $row5['lvl_name'],
        "BSU_name" => $row['BSU_name'],
        "BSU" => $row['BSU'],
        "bsu_id" => $row['BSU_id'],
        "div" => $row['DIV_ID'],
        "phone" => $row['phone'],
        "division" => $row['DIV_NAME'],
        "img_path" => $row1['FILE_PATH'],
        "timeIn" => date('h:i a', strtotime($timeIn)),
        "timeIn1" => date('Hi', strtotime($timeIn)),
        "timeOut" => date('h:i a', strtotime($timeOut)),
        "timeOut1" => date('Hi', strtotime($timeOut)),
        "record_id" => $qr_row1['record_id'],
        "day" => $row2['desc'],
        "logdate" => date("d M Y, h:i a", strtotime($row3['CREATED_ON'])),
        "app_version" => $row4['app_version'],
        "ios_link" => $row4['app_link_apple'],
        "android_link" => $row4['app_link_android']

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

    $conn -> close();

?>