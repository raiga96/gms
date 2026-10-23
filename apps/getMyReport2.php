<?php

require 'connect.php';

$uid = $_GET['uid'];
//$sdate = date("Y-m-d", strtotime($_GET['StartDate']));
//$ndate =date("Y-m-d", strtotime($_GET['EndDate']));
$nowdate = date( "Y-m-d" );
$curYear = date( "Y");


    // $sql = "SELECT distinct(calendar.datefield) AS DATE,  IFNULL(qrscan.date, 0) AS absent 
    //             FROM qrscan RIGHT JOIN calendar ON (DATE(qrscan.date) = calendar.datefield)
    //             WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$sdate')) FROM qrscan) AND (SELECT MAX(DATE('$ndate')) FROM qrscan))    
    //             AND calendar.datefield <= '$nowdate'  ORDER BY DATE ASC ";
    // $result = $conn->query($sql);

    // $data = array();
    // if ($result->num_rows > 0) {
    //     while($Y = $result -> fetch_assoc()){
    //                 $sql1 = "SELECT * FROM qrscan WHERE date like '".$Y["DATE"]."' AND uid = '$uid'";
    //                 $exc1 = $conn -> query($sql1);
    //                 while ($row = $exc1 -> fetch_assoc()) {
    //                     $data[] = $Y;
    //                 }
            
    //     }
    // }
$sql = "SELECT DISTINCT calendar.datefield AS DATE, COALESCE(qrscan.date, 0) AS absent 
        FROM qrscan RIGHT JOIN calendar ON (DATE(qrscan.date) = calendar.datefield)
        WHERE YEAR(calendar.datefield) = '$curYear'    
        AND calendar.datefield <= '$nowdate'  
        ORDER BY DATE ASC";

$result = $conn->query($sql);

// Fetch data and store it in an array
$data = array();

if ($result->num_rows > 0) {
    while ($Y = $result->fetch_assoc()) {

        $sql2 = "SELECT DATE_FORMAT('".$Y["DATE"]."', '%a') as namahari FROM calendar ";
        $exc2 = mysqli_query($conn, $sql2);
        $result2 = $exc2->fetch_assoc();
        $hari = $result2[ 'namahari' ]; 

        $sql3 = "SELECT * FROM holiday WHERE holiday_date = '".$Y["DATE"]."' ";
        $exc3 = mysqli_query($conn, $sql3);
        $result3 = $exc3->fetch_assoc();
        $holiday = $result3[ "desc" ];

        $sqlCC = "SELECT * FROM qrscan WHERE date LIKE '".$Y["DATE"]."' AND uid = '$uid' -- REPORT (2) APPS";
        $excCC = $conn->query($sqlCC);

        if ($excCC -> num_rows > 0){
            while ($rowCC = $excCC -> fetch_assoc()){
                $dataKehadiran = $rowCC['record_id'];
            }
        }else{
            $dataKehadiran = 'absent';
        }

        $statusHari = $Y['absent'];
        $dateSebenar = $Y['DATE'];
        $title = '';
        $statKehadiran = $dataKehadiran;
        if(!empty($holiday) && $holiday != 'WEEKEND' && $statKehadiran == 'absent'){
            $title = $holiday;
        }else if($statusHari != '0' && $holiday == 'WEEKEND' && $statKehadiran == 'absent'){
            $title = 'WEEKEND';
        }else if($statusHari != '0' && $holiday != 'WEEKEND' && $statKehadiran == 'absent'){
            $title = 'ABSENT';
        }else{
            $title = 'PRESENT';
        }


        $sqlXC = "SELECT * FROM qrscan WHERE date LIKE '" . $Y["DATE"] . "' AND uid = '$uid' -- GET REPORT (2) FIRST";
        $excXC = $conn->query($sqlXC);

        $timeIn = "absent";
        $timeOut = "absent";
        $timeIn1 = "absent";
        $timeOut1 = "absent";
        $scannerIn = "0.0.0.0";
        $scannerOut = "0.0.0.0";
        $latIn = "0.0";
        $lngIn = "0.0";
        $latOut = "0.0";
        $lngOut = "0.0";
        $markIn = "0.0";
        $markOut = "0.0";
        $record_id = "0";
        $hari_ini = "";
        $inStat = "";
        $outStat = "";

        if ($excXC->num_rows > 0) {
            while ($rowXC = $excXC->fetch_assoc()) {
                $timeIn = date('h:i a', strtotime($rowXC['timeIN']));
                $timeOut = date('h:i a', strtotime($rowXC['timeOUT']));
                $timeIn1 = date('Hi', strtotime($rowXC['timeIN']));
                $timeOut1 = date('Hi', strtotime($rowXC['timeOUT']));
                $scanIN = $rowXC['scannerIN'];
                $scanOUT = $rowXC['scannerOUT'];
                $latIn = $rowXC['latIN'];
                $lngIn = $rowXC['lngIN'];
                $latOut = $rowXC['latOUT'];
                $lngOut = $rowXC['lngOUT'];
                $markIn = $rowXC['markIN'];
                $markOut = $rowXC['markOUT'];
                $record_id = $rowXC['record_id'];
                $scanIPin = $rowXC[ "scannerIN" ];
                $scanIPout = $rowXC[ "scannerOUT" ];
                    
                    $scanINmain=substr($scanIPin,0,3);
                    $scanIN=substr($scanIPin,4,5);//extract IP Class B n C
                    
                    if($scanINmain == '172'){
                    $sqlSubIN = "SELECT * FROM kiosk WHERE subnet = '" . $scanIN . "'";
                        $resultSubIn = $conn->query( $sqlSubIN );
                        $rowSubI = $resultSubIn->fetch_assoc();
                        $scannerIn = $rowSubI[ 'LocDesc' ];
                    }else{
                    $scannerIn = 'Work From Location';
                    }
                    
                    $scanOUTmain=substr($scanIPout,0,3);
                    $scanOUT=substr($scanIPout,4,5);//extract IP Class B n C
                    
                    if($scanOUTmain == '172'){
                    $sqlSubOUT = "SELECT * FROM kiosk WHERE subnet = '" . $scanOUT . "'";
                        $resultSubOut = $conn->query( $sqlSubOUT );
                        $rowSubO = $resultSubOut->fetch_assoc();
                        $scannerOut = $rowSubO[ 'LocDesc' ];
                    }else{
                    $scannerOut = 'Work From Location';
                    }

                    if($timeIn1 > 0800){
                        $inStat = "LATE";
                    }else if($timeIn1 <= 0800){
                        $inStat = "ONTIME";
                    }

                    if($timeOut1 == '0000'){
                        $outStat = "NCO";
                    }else if($timeOut1 < 1700){
                        $outStat = "EARLY";
                    }else if($timeOut1 >= '1700'){
                        $outStat = "ONTIME";
                    }


            }
        }
        

        $dateData = array(
            'date' => $Y['DATE'],  
            'pubday' => $Y['absent'],
            'inStat' => $inStat,
            'outStat' => $outStat,
            'title' => $title, 
            'hari' => $hari,
            'timeIn' => $timeIn,
            'timeOut' => $timeOut,
            'timeIn1' => $timeIn1,
            'timeOut1' => $timeOut1,
            'scannerIn' => $scannerIn,
            'scarnnerOut' => $scannerOut,
            'latIn' => $latIn,
            'lngIn' => $lngIn,
            'latOut' => $latOut,
            'lngOut' => $lngOut,
            'markIn' => $markIn,
            'markOut' => $markOut,
            'recordId' => $record_id,
            'qrscan_data' => array()
        );

        $sql1 = "SELECT * FROM qrscan WHERE date LIKE '".$Y["DATE"]."' AND uid = '$uid' -- GET REPORT (2) LAST";
        $exc1 = $conn->query($sql1);

        while ($row = $exc1->fetch_assoc()) {
            $dateData['qrscan_data'][] = $row;
        }
        // Check if qrscan_data is empty
        if (empty($dateData['qrscan_data'])) {
            $dateData['qrscan_data'] = "absent";
        }

        $data[] = $dateData;
    }
}
    else{
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
