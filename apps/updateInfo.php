<?php
require 'connect.php';
$status = 'Y';
$email = $_POST['email'];
$uid = $_POST['uid'];
$sect = $_POST['section'];
$div = $_POST['division'];
$fullname = $_POST['fullname'];
$user_id = $_POST['user_id'];
$cur_pwd = $_POST['cur_pwd'];
$phone = $_POST['phone'];
$access_level = $_POST['access_level'];

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
        // Continue with file processing
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
    
    // Insert attachments
    if (
        moveFile("file", $uid . '_' . basename($_FILES["file"]["name"]), 'PROFILE_IMAGE', $targetDir, $linkDir, $uid, $conn)
    ) {
        $sql1 = "INSERT INTO `qreasy_log` (`LOG_BY`, `ACTIVITY`) VALUES (?, 'IMAGE UPLOAD & INFO UPDATE')";
    
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("s", $uid);
        $stmt1->execute();

        $sql2 = "INSERT INTO `userinfo` (`uid`,`fullname`,`section`,`division`,`phone`) VALUES (?,?,?,?,?)";
    
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("sssss", $uid, $fullname, $section, $division, $phone);
        $stmt2->execute();

        $hashedPwd = password_hash( $cur_pwd, PASSWORD_DEFAULT );

        $sql5 = "INSERT INTO `users` (`uid`,`user_id`,`user_email`,`user_password`,`access_level`,`status`) VALUES (?,?,?,?,?,?)";
        $stmt5 = $conn->prepare($sql5);
        $stmt5->bind_param("ssssss", $uid, $user_id, $email, $hashedPwd, $access_level, $status);
        if ($stmt5->execute()) {
    
                $sql = "SELECT * FROM users WHERE uid = '$uid'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $access = $row['access_level'];
                $email = $row['user_email'];
                $token = md5(uniqid());
    
                $data = array(
                    "status" => "success",
                    "uid" => $uid,
                    "email" => $email,
                    "access_level" => $access,
                    "sessionToken" => $token
                    
                );
                    echo json_encode($data);
    
            
        } else {
    
            echo json_encode("failappCheck");
    
        }
    } else {
    
        echo json_encode(array("status" => "error", "message" => "Error Occured"));
    }

?>