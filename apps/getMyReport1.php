<?php
require 'connect.php';

$uid = $_GET['uid'];
$sdate = date("Y-m-d", strtotime($_GET['StartDate']));
$ndate = date("Y-m-d", strtotime($_GET['EndDate']));
$nowdate = date("Y-m-d");
$curYear = date("Y");

$sql = "SELECT DISTINCT calendar.datefield AS DATE, COALESCE(qrscan.date, 0) AS absent 
        FROM qrscan RIGHT JOIN calendar ON (DATE(qrscan.date) = calendar.datefield)
        WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$sdate')) FROM qrscan) AND (SELECT MAX(DATE('$ndate')) FROM qrscan))    
        AND calendar.datefield <= '$nowdate'  
        ORDER BY DATE ASC";

$result = $conn->query($sql);

// Fetch data and store it in an array
$data = array();

if ($result->num_rows > 0) {
    while ($Y = $result->fetch_assoc()) {

        $sql2 = "SELECT DATE_FORMAT('" . $Y["DATE"] . "', '%a') as namahari FROM calendar ";
        $exc2 = mysqli_query($conn, $sql2);
        $result2 = $exc2->fetch_assoc();
        $hari = $result2['namahari'];

        $sql3 = "SELECT * FROM holiday WHERE holiday_date = '" . $Y["DATE"] . "' ";
        $exc3 = mysqli_query($conn, $sql3);
        $result3 = $exc3->fetch_assoc();
        $holiday = $result3["desc"];

        $sqlCC = "SELECT * FROM qrscan WHERE uid = '$uid' AND date LIKE '" . $Y["DATE"] . "'";
        $excCC = $conn->query($sqlCC);

        $timeIn = "absent";
        $timeOut = "absent";

        if ($excCC->num_rows > 0) {
            while ($rowCC = $excCC->fetch_assoc()) {
                $timeIn = date('h:i a', strtotime($rowCC['timeIN']));
                $timeOut = date('h:i a', strtotime($rowCC['timeOUT']));
            }
        }

        $dateData = array(
            'date' => $Y['DATE'],
            'hari' => $hari,
            'holiday' => $holiday,
            'timeIn' => $timeIn,
            'timeOut' => $timeOut
        );

        $data[] = $dateData;
    }
} else {
    $data = array();
    $data[] = ["error" => "No data found"];
}

// Close the database connection
$conn->close();

// Encode the data in JSON format and send it as the response
header('Content-Type: application/json');
echo json_encode($data);
?>
