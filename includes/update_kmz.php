<?php
session_start();
include 'connection.php';

$uploadDir = '../kmz/';
$publicUrlBase = 'https://jts.sarawak.gov.my/dbku/kmz/';
$add_by = 1;

$zone_id = (int)$_POST['zone_id'];
$subzone_id = (int)$_POST['subzone_id'];

$status = 'success';
$message = 'KMZ updated successfully';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt1 = $conn->prepare("UPDATE kmz_file SET kmz_status = 'N' WHERE zone_id=? AND sz_id=?");
    $stmt1->bind_param("ii", $zone_id, $subzone_id);
    $stmt1->execute();

    if (!empty($_FILES['files']['name'][0])) {

        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {

            $originalName = basename($_FILES['files']['name'][$index]);

            // sanitize nama fail
            $cleanName = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $originalName);
            $cleanName = strtolower(time() . '_' . $cleanName);

            $targetPath = $uploadDir . $cleanName;
            $publicUrl = $publicUrlBase . $cleanName;

            if (move_uploaded_file($tmpName, $targetPath)) {

                $sql = "INSERT INTO kmz_file (zone_id, sz_id, kmz_file, file_path, url, add_by)
                        VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissss", $zone_id, $subzone_id, $cleanName, $targetPath, $publicUrl, $add_by);

                if (!$stmt->execute()) {
                    $status = 'error';
                    $message = 'DB insert failed: ' . $stmt->error;
                }

                $stmt->close();
            } else {
                $status = 'error';
                $message = 'Fail upload file';
            }
        }
    } else {
        $status = 'error';
        $message = 'No file selected';
    }
} else {
    $status = 'error';
    $message = 'Invalid request';
}

$_SESSION['status'] = $status;
$_SESSION['message'] = $message;

header("Location: ../update_sz.php?szid=" . $subzone_id);
exit();

$conn->close();
