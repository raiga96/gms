<?php
include 'includes/connection.php';

if (isset($_GET['cid'])) {
    $cid = $_GET['cid'];

    $stmt = $conn->prepare("SELECT * FROM contractor WHERE cid = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    }

    $stmt->close();
    $conn->close();
}
