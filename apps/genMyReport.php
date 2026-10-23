<?php

require 'connect.php';

$uid = $_POST['uid'];
$sdate = date("Y-m-d", strtotime($_POST['StartDate']));
$ndate =date("Y-m-d", strtotime($_POST['EndDate'])) ;
$nowdate = date( "Y-m-d" );



    //$sql = "SELECT * FROM schedule WHERE DAY = '$day' ORDER BY S_TIME ASC";
    $sql = "SELECT distinct(calendar.datefield) AS DATE,  IFNULL(qrscan.date, 0) AS absent 
				FROM qrscan RIGHT JOIN calendar ON (DATE(qrscan.date) = calendar.datefield)
				WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$sdate')) FROM qrscan) AND (SELECT MAX(DATE('$ndate')) FROM qrscan))	
			    AND calendar.datefield <= '$nowdate'  ORDER BY DATE ASC ";
    $result = $conn->query($sql);

 
    if ($result->num_rows > 0) {
        echo json_encode("success");
    }else{
        echo json_encode("fail");
    }


  
?>
