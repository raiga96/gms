<?php
header('Content-Type: application/json');
// include 'db_connection.php'; 

include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid         = $_POST['uid'];
    $action      = $_POST['action']; 
    $lat         = $_POST['lat'];
    $lng         = $_POST['lng'];
    $location_id = $_POST['location_id'];
    $type        = $_POST['type']; // 'C' atau 'O'
    $date        = date('Y-m-d');
    $time        = date('H:i:s');

    if ($action === 'IN') {
        // Semak jika sudah Clock In hari ini
        $check = mysqli_query($conn, "SELECT * FROM attendance WHERE uid = '$uid' AND date = '$date'");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(["status" => "error", "message" => "Anda sudah Clock In hari ini."]);
        } else {
            $sql = "INSERT INTO attendance (uid, date, timeIN, latIN, lngIN, locationIN, locINType) 
                    VALUES ('$uid', '$date', '$time', '$lat', '$lng', '$location_id', '$type')";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(["status" => "success", "message" => "Clock In Berjaya"]);
            } else {
                echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
            }
        }
    } else {
        // Cari rekod Clock In hari ini yang belum di-OUT-kan
        $check = mysqli_query($conn, "SELECT * FROM attendance WHERE uid = '$uid' AND date = '$date' AND (timeOUT = '00:00:00' OR timeOUT IS NULL OR timeOUT = '')");
        if (mysqli_num_rows($check) == 0) {
            echo json_encode(["status" => "error", "message" => "Tiada rekod Clock In aktif ditemui untuk hari ini."]);
        } else {
            $sql = "UPDATE attendance SET timeOUT = '$time', latOUT = '$lat', lngOUT = '$lng', locationOUT = '$location_id', locOUTType = '$type' 
                    WHERE uid = '$uid' AND date = '$date' AND (timeOUT = '00:00:00' OR timeOUT IS NULL OR timeOUT = '')";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(["status" => "success", "message" => "Clock Out Berjaya"]);
            } else {
                echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
            }
        }
    }
} else {
    // GET logic untuk semak status butang
    $uid  = $_GET['uid'];
    $date = date('Y-m-d');
    $res  = mysqli_query($conn, "SELECT * FROM attendance WHERE uid = '$uid' AND date = '$date'");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['timeOUT']) && $row['timeOUT'] != '00:00:00') {
            echo json_encode(["status" => "DONE"]);
        } else {
            echo json_encode(["status" => "OUT"]);
        }
    } else {
        echo json_encode(["status" => "IN"]);
    }
}
?>