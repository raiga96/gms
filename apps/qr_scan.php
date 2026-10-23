<?php
require 'connect.php';

// Get input data
$uuid = $_POST['uid'];
$sctype = $_POST['SUBMIT'];
$seckey = $_POST['second'];
$thikey = $_POST['third'];

    //check user
    $sql = "SELECT * FROM users WHERE user_email = '".$uuid."' OR user_id = '".$uuid."' OR uid = '".$uuid."'";
    $result = $conn -> query($sql);
    $row = $result -> fetch_assoc();
    $uid = $row['uid'];
    // Check if the user already scan
    $chk = "SELECT * FROM check_in WHERE uid = '$uid' AND activity = '$seckey'";
    $cres = $conn -> query($chk);

    if($crow = $cres -> fetch_assoc()){

        echo json_encode("scanned");
        $conn -> close();
        
    }else{

    // Log the QR scan activity
    $sql1 = "INSERT INTO `qreasy_log` (`LOG_BY`, `ACTIVITY`) VALUES (?, 'QR ATTENDANCE SCAN')";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("s", $uid);
    $stmt1->execute();

    // Insert attendance data into the attendance table
    $sql5 = "INSERT INTO `check_in` (`uid`,`activity`) VALUES (?, ?)";
    $stmt5 = $conn->prepare($sql5);
    $stmt5->bind_param("ss", $uid, $seckey);

    if ($stmt5->execute()) {
        echo json_encode("success");
    } else {
        echo json_encode("fail");
    }
    $conn -> close();
}
?>