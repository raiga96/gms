<?php
include 'connection.php';

// RBAC: Hanya Pentadbir (ADM / ROOT) dibenarkan memadam Sub-Zon
if (function_exists('gms_require_role')) {
    gms_require_role(['ADM', 'ROOT']);
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo "ID tidak sah";
    exit();
}

// Semak jika terdapat kontrak aktif yang terikat dengan sub-zon ini
$stmtCheck = $conn->prepare("SELECT COUNT(*) AS active_contracts FROM contract WHERE sz_id = ? AND c_status = 'Y'");
$stmtCheck->bind_param("i", $id);
$stmtCheck->execute();
$rowCheck = $stmtCheck->get_result()->fetch_assoc();
$stmtCheck->close();

if (!empty($rowCheck['active_contracts']) && (int)$rowCheck['active_contracts'] > 0) {
    http_response_code(409);
    echo "Tidak boleh dipadam: Terdapat " . (int)$rowCheck['active_contracts'] . " kontrak aktif yang menggunakan sub-zon ini.";
    exit();
}

$conn->begin_transaction();
try {
    // Padam rujukan fail KMZ bagi subzon ini
    $stmtKmz = $conn->prepare("DELETE FROM kmz_file WHERE sz_id = ?");
    $stmtKmz->bind_param("i", $id);
    $stmtKmz->execute();
    $stmtKmz->close();

    // Padam rekod subzon
    $stmt = $conn->prepare("DELETE FROM subzone WHERE sz_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo "success";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Ralat pangkalan data: " . $e->getMessage();
}
