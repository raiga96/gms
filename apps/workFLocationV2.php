<?php
require 'connect.php';
$uid = $_POST['uid'];
$ipAddr = '172.25.99.240';
$act = $_POST['act'];
$lat = $_POST['lat'];
$long = $_POST['long'];
$dateToday = date('Y-m-d');
$clockTime = date('H:i:s');

    if(empty($lat) || empty($long)){
        echo json_encode(array("status" => "error", "message" => "Please enable your location services"));
        exit();
    }else{
        if($act == 'IN'){
        $sql = "SELECT * FROM users WHERE uid = '$uid'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $newuid = $row['uid'];

        $sql5 = "INSERT INTO `qrscan` (`uid`,`date`,`timeIN`,`scannerIN`,`latIN`,`lngIN`) VALUES (?,?,?,?,?,?)";
        $stmt5 = $conn->prepare($sql5);
        $stmt5->bind_param("ssssss", $uid, $dateToday, $clockTime, $ipAddr, $lat, $long);
        if ($stmt5->execute()) {
        
        
            $data = array(
            "status" => "success",
            "uid" => $uid,
            "email" => $email,
            "access_level" => $access,
            "sessionToken" => $token
                        
            );
            echo json_encode($data);
         
        } else {        
            echo json_encode(array("status" => "error", "message" => "Error Occured In"));
        }
    } else if($act == 'OUT'){
        $sql = "SELECT * FROM users WHERE uid = '$uid'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $newuid = $row['uid'];

        $sql5 = "UPDATE `qrscan` SET `timeOUT`='$clockTime',`scannerOUT`='$ipAddr',`latOUT`='$lat',`lngOUT`='$long' WHERE `uid` = ? AND `date` = ?";
        $stmt5 = $conn->prepare($sql5);
        $stmt5->bind_param("ss", $uid, $dateToday);
        if ($stmt5->execute()) {
        
        
            $data = array(
            "status" => "success",
            "uid" => $uid
                        
            );
            echo json_encode($data);
         
        } else {        
            echo json_encode(array("status" => "error", "message" => "Error Occured Out"));
        }
    }
}

?>