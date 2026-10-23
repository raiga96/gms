<?php

require 'connect.php';
    $curYear = date('Y');
    $uuid = $_GET['uid'];

    $sql1 = "SELECT * FROM users WHERE user_email = '".$uuid."' OR user_id = '".$uuid."' OR uid = '".$uuid."'";
	$result1 = $conn -> query($sql1);
	$row1 = $result1 -> fetch_assoc();
	$uid = $row1['uid'];

    $sql = "SELECT *, YEAR(A.EVENT_DATE) as year, DATE_FORMAT(A.EVENT_DATE, '%d %b %Y') as formatted_date, A.EVENT_NAME as description FROM `events` as A JOIN `participants` as B on B.event_id = A.EV_ID WHERE B.p_uid = '$uid'";
    $result = $conn2->query($sql);

    // Fetch data and store it in an array
    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Close the database connection
    $conn->close();

    // Encode the data in JSON format and send it as the response
    header('Content-Type: application/json');
    echo json_encode($data);

?>