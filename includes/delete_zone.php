<?php
include 'connection.php';

// RBAC: Hanya Pentadbir (ADM / ROOT) dibenarkan memadam Zon
if (function_exists('gms_require_role')) {
    gms_require_role(['ADM', 'ROOT']);
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo "ID tidak sah";
    exit();
}

// Semak jika terdapat kontrak aktif di bawah zon ini
$stmtCheck = $conn->prepare("
    SELECT COUNT(*) AS active_contracts 
    FROM contract ct 
    JOIN subzone sz ON sz.sz_id = ct.sz_id 
    WHERE sz.zone_id = ? AND ct.c_status = 'Y'
");
$stmtCheck->bind_param("i", $id);
$stmtCheck->execute();
$rowCheck = $stmtCheck->get_result()->fetch_assoc();
$stmtCheck->close();

if (!empty($rowCheck['active_contracts']) && (int)$rowCheck['active_contracts'] > 0) {
    http_response_code(409);
    echo "Tidak boleh dipadam: Terdapat " . (int)$rowCheck['active_contracts'] . " kontrak aktif di bawah zon ini.";
    exit();
}

$conn->begin_transaction();
try {
    // Padam subzon di bawah zon
    $stmt1 = $conn->prepare("DELETE FROM subzone WHERE zone_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // Padam fail KMZ berkaitan
    $stmtKmz = $conn->prepare("DELETE FROM kmz_file WHERE zone_id = ?");
    $stmtKmz->bind_param("i", $id);
    $stmtKmz->execute();
    $stmtKmz->close();

    // Padam zon
    $stmt2 = $conn->prepare("DELETE FROM zone WHERE zone_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();
    echo "success";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Ralat pangkalan data: " . $e->getMessage();
}
