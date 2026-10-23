<?php

/**
 * cancelSI.php - Versi Padam Fail Imej Sahaja (Tanpa __DIR__)
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

require 'conn.php';

/**
 * Fungsi untuk mengimbas folder dan memadam fail imej sahaja.
 * Folder utama dan sub-folder akan dikekalkan.
 */
function deleteImagesOnly($dir)
{
    if (!is_dir($dir)) return false;

    // Senarai format imej yang dibenarkan untuk dipadam
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        // Pastikan ia adalah fail, bukannya folder
        if ($item->isFile()) {
            $path_info = pathinfo($item->getRealPath());
            $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

            // Jika fail tersebut adalah imej, padam fail itu
            if (in_array($extension, $allowed_extensions)) {
                if (!@unlink($item->getRealPath())) {
                    throw new Exception("Gagal memadam fail imej: " . $item->getFilename());
                }
            }
        }
    }
    return true;
}

$si_no = $_POST['si_no'] ?? '';

if (empty($si_no)) {
    echo json_encode(["status" => "error", "message" => "Nombor SI tidak dikesan."]);
    exit;
}

$conn->begin_transaction();

try {
    // Pembersihan nama folder (tukar '/' kepada '-')
    $si_no_clean = str_replace('/', '-', $si_no);
    $upload_dir = "attachment/si/" . $si_no_clean;

    // ─── 1. PROSES PEMADAMAN FIZIKAL (IMEJ SAHAJA) ──────────────────────────
    if (is_dir($upload_dir)) {
        if (!is_writable($upload_dir)) {
            throw new Exception("Folder dikesan, tetapi PHP tiada kebenaran untuk memadam fail di dalamnya.");
        }
        
        // Jalankan fungsi memadam imej sahaja
        deleteImagesOnly($upload_dir);
    } else {
        throw new Exception("Folder tidak dikesan di laluan relatif: " . getcwd() . "/" . $upload_dir);
    }

    // ─── 2. PROSES PEMADAMAN DATABASE ────────────────────────────────────────
    $sql_del_att = "DELETE FROM si_attachment WHERE num_si = ?";
    $stmt_a = $conn->prepare($sql_del_att);
    $stmt_a->bind_param("s", $si_no);
    $stmt_a->execute();

    $sql_del_si = "DELETE FROM site_instruction WHERE num_si = ?";
    $stmt_s = $conn->prepare($sql_del_si);
    $stmt_s->bind_param("s", $si_no);
    $stmt_s->execute();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Semua fail imej bagi SI $si_no telah dipadam, rekod database dikemas kini."
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "status" => "error",
        "message" => "Proses gagal: " . $e->getMessage()
    ]);
}

$conn->close();