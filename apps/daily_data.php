<?php
// 1. Set header untuk JSON dan Flutter
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// 2. Panggil fail sambungan database MySQLi
require 'conn.php';

// 3. Semak parameter yang dihantar (Sama ada 'cid' ATAU 'dd_id')
$cid = isset($_GET['cid']) && !empty($_GET['cid']) ? $_GET['cid'] : null;
$dd_id = isset($_GET['dd_id']) && !empty($_GET['dd_id']) ? $_GET['dd_id'] : null;

// Jika kedua-dua parameter tiada
if (!$cid && !$dd_id) {
    http_response_code(400);
    echo json_encode(["error" => "Parameter 'cid' atau 'dd_id' diperlukan."]);
    exit();
}

// 4. Bina Query SQL secara dinamik berdasarkan parameter yang ada
if ($dd_id) {
    // Jika carian dari History (guna dd_id), tak perlu sekat guna CURDATE()
    $query = "SELECT a.*, b.* FROM daily_records AS a 
              LEFT JOIN site_instruction AS b ON b.ddId = a.id
              WHERE a.id = ?";
    $param_type = "s"; // Tukar ke "i" jika id dalam database adalah Integer
    $param_value = $dd_id;
} else {
    // Jika carian harian biasa (guna cid), kekalkan filter CURDATE()
    $query = "SELECT a.*, b.* FROM daily_records AS a 
              LEFT JOIN site_instruction AS b ON b.ddId = a.id
              WHERE a.cid = ? AND a.date = CURDATE()";
    $param_type = "s";
    $param_value = $cid;
}

// Sediakan statement (Prepare)
if ($stmt = $conn->prepare($query)) {

    // Ikat parameter dinamik tadi
    $stmt->bind_param($param_type, $param_value);

    // Jalankan query
    $stmt->execute();

    // Ambil hasil carian
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        // Tips: Jika ada nama kolum bertindih antara daily_records & site_instruction,
        // hasil query akan ambil nilai dari table terakhir (site_instruction).
        $records[] = $row;
    }

    // 5. Pulangkan hasil ke Flutter
    if (!empty($records)) {
        http_response_code(200);
        echo json_encode($records);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Tiada rekod dijumpai."]);
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["error" => "Ralat pada penyediaan query: " . $conn->error]);
}

$conn->close();
?>
