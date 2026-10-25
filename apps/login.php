<?php

require 'conn.php';

$uname = (string)($_POST['uname'] ?? '');
$pwd = (string)($_POST['pwd'] ?? '');
$logdate = date("Y-m-d");
$logtime = date("H:i:s");

$stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ? OR SUBSTRING_INDEX(user_email, '@', 1) = ? -- LOGIN APPS");
$stmt->bind_param('ss', $uname, $uname);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row) {
    $pwdC = password_verify($pwd, $row['user_password']);

    if ($row['status'] == 'Y') {
        if ($pwdC) {
            $newuid = (int)$row['uid'];

            $stmtLog = $conn->prepare("INSERT INTO user_log (uid, log_type, log_date, log_time) VALUES (?, 'MOBILE_LOGIN', ?, ?)");
            $stmtLog->bind_param("iss", $newuid, $logdate, $logtime);
            if ($stmtLog->execute()) {

                // Generate a cryptographically secure session token
                $token = bin2hex(random_bytes(16));
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
