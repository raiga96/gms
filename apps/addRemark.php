<?php
require 'connect.php';

// Get input data
$uuid = $_POST['uid'];
$remark = $_POST['remark'];
$act = $_POST['act'];
$recordId = $_POST['recordId'];
$date = $_POST['date'];
$remarktype = '3';
$spv = $_POST['spv'];

$rm1 = date( "y" );
$rm2 = date( "m" );
$rm3 = date( "d" );
$rm4 = rand(1,999);
$remarkID = 'A'.$rm1.$rm2.$rm3.$rm4;

// $uuid = $_GET['uid'];
// $remark = $_GET['remark'];
// $act = $_GET['act'];
// //$recordId = $_GET['recordId'];
// $date = $_GET['date'];
// $spv = $_GET['spv'];

    //check user
    $sql = "SELECT * FROM users WHERE user_email = '".$uuid."' OR user_id = '".$uuid."' OR uid = '".$uuid."'";
    $result = $conn -> query($sql);
    $row = $result -> fetch_assoc();
    $uid = $row['uid'];
    $EMAIL = $row['user_email'];

    if(empty($remark)){
         echo json_encode(array("status" => "error", "message" => "Remark Column Empty"));
        //echo json_encode(array("status"=>"error"));
        $conn -> close();
    }else{
    
    if($act == 'ABSENT'){

        $sql1 = "INSERT INTO exception ( uid, date, remarkID, remarktype, remark, remarkApvBy) VALUES(?,?,?,?,?,?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ssssss", $uid, $date, $remarkID, $remarktype, $remark, $spv);
        

        $category = 'ABSENT';   
                
/////////////////////////////////////////////////email to pemohon

$to2 = "".$EMAIL."";
$subject2 = "Your Attendance Exception Reason for ".$category." on ".$date." has been successfully submited to your supervisor. ";
$msg2 = " 
".$category." Date : ".$date."
Reason : ".$remark."

This is a system generated email. Please do not reply to it.

Thank You
Regards
QREASy
Land and Survey Department ";

$headers2 = "From: donotreply-QREASy@sarawak.gov.my \r\n";

mail("$to2", "$subject2", "$msg2",  "$headers2" );


$sqlspv = "SELECT * FROM users WHERE uid = '".$spv."' ";
    $resultMail = $conn->query( $sqlspv );
    $rowmail = $resultMail->fetch_assoc();
    $EmailSPV = $rowmail[ 'user_email' ];

//////////////////////////////////////////////// email to supervisor

$to = "".$EmailSPV."";
$subject = "New ".$category." reason for your approval. ";
$msg = "
Requester : ".$NAME."
".$category." Date : ".$date."
Reason : ".$remark."

To Approve or Reject the ".$category." reason please click at this link https://jts.sarawak.gov.my/qreasyV2/ApvList?menu=3

This is a system generated email. Please do not reply to it.

Thank You
Regards
QREASy
Land and Survey Department ";

$headers = "From: donotreply-QREASy@sarawak.gov.my \r\n";

mail("$to", "$subject", "$msg",  "$headers" );

    }else if($act == 'LCI&ECO'){

        $sql1 = "UPDATE `qrscan` SET `markIN` = ? WHERE `record_id` = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ss", $remark, $recordId);
        

    }else if ($act == 'LCI&NTO'){

        $sql1 = "UPDATE `qrscan` SET `markIN` = ? WHERE `record_id` = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ss", $remark, $recordId);
        

    }else if ($act == 'LCI'){

        $sql1 = "UPDATE `qrscan` SET `markIN` = ? WHERE `record_id` = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ss", $remark, $recordId);
        

    }else if ($act == 'ECO'){

        $sql1 = "UPDATE `qrscan` SET `markOUT` = ? WHERE `record_id` = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ss", $remark, $recordId);
        

    }else if ($act == 'NTO'){

        $sql1 = "UPDATE `qrscan` SET `markOUT` = ? WHERE `record_id` = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ss", $remark, $recordId);
        

    }
    


    if ($stmt1->execute()) {

        if($act == 'ABSENT'){
        $data = array(
            "status" => "success",  
            "title" => "Yeay", 
            "act" => $act,
            "spv" => $EmailSPV,
            "message" => "You have successfully submit your reason.  Thank You."
            );
        }else{
        $data = array(
            "status" => "success",  
            "title" => "Yeay", 
            "act" => $act,
            "message" => "You have successfully submit your remark.  Thank You."
        );
    }
        echo json_encode($data);
    } else {
        $data = array(
            "status" => "fail",  
            "title" => "Yeay", 
        );
        echo json_encode($data);
    }
    $stmt1 -> close();
    $conn -> close();
}
?>