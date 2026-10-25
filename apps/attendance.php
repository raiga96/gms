<?php
header('Content-Type: application/json');

include 'conn.php';

// Pembantu: jalankan prepared stmt dan pulangkan mysqli_stmt (atau false)
function att_run($conn, $sql, $types, ...$params)
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('attendance prepare: ' . $conn->error);
        return false;
    }
    $stmt->bind_param($types, ...$params);
    return $stmt->execute() ? $stmt : false;
}

$open = "(timeOUT = '00:00:00' OR timeOUT IS NULL OR timeOUT = '')";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid         = (string)($_POST['uid'] ?? '');
    $action      = (string)($_POST['action'] ?? '');
    $lat         = (string)($_POST['lat'] ?? '');
    $lng         = (string)($_POST['lng'] ?? '');
    $location_id = (string)($_POST['location_id'] ?? '');
    $type        = (string)($_POST['type'] ?? ''); // 'C' atau 'O'
    $date        = date('Y-m-d');
    $time        = date('H:i:s');
    $fail        = ["status" => "error", "message" => "Ralat pelayan."];

    if ($action === 'IN') {
        // Semak jika sudah Clock In hari ini
        $check = att_run($conn, "SELECT 1 FROM attendance WHERE uid = ? AND date = ?", 'ss', $uid, $date);
        if (!$check) {
            echo json_encode($fail);
        } elseif ($check->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Anda sudah Clock In hari ini."]);
        } elseif (att_run($conn, "INSERT INTO attendance (uid, date, timeIN, latIN, lngIN, locationIN, locINType) VALUES (?, ?, ?, ?, ?, ?, ?)", 'sssssss', $uid, $date, $time, $lat, $lng, $location_id, $type)) {
            echo json_encode(["status" => "success", "message" => "Clock In Berjaya"]);
        } else {
            echo json_encode($fail);
        }
    } else {
        // Cari rekod Clock In hari ini yang belum di-OUT-kan
        $check = att_run($conn, "SELECT 1 FROM attendance WHERE uid = ? AND date = ? AND $open", 'ss', $uid, $date);
        if (!$check) {
            echo json_encode($fail);
        } elseif ($check->get_result()->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Tiada rekod Clock In aktif ditemui untuk hari ini."]);
        } elseif (att_run($conn, "UPDATE attendance SET timeOUT = ?, latOUT = ?, lngOUT = ?, locationOUT = ?, locOUTType = ? WHERE uid = ? AND date = ? AND $open", 'sssssss', $time, $lat, $lng, $location_id, $type, $uid, $date)) {
            echo json_encode(["status" => "success", "message" => "Clock Out Berjaya"]);
        } else {
            echo json_encode($fail);
        }
    }
} else {
    // GET logic untuk semak status butang
    $uid  = (string)($_GET['uid'] ?? '');
    $date = date('Y-m-d');
    $res  = att_run($conn, "SELECT timeOUT FROM attendance WHERE uid = ? AND date = ?", 'ss', $uid, $date);
    if ($res && ($row = $res->get_result()->fetch_assoc())) {
        if (!empty($row['timeOUT']) && $row['timeOUT'] != '00:00:00') {
            echo json_encode(["status" => "DONE"]);
        } else {
            echo json_encode(["status" => "OUT"]);
        }
    } else {
        echo json_encode(["status" => "IN"]);
    }
}
