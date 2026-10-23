<?php
require 'connect.php';
$uid = $_POST['uid'];
$remark = $_POST['remark'];
$ipAddr = $_POST['ipAddr'];
$act = $_POST['act'];
$lat = $_POST['lat'];
$long = $_POST['long'];
$dateToday = date('Y-m-d');
$clockTime = date('H:i:s');


        echo json_encode(array("status" => "error", "message" => "Under Maintenance"));
        exit();

?>