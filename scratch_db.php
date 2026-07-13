<?php
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require 'includes/connection.php';
$r=$conn->query('DESCRIBE users'); 
echo "USERS:\n";
while($row=$r->fetch_assoc()) { print_r($row); } 
$r2=$conn->query('DESCRIBE userinfo'); 
echo "USERINFO:\n";
if($r2) { while($row=$r2->fetch_assoc()) { print_r($row); } } else { echo 'No userinfo table: '.$conn->error; }
?>
