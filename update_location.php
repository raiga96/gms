<?php
require 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $area = $_POST['area'];
    $location = $_POST['location'];

    if (empty($id) || empty($area) || empty($location)) {
        echo json_encode(['status' => 'error', 'message' => 'Sila isi semua ruangan.']);
        exit;
    }

    // Get primary key column dynamically
    $pk_col = 'id';
    $res_pk = $conn->query("SHOW KEYS FROM contract_location WHERE Key_name = 'PRIMARY'");
    if ($res_pk && $res_pk->num_rows > 0) {
        $pk_row = $res_pk->fetch_assoc();
        $pk_col = $pk_row['Column_name'];
    }

    $stmt = $conn->prepare("UPDATE contract_location SET area = ?, location = ? WHERE $pk_col = ?");
    $stmt->bind_param("sss", $area, $location, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Berjaya dikemaskini.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal dikemaskini: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
