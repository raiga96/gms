<?php
require 'conn.php';

// Pastikan parameter UID ada
if (!isset($_GET['uid']) || empty($_GET['uid'])) {
    header('Content-Type: application/json');
    echo json_encode([["error" => "UID parameter is missing"]]);
    exit;
}

$uid = $_GET['uid'];
$encryptionKey = "2e54d1151eaf55af8d889ec1425fdc85";
$iv = "5fbc84cbabd58453";

// 1. Ambil tipe user menggunakan Prepared Statement
$stmt = $conn->prepare("SELECT user_type FROM users WHERE uid = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();
$tp = $result->fetch_assoc();

if (!$tp) {
    header('Content-Type: application/json');
    echo json_encode([["error" => "User tidak terdaftar"]]);
    exit;
}

$type = $tp['user_type'];

$data = array();

// 2. Tentukan query berdasarkan tipe user
if ($type == 'CON') {
    // KEKAL: Query asal anda langsung tidak disentuh
    $stmt = $conn->prepare("SELECT * FROM contract AS a 
                            JOIN contractor AS b ON b.cid = a.cid 
                            JOIN subzone AS c ON c.sz_id = a.sz_id 
                            JOIN zone AS d ON d.zone_id = c.zone_id 
                            WHERE b.uid = ?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result_data = $stmt->get_result();

    // TAMBAHAN: $stmt1 untuk dapatkan data pengiraan progress, total tempat & pekerja
    $stmt1 = $conn->prepare("SELECT 
        a.contract_id,
        a.sz_id AS target_sz_id, -- Alias selamat untuk Flutter
        COUNT(f.locId) AS total_tempat,
        (COUNT(f.locId) * 2) AS total_kerja_wajib,
        SUM(CASE WHEN e.gc_progress IN (2, 3) THEN 1 ELSE 0 END) AS gc_siap,
        SUM(CASE WHEN e.dc_progress IN (2, 3) THEN 1 ELSE 0 END) AS dc_siap,
        (
            SUM(CASE WHEN e.gc_progress IN (2, 3) THEN 1 ELSE 0 END) + 
            SUM(CASE WHEN e.dc_progress IN (2, 3) THEN 1 ELSE 0 END)
        ) AS total_kerja_siap,
        ROUND(
            (
                (SUM(CASE WHEN e.gc_progress IN (2, 3) THEN 1 ELSE 0 END) + 
                 SUM(CASE WHEN e.dc_progress IN (2, 3) THEN 1 ELSE 0 END)) 
                / NULLIF(COUNT(f.locId) * 2, 0)
            ) * 100, 
            2
        ) AS progress_sesi_peratusan,
        IFNULL(MAX(g.gc_workers), 0) AS max_gc_workers,
        IFNULL(MAX(g.dc_workers), 0) AS max_dc_workers,
        (IFNULL(MAX(g.gc_workers), 0) + IFNULL(MAX(g.dc_workers), 0)) AS total_max_workers
        FROM contract AS a 
        JOIN contractor AS b ON b.cid = a.cid 
        LEFT JOIN area_daily_records AS e ON e.cid = a.contract_id 
        LEFT JOIN contract_location AS f ON f.locId = e.area_id 
        LEFT JOIN daily_records AS g ON g.id = e.dr_id 
        WHERE b.uid = ?
        GROUP BY a.contract_id");
    $stmt1->bind_param("s", $uid);
    $stmt1->execute();
    $result_data1 = $stmt1->get_result();

    // Masukkan data $stmt1 ke dalam map array berdasarkan contract_id untuk mudahkan mapping
    $progress_map = array();
    while ($row1 = $result_data1->fetch_assoc()) {
        $progress_map[$row1['contract_id']] = $row1;
    }

    // 4. Looping untuk gabungkan query asal ($stmt) + query progress ($stmt1)
    if ($result_data->num_rows > 0) {
        while ($row = $result_data->fetch_assoc()) {
            $c_id = $row['contract_id'];
            
            // Jika ada data progress yang sepadan dengan contract_id ini, gabungkan sekali
            if (isset($progress_map[$c_id])) {
                $row = array_merge($row, $progress_map[$c_id]);
            }
            
            $data[] = $row;
        }
    } else {
        $data[] = ["error" => "No data found"];
    }

} else {
    // KEKAL: Bagian ELSE untuk SPV / PGS (Query asal anda)
    $stmt = $conn->prepare("SELECT *, e.name AS con_name, f.id AS ddID
        FROM contract AS a 
        LEFT JOIN users AS b ON (b.uid = a.spv_id OR b.uid = a.pgs_id) 
        JOIN subzone AS c ON c.sz_id = a.sz_id 
        JOIN zone AS d ON d.zone_id = c.zone_id 
        LEFT JOIN contractor AS e ON e.cid = a.cid
        LEFT JOIN daily_records AS f ON (f.cid = a.contract_id AND f.date = CURDATE()) 
        WHERE b.uid = ? ORDER BY a.contract_number");
    
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result_data = $stmt->get_result();

    if ($result_data->num_rows > 0) {
        while ($row = $result_data->fetch_assoc()) {
            // Untuk SPV/PGS, letak key target_sz_id secara manual supaya Flutter tak error membaca key yang sama
            $row['target_sz_id'] = $row['sz_id']; 
            $data[] = $row;
        }
    } else {
        $data[] = ["error" => "No data found"];
    }
}

// 5. Set Header JSON
header('Content-Type: application/json');

// 6. Output data menggunakan echo json_encode
echo json_encode($data);

$conn->close();