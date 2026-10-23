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
