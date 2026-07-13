<?php
include 'connection.php';

$id = $_POST['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM subzone WHERE sz_id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
