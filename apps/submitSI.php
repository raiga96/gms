<?php

/**
 * submitSI.php - Senior Developer Verified Version
 * Mengendalikan Site Instruction, Lampiran, dan Auto-Compression.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

require 'conn.php';

// ─── FUNGSI PEMAMPATAN IMEJ (DIPERBAIKI) ──────────────────────────────────────
function compressImage($source, $destination, $quality)
{
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } elseif ($info['mime'] == 'image/webp') {
        $image = imagecreatefromwebp($source);
    } else {
        return false;
    }

    // Simpan imej (Output sentiasa JPEG untuk saiz minima)
    imagejpeg($image, $destination, $quality);

    // PENTING: Buang objek imej dari memori RAM server
    imagedestroy($image);
    return $destination;
}

// ─── TERIMA DATA POST ────────────────────────────────────────────────────────
$ddId        = $_POST['ddId'] ?? 0;
$area_id     = $_POST['area_id'] ?? 0;
$contract_id = $_POST['mid'] ?? 0;
$num_si      = $_POST['si_no'] ?? '';
$si_category = $_POST['category'] ?? ''; // Pastikan DB length > 15
$instruction = $_POST['instruction'] ?? '';
$reference   = $_POST['reference'] ?? ''; // JSON String dari Flutter
$date_issued = $_POST['date'] ?? date('Y-m-d');
$issued_by   = $_POST['cid'] ?? 0;

if (empty($num_si) || empty($instruction)) {
    echo json_encode(["status" => "error", "message" => "Sila isi butiran arahan."]);
    exit;
}

$conn->begin_transaction();

try {
    // ─── 1. MASUKKAN DATA KE TABLE site_instruction ──────────────────────────────
    $sql_si = "INSERT INTO site_instruction (
        ddId, area_id, contract_id, num_si, si_category, 
        instruction, reference, date_issued, issued_by, created_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql_si);
    // i = integer, s = string
    $stmt->bind_param("iiisssssi", $ddId, $area_id, $contract_id, $num_si, $si_category, $instruction, $reference, $date_issued, $issued_by);
    $stmt->execute();
    $si_id = $conn->insert_id;

    // ─── 2. PROSES MUAT NAIK IMEJ (si_attachment) ───────────────────────────
    // Key 'images[]' dari Flutter akan dibaca sebagai 'images' di PHP
    if (isset($_FILES['images'])) {
        $upload_root = "attachment/si/";
        $upload_dir = $upload_root . $num_si . "/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (empty($tmp_name)) continue;

            $original_name = $_FILES['images']['name'][$key];
            $file_size     = $_FILES['images']['size'][$key];

            // Bersihkan nama fail (buang ruang kosong/simbol pelik)
            $clean_name    = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $original_name);
            $target_file   = $upload_dir . $clean_name;
            $file_ext      = strtolower(pathinfo($clean_name, PATHINFO_EXTENSION));

            // Filter Format
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'heic'];
            if (!in_array($file_ext, $allowed_ext)) continue;

            // Logik Compression (5MB = 5,242,880 bytes)
            if ($file_size > 5242880) {
                compressImage($tmp_name, $target_file, 50); // Mampatkan ke 50% kualiti
            } else {
                move_uploaded_file($tmp_name, $target_file);
            }

            // URL format yang anda minta
            $url = "https://jts.sarawak.gov.my/smartcut/apps/attachment/si/" . $num_si . "/" . $clean_name;

            $sql_att = "INSERT INTO si_attachment (num_si, file_name, file_path, url, created_date) VALUES (?, ?, ?, ?, NOW())";
            $stmt_att = $conn->prepare($sql_att);
            $stmt_att->bind_param("ssss", $num_si, $clean_name, $target_file, $url);
            $stmt_att->execute();
        }
    }

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Site Instruction ($num_si) has been recorded.", "si_id" => $si_id]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}

$conn->close();
