<?php
require 'conn.php';

header('Content-Type: application/json');

// Menerima parameter GET
$con_id = isset($_GET['con_id']) ? $_GET['con_id'] : '';
$session = isset($_GET['session']) ? $_GET['session'] : '';

// Sediakan output default
$data = array();

// Pastikan con_id tak kosong
if (empty($con_id)) {
    $data[] = ["error" => "Contract ID parameter is empty"];
    echo json_encode($data);
    exit;
}

// Bina SQL Query (Menggunakan logik Session)
$sql = "SELECT * FROM daily_records WHERE cid = ?";
$params = [$con_id];
$types = "s"; // s untuk string

// Jika session disediakan, tambah query filter 
if (!empty($session)) {
    $sql .= " AND gc_session = ?";
    $params[] = $session;
    $types .= "s"; // s untuk string tambahan
}

// Penyediaan Statement (Prepared Statement - lebih selamat)
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (count($params) == 2) {
        $stmt->bind_param($types, $params[0], $params[1]);
    } else {
        $stmt->bind_param($types, $params[0]);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        $data[] = ["error" => "No data found"];
    }

    $stmt->close();
} else {
    $data[] = ["error" => "SQL Prepare failed: " . $conn->error];
}

$conn->close();

echo json_encode($data);
