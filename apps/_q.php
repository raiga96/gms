<?php
// Prepared SELECT helper: gms_q($conn, $sql, 'ss', [$a, $b]) -> mysqli_result|false
function gms_q($conn, $sql, $types = '', array $args = [])
{
    $st = $conn->prepare($sql);
    if (!$st) return false;
    if ($types !== '') $st->bind_param($types, ...$args);
    return $st->execute() ? $st->get_result() : false;
}
