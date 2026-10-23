<?php

require 'connect.php';

$uuid = $_GET['uid'];

$sql1 = "SELECT * FROM users WHERE user_email = '".$uuid."' OR user_id = '".$uuid."' OR uid = '".$uuid."' -- CHECKIN APPS";
$result1 = $conn -> query($sql1);
$row1 = $result1 -> fetch_assoc();
$uid = $row1['uid'];


    //$sql = "SELECT * FROM schedule WHERE DAY = '$day' ORDER BY S_TIME ASC";
    $sql = "SELECT * FROM easy.check_in AS a JOIN hems.events AS b ON b.EV_ID = a.activity JOIN hems.venue AS c ON b.EVENT_VENUE = c.VENUE_ID WHERE a.uid = '$uid' ORDER BY a.scan_date DESC LIMIT 15";
    $result = $conn->query($sql);

    // Fetch data and store it in an array
    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result -> fetch_assoc()) {
            $data[] = $row;
        }
    }else{
        //echo json_encode(["error" => "Search parameter is empty"]);
        $data = array();
        $data[] = ["error" => "No data found"];
    }

    // Close the database connection
    $conn->close();

    // Encode the data in JSON format and send it as the response
    header('Content-Type: application/json');
    echo json_encode($data);
?>
