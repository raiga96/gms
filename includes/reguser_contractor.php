<?php

/** @var mysqli $conn */

//Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include("connection.php");

// Initialize response array
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $user_type = mysqli_real_escape_string($conn, "CON");
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $pwdpost = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['pwd']));
    $pwdhash = password_hash($pwdpost, PASSWORD_DEFAULT);

    // Check if user already exists
    $usrquery = mysqli_query($conn, "SELECT * FROM users WHERE user_email='$email'");
    $row = mysqli_fetch_array($usrquery);

    if (!empty($row)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "E-mel ini telah didaftarkan dalam sistem!";
    } else {
        $query = "INSERT INTO users (user_type, user_id, user_email, user_password, access_level, status) 
        VALUES ('$user_type', '$username', '$email', '$pwdhash', '1', 'Y')";
        $insertuser = mysqli_query($conn, $query);
        $last_id = mysqli_insert_id($conn);

        $stmt1 = $conn->prepare("UPDATE contractor SET uid = ? WHERE cid = ?");
        $stmt1->bind_param("ii", $last_id, $id,);
        $stmt1->execute();

        $query = "INSERT INTO contractor_users (contractor_id, user_id, role) 
        VALUES ('$id','$last_id', 'owner')";
        $insertuser1 = mysqli_query($conn, $query);

        if ($insertuser) {
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Akaun kontraktor berjaya dicipta!';

            header("Location: ../contractor_details?id=" . $_POST['id']);
            exit();
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Gagal mendaftarkan akaun: ' . mysqli_error($conn);

            header("Location: ../contractor_details?id=" . $_POST['id']);
            exit();
        }
    }
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Tiada data diterima.';
    // Send JSON response
    header("Location: ../contractor_details?id=" . $_POST['id']);
    exit();
}
