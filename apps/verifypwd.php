<?php

require 'connect.php';

$uid = $_POST['uid'];
$verpwd = $_POST['verpwd'];



$sql = "SELECT * FROM users WHERE uid = '$uid' OR user_email = '$uid' OR user_id = '$uid'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row) {
    $pwdC = password_verify($verpwd, $row['user_password']);

    if(empty($verpwd)){
         echo json_encode(array("status" => "error", "message" => "Please input your current password to validate"));
    }else{
        if ($pwdC) {
            $response = array(
                "status" => "verified",
                "uid" => $row['uid'],
                "email" => $row['user_email'],
                "user_id" => $row['user_id']
            );
            echo json_encode($response);
        } else {
            echo json_encode(array("status" => "error", "message" => "Invalid Id / Password!"));
        }
    }

} else {
    echo json_encode(array("status" => "error", "message" => "Error Occured!"));
}
?>