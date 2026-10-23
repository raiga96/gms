<?php
header('Content-Type: application/json');

/**
 * overall_si.php
 * Digunakan untuk mengambil semua rekod Site Instruction bagi satu con_id (ID Kontrak)
 */

// Konfigurasi Pangkalan Data
include 'conn.php';

// Semak sambungan
if ($conn->connect_error) {
    die(json_encode([["error" => "Connection failed: " . $conn->connect_error]]));
}

// Ambil con_id daripada parameter GET
$con_id = isset($_GET['con_id']) ? $_GET['con_id'] : '';

if (empty($con_id)) {
    echo json_encode([["error" => "Contract ID is required"]]);
    exit;
}

/**
 * QUERY PENJELASAN:
 * Mengambil semua kolum daripada jadual site_instruction 
 * (atau nama jadual SI anda yang mengandungi con_id)
 * Disusun mengikut tarikh terbaru di atas.
 */
// $sql = "SELECT * FROM site_instruction WHERE contract_id = ? ORDER BY date_issued DESC, num_si DESC";
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
WHERE b.contract_id = ? 
ORDER BY b.date_issued DESC, b.num_si DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $con_id);
$stmt->execute();
$result = $stmt->get_result();

$si_list = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        /**
         * Memastikan jenis data adalah konsisten untuk Flutter
         * demerit_point_sebenar dihantar sebagai string/int (akan di-parse di Flutter)
         * reference dihantar sebagai JSON string (akan di-decode di Flutter)
         */
        $si_list[] = $row;
    }
    echo json_encode($si_list);
} else {
    // Memulangkan format ralat yang dikenali oleh Flutter buildNoRecord()
    echo json_encode([["error" => "No data found"]]);
}

$stmt->close();
$conn->close();
?>