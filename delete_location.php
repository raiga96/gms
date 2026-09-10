<?php
require 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemui.']);
        exit;
    }

    // Get primary key column dynamically
    $pk_col = 'id';
    $res_pk = $conn->query("SHOW KEYS FROM contract_location WHERE Key_name = 'PRIMARY'");
    if ($res_pk && $res_pk->num_rows > 0) {
        $pk_row = $res_pk->fetch_assoc();
        $pk_col = $pk_row['Column_name'];
    }

    $stmt = $conn->prepare("DELETE FROM contract_location WHERE $pk_col = ?");
    $stmt->bind_param("s", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Berjaya dipadam.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memadam: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Kaedah permintaan tidak sah.']);
}
?>
