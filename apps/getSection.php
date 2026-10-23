<?php

require 'connect.php';

    $division = $_GET['div'];

    //$sql = "SELECT * FROM flight_schec ORDER BY FID ASC";
    $sql = "SELECT distinct(BSU),BSU_name, section FROM userinfo A, brasecunit B WHERE B.BSU_id = A.section AND A.division ='$division'";
    $result = $conn->query($sql);

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