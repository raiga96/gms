<?php

require 'connect.php';

$uid = $_POST['uid'];
$newpwd = $_POST['newpwd'];
$repwd = $_POST['repwd'];

if(empty($newpwd) || empty($repwd)){
    echo json_encode(array("status" => "error", "message" => "Please fill all the password input"));
    exit();
}else if ( $newpwd !== $repwd ) {
    echo json_encode(array("status" => "error", "message" => "Password Not Match"));
    exit();
} else {
    $sql = "SELECT * FROM users WHERE uid = '$uid' OR user_email = '$uid' OR user_id = '$uid'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $newuid = $row['uid'];

    $hashedPwd = password_hash( $newpwd, PASSWORD_DEFAULT );

    $sql5 = "UPDATE `users` SET `user_password` = '$hashedPwd' WHERE UID = ?";
    $stmt5 = $conn->prepare($sql5);
    $stmt5->bind_param("s", $newuid);

    if ($stmt5->execute()) {
         $data = array(
            
                    "status" => "success",
                    
                );
        echo json_encode($data);

    } else {
        echo json_encode(array("status" => "error", "message" => "Error Occured!"));
    }
}

?>