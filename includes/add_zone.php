<?php
session_start();
include 'connection.php';

$zone = $_POST['zone'];


mysqli_begin_transaction($conn);

if (empty($zone)) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Zone Name empty';

    header("Location: ../zone");
    exit();
}

try {

    $stmt = $conn->prepare("INSERT INTO zone (zone_name) VALUES (?)");
    $stmt->bind_param("s", $zone);
    $stmt->execute();


    mysqli_commit($conn);

    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Zone added successfully';
} catch (Exception $e) {

    mysqli_rollback($conn);

    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Database error: ' . $e->getMessage();
}

header("Location: ../zone");
exit();
