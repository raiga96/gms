<?php
require 'connect.php';

// Encryption Key and Initialization Vector (IV)
define('ENCRYPTION_KEY', '2e54d1151eaf55af8d889ec1425fdc85'); // Replace with your own key
define('IV', '5fbc84cbabd58453'); // Replace with your own IV

function fetchData($conn, $uid) {
    // Fetch user data
    $userSql = "SELECT u.*, ui.*, b.BSU_name, b.BSU, b.BSU_id, d.*, a.app_version, a.app_link_apple, a.app_link_android, al.lvl_name
                FROM users AS u
                JOIN userinfo AS ui ON ui.uid = u.uid
                JOIN brasecunit AS b ON b.BSU_id = ui.section
                JOIN division AS d ON d.DIV_ID = ui.division
                JOIN app_info AS a ON a.app_status = 'Y'
                JOIN access_level AS al ON al.acc_lvl = u.access_level
                WHERE u.uid = ?";
    
    $userStmt = $conn->prepare($userSql);
    $userStmt->bind_param("s", $uid);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows > 0) {
        $userData = $userResult->fetch_assoc();
        $userStmt->close();

        // Fetch additional data
        $qrSql = "SELECT * FROM users WHERE user_email = ? OR uid = ? -- OPT API APPS";
        $qrStmt = $conn->prepare($qrSql);
        $qrStmt->bind_param("ss", $userData['user_email'], $uid);
        $qrStmt->execute();
        $qrResult = $qrStmt->get_result();

        if ($qrResult->num_rows > 0) {
            $qrData = $qrResult->fetch_assoc();
            $qrStmt->close();

            $todayDate = date('Y-m-d');

            $qrScanSql = "SELECT * FROM qrscan WHERE uid = ? AND date = ? -- OPT API APPS";
            $qrScanStmt = $conn->prepare($qrScanSql);
            $qrScanStmt->bind_param("ss", $qrData['uid'], $todayDate);
            $qrScanStmt->execute();
            $qrScanResult = $qrScanStmt->get_result();
            
            if ($qrScanResult->num_rows > 0) {
                $qrScanData = $qrScanResult->fetch_assoc();
                $timeIn = empty($qrScanData['record_id']) ? $todayDate.' 00:00:00' : $qrScanData['date'].' '.$qrScanData['timeIN'];
                $timeOut = empty($qrScanData['record_id']) ? $todayDate.' 00:00:00' : $qrScanData['date'].' '.$qrScanData['timeOUT'];
            } else {
                $timeIn = $todayDate.' 00:00:00';
                $timeOut = $todayDate.' 00:00:00';
            }
        }

        // Fetch profile image data
        $profileImgSql = "SELECT * FROM profile_image WHERE UID = ? -- OPT API APPS";
        $profileImgStmt = $conn->prepare($profileImgSql);
        $profileImgStmt->bind_param("s", $uid);
        $profileImgStmt->execute();
        $profileImgResult = $profileImgStmt->get_result();
        
        if ($profileImgResult->num_rows > 0) {
            $row1 = $profileImgResult->fetch_assoc();
        }

        // Fetch holiday data
        $holidaySql = "SELECT * FROM holiday WHERE holiday_date = ? -- OPT API APPS";
        $holidayStmt = $conn->prepare($holidaySql);
        $holidayStmt->bind_param("s", $todayDate);
        $holidayStmt->execute();
        $holidayResult = $holidayStmt->get_result();
        
        if ($holidayResult->num_rows > 0) {
            $row2 = $holidayResult->fetch_assoc();
        }

        // Fetch user log data
        $userLogSql = "SELECT * FROM user_log WHERE uid = ? ORDER BY log_id DESC -- OPT API APPS";
        $userLogStmt = $conn->prepare($userLogSql);
        $userLogStmt->bind_param("s", $userData['uid']);
        $userLogStmt->execute();
        $userLogResult = $userLogStmt->get_result();
        
        if ($userLogResult->num_rows > 0) {
            $row3 = $userLogResult->fetch_assoc();
        }

        // Fetch app info data
        $appInfoSql = "SELECT * FROM app_info WHERE app_status = 'Y' -- OPT API APPS";
        $appInfoResult = $conn->query($appInfoSql);
        
        if ($appInfoResult->num_rows > 0) {
            $row4 = $appInfoResult->fetch_assoc();
        }

        // Fetch access level data
        $accessLevelSql = "SELECT * FROM access_level WHERE acc_lvl = ? -- OPT API APPS";
        $accessLevelStmt = $conn->prepare($accessLevelSql);
        $accessLevelStmt->bind_param("s", $userData['access_level']);
        $accessLevelStmt->execute();
        $accessLevelResult = $accessLevelStmt->get_result();
        
        if ($accessLevelResult->num_rows > 0) {
            $row5 = $accessLevelResult->fetch_assoc();
        }

        // Fetch other data...
        // Add your remaining SQL queries and result fetching logic here
        
        // Construct and return the final data array...
        $data = [
            "uid" => $userData['uid'],
            "name" => $userData['fullname'],
            "email" => $userData['user_email'],
            "access_level" => $row5['lvl_name'],
            "BSU_name" => $userData['BSU_name'],
            "BSU" => $userData['BSU'],
            "bsu_id" => $userData['BSU_id'],
            "div" => $userData['DIV_ID'],
            "phone" => $userData['phone'],
            "division" => $userData['DIV_NAME'],
            "img_path" => isset($row1['FILE_PATH']) ? $row1['FILE_PATH'] : '', // Assuming FILE_PATH column exists in profile_image table
            "timeIn" => date('h:i a', strtotime($timeIn)),
            "timeIn1" => date('Hi', strtotime($timeIn)),
            "timeOut" => date('h:i a', strtotime($timeOut)),
            "timeOut1" => date('Hi', strtotime($timeOut)),
            "record_id" => isset($qrScanData['record_id']) ? $qrScanData['record_id'] : '', // Assuming record_id exists in qrscan table
            "day" => isset($row2['desc']) ? $row2['desc'] : '', // Assuming desc column exists in holiday table
            "logdate" => isset($row3['CREATED_ON']) ? date("d M Y, h:i a", strtotime($row3['CREATED_ON'])) : '', // Assuming CREATED_ON column exists in user_log table
            "app_version" => isset($row4['app_version']) ? $row4['app_version'] : '', // Assuming app_version column exists in app_info table
            "ios_link" => isset($row4['app_link_apple']) ? $row4['app_link_apple'] : '', // Assuming app_link_apple column exists in app_info table
            "android_link" => isset($row4['app_link_android']) ? $row4['app_link_android'] : '', // Assuming app_link_android column exists in app_info table
        ];

        // Convert the data to JSON format
        $jsonData = json_encode($data);

        // Encryption
        $encryptedData = openssl_encrypt($jsonData, 'aes-256-cbc', ENCRYPTION_KEY, 0, IV);

        // Set the Content-Type header to indicate that the response contains JSON data
        header('Content-Type: application/json');

        // Output the encrypted JSON data
        print_r($encryptedData);
        //print_r($jsonData);
    } else {
        // No data found for the provided UID
        echo json_encode(["error" => "No data found for the provided UID"]);
    }

    $conn->close();
}




// Assume $uid is passed as a query parameter
$uid = $_GET['uid'];

// Fetch data using the provided UID
fetchData($conn, $uid);
?>