<?php
session_start();
include 'connection.php';

$zid = $_POST['zid'];
$sz_id = $_POST['sz_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $spv_id = !empty($_POST['spv_id']) ? mysqli_real_escape_string($conn, $_POST['spv_id']) : 'NULL';
    $pgs_id = !empty($_POST['pgs_id']) ? mysqli_real_escape_string($conn, $_POST['pgs_id']) : 'NULL';

    // spv_id and pgs_id are integers or NULL in database
    $spv_val = ($spv_id === 'NULL') ? 'NULL' : "'$spv_id'";
    $pgs_val = ($pgs_id === 'NULL') ? 'NULL' : "'$pgs_id'";

    // Update contract table where sz_id matches and status is active ('Y')
    $sql = "UPDATE contract SET spv_id = $spv_val, pgs_id = $pgs_val WHERE sz_id = '$sz_id' AND c_status = 'Y'";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['status'] = "success";
        $_SESSION['message'] = "DBKU Staff assigned successfully.";
    } else {
        $_SESSION['status'] = "error";
        $_SESSION['message'] = "Failed to assign DBKU Staff.";
    }
}

header("Location: ../subzone_details?zid=" . $zid . "&szid=" . $sz_id);
exit();
?>
