<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? OR user_email = ?");
    $stmt->bind_param("ss", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['user_password'])) {
            // Check status
            if ($row['status'] == 'Y') {
                // Success
                session_regenerate_id(true);
                $_SESSION['uid'] = $row['uid'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_type'] = $row['user_type'];
                
                // Get fullname if available
                $info_stmt = $conn->prepare("SELECT fullname FROM userinfo WHERE uid = ?");
                $info_stmt->bind_param("i", $row['uid']);
                $info_stmt->execute();
                $info_res = $info_stmt->get_result();
                if($info_res && $info_res->num_rows > 0) {
                    $info_row = $info_res->fetch_assoc();
                    $_SESSION['fullname'] = $info_row['fullname'];
                } else {
                    $_SESSION['fullname'] = $row['user_id'];
                }
                
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Akaun anda tidak aktif. Sila hubungi pentadbir.";
            }
        } else {
            $_SESSION['login_error'] = "Kata laluan tidak sah.";
        }
    } else {
        $_SESSION['login_error'] = "Pengguna tidak ditemui.";
    }
} else {
    $_SESSION['login_error'] = "Permintaan tidak sah.";
}

header("Location: ../login.php");
exit();
?>
