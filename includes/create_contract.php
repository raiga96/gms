<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../new_contract.php');
    exit();
}

$zone_id = filter_input(INPUT_POST, 'zid', FILTER_VALIDATE_INT);
$subzone_id = filter_input(INPUT_POST, 'sz_id', FILTER_VALIDATE_INT);
$contractor_id = filter_input(INPUT_POST, 'cid', FILTER_VALIDATE_INT);
$contract_number = trim($_POST['contract_number'] ?? '');
$start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');
$supervisor_1 = trim($_POST['supervisor1_name'] ?? '');
$supervisor1_contact = trim($_POST['supervisor1_contact'] ?? '');
$supervisor_2 = trim($_POST['supervisor2_name'] ?? '');
$supervisor2_contact = trim($_POST['supervisor2_contact'] ?? '');
$fund = trim($_POST['fund'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

$redirect = '../new_contract.php';

if (!$zone_id || !$subzone_id || !$contractor_id || $contract_number === '' || $start_date === '' || $end_date === '') {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Sila lengkapkan semua ruangan wajib.';
    header('Location: ' . $redirect);
    exit();
}

$start_date_obj = DateTime::createFromFormat('!Y-m-d', $start_date);
$end_date_obj = DateTime::createFromFormat('!Y-m-d', $end_date);
$valid_start = $start_date_obj && $start_date_obj->format('Y-m-d') === $start_date;
$valid_end = $end_date_obj && $end_date_obj->format('Y-m-d') === $end_date;

if (!$valid_start || !$valid_end || $start_date > $end_date) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Sila masukkan julat tarikh kontrak yang sah.';
    header('Location: ' . $redirect);
    exit();
}

$stmt = $conn->prepare("SELECT 1 FROM zone z INNER JOIN subzone sz ON sz.zone_id = z.zone_id INNER JOIN contractor c ON c.cid = ? WHERE z.zone_id = ? AND sz.sz_id = ? LIMIT 1");
$stmt->bind_param('iii', $contractor_id, $zone_id, $subzone_id);
$stmt->execute();
$valid_scope = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$valid_scope) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Zon, sub-zon atau kontraktor yang dipilih tidak sah.';
    header('Location: ' . $redirect);
    exit();
}

$stmt = $conn->prepare("SELECT 1 FROM contract WHERE contract_number = ? LIMIT 1");
$stmt->bind_param('s', $contract_number);
$stmt->execute();
$duplicate = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($duplicate) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Nombor kontrak tersebut telah wujud dalam sistem.';
    header('Location: ' . $redirect);
    exit();
}

$add_by = (int)($_SESSION['uid'] ?? 0);

try {
    $conn->begin_transaction();
    $stmt = $conn->prepare("INSERT INTO contract (sz_id, cid, contract_number, start_date, end_date, supervisor_1, supervisor1_contact, supervisor_2, supervisor2_contact, fund, remarks, add_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iisssssssssi', $subzone_id, $contractor_id, $contract_number, $start_date, $end_date, $supervisor_1, $supervisor1_contact, $supervisor_2, $supervisor2_contact, $fund, $remarks, $add_by);
    $stmt->execute();
    $stmt->close();
    $conn->commit();

    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Kontrak baharu berjaya didaftarkan.';
    header('Location: ../contract_list.php');
    exit();
} catch (Throwable $exception) {
    if ($conn->in_transaction) {
        $conn->rollback();
    }

    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Kontrak gagal didaftarkan. Sila cuba lagi.';
    header('Location: ' . $redirect);
    exit();
}
