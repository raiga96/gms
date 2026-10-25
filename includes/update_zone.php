<?php
session_start();
include 'connection.php';

$zone_id = $_POST['zone_id'];
$name = $_POST['zone_name'];


mysqli_begin_transaction($conn);

$sel = $conn->prepare("SELECT * FROM zone WHERE zone_id = ?");
$sel->bind_param("i", $zone_id);
$sel->execute();
$res = $sel->get_result();
$row = $res->fetch_assoc();
$zone_name = $row['zone_name'];

if ($name == $zone_name) {
    $_SESSION['status'] = 'warning';
    $_SESSION['message'] = 'Tiada perubahan dibuat pada nama zon.';

    header("Location: ../update_zone.php?id=" . $zone_id);
    exit();
}


try {

    $stmt = $conn->prepare("UPDATE zone SET zone_name = ? WHERE zone_id = ?");
    $stmt->bind_param("si", $name, $zone_id);
    $stmt->execute();


    mysqli_commit($conn);

    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Zon berjaya dikemaskini.';
} catch (Exception $e) {

    mysqli_rollback($conn);

    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Ralat pangkalan data: ' . $e->getMessage();
}

header("Location: ../update_zone.php?id=" . $zone_id);
exit();
