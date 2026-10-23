<?php
require 'connect.php';

$name = $_POST['fullname'];
$user_id = $_POST['user_id'];
$email = $_POST['email'];
$pwd = $_POST['pwd'];
$pwdRe = $_POST['repwd'];
$sect = $_POST['section'];
$div = $_POST['division'];
$status = 'Y';
$role = $_POST['role'];

$sq1 = "SELECT * FROM brasecunit WHERE BSU_name LIKE '%".$sect."%'";
$res1 = $conn -> query($sq1);
$row1 = $res1 -> fetch_assoc();
$section = $row1['BSU_id'];

$sq2 = "SELECT * FROM division WHERE DIV_NAME LIKE '%".$div."%'";
$res2 = $conn -> query($sq2);
$row2 = $res2 -> fetch_assoc();
if(empty($row2['DIV_ID'])){
    $division = '00';
}else{
    $division = $row2['DIV_ID'];
}

$targetDir = "../assets/img/profileImage/";
$linkDir = "assets/img/profileImage/";
$uploadOk = 1;

function isFileEmpty($file) {
    return $_FILES[$file]["size"] == 0;
}

if (isFileEmpty("file")) {
    echo json_encode(array("status" => "error", "message" => "Please Upload Your Image!"));
    exit();
}

function moveFile($file, $newName, $fileType, $targetDir, $linkDir, $uid, $conn) {
        global $uploadOk;
        $targetFile = $targetDir . $newName;
        $linkTo = $linkDir . $newName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
        if ($uploadOk == 0) {
            echo json_encode("notupload");
            exit();
        } else {
            if (move_uploaded_file($_FILES[$file]["tmp_name"], $targetFile)) {
                $sql = "INSERT INTO profile_image (UID, FILE_NAME, FILE_PATH, FILE_TYPE) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $uid, $newName, $linkTo, $fileType);
                if ($stmt->execute()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }


if ( empty( $name ) || empty($sect) || empty( $email ) || empty( $pwd ) || empty( $pwdRe ) || empty( $div ) || empty( $role ) ) {
    echo json_encode(array("status" => "error", "message" => "Please fill all the requirement"));
    exit();
  } else if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) && !preg_match( "/^[a-zA-Z0-9]*$/", $email ) ) {
    echo json_encode(array("status" => "error", "message" => "Invalid Email"));
    exit();
  } else if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
    echo json_encode(array("status" => "error", "message" => "Invalid Email"));
    exit();
  } else if ( $pwd !== $pwdRe ) {
    echo json_encode(array("status" => "error", "message" => "Password Not Match"));
    exit();
  } else {
        $sql = "SELECT * FROM users WHERE user_email=?";
        $stmt = mysqli_stmt_init( $conn );
    if ( !mysqli_stmt_prepare( $stmt, $sql ) ) {
        echo json_encode(array("status" => "error", "message" => "SQL ERROR"));
      exit();
    } else {
      mysqli_stmt_bind_param( $stmt, "s", $email );
      mysqli_stmt_execute( $stmt );
      mysqli_stmt_store_result( $stmt );
      $resultCheck = mysqli_stmt_num_rows( $stmt );
      if ( $resultCheck > 0 ) {
            echo json_encode(array("status" => "error", "message" => "Your Email has been registered. Proceed to login."));
        } else {

                if($role == 'Normal'){
                    $role = '1';
                }else if ($role == 'Supervisor'){
                    $role = '2';
                }
                $hashedPwd = password_hash( $pwd, PASSWORD_DEFAULT );
                $sql1 = "INSERT INTO users SET user_id = '$user_id', user_email = '$email', user_password = '$hashedPwd', access_level = '$role', status ='$status'";
                if(mysqli_query( $conn, $sql1 )){
                    //bind user id for log id
                    $id = mysqli_insert_id($conn);
                    $s = "SELECT * FROM users WHERE uid = '$id'";
                    $r = $conn -> query($s);
                    $ro = $r -> fetch_assoc();
                    $log_by = $ro['uid'];
                    $logdate = date("Y-m-d");
                    $logtime = date("H:i:s");

                    $sql2 = "INSERT INTO userinfo SET `uid` = '$log_by',`fullname` = '$name',`section` = '$section',`division` = '$division', `phone` = '0000000000' ";
                    if(mysqli_query($conn, $sql2)){
                        $sql3 = "INSERT INTO `qreasy_log` (`LOG_BY`, `ACTIVITY`) VALUES ('$log_by', 'USER REGISTRATION')";
                        if(mysqli_query($conn , $sql3)){
                                $sql = "SELECT * FROM users WHERE uid = '$log_by'";
                                $result = $conn->query($sql);
                                $row = $result->fetch_assoc();
                                $access = $row['access_level'];
                                $email = $row['user_email'];
                                $token = md5(uniqid());
                                
                                if (
                                    moveFile("file", $log_by . '_' . basename($_FILES["file"]["name"]), 'PROFILE_IMAGE', $targetDir, $linkDir, $log_by, $conn)
                                ) {
                                $data = array(
                                    "status" => "success",
                                    "uid" => $log_by,
                                    "email" => $email,
                                    "userid" => $row['user_id'],
                                    "access_level" => $access,
                                    "sessionToken" => $token
                                    
                                );
                                    echo json_encode($data);
                                    } else {
                                        echo json_encode(array("status" => "error", "message" => "Error Occured"));
                                    }
                        }else{
                            echo json_encode(array("status" => "error", "message" => "LOG FAIL"));
                        }   
                    }else{
                        echo json_encode(array("status" => "error", "message" => "FAIL USERINFO !"));
                    }
                }else{
                    echo json_encode(array("status" => "error", "message" => "FAILED!'.$email.$pwd.$repwd.$role.$name.$section.$division.$user_id.'"));
                }

        }
    }
  }
  mysqli_close( $conn );




 ?>