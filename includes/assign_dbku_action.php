<?php
session_start();
include 'connection.php';

$zid = $_POST['zid'];
$sz_id = $_POST['sz_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // spv_id and pgs_id are integers or NULL in database
    $spv_id = !empty($_POST['spv_id']) ? (int)$_POST['spv_id'] : null;
    $pgs_id = !empty($_POST['pgs_id']) ? (int)$_POST['pgs_id'] : null;

    // Update contract table where sz_id matches and status is active ('Y')
    $stmt = $conn->prepare("UPDATE contract SET spv_id = ?, pgs_id = ? WHERE sz_id = ? AND c_status = 'Y'");
    $stmt->bind_param("iii", $spv_id, $pgs_id, $sz_id);

    if ($stmt->execute()) {
        $_SESSION['status'] = "success";
        $_SESSION['message'] = "Staf DBKU berjaya ditugaskan.";
    } else {
        $_SESSION['status'] = "error";
        $_SESSION['message'] = "Gagal menugaskan staf DBKU.";
    }
}

header("Location: ../subzone_details.php?zid=" . $zid . "&szid=" . $sz_id);
exit();
?>
