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
//$sql = "SELECT * FROM daily_records AS a JOIN site_instruction AS b ON b.ddId = a.id WHERE a.cid = ?";
$sql = "SELECT 
    a.*, b.*,
    COALESCE(sub_dps.total_demerit, 0) AS demerit_point_sebenar
FROM daily_records AS a 
JOIN site_instruction AS b ON b.ddId = a.id 
LEFT JOIN (
    -- SUBQUERY: Kira total demerit dengan padanan KOD + KATEGORI
    SELECT 
        b_sub.si_id AS actual_id,
        SUM(c_sub.demerit_point) AS total_demerit
    FROM site_instruction AS b_sub
    JOIN dps AS c_sub ON 
        -- 1. Padankan Kategori (Sangat Penting!)
        c_sub.dem_cate = b_sub.si_category 
        -- 2. Padankan elemen dalam JSON Array
        AND JSON_CONTAINS(
            b_sub.reference, 
            JSON_QUOTE(CONCAT(c_sub.ref, '(', c_sub.numb, ')')), 
            '$'
        )
    GROUP BY b_sub.si_id
) AS sub_dps ON sub_dps.actual_id = b.si_id
WHERE a.cid = ?";
$params = [$con_id];
$types = "s"; // s untuk string

// Jika session disediakan, tambah query filter 
if (!empty($session)) {
    $sql .= " AND a.gc_session = ?";
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
