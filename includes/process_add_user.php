<?php
require 'connection.php';
gms_require_role(['ADM', 'ROOT']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $user_type = $_POST['user_type'] ?? '';
    $raw_password = $_POST['user_password'] ?? '';

    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $section = $_POST['section'] ?? '';
    $division = $_POST['division'] ?? '';

    // Check if username or email already exists
    $chk = $conn->prepare("SELECT uid FROM users WHERE user_id = ? OR user_email = ?");
    $chk->bind_param("ss", $user_id, $user_email);
    $chk->execute();
    $check_res = $chk->get_result();

    if (!in_array($user_type, ['SPV','PGS','ASO','SO','PGRH','ADM','ROOT','CON'], true) || !filter_var($user_email, FILTER_VALIDATE_EMAIL) || $user_id === '' || strlen($raw_password) < 8) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Data pengguna tidak sah.';
    } elseif ($check_res && $check_res->num_rows > 0) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Nama pengguna atau e-mel telah didaftarkan!';
    } else {
        try {
            $conn->begin_transaction();
            $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (user_type, user_id, user_email, user_password, access_level, status) VALUES (?, ?, ?, ?, 1, 'Y')");
            $ins->bind_param("ssss", $user_type, $user_id, $user_email, $hashed_password);
            if (!$ins->execute()) {
                throw new RuntimeException($ins->error);
            }
            $new_uid = $conn->insert_id;

            $inf = $conn->prepare("INSERT INTO userinfo (uid, fullname, sex, section, division, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $inf->bind_param("isssss", $new_uid, $fullname, $sex, $section, $division, $phone);
            if (!$inf->execute()) {
                throw new RuntimeException($inf->error);
            }
            $conn->commit();
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Pengguna berjaya dicipta!';
        } catch (Throwable $e) {
            if ($conn->in_transaction) {
                $conn->rollback();
            }
            error_log('GMS create user failed: ' . $e->getMessage());
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Gagal mencipta pengguna. Sila semak maklumat dan cuba lagi.';
        }
    }
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Permintaan tidak sah.';
}

header("Location: ../users_management.php");
exit();
?>
