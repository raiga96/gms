<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escape users table inputs
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
    $raw_password = $_POST['user_password'];
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    // Escape userinfo table inputs
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $sex = mysqli_real_escape_string($conn, $_POST['sex'] ?? '');
    $section = mysqli_real_escape_string($conn, $_POST['section'] ?? '');
    $division = mysqli_real_escape_string($conn, $_POST['division'] ?? '');

    // Check if username or email already exists
    $check_sql = "SELECT * FROM users WHERE user_id = '$user_id' OR user_email = '$user_email'";
    $check_res = $conn->query($check_sql);

    if ($check_res && $check_res->num_rows > 0) {
        $_SESSION['status'] = 'error';
                $_SESSION['message'] = 'Nama pengguna atau e-mel telah didaftarkan!';
    } else {
        // Insert into users
        $insert_user_sql = "INSERT INTO users (user_type, user_id, user_email, user_password, access_level, status) 
                            VALUES ('$user_type', '$user_id', '$user_email', '$hashed_password', 1, 'Y')";
        
        if ($conn->query($insert_user_sql)) {
            $new_uid = $conn->insert_id;

            // Insert into userinfo
            $insert_info_sql = "INSERT INTO userinfo (uid, fullname, sex, section, division, phone) 
                                VALUES ('$new_uid', '$fullname', '$sex', '$section', '$division', '$phone')";
            
            if ($conn->query($insert_info_sql)) {
                $_SESSION['status'] = 'success';
                $_SESSION['message'] = 'Pengguna berjaya dicipta!';
            } else {
                $_SESSION['status'] = 'error';
                $_SESSION['message'] = 'Pengguna dicipta tetapi maklumat pengguna gagal disimpan: ' . $conn->error;
            }
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Gagal mencipta pengguna: ' . $conn->error;
        }
    }
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Permintaan tidak sah.';
}

header("Location: ../users_management.php");
exit();
?>
