<?php require_once __DIR__ . '/_q.php';
/**
 * getSIReferences.php
 * Mengambil senarai rujukan standard untuk Site Instruction.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$cate = isset($_GET['cate']) ? $_GET['cate'] : '';
// 1. Panggil fail sambungan MySQLi anda
require 'conn.php';

// 2. Query untuk mengambil data rujukan
// Saya andaikan nama table adalah 'si_references' dan column adalah 'reference_name'
// Anda boleh tukar nama table mengikut pangkalan data anda.
$sql = "SELECT * FROM dps WHERE dem_cate = ?";

$result = gms_q($conn, $sql, "s", [$cate]);

$references = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $references[] = [
            "id" => $row['dem_id'],
            "ref" => $row['ref'],
            "numb" => $row['numb'],
            "aspect" => $row['aspect'],
            "desc" => $row['description'],
            "point" => $row['demerit_point']
        ];
    }
}

// 3. Pulangkan hasil dalam format JSON
echo json_encode($references);

// Tutup sambungan
$conn->close();
?>