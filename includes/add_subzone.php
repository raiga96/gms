<?php
session_start();
include 'connection.php';

$zone_id = $_POST['zone_id'];
$subzone = $_POST['subzone'];
$perimeter = $_POST['perimeter'] ?? 0;
$area = $_POST['area'] ?? 0;


mysqli_begin_transaction($conn);

if (empty($subzone)) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Empty Subzone name';

    header("Location: ../add_subzone?id=" . $zone_id);
    exit();
}


try {

    $stmt = $conn->prepare("INSERT INTO subzone (zone_id, sz_name, perimeter, area) VALUES (?, ?,?,?)");
    $stmt->bind_param("isss", $zone_id, $subzone, $perimeter, $area);
    $stmt->execute();


    mysqli_commit($conn);

    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Sub-Zone added successfully';
} catch (Exception $e) {

    mysqli_rollback($conn);

    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Database error: ' . $e->getMessage();
}

header("Location: ../zone_details?id=" . $zone_id);
exit();
