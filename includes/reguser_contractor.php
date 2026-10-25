<?php

/** @var mysqli $conn */

session_start();
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $user_type = "CON";
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $raw_pwd = $_POST['pwd'] ?? '';

    $redirect_url = "../contractor_details.php?id=" . $id;

    if ($id <= 0 || empty($email) || empty($username) || empty($raw_pwd)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Sila lengkapkan semua butiran akaun.';
        header("Location: " . $redirect_url);
        exit();
    }

    $pwdhash = password_hash($raw_pwd, PASSWORD_DEFAULT);

    // Check if email already exists using prepared statement
    $stmt = $conn->prepare("SELECT uid FROM users WHERE user_email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $stmt->close();
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "E-mel ini telah didaftarkan dalam sistem!";
        header("Location: " . $redirect_url);
        exit();
    }
    $stmt->close();

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("INSERT INTO users (user_type, user_id, user_email, user_password, access_level, status) VALUES (?, ?, ?, ?, '1', 'Y')");
        $stmt->bind_param("ssss", $user_type, $username, $email, $pwdhash);
        $stmt->execute();
        $last_id = $conn->insert_id;
        $stmt->close();

        $stmt1 = $conn->prepare("UPDATE contractor SET uid = ? WHERE cid = ?");
        $stmt1->bind_param("ii", $last_id, $id);
        $stmt1->execute();
        $stmt1->close();

        $role = "owner";
        $stmt2 = $conn->prepare("INSERT INTO contractor_users (contractor_id, user_id, role) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $id, $last_id, $role);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();

        $_SESSION['status'] = 'success';
        $_SESSION['message'] = 'Akaun kontraktor berjaya dicipta!';
    } catch (Throwable $e) {
        if ($conn->in_transaction) {
            $conn->rollback();
        }
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Gagal mendaftarkan akaun: ' . $e->getMessage();
    }

    header("Location: " . $redirect_url);
    exit();
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Tiada data diterima.';
    $id = (int)($_POST['id'] ?? 0);
    header("Location: ../contractor_details.php?id=" . $id);
    exit();
}
