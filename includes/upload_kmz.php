<?php
session_start();
include 'connection.php';

// RBAC: Hanya peranan yang dibenarkan boleh memuat naik KMZ
if (function_exists('gms_require_role')) {
    gms_require_role(['ADM', 'ROOT', 'SO', 'ASO', 'SPV']);
}

$uploadDir = realpath(__DIR__ . '/../kmz');
if (!$uploadDir) {
    $uploadDir = __DIR__ . '/../kmz';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
}
$uploadDir = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

$publicUrlBase = 'https://jts.sarawak.gov.my/dbku/kmz/';
$add_by = (int)($_SESSION['user_id'] ?? 1);

$zone_id = (int)($_POST['zone_id'] ?? 0);
$subzone_id = (int)($_POST['subzone_id'] ?? 0);

$status = 'success';
$message = 'Fail KMZ berjaya dimuat naik.';

$allowed_extensions = ['kmz', 'kml'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            if (empty($tmpName) || !is_uploaded_file($tmpName)) {
                continue;
            }

            $originalName = basename($_FILES['files']['name'][$index]);
            $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // 1. Sekatan Ketat Jenis Fail (Whitelist)
            if (!in_array($fileExt, $allowed_extensions, true)) {
                $status = 'error';
                $message = 'Hanya fail format .kmz atau .kml sahaja dibenarkan.';
                break;
            }

            // 2. Semakan Had Saiz Fail (Maksimum 50MB)
            if (filesize($tmpName) > 50 * 1024 * 1024) {
                $status = 'error';
                $message = 'Saiz fail melebihi had maksimum 50MB.';
                break;
            }

            // 3. Semakan Magic Bytes (Keselamatan Muat Naik)
            $handle = fopen($tmpName, 'rb');
            $header = fread($handle, 4);
            fclose($handle);

            if ($fileExt === 'kmz' && $header !== "PK\x03\x04") {
                $status = 'error';
                $message = 'Kandungan fail KMZ tidak sah atau rosak.';
                break;
            }

            // Sanitize nama fail
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $cleanBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName);
            $cleanName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $cleanBase . '.' . $fileExt;

            $targetPath = $uploadDir . $cleanName;
            $publicUrl = $publicUrlBase . $cleanName;
            $dbPath = 'kmz/' . $cleanName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $sql = "INSERT INTO kmz_file (zone_id, sz_id, kmz_file, file_path, url, add_by)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisssi", $zone_id, $subzone_id, $cleanName, $dbPath, $publicUrl, $add_by);

                if (!$stmt->execute()) {
                    $status = 'error';
                    $message = 'Gagal menyimpan rekod fail ke pangkalan data.';
                }
                $stmt->close();
            } else {
                $status = 'error';
                $message = 'Gagal memindahkan fail ke direktori simpanan.';
                break;
            }
        }
    } else {
        $status = 'error';
        $message = 'Tiada fail dipilih untuk dimuat naik.';
    }
} else {
    $status = 'error';
    $message = 'Kaedah permintaan tidak sah.';
}

$_SESSION['status'] = $status;
$_SESSION['message'] = $message;

header("Location: ../update_sz.php?szid=" . $subzone_id);
exit();
