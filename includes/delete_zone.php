<?php
include 'connection.php';

$id = $_POST['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM zone WHERE zone_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM subzone WHERE zone_id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
