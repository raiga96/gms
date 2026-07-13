<?php
require 'includes/connection.php';
$res = $conn->query("DESCRIBE contract_location");
$cols = [];
while($row = $res->fetch_assoc()) {
    $cols[] = $row;
}
echo json_encode($cols);
?>
