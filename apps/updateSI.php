<?php

/**
 * updateSI.php
 * Mengemas kini rekod Site Instruction, menambah lampiran baru, 
 * dan memadam lampiran lama (Fail & DB) secara automatik.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// 1. Sambungan Database
require 'conn.php';

// ─── FUNGSI PEMAMPATAN IMEJ ──────────────────────────────────────────────────
function compressImage($source, $destination, $quality)
{
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } elseif ($info['mime'] == 'image/webp') {
        $image = imagecreatefromwebp($source);
    } else {
        return false;
    }

    // Simpan imej (Format output JPEG)
    imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return $destination;
}

// ─── TERIMA DATA POST ────────────────────────────────────────────────────────
$num_si      = $_POST['si_no'] ?? '';
$instruction = $_POST['instruction'] ?? '';
$reference   = $_POST['reference'] ?? ''; // Senarai JSON dari Flutter
$delete_json = $_POST['delete_images'] ?? '[]'; // Senarai URL untuk dipadam
$delete_list = json_decode($delete_json, true);

if (empty($num_si)) {
    echo json_encode(["status" => "error", "message" => "Nombor SI tidak dikesan."]);
    exit;
}

// Mulakan Transaksi Database
$conn->begin_transaction();

try {
    // ─── 1. KEMASKINI DATA UTAMA ─────────────────────────────────────────────
    $sql_update = "UPDATE site_instruction SET instruction = ?, reference = ? WHERE num_si = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sss", $instruction, $reference, $num_si);
    $stmt->execute();

    // ─── 2. PROSES PEMADAMAN GAMBAR (DB & FOLDER SERVER) ────────────────────
    if (!empty($delete_list) && is_array($delete_list)) {
        foreach ($delete_list as $url) {
            // Cari path fail fizikal berdasarkan URL
            $sql_find = "SELECT file_path FROM si_attachment WHERE url = ?";
            $stmt_f = $conn->prepare($sql_find);
            $stmt_f->bind_param("s", $url);
            $stmt_f->execute();
            $res = $stmt_f->get_result();

            if ($row = $res->fetch_assoc()) {
                $physical_path = $row['file_path'];

                // Padam fail dari storan server (Physical Delete)
                if (file_exists($physical_path)) {
                    unlink($physical_path);
                }

                // Padam rekod dari table si_attachment
                $sql_del = "DELETE FROM si_attachment WHERE url = ?";
                $stmt_d = $conn->prepare($sql_del);
                $stmt_d->bind_param("s", $url);
                $stmt_d->execute();
            }
        }
    }

    // ─── 3. PROSES MUAT NAIK IMEJ BARU (JIKA ADA) ──────────────────────────
    if (isset($_FILES['images'])) {
        $upload_dir = "attachment/si/" . $num_si . "/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (empty($tmp_name)) continue;

            $original_name = $_FILES['images']['name'][$key];
            $file_size     = $_FILES['images']['size'][$key];

            // Nama fail unik menggunakan timestamp
            $clean_name    = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $original_name);
            $target_file   = $upload_dir . $clean_name;
            $file_ext      = strtolower(pathinfo($clean_name, PATHINFO_EXTENSION));

            // Hanya format imej dibenarkan
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'heic'];
            if (!in_array($file_ext, $allowed_ext)) continue;

            // Logik Compression jika > 5MB
            if ($file_size > 5242880) {
                compressImage($tmp_name, $target_file, 50);
            } else {
                move_uploaded_file($tmp_name, $target_file);
            }

            // Simpan info ke table si_attachment
            $new_url = "https://jts.sarawak.gov.my/smartcut/apps/attachment/si/" . $num_si . "/" . $clean_name;

            $sql_att = "INSERT INTO si_attachment (num_si, file_name, file_path, url, created_date) VALUES (?, ?, ?, ?, NOW())";
            $stmt_att = $conn->prepare($sql_att);
            $stmt_att->bind_param("ssss", $num_si, $clean_name, $target_file, $new_url);
            $stmt_att->execute();
        }
    }

    // Jika semua berjaya, simpan perubahan
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Rekod SI $num_si telah berjaya dikemas kini."]);
} catch (Exception $e) {
    // Jika ralat, batalkan semua perubahan
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Ralat: " . $e->getMessage()]);
}

$conn->close();
