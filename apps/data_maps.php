<?php

require 'conn.php';

$mid = $_GET['mid'];

$sql = "SELECT 
    a.*,          -- Maklumat Kontrak
    b.*,          -- Maklumat Kontraktor
    c.*,          -- Maklumat Subzone
    d.*,          -- Maklumat Zone
    e.*,          -- Rekod Harian Hari Ini
    spv.fullname AS nama_supervisor,  -- Ambil nama supervisor
    spv.phone AS spv_phone,
    pgs.fullname AS nama_pengawas,    -- Ambil nama pengawas
    pgs.phone AS pgs_phone
FROM contract AS a 
JOIN contractor AS b ON b.cid = a.cid 
JOIN subzone AS c ON c.sz_id = a.sz_id 
JOIN zone AS d ON d.zone_id = c.zone_id 
LEFT JOIN daily_records AS e ON (e.cid = a.contract_id AND e.date = CURDATE()) 
-- JOIN pertama untuk Supervisor
LEFT JOIN userinfo AS spv ON spv.uid = a.spv_id  
-- JOIN kedua untuk Pengawas
LEFT JOIN userinfo AS pgs ON pgs.uid = a.pgs_id  
WHERE c.sz_id = '$mid'";
// WHERE a.contract_id = '$mid'";

$result = $conn->query($sql);

// Fetch data and store it in an array
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    //echo json_encode(["error" => "Search parameter is empty"]);
    $data = array();
    $data[] = ["error" => "No data found"];
}

// Close the database connection
$conn->close();

// Encode the data in JSON format and send it as the response
header('Content-Type: application/json');
echo json_encode($data);
