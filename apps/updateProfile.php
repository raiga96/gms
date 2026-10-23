<?php
// Disable error reporting
error_reporting(0);

// Turn off display of errors
ini_set('display_errors', 0);

require 'connect.php';

$email = $_POST['email'];
$uid = $_POST['uid'];
$sect = $_POST['section'];
$div = $_POST['division'];
$fullname = $_POST['fullname'];
$phone = $_POST['phone'];
$type = $_POST['type'];

$sq1 = "SELECT * FROM brasecunit WHERE BSU_name LIKE '%".$sect."%'";
$res1 = $conn -> query($sq1);
$row1 = $res1 -> fetch_assoc();
$section = $row1['BSU_id'];

$sq2 = "SELECT * FROM division WHERE DIV_NAME LIKE '%".$div."%'";
$res2 = $conn -> query($sq2);
$row2 = $res2 -> fetch_assoc();
$division = $row2['DIV_ID'];


// $targetDir = "../assets/img/profileImage/";
// $linkDir = "assets/img/profileImage/";
// $uploadOk = 1;

// function isFileEmpty($file) {
//     return $_FILES[$file]["size"] == 0;
// }

$sql = "SELECT * FROM users WHERE user_email = '$uid' OR uid = '$uid' OR user_id = '$uid'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$newuid = $row['uid'];

$csql = "SELECT * FROM userinfo WHERE uid = '$newuid'";
$cres = $conn -> query($csql);
$crow = $cres -> fetch_assoc();
$cname = $crow['fullname'];
$csect = $crow['section'];
$cdiv = $crow['division'];
$cphone = $crow['phone'];

// Check if a file is uploaded

if($type == 'updateProfile'){

    if($cname == $fullname && $csect == $section && $cdiv == $division && $cphone == $phone){
        echo json_encode(array("status" => "error", "message" => "No change were made"));
    }else{

        $sql2 = "UPDATE `userinfo` SET `fullname` = '$fullname',`section` = '$section',`division` = '$division',`phone` = '$phone' WHERE `uid` = '$newuid'";
        if(mysqli_query($conn, $sql2)){
                if(($cname != $fullname) && ($cphone != $phone) && ($csect != $section) && ($cdiv != $division)){
                    $change = 'Profile';
                }else if(($cname != $fullname) && ($csect != $section)){
                    $change = 'Profile';
                }else if(($cname != $fullname) && ($cdiv != $division)){
                    $change = 'Profile';
                }else if(($cphone != $phone) && ($csect != $section)){
                    $change = 'Profile';
                }else if(($cphone != $phone) && ($cdiv != $division)){
                    $change = 'Profile';
                }else if(($csect != $section) && ($cdiv != $division)){
                    $change = 'Profile';
                }else if($cname != $fullname){
                    $change = 'Fullname';
                }else if($csect != $section){
                    $change = 'Branch/Section';
                }else if($cdiv != $division){
                    $change = 'Division';
                }else if($cphone != $phone){
                    $change = 'Phone';
                }else{
                    $change = 'Profile';
                }

        // $sql1 = "INSERT INTO `qreasy_log` (`LOG_BY`, `ACTIVITY`) VALUES (?, '$change Update')"; 
        $sql1 = "INSERT INTO `qreasy_log` (`LOG_BY`, `ACTIVITY`) VALUES (?, '" . $change . " Update')";
  
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("s", $newuid);
        $stmt1->execute();
    
                $data = array(
                    "status" => "success",
                    "message" => "$change update success",
                    "uid" => $uid
                    
                );
                    echo json_encode($data);
    
            
        } else {   
            echo json_encode(array("status" => "error", "message" => "Error Updating your profile"));
        }
    }
} elseif ($type == 'updateImage'){

$targetDir = "../assets/img/profileImage/";
$linkDir = "assets/img/profileImage/";
$uploadOk = 1;
$file = $_POST['file'];

if (empty($_FILES["file"]["name"])) {
    // No file uploaded, return an error response
    echo json_encode(array("status" => "error", "message" => "No File Uploaded"));
} else {
    // File is uploaded, proceed with processing
    function isFileEmpty($file) {
        return $_FILES[$file]["size"] == 0;
    }
    if (!is_readable($_FILES["file"]["tmp_name"])) {
            echo json_encode(array("status" => "error", "message" => "Temporary file location is not readable"));
            exit();
        }
        elseif (!is_writable($targetDir)) {
            echo json_encode(array("status" => "error", "message" => "Target directory is not writable"));
            exit();
        }
    elseif (isFileEmpty("file")) {
         echo json_encode(array("status" => "error", "message" => "Error Uploading your image"));
    } else {
        
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
                    $sql = "UPDATE `profile_image` SET `FILE_NAME` = ?, `FILE_PATH` = ? WHERE `UID` = ?";
                    //$sql = "INSERT INTO profile_image (UID, FILE_NAME, FILE_PATH, FILE_TYPE) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $newName, $linkTo, $uid);
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

        if (moveFile("file", $newuid . '_' . basename($_FILES["file"]["name"]), 'PROFILE_IMAGE', $targetDir, $linkDir, $newuid, $conn)) {
            
             $sql1 = "INSERT INTO `qreasy_log` (`LOG_BY`, `ACTIVITY`) VALUES (?, 'IMAGE UPDATE')";   
                $stmt1 = $conn->prepare($sql1);
                $stmt1->bind_param("s", $newuid);
                $stmt1->execute();

                echo json_encode(array("status" => "error", "message" => "Profile Image Update Successfully"));
            
        } else {
            echo json_encode(array("status" => "error", "message" => "Error Occured"));
        }
    }
}


}else{
     echo json_encode(array("status" => "error", "message" => "Error Updating your profile"));
}


?>