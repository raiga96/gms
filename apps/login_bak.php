<?php

require 'connect.php';

$uname = $_POST['uname'];
$pwd = $_POST['pwd'];
$logdate = date("Y-m-d");
$logtime = date("H:i:s");



$sql = "SELECT * FROM users WHERE user_email='" . $uname . "' OR SUBSTRING_INDEX(user_email, '@', 1) = '" .$uname. "' -- LOGIN APPS";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row) {
    $pwdC = password_verify($pwd, $row['user_password']);

    if ($row['status'] == 'Y') {
        if ($pwdC) {
            $newuid = $row['uid'];
            
            $sqLog = "INSERT INTO user_log SET uid = '$newuid', log_type = 'MOBILE_LOGIN', log_date = '$logdate', log_time = '$logtime'";
            if(mysqli_query($conn, $sqLog)){

            // Generate a secure session token
            $token = md5(uniqid());
            $uid = $row['uid'];
            $email = $row['user_email'];
            $role = $row['access_level'];
            $response = array(
                "status" => "success",
                "uid" => $uid,
                "email" => $email,
                "role" => $role,
                "sessionToken" => $token
            );
            echo json_encode($response);
        } else {
            echo json_encode(array("status" => "error", "message" => "Log Error"));
        }
    } else{
        echo json_encode(array("status" => "error", "message" => "Invalid Id / Password!"));
    }
    } else if ($row['status'] == 'N') {
        if ($pwdC) {
            $uid = $row['uid'];
            $response = array(
                "status" => "not_active",
                "uid" => $uid
            );
            echo json_encode($response);
        } else {
            echo json_encode(array("status" => "error", "message" => "Your Account is inactive. Please contact System Admin"));
        }
    }
} else if (!$row){

    $sql1 = "SELECT * FROM user_temp WHERE email = '".$uname."' OR SUBSTRING_INDEX(email, '@', 1) = '".$uname."' ";
    $result1 = $conn -> query($sql1);
    $row1 = $result1 -> fetch_assoc();

    if($row1){

        if (md5($pwd) === $row1['password']) {
            // Generate a secure session token
            $token = md5(uniqid());
            $response = array(
                "status" => "old_user",
                "uid" => $row1['id'],
                "email" => $row1['email'],
                "fullname" => $row1['fullname'],
                "section" => $row1['section'],
                "division" => $row1['division'],
                "access_level" => $row1['access_level'],
                "phone" => $row1['phone'],
                "active" => $row1['active'],
                "sessionToken" => $token
            );
            echo json_encode($response);

        } else {

            echo json_encode(array("status" => "error", "message" => "Invalid Id / Password!"));

        }

    } else {

         echo json_encode(array("status" => "error", "message" => "User not found!"));

    }

} else {
    echo json_encode(array("status" => "error", "message" => "User not found!"));
}
?>