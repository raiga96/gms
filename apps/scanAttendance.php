<?php
// Assume this PHP script fetches the data you need and returns it as JSON
require 'connect.php';

$uid = $_GET['uid'];
$scanIPin = $_GET[ "scannerIN" ];
$scannerIn = "0.0.0.0";
$scannerOut = "0.0.0.0";
$lat = 0.0;
$lng = 0.0;
$stat = "N";

        $s = "SELECT * FROM users AS a JOIN userinfo AS b ON b.uid = a.uid WHERE a.uid = '".$uid."' OR a.user_id = '$uid' OR a.user_email = '$uid'";
        $r = $conn -> query($s);
        $row = $r -> fetch_assoc();

                    
                    $scanINmain=substr($scanIPin,0,3);
                    $scanIN=substr($scanIPin,4,5);//extract IP Class B n C
                    
                    if($scanINmain == '172' || $scanINmain == '10'){
                    $sqlSubIN = "SELECT * FROM kiosk WHERE subnet = '" . $scanIN . "'";
                        $resultSubIn = $conn->query( $sqlSubIN );
                        $rowSubI = $resultSubIn->fetch_assoc();
                        $scannerIn = $rowSubI[ 'LocDesc' ];
                        // $lat = $rowSubI[ 'lat' ];
                        // $lng = $rowSubI[ 'lng' ];
                        $lat = $rowSubI['lat'];
                        $lng = $rowSubI['lng'];
                        $stat = "Y";
                    }else{
                    $scannerIn = 'Work From Location';
                    $lat = "0.0";
                    $lng = "0.0";
                    $stat = "N";
                    }
                    



        $data = array(
            "uid" => $row['uid'],
            "email" => $row['user_email'],
            "name" => $row['fullname'],
            "scanner" => $scannerIn,
            "lat" => $lat,
            "lng" => $lng,
            "stat" => $stat,
            "allDis" => $rowSubI['allDis'],
        );

    $jsonData = json_encode($data);
    
    // Convert the data to JSON format
    

    $conn->close();

    // Set the Content-Type header to indicate that the response contains JSON data
    header('Content-Type: application/json');

    // Output the JSON data
    print_r($jsonData);


?>