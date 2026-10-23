<?php
// 1. Set header untuk JSON dan Flutter
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// 2. Panggil fail sambungan database MySQLi korang
require 'conn.php';
// Nota: Sila pastikan nama pembolehubah sambungan dalam conn.php adalah $conn

// 3. Semak jika 'cid' dihantar dari Flutter
if (!isset($_GET['cid']) || empty($_GET['cid'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parameter 'cid' diperlukan."]);
    exit();
}

$cid = $_GET['cid'];

// 4. Query SQL guna cara MySQLi (Guna tanda '?' untuk keselamatan daripada SQL Injection)
$query = "SELECT 
            * 
          FROM daily_records AS a LEFT JOIN site_instruction AS b ON b.ddId = a.id AND a.date = CURDATE()
          WHERE a.cid = ? AND DATE(a.date) = CURDATE()";

// Sediakan statement (Prepare)
if ($stmt = $conn->prepare($query)) {

    // Ikat parameter 'cid' (s = string, i = integer). Kita anggap cid ni string/text.
    $stmt->bind_param("s", $cid);

    // Jalankan query
    $stmt->execute();

    // Ambil hasil carian
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    // 5. Pulangkan hasil ke Flutter
    if (!empty($records)) {
        http_response_code(200);
        echo json_encode($records);
    } else {
        echo json_encode(["message" => "Tiada rekod dijumpai untuk cid: " . $cid]);
    }

    // Tutup statement
    $stmt->close();
} else {
    // Jika query ada masalah tulisan/syntax
    http_response_code(500);
    echo json_encode(["error" => "Ralat pada penyediaan query: " . $conn->error]);
}

// Tutup sambungan database
$conn->close();
