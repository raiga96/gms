<?php
require_once __DIR__ . '/../includes/x.php';
$servername = $host;
$dBUsername = $user;
$dBPassword = $password;
$dBName = $db;
$dBPort = isset($port) ? (int)$port : 3306;

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName, $dBPort);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
