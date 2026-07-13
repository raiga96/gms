<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE user_id = '$user_id' OR user_email = '$user_id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['user_password'])) {
            // Check status
            if ($row['status'] == 'Y') {
                // Success
                $_SESSION['uid'] = $row['uid'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_type'] = $row['user_type'];
                
                // Get fullname if available
                $info_sql = "SELECT fullname FROM userinfo WHERE uid = '".$row['uid']."'";
                $info_res = $conn->query($info_sql);
                if($info_res && $info_res->num_rows > 0) {
                    $info_row = $info_res->fetch_assoc();
                    $_SESSION['fullname'] = $info_row['fullname'];
                } else {
                    $_SESSION['fullname'] = $row['user_id'];
                }
                
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Your account is not active. Please contact administrator.";
            }
        } else {
            $_SESSION['login_error'] = "Invalid password.";
        }
    } else {
        $_SESSION['login_error'] = "User not found.";
    }
} else {
    $_SESSION['login_error'] = "Invalid request.";
}

header("Location: ../login.php");
exit();
?>
