<?php
require 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sz_id = $_POST['sz_id'];
    $area = $_POST['area'];
    $location = $_POST['location'];

    // Basic validation
    if (empty($sz_id) || empty($area) || empty($location)) {
        echo json_encode(['status' => 'error', 'message' => 'Sila isi semua ruangan.']);
        exit;
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO contract_location (sz_id, area, location) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $sz_id, $area, $location);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Lokasi berjaya ditambah!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambah lokasi: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
