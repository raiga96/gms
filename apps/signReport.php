<?php

/**
 * signReport.php
 * Mengemas kini tandatangan berdasarkan Role (CON, SPV, PGS).
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

require 'conn.php';

$ddId = $_POST['ddId'] ?? 0;
$uid  = $_POST['uid'] ?? 0;
$type = $_POST['type'] ?? ''; // CON, SPV, atau PGS

if (empty($ddId) || empty($uid) || empty($type)) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
    exit;
}

try {
    $now = date('Y-m-d H:i:s');

    // Tentukan column mana yang hendak diupdate mengikut Role
    if ($type == 'CON') {
        $sql = "UPDATE daily_records SET contractor_dsign = 'Y', con_by = ?, date_con_sign = ? WHERE id = ?";
    } else if ($type == 'SPV') {
        $sql = "UPDATE daily_records SET supervisor_dsign = 'Y', spv_by = ?, date_spv_sign = ? WHERE id = ?";
    } else if ($type == 'PGS') {
        $sql = "UPDATE daily_records SET pengawas_dsign = 'Y', pgs_by = ?, date_pgs_sign = ? WHERE id = ?";
    } else {
        throw new Exception("Role tidak sah.");
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $uid, $now, $ddId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Signature recorded successfully."]);
    } else {
        throw new Exception("Gagal mengemaskini pangkalan data.");
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
