<?php
$user_ip = $_SERVER['REMOTE_ADDR'];

if ($user_ip === '127.0.0.1' || $user_ip === '::1') {
    //  echo "Running on localhost";
    $password = '';
    // Use localhost-specific credentials
} else {
    // echo "Running on server";
    $password = '';
    // Use server-specific credentials
}

$host = 'localhost';
$user = 'root';
$password1 = '';
$db = 'smartcut';
