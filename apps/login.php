<?php

require 'conn.php';

$uname = $_POST['uname'];
$pwd = $_POST['pwd'];
$logdate = date("Y-m-d");
$logtime = date("H:i:s");



$sql = "SELECT * FROM users WHERE user_email='" . $uname . "' OR SUBSTRING_INDEX(user_email, '@', 1) = '" . $uname . "' -- LOGIN APPS";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row) {
    $pwdC = password_verify($pwd, $row['user_password']);

    if ($row['status'] == 'Y') {
        if ($pwdC) {
            $newuid = $row['uid'];

            $sqLog = "INSERT INTO user_log SET uid = '$newuid', log_type = 'MOBILE_LOGIN', log_date = '$logdate', log_time = '$logtime'";
            if (mysqli_query($conn, $sqLog)) {

                // Generate a secure session token
                $token = md5(uniqid());
                $uid = $row['uid'];
                $email = $row['user_email'];
                $role = $row['access_level'];
                $userType = $row['user_type'];
                $response = array(
                    "status" => "success",
                    "uid" => $uid,
                    "email" => $email,
                    "role" => $role,
                    "sessionToken" => $token,
                    "userType" => $userType
                );
                echo json_encode($response);
            } else {
                echo json_encode(array("status" => "error", "message" => "Log Error"));
            }
        } else {
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
} else {
    echo json_encode(array("status" => "error", "message" => "User not found!"));
}
