<?php
require 'x.php';
$servername = $host;
$dBUsername = $user;
$dBPassword = $password;
$dBName = $db;

$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Global Authentication Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'login_action.php'];

if (!in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['user_id'])) {
        $redirect_url = "login.php";
        if (strpos($_SERVER['REQUEST_URI'], '/includes/') !== false) {
            $redirect_url = "../login.php";
        }
        header("Location: " . $redirect_url);
        exit();
    }
}
