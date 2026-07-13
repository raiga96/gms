<?php
require 'includes/connection.php';
$res = $conn->query("SHOW TABLES");
while($r = $res->fetch_array()) echo $r[0]."\n";

$res = $conn->query("DESCRIBE daily_records");
echo "--- daily_records ---\n";
while($r = $res->fetch_assoc()) echo $r['Field'] . " - " . $r['Type'] . "\n";

$res = $conn->query("DESCRIBE contract");
echo "--- contract ---\n";
while($r = $res->fetch_assoc()) echo $r['Field'] . " - " . $r['Type'] . "\n";

$res = $conn->query("DESCRIBE session");
if($res) {
    echo "--- session ---\n";
    while($r = $res->fetch_assoc()) echo $r['Field'] . " - " . $r['Type'] . "\n";
} else {
    echo "No session table\n";
}
