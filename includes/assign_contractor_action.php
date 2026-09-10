<?php
session_start();
include 'connection.php'; // tukar ikut nama file connection anda
$zid                   = $_POST['zid'];
$sz_id                 = $_POST['sz_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // =========================
    // GET FORM DATA
    // =========================
    $zid                   = mysqli_real_escape_string($conn, $_POST['zid']);
    $sz_id                 = mysqli_real_escape_string($conn, $_POST['sz_id']);
    $cid                   = mysqli_real_escape_string($conn, $_POST['cid']);

    $contract_number       = mysqli_real_escape_string($conn, $_POST['contract_number']);
    $start_date            = date("Y-m-d", strtotime($_POST['start_date']));
    $end_date              = date("Y-m-d", strtotime($_POST['end_date']));

    $supervisor_1          = mysqli_real_escape_string($conn, $_POST['supervisor1_name']);
    $supervisor1_contact   = mysqli_real_escape_string($conn, $_POST['supervisor1_contact']);

    $supervisor_2          = mysqli_real_escape_string($conn, $_POST['supervisor2_name']);
    $supervisor2_contact   = mysqli_real_escape_string($conn, $_POST['supervisor2_contact']);

    $fund                  = mysqli_real_escape_string($conn, $_POST['fund']);
    $remarks               = mysqli_real_escape_string($conn, $_POST['remarks']);

    // session user id
    $add_by                = "1"; // Gantikan dengan session user id sebenar, contoh: $_SESSION['user_id']

    // current datetime

    // =========================
    // INSERT QUERY
    // =========================
    $sql = "INSERT INTO contract (
                sz_id,
                cid,
                contract_number,
                start_date,
                end_date,
                supervisor_1,
                supervisor1_contact,
                supervisor_2,
                supervisor2_contact,
                fund,
                remarks,
                add_by
            ) VALUES (
                '$sz_id',
                '$cid',
                '$contract_number',
                '$start_date',
                '$end_date',
                '$supervisor_1',
                '$supervisor1_contact',
                '$supervisor_2',
                '$supervisor2_contact',
                '$fund',
                '$remarks',
                '$add_by'
            )";

    // =========================
    // EXECUTE
    // =========================
    if (mysqli_query($conn, $sql)) {
        $_SESSION['status'] = "success";
        $_SESSION['message'] = "Kontraktor berjaya ditugaskan.";

        header("Location: ../subzone_details?zid=" . $zid . "&szid=" . $sz_id);
        exit();
    } else {

        $_SESSION['status'] = "error";
        $_SESSION['message'] = "Gagal menugaskan kontraktor.";

        echo "Ralat pangkalan data: " . mysqli_error($conn);
    }
} else {

    header("Location: ../subzone_details?zid=" . $zid . "&szid=" . $sz_id);
    exit();
}
