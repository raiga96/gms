<?php
$servername = "localhost";
$dBUsername = "root";
$dBPassword= "KataLaluan4kik@JTS";
$dBName = "easy";
$dBName2 = "hems";

$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);

$conn2 = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName2);


if($conn->connect_error){
  die("Connection failed: ".mysqli_connect_error());
}
if($conn2->connect_error){
  die("Connection failed: ".mysqli_connect_error());
}


