<?php
$gc_session = 3;
$yr = 2026;

$month = ceil($gc_session / 2);

if ($gc_session % 2 != 0) {
    $start_date = sprintf("%04d-%02d-01", $yr, $month);
    $end_date = sprintf("%04d-%02d-15", $yr, $month);
} else {
    $start_date = sprintf("%04d-%02d-16", $yr, $month);
    $end_date = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $yr, $month)));
}

echo "Session: $gc_session, Year: $yr\n";
echo "Start: $start_date\n";
echo "End: $end_date\n";

// Generate dates
$current = strtotime($start_date);
$last = strtotime($end_date);
while($current <= $last) {
    echo date("Y-m-d", $current) . "\n";
    $current = strtotime("+1 day", $current);
}
