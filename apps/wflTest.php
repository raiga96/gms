<?php
require 'connect.php';
$uid = $_POST['uid'];
$remark = $_POST['remark'];
$ipAddr = $_POST['ipAddr'];
$act = $_POST['act'];
$lat = $_POST['lat'];
$long = $_POST['long'];
$dateToday = date('Y-m-d');
$clockTime = date('H:i:s');
$ADA = 'false';

    if(empty($lat) || empty($long)){
        echo json_encode(array("status" => "error", "message" => "Please enable your location services"));
        exit();
    }else if (empty($remark)) {
        echo json_encode(array("status" => "error", "message" => "Remark empty!"));
        exit();
    }else{
        if($act == 'IN'){
    

       /* (Hilary hide tok)     
        $sql = "SELECT * FROM users WHERE uid = '$uid'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $newuid = $row['uid'];
        */
            // Scan only 1 CLOCK-IN (Hilary Tambah)        
              $query = "SELECT * FROM qrscan WHERE uid = '".$uid."' AND date ='".$dateToday."' ";
              $result = $conn -> query($query);
              if($result -> num_rows > 0 ){
                $ADA = 'true';
              } 
                // $result =mysqli_query($conn, $query);
                // if($row = mysqli_num_rows( $result )>0 )
                //     { $ADA = true; }

        if($ADA != 'true'){

        $sql5 = "INSERT INTO `qrscan` (`uid`,`date`,`timeIN`,`scannerIN`,`latIN`,`lngIN`,`markIN`) VALUES (?,?,?,?,?,?,?)";
        $stmt5 = $conn->prepare($sql5);
        $stmt5->bind_param("sssssss", $uid, $dateToday, $clockTime, $ipAddr, $lat, $long, $remark);
        if ($stmt5->execute()) {
        
        
            // $data = array(
            // "status" => "success",
            // "uid" => $uid,
                        
            // );
            //echo json_encode($data);
            echo json_encode(array("status" => "success", "uid" => $uid));
         
        } else {        
            echo json_encode(array("status" => "error", "message" => "Error Occured IN"));
        }

        }else{
            echo json_encode(array("status" => "error", "message" => "Your Clock IN already Taken"));
        }

    } else if($act == 'OUT'){
        /* (Hilary hide tok)
        $sql = "SELECT * FROM users WHERE uid = '$uid'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $newuid = $row['uid'];
        */

        $sql5 = "UPDATE `qrscan` SET `timeOUT`='$clockTime',`scannerOUT`='$ipAddr',`latOUT`='$lat',`lngOUT`='$long',`markOUT`='$remark' WHERE `uid` = ? AND `date` = ?";
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