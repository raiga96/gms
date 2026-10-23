<?php
// Assume this PHP script fetches the data you need and returns it as JSON
require 'connect.php';
require 'conn.php';

$uid = $_GET['uid'];


        $sec = $_GET['sec'];
        $thi = $_GET['thi'];
        $s = "SELECT * FROM users AS a 
        JOIN userinfo AS b ON b.uid = a.uid 
        WHERE a.uid = '".$uid."' OR a.user_id = '$uid' OR a.user_email = '$uid'";
        $r = $conn1 -> query($s);
        $row = $r -> fetch_assoc();

        $e = "SELECT * FROM events AS a JOIN venue AS b ON b.VENUE_ID = a.EVENT_VENUE WHERE a.EV_ID = '$sec'";
        $e1 = $conn2 -> query($e);
        $row1 = $e1 -> fetch_assoc();


        $data = array(
            "uid" => $row['uid'],
            "email" => $row['user_email'],
            "name" => $row['fullname'],
            "event_name" => $row1['EVENT_NAME'],
            "venue_name" => $row1['VENUE_NAME'],
            "session" => $row1['EVENT_SESSION'],
            "event_type" => $row1['EVENT_TYPE']
        );

    $jsonData = json_encode($data);

    $conn -> close();
    
    // Convert the data to JSON format
    

    // Set the Content-Type header to indicate that the response contains JSON data
    header('Content-Type: application/json');

    // Output the JSON data
    print_r($jsonData);


?>