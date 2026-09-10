<?php
session_start();
include 'connection.php';

$zone_id = $_POST['zone_id'];
$subzone_id = $_POST['subzone_id'];
$name = $_POST['subzone'];
$perimeter = $_POST['perimeter'];
$area = $_POST['area'];


mysqli_begin_transaction($conn);

$sql = "SELECT * FROM subzone WHERE sz_id = '$subzone_id'";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$subzone_name = $row['sz_name'];

if ($name == $subzone_name && $perimeter == $row['perimeter'] && $area == $row['area'] && $zone_id == $row['zone_id']) {
    $_SESSION['status'] = 'warning';
    $_SESSION['message'] = 'Tiada perubahan dibuat.';

    header("Location: ../update_sz?szid=" . $subzone_id);
    exit();
}


try {

    $stmt = $conn->prepare("UPDATE subzone SET zone_id = ?, sz_name = ?, perimeter = ?, area = ? WHERE sz_id = ?");
    $stmt->bind_param("sssss", $zone_id, $name, $perimeter, $area, $subzone_id);
    $stmt->execute();


    mysqli_commit($conn);

    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Sub-zon berjaya dikemaskini.';
} catch (Exception $e) {

    mysqli_rollback($conn);

    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Ralat pangkalan data: ' . $e->getMessage();
}

header("Location: ../update_sz?szid=" . $subzone_id);
exit();
