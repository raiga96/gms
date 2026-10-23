<?php

require 'connect.php';
require 'conn.php';

$uid = $_GET['uid'];
// $sdate = date("Y-m-d", strtotime($_GET['StartDate']));
// $ndate =date("Y-m-d", strtotime($_GET['EndDate']));
$nowdate = date( "Y-m-d" );
$curYear = date( "Y");

// Calculate the start date of the current week (Sunday)
$sdate = date('Y-m-d', strtotime('last Sunday', strtotime($nowdate)));

// Calculate the end date of the current week (Saturday)
$ndate = date('Y-m-d', strtotime('next Saturday', strtotime($nowdate)));

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
$timer=date('H:i:s');

  //redirect if hour between 8:00 to 21:00
  if(  (($timer > "07:30:00")  &&  ($timer < "08:30:00")) || (($timer > "16:30:00")  &&  ($timer < "17:30:00")) ) {
    
    $data = array();
        $data[] = [];

    
    // Convert the data to JSON format
    $jsonData = json_encode($data);

    // Set the Content-Type header to indicate that the response contains JSON data
   // Encode the data in JSON format and send it as the response
    header('Content-Type: application/json');
    echo json_encode($data);


  }else{
$sql = "SELECT DISTINCT calendar.datefield AS DATE, COALESCE(qrscan.date, 0) AS absent 
        FROM qrscan RIGHT JOIN calendar ON (DATE(qrscan.date) = calendar.datefield)
        WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$sdate')) FROM qrscan) AND (SELECT MAX(DATE('$ndate')) FROM qrscan))    
        AND calendar.datefield <= '$nowdate'  
        ORDER BY DATE ASC";

$result = $conn->query($sql);

// Fetch data and store it in an array
$data = array();

if ($result-> num_rows > 0) {
    while ($Y = $result->fetch_assoc()) {

        $sql2 = "SELECT DATE_FORMAT('".$Y["DATE"]."', '%a') as namahari FROM calendar ";
        $exc2 = mysqli_query($conn1, $sql2);
        $result2 = $exc2->fetch_assoc();
        $hari = $result2[ 'namahari' ]; 

        $sql3 = "SELECT * FROM holiday WHERE holiday_date = '".$Y["DATE"]."' ";
        $exc3 = mysqli_query($conn1, $sql3);
        $result3 = $exc3->fetch_assoc();
        $holiday = $result3[ "desc" ];

        $sqlCC = "SELECT * FROM qrscan WHERE date LIKE '".$Y["DATE"]."' AND uid = '$uid' -- SQL1 MAIN REPORTS APPS";
        $excCC = $conn1->query($sqlCC);

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
    /* 
       if(!empty($holiday) && $holiday != 'WEEKEND' && $statKehadiran == 'absent'){
            $title = $holiday;
        }else if($statusHari != '0' && $holiday == 'WEEKEND' && $statKehadiran == 'absent'){
            $title = 'WEEKEND';
        }else if($statusHari != '0' && $holiday != 'WEEKEND' && $statKehadiran == 'absent'){
            $title = 'ABSENT';
        }else{
            $title = 'PRESENT';
        }
    */

////Edit by HB
        if($statKehadiran != 'absent'){
            $title = 'PRESENT';
        }else if(!empty($holiday) && $statKehadiran == 'absent'){
            $title = $holiday;
        }else if($statKehadiran == 'absent'){
            $title = 'ABSENT';
        }
////
        
        $sqlXC = "SELECT * FROM qrscan WHERE date LIKE '" . $Y["DATE"] . "' AND uid = '$uid' -- SQL2 MAIN REPORTS APPS";
        $excXC = $conn1->query($sqlXC);

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
        $remark_id = "0";
        $remarkBesar = "0";
        $remarkType = "0";
        $remarkApv = "0";
        $remarkApvBy = "0";
        $remarkApvOn = "0";
        $statusRemark = "N";

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
                    
            }
        }
        $sqlExp = "SELECT * FROM exception WHERE date = '".$Y["DATE"]."' AND uid = '$uid' -- EXP REPORTS APPS";
                    $excExp = $conn1 -> query($sqlExp);
                    if($excExp -> num_rows > 0){
                        while($rowExp = $excExp -> fetch_assoc()){
                            $remark_id = $rowExp['remarkID'];
                            $remarkBesar = $rowExp['remark'];
                            $remarkType = $rowExp['remarktype'];
                            $remarkApv = $rowExp['remarkApv'];
                            $remarkApvBy = $rowExp['remarkApvBy'];
                            $remarkApvOn = $rowExp['remarkApvOn'];

                            if(empty($remark_id)){
                                $statusRemark = 'N';
                            }else{
                                $statusRemark = 'Y';
                            }
                        }
                    }

        $dateData = array(
            'date' => $Y['DATE'],  
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
            'remarkId' => $remark_id,
            'remarkBesar' => $remarkBesar,
            'remarkType' => $remarkType,
            'remarkApv' => $remarkApv,
            'remarkApvBy' => $remarkApvBy,
            'remarkApvOn' => $remarkApvOn,
            'statusRemark' => $statusRemark,
            'qrscan_data' => array()
        );

        $sql1 = "SELECT * FROM qrscan WHERE date LIKE '".$Y["DATE"]."' AND uid = '$uid' -- SQL LAST MAIN REPORTS APPS";
        $exc1 = $conn1->query($sql1);

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
}
?>
