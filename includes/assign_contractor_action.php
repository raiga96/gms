<?php
include 'connection.php';

$zid   = (int)($_POST['zid'] ?? 0);
$sz_id = (int)($_POST['sz_id'] ?? 0);
$back  = "Location: ../subzone_details.php?zid=" . $zid . "&szid=" . $sz_id;

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header($back);
    exit();
}

$cid                 = (int)($_POST['cid'] ?? 0);
$contract_number     = $_POST['contract_number'] ?? '';
$start_date          = date("Y-m-d", strtotime($_POST['start_date'] ?? ''));
$end_date            = date("Y-m-d", strtotime($_POST['end_date'] ?? ''));
$supervisor_1        = $_POST['supervisor1_name'] ?? '';
$supervisor1_contact = $_POST['supervisor1_contact'] ?? '';
$supervisor_2        = $_POST['supervisor2_name'] ?? '';
$supervisor2_contact = $_POST['supervisor2_contact'] ?? '';
$fund                = $_POST['fund'] ?? '';
$remarks             = $_POST['remarks'] ?? '';
$add_by              = (int)($_SESSION['uid'] ?? 0);

$stmt = $conn->prepare("INSERT INTO contract (
        sz_id, cid, contract_number, start_date, end_date,
        supervisor_1, supervisor1_contact, supervisor_2, supervisor2_contact,
        fund, remarks, add_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssssssssi", $sz_id, $cid, $contract_number, $start_date, $end_date,
    $supervisor_1, $supervisor1_contact, $supervisor_2, $supervisor2_contact,
    $fund, $remarks, $add_by);

if ($stmt->execute()) {
    $_SESSION['status'] = "success";
    $_SESSION['message'] = "Kontraktor berjaya ditugaskan.";
} else {
    error_log('GMS assign_contractor: ' . $stmt->error);
    $_SESSION['status'] = "error";
    $_SESSION['message'] = "Gagal menugaskan kontraktor.";
}

header($back);
exit();
