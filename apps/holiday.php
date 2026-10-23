<?php

require 'connect.php';
    $curYear = date('Y');

    $sql = "SELECT *, YEAR(`holiday_date`) as year, DATE_FORMAT(`holiday_date`, '%d %b %Y') as formatted_date, CASE WHEN `desc` LIKE '%Public%' THEN 'p' ELSE 'm' END AS type FROM `holiday` WHERE `desc` != 'WEEKEND' AND YEAR(`holiday_date`) = $curYear ORDER BY holiday_date";
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