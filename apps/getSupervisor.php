<?php

require 'connect.php';

    $division = $_GET['div'];
    $uid = $_GET['uid'];

    //$sql = "SELECT * FROM flight_schec ORDER BY FID ASC";
    $sql = "SELECT * FROM users A, userinfo B WHERE A.uid = B.uid AND A.access_level >='2' AND B.division ='$division' AND B.uid != '$uid'";
    $result = $conn->query($sql);

    // Fetch data and store it in an array
    $data = array();
    if ($result->num_rows > 0) {
        while (
            $row = $result->fetch_assoc()) {
            // $data[] = $row;
            $data[] = array(
                'fullname' => $row['fullname']."*@*".$row['uid'],
            );
        }
    }

    // Close the database connection
    $conn->close();

    // Encode the data in JSON format and send it as the response
    header('Content-Type: application/json');
    echo json_encode($data);

?>