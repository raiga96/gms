<?php

require 'connect.php';

$type = $_POST['type'];
$remark = $_POST['remark'];
$spv = $_POST['spv'];
$uid = $_POST['uid'];


// $startdate = strtotime($_POST["StartDate"]);
// $enddate = strtotime($_POST["EndDate"]);

$startdate = strtotime($_POST["StartDate"]);
$enddate = strtotime($_POST["EndDate"]);

// Remark ID generator
$remarkID = 'A' . date("ymd") . rand(1, 999);

$response = [];

$sqlSPV = "SELECT * FROM users AS a JOIN userinfo AS b ON b.uid = a.uid WHERE a.uid = '$uid' -- ADDEXCEPT APPS";
$resSPV = $conn -> query($sqlSPV);
$rowSPV = $resSPV -> fetch_assoc();
$EMAIL = $rowSPV['user_email'];
$NAME = $rowSPV['fullname'];

if (!empty($remark) && $type != '-' && $spv != '-') {
    $sdate = date('Y-m-d', $startdate);
    $edate = date('Y-m-d', $enddate);

    if ($sdate != '1970-01-01' && $edate != '1970-01-01') {
        if ($sdate <= $edate) {
            $sqlX = "SELECT * FROM exception WHERE date >= '$sdate' AND date <= '$edate' AND uid = '$uid' -- ADDEXCEPT APPS";
            $excX = mysqli_query($conn, $sqlX);
            $ada = mysqli_num_rows($excX) > 0;

            if (!$ada) {
                $datediff = "SELECT DATEDIFF('$edate', '$sdate') AS Result";
                $exc = mysqli_query($conn, $datediff);
                $result2 = $exc->fetch_assoc();
                $countdays = $result2['Result'];

                // List of dates to insert
                $dates_to_insert = [$sdate];

                // Generate mid dates if necessary
                for ($i = 1; $i < $countdays; $i++) {
                    $expiredate = "SELECT DATE_ADD('$sdate', INTERVAL $i DAY) as datex";
                    $resultdate = mysqli_query($conn, $expiredate);
                    $result3 = $resultdate->fetch_assoc();
                    $dates_to_insert[] = $result3['datex'];
                }

                // Add end date to the list
                $dates_to_insert[] = $edate;

                // Insert all dates, excluding holidays
                foreach ($dates_to_insert as $date) {
                    $holiday = "SELECT * FROM holiday WHERE holiday_date = '$date'";
                    $result = mysqli_query($conn, $holiday);

                    if (mysqli_num_rows($result) == 0) {
                        $insert = "INSERT INTO exception (uid, date, remarkID, remarktype, remark, remarkApvBy) 
                                   VALUES ('$uid', '$date', '$remarkID', '$type', '$remark', '$spv')";
                        mysqli_query($conn, $insert);
                    }
                }

                // Determine the category based on type
                switch ($type) {
                    case '4':
                        $category = 'On Leave';
                        break;
                    case '5':
                        $category = 'Traveling';
                        break;
                    case '6':
                        $category = 'Others';
                        break;
                    default:
                        $category = 'Unknown';
                }

                // Email to the requester
                $to2 = $EMAIL;
                $subject2 = "Your Attendance Exception for $category on $sdate till $edate has been successfully submitted to your supervisor.";
                $msg2 = "$category Date: $sdate till $edate\nRemark: $remark\n\nThis is a system generated email. Please do not reply to it.\n\nThank You\nRegards\nQREASy\nLand and Survey Department";
                $headers2 = "From: donotreply-QREASy@sarawak.gov.my\r\n";
                mail($to2, $subject2, $msg2, $headers2);

                // Email to the supervisor
                $sql = "SELECT user_email FROM users WHERE uid = '$spv'";
                $resultMail = $conn->query($sql);
                $rowmail = $resultMail->fetch_assoc();
                $EmailSPV = $rowmail['user_email'];

                $to = $EmailSPV;
                $subject = "New Attendance Exception for your approval.";
                $msg = "Requester: $NAME\n$category Date: $sdate till $edate\nRemark: $remark\n\nTo Approve or Reject the $category please click at this link https://jts.sarawak.gov.my/qreasyV2/ApvList?menu=3\n\nThis is a system generated email. Please do not reply to it.\n\nThank You\nRegards\nQREASy\nLand and Survey Department";
                $headers = "From: donotreply-QREASy@sarawak.gov.my\r\n";
                mail($to, $subject, $msg, $headers);


                $response = [
                    'status' => 'success',
                    'message' => 'You have successfully submitted your Attendance Exception. Thank you.'
                ];
            } else {
                
                $response = [
                    'status' => 'error',
                    'message' => 'You have already submitted your Attendance Exception for approval during that period. Thank you.'
                ];
            }
        } else {
            
            $response = [
                'status' => 'error',
                'message' => 'Invalid date. Your start date is later than your end date.'
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Invalid date. Please try again. Thank you."'.$startdate.'"'
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Your record is not complete. Please try again. Thank you.'
    ];
}

// Return the response as JSON
$conn->close();
header('Content-Type: application/json');
echo json_encode($response);

?>
