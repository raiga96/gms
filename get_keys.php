<?php
require 'includes/connection.php';
$res = $conn->query("SELECT * FROM contract_location LIMIT 1");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    file_put_contents('keys.txt', json_encode(array_keys($row)));
} else {
    $res = $conn->query("DESCRIBE contract_location");
    $cols = [];
    while($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    file_put_contents('keys.txt', json_encode($cols));
}
?>
